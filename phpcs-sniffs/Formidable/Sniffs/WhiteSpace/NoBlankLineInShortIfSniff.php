<?php
/**
 * Formidable_Sniffs_WhiteSpace_NoBlankLineInShortIfSniff
 *
 * Removes blank lines inside if blocks that are exactly 3 lines.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Removes blank lines inside if blocks that are exactly 3 lines.
 *
 * Bad:
 * if ( $condition ) {
 *     do_something();
 *
 *     return;
 * }
 *
 * Good:
 * if ( $condition ) {
 *     do_something();
 *     return;
 * }
 */
class NoBlankLineInShortIfSniff implements Sniff {

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

		// Make sure this if/else has a scope (curly braces).
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Calculate the number of lines in the block body.
		$openerLine = $tokens[ $scopeOpener ]['line'];
		$closerLine = $tokens[ $scopeCloser ]['line'];

		// Body lines = closerLine - openerLine - 1.
		// We want to trigger when there are exactly 3 body lines (2 code + 1 blank).
		// This means the if block has 2 statements but a blank line between them.
		$bodyLines = $closerLine - $openerLine - 1;

		// Only process if the body is exactly 3 lines (2 code lines + 1 blank line).
		if ( $bodyLines !== 3 ) {
			return;
		}

		// Find if there's a blank line inside the block.
		// A blank line is a whitespace token that contains 2+ consecutive newlines.
		$blankLineToken = null;

		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				$content      = $tokens[ $i ]['content'];
				$newlineCount = substr_count( $content, "\n" );

				// If this whitespace has 2+ newlines, it contains a blank line.
				if ( $newlineCount >= 2 ) {
					$blankLineToken = $i;
					break;
				}
			}
		}

		if ( null === $blankLineToken ) {
			// Also check for consecutive newline tokens (PHPCS sometimes splits them).
			$lastWasNewline = false;

			for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE && $tokens[ $i ]['content'] === "\n" ) {
					if ( $lastWasNewline ) {
						$blankLineToken = $i;
						break;
					}
					$lastWasNewline = true;
				} else {
					$lastWasNewline = false;
				}
			}
		}

		if ( null === $blankLineToken ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Remove blank line inside short if block (3 lines or less).',
			$blankLineToken,
			'BlankLineInShortIf'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			$content = $tokens[ $blankLineToken ]['content'];

			// Remove one newline from the whitespace.
			if ( substr_count( $content, "\n" ) >= 2 ) {
				// Replace double newline with single newline, preserving indentation.
				$newContent = preg_replace( "/\n\n/", "\n", $content, 1 );
				$phpcsFile->fixer->replaceToken( $blankLineToken, $newContent );
			} elseif ( $content === "\n" ) {
				// This is a standalone newline token that creates a blank line.
				$phpcsFile->fixer->replaceToken( $blankLineToken, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}
}
