<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantEmptyOnAssignedVariableSniff
 *
 * Detects redundant empty() calls on variables that were unconditionally assigned
 * in the same function scope. Since the variable is guaranteed to be set,
 * empty() can be simplified to a falsy check.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant empty() calls on variables that were unconditionally assigned.
 *
 * Bad:
 * $actions = get_actions();
 * if ( empty( $actions ) ) { ... }
 *
 * Good:
 * $actions = get_actions();
 * if ( ! $actions ) { ... }
 *
 * OK (not flagged - conditional assignment):
 * if ( $condition ) {
 *     $success_url = get_url();
 * }
 * if ( empty( $success_url ) ) { ... }
 */
class RedundantEmptyOnAssignedVariableSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_EMPTY );
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

		// Only process if empty() is directly inside an if/elseif condition.
		if ( ! $this->isInIfCondition( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Find the opening parenthesis after empty.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the variable inside empty().
		$variablePtr = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, null, true );

		if ( false === $variablePtr || $tokens[ $variablePtr ]['code'] !== T_VARIABLE ) {
			return;
		}

		$variableName = $tokens[ $variablePtr ]['content'];

		// Check if there's anything else inside the empty() call (like array access).
		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$nextToken  = $phpcsFile->findNext( T_WHITESPACE, $variablePtr + 1, $closeParen, true );

		if ( false !== $nextToken ) {
			// There's something after the variable (like array access $var['key']).
			// Skip this case as it's more complex.
			return;
		}

		// Find the containing function.
		$functionToken = $this->findContainingFunction( $phpcsFile, $stackPtr );

		if ( false === $functionToken ) {
			return;
		}

		// Check if the variable was unconditionally assigned earlier in this function.
		if ( ! $this->wasVariableUnconditionallyAssigned( $phpcsFile, $functionToken, $stackPtr, $variableName ) ) {
			return;
		}

		// Check if there's a boolean NOT before empty.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		$isNegated = ( false !== $prevToken && $tokens[ $prevToken ]['code'] === T_BOOLEAN_NOT );

		// Determine the suggested replacement.
		if ( $isNegated ) {
			$suggestion = $variableName;
			$message    = 'Redundant empty() on assigned variable %s. Use "%s" instead of "! empty( %s )"';
		} else {
			$suggestion = '! ' . $variableName;
			$message    = 'Redundant empty() on assigned variable %s. Use "%s" instead of "empty( %s )"';
		}

		$fix = $phpcsFile->addFixableError(
			$message,
			$stackPtr,
			'Found',
			array( $variableName, $suggestion, $variableName )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			if ( $isNegated ) {
				// Remove the "!" before empty.
				$phpcsFile->fixer->replaceToken( $prevToken, '' );

				// Remove any whitespace between "!" and "empty".
				for ( $i = $prevToken + 1; $i < $stackPtr; $i++ ) {
					if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
						$phpcsFile->fixer->replaceToken( $i, '' );
					}
				}
			}

			// Replace empty( $var ) with $var or ! $var.
			$phpcsFile->fixer->replaceToken( $stackPtr, '' );

			// Remove whitespace after empty.
			for ( $i = $stackPtr + 1; $i < $openParen; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}

			// Replace opening paren.
			$phpcsFile->fixer->replaceToken( $openParen, '' );

			// Remove whitespace after opening paren.
			$nextAfterOpen = $openParen + 1;

			while ( $nextAfterOpen < $variablePtr && $tokens[ $nextAfterOpen ]['code'] === T_WHITESPACE ) {
				$phpcsFile->fixer->replaceToken( $nextAfterOpen, '' );
				++$nextAfterOpen;
			}

			// Keep the variable, but add "! " prefix if not negated.
			if ( ! $isNegated ) {
				$phpcsFile->fixer->addContentBefore( $variablePtr, '! ' );
			}

			// Remove whitespace before closing paren.
			$prevBeforeClose = $closeParen - 1;

			while ( $prevBeforeClose > $variablePtr && $tokens[ $prevBeforeClose ]['code'] === T_WHITESPACE ) {
				$phpcsFile->fixer->replaceToken( $prevBeforeClose, '' );
				--$prevBeforeClose;
			}

			// Replace closing paren.
			$phpcsFile->fixer->replaceToken( $closeParen, '' );

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Check if the empty() call is directly inside an if/elseif condition.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty token.
	 *
	 * @return bool True if inside an if/elseif condition, false otherwise.
	 */
	private function isInIfCondition( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the opening parenthesis that contains this empty() call.
		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			// Skip whitespace and the "!" operator.
			if ( $code === T_WHITESPACE || $code === T_BOOLEAN_NOT ) {
				continue;
			}

			// If we hit an open parenthesis, check if it belongs to if/elseif.
			if ( $code === T_OPEN_PARENTHESIS ) {
				$beforeParen = $phpcsFile->findPrevious( T_WHITESPACE, $i - 1, null, true );

				if ( false !== $beforeParen ) {
					$beforeCode = $tokens[ $beforeParen ]['code'];

					if ( $beforeCode === T_IF || $beforeCode === T_ELSEIF ) {
						return true;
					}
				}

				return false;
			}

			// If we hit something else, stop.
			if ( $code !== T_OPEN_PARENTHESIS ) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Find the function or method that contains the given token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return false|int The position of the function token, or false if not found.
	 */
	private function findContainingFunction( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_FUNCTION || $tokens[ $i ]['code'] === T_CLOSURE ) {
				if ( isset( $tokens[ $i ]['scope_opener'], $tokens[ $i ]['scope_closer'] ) ) {
					if ( $stackPtr > $tokens[ $i ]['scope_opener'] && $stackPtr < $tokens[ $i ]['scope_closer'] ) {
						return $i;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if the variable was unconditionally assigned earlier in the function.
	 *
	 * Only returns true if the variable is guaranteed to be set - i.e., it was
	 * assigned at the same scope level as the if statement (not inside a nested block).
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param int    $functionToken The position of the function token.
	 * @param int    $emptyPtr      The position of the empty() call.
	 * @param string $variableName  The variable name to check.
	 *
	 * @return bool True if the variable was unconditionally assigned, false otherwise.
	 */
	private function wasVariableUnconditionallyAssigned( File $phpcsFile, $functionToken, $emptyPtr, $variableName ) {
		$tokens      = $phpcsFile->getTokens();
		$scopeOpener = $tokens[ $functionToken ]['scope_opener'];

		// Find the if/elseif statement that contains the empty() call.
		$ifToken = $this->findContainingIf( $phpcsFile, $emptyPtr );

		if ( false === $ifToken ) {
			return false;
		}

		// The if statement's level is what we compare against.
		$ifLevel = $tokens[ $ifToken ]['level'];

		// Search from the function start to the if statement (not the empty call).
		for ( $i = $scopeOpener + 1; $i < $ifToken; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE ) {
				continue;
			}

			if ( $tokens[ $i ]['content'] !== $variableName ) {
				continue;
			}

			// Check if this variable is being assigned (has = after it).
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_EQUAL ) {
				continue;
			}

			// Check if the assignment is at the same scope level as the if statement.
			// This ensures the variable was assigned unconditionally before the if.
			$assignmentLevel = $tokens[ $i ]['level'];

			if ( $assignmentLevel === $ifLevel ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Find the if/elseif statement that contains the given token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return false|int The position of the if/elseif token, or false if not found.
	 */
	private function findContainingIf( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_IF || $code === T_ELSEIF ) {
				return $i;
			}

			// Stop if we hit a function or class boundary.
			if ( $code === T_FUNCTION || $code === T_CLOSURE || $code === T_CLASS ) {
				return false;
			}
		}

		return false;
	}
}
