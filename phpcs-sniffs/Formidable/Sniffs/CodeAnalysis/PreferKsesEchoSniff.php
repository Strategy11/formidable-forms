<?php
/**
 * Sniff to convert echo FrmAppHelper::kses() to FrmAppHelper::kses_echo().
 *
 * The kses_echo method handles the echo internally, so we can remove the echo
 * and the phpcs:ignore comment for EscapeOutput.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects and fixes echo FrmAppHelper::kses() to use FrmAppHelper::kses_echo().
 */
class PreferKsesEchoSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_ECHO );
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

		// Find the next non-whitespace token after echo.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken ) {
			return;
		}

		// Check for self::kses( or FrmAppHelper::kses(.
		$ksesInfo = $this->findKsesCall( $phpcsFile, $nextToken );

		if ( false === $ksesInfo ) {
			return;
		}

		// Make sure it's not already kses_echo.
		if ( $tokens[ $ksesInfo['methodToken'] ]['content'] === 'kses_echo' ) {
			return;
		}

		// Skip if we're inside the kses_echo method itself (to avoid infinite recursion).
		if ( $this->isInsideKsesEchoMethod( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use FrmAppHelper::kses_echo() instead of echo FrmAppHelper::kses().',
			$stackPtr,
			'UseKsesEcho'
		);

		if ( $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $ksesInfo );
		}
	}

	/**
	 * Check if the current position is inside the kses_echo method.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The current position.
	 *
	 * @return bool
	 */
	private function isInsideKsesEchoMethod( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the enclosing function.
		foreach ( $tokens[ $stackPtr ]['conditions'] as $scope => $code ) {
			if ( $code !== T_FUNCTION ) {
				continue;
			}

			// Find the function name.
			$nameToken = $phpcsFile->findNext( T_STRING, $scope );

			if ( false !== $nameToken && $tokens[ $nameToken ]['content'] === 'kses_echo' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Find a kses() call starting at the given position.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  The position to start looking.
	 *
	 * @return array|false Array with call info or false if not found.
	 */
	private function findKsesCall( File $phpcsFile, $startPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Check for self:: or FrmAppHelper::.
		if ( $tokens[ $startPtr ]['code'] === T_SELF ) {
			// self::kses.
			$doubleColon = $phpcsFile->findNext( T_WHITESPACE, $startPtr + 1, null, true );

			if ( false === $doubleColon || $tokens[ $doubleColon ]['code'] !== T_DOUBLE_COLON ) {
				return false;
			}

			$methodToken = $phpcsFile->findNext( T_WHITESPACE, $doubleColon + 1, null, true );

			if ( false === $methodToken || $tokens[ $methodToken ]['code'] !== T_STRING ) {
				return false;
			}

			if ( $tokens[ $methodToken ]['content'] !== 'kses' ) {
				return false;
			}

			return array(
				'classToken'  => $startPtr,
				'methodToken' => $methodToken,
				'isSelf'      => true,
			);
		}

		if ( $tokens[ $startPtr ]['code'] === T_STRING && $tokens[ $startPtr ]['content'] === 'FrmAppHelper' ) {
			// FrmAppHelper::kses.
			$doubleColon = $phpcsFile->findNext( T_WHITESPACE, $startPtr + 1, null, true );

			if ( false === $doubleColon || $tokens[ $doubleColon ]['code'] !== T_DOUBLE_COLON ) {
				return false;
			}

			$methodToken = $phpcsFile->findNext( T_WHITESPACE, $doubleColon + 1, null, true );

			if ( false === $methodToken || $tokens[ $methodToken ]['code'] !== T_STRING ) {
				return false;
			}

			if ( $tokens[ $methodToken ]['content'] !== 'kses' ) {
				return false;
			}

			return array(
				'classToken'  => $startPtr,
				'methodToken' => $methodToken,
				'isSelf'      => false,
			);
		}

		return false;
	}

	/**
	 * Apply the fix.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $echoPtr   The position of the echo token.
	 * @param array $ksesInfo  Information about the kses call.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $echoPtr, $ksesInfo ) {
		$tokens = $phpcsFile->getTokens();
		$phpcsFile->fixer->beginChangeset();

		// Remove echo and any whitespace after it.
		$phpcsFile->fixer->replaceToken( $echoPtr, '' );

		$next = $echoPtr + 1;

		while ( isset( $tokens[ $next ] ) && $tokens[ $next ]['code'] === T_WHITESPACE ) {
			$phpcsFile->fixer->replaceToken( $next, '' );
			$next++;
		}

		// Change kses to kses_echo.
		$phpcsFile->fixer->replaceToken( $ksesInfo['methodToken'], 'kses_echo' );

		// Find and remove the phpcs:ignore comment for EscapeOutput if present.
		$this->removeEscapeOutputIgnore( $phpcsFile, $echoPtr );

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Remove phpcs:ignore comment for EscapeOutput on the same line.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $echoPtr   The position of the echo token.
	 *
	 * @return void
	 */
	private function removeEscapeOutputIgnore( File $phpcsFile, $echoPtr ) {
		$tokens = $phpcsFile->getTokens();
		$line   = $tokens[ $echoPtr ]['line'];

		// Look for comments on the same line.
		for ( $i = $echoPtr; isset( $tokens[ $i ] ) && $tokens[ $i ]['line'] === $line; $i++ ) {
			// PHPCS uses T_COMMENT or T_PHPCS_IGNORE for comments.
			if ( ! in_array( $tokens[ $i ]['code'], array( T_COMMENT, T_PHPCS_IGNORE ), true ) ) {
				continue;
			}

			$content = $tokens[ $i ]['content'];

			// Check if this is a phpcs:ignore comment for EscapeOutput.
			if ( strpos( $content, 'phpcs:ignore' ) === false && strpos( $content, 'EscapeOutput' ) === false ) {
				continue;
			}

			if ( strpos( $content, 'EscapeOutput' ) === false ) {
				continue;
			}

			// Check if EscapeOutput is the only rule being ignored.
			if ( preg_match( '/phpcs:ignore\s+WordPress\.Security\.EscapeOutput\.[A-Za-z]+\s*$/', $content ) ) {
				// Remove the entire comment but keep the newline if present.
				$commentContent = $tokens[ $i ]['content'];

				if ( strpos( $commentContent, "\n" ) !== false ) {
					// Keep the newline.
					$phpcsFile->fixer->replaceToken( $i, "\n" );
				} else {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}

				// Also remove leading whitespace/tabs before the comment (but not the semicolon).
				$prev = $i - 1;

				while ( isset( $tokens[ $prev ] ) && $tokens[ $prev ]['line'] === $line && $tokens[ $prev ]['code'] === T_WHITESPACE ) {
					$phpcsFile->fixer->replaceToken( $prev, '' );
					$prev--;
				}
			} else {
				// Multiple rules - just remove the EscapeOutput part.
				$newContent = preg_replace( '/,?\s*WordPress\.Security\.EscapeOutput\.[A-Za-z]+/', '', $content );
				$newContent = preg_replace( '/WordPress\.Security\.EscapeOutput\.[A-Za-z]+,?\s*/', '', $newContent );
				$phpcsFile->fixer->replaceToken( $i, $newContent );
			}
		}
	}
}
