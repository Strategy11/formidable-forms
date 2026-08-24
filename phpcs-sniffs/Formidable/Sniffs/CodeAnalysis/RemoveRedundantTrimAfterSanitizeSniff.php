<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RemoveRedundantTrimAfterSanitizeSniff
 *
 * Detects trim() wrappers around sanitizer functions that already trim
 * their values (such as sanitize_text_field()).
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Removes redundant trim() calls after sanitize_text_field().
 */
class RemoveRedundantTrimAfterSanitizeSniff implements Sniff {

	/**
	 * Sanitizer functions that already trim their result.
	 *
	 * @var string[]
	 */
	public $sanitizers = array(
		'sanitize_text_field',
		'sanitize_textarea_field',
	);

	/** {@inheritDoc} */
	public function register() {
		return array( T_STRING );
	}

	/** {@inheritDoc} */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'trim' ) {
			return;
		}

		$openParen = $phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true );

		if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] ) {
			return;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		$argStart = $phpcsFile->findNext( Tokens::$emptyTokens, $openParen + 1, $closeParen, true );

		if ( false === $argStart ) {
			return;
		}

		$functionInfo = $this->getFunctionName( $phpcsFile, $argStart, $closeParen );

		if ( false === $functionInfo ) {
			return;
		}

		list( $sanitizerName, $nameEnd, $sanitizerOpen ) = $functionInfo;
		$normalized = ltrim( strtolower( $sanitizerName ), '\\' );

		if ( ! in_array( $normalized, $this->sanitizers, true ) ) {
			return;
		}

		if ( ! isset( $tokens[ $sanitizerOpen ]['parenthesis_closer'] ) ) {
			return;
		}

		$sanitizerClose = $tokens[ $sanitizerOpen ]['parenthesis_closer'];

		$afterSanitizer = $phpcsFile->findNext( Tokens::$emptyTokens, $sanitizerClose + 1, $closeParen, true );

		if ( false !== $afterSanitizer ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Redundant trim() around %s(). Remove the trim() call.',
			$stackPtr,
			'RedundantTrim',
			array( $normalized )
		);

		if ( true !== $fix ) {
			return;
		}

		$phpcsFile->fixer->beginChangeset();

		for ( $i = $stackPtr; $i < $argStart; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		for ( $i = $sanitizerClose + 1; $i <= $closeParen; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Extracts the function name beginning at the provided pointer.
	 *
	 * @param File $phpcsFile File being scanned.
	 * @param int  $start     Pointer at start of function name.
	 * @param int  $limit     Upper bound for tokens.
	 *
	 * @return array|false Array of (name, nameEndPtr, openParenPtr) or false.
	 */
	private function getFunctionName( File $phpcsFile, $start, $limit ) {
		$tokens         = $phpcsFile->getTokens();
		$ptr            = $start;
		$name           = '';
		$nameEndPointer = null;

		while ( $ptr < $limit ) {
			$code = $tokens[ $ptr ]['code'];

			if ( T_STRING === $code || T_NS_SEPARATOR === $code ) {
				$name          .= $tokens[ $ptr ]['content'];
				$nameEndPointer = $ptr;
				++$ptr;
				continue;
			}

			break;
		}

		if ( '' === $name ) {
			return false;
		}

		$openParen = $phpcsFile->findNext( Tokens::$emptyTokens, $nameEndPointer + 1, $limit, true );

		if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] ) {
			return false;
		}

		return array( $name, $nameEndPointer, $openParen );
	}
}
