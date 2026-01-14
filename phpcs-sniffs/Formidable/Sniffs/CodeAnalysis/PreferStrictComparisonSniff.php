<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferStrictComparisonSniff
 *
 * Converts == and != to === and !== when comparing against non-empty, non-numeric strings.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Converts loose comparisons to strict when comparing to safe string literals.
 *
 * Bad:
 * if ( $var == 'something' ) { ... }
 * if ( $var != 'something' ) { ... }
 *
 * Good:
 * if ( $var === 'something' ) { ... }
 * if ( $var !== 'something' ) { ... }
 *
 * Only applies when comparing to non-empty, non-numeric string literals.
 */
class PreferStrictComparisonSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_IS_EQUAL, T_IS_NOT_EQUAL );
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

		// Check if there's a phpcs:ignore comment for this line.
		if ( $this->hasIgnoreComment( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Find the string literal on either side of the comparison.
		$leftToken  = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		$rightToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $leftToken || false === $rightToken ) {
			return;
		}

		// Check if either side is a string literal.
		$stringToken = false;

		if ( $tokens[ $leftToken ]['code'] === T_CONSTANT_ENCAPSED_STRING ) {
			$stringToken = $leftToken;
		} elseif ( $tokens[ $rightToken ]['code'] === T_CONSTANT_ENCAPSED_STRING ) {
			$stringToken = $rightToken;
		}

		if ( false === $stringToken ) {
			return;
		}

		// Get the string value and check if it's safe for strict comparison.
		$stringValue = $tokens[ $stringToken ]['content'];

		if ( ! $this->isSafeStringForStrictComparison( $stringValue ) ) {
			return;
		}

		// Remove quotes to get the actual string content for the message.
		$stringContent = substr( $stringValue, 1, -1 );

		// Determine the strict operator to use.
		$isEqual         = $tokens[ $stackPtr ]['code'] === T_IS_EQUAL;
		$looseOperator   = $isEqual ? '==' : '!=';
		$strictOperator  = $isEqual ? '===' : '!==';

		$fix = $phpcsFile->addFixableError(
			'Use strict comparison (%s) instead of loose comparison (%s) when comparing to string "%s"',
			$stackPtr,
			'Found',
			array( $strictOperator, $looseOperator, $stringContent )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->replaceToken( $stackPtr, $strictOperator );
		}
	}

	/**
	 * Check if a string value is safe for strict comparison.
	 *
	 * A string is safe if it's:
	 * - Not empty
	 * - Not numeric (to avoid issues with '0' being falsy or numeric coercion)
	 *
	 * @param string $stringValue The string token content (including quotes).
	 *
	 * @return bool
	 */
	private function isSafeStringForStrictComparison( $stringValue ) {
		// Remove quotes.
		$content = substr( $stringValue, 1, -1 );

		// Empty string is not safe.
		if ( $content === '' ) {
			return false;
		}

		// Numeric strings are not safe (e.g., '0', '123', '1.5').
		if ( is_numeric( $content ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if there's a phpcs:ignore comment for this line.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return bool
	 */
	private function hasIgnoreComment( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$line   = $tokens[ $stackPtr ]['line'];

		// Check the previous line for a phpcs:ignore comment.
		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $line - 1 ) {
				break;
			}

			if ( $tokens[ $i ]['line'] === $line - 1 || $tokens[ $i ]['line'] === $line ) {
				if ( in_array( $tokens[ $i ]['code'], array( T_COMMENT, T_DOC_COMMENT_STRING ), true ) ) {
					if ( strpos( $tokens[ $i ]['content'], 'phpcs:ignore' ) !== false ) {
						// Check if it ignores StrictComparisons or our sniff.
						if ( strpos( $tokens[ $i ]['content'], 'StrictComparisons' ) !== false ||
							strpos( $tokens[ $i ]['content'], 'PreferStrictComparison' ) !== false ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}
}
