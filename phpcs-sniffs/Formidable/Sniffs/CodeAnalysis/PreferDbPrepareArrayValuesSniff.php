<?php
/**
 * Sniff to detect equivalent inline SQL placeholder lists.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

declare(strict_types=1);

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects and fixes equivalent inline SQL placeholder lists.
 */
class PreferDbPrepareArrayValuesSniff implements Sniff {

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

		if ( 'implode' !== strtolower( $tokens[ $stackPtr ]['content'] ) ) {
			return;
		}
		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NS_SEPARATOR ), true ) ) {
			return;
		}
		$open = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $open || ! isset( $tokens[ $open ]['parenthesis_closer'] ) ) {
			return;
		}
		$close   = $tokens[ $open ]['parenthesis_closer'];
		$source  = $phpcsFile->getTokensAsString( $stackPtr, $close - $stackPtr + 1 );
		$pattern = '~^implode\s*\(\s*([\'\"]), \\1\s*,\s*array_fill\s*\(\s*0\s*,\s*count\s*\(\s*(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*\)\s*,\s*([\'\"])(%[dsf])\\3\s*\)\s*\)$~i';

		if ( ! preg_match( $pattern, $source, $match ) ) {
			return;
		}

		// Do not replace the implementation with a recursive call to itself.
		if ( 'FrmDb.php' === basename( $phpcsFile->getFilename() ) ) {
			return;
		}
		$replacement = 'FrmDb::prepare_array_values( ' . $match[2] . ", '" . $match[4] . "' )";
		$fix         = $phpcsFile->addFixableError( 'Use FrmDb::prepare_array_values() for SQL placeholder lists.', $stackPtr, 'Found' );

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
