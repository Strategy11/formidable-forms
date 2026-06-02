<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantIssetBeforeNotEmptySniff
 *
 * Detects redundant isset() checks before ! empty() on the same variable.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects and removes redundant isset() before ! empty() checks.
 *
 * Bad:
 * isset( $var ) && ! empty( $var )
 * isset( $array['key'] ) && ! empty( $array['key'] )
 *
 * Good:
 * ! empty( $var )
 * ! empty( $array['key'] )
 *
 * The empty() function already checks if a variable is set, so isset() is redundant.
 */
class RedundantIssetBeforeNotEmptySniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_ISSET );
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

		// Get the opening parenthesis of isset().
		$issetOpen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $issetOpen || $tokens[ $issetOpen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Get the closing parenthesis of isset().
		if ( ! isset( $tokens[ $issetOpen ]['parenthesis_closer'] ) ) {
			return;
		}

		$issetClose = $tokens[ $issetOpen ]['parenthesis_closer'];

		// Get the variable expression inside isset().
		$issetVarContent = $this->getParenthesesContent( $phpcsFile, $issetOpen, $issetClose );

		// Find the && operator after isset().
		$andOperator = $phpcsFile->findNext( T_WHITESPACE, $issetClose + 1, null, true );

		if ( false === $andOperator || $tokens[ $andOperator ]['code'] !== T_BOOLEAN_AND ) {
			return;
		}

		// Find the ! (boolean not) after &&.
		$notOperator = $phpcsFile->findNext( T_WHITESPACE, $andOperator + 1, null, true );

		if ( false === $notOperator || $tokens[ $notOperator ]['code'] !== T_BOOLEAN_NOT ) {
			return;
		}

		// Find empty() after the !.
		$emptyToken = $phpcsFile->findNext( T_WHITESPACE, $notOperator + 1, null, true );

		if ( false === $emptyToken || $tokens[ $emptyToken ]['code'] !== T_EMPTY ) {
			return;
		}

		// Get the opening parenthesis of empty().
		$emptyOpen = $phpcsFile->findNext( T_WHITESPACE, $emptyToken + 1, null, true );

		if ( false === $emptyOpen || $tokens[ $emptyOpen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Get the closing parenthesis of empty().
		if ( ! isset( $tokens[ $emptyOpen ]['parenthesis_closer'] ) ) {
			return;
		}

		$emptyClose = $tokens[ $emptyOpen ]['parenthesis_closer'];

		// Get the variable expression inside empty().
		$emptyVarContent = $this->getParenthesesContent( $phpcsFile, $emptyOpen, $emptyClose );

		// Check if the variables match.
		if ( $issetVarContent !== $emptyVarContent ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Redundant isset() before ! empty(). The empty() function already checks if a variable is set. Use "! empty( %s )" instead.',
			$stackPtr,
			'Found',
			array( $issetVarContent )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove isset( $var ) &&.
			// Find the end of the && operator (including trailing whitespace).
			$removeEnd = $phpcsFile->findNext( T_WHITESPACE, $andOperator + 1, null, true );

			// Remove all tokens from isset to just before the !.
			for ( $i = $stackPtr; $i < $removeEnd; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Get the content inside parentheses (excluding the parentheses themselves).
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The position of the opening parenthesis.
	 * @param int  $closeParen The position of the closing parenthesis.
	 *
	 * @return string
	 */
	private function getParenthesesContent( File $phpcsFile, $openParen, $closeParen ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			// Skip whitespace for comparison purposes.
			if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
				$content .= $tokens[ $i ]['content'];
			}
		}

		return $content;
	}
}
