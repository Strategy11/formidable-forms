<?php
/**
 * Formidable_Sniffs_CodeAnalysis_InlineSingleUseVariableSniff
 *
 * Detects variables that are assigned from a function call and used only once.
 * These can be inlined directly at the usage site.
 *
 * Conditions to flag:
 * 1. The variable is assigned once and referenced once in the function scope.
 * 2. The function name is as descriptive as the variable name.
 * 3. The function call expression is not 20+ characters longer than the variable name.
 * 4. The function scope has no include/require statements (variable could be used in a view).
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects single-use variables assigned from a function call that can be inlined.
 *
 * Bad:
 * $count = FrmDb::get_count( $table, $where );
 * return $count;
 *
 * $fields = FrmField::getAll( $where );
 * foreach ( $fields as $field ) {
 *
 * Good (inlined):
 * return FrmDb::get_count( $table, $where );
 *
 * foreach ( FrmField::getAll( $where ) as $field ) {
 *
 * Kept (variable adds context):
 * $active_field_count = count( array_filter( $fields, 'is_active' ) );
 *
 * Kept (function call too long):
 * $id = FrmSomeVeryLongClassName::get_some_very_long_method_name( $arg1, $arg2, $arg3 );
 */
class InlineSingleUseVariableSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_VARIABLE );
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$variableName = $tokens[ $stackPtr ]['content'];

		if ( '$this' === $variableName ) {
			return;
		}

		// Check if this is an assignment (next non-whitespace is =).
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_EQUAL ) {
			return;
		}

		// Make sure this is a simple variable, not $var['key'] or $var->prop.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prevToken && in_array( $tokens[ $prevToken ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR ), true ) ) {
			return;
		}

		// Find the semicolon ending this assignment.
		$assignmentEnd = $this->findAssignmentEnd( $phpcsFile, $nextToken + 1 );

		if ( false === $assignmentEnd ) {
			return;
		}

		// Get the right-hand side of the assignment (the value expression).
		$valueStart = $phpcsFile->findNext( T_WHITESPACE, $nextToken + 1, $assignmentEnd, true );

		if ( false === $valueStart ) {
			return;
		}

		// The value must start with a function call (not a literal, variable, array, etc.).
		$callInfo = $this->getFunctionCallInfo( $phpcsFile, $tokens, $valueStart, $assignmentEnd );

		if ( false === $callInfo ) {
			return;
		}

		// Find the function scope we're in.
		$functionScope = $this->getFunctionScope( $phpcsFile, $stackPtr );

		if ( false === $functionScope ) {
			return;
		}

		$functionOpener = $tokens[ $functionScope ]['scope_opener'];
		$functionCloser = $tokens[ $functionScope ]['scope_closer'];

		// Skip if the function has any include/require statements (variable may be used in a view).
		if ( $this->hasRequireOrInclude( $phpcsFile, $tokens, $functionOpener, $functionCloser ) ) {
			return;
		}

		// Skip if the variable is referenced by name in a compact() call.
		$varNameClean = ltrim( $variableName, '$' );

		if ( $this->isReferencedInCompact( $phpcsFile, $tokens, $functionOpener, $functionCloser, $varNameClean ) ) {
			return;
		}

		// Count all occurrences of this variable in the function scope.
		$occurrences = $this->findVariableOccurrences( $tokens, $functionOpener, $functionCloser, $variableName );

		// Must appear exactly twice: once for assignment, once for usage.
		if ( count( $occurrences ) !== 2 ) {
			return;
		}

		// First occurrence should be this assignment.
		if ( $occurrences[0] !== $stackPtr ) {
			return;
		}

		$usagePtr = $occurrences[1];

		// The usage must be after the assignment ends.
		if ( $usagePtr <= $assignmentEnd ) {
			return;
		}

		// Skip if the variable is used in a complex assignment context (e.g., $var .= or $var[]).
		if ( $this->isComplexUsage( $phpcsFile, $tokens, $usagePtr ) ) {
			return;
		}

		// Check condition 2: function name descriptiveness vs variable name.
		$functionName = $callInfo['function_name'];

		if ( $this->variableAddsContext( $varNameClean, $functionName ) ) {
			return;
		}

		// Check condition 3: function call length vs variable name length.
		$callExpression = $this->getTokenContent( $phpcsFile, $valueStart, $assignmentEnd - 1 );
		$callLength     = strlen( trim( $callExpression ) );
		$varLength      = strlen( $variableName );

		if ( $callLength >= $varLength + 20 ) {
			return;
		}

		// This is an inlineable single-use variable.
		$fix = $phpcsFile->addFixableError(
			'Variable %s is assigned once and used once. Inline the function call directly.',
			$stackPtr,
			'Found',
			array( $variableName )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $tokens, $stackPtr, $assignmentEnd, $usagePtr, $valueStart );
		}
	}

	/**
	 * Find the semicolon that ends the assignment, respecting nested parentheses and brackets.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position after the equals sign.
	 *
	 * @return false|int
	 */
	private function findAssignmentEnd( File $phpcsFile, $start ) {
		$tokens = $phpcsFile->getTokens();
		$depth  = 0;

		for ( $i = $start; isset( $tokens[ $i ] ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( in_array( $code, array( T_OPEN_PARENTHESIS, T_OPEN_SQUARE_BRACKET, T_OPEN_CURLY_BRACKET ), true ) ) {
				++$depth;
			} elseif ( in_array( $code, array( T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET, T_CLOSE_CURLY_BRACKET ), true ) ) {
				--$depth;
			} elseif ( 0 === $depth && T_SEMICOLON === $code ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Get function call info from the right-hand side of an assignment.
	 *
	 * Supports:
	 * - function_name(...)
	 * - ClassName::method(...)
	 * - $var->method(...)
	 * - new ClassName(...)
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $start     Start of the value expression.
	 * @param int   $end       Semicolon position.
	 *
	 * @return array|false Array with 'function_name' key, or false.
	 */
	private function getFunctionCallInfo( File $phpcsFile, array $tokens, $start, $end ) {
		$current = $start;

		// Handle 'new ClassName(...)' — skip these as the variable likely adds context.
		if ( $tokens[ $current ]['code'] === T_NEW ) {
			return false;
		}

		// Handle type casts like (int), (array) — skip ahead.
		if ( $tokens[ $current ]['code'] === T_OPEN_PARENTHESIS ) {
			return false;
		}

		// Handle negation operator.
		if ( $tokens[ $current ]['code'] === T_BOOLEAN_NOT ) {
			return false;
		}

		// Collect the function/method name parts.
		$nameParts     = array();
		$lastNameToken = $current;

		while ( $current < $end ) {
			$code = $tokens[ $current ]['code'];

			if ( in_array( $code, array( T_STRING, T_VARIABLE, T_SELF, T_STATIC, T_PARENT ), true ) ) {
				$nameParts[]  = $tokens[ $current ]['content'];
				$lastNameToken = $current;
			} elseif ( in_array( $code, array( T_DOUBLE_COLON, T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ), true ) ) {
				$nameParts[] = $tokens[ $current ]['content'];
			} elseif ( T_OPEN_PARENTHESIS === $code ) {
				break;
			} elseif ( T_WHITESPACE !== $code ) {
				// Something unexpected before the opening parenthesis.
				return false;
			}

			++$current;
		}

		if ( empty( $nameParts ) || $current >= $end ) {
			return false;
		}

		if ( $tokens[ $current ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		// Verify there is a matching close parenthesis.
		if ( ! isset( $tokens[ $current ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $current ]['parenthesis_closer'];

		// After the close parenthesis, the next non-whitespace should be the semicolon.
		// Allow chained method calls or property access after the call.
		$afterClose = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, $end + 1, true );

		if ( false !== $afterClose && $afterClose !== $end ) {
			// There's something after the function call before the semicolon.
			// This could be a chained call, array access, etc.
			// Still valid but we use the full expression as the "call".
		}

		// Extract just the function/method name (last meaningful part).
		$functionName = end( $nameParts );

		// If the last part is an operator, go back one.
		if ( in_array( $functionName, array( '::', '->', '?->' ), true ) ) {
			$functionName = prev( $nameParts );
		}

		// Strip $ from variable names for comparison.
		$functionName = ltrim( $functionName, '$' );

		return array(
			'function_name' => $functionName,
			'full_call'     => implode( '', $nameParts ),
		);
	}

	/**
	 * Get the function scope for a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The token position.
	 *
	 * @return false|int The function token position or false.
	 */
	private function getFunctionScope( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( in_array( $tokens[ $i ]['code'], array( T_FUNCTION, T_CLOSURE ), true ) ) {
				if ( isset( $tokens[ $i ]['scope_opener'] ) && isset( $tokens[ $i ]['scope_closer'] ) ) {
					if ( $stackPtr > $tokens[ $i ]['scope_opener'] && $stackPtr < $tokens[ $i ]['scope_closer'] ) {
						return $i;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if there are require/include statements anywhere in the function scope.
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param array $tokens         The token stack.
	 * @param int   $functionOpener Function opener position.
	 * @param int   $functionCloser Function closer position.
	 *
	 * @return bool
	 */
	private function hasRequireOrInclude( File $phpcsFile, array $tokens, $functionOpener, $functionCloser ) {
		$includeTokens = array( T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE );

		for ( $i = $functionOpener + 1; $i < $functionCloser; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], $includeTokens, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a variable name is referenced inside a compact() call as a string argument.
	 *
	 * compact( 'form_ids' ) references $form_ids by name, so the variable cannot be inlined.
	 *
	 * @param File   $phpcsFile      The file being scanned.
	 * @param array  $tokens         The token stack.
	 * @param int    $functionOpener Function opener position.
	 * @param int    $functionCloser Function closer position.
	 * @param string $varNameClean   Variable name without the leading $.
	 *
	 * @return bool
	 */
	private function isReferencedInCompact( File $phpcsFile, array $tokens, $functionOpener, $functionCloser, $varNameClean ) {
		for ( $i = $functionOpener + 1; $i < $functionCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_STRING || 'compact' !== strtolower( $tokens[ $i ]['content'] ) ) {
				continue;
			}

			$openParen = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
				continue;
			}

			if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
				continue;
			}

			$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

			for ( $j = $openParen + 1; $j < $closeParen; $j++ ) {
				if ( $tokens[ $j ]['code'] !== T_CONSTANT_ENCAPSED_STRING ) {
					continue;
				}

				$stringContent = trim( $tokens[ $j ]['content'], "\"'" );

				if ( $stringContent === $varNameClean ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Find all occurrences of a variable in a range.
	 *
	 * @param array  $tokens    The token stack.
	 * @param int    $start     Start position.
	 * @param int    $end       End position.
	 * @param string $variableName The variable name including $.
	 *
	 * @return int[] Array of token positions.
	 */
	private function findVariableOccurrences( array $tokens, $start, $end, $variableName ) {
		$occurrences = array();

		for ( $i = $start + 1; $i < $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === $variableName ) {
				$occurrences[] = $i;
			}
		}

		return $occurrences;
	}

	/**
	 * Check if the variable usage is complex (e.g., used as array key, concatenation assignment, etc.).
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $usagePtr  The usage token position.
	 *
	 * @return bool
	 */
	private function isComplexUsage( File $phpcsFile, array $tokens, $usagePtr ) {
		$afterUsage = $phpcsFile->findNext( T_WHITESPACE, $usagePtr + 1, null, true );

		if ( false === $afterUsage ) {
			return true;
		}

		// If the variable is being assigned to (e.g., used on the left side of =).
		$assignmentOps = array(
			T_EQUAL,
			T_PLUS_EQUAL,
			T_MINUS_EQUAL,
			T_CONCAT_EQUAL,
			T_MUL_EQUAL,
			T_DIV_EQUAL,
			T_MOD_EQUAL,
			T_AND_EQUAL,
			T_OR_EQUAL,
			T_XOR_EQUAL,
			T_SL_EQUAL,
			T_SR_EQUAL,
			T_DOUBLE_ARROW,
		);

		if ( in_array( $tokens[ $afterUsage ]['code'], $assignmentOps, true ) ) {
			return true;
		}

		// Check if followed by ++ or --.
		if ( in_array( $tokens[ $afterUsage ]['code'], array( T_INC, T_DEC ), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the variable name adds meaningful context beyond what the function name provides.
	 *
	 * @param string $varName      Variable name without $.
	 * @param string $functionName The function or method name.
	 *
	 * @return bool True if the variable adds context and should be kept.
	 */
	private function variableAddsContext( $varName, $functionName ) {
		// Normalize both names for comparison: convert to lowercase, strip underscores/camelCase.
		$varWords      = $this->extractWords( $varName );
		$functionWords = $this->extractWords( $functionName );

		if ( empty( $varWords ) || empty( $functionWords ) ) {
			return false;
		}

		// Check how many variable words are already present in the function name.
		$matchedWords = 0;

		foreach ( $varWords as $varWord ) {
			foreach ( $functionWords as $funcWord ) {
				if ( $varWord === $funcWord || $this->isSingularPluralMatch( $varWord, $funcWord ) ) {
					++$matchedWords;
					break;
				}
			}
		}

		// If all variable words are represented in the function name, it adds no context.
		if ( $matchedWords === count( $varWords ) ) {
			return false;
		}

		// If more than half the variable words are NOT in the function name,
		// the variable likely adds meaningful context.
		$unmatchedRatio = ( count( $varWords ) - $matchedWords ) / count( $varWords );

		return $unmatchedRatio >= 0.5;
	}

	/**
	 * Extract individual words from a snake_case or camelCase name.
	 *
	 * @param string $name The name to split.
	 *
	 * @return string[] Lowercase words.
	 */
	private function extractWords( $name ) {
		// Split on underscores first.
		$parts = explode( '_', $name );

		$words = array();

		foreach ( $parts as $part ) {
			if ( '' === $part ) {
				continue;
			}

			// Split camelCase.
			$camelWords = preg_split( '/(?<=[a-z])(?=[A-Z])/', $part );

			foreach ( $camelWords as $word ) {
				$word = strtolower( $word );

				if ( '' !== $word ) {
					$words[] = $word;
				}
			}
		}

		return $words;
	}

	/**
	 * Check if two words are singular/plural forms of each other.
	 *
	 * @param string $word1 First word.
	 * @param string $word2 Second word.
	 *
	 * @return bool
	 */
	private function isSingularPluralMatch( $word1, $word2 ) {
		// Simple check: one is the other + 's' or 'es'.
		if ( $word1 . 's' === $word2 || $word2 . 's' === $word1 ) {
			return true;
		}

		if ( $word1 . 'es' === $word2 || $word2 . 'es' === $word1 ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the content of tokens in a range as a string.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position (inclusive).
	 * @param int  $end       End position (inclusive).
	 *
	 * @return string
	 */
	private function getTokenContent( File $phpcsFile, $start, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';

		for ( $i = $start; $i <= $end; $i++ ) {
			$content .= $tokens[ $i ]['content'];
		}

		return $content;
	}

	/**
	 * Apply the fix: remove the variable assignment line and inline the value at the usage site.
	 *
	 * @param File  $phpcsFile     The file being scanned.
	 * @param array $tokens        The token stack.
	 * @param int   $varStart      Start of variable declaration.
	 * @param int   $assignmentEnd Semicolon ending the assignment.
	 * @param int   $usagePtr      Token position where the variable is used.
	 * @param int   $valueStart    Start of the value expression.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, array $tokens, $varStart, $assignmentEnd, $usagePtr, $valueStart ) {
		$phpcsFile->fixer->beginChangeset();

		// Get the value content (everything between = and ;).
		$valueContent = '';

		for ( $i = $valueStart; $i < $assignmentEnd; $i++ ) {
			$valueContent .= $tokens[ $i ]['content'];
		}

		// Find the start of the line for the variable declaration.
		$lineStart = $varStart;

		for ( $i = $varStart - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $tokens[ $varStart ]['line'] ) {
				break;
			}
			$lineStart = $i;
		}

		// Remove the variable declaration line.
		for ( $i = $lineStart; $i <= $assignmentEnd; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Remove the newline after the semicolon.
		if ( isset( $tokens[ $assignmentEnd + 1 ] ) && $tokens[ $assignmentEnd + 1 ]['code'] === T_WHITESPACE ) {
			$wsContent = $tokens[ $assignmentEnd + 1 ]['content'];

			if ( strpos( $wsContent, "\n" ) === 0 ) {
				$phpcsFile->fixer->replaceToken( $assignmentEnd + 1, substr( $wsContent, 1 ) );
			}
		}

		// Replace the variable at the usage site with the value expression.
		$phpcsFile->fixer->replaceToken( $usagePtr, $valueContent );

		$phpcsFile->fixer->endChangeset();
	}
}
