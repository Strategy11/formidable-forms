<?php
/**
 * Formidable_Sniffs_WhiteSpace_NoBlankLineBeforeCloseBraceSniff
 *
 * Detects and removes blank lines before closing braces.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects blank lines before closing braces.
 *
 * Bad:
 * function example() {
 *     return $value;
 *
 * }
 *
 * Good:
 * function example() {
 *     return $value;
 * }
 */
class NoBlankLineBeforeCloseBraceSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_CLOSE_CURLY_BRACKET );
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

		// Get the line of the closing brace.
		$closeBraceLine = $tokens[ $stackPtr ]['line'];

		// Find the previous non-whitespace token.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken ) {
			return;
		}

		$prevTokenLine = $tokens[ $prevToken ]['line'];

		// Check if there's more than one line between the previous token and the closing brace.
		$blankLines = $closeBraceLine - $prevTokenLine - 1;

		if ( $blankLines < 1 ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'No blank line allowed before closing brace',
			$stackPtr,
			'Found'
		);

		if ( true === $fix ) {
			$fixer = $phpcsFile->fixer;

			$fixer->beginChangeset();

			// Remove the blank lines by replacing whitespace tokens.
			for ( $i = $prevToken + 1; $i < $stackPtr; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					// Keep only the necessary whitespace (newline + indentation for the closing brace).
					if ( $tokens[ $i ]['line'] === $closeBraceLine - 1 ) {
						// This is the line right before the closing brace - keep the newline.
						$fixer->replaceToken( $i, $phpcsFile->eolChar );
					} elseif ( $tokens[ $i ]['line'] === $closeBraceLine ) {
						// This is the indentation on the same line as the closing brace - keep it.
						continue;
					} else {
						// Remove extra blank lines.
						$fixer->replaceToken( $i, '' );
					}
				}
			}

			$fixer->endChangeset();
		}
	}
}
