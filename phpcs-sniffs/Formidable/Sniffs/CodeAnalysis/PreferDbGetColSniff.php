<?php
/**
 * Sniff to detect database calls explicitly requesting column mode through get_var.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

declare(strict_types=1);

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Detects and fixes database calls explicitly requesting column mode through get_var.
 */
class PreferDbGetColSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING );
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

		if ( 'wp_list_pluck' === strtolower( $tokens[ $stackPtr ]['content'] ) ) {
			$this->processPluck( $phpcsFile, $stackPtr );
			return;
		}

		if ( 'FrmDb' !== $tokens[ $stackPtr ]['content'] ) {
			return;
		}
		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_NS_SEPARATOR, T_OBJECT_OPERATOR, T_DOUBLE_COLON ), true ) ) {
			return;
		}
		$colon = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		$name  = false !== $colon ? $phpcsFile->findNext( T_WHITESPACE, $colon + 1, null, true ) : false;

		if ( false === $name || T_DOUBLE_COLON !== $tokens[ $colon ]['code'] || 'get_var' !== strtolower( $tokens[ $name ]['content'] ) ) {
			return;
		}
		$open = $phpcsFile->findNext( T_WHITESPACE, $name + 1, null, true );

		if ( false === $open || ! isset( $tokens[ $open ]['parenthesis_closer'] ) ) {
			return;
		}
		$args = PassedParameters::getParameters( $phpcsFile, $name );

		if ( 6 !== count( $args ) || ! isset( $args[6] ) || ! in_array( $args[6]['raw'], array( "'col'", '"col"' ), true ) ) {
			return;
		}
		$close  = $tokens[ $open ]['parenthesis_closer'];
		$source = $phpcsFile->getTokensAsString( $open, $close - $open + 1 );

		if ( preg_match( '/\.\.\.|\/\*|\/\/|#|\b[a-zA-Z_]\w*\s*:(?!:)/', $source ) ) {
			return;
		}
		$replacement = 'FrmDb::get_col( ' . implode( ', ', array_column( array_slice( $args, 0, 5 ), 'raw' ) ) . ' )';
		$fix         = $phpcsFile->addFixableError( 'Use FrmDb::get_col() instead of get_var() in column mode.', $stackPtr, 'Found' );

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $stackPtr, $replacement );

			for ( $i = $stackPtr + 1; $i <= $close; ++$i ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
			$phpcsFile->fixer->endChangeset();
		}
	}
	/**
	 * Replaces a direct extraction from a query selecting that same single column.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of wp_list_pluck().
	 *
	 * @return void
	 */
	private function processPluck( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$prev   = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_DOUBLE_COLON, T_OBJECT_OPERATOR, T_FUNCTION, T_NS_SEPARATOR ), true ) ) {
			return;
		}
		$open = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $open || ! isset( $tokens[ $open ]['parenthesis_closer'] ) ) {
			return;
		}
		$args = PassedParameters::getParameters( $phpcsFile, $stackPtr );

		if ( 2 !== count( $args ) || ! isset( $args[1], $args[2] ) || ! preg_match( '/^([\'\"])([a-zA-Z_][a-zA-Z0-9_]*)\\1$/', $args[2]['raw'], $field ) ) {
			return;
		}
		$inner = $phpcsFile->findNext( T_WHITESPACE, $args[1]['start'], $args[1]['end'] + 1, true );

		if ( false === $inner || 'FrmDb' !== $tokens[ $inner ]['content'] ) {
			return;
		}
		$colon = $phpcsFile->findNext( T_WHITESPACE, $inner + 1, null, true );
		$name  = false !== $colon ? $phpcsFile->findNext( T_WHITESPACE, $colon + 1, null, true ) : false;

		if ( false === $name || T_DOUBLE_COLON !== $tokens[ $colon ]['code'] || 'get_results' !== strtolower( $tokens[ $name ]['content'] ) ) {
			return;
		}
		$innerOpen = $phpcsFile->findNext( T_WHITESPACE, $name + 1, null, true );

		if ( false === $innerOpen || ! isset( $tokens[ $innerOpen ]['parenthesis_closer'] ) ) {
			return;
		}
		$innerClose = $tokens[ $innerOpen ]['parenthesis_closer'];
		$last       = $phpcsFile->findPrevious( T_WHITESPACE, $args[1]['end'], $inner, true );
		$innerArgs  = PassedParameters::getParameters( $phpcsFile, $name );

		if ( $last !== $innerClose || ! isset( $innerArgs[3] ) || count( $innerArgs ) > 4 || ! in_array( $innerArgs[3]['raw'], array( "'" . $field[2] . "'", '"' . $field[2] . '"' ), true ) ) {
			return;
		}
		$close  = $tokens[ $open ]['parenthesis_closer'];
		$source = $phpcsFile->getTokensAsString( $stackPtr, $close - $stackPtr + 1 );

		if ( preg_match( '/\.\.\.|\/\*|\/\/|#|\b[a-zA-Z_]\w*\s*:(?!:)/', $source ) ) {
			return;
		}
		$replacement = 'FrmDb::get_col( ' . implode( ', ', array_column( $innerArgs, 'raw' ) ) . ' )';
		$fix         = $phpcsFile->addFixableError( 'Use FrmDb::get_col() when extracting the only selected column.', $stackPtr, 'PluckSingleColumn' );

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $stackPtr, $replacement );

			for ( $i = $stackPtr + 1; $i <= $close; ++$i ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
			$phpcsFile->fixer->endChangeset();
		}
	}
}
