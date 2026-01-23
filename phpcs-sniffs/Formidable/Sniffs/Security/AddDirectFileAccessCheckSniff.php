<?php
/**
 * Sniff to add missing direct file access check.
 *
 * Ensures PHP files have the ABSPATH check to prevent direct file access:
 * if ( ! defined( 'ABSPATH' ) ) {
 *     die( 'You are not allowed to call this page directly.' );
 * }
 *
 * @package Formidable\Sniffs\Security
 */

namespace Formidable\Sniffs\Security;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Adds missing direct file access check to PHP files.
 */
class AddDirectFileAccessCheckSniff implements Sniff {

	/**
	 * The direct file access check code.
	 *
	 * @var string
	 */
	private $accessCheck = "if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}";

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_OPEN_TAG );
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return int|void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens   = $phpcsFile->getTokens();
		$filename = $phpcsFile->getFilename();

		// Skip files in the tests folder.
		if ( strpos( $filename, '/tests/' ) !== false || strpos( $filename, '\\tests\\' ) !== false ) {
			return $phpcsFile->numTokens;
		}

		// Only process the first T_OPEN_TAG in the file.
		$firstOpenTag = $phpcsFile->findNext( T_OPEN_TAG, 0 );

		if ( $stackPtr !== $firstOpenTag ) {
			return;
		}

		// Check if the file already has the ABSPATH check.
		if ( $this->hasAbspathCheck( $phpcsFile ) ) {
			return $phpcsFile->numTokens;
		}

		$fix = $phpcsFile->addFixableError(
			'Missing direct file access check. Add ABSPATH check to prevent direct file access.',
			$stackPtr,
			'MissingAbspathCheck'
		);

		if ( $fix ) {
			$this->addAbspathCheck( $phpcsFile, $stackPtr );
		}

		// Return the end of the file to prevent processing other T_OPEN_TAG tokens.
		return $phpcsFile->numTokens;
	}

	/**
	 * Check if the file already has the ABSPATH check.
	 *
	 * @param File $phpcsFile The file being scanned.
	 *
	 * @return bool
	 */
	private function hasAbspathCheck( File $phpcsFile ) {
		$tokens = $phpcsFile->getTokens();

		// Look for "defined( 'ABSPATH' )" or "defined('ABSPATH')" pattern.
		for ( $i = 0; $i < $phpcsFile->numTokens; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_STRING || strtolower( $tokens[ $i ]['content'] ) !== 'defined' ) {
				continue;
			}

			// Check if the next non-whitespace token is an open parenthesis.
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_OPEN_PARENTHESIS ) {
				continue;
			}

			// Check if the string inside is 'ABSPATH'.
			$stringToken = $phpcsFile->findNext( T_WHITESPACE, $nextToken + 1, null, true );

			if ( false === $stringToken ) {
				continue;
			}

			if ( $tokens[ $stringToken ]['code'] === T_CONSTANT_ENCAPSED_STRING ) {
				$content = trim( $tokens[ $stringToken ]['content'], "\"'" );

				if ( $content === 'ABSPATH' ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Add the ABSPATH check to the file.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the T_OPEN_TAG token.
	 *
	 * @return void
	 */
	private function addAbspathCheck( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find where to insert the check.
		$insertAfter = $this->findInsertPosition( $phpcsFile, $stackPtr );

		$phpcsFile->fixer->beginChangeset();

		// Replace the <?php tag with <?php + newline + ABSPATH check + blank line.
		$phpcsFile->fixer->replaceToken( $stackPtr, "<?php\n" . $this->accessCheck . "\n\n" );

		// Remove all whitespace tokens between <?php and the next non-whitespace.
		$nextNonWhitespace = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false !== $nextNonWhitespace ) {
			for ( $i = $stackPtr + 1; $i < $nextNonWhitespace; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
		}

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Find the position to insert the ABSPATH check.
	 *
	 * Should be after the opening PHP tag and any file-level docblock.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the T_OPEN_TAG token.
	 *
	 * @return int The token position to insert after.
	 */
	private function findInsertPosition( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Start after the opening PHP tag.
		$current = $stackPtr;

		// Skip any whitespace after the opening tag.
		$next = $phpcsFile->findNext( T_WHITESPACE, $current + 1, null, true );

		if ( false === $next ) {
			return $current;
		}

		// Check if there's a file-level docblock.
		if ( $tokens[ $next ]['code'] === T_DOC_COMMENT_OPEN_TAG ) {
			// Find the end of the docblock.
			if ( isset( $tokens[ $next ]['comment_closer'] ) ) {
				$docblockEnd = $tokens[ $next ]['comment_closer'];

				// Check if this docblock contains @package (file-level docblock).
				$hasPackage = false;

				for ( $i = $next; $i <= $docblockEnd; $i++ ) {
					if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG && $tokens[ $i ]['content'] === '@package' ) {
						$hasPackage = true;
						break;
					}
				}

				if ( $hasPackage ) {
					// Insert after the docblock.
					// Find the whitespace after the docblock closer.
					$afterDocblock = $phpcsFile->findNext( T_WHITESPACE, $docblockEnd + 1, null, true );

					if ( false !== $afterDocblock ) {
						// Return the whitespace token before the next code.
						$whitespaceToken = $docblockEnd + 1;

						if ( $tokens[ $whitespaceToken ]['code'] === T_WHITESPACE ) {
							return $whitespaceToken;
						}
					}

					return $docblockEnd;
				}
			}
		}

		// No file-level docblock, insert after the opening PHP tag.
		// Find the whitespace after the opening tag.
		$whitespaceAfterTag = $stackPtr + 1;

		if ( isset( $tokens[ $whitespaceAfterTag ] ) && $tokens[ $whitespaceAfterTag ]['code'] === T_WHITESPACE ) {
			return $whitespaceAfterTag;
		}

		return $stackPtr;
	}
}
