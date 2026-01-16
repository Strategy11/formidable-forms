<?php
/**
 * Sniff to convert echo esc_*( __( or _x( ) to the combined function.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects echo with escape and translation functions and suggests combined alternatives.
 *
 * Conversions:
 * - echo esc_html( __( ... ) ) -> esc_html_e( ... )
 * - echo esc_attr( __( ... ) ) -> esc_attr_e( ... )
 * - echo esc_html( _x( ... ) ) -> esc_html_x( ... )
 * - echo esc_attr( _x( ... ) ) -> esc_attr_x( ... )
 */
class PreferEscHtmlESniff implements Sniff {

	/**
	 * Mapping of escape functions and translation functions to their combined equivalents.
	 *
	 * @var array
	 */
	private $replacements = array(
		'esc_html' => array(
			'__' => 'esc_html_e',
			'_x' => 'esc_html_x',
		),
		'esc_attr' => array(
			'__' => 'esc_attr_e',
			'_x' => 'esc_attr_x',
		),
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_ECHO );
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

		// Find the next non-whitespace token after echo.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken ) {
			return;
		}

		// Check if it's an escape function we handle.
		if ( $tokens[ $nextToken ]['code'] !== T_STRING ) {
			return;
		}

		$escapeFunc = $tokens[ $nextToken ]['content'];

		if ( ! isset( $this->replacements[ $escapeFunc ] ) ) {
			return;
		}

		$escapeFuncToken = $nextToken;

		// Find the opening parenthesis after the escape function.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $escapeFuncToken + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the first non-whitespace token inside the escape function.
		$insideToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, null, true );

		if ( false === $insideToken ) {
			return;
		}

		// Check if it's a translation function we handle.
		if ( $tokens[ $insideToken ]['code'] !== T_STRING ) {
			return;
		}

		$translateFunc = $tokens[ $insideToken ]['content'];

		if ( ! isset( $this->replacements[ $escapeFunc ][ $translateFunc ] ) ) {
			return;
		}

		$replacementFunc = $this->replacements[ $escapeFunc ][ $translateFunc ];
		$translateToken  = $insideToken;

		// Find the opening parenthesis after the translation function.
		$translateOpenParen = $phpcsFile->findNext( T_WHITESPACE, $translateToken + 1, null, true );

		if ( false === $translateOpenParen || $tokens[ $translateOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the closing parenthesis of the translation function.
		if ( ! isset( $tokens[ $translateOpenParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$translateCloseParen = $tokens[ $translateOpenParen ]['parenthesis_closer'];

		// Find the closing parenthesis of the escape function.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$escapeCloseParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Find the semicolon.
		$semicolon = $phpcsFile->findNext( T_WHITESPACE, $escapeCloseParen + 1, null, true );

		if ( false === $semicolon || $tokens[ $semicolon ]['code'] !== T_SEMICOLON ) {
			return;
		}

		// Get the arguments of the translation function.
		$translateArgs = $phpcsFile->getTokensAsString( $translateOpenParen + 1, $translateCloseParen - $translateOpenParen - 1 );

		$fix = $phpcsFile->addFixableError(
			'Use %s() instead of echo %s( %s() ).',
			$stackPtr,
			'PreferCombinedFunction',
			array( $replacementFunc, $escapeFunc, $translateFunc )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove everything from echo to the semicolon.
			for ( $i = $stackPtr; $i <= $semicolon; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Add the new combined function call.
			$phpcsFile->fixer->addContent( $stackPtr, $replacementFunc . '( ' . trim( $translateArgs ) . ' );' );

			$phpcsFile->fixer->endChangeset();
		}
	}
}
