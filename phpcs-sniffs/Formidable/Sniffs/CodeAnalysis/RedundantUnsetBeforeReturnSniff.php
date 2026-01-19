<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantUnsetBeforeReturnSniff
 *
 * Detects redundant unset() calls immediately before a return statement.
 * When a function returns, all local variables are automatically destroyed,
 * making the unset() call unnecessary.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant unset() calls before return statements.
 *
 * Bad:
 * unset( $tax );
 * return $link;
 *
 * Good:
 * return $link;
 */
class RedundantUnsetBeforeReturnSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_UNSET );
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

		// Find the semicolon ending this unset statement.
		$unsetEnd = $phpcsFile->findNext( T_SEMICOLON, $stackPtr + 1 );

		if ( false === $unsetEnd ) {
			return;
		}

		// Find the next non-whitespace token after the unset statement.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $unsetEnd + 1, null, true );

		if ( false === $nextToken ) {
			return;
		}

		// Check if the next statement is a return.
		if ( $tokens[ $nextToken ]['code'] !== T_RETURN ) {
			return;
		}

		// Get the variables being unset for the error message.
		$openParen = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $stackPtr + 1, $unsetEnd );

		if ( false === $openParen ) {
			return;
		}

		$closeParen = $phpcsFile->findNext( T_CLOSE_PARENTHESIS, $openParen + 1, $unsetEnd );

		if ( false === $closeParen ) {
			return;
		}

		// Check if we're unsetting simple variables only (not array keys or object properties).
		// If we find [ or -> after a variable, it's modifying data, not just freeing memory.
		$variables         = array();
		$hasComplexUnset   = false;
		$lastWasVariable   = false;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE ) {
				$variables[]     = $tokens[ $i ]['content'];
				$lastWasVariable = true;
			} elseif ( $lastWasVariable ) {
				// Check if this variable is followed by array access or object access.
				if ( in_array( $tokens[ $i ]['code'], array( T_OPEN_SQUARE_BRACKET, T_OBJECT_OPERATOR ), true ) ) {
					$hasComplexUnset = true;
					break;
				} elseif ( $tokens[ $i ]['code'] !== T_WHITESPACE && $tokens[ $i ]['code'] !== T_COMMA ) {
					$lastWasVariable = false;
				}
			}
		}

		// Don't flag unsets that modify array keys or object properties - those are meaningful.
		if ( $hasComplexUnset ) {
			return;
		}

		$variableList = implode( ', ', $variables );

		$fix = $phpcsFile->addFixableError(
			'Redundant unset(%s) before return. Local variables are automatically destroyed when the function returns.',
			$stackPtr,
			'RedundantUnsetBeforeReturn',
			array( $variableList )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $tokens, $stackPtr, $unsetEnd );
		}
	}

	/**
	 * Apply the fix by removing the unset statement and its trailing whitespace.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  Start of unset.
	 * @param int   $unsetEnd  End of unset (semicolon).
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, array $tokens, $stackPtr, $unsetEnd ) {
		$phpcsFile->fixer->beginChangeset();

		// Find the start of the line for the unset statement.
		$lineStart = $stackPtr;

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $tokens[ $stackPtr ]['line'] ) {
				break;
			}
			$lineStart = $i;
		}

		// Remove from line start to semicolon.
		for ( $i = $lineStart; $i <= $unsetEnd; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Remove the newline after the semicolon.
		if ( isset( $tokens[ $unsetEnd + 1 ] ) && $tokens[ $unsetEnd + 1 ]['code'] === T_WHITESPACE ) {
			$wsContent = $tokens[ $unsetEnd + 1 ]['content'];

			if ( strpos( $wsContent, "\n" ) === 0 ) {
				$phpcsFile->fixer->replaceToken( $unsetEnd + 1, substr( $wsContent, 1 ) );
			}
		}

		$phpcsFile->fixer->endChangeset();
	}
}
