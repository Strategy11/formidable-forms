<?php
/**
 * Sniff to detect adjacent PHP blocks containing only whitespace between tags.
 *
 * Multiline HTML whitespace and closing braces are left alone to avoid changing output or scope.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

declare(strict_types=1);

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Detects and fixes adjacent PHP blocks containing only whitespace between tags.
 */
class MergeAdjacentPhpBlocksSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_CLOSE_TAG );
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
		$open   = $stackPtr + 1;
		$text   = '';

		while ( isset( $tokens[ $open ] ) && T_INLINE_HTML === $tokens[ $open ]['code'] ) {
			$text .= $tokens[ $open ]['content'];
			++$open;
		}

		if ( ! isset( $tokens[ $open ] ) || T_OPEN_TAG !== $tokens[ $open ]['code'] || ! in_array( $text, array( '', ' ' ), true ) ) {
			return;
		}
		$previous = $phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true );

		if ( false === $previous || in_array( $tokens[ $previous ]['code'], array( T_CLOSE_CURLY_BRACKET, T_OPEN_CURLY_BRACKET, T_COLON, T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO ), true ) ) {
			return;
		}
		// Short echo tags need their echo keyword restored, so leave those blocks alone.
		$tag = $phpcsFile->findPrevious( array( T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO ), $stackPtr - 1 );

		if ( false === $tag || T_OPEN_TAG_WITH_ECHO === $tokens[ $tag ]['code'] ) {
			return;
		}

		// Embedded PHP formatting can add whitespace inside attributes, so leave tags intact.
		$html    = '';
		$htmlPtr = $phpcsFile->findPrevious( T_INLINE_HTML, $tag - 1 );

		while ( false !== $htmlPtr ) {
			$html    = $tokens[ $htmlPtr ]['content'] . $html;
			$less    = strrpos( $html, '<' );
			$greater = strrpos( $html, '>' );

			if ( false !== $less || false !== $greater ) {
				if ( false !== $less && ( false === $greater || $less > $greater ) ) {
					return;
				}
				break;
			}
			$htmlPtr = $phpcsFile->findPrevious( T_INLINE_HTML, $htmlPtr - 1 );
		}

		for ( $i = $previous + 1; $i < $stackPtr; ++$i ) {
			if ( T_WHITESPACE !== $tokens[ $i ]['code'] ) {
				return;
			}
		}
		$separator = in_array( $tokens[ $previous ]['code'], array( T_SEMICOLON, T_COLON, T_OPEN_CURLY_BRACKET, T_OPEN_TAG ), true ) ? '' : ';';
		$prefix    = $phpcsFile->getTokensAsString( 0, $stackPtr );
		$lineStart = strrpos( $prefix, "\n" );
		$line      = false === $lineStart ? $prefix : substr( $prefix, $lineStart + 1 );
		preg_match( '/^[\t ]*/', $line, $matches );
		$indent      = $matches[0];
		$replacement = $separator . $phpcsFile->eolChar . $indent;

		if ( '' !== $text ) {
			// Inline whitespace is output, unlike the newline consumed by a closing tag.
			$replacement .= "echo ' ';" . $phpcsFile->eolChar . $indent;
		}
		$last = $open;

		while ( isset( $tokens[ $last + 1 ] ) && T_WHITESPACE === $tokens[ $last + 1 ]['code'] ) {
			++$last;
		}
		$fix = $phpcsFile->addFixableError( 'Merge adjacent PHP blocks while preserving their output.', $stackPtr, 'Found' );

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $previous + 1, $replacement );

			for ( $i = $previous + 2; $i <= $last; ++$i ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
			$phpcsFile->fixer->endChangeset();
		}
	}
}
