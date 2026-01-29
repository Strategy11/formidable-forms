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
 * - echo esc_html__( ... ) -> esc_html_e( ... )
 * - echo esc_attr__( ... ) -> esc_attr_e( ... )
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
	 * Mapping of already combined functions that should drop echo entirely.
	 *
	 * @var array
	 */
	private $directReplacements = array(
		'esc_html__' => 'esc_html_e',
		'esc_attr__' => 'esc_attr_e',
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

		if ( isset( $this->directReplacements[ $escapeFunc ] ) ) {
			$this->processDirectCombinedFunction( $phpcsFile, $stackPtr, $nextToken, $escapeFunc );
			return;
		}

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
			$this->replaceEchoWithCombinedFunction( $phpcsFile, $stackPtr, $semicolon, $replacementFunc, $translateArgs );
		}
	}

	/**
	 * Handle echo statements that already use combined translation helpers.
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param int    $stackPtr        Pointer to the echo token.
	 * @param int    $functionPointer Pointer to the combined function token.
	 * @param string $functionName    The function currently in use (e.g. esc_html__).
	 *
	 * @return void
	 */
	private function processDirectCombinedFunction( File $phpcsFile, $stackPtr, $functionPointer, $functionName ) {
		$tokens = $phpcsFile->getTokens();

		$openParen = $phpcsFile->findNext( T_WHITESPACE, $functionPointer + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		$semicolon = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false === $semicolon || $tokens[ $semicolon ]['code'] !== T_SEMICOLON ) {
			return;
		}

		$args            = $phpcsFile->getTokensAsString( $openParen + 1, $closeParen - $openParen - 1 );
		$replacementFunc = $this->directReplacements[ $functionName ];

		$fix = $phpcsFile->addFixableError(
			'Use %s() instead of echo %s().',
			$stackPtr,
			'PreferCombinedFunctionDirect',
			array( $replacementFunc, $functionName )
		);

		if ( true === $fix ) {
			$this->replaceEchoWithCombinedFunction( $phpcsFile, $stackPtr, $semicolon, $replacementFunc, $args );
		}
	}

	/**
	 * Replace an echo statement with the combined helper.
	 *
	 * @param File   $phpcsFile      The file being scanned.
	 * @param int    $stackPtr       Pointer to the echo token.
	 * @param int    $semicolonPtr   Pointer to the semicolon.
	 * @param string $replacement    Function name to use.
	 * @param string $argumentString Function arguments as a string.
	 *
	 * @return void
	 */
	private function replaceEchoWithCombinedFunction( File $phpcsFile, $stackPtr, $semicolonPtr, $replacement, $argumentString ) {
		$phpcsFile->fixer->beginChangeset();

		for ( $i = $stackPtr; $i <= $semicolonPtr; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		$phpcsFile->fixer->addContent( $stackPtr, $replacement . '( ' . trim( $argumentString ) . ' );' );

		$phpcsFile->fixer->endChangeset();
	}
}
