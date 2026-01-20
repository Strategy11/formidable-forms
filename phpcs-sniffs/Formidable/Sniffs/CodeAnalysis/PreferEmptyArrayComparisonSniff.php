<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferEmptyArrayComparisonSniff
 *
 * Detects count($var) === 0 and suggests using $var === array() instead.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects count($var) === 0 and converts to $var === array().
 *
 * Bad:
 * if ( count( $items ) === 0 ) {}
 * if ( 0 === count( $items ) ) {}
 *
 * Good:
 * if ( $items === array() ) {}
 * if ( array() === $items ) {}
 */
class PreferEmptyArrayComparisonSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING );
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

		// Check if this is a count() function call.
		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'count' ) {
			return;
		}

		// Find the opening parenthesis.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the closing parenthesis.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Get the variable inside count().
		$varStart = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $varStart ) {
			return;
		}

		// Build the variable content (could be $var, $var['key'], $this->prop, etc.).
		$varContent = $this->getVariableContent( $phpcsFile, $varStart, $closeParen );

		if ( empty( $varContent ) ) {
			return;
		}

		// Check for comparison with 0 after count().
		// Pattern: count($var) === 0 or count($var) == 0.
		$afterClose = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false !== $afterClose && $this->isComparisonWithZero( $phpcsFile, $afterClose ) ) {
			$comparisonToken = $afterClose;
			$zeroToken       = $phpcsFile->findNext( T_WHITESPACE, $comparisonToken + 1, null, true );
			$isStrict        = $tokens[ $comparisonToken ]['code'] === T_IS_IDENTICAL;

			$fix = $phpcsFile->addFixableError(
				'Use "%s %s array()" instead of "count( %s ) %s 0".',
				$stackPtr,
				'CountEqualsZero',
				array(
					$varContent,
					$isStrict ? '===' : '==',
					$varContent,
					$isStrict ? '===' : '==',
				)
			);

			if ( true === $fix ) {
				$this->fixCountEqualsZero( $phpcsFile, $stackPtr, $closeParen, $zeroToken, $varContent, $isStrict );
			}

			return;
		}

		// Check for comparison with 0 before count().
		// Pattern: 0 === count($var) or 0 == count($var).
		$beforeCount = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $beforeCount && $this->isComparisonOperator( $phpcsFile, $beforeCount ) ) {
			$comparisonToken = $beforeCount;
			$zeroToken       = $phpcsFile->findPrevious( T_WHITESPACE, $comparisonToken - 1, null, true );

			if ( false !== $zeroToken && $tokens[ $zeroToken ]['code'] === T_LNUMBER && $tokens[ $zeroToken ]['content'] === '0' ) {
				$isStrict = $tokens[ $comparisonToken ]['code'] === T_IS_IDENTICAL;

				$fix = $phpcsFile->addFixableError(
					'Use "array() %s %s" instead of "0 %s count( %s )".',
					$stackPtr,
					'ZeroEqualsCount',
					array(
						$isStrict ? '===' : '==',
						$varContent,
						$isStrict ? '===' : '==',
						$varContent,
					)
				);

				if ( true === $fix ) {
					$this->fixZeroEqualsCount( $phpcsFile, $zeroToken, $closeParen, $varContent, $isStrict );
				}
			}
		}
	}

	/**
	 * Get the variable content from inside count().
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $varStart   The start position of the variable.
	 * @param int  $closeParen The closing parenthesis position.
	 *
	 * @return string The variable content.
	 */
	private function getVariableContent( File $phpcsFile, $varStart, $closeParen ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';

		for ( $i = $varStart; $i < $closeParen; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				// Skip leading/trailing whitespace but keep internal whitespace.
				if ( empty( $content ) ) {
					continue;
				}
			}

			$content .= $tokens[ $i ]['content'];
		}

		return rtrim( $content );
	}

	/**
	 * Check if the token is a comparison with zero.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position to check.
	 *
	 * @return bool
	 */
	private function isComparisonWithZero( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! $this->isComparisonOperator( $phpcsFile, $stackPtr ) ) {
			return false;
		}

		// Check if the next non-whitespace token is 0.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken ) {
			return false;
		}

		return $tokens[ $nextToken ]['code'] === T_LNUMBER && $tokens[ $nextToken ]['content'] === '0';
	}

	/**
	 * Check if the token is a comparison operator (=== or ==).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position to check.
	 *
	 * @return bool
	 */
	private function isComparisonOperator( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		return in_array( $tokens[ $stackPtr ]['code'], array( T_IS_IDENTICAL, T_IS_EQUAL ), true );
	}

	/**
	 * Fix count($var) === 0 to $var === array().
	 *
	 * @param File   $phpcsFile  The file being scanned.
	 * @param int    $countStart The count function start position.
	 * @param int    $closeParen The closing parenthesis position.
	 * @param int    $zeroToken  The zero token position.
	 * @param string $varContent The variable content.
	 * @param bool   $isStrict   Whether the comparison is strict.
	 *
	 * @return void
	 */
	private function fixCountEqualsZero( File $phpcsFile, $countStart, $closeParen, $zeroToken, $varContent, $isStrict ) {
		$fixer      = $phpcsFile->fixer;
		$comparison = $isStrict ? '===' : '==';

		// Replace from count to 0 with $var === array().
		for ( $i = $countStart; $i <= $zeroToken; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		$fixer->addContent( $countStart, $varContent . ' ' . $comparison . ' array()' );
	}

	/**
	 * Fix 0 === count($var) to array() === $var.
	 *
	 * @param File   $phpcsFile  The file being scanned.
	 * @param int    $zeroToken  The zero token position.
	 * @param int    $closeParen The closing parenthesis position.
	 * @param string $varContent The variable content.
	 * @param bool   $isStrict   Whether the comparison is strict.
	 *
	 * @return void
	 */
	private function fixZeroEqualsCount( File $phpcsFile, $zeroToken, $closeParen, $varContent, $isStrict ) {
		$fixer      = $phpcsFile->fixer;
		$comparison = $isStrict ? '===' : '==';

		// Replace from 0 to closing paren with array() === $var.
		$fixer->beginChangeset();

		for ( $i = $zeroToken; $i <= $closeParen; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		$fixer->replaceToken( $zeroToken, 'array() ' . $comparison . ' ' . $varContent );
		$fixer->endChangeset();
	}
}
