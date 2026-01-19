<?php
/**
 * Formidable_Sniffs_CodeAnalysis_FlipNestedIfToEarlyReturnSniff
 *
 * Detects functions where the last code before return is an if statement
 * containing nested structures (loops or conditions). Suggests flipping
 * to early return to reduce indentation.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects if statements with nested code that should use early return.
 *
 * Bad:
 * function example($key) {
 *     if (strlen($key) > 60) {
 *         $key = substr($key, 0, 60);
 *         if (is_numeric($key)) {
 *             $key .= 'a';
 *         }
 *     }
 *     return $key;
 * }
 *
 * Good:
 * function example($key) {
 *     if (strlen($key) <= 60) {
 *         return $key;
 *     }
 *     $key = substr($key, 0, 60);
 *     if (is_numeric($key)) {
 *         $key .= 'a';
 *     }
 *     return $key;
 * }
 */
class FlipNestedIfToEarlyReturnSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FUNCTION );
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

		// Get the function's scope opener and closer.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$funcOpener = $tokens[ $stackPtr ]['scope_opener'];
		$funcCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find the last statement before the function closer.
		// Should be a return statement.
		$lastToken = $phpcsFile->findPrevious( T_WHITESPACE, $funcCloser - 1, $funcOpener, true );

		if ( false === $lastToken || $tokens[ $lastToken ]['code'] !== T_SEMICOLON ) {
			return;
		}

		// Find the return keyword for this statement.
		$returnToken = $this->findReturnForSemicolon( $phpcsFile, $lastToken, $funcOpener );

		if ( false === $returnToken ) {
			return;
		}

		// The return should be at the function's direct scope level.
		if ( ! $this->isAtFunctionLevel( $phpcsFile, $returnToken, $stackPtr ) ) {
			return;
		}

		// Find what's before the return statement.
		$beforeReturn = $phpcsFile->findPrevious( T_WHITESPACE, $returnToken - 1, $funcOpener, true );

		if ( false === $beforeReturn ) {
			return;
		}

		// Should be a closing brace of an if statement.
		if ( $tokens[ $beforeReturn ]['code'] !== T_CLOSE_CURLY_BRACKET ) {
			return;
		}

		// Find what this brace belongs to.
		if ( ! isset( $tokens[ $beforeReturn ]['scope_condition'] ) ) {
			return;
		}

		$scopeCondition = $tokens[ $beforeReturn ]['scope_condition'];

		// Must be an if (not else, elseif, loop, etc).
		if ( $tokens[ $scopeCondition ]['code'] !== T_IF ) {
			return;
		}

		$ifToken = $scopeCondition;

		// Check that this if doesn't have an else/elseif.
		$afterIfClose = $phpcsFile->findNext( T_WHITESPACE, $beforeReturn + 1, null, true );

		if ( false !== $afterIfClose && $afterIfClose !== $returnToken ) {
			// There's something between the if close and return that's not whitespace.
			// Check if it's else/elseif.
			if ( in_array( $tokens[ $afterIfClose ]['code'], array( T_ELSE, T_ELSEIF ), true ) ) {
				return;
			}
		}

		// Check that the if contains nested structures (another if, loop, etc).
		if ( ! $this->containsNestedStructure( $phpcsFile, $ifToken ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'If statement with nested code before return. Consider flipping to early return to reduce indentation.',
			$ifToken,
			'Found'
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $ifToken, $returnToken, $lastToken, $funcOpener );
		}
	}

	/**
	 * Find the return token for a given semicolon.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $semicolon The semicolon position.
	 * @param int  $limit     The limit to search back to.
	 *
	 * @return false|int
	 */
	private function findReturnForSemicolon( File $phpcsFile, $semicolon, $limit ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $semicolon - 1; $i > $limit; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_RETURN ) {
				return $i;
			}

			if ( $tokens[ $i ]['code'] === T_SEMICOLON ) {
				// Hit another statement, no return found.
				return false;
			}
		}

		return false;
	}

	/**
	 * Check if a token is at the function's direct scope level.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $tokenPtr    The token position.
	 * @param int  $funcPtr     The function token position.
	 *
	 * @return bool
	 */
	private function isAtFunctionLevel( File $phpcsFile, $tokenPtr, $funcPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $tokenPtr ]['conditions'] ) ) {
			return false;
		}

		$conditions = $tokens[ $tokenPtr ]['conditions'];

		// The function should be the innermost condition.
		// For class methods, there may also be a class condition.
		if ( ! isset( $conditions[ $funcPtr ] ) ) {
			return false;
		}

		// Get the keys and check if funcPtr is the last (innermost) condition.
		$conditionKeys = array_keys( $conditions );
		$lastCondition = end( $conditionKeys );

		return $lastCondition === $funcPtr;
	}

	/**
	 * Check if an if statement contains nested structures.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $ifToken   The if token position.
	 *
	 * @return bool
	 */
	private function containsNestedStructure( File $phpcsFile, $ifToken ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $ifToken ]['scope_opener'] ) || ! isset( $tokens[ $ifToken ]['scope_closer'] ) ) {
			return false;
		}

		$scopeOpener = $tokens[ $ifToken ]['scope_opener'];
		$scopeCloser = $tokens[ $ifToken ]['scope_closer'];

		$nestedTokens = array(
			T_IF,
			T_FOREACH,
			T_FOR,
			T_WHILE,
			T_DO,
			T_SWITCH,
		);

		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], $nestedTokens, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Apply the fix.
	 *
	 * @param File $phpcsFile       The file being scanned.
	 * @param int  $ifToken         The if token position.
	 * @param int  $returnToken     The return token position.
	 * @param int  $returnSemicolon The return semicolon position.
	 * @param int  $funcOpener      The function opener position.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $ifToken, $returnToken, $returnSemicolon, $funcOpener ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$ifOpener        = $tokens[ $ifToken ]['scope_opener'];
		$ifCloser        = $tokens[ $ifToken ]['scope_closer'];
		$conditionOpener = $tokens[ $ifToken ]['parenthesis_opener'];
		$conditionCloser = $tokens[ $ifToken ]['parenthesis_closer'];

		// Get the original condition.
		$originalCondition = '';

		for ( $i = $conditionOpener + 1; $i < $conditionCloser; $i++ ) {
			$originalCondition .= $tokens[ $i ]['content'];
		}
		$originalCondition = trim( $originalCondition );

		// Negate the condition.
		$negatedCondition = $this->negateCondition( $originalCondition );

		// Get the if body content.
		$ifBodyContent = '';

		for ( $i = $ifOpener + 1; $i < $ifCloser; $i++ ) {
			$ifBodyContent .= $tokens[ $i ]['content'];
		}

		// Get the return statement.
		$returnStatement = '';

		for ( $i = $returnToken; $i <= $returnSemicolon; $i++ ) {
			$returnStatement .= $tokens[ $i ]['content'];
		}

		// Dedent the if body.
		$ifBodyContent = $this->dedentCode( $ifBodyContent );

		// Get the indentation.
		$ifIndent = $this->getIndentation( $phpcsFile, $ifToken );

		$fixer->beginChangeset();

		// Replace the condition with negated version.
		for ( $i = $conditionOpener + 1; $i < $conditionCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
		$fixer->addContent( $conditionOpener, ' ' . $negatedCondition . ' ' );

		// Replace the if body with just the return statement.
		for ( $i = $ifOpener + 1; $i < $ifCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
		$fixer->addContent( $ifOpener, $phpcsFile->eolChar . $ifIndent . "\t" . $returnStatement . $phpcsFile->eolChar . $ifIndent );

		// Remove the old return statement.
		for ( $i = $ifCloser + 1; $i <= $returnSemicolon; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Add the dedented if body after the if, followed by the return.
		$ifBodyContent = rtrim( $ifBodyContent );
		$fixer->addContent(
			$ifCloser,
			$phpcsFile->eolChar . $phpcsFile->eolChar .
			$ifIndent . ltrim( $ifBodyContent ) . $phpcsFile->eolChar .
			$phpcsFile->eolChar .
			$ifIndent . $returnStatement . $phpcsFile->eolChar
		);

		$fixer->endChangeset();
	}

	/**
	 * Negate a condition string.
	 *
	 * @param string $condition The original condition.
	 *
	 * @return string The negated condition.
	 */
	private function negateCondition( $condition ) {
		$condition = trim( $condition );

		// Check if this is a compound condition (has && or || at top level).
		$isCompound = $this->hasTopLevelOperator( $condition );

		// Only remove leading ! if it's a simple condition (not compound).
		if ( ! $isCompound ) {
			// If condition starts with !, remove it.
			if ( strpos( $condition, '! ' ) === 0 ) {
				return substr( $condition, 2 );
			}

			if ( strpos( $condition, '!' ) === 0 && strpos( $condition, '!=' ) !== 0 ) {
				return substr( $condition, 1 );
			}

			// Try to flip comparison operators.
			$flipped = $this->flipComparisonOperator( $condition );

			if ( $flipped !== false ) {
				return $flipped;
			}

			// Simple condition, just add !
			return '! ' . $condition;
		}

		// For compound conditions, wrap in parentheses and negate.
		return '! ( ' . $condition . ' )';
	}

	/**
	 * Check if a condition has && or || at the top level.
	 *
	 * @param string $condition The condition to check.
	 *
	 * @return bool
	 */
	private function hasTopLevelOperator( $condition ) {
		$parenDepth = 0;
		$len        = strlen( $condition );

		for ( $i = 0; $i < $len; $i++ ) {
			$char = $condition[ $i ];

			if ( $char === '(' ) {
				++$parenDepth;
				continue;
			}

			if ( $char === ')' ) {
				--$parenDepth;
				continue;
			}

			if ( $parenDepth !== 0 ) {
				continue;
			}

			if ( $i < $len - 1 ) {
				$twoChars = $condition[ $i ] . $condition[ $i + 1 ];

				if ( $twoChars === '&&' || $twoChars === '||' ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Try to flip a comparison operator.
	 *
	 * @param string $condition The condition to flip.
	 *
	 * @return false|string
	 */
	private function flipComparisonOperator( $condition ) {
		$operatorMap = array(
			'!==' => '===',
			'===' => '!==',
			'!='  => '==',
			'=='  => '!=',
			'>='  => '<',
			'<='  => '>',
			'>'   => '<=',
			'<'   => '>=',
		);

		foreach ( $operatorMap as $op => $opposite ) {
			$pos = strpos( $condition, $op );

			if ( $pos !== false ) {
				if ( strpos( $condition, '&&' ) !== false || strpos( $condition, '||' ) !== false ) {
					return false;
				}

				// Skip if this is part of -> (object operator).
				if ( $op === '>' && $pos > 0 && $condition[ $pos - 1 ] === '-' ) {
					continue;
				}

				return substr( $condition, 0, $pos ) . $opposite . substr( $condition, $pos + strlen( $op ) );
			}
		}

		return false;
	}

	/**
	 * Get the indentation of a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The token position.
	 *
	 * @return string
	 */
	private function getIndentation( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$lineStart = $stackPtr;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			--$lineStart;
		}

		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			return $tokens[ $lineStart ]['content'];
		}

		return '';
	}

	/**
	 * Remove one level of indentation from code.
	 *
	 * @param string $code The code to dedent.
	 *
	 * @return string
	 */
	private function dedentCode( $code ) {
		$lines  = explode( "\n", $code );
		$result = array();

		foreach ( $lines as $line ) {
			if ( strpos( $line, "\t" ) === 0 ) {
				$line = substr( $line, 1 );
			} elseif ( strpos( $line, '    ' ) === 0 ) {
				$line = substr( $line, 4 );
			}
			$result[] = $line;
		}

		return implode( "\n", $result );
	}
}
