<?php
/**
 * Formidable_Sniffs_WhiteSpace_BlankLineAfterClosingBraceSniff
 *
 * Ensures there is a blank line after a closing brace when followed by a variable assignment.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures there is a blank line after a closing brace when followed by a variable assignment.
 *
 * Bad:
 * if ( $condition ) {
 *     // code
 * }
 * $var = 123;
 *
 * Good:
 * if ( $condition ) {
 *     // code
 * }
 *
 * $var = 123;
 */
class BlankLineAfterClosingBraceSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_VARIABLE );
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

		// Check if this variable is at the start of a statement (assignment).
		// We need to verify this is an assignment by looking for an equals sign.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken ) {
			return;
		}

		// Check if the next non-whitespace token is an assignment operator.
		$assignmentTokens = array(
			T_EQUAL,
			T_PLUS_EQUAL,
			T_MINUS_EQUAL,
			T_MUL_EQUAL,
			T_DIV_EQUAL,
			T_CONCAT_EQUAL,
			T_MOD_EQUAL,
			T_AND_EQUAL,
			T_OR_EQUAL,
			T_XOR_EQUAL,
			T_SL_EQUAL,
			T_SR_EQUAL,
			T_POW_EQUAL,
			T_COALESCE_EQUAL,
		);

		if ( ! in_array( $tokens[ $nextToken ]['code'], $assignmentTokens, true ) ) {
			return;
		}

		// Find the first token on the current line.
		$firstOnLine = $stackPtr;

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $tokens[ $stackPtr ]['line'] ) {
				break;
			}
			$firstOnLine = $i;
		}

		// Check if the variable is the first non-whitespace token on the line.
		$firstNonWhitespace = $phpcsFile->findNext( T_WHITESPACE, $firstOnLine, $stackPtr + 1, true );

		if ( $firstNonWhitespace !== $stackPtr ) {
			return;
		}

		// Now find what's on the previous line.
		$currentLine  = $tokens[ $stackPtr ]['line'];
		$previousLine = $currentLine - 1;

		if ( $previousLine < 1 ) {
			return;
		}

		// Find the last non-whitespace token on the previous line.
		$lastTokenOnPrevLine = null;

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $previousLine ) {
				break;
			}

			if ( $tokens[ $i ]['line'] === $previousLine && $tokens[ $i ]['code'] !== T_WHITESPACE ) {
				$lastTokenOnPrevLine = $i;
				break;
			}
		}

		// If we couldn't find a token on the previous line, check if it's a blank line.
		if ( null === $lastTokenOnPrevLine ) {
			// The previous line is blank, so we're good.
			return;
		}

		// Check if the last token on the previous line is a closing brace.
		if ( $tokens[ $lastTokenOnPrevLine ]['code'] !== T_CLOSE_CURLY_BRACKET ) {
			return;
		}

		// We found a closing brace on the previous line followed by a variable assignment.
		// This is the error condition.
		$fix = $phpcsFile->addFixableError(
			'Expected blank line after closing brace before variable assignment',
			$stackPtr,
			'MissingBlankLine'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Find the first token on the current line and add a newline before it.
			$phpcsFile->fixer->addNewlineBefore( $firstOnLine );

			$phpcsFile->fixer->endChangeset();
		}
	}
}
