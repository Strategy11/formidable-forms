<?php
/**
 * Formidable_Sniffs_CodeAnalysis_FlipIfToEarlyReturnInitialValueSniff
 *
 * Detects functions where a variable is initialized, followed by a single if
 * that wraps most logic, then returns the variable. Suggests flipping to
 * return the initial value early.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects functions that can return initial value early.
 *
 * Bad:
 * function example($input) {
 *     $value = array();
 *     if ($input) {
 *         // lots of code modifying $value
 *     }
 *     return $value;
 * }
 *
 * Good:
 * function example($input) {
 *     if (! $input) {
 *         return array();
 *     }
 *     $value = array();
 *     // lots of code modifying $value
 *     return $value;
 * }
 */
class FlipIfToEarlyReturnInitialValueSniff implements Sniff {

	/**
	 * Minimum number of statements inside the if body to trigger this sniff.
	 *
	 * @var int
	 */
	public $minStatements = 5;

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

		// Find the first statement - should be a variable assignment.
		$firstStatement = $phpcsFile->findNext( T_WHITESPACE, $scopeOpener + 1, $scopeCloser, true );

		if ( false === $firstStatement || $tokens[ $firstStatement ]['code'] !== T_VARIABLE ) {
			return;
		}

		$variableName = $tokens[ $firstStatement ]['content'];

		// Check that it's an assignment.
		$assignOp = $phpcsFile->findNext( T_WHITESPACE, $firstStatement + 1, $scopeCloser, true );

		if ( false === $assignOp || $tokens[ $assignOp ]['code'] !== T_EQUAL ) {
			return;
		}

		// Get the initial value.
		$initialValue = $this->getInitialValue( $phpcsFile, $assignOp, $scopeCloser );

		if ( false === $initialValue ) {
			return;
		}

		// Find the semicolon ending the assignment.
		$assignSemicolon = $phpcsFile->findNext( T_SEMICOLON, $assignOp + 1, $scopeCloser );

		if ( false === $assignSemicolon ) {
			return;
		}

		// Find the next statement - should be an if.
		$ifToken = $phpcsFile->findNext( T_WHITESPACE, $assignSemicolon + 1, $scopeCloser, true );

		if ( false === $ifToken || $tokens[ $ifToken ]['code'] !== T_IF ) {
			return;
		}

		// Check if this if has an else or elseif - skip those.
		if ( $this->hasElseOrElseif( $phpcsFile, $tokens, $ifToken ) ) {
			return;
		}

		// Get the if's scope closer.
		if ( ! isset( $tokens[ $ifToken ]['scope_closer'] ) ) {
			return;
		}

		$ifScopeCloser = $tokens[ $ifToken ]['scope_closer'];

		// Find what comes after the if - should be return $variable.
		$afterIf = $phpcsFile->findNext( T_WHITESPACE, $ifScopeCloser + 1, $scopeCloser, true );

		if ( false === $afterIf || $tokens[ $afterIf ]['code'] !== T_RETURN ) {
			return;
		}

		$returnToken = $afterIf;

		// Check that the return statement returns the same variable.
		$returnVar = $phpcsFile->findNext( T_WHITESPACE, $returnToken + 1, $scopeCloser, true );

		if ( false === $returnVar || $tokens[ $returnVar ]['code'] !== T_VARIABLE ) {
			return;
		}

		if ( $tokens[ $returnVar ]['content'] !== $variableName ) {
			return;
		}

		// Make sure the return is followed by semicolon and nothing else.
		$afterReturnVar = $phpcsFile->findNext( T_WHITESPACE, $returnVar + 1, $scopeCloser, true );

		if ( false === $afterReturnVar || $tokens[ $afterReturnVar ]['code'] !== T_SEMICOLON ) {
			return;
		}

		// Make sure there's nothing after the return statement except the function closer.
		$afterReturn = $phpcsFile->findNext(
			array( T_WHITESPACE, T_COMMENT ),
			$afterReturnVar + 1,
			$scopeCloser,
			true
		);

		if ( false !== $afterReturn ) {
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
			'Consider flipping "if (%s)" to "if (%s) { return %s; }" for early return pattern.',
			$ifToken,
			'Found',
			array( $condition, $this->suggestNegatedCondition( $condition ), $initialValue['display'] )
		);

