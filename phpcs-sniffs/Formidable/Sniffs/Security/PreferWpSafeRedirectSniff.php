<?php
/**
 * Sniff to convert wp_redirect() calls that point to local URLs into wp_safe_redirect().
 *
 * @package Formidable\Sniffs\Security
 */

namespace Formidable\Sniffs\Security;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags wp_redirect() when the destination is built from clearly local helpers.
 */
class PreferWpSafeRedirectSniff implements Sniff {

	/**
	 * Functions that always return a site-local URL.
	 *
	 * @var string[]
	 */
	private $safeFunctions = array(
		'home_url',
		'site_url',
		'admin_url',
	);

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		return array( T_STRING );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( 'wp_redirect' !== strtolower( $tokens[ $stackPtr ]['content'] ) ) {
			return;
		}

		// Skip method calls like $this->wp_redirect().
		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON ), true ) ) {
			return;
		}

		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] ) {
			return;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$firstArg = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $tokens[ $openParen ]['parenthesis_closer'], true );

		if ( false === $firstArg ) {
			return;
		}

		$argumentEnd = $this->getFirstArgumentEnd( $phpcsFile, $openParen );

		if ( null === $argumentEnd ) {
			return;
		}

		$safeSource = $this->findLocalFunctionInRange( $phpcsFile, $firstArg, $argumentEnd );

		if ( null === $safeSource ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use wp_safe_redirect() when redirecting to %s().',
			$stackPtr,
			'UseSafeRedirect',
			array( $safeSource )
		);

		if ( $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $stackPtr, 'wp_safe_redirect' );
			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Find the closing boundary for the first argument.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $openParen The opening parentheses token for wp_redirect().
	 *
	 * @return int|null
	 */
	private function getFirstArgumentEnd( File $phpcsFile, $openParen ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return null;
		}

		$close = $tokens[ $openParen ]['parenthesis_closer'];
		$depth = 0;

		for ( $ptr = $openParen + 1; $ptr < $close; $ptr++ ) {
			$code = $tokens[ $ptr ]['code'];

			if ( T_OPEN_PARENTHESIS === $code ) {
				$depth++;
				continue;
			}

			if ( T_CLOSE_PARENTHESIS === $code ) {
				if ( $depth > 0 ) {
					$depth--;
				}
				continue;
			}

			if ( T_COMMA === $code && 0 === $depth ) {
				return $ptr - 1;
			}
		}

		return $close - 1;
	}

	/**
	 * Determine if a safe helper is used within a range of tokens.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  The first token within the first argument.
	 * @param int  $endPtr    The last token belonging to the first argument.
	 *
	 * @return string|null The helper name if found, otherwise null.
	 */
	private function findLocalFunctionInRange( File $phpcsFile, $startPtr, $endPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $ptr = $startPtr; $ptr <= $endPtr && $ptr < $phpcsFile->numTokens; $ptr++ ) {
			if ( T_STRING !== $tokens[ $ptr ]['code'] ) {
				continue;
			}

			$functionName = strtolower( $tokens[ $ptr ]['content'] );

			if ( ! in_array( $functionName, $this->safeFunctions, true ) ) {
				continue;
			}

			$prev = $phpcsFile->findPrevious( T_WHITESPACE, $ptr - 1, $startPtr - 1, true );

			if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON ), true ) ) {
				continue;
			}

			$next = $phpcsFile->findNext( T_WHITESPACE, $ptr + 1, $endPtr + 1, true );

			if ( false === $next || T_OPEN_PARENTHESIS !== $tokens[ $next ]['code'] ) {
				continue;
			}

			return $tokens[ $ptr ]['content'];
		}

		return null;
	}
}
