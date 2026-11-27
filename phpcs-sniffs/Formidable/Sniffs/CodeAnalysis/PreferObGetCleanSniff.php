<?php
/**
 * Sniff to detect ob_get_contents() followed by ob_end_clean() that can be replaced with ob_get_clean().
 *
 * Detects patterns like:
 * $var = ob_get_contents();
 * ob_end_clean();
 *
 * These can be simplified to: $var = ob_get_clean();
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects ob_get_contents + ob_end_clean patterns that can be simplified to ob_get_clean.
 */
class PreferObGetCleanSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING );
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Check if this is ob_get_contents.
		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'ob_get_contents' ) {
			return;
		}

		// Find the semicolon ending this statement.
		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $stackPtr + 1 );

		if ( false === $semicolon ) {
			return;
		}

		// Find the next non-whitespace token after the semicolon.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $semicolon + 1, null, true );

		if ( false === $nextToken ) {
			return;
		}

		// Check if the next statement is ob_end_clean().
		if ( $tokens[ $nextToken ]['code'] !== T_STRING ) {
			return;
		}

		if ( strtolower( $tokens[ $nextToken ]['content'] ) !== 'ob_end_clean' ) {
			return;
		}

		// Find the semicolon ending ob_end_clean().
		$endCleanSemicolon = $phpcsFile->findNext( T_SEMICOLON, $nextToken + 1 );

		if ( false === $endCleanSemicolon ) {
			return;
		}

		// We found the pattern. Report a fixable error.
		$fix = $phpcsFile->addFixableError(
			'Use ob_get_clean() instead of ob_get_contents() followed by ob_end_clean().',
			$stackPtr,
			'UseObGetClean'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Replace ob_get_contents with ob_get_clean.
			$phpcsFile->fixer->replaceToken( $stackPtr, 'ob_get_clean' );

			// Remove everything from after the first semicolon to the second semicolon (inclusive).
			for ( $i = $semicolon + 1; $i <= $endCleanSemicolon; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}
}
