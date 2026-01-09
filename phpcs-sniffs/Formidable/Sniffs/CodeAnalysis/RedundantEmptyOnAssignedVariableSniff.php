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

		// Find the statement context (if condition, return statement, etc.).
		$context = $this->getStatementContext( $phpcsFile, $stackPtr );

		if ( false === $context ) {
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
		if ( ! $this->wasVariableUnconditionallyAssigned( $phpcsFile, $functionToken, $context, $variableName ) ) {
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
	 * Get the statement context for the empty() call.
	 *
	 * Returns the token position of the statement start (if, elseif, return, etc.)
	 * if the empty() is in a valid boolean context, or false otherwise.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty token.
	 *
	 * @return false|int The statement token position, or false if not in a valid context.
	 */
	private function getStatementContext( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Check if in an if/elseif condition.
		$ifContext = $this->isInIfCondition( $phpcsFile, $stackPtr );

		if ( false !== $ifContext ) {
			return $ifContext;
		}

		// Check if in a boolean expression (with && or ||).
		return $this->isInBooleanExpression( $phpcsFile, $stackPtr );
	}

	/**
	 * Check if the empty() call is directly inside an if/elseif condition.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty token.
	 *
	 * @return false|int The if/elseif token position, or false if not in an if condition.
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
						return $beforeParen;
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
	 * Check if the empty() call is in a boolean expression (with && or ||).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty token.
	 *
	 * @return false|int The statement token position, or false if not in a boolean expression.
	 */
	private function isInBooleanExpression( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the end of the empty() call.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Look for && or || after the empty() call.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false !== $nextToken && in_array( $tokens[ $nextToken ]['code'], array( T_BOOLEAN_AND, T_BOOLEAN_OR ), true ) ) {
			// Find the statement start (return, etc.).
			return $this->findStatementStart( $phpcsFile, $stackPtr );
		}

		// Look for && or || before the empty() call (or before the ! if negated).
		$searchFrom = $stackPtr;
		$prevToken  = $phpcsFile->findPrevious( T_WHITESPACE, $searchFrom - 1, null, true );

		if ( false !== $prevToken && $tokens[ $prevToken ]['code'] === T_BOOLEAN_NOT ) {
			$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $prevToken - 1, null, true );
		}

		if ( false !== $prevToken && in_array( $tokens[ $prevToken ]['code'], array( T_BOOLEAN_AND, T_BOOLEAN_OR ), true ) ) {
			return $this->findStatementStart( $phpcsFile, $stackPtr );
		}

		return false;
	}

	/**
	 * Find the start of the statement containing the given token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return false|int The statement start token position, or false if not found.
	 */
	private function findStatementStart( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			// Found a statement start.
			if ( in_array( $code, array( T_RETURN, T_ECHO, T_IF, T_ELSEIF ), true ) ) {
				return $i;
			}

			// Found a semicolon or opening brace - statement starts after this.
			if ( $code === T_SEMICOLON || $code === T_OPEN_CURLY_BRACKET ) {
				return $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );
			}

			// Stop if we hit a function or class boundary.
			if ( $code === T_FUNCTION || $code === T_CLOSURE || $code === T_CLASS ) {
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
	 * assigned at the same scope level as the statement (not inside a nested block).
	 *
	 * @param File   $phpcsFile      The file being scanned.
	 * @param int    $functionToken  The position of the function token.
	 * @param int    $statementToken The position of the statement containing empty().
	 * @param string $variableName   The variable name to check.
	 *
	 * @return bool True if the variable was unconditionally assigned, false otherwise.
	 */
	private function wasVariableUnconditionallyAssigned( File $phpcsFile, $functionToken, $statementToken, $variableName ) {
		$tokens      = $phpcsFile->getTokens();
		$scopeOpener = $tokens[ $functionToken ]['scope_opener'];

		// The statement's level is what we compare against.
		$statementLevel = $tokens[ $statementToken ]['level'];

		// Search from the function start to the statement.
		for ( $i = $scopeOpener + 1; $i < $statementToken; $i++ ) {
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

			// Check if the assignment is at the same scope level as the statement.
			// This ensures the variable was assigned unconditionally before the statement.
			$assignmentLevel = $tokens[ $i ]['level'];

			if ( $assignmentLevel === $statementLevel ) {
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
