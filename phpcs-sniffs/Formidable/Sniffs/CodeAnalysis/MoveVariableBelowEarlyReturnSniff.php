<?php
/**
 * Formidable_Sniffs_CodeAnalysis_MoveVariableBelowEarlyReturnSniff
 *
 * Detects variable declarations that should be moved below early return conditions.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects variable declarations that should be moved below early return conditions.
 *
 * Bad:
 * function example($form) {
 *     $style = 1;
 *
 *     if ( ! $form ) {
 *         return;
 *     }
 *
 *     return $style;
 * }
 *
 * Good:
 * function example($form) {
 *     if ( ! $form ) {
 *         return;
 *     }
 *
 *     $style = 1;
 *
 *     return $style;
 * }
 *
 * Also handles variables declared before an early return that are only used after it:
 *
 * Bad:
 * function example($id, $form_id) {
 *     $entry_ids = get_entries($form_id);
 *     $total = count($entry_ids);
 *     $position = array_search($id, $entry_ids);
 *
 *     if ($position === false) {
 *         return;
 *     }
 *
 *     $prev = $position > 0 ? $entry_ids[$position - 1] : null;
 *     $next = $position < $total - 1 ? $entry_ids[$position + 1] : null;
 * }
 *
 * Good:
 * function example($id, $form_id) {
 *     $entry_ids = get_entries($form_id);
 *     $position = array_search($id, $entry_ids);
 *
 *     if ($position === false) {
 *         return;
 *     }
 *
 *     $total = count($entry_ids);
 *     $prev = $position > 0 ? $entry_ids[$position - 1] : null;
 *     $next = $position < $total - 1 ? $entry_ids[$position + 1] : null;
 * }
 */
class MoveVariableBelowEarlyReturnSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FUNCTION, T_CLOSURE );
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

		// Make sure this function has a scope.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$functionOpener = $tokens[ $stackPtr ]['scope_opener'];
		$functionCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find all early return if statements in the function.
		$earlyReturns = $this->findEarlyReturns( $phpcsFile, $functionOpener, $functionCloser );

		if ( empty( $earlyReturns ) ) {
			return;
		}

		// For each early return, check if there are variables before it that should be moved after.
		foreach ( $earlyReturns as $earlyReturn ) {
			$this->checkVariablesBeforeEarlyReturn( $phpcsFile, $stackPtr, $functionOpener, $functionCloser, $earlyReturn );
		}
	}

	/**
	 * Find all early return if statements in a function.
	 *
	 * @param File $phpcsFile      The file being scanned.
	 * @param int  $functionOpener The function scope opener.
	 * @param int  $functionCloser The function scope closer.
	 *
	 * @return array Array of early return info: ['if' => token, 'ifOpener' => token, 'ifCloser' => token].
	 */
	private function findEarlyReturns( File $phpcsFile, $functionOpener, $functionCloser ) {
		$tokens       = $phpcsFile->getTokens();
		$earlyReturns = array();

		$current = $functionOpener + 1;

		while ( $current < $functionCloser ) {
			$current = $phpcsFile->findNext( T_IF, $current, $functionCloser );

			if ( false === $current ) {
				break;
			}

			// Make sure the if has a scope.
			if ( ! isset( $tokens[ $current ]['scope_opener'] ) || ! isset( $tokens[ $current ]['scope_closer'] ) ) {
				++$current;
				continue;
			}

			$ifOpener = $tokens[ $current ]['scope_opener'];
			$ifCloser = $tokens[ $current ]['scope_closer'];

			// Check if the if body contains only a return statement.
			$ifFirstStatement = $phpcsFile->findNext( T_WHITESPACE, $ifOpener + 1, $ifCloser, true );

			if ( false === $ifFirstStatement || $tokens[ $ifFirstStatement ]['code'] !== T_RETURN ) {
				++$current;
				continue;
			}

			// Check if there's anything else in the if body after the return.
			$returnEnd = $phpcsFile->findNext( T_SEMICOLON, $ifFirstStatement + 1, $ifCloser );

			if ( false === $returnEnd ) {
				++$current;
				continue;
			}

			$afterReturn = $phpcsFile->findNext( T_WHITESPACE, $returnEnd + 1, $ifCloser, true );

			if ( false !== $afterReturn ) {
				// There's more than just a return in the if body.
				++$current;
				continue;
			}

			// Check if the if has an else/elseif - skip those.
			$afterIfClose = $phpcsFile->findNext( T_WHITESPACE, $ifCloser + 1, $functionCloser, true );

			if ( false !== $afterIfClose && in_array( $tokens[ $afterIfClose ]['code'], array( T_ELSE, T_ELSEIF ), true ) ) {
				++$current;
				continue;
			}

			$earlyReturns[] = array(
				'if'        => $current,
				'ifOpener'  => $ifOpener,
				'ifCloser'  => $ifCloser,
				'returnEnd' => $returnEnd,
			);

			$current = $ifCloser + 1;
		}

		return $earlyReturns;
	}

	/**
	 * Check variables declared before an early return to see if they should be moved after.
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param int   $stackPtr       The function token position.
	 * @param int   $functionOpener The function scope opener.
	 * @param int   $functionCloser The function scope closer.
	 * @param array $earlyReturn    Info about the early return.
	 *
	 * @return void
	 */
	private function checkVariablesBeforeEarlyReturn( File $phpcsFile, $stackPtr, $functionOpener, $functionCloser, array $earlyReturn ) {
		$tokens = $phpcsFile->getTokens();

		$ifToken   = $earlyReturn['if'];
		$ifOpener  = $earlyReturn['ifOpener'];
		$ifCloser  = $earlyReturn['ifCloser'];
		$returnEnd = $earlyReturn['returnEnd'];

		// Find all variable assignments before this early return.
		$current          = $functionOpener + 1;
		$seenVariables    = array();

		while ( $current < $ifToken ) {
			$varToken = $phpcsFile->findNext( T_VARIABLE, $current, $ifToken );

			if ( false === $varToken ) {
				break;
			}

			// Skip static property assignments (self::$var, static::$var, ClassName::$var).
			$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $varToken - 1, null, true );

			if ( false !== $prevToken && $tokens[ $prevToken ]['code'] === T_DOUBLE_COLON ) {
				$current = $varToken + 1;
				continue;
			}

			// Check if this is an assignment (next non-whitespace is =).
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $varToken + 1, null, true );

			if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_EQUAL ) {
				$current = $varToken + 1;
				continue;
			}

			$variableName = $tokens[ $varToken ]['content'];

			// Skip if we've already seen this variable (only consider first declaration).
			if ( isset( $seenVariables[ $variableName ] ) ) {
				$current = $varToken + 1;
				continue;
			}
			$seenVariables[ $variableName ] = true;

			// Skip if this is a function parameter.
			if ( $this->isVariableAParameter( $phpcsFile, $tokens, $stackPtr, $variableName ) ) {
				$current = $varToken + 1;
				continue;
			}

			// Find the semicolon ending this assignment.
			$assignmentEnd = $phpcsFile->findNext( T_SEMICOLON, $varToken + 1, $ifToken );

			if ( false === $assignmentEnd ) {
				$current = $varToken + 1;
				continue;
			}

			// Check if the variable is used in the if condition or the return statement.
			$variableUsedInCondition = $this->isVariableUsedInRange(
				$phpcsFile,
				$tokens,
				$ifToken,
				$ifOpener,
				$variableName
			);

			$variableUsedInReturn = $this->isVariableUsedInRange(
				$phpcsFile,
				$tokens,
				$ifOpener + 1,
				$returnEnd,
				$variableName
			);

			// Check if the variable is used between its declaration and the early return.
			$variableUsedBefore = $this->isVariableUsedInRange(
				$phpcsFile,
				$tokens,
				$assignmentEnd + 1,
				$ifToken - 1,
				$variableName
			);

			// Check if the variable is reassigned anywhere between its declaration and the early return.
			// This handles cases like: $is_setup = false; if (!$x) { $is_setup = true; if (!$y) { return; } }
			$variableReassignedBefore = $this->isVariableAssignedInRange(
				$phpcsFile,
				$tokens,
				$assignmentEnd + 1,
				$ifCloser - 1,
				$variableName
			);

			// Check if the variable is used after the early return.
			$variableUsedAfter = $this->isVariableUsedInRange(
				$phpcsFile,
				$tokens,
				$ifCloser + 1,
				$functionCloser - 1,
				$variableName
			);

			if ( $variableUsedInCondition || $variableUsedInReturn || $variableUsedBefore || $variableReassignedBefore ) {
				// The variable is used before or in the early return check, so it's fine where it is.
				$current = $assignmentEnd + 1;
				continue;
			}

			if ( ! $variableUsedAfter ) {
				// Variable is not used at all after declaration - skip (might be a different issue).
				$current = $assignmentEnd + 1;
				continue;
			}

			// The variable is only used after the early return - it should be moved below.
			$fix = $phpcsFile->addFixableError(
				'Variable %s should be declared after the early return condition, not before.',
				$varToken,
				'VariableBeforeEarlyReturn',
				array( $variableName )
			);

			if ( true === $fix ) {
				$this->applyFix( $phpcsFile, $tokens, $varToken, $assignmentEnd, $ifCloser );
			}

			$current = $assignmentEnd + 1;
		}
	}

	/**
	 * Apply the fix to move the variable declaration below the early return.
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param array $tokens         The token stack.
	 * @param int   $varStart       Start of the variable declaration.
	 * @param int   $assignmentEnd  End of the assignment (semicolon).
	 * @param int   $ifCloser       The closing brace of the if statement.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, array $tokens, $varStart, $assignmentEnd, $ifCloser ) {
		$phpcsFile->fixer->beginChangeset();

		// Check if there's a comment on the line before the variable.
		$varLine      = $tokens[ $varStart ]['line'];
		$commentStart = null;
		$commentEnd   = null;

		// Look backwards for a comment on the previous line.
		for ( $i = $varStart - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $varLine - 1 ) {
				break;
			}

			if ( $tokens[ $i ]['line'] === $varLine - 1 && $tokens[ $i ]['code'] === T_COMMENT ) {
				$commentEnd = $i;

				// Find the start of this comment line.
				for ( $j = $i; $j >= 0; $j-- ) {
					if ( $tokens[ $j ]['line'] < $varLine - 1 ) {
						break;
					}
					$commentStart = $j;
				}
				break;
			}
		}

		// Get the indentation from the whitespace before the variable.
		$indent = '';

		for ( $i = $varStart - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $varLine ) {
				break;
			}

			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				$indent = $tokens[ $i ]['content'];
			}
		}

		// Build the content to move.
		$contentToMove = '';

		// Include the comment if found.
		if ( null !== $commentStart ) {
			// Get comment indentation.
			$commentIndent = '';

			for ( $i = $commentStart; $i <= $commentEnd; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE && $tokens[ $i ]['line'] === $varLine - 1 ) {
					$commentIndent = $tokens[ $i ]['content'];
				} elseif ( $tokens[ $i ]['code'] === T_COMMENT ) {
					$contentToMove .= $commentIndent . rtrim( $tokens[ $i ]['content'] ) . "\n";
				}
			}
		}

		// Add the variable declaration.
		$contentToMove .= $indent;

		for ( $i = $varStart; $i <= $assignmentEnd; $i++ ) {
			$contentToMove .= $tokens[ $i ]['content'];
		}

		// Determine what to remove - start from comment if present, otherwise from line start.
		$removeStart = $varStart;

		if ( null !== $commentStart ) {
			$removeStart = $commentStart;
		} else {
			// Find the start of the variable line.
			for ( $i = $varStart - 1; $i >= 0; $i-- ) {
				if ( $tokens[ $i ]['line'] < $varLine ) {
					break;
				}
				$removeStart = $i;
			}
		}

		// Remove the content (comment + variable declaration).
		for ( $i = $removeStart; $i <= $assignmentEnd; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Also remove the newline after the semicolon if it exists.
		if ( isset( $tokens[ $assignmentEnd + 1 ] ) && $tokens[ $assignmentEnd + 1 ]['code'] === T_WHITESPACE ) {
			$wsContent = $tokens[ $assignmentEnd + 1 ]['content'];

			// Remove one newline from the whitespace.
			if ( strpos( $wsContent, "\n" ) === 0 ) {
				$phpcsFile->fixer->replaceToken( $assignmentEnd + 1, substr( $wsContent, 1 ) );
			}
		}

		// Add the content after the if closing brace.
		$afterIfCloser = $ifCloser + 1;

		if ( isset( $tokens[ $afterIfCloser ] ) && $tokens[ $afterIfCloser ]['code'] === T_WHITESPACE ) {
			$existingWs = $tokens[ $afterIfCloser ]['content'];
			$newContent = "\n\n" . $contentToMove . $existingWs;
			$phpcsFile->fixer->replaceToken( $afterIfCloser, $newContent );
		} else {
			$phpcsFile->fixer->addContent( $ifCloser, "\n\n" . $contentToMove );
		}

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Check if a variable is used within a range of tokens.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $tokens       The token stack.
	 * @param int    $start        Start position.
	 * @param int    $end          End position.
	 * @param string $variableName The variable name to look for.
	 *
	 * @return bool
	 */
	private function isVariableUsedInRange( File $phpcsFile, array $tokens, $start, $end, $variableName ) {
		for ( $i = $start; $i <= $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === $variableName ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a variable is assigned within a range of tokens.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $tokens       The token stack.
	 * @param int    $start        Start position.
	 * @param int    $end          End position.
	 * @param string $variableName The variable name to look for.
	 *
	 * @return bool
	 */
	private function isVariableAssignedInRange( File $phpcsFile, array $tokens, $start, $end, $variableName ) {
		for ( $i = $start; $i <= $end; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE || $tokens[ $i ]['content'] !== $variableName ) {
				continue;
			}

			// Check if the next non-whitespace token is an assignment operator.
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false !== $nextToken && $tokens[ $nextToken ]['code'] === T_EQUAL ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a variable is a function parameter.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $tokens       The token stack.
	 * @param int    $functionPtr  The function token position.
	 * @param string $variableName The variable name to check.
	 *
	 * @return bool
	 */
	private function isVariableAParameter( File $phpcsFile, array $tokens, $functionPtr, $variableName ) {
		// Find the opening parenthesis of the function.
		$openParen = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $functionPtr + 1 );

		if ( false === $openParen || ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Look for the variable in the parameter list.
		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === $variableName ) {
				return true;
			}
		}

		return false;
	}
}
