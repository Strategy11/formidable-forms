<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RemoveRedundantWpdbPrefixSniff
 *
 * Detects redundant $wpdb->prefix usage in FrmDb function calls.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects and removes redundant $wpdb->prefix in FrmDb::get_var, FrmDb::get_col, and FrmDb::get_results calls.
 *
 * Bad:
 * FrmDb::get_var( $wpdb->prefix . 'frm_forms', $where );
 *
 * Good:
 * FrmDb::get_var( 'frm_forms', $where );
 *
 * The FrmDb functions automatically add $wpdb->prefix when the table name is a simple string without spaces.
 */
class RemoveRedundantWpdbPrefixSniff implements Sniff {

	/**
	 * Target FrmDb methods.
	 *
	 * @var array
	 */
	private $targetMethods = array(
		'get_var',
		'get_col',
		'get_results',
	);

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

		// Check if this is "FrmDb".
		if ( $tokens[ $stackPtr ]['content'] !== 'FrmDb' ) {
			return;
		}

		// Find the :: operator.
		$doubleColon = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $doubleColon || $tokens[ $doubleColon ]['code'] !== T_DOUBLE_COLON ) {
			return;
		}

		// Find the method name.
		$methodToken = $phpcsFile->findNext( T_WHITESPACE, $doubleColon + 1, null, true );

		if ( false === $methodToken || $tokens[ $methodToken ]['code'] !== T_STRING ) {
			return;
		}

		$methodName = $tokens[ $methodToken ]['content'];

		if ( ! in_array( $methodName, $this->targetMethods, true ) ) {
			return;
		}

		// Find the opening parenthesis.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $methodToken + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Check the first argument for $wpdb->prefix . 'simple_string' pattern.
		$firstArgStart = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, null, true );

		if ( false === $firstArgStart ) {
			return;
		}

		// Look for $wpdb.
		if ( $tokens[ $firstArgStart ]['code'] !== T_VARIABLE || $tokens[ $firstArgStart ]['content'] !== '$wpdb' ) {
			return;
		}

		// Find the -> operator.
		$objectOp = $phpcsFile->findNext( T_WHITESPACE, $firstArgStart + 1, null, true );

		if ( false === $objectOp || $tokens[ $objectOp ]['code'] !== T_OBJECT_OPERATOR ) {
			return;
		}

		// Find "prefix".
		$prefixToken = $phpcsFile->findNext( T_WHITESPACE, $objectOp + 1, null, true );

		if ( false === $prefixToken || $tokens[ $prefixToken ]['code'] !== T_STRING || $tokens[ $prefixToken ]['content'] !== 'prefix' ) {
			return;
		}

		// Find the . concatenation operator.
		$concatOp = $phpcsFile->findNext( T_WHITESPACE, $prefixToken + 1, null, true );

		if ( false === $concatOp || $tokens[ $concatOp ]['code'] !== T_STRING_CONCAT ) {
			return;
		}

		// Find the string literal.
		$stringToken = $phpcsFile->findNext( T_WHITESPACE, $concatOp + 1, null, true );

		if ( false === $stringToken || $tokens[ $stringToken ]['code'] !== T_CONSTANT_ENCAPSED_STRING ) {
			return;
		}

		// Get the string value (without quotes).
		$stringValue = substr( $tokens[ $stringToken ]['content'], 1, -1 );

		// Check if it's a simple string (no spaces).
		if ( strpos( $stringValue, ' ' ) !== false ) {
			return;
		}

		// Check that the next token after the string is either a comma or closing paren (simple first arg).
		$afterString = $phpcsFile->findNext( T_WHITESPACE, $stringToken + 1, null, true );

		if ( false === $afterString ) {
			return;
		}

		if ( $tokens[ $afterString ]['code'] !== T_COMMA && $tokens[ $afterString ]['code'] !== T_CLOSE_PARENTHESIS ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Redundant $wpdb->prefix in FrmDb::%s() call. The function adds the prefix automatically for simple table names. Use "%s" instead.',
			$firstArgStart,
			'Found',
			array( $methodName, $stringValue )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove $wpdb->prefix . and keep just the string.
			for ( $i = $firstArgStart; $i <= $concatOp; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Also remove any whitespace between concat and string.
			$nextNonWhitespace = $phpcsFile->findNext( T_WHITESPACE, $concatOp + 1, $stringToken, true );

			if ( false === $nextNonWhitespace ) {
				for ( $i = $concatOp + 1; $i < $stringToken; $i++ ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}

			$phpcsFile->fixer->endChangeset();
		}
	}
}
