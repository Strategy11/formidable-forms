<?php
/**
 * Formidable_Sniffs_CodeAnalysis_FlipIfToEarlyReturnVariableSniff
 *
 * Detects functions where an if statement modifies a variable that is returned at the end,
 * and the condition can be flipped to return the variable early.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects functions that can use early return with a variable.
 *
 * Bad:
 * function example($value) {
 *     $result = 'default';
 *     if ($condition) {
 *         $result = 'modified';
 *     }
 *     return $result;
 * }
 *
 * Good:
 * function example($value) {
 *     $result = 'default';
 *     if (! $condition) {
 *         return $result;
 *     }
 *     $result = 'modified';
 *     return $result;
 * }
 */
class FlipIfToEarlyReturnVariableSniff implements Sniff {

	/**
	 * Minimum number of statements inside the if body to trigger this sniff.
	 *
	 * @var int
	 */
	public $minStatements = 3;

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

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find the last return statement at the function's top level.
		$lastReturn = $this->findLastTopLevelReturn( $phpcsFile, $tokens, $scopeOpener, $scopeCloser );

		if ( false === $lastReturn ) {
			return;
		}

		// Check if the return statement returns a simple variable.
		$returnVar = $this->getReturnVariable( $phpcsFile, $tokens, $lastReturn, $scopeCloser );

		if ( false === $returnVar ) {
			return;
		}

		// Find the if statement that precedes the return (should be the only thing between assignments and return).
		$ifToken = $this->findPrecedingIf( $phpcsFile, $tokens, $scopeOpener, $lastReturn );

		if ( false === $ifToken ) {
			return;
		}

		// Check if this if has an else or elseif - skip those.
		if ( $this->hasElseOrElseif( $phpcsFile, $tokens, $ifToken ) ) {
			return;
		}

		// Check if the if's closing brace is followed only by the return statement.
		$ifCloser = $tokens[ $ifToken ]['scope_closer'];
		$afterIf  = $phpcsFile->findNext(
			array( T_WHITESPACE, T_COMMENT ),
			$ifCloser + 1,
			$scopeCloser,
			true
		);

		if ( false === $afterIf || $afterIf !== $lastReturn ) {
			return;
		}

		// Count statements inside the if body.
		$statementCount = $this->countStatementsInScope( $phpcsFile, $tokens, $ifToken );

		if ( $statementCount < $this->minStatements ) {
			return;
		}

		// Get the condition for the error message.
		$condition = $this->getConditionString( $phpcsFile, $tokens, $ifToken );

