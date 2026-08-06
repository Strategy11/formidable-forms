<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantEmptyOnLoopVariableSniff
 *
 * Detects redundant empty() calls on foreach loop variables.
 * Since loop variables are always set when iterating, empty() is redundant
 * and can be replaced with a simple falsy check.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant empty() on foreach loop variables.
 *
 * Bad:
 * foreach ($items as $key => $value) {
 *     if (empty($value)) {
 *         // ...
 *     }
 * }
 *
 * Good:
 * foreach ($items as $key => $value) {
 *     if (! $value) {
 *         // ...
 *     }
 * }
 *
 * The loop variable is always set during iteration, so empty() is redundant.
 */
class RedundantEmptyOnLoopVariableSniff implements Sniff {

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

		// Find the opening parenthesis after empty.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the variable inside empty().
		$variableToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, null, true );

		if ( false === $variableToken || $tokens[ $variableToken ]['code'] !== T_VARIABLE ) {
			return;
		}

		$variableName = $tokens[ $variableToken ]['content'];

		// Check if the next non-whitespace token is the closing parenthesis.
		// This ensures we're dealing with a simple empty($var) call.
		$closeParen = $phpcsFile->findNext( T_WHITESPACE, $variableToken + 1, null, true );

		if ( false === $closeParen || $tokens[ $closeParen ]['code'] !== T_CLOSE_PARENTHESIS ) {
			// Not a simple variable, could be empty($var['key']) or empty($var->prop).
			return;
		}

		// Find the containing foreach loop.
		$foreachToken = $this->findContainingForeach( $phpcsFile, $stackPtr );

		if ( false === $foreachToken ) {
			return;
		}

		// Check if this variable is a loop variable (key or value).
		if ( ! $this->isLoopVariable( $phpcsFile, $foreachToken, $variableName ) ) {
			return;
		}

		// Check if the variable was reassigned before this empty() call.
		$foreachOpener = $tokens[ $foreachToken ]['scope_opener'];

		if ( $this->wasVariableReassigned( $phpcsFile, $foreachOpener, $stackPtr, $variableName ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Redundant empty() on loop variable %s. Use ! %s instead since loop variables are always set.',
			$stackPtr,
			'Found',
			array( $variableName, $variableName )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $openParen, $closeParen, $variableName );
		}
	}

	/**
	 * Find the containing foreach loop.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return false|int The position of the foreach token, or false if not found.
	 */
	private function findContainingForeach( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_FOREACH ) {
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
	 * Check if a variable is a loop variable (key or value) in a foreach.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param int    $foreachToken The foreach token position.
	 * @param string $variableName The variable name to check.
	 *
	 * @return bool True if the variable is a loop variable.
	 */
	private function isLoopVariable( File $phpcsFile, $foreachToken, $variableName ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $foreachToken ]['parenthesis_opener'], $tokens[ $foreachToken ]['parenthesis_closer'] ) ) {
			return false;
		}

		$parenOpener = $tokens[ $foreachToken ]['parenthesis_opener'];
		$parenCloser = $tokens[ $foreachToken ]['parenthesis_closer'];

		// Find the 'as' keyword.
		$asToken = $phpcsFile->findNext( T_AS, $parenOpener + 1, $parenCloser );

		if ( false === $asToken ) {
			return false;
		}

		// Look for variables after 'as'.
		$loopVariables = array();

		for ( $i = $asToken + 1; $i < $parenCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE ) {
				$loopVariables[] = $tokens[ $i ]['content'];
			}
		}

		return in_array( $variableName, $loopVariables, true );
	}

	/**
	 * Check if a variable was reassigned between two positions.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param int    $startPos     The start position to search from.
	 * @param int    $endPos       The end position to search to.
	 * @param string $variableName The variable name to check.
	 *
	 * @return bool True if the variable was reassigned.
	 */
	private function wasVariableReassigned( File $phpcsFile, $startPos, $endPos, $variableName ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $startPos + 1; $i < $endPos; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE ) {
				continue;
			}

			if ( $tokens[ $i ]['content'] !== $variableName ) {
				continue;
			}

			// Check if this is an assignment.
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false !== $nextToken && $tokens[ $nextToken ]['code'] === T_EQUAL ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Apply the fix.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param int    $emptyToken   The empty token position.
	 * @param int    $openParen    The opening parenthesis position.
	 * @param int    $closeParen   The closing parenthesis position.
	 * @param string $variableName The variable name.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $emptyToken, $openParen, $closeParen, $variableName ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		// Check if there's a ! before empty.
		$beforeEmpty = $phpcsFile->findPrevious( T_WHITESPACE, $emptyToken - 1, null, true );
		$hasNot      = false !== $beforeEmpty && $tokens[ $beforeEmpty ]['code'] === T_BOOLEAN_NOT;

		$fixer->beginChangeset();

		if ( $hasNot ) {
			// ! empty($var) becomes $var
			// Remove the !
			$fixer->replaceToken( $beforeEmpty, '' );

			// Remove whitespace between ! and empty if any.
			for ( $i = $beforeEmpty + 1; $i < $emptyToken; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					$fixer->replaceToken( $i, '' );
				}
			}

			// Replace empty( with just the variable.
			$fixer->replaceToken( $emptyToken, '' );

			for ( $i = $emptyToken + 1; $i <= $openParen; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}

			// Keep the variable, remove whitespace before it.
			$variableToken = $phpcsFile->findNext( T_VARIABLE, $openParen + 1, $closeParen );

			for ( $i = $openParen + 1; $i < $variableToken; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}

			// Remove whitespace after variable and closing paren.
			for ( $i = $variableToken + 1; $i <= $closeParen; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}
		} else {
			// empty($var) becomes ! $var
			// Replace empty( with ! $var
			$fixer->replaceToken( $emptyToken, '!' );

			for ( $i = $emptyToken + 1; $i <= $openParen; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}

			// Keep the variable, but ensure proper spacing.
			$variableToken = $phpcsFile->findNext( T_VARIABLE, $openParen + 1, $closeParen );

			// Replace whitespace before variable with single space.
			$hasWhitespace = false;

			for ( $i = $openParen + 1; $i < $variableToken; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					if ( ! $hasWhitespace ) {
						$fixer->replaceToken( $i, ' ' );
						$hasWhitespace = true;
					} else {
						$fixer->replaceToken( $i, '' );
					}
				}
			}

			if ( ! $hasWhitespace ) {
				$fixer->addContentBefore( $variableToken, ' ' );
			}

			// Remove whitespace after variable and closing paren.
			for ( $i = $variableToken + 1; $i <= $closeParen; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}
		}

		$fixer->endChangeset();
	}
}
