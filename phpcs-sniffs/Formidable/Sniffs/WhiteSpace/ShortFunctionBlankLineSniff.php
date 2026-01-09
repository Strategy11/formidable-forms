<?php
/**
 * Sniff to detect unnecessary blank lines in short functions.
 *
 * If a function body has only 2 statements with a blank line between them,
 * the blank line should be removed.
 *
 * @package Formidable\Sniffs\WhiteSpace
 */

namespace Formidable\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects and removes unnecessary blank lines in short functions.
 */
class ShortFunctionBlankLineSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FUNCTION );
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

		// Make sure this function has a body.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$openBrace  = $tokens[ $stackPtr ]['scope_opener'];
		$closeBrace = $tokens[ $stackPtr ]['scope_closer'];

		// Get the line numbers.
		$openLine  = $tokens[ $openBrace ]['line'];
		$closeLine = $tokens[ $closeBrace ]['line'];

		// Calculate the number of lines in the function body.
		// For a 3-line function with a blank line:
		// Line 1: open brace (openLine)
		// Line 2: first statement
		// Line 3: blank
		// Line 4: second statement (return)
		// Line 5: close brace (closeLine)
		// So closeLine - openLine = 4
		$totalLines = $closeLine - $openLine;

		if ( $totalLines !== 4 ) {
			return;
		}

		// Count the number of statements (semicolons) in the function.
		$statementCount = 0;
		$semicolons     = array();

		for ( $i = $openBrace + 1; $i < $closeBrace; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_SEMICOLON ) {
				++$statementCount;
				$semicolons[] = $i;
			}
		}

		// We only care about functions with exactly 2 statements.
		if ( $statementCount !== 2 ) {
			return;
		}

		// Check if there's a blank line between the two statements.
		$firstSemicolon  = $semicolons[0];
		$secondStatement = $phpcsFile->findNext( T_WHITESPACE, $firstSemicolon + 1, $closeBrace, true );

		if ( false === $secondStatement ) {
			return;
		}

		$firstLine  = $tokens[ $firstSemicolon ]['line'];
		$secondLine = $tokens[ $secondStatement ]['line'];

		// If there's exactly one blank line between them (2 line difference).
		if ( $secondLine - $firstLine !== 2 ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Unnecessary blank line in short function with only 2 statements. Remove the blank line.',
			$secondStatement,
			'BlankLineInShortFunction'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Get the indentation from the token before the second statement.
			$indent = '';

			if ( $tokens[ $secondStatement - 1 ]['code'] === T_WHITESPACE ) {
				$wsContent   = $tokens[ $secondStatement - 1 ]['content'];
				$lastNewline = strrpos( $wsContent, "\n" );

				if ( false !== $lastNewline ) {
					$indent = substr( $wsContent, $lastNewline + 1 );
				} else {
					$indent = $wsContent;
				}
			}

			// Replace all whitespace tokens between semicolon and next statement.
			$first = true;

			for ( $i = $firstSemicolon + 1; $i < $secondStatement; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					if ( $first ) {
						$phpcsFile->fixer->replaceToken( $i, "\n" . $indent );
						$first = false;
					} else {
						$phpcsFile->fixer->replaceToken( $i, '' );
					}
				}
			}

			$phpcsFile->fixer->endChangeset();
		}
	}
}