		$fix = $phpcsFile->addFixableError(
			'Consider flipping the condition "if (%s)" to "if (%s) { return %s; }" to reduce nesting.',
			$ifToken,
			'Found',
			array( $condition, $this->suggestNegatedCondition( $condition ), $returnVar )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $tokens, $ifToken, $returnVar );
		}
	}

	/**
	 * Find the last return statement at the function's top level.
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param array $tokens      The token stack.
	 * @param int   $scopeOpener Function scope opener.
	 * @param int   $scopeCloser Function scope closer.
	 *
	 * @return false|int
	 */
	private function findLastTopLevelReturn( File $phpcsFile, array $tokens, $scopeOpener, $scopeCloser ) {
		$current = $scopeCloser - 1;

		while ( $current > $scopeOpener ) {
			$current = $phpcsFile->findPrevious( T_RETURN, $current, $scopeOpener );

			if ( false === $current ) {
				return false;
			}

			// Check if this return is at the function's top level.
			if ( $this->isAtFunctionTopLevel( $tokens, $current, $scopeOpener ) ) {
				return $current;
			}

			--$current;
		}

		return false;
	}

	/**
	 * Check if a token is at the function's top level (not nested).
	 *
	 * @param array $tokens       The token stack.
	 * @param int   $stackPtr     The token position.
	 * @param int   $scopeOpener  Function scope opener.
	 *
	 * @return bool
	 */
	private function isAtFunctionTopLevel( array $tokens, $stackPtr, $scopeOpener ) {
		$depth = 0;

		for ( $i = $scopeOpener + 1; $i < $stackPtr; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_OPEN_CURLY_BRACKET ) {
				++$depth;
			} elseif ( $tokens[ $i ]['code'] === T_CLOSE_CURLY_BRACKET ) {
				--$depth;
			}
		}

		return 0 === $depth;
	}

	/**
	 * Get the variable being returned, if it's a simple variable.
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param array $tokens      The token stack.
	 * @param int   $returnToken The return token position.
	 * @param int   $scopeCloser Function scope closer.
	 *
	 * @return false|string
	 */
	private function getReturnVariable( File $phpcsFile, array $tokens, $returnToken, $scopeCloser ) {
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $returnToken + 1, $scopeCloser, true );

		if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_VARIABLE ) {
			return false;
		}

		$variableName = $tokens[ $nextToken ]['content'];

		// Make sure the next non-whitespace is a semicolon.
		$afterVar = $phpcsFile->findNext( T_WHITESPACE, $nextToken + 1, $scopeCloser, true );

		if ( false === $afterVar || $tokens[ $afterVar ]['code'] !== T_SEMICOLON ) {
			return false;
		}

		return $variableName;
	}

	/**
	 * Find the if statement that precedes the return.
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param array $tokens      The token stack.
	 * @param int   $scopeOpener Function scope opener.
	 * @param int   $returnToken The return token position.
	 *
	 * @return false|int
	 */
	private function findPrecedingIf( File $phpcsFile, array $tokens, $scopeOpener, $returnToken ) {
		// Look backwards from the return to find an if statement at the top level.
		$current = $returnToken - 1;

		while ( $current > $scopeOpener ) {
			// Skip whitespace and comments.
			if ( in_array( $tokens[ $current ]['code'], array( T_WHITESPACE, T_COMMENT ), true ) ) {
				--$current;
				continue;
			}

			// If we hit a closing brace, find its opener.
			if ( $tokens[ $current ]['code'] === T_CLOSE_CURLY_BRACKET ) {
				// Find the owner of this brace.
				if ( isset( $tokens[ $current ]['scope_condition'] ) ) {
					$owner = $tokens[ $current ]['scope_condition'];

					if ( $tokens[ $owner ]['code'] === T_IF ) {
						return $owner;
					}
				}

				return false;
			}

			--$current;
		}

		return false;
	}

	/**
	 * Check if an if statement has an else or elseif clause.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $ifToken   The if token position.
	 *
	 * @return bool
	 */
	private function hasElseOrElseif( File $phpcsFile, array $tokens, $ifToken ) {
		if ( ! isset( $tokens[ $ifToken ]['scope_closer'] ) ) {
			return false;
		}

		$scopeCloser = $tokens[ $ifToken ]['scope_closer'];
		$next        = $phpcsFile->findNext( T_WHITESPACE, $scopeCloser + 1, null, true );

		if ( false === $next ) {
			return false;
		}

		return in_array( $tokens[ $next ]['code'], array( T_ELSE, T_ELSEIF ), true );
	}

	/**
	 * Count the number of statements inside a scope.
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $tokens     The token stack.
	 * @param int   $scopeToken The token with the scope to count.
	 *
	 * @return int
	 */
	private function countStatementsInScope( File $phpcsFile, array $tokens, $scopeToken ) {
		if ( ! isset( $tokens[ $scopeToken ]['scope_opener'] ) || ! isset( $tokens[ $scopeToken ]['scope_closer'] ) ) {
			return 0;
		}

		$scopeOpener = $tokens[ $scopeToken ]['scope_opener'];
		$scopeCloser = $tokens[ $scopeToken ]['scope_closer'];

		$count = 0;

		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_SEMICOLON ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Get the condition string from an if statement.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $ifToken   The if token position.
	 *
	 * @return string
	 */
	private function getConditionString( File $phpcsFile, array $tokens, $ifToken ) {
		if ( ! isset( $tokens[ $ifToken ]['parenthesis_opener'] ) || ! isset( $tokens[ $ifToken ]['parenthesis_closer'] ) ) {
			return '...';
		}

		$opener = $tokens[ $ifToken ]['parenthesis_opener'];
		$closer = $tokens[ $ifToken ]['parenthesis_closer'];

		$condition = '';

		for ( $i = $opener + 1; $i < $closer; $i++ ) {
			$condition .= $tokens[ $i ]['content'];
		}

		$condition = trim( $condition );

		if ( strlen( $condition ) > 50 ) {
			$condition = substr( $condition, 0, 47 ) . '...';
		}

		return $condition;
	}

	/**
	 * Suggest a negated version of the condition.
	 *
	 * @param string $condition The original condition.
	 *
	 * @return string
	 */
	private function suggestNegatedCondition( $condition ) {
		$condition = trim( $condition );

		// Simple negation patterns.
		if ( strpos( $condition, '> ' ) !== false ) {
			return str_replace( '> ', '<= ', $condition );
		}

		if ( strpos( $condition, ' > ' ) !== false ) {
			return str_replace( ' > ', ' <= ', $condition );
		}

		if ( strpos( $condition, '>=' ) !== false ) {
			return str_replace( '>=', '<', $condition );
		}

		if ( strpos( $condition, '<=' ) !== false ) {
			return str_replace( '<=', '>', $condition );
		}

		if ( strpos( $condition, '< ' ) !== false ) {
			return str_replace( '< ', '>= ', $condition );
		}

		if ( strpos( $condition, ' < ' ) !== false ) {
			return str_replace( ' < ', ' >= ', $condition );
		}

		if ( strpos( $condition, '!==' ) !== false ) {
			return str_replace( '!==', '===', $condition );
		}

		if ( strpos( $condition, '===' ) !== false ) {
			return str_replace( '===', '!==', $condition );
		}

		if ( strpos( $condition, '!=' ) !== false ) {
			return str_replace( '!=', '==', $condition );
		}

		if ( strpos( $condition, '==' ) !== false ) {
			return str_replace( '==', '!=', $condition );
		}

		if ( strpos( $condition, '! ' ) === 0 ) {
			return substr( $condition, 2 );
		}

		if ( strpos( $condition, '!' ) === 0 ) {
			return substr( $condition, 1 );
		}

		return '! ( ' . $condition . ' )';
	}

	/**
	 * Apply the fix to flip the if statement to early return.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param array  $tokens    The token stack.
	 * @param int    $ifToken   The if token position.
	 * @param string $returnVar The variable being returned.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, array $tokens, $ifToken, $returnVar ) {
		$fixer = $phpcsFile->fixer;

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

		// Get the if body content (without the braces), and dedent by one level.
		$ifBodyContent = '';

		for ( $i = $ifOpener + 1; $i < $ifCloser; $i++ ) {
			$ifBodyContent .= $tokens[ $i ]['content'];
		}

		// Remove one level of indentation from the body.
		$ifBodyContent = $this->dedentCode( $ifBodyContent );

		// Remove trailing whitespace/newlines from the body.
		$ifBodyContent = rtrim( $ifBodyContent );

		// Determine the indentation.
		$indent = $this->getIndentation( $phpcsFile, $ifToken );

		$fixer->beginChangeset();

		// Remove everything from the if token to the if's closing brace.
		for ( $i = $ifToken; $i <= $ifCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Build the new code: early return + body.
		$newCode  = 'if ( ' . $negatedCondition . ' ) {' . $phpcsFile->eolChar;
		$newCode .= $indent . "\treturn " . $returnVar . ';' . $phpcsFile->eolChar;
		$newCode .= $indent . '}' . $phpcsFile->eolChar;
		$newCode .= $phpcsFile->eolChar;
		$newCode .= $indent . ltrim( $ifBodyContent );

		// Insert the new code at the if token position.
		$fixer->addContent( $ifToken, $newCode );

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
	 * Check if a condition has && or || at the top level (not inside parentheses).
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

			// Only check at top level.
			if ( $parenDepth !== 0 ) {
				continue;
			}

			// Check for && or ||.
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
	 * Try to flip a comparison operator in a condition.
	 *
	 * @param string $condition The condition to flip.
	 *
	 * @return false|string The flipped condition, or false if not a simple comparison.
	 */
	private function flipComparisonOperator( $condition ) {
		// Map of operators to their opposites.
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

		// Check for each operator (check longer ones first).
		foreach ( $operatorMap as $op => $opposite ) {
			$pos = strpos( $condition, $op );

			if ( $pos !== false ) {
				// Make sure this is a simple comparison (no && or ||).
				if ( strpos( $condition, '&&' ) !== false || strpos( $condition, '||' ) !== false ) {
					return false;
				}

				return substr( $condition, 0, $pos ) . $opposite . substr( $condition, $pos + strlen( $op ) );
			}
		}

		return false;
	}

	/**
	 * Remove one level of indentation from code.
	 *
	 * @param string $code The code to dedent.
	 *
	 * @return string The dedented code.
	 */
	private function dedentCode( $code ) {
		$lines  = explode( "\n", $code );
		$result = array();

		foreach ( $lines as $line ) {
			// Remove one tab or 4 spaces from the beginning.
			if ( strpos( $line, "\t" ) === 0 ) {
				$line = substr( $line, 1 );
			} elseif ( strpos( $line, '    ' ) === 0 ) {
				$line = substr( $line, 4 );
			}
			$result[] = $line;
		}

		return implode( "\n", $result );
	}

	/**
	 * Get the indentation of a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The token position.
	 *
	 * @return string The indentation string.
	 */
	private function getIndentation( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the first token on this line.
		$lineStart = $stackPtr;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			--$lineStart;
		}

		// If the first token is whitespace, that's our indentation.
		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			return $tokens[ $lineStart ]['content'];
		}

		return '';
	}
}
