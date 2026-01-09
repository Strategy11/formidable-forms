<?php
/**
 * Formidable_Sniffs_WhiteSpace_BlankLineBeforeReturnAfterBraceSniff
 *
 * Ensures there is a blank line before a return statement when the previous line ends with a closing brace.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures there is a blank line before a return statement when the previous line ends with a closing brace.
 *
 * Bad:
 * if ( $condition ) {
 *     return 'a';
 * }
 * return 'b';
 *
 * Good:
 * if ( $condition ) {
 *     return 'a';
 * }
 *
 * return 'b';
 */
class BlankLineBeforeReturnAfterBraceSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_RETURN );
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

		// Find the function/method this return belongs to.
		$functionToken = $this->findContainingFunction( $phpcsFile, $stackPtr );

		if ( false === $functionToken ) {
			return;
		}

		// Only enforce this rule if the function already uses blank lines for readability.
		if ( ! $this->functionHasBlankLines( $phpcsFile, $functionToken ) ) {
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

		// Check if the return is the first non-whitespace token on the line.
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
				// Keep looking for the actual last token on this line (don't break on first match).
				if ( null === $lastTokenOnPrevLine || $tokens[ $i ]['column'] > $tokens[ $lastTokenOnPrevLine ]['column'] ) {
					$lastTokenOnPrevLine = $i;
				}
			}
		}

		// If we couldn't find a token on the previous line, it's a blank line - we're good.
		if ( null === $lastTokenOnPrevLine ) {
			return;
		}

		// Check if the last token on the previous line is a closing brace.
		if ( $tokens[ $lastTokenOnPrevLine ]['code'] !== T_CLOSE_CURLY_BRACKET ) {
			return;
		}

		// We found a closing brace on the previous line followed by a return statement.
		// This is the error condition.
		$fix = $phpcsFile->addFixableError(
			'Expected blank line before return statement when previous line ends with closing brace',
			$stackPtr,
			'MissingBlankLine'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Add a newline before the first token on the current line.
			$phpcsFile->fixer->addNewlineBefore( $firstOnLine );

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Find the function or method that contains the given token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return int|false The position of the function token, or false if not found.
	 */
	private function findContainingFunction( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_FUNCTION || $tokens[ $i ]['code'] === T_CLOSURE ) {
				// Check if the stackPtr is within this function's scope.
				if ( isset( $tokens[ $i ]['scope_opener'], $tokens[ $i ]['scope_closer'] ) ) {
					if ( $stackPtr > $tokens[ $i ]['scope_opener'] && $stackPtr < $tokens[ $i ]['scope_closer'] ) {
						return $i;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if a function contains blank lines (lines with only whitespace).
	 *
	 * @param File $phpcsFile     The file being scanned.
	 * @param int  $functionToken The position of the function token.
	 *
	 * @return bool True if the function has blank lines, false otherwise.
	 */
	private function functionHasBlankLines( File $phpcsFile, $functionToken ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $functionToken ]['scope_opener'], $tokens[ $functionToken ]['scope_closer'] ) ) {
			return false;
		}

		$scopeOpener = $tokens[ $functionToken ]['scope_opener'];
		$scopeCloser = $tokens[ $functionToken ]['scope_closer'];

		// Build a map of which lines have non-whitespace content.
		$linesWithContent = array();
		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
				$linesWithContent[ $tokens[ $i ]['line'] ] = true;
			}
		}

		// Check if there are any lines between scope opener and closer that have no content.
		$startLine = $tokens[ $scopeOpener ]['line'] + 1;
		$endLine   = $tokens[ $scopeCloser ]['line'] - 1;

		for ( $line = $startLine; $line <= $endLine; $line++ ) {
			if ( ! isset( $linesWithContent[ $line ] ) ) {
				// This line has no non-whitespace content - it's a blank line.
				return true;
			}
		}

		return false;
	}
}
