<?php
/**
 * Sniff to detect conditional HTML attributes printed without their WordPress helpers.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

declare(strict_types=1);

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Detects and fixes conditional HTML attributes printed without their WordPress helpers.
 */
class PreferWpAttributeHelpersSniff implements Sniff {

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
		$start  = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		$end    = $phpcsFile->findNext( array( T_SEMICOLON, T_CLOSE_TAG ), $stackPtr + 1 );

		if ( false === $start || false === $end ) {
			return;
		}
		$tag  = $phpcsFile->findPrevious( T_OPEN_TAG, $stackPtr - 1 );
		$html = false !== $tag ? $phpcsFile->findPrevious( T_INLINE_HTML, $tag - 1 ) : false;

		if ( false === $html || ! preg_match( '/<(?:input|option|select|textarea|button|optgroup|fieldset)\\b[^<>]*$/i', $tokens[ $html ]['content'], $htmlMatch ) ) {
			return;
		}

		// The helper prints an attribute, so never replace text inside a quoted value.
		if ( ! preg_match( '/^(?:[^\'\"]|\'[^\']*\'|"[^"]*")*$/s', $htmlMatch[0] ) ) {
			return;
		}

		$source = trim( $phpcsFile->getTokensAsString( $start, $end - $start ) );
		// An exact echo statement avoids changing strings assembled for other purposes.
		$pattern = '~^([^?;]+)\?\s*([\'\"])((checked|selected|disabled|readonly)(?:=\"\\4\")?)\\2\s*:\s*([\'\"])\\5$~s';

		if ( ! preg_match( $pattern, $source, $match ) || preg_match( '/\/\*|\/\/|#|\?>/', $source ) ) {
			return;
		}
		$unsafe = Tokens::$assignmentTokens + array_fill_keys( array( T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR, T_YIELD, T_YIELD_FROM, T_PRINT, T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE, T_THROW, T_CLOSURE, T_FN ), true );

		if ( false !== $phpcsFile->findNext( array_keys( $unsafe ), $start, $end ) ) {
			return;
		}
		$condition   = trim( $match[1] );
		$helper      = 'readonly' === $match[4] ? 'wp_readonly' : $match[4];
		$replacement = $helper . '( (bool) ( ' . $condition . ' ), true );';
		$fix         = $phpcsFile->addFixableError( 'Use %s() for the conditional HTML attribute.', $stackPtr, 'Found', array( $helper ) );

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $stackPtr, $replacement );
			$last = T_SEMICOLON === $tokens[ $end ]['code'] ? $end : $end - 1;

			for ( $i = $stackPtr + 1; $i <= $last; ++$i ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
			$phpcsFile->fixer->endChangeset();
		}
	}
}
