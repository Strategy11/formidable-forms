<?php
/**
 * Sniff to detect HTML attribute helper calls echoed instead of using echo mode.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

declare(strict_types=1);

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Detects and fixes HTML attribute helper calls echoed instead of using echo mode.
 */
class PreferHtmlParamsEchoModeSniff implements Sniff {

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
		$method = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $method || 'FrmAppHelper' !== $tokens[ $method ]['content'] ) {
			return;
		}
		$colon = $phpcsFile->findNext( T_WHITESPACE, $method + 1, null, true );
		$name  = false !== $colon ? $phpcsFile->findNext( T_WHITESPACE, $colon + 1, null, true ) : false;

		if ( false === $name || T_DOUBLE_COLON !== $tokens[ $colon ]['code'] || 'array_to_html_params' !== strtolower( $tokens[ $name ]['content'] ) ) {
			return;
		}
		$open = $phpcsFile->findNext( T_WHITESPACE, $name + 1, null, true );

		if ( false === $open || ! isset( $tokens[ $open ]['parenthesis_closer'] ) ) {
			return;
		}
		$close = $tokens[ $open ]['parenthesis_closer'];
		$end   = $phpcsFile->findNext( T_WHITESPACE, $close + 1, null, true );

		if ( false === $end || ! in_array( $tokens[ $end ]['code'], array( T_SEMICOLON, T_CLOSE_TAG ), true ) ) {
			return;
		}
		$args = PassedParameters::getParameters( $phpcsFile, $name );

		if ( ! isset( $args[1] ) || array_keys( $args ) !== range( 1, count( $args ) ) || count( $args ) > 2 || ( isset( $args[2] ) && 'false' !== strtolower( $args[2]['raw'] ) ) ) {
			return;
		}

		if ( preg_match( '/\.\.\.|\/\*|\/\/|#|\b[a-zA-Z_]\w*\s*:(?!:)/', $args[1]['raw'] ) ) {
			return;
		}
		$fix = $phpcsFile->addFixableError( 'Use array_to_html_params() with its echo argument set to true.', $stackPtr, 'Found' );

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $stackPtr, 'FrmAppHelper::array_to_html_params( ' . $args[1]['raw'] . ', true )' );

			for ( $i = $stackPtr + 1; $i <= $close; ++$i ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
			$phpcsFile->fixer->endChangeset();
		}
	}
}