		if ( true === $fix ) {
			$this->applyFix(
				$phpcsFile,
				$tokens,
				$firstStatement,
				$assignSemicolon,
				$ifToken,
				$returnToken,
				$afterReturnVar,
				$initialValue['content']
			);
		}
	}

	/**
	 * Get the initial value from an assignment.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $assignOp  The assignment operator position.
	 * @param int  $scopeEnd  The scope end position.
	 *
	 * @return array|false Array with 'content' and 'display' keys, or false.
	 */
	private function getInitialValue( File $phpcsFile, $assignOp, $scopeEnd ) {
		$tokens = $phpcsFile->getTokens();

		$valueStart = $phpcsFile->findNext( T_WHITESPACE, $assignOp + 1, $scopeEnd, true );

		if ( false === $valueStart ) {
			return false;
		}

		// Handle array() or [].
		if ( $tokens[ $valueStart ]['code'] === T_ARRAY ) {
			$openParen = $phpcsFile->findNext( T_WHITESPACE, $valueStart + 1, $scopeEnd, true );

			if ( false !== $openParen && $tokens[ $openParen ]['code'] === T_OPEN_PARENTHESIS ) {
				if ( isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
					$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
					$nextAfter  = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, $scopeEnd, true );

					if ( false !== $nextAfter && $tokens[ $nextAfter ]['code'] === T_SEMICOLON ) {
						// Check if it's empty array().
						$inside = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

						if ( false === $inside || $inside === $closeParen ) {
							return array(
								'content' => 'array()',
								'display' => 'array()',
							);
						}
					}
				}
			}

			return false;
		}

		if ( $tokens[ $valueStart ]['code'] === T_OPEN_SHORT_ARRAY ) {
			if ( isset( $tokens[ $valueStart ]['bracket_closer'] ) ) {
				$closeBracket = $tokens[ $valueStart ]['bracket_closer'];
				$nextAfter    = $phpcsFile->findNext( T_WHITESPACE, $closeBracket + 1, $scopeEnd, true );

				if ( false !== $nextAfter && $tokens[ $nextAfter ]['code'] === T_SEMICOLON ) {
					// Check if it's empty [].
					$inside = $phpcsFile->findNext( T_WHITESPACE, $valueStart + 1, $closeBracket, true );

					if ( false === $inside || $inside === $closeBracket ) {
						return array(
							'content' => '[]',
							'display' => 'array()',
						);
					}
				}
			}

			return false;
		}

		// Handle simple literals: strings, numbers, null, true, false.
		$simpleTypes = array(
			T_CONSTANT_ENCAPSED_STRING,
			T_LNUMBER,
			T_DNUMBER,
			T_NULL,
			T_TRUE,
			T_FALSE,
		);

		if ( in_array( $tokens[ $valueStart ]['code'], $simpleTypes, true ) ) {
			$nextAfter = $phpcsFile->findNext( T_WHITESPACE, $valueStart + 1, $scopeEnd, true );

			if ( false !== $nextAfter && $tokens[ $nextAfter ]['code'] === T_SEMICOLON ) {
				$content = $tokens[ $valueStart ]['content'];

				return array(
					'content' => $content,
					'display' => $content,
				);
			}
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

		return '! ' . $condition;
	}

	/**
	 * Apply the fix to flip the if statement to early return.
	 *
	 * @param File   $phpcsFile        The file being scanned.
	 * @param array  $tokens           The token stack.
	 * @param int    $varToken         The variable token position.
	 * @param int    $assignSemicolon  The semicolon ending the assignment.
	 * @param int    $ifToken          The if token position.
	 * @param int    $returnToken      The return token position.
	 * @param int    $returnSemicolon  The semicolon ending the return.
	 * @param string $initialValue     The initial value to return early.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, array $tokens, $varToken, $assignSemicolon, $ifToken, $returnToken, $returnSemicolon, $initialValue ) {
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

		// Get the variable assignment line.
		$assignmentContent = '';

		for ( $i = $varToken; $i <= $assignSemicolon; $i++ ) {
			$assignmentContent .= $tokens[ $i ]['content'];
		}
		$assignmentContent = trim( $assignmentContent );

		$fixer->beginChangeset();

		// Remove the original variable assignment (from var to semicolon).
		for ( $i = $varToken; $i <= $assignSemicolon; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Remove whitespace between assignment and if.
		for ( $i = $assignSemicolon + 1; $i < $ifToken; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				$fixer->replaceToken( $i, '' );
			}
		}

		// Remove the if statement (from if to closing brace).
		for ( $i = $ifToken; $i <= $ifCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Remove whitespace between if and return.
		for ( $i = $ifCloser + 1; $i < $returnToken; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				$fixer->replaceToken( $i, '' );
			}
		}

		// Remove the return statement.
		for ( $i = $returnToken; $i <= $returnSemicolon; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Build the new code.
		$newCode  = 'if ( ' . $negatedCondition . ' ) {' . $phpcsFile->eolChar;
		$newCode .= $indent . "\treturn " . $initialValue . ';' . $phpcsFile->eolChar;
		$newCode .= $indent . '}' . $phpcsFile->eolChar;
		$newCode .= $phpcsFile->eolChar;
		$newCode .= $indent . $assignmentContent . $phpcsFile->eolChar;
		$newCode .= $phpcsFile->eolChar;
		$newCode .= $indent . ltrim( $ifBodyContent ) . $phpcsFile->eolChar;
		$newCode .= $phpcsFile->eolChar;
		$newCode .= $indent . 'return ' . $tokens[ $returnToken + 2 ]['content'] . ';';

		// Insert the new code at the variable token position.
		$fixer->addContent( $varToken, $newCode );

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
		// Map of operators to their opposites (check longer ones first).
		$operatorMap = array(
			'!==' => '===',
			'===' => '!==',
			'!='  => '==',
			'=='  => '!=',
			'>='  => '<',
			'<='  => '>',
		);

		// Make sure this is a simple comparison (no && or ||).
		if ( strpos( $condition, '&&' ) !== false || strpos( $condition, '||' ) !== false ) {
			return false;
		}

		// Check for each operator (check longer ones first).
		foreach ( $operatorMap as $op => $opposite ) {
			$pos = strpos( $condition, $op );

			if ( false !== $pos ) {
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
