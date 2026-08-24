<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferArrayKeyExistsSniff
 *
 * Detects in_array() with array_keys() and suggests using array_key_exists() instead.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects in_array($x, array_keys($arr)) and suggests array_key_exists($x, $arr).
 *
 * Bad:
 * in_array( $status, array_keys( $nice_names ), true )
 *
 * Good:
 * array_key_exists( $status, $nice_names )
 */
class PreferArrayKeyExistsSniff implements Sniff {

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

		// Check if this is in_array.
		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'in_array' ) {
			return;
		}

		// Find the opening parenthesis.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Find the first argument (needle).
		$needleStart = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $needleStart ) {
			return;
		}

		// Find the comma after the first argument.
		$firstComma = $this->findNextCommaAtSameLevel( $phpcsFile, $tokens, $needleStart, $closeParen );

		if ( false === $firstComma ) {
			return;
		}

		// Find the second argument (haystack) - should be array_keys().
		$haystackStart = $phpcsFile->findNext( T_WHITESPACE, $firstComma + 1, $closeParen, true );

		if ( false === $haystackStart ) {
			return;
		}

		// Check if the second argument is array_keys().
		if ( $tokens[ $haystackStart ]['code'] !== T_STRING || strtolower( $tokens[ $haystackStart ]['content'] ) !== 'array_keys' ) {
			return;
		}

		// Find the opening parenthesis of array_keys.
		$arrayKeysOpenParen = $phpcsFile->findNext( T_WHITESPACE, $haystackStart + 1, $closeParen, true );

		if ( false === $arrayKeysOpenParen || $tokens[ $arrayKeysOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		$arrayKeysCloseParen = $tokens[ $arrayKeysOpenParen ]['parenthesis_closer'];

		// Get the array being passed to array_keys.
		$arrayStart = $phpcsFile->findNext( T_WHITESPACE, $arrayKeysOpenParen + 1, $arrayKeysCloseParen, true );

		if ( false === $arrayStart ) {
			return;
		}

		// Get the array content (everything inside array_keys parentheses).
		$arrayContent = '';

		for ( $i = $arrayStart; $i < $arrayKeysCloseParen; $i++ ) {
			$arrayContent .= $tokens[ $i ]['content'];
		}

		// Get the needle content.
		$needleContent = '';

		for ( $i = $needleStart; $i < $firstComma; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_WHITESPACE || $needleContent !== '' ) {
				$needleContent .= $tokens[ $i ]['content'];
			}
		}

		$needleContent = rtrim( $needleContent );

		$fix = $phpcsFile->addFixableError(
			'Use array_key_exists( %s, %s ) instead of in_array( %s, array_keys( %s ) ).',
			$stackPtr,
			'PreferArrayKeyExists',
			array( $needleContent, $arrayContent, $needleContent, $arrayContent )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $tokens, $stackPtr, $closeParen, $needleContent, $arrayContent );
		}
	}

	/**
	 * Find the next comma at the same parenthesis level.
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $tokens     The token stack.
	 * @param int   $start      Start position.
	 * @param int   $end        End position.
	 *
	 * @return false|int
	 */
	private function findNextCommaAtSameLevel( File $phpcsFile, array $tokens, $start, $end ) {
		$depth = 0;

		for ( $i = $start; $i < $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_OPEN_PARENTHESIS ) {
				++$depth;
			} elseif ( $tokens[ $i ]['code'] === T_CLOSE_PARENTHESIS ) {
				--$depth;
			} elseif ( $tokens[ $i ]['code'] === T_COMMA && 0 === $depth ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Apply the fix.
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param array  $tokens        The token stack.
	 * @param int    $stackPtr      Start of in_array.
	 * @param int    $closeParen    Close parenthesis of in_array.
	 * @param string $needleContent The needle argument content.
	 * @param string $arrayContent  The array argument content.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, array $tokens, $stackPtr, $closeParen, $needleContent, $arrayContent ) {
		$phpcsFile->fixer->beginChangeset();

		// Replace the entire in_array(...) with array_key_exists(...).
		$replacement = 'array_key_exists( ' . $needleContent . ', ' . $arrayContent . ' )';

		// Remove all tokens from in_array to closing paren.
		for ( $i = $stackPtr; $i <= $closeParen; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Add the replacement at the start position.
		$phpcsFile->fixer->addContent( $stackPtr - 1, $replacement );

		$phpcsFile->fixer->endChangeset();
	}
}
