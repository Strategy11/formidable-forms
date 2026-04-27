<?php
/**
 * Formidable_Sniffs_WhiteSpace_NoBlankLineAfterIfOpenSniff
 *
 * Ensures there is no blank line immediately after the opening brace of an if/elseif/else.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures there is no blank line immediately after the opening brace of an if/elseif/else.
 *
 * Bad:
 * if ( $condition ) {
 *
 *     do_something();
 * }
 *
 * Good:
 * if ( $condition ) {
 *     do_something();
 * }
 */
class NoBlankLineAfterIfOpenSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_IF, T_ELSEIF, T_ELSE );
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

		// Make sure this block has a scope (curly braces).
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) ) {
			return;
		}

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find the first non-whitespace token after the opening brace.
		$firstContent = $phpcsFile->findNext( T_WHITESPACE, $scopeOpener + 1, $scopeCloser, true );

		if ( false === $firstContent ) {
			// Empty block body.
			return;
		}

		$openerLine  = $tokens[ $scopeOpener ]['line'];
		$contentLine = $tokens[ $firstContent ]['line'];

		// Check if there's a blank line between the opener and first content.
		// A blank line means the content is more than 1 line after the opener.
		if ( $contentLine <= $openerLine + 1 ) {
			// No blank line, content is on the next line or same line.
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'No blank line should follow the opening brace of an if/elseif/else.',
			$scopeOpener,
			'BlankLineAfterIfOpen'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Find the whitespace token right after the opener that contains the blank line.
			$nextToken = $scopeOpener + 1;

			if ( $tokens[ $nextToken ]['code'] === T_WHITESPACE ) {
				$content      = $tokens[ $nextToken ]['content'];
				$newlineCount = substr_count( $content, "\n" );

				if ( $newlineCount >= 2 ) {
					// Multiple newlines - reduce to single newline + indentation.
					$lastNewline = strrpos( $content, "\n" );
					$indent      = substr( $content, $lastNewline + 1 );
					$phpcsFile->fixer->replaceToken( $nextToken, "\n" . $indent );
				} elseif ( $newlineCount === 1 ) {
					// Single newline here, but there might be another whitespace token creating the blank.
					// Check if the next token is also whitespace with a newline.
					$afterNext = $nextToken + 1;

					if ( $afterNext < $firstContent && $tokens[ $afterNext ]['code'] === T_WHITESPACE ) {
						// Remove this extra whitespace token.
						$phpcsFile->fixer->replaceToken( $afterNext, '' );
					}
				}
			}

			$phpcsFile->fixer->endChangeset();
		}
	}
}
