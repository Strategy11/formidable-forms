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

		// Scan through the function looking for variable assignments followed by early returns.
		$current = $functionOpener + 1;

		while ( $current < $functionCloser ) {
			// Find the next variable at the function's top level.
			$varToken = $phpcsFile->findNext( T_VARIABLE, $current, $functionCloser );

			if ( false === $varToken ) {
				break;
			}

			// Make sure this variable is at the function's top level (not nested).
			if ( ! $this->isAtFunctionTopLevel( $tokens, $varToken, $functionOpener, $functionCloser ) ) {
				$current = $varToken + 1;
				continue;
			}

			// Check if this is an assignment (not just a variable reference).
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $varToken + 1, $functionCloser, true );

			if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_EQUAL ) {
				$current = $varToken + 1;
				continue;
			}

			$variableName = $tokens[ $varToken ]['content'];

			// Check if this variable is a function parameter.
			if ( $this->isVariableAParameter( $phpcsFile, $tokens, $stackPtr, $variableName ) ) {
				$current = $varToken + 1;
				continue;
			}

			// Find the semicolon ending this assignment.
			// If the assignment is a closure, we need to find the semicolon after the closure, not inside it.
			$assignmentEnd = $this->findAssignmentEnd( $phpcsFile, $tokens, $varToken, $functionCloser );

			if ( false === $assignmentEnd ) {
				$current = $varToken + 1;
				continue;
			}

			// Check if the assignment involves apply_filters or do_action - skip those for safety.
			if ( $this->assignmentInvolvesFilter( $phpcsFile, $tokens, $varToken, $assignmentEnd ) ) {
				$current = $varToken + 1;
				continue;
			}

			// Find the next statement after the assignment.
			$nextStatement = $phpcsFile->findNext( T_WHITESPACE, $assignmentEnd + 1, $functionCloser, true );

			if ( false === $nextStatement ) {
				break;
			}

			// Check if the next statement is an if with an early return.
			$earlyReturnIf = $this->findEarlyReturnIf( $phpcsFile, $tokens, $nextStatement, $functionCloser, $variableName );

			if ( false === $earlyReturnIf ) {
				$current = $varToken + 1;
				continue;
			}

			// The variable is not used in the early return - it should be moved below.
			$fix = $phpcsFile->addFixableError(
				'Variable %s should be declared after the early return condition, not before.',
				$varToken,
				'VariableBeforeEarlyReturn',
				array( $variableName )
			);

			if ( true === $fix ) {
				$this->applyFix( $phpcsFile, $tokens, $varToken, $assignmentEnd, $earlyReturnIf );
			}

			// Move past this variable to continue scanning.
			$current = $assignmentEnd + 1;
		}
	}

	/**
	 * Find an early return if statement that doesn't use the variable.
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param array  $tokens          The token stack.
	 * @param int    $startToken      Where to start looking.
	 * @param int    $functionCloser  The function's closing brace.
	 * @param string $variableName    The variable name to check.
	 *
	 * @return false|int The if closer position, or false if not found.
	 */
	private function findEarlyReturnIf( File $phpcsFile, array $tokens, $startToken, $functionCloser, $variableName ) {
		// Check if this is an if statement.
		if ( $tokens[ $startToken ]['code'] !== T_IF ) {
			return false;
		}

		// Make sure the if has a scope.
		if ( ! isset( $tokens[ $startToken ]['scope_opener'] ) || ! isset( $tokens[ $startToken ]['scope_closer'] ) ) {
			return false;
		}

		$ifOpener = $tokens[ $startToken ]['scope_opener'];
		$ifCloser = $tokens[ $startToken ]['scope_closer'];

		// Check if the if body contains only a return statement.
		$ifFirstStatement = $phpcsFile->findNext( T_WHITESPACE, $ifOpener + 1, $ifCloser, true );

		if ( false === $ifFirstStatement || $tokens[ $ifFirstStatement ]['code'] !== T_RETURN ) {
			return false;
		}

		// Check if there's anything else in the if body after the return.
		$returnEnd = $phpcsFile->findNext( T_SEMICOLON, $ifFirstStatement + 1, $ifCloser );

		if ( false === $returnEnd ) {
			return false;
		}

		$afterReturn = $phpcsFile->findNext( T_WHITESPACE, $returnEnd + 1, $ifCloser, true );

		if ( false !== $afterReturn ) {
			// There's more than just a return in the if body.
			return false;
		}

		// Check if the if has an else/elseif - skip those.
		$afterIfClose = $phpcsFile->findNext( T_WHITESPACE, $ifCloser + 1, $functionCloser, true );

		if ( false !== $afterIfClose && in_array( $tokens[ $afterIfClose ]['code'], array( T_ELSE, T_ELSEIF ), true ) ) {
			return false;
		}

		// Check if the variable is used in the if condition or the return statement.
		$variableUsedInCondition = $this->isVariableUsedInRange(
			$phpcsFile,
			$tokens,
			$startToken,
			$ifOpener,
			$variableName
		);

		$variableUsedInReturn = $this->isVariableUsedInRange(
			$phpcsFile,
			$tokens,
			$ifFirstStatement,
			$returnEnd,
			$variableName
		);

		if ( $variableUsedInCondition || $variableUsedInReturn ) {
			// The variable is used in this early return check.
			return false;
		}

		// This is a valid early return that doesn't use the variable.
		return $ifCloser;
	}

	/**
	 * Check if a token is at the function's top level (not nested in another scope).
	 *
	 * @param array $tokens          The token stack.
	 * @param int   $stackPtr        The token position.
	 * @param int   $functionOpener  The function's opening brace.
	 * @param int   $functionCloser  The function's closing brace.
	 *
	 * @return bool
	 */
	private function isAtFunctionTopLevel( array $tokens, $stackPtr, $functionOpener, $functionCloser ) {
		$depth = 0;

		for ( $i = $functionOpener + 1; $i < $stackPtr; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_OPEN_CURLY_BRACKET ) {
				++$depth;
			} elseif ( $tokens[ $i ]['code'] === T_CLOSE_CURLY_BRACKET ) {
				--$depth;
			}
		}

		return 0 === $depth;
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

		// Get the variable declaration content.
		$varDeclaration = '';

		for ( $i = $varStart; $i <= $assignmentEnd; $i++ ) {
			$varDeclaration .= $tokens[ $i ]['content'];
		}

		// Find the start of the line for the variable (to get indentation).
		$lineStart = $varStart;

		for ( $i = $varStart - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $tokens[ $varStart ]['line'] ) {
				break;
			}
			$lineStart = $i;
		}

		// Get the actual indentation from the whitespace token at line start.
		$indent = '';

		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			$indent = $tokens[ $lineStart ]['content'];
		}

		// Remove the variable declaration line (from line start to semicolon).
		for ( $i = $lineStart; $i <= $assignmentEnd; $i++ ) {
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

		// Add the variable declaration after the if closing brace.
		// Find the whitespace after the if closer.
		$afterIfCloser = $ifCloser + 1;

		if ( isset( $tokens[ $afterIfCloser ] ) && $tokens[ $afterIfCloser ]['code'] === T_WHITESPACE ) {
			$existingWs = $tokens[ $afterIfCloser ]['content'];
			// Add the variable declaration with proper formatting.
			// Keep the existing whitespace structure but insert the variable declaration.
			$newContent = "\n\n" . $indent . $varDeclaration . $existingWs;
			$phpcsFile->fixer->replaceToken( $afterIfCloser, $newContent );
		} else {
			// No whitespace after if closer, add it.
			$phpcsFile->fixer->addContent( $ifCloser, "\n\n" . $indent . $varDeclaration );
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
	 * Find the semicolon ending an assignment, skipping over closure bodies.
	 *
	 * @param File  $phpcsFile       The file being scanned.
	 * @param array $tokens          The token stack.
	 * @param int   $varToken        The variable token position.
	 * @param int   $functionCloser  The function's closing brace.
	 *
	 * @return false|int The semicolon position, or false if not found.
	 */
	private function findAssignmentEnd( File $phpcsFile, array $tokens, $varToken, $functionCloser ) {
		$current = $varToken + 1;

		while ( $current < $functionCloser ) {
			// If we hit a closure or anonymous function, skip to after its closing brace.
			if ( $tokens[ $current ]['code'] === T_CLOSURE || $tokens[ $current ]['code'] === T_FN ) {
				if ( isset( $tokens[ $current ]['scope_closer'] ) ) {
					$current = $tokens[ $current ]['scope_closer'] + 1;
					continue;
				}
			}

			// If we hit a semicolon at this level, we found the end.
			if ( $tokens[ $current ]['code'] === T_SEMICOLON ) {
				return $current;
			}

			++$current;
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

	/**
	 * Check if an assignment involves apply_filters, do_action, or similar filter/action calls.
	 *
	 * @param File  $phpcsFile     The file being scanned.
	 * @param array $tokens        The token stack.
	 * @param int   $varToken      The variable token position.
	 * @param int   $assignmentEnd The semicolon ending the assignment.
	 *
	 * @return bool
	 */
	private function assignmentInvolvesFilter( File $phpcsFile, array $tokens, $varToken, $assignmentEnd ) {
		$filterFunctions = array(
			'apply_filters',
			'apply_filters_ref_array',
			'apply_filters_deprecated',
			'do_action',
			'do_action_ref_array',
			'do_action_deprecated',
		);

		for ( $i = $varToken; $i <= $assignmentEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_STRING && in_array( $tokens[ $i ]['content'], $filterFunctions, true ) ) {
				return true;
			}
		}

		return false;
	}
}
