<?php
/**
 * Formidable_Sniffs_CodeAnalysis_SimplifyIssetEmptyCheckSniff
 *
 * Detects patterns like `isset( $x ) && empty( $x )` that should use `! $x` instead of `empty( $x )`.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant empty() after isset() on the same variable.
 *
 * Bad:
 * if ( isset( $values[ $opt ] ) && empty( $values[ $opt ] ) ) { ... }
 *
 * Good:
 * if ( isset( $values[ $opt ] ) && ! $values[ $opt ] ) { ... }
 *
 * Since isset() already confirms the variable exists, empty() is redundant.
 * We should just use a falsy check instead.
 */
class SimplifyIssetEmptyCheckSniff implements Sniff {

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

		// Find the opening parenthesis after isset.
		$issetOpenParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $issetOpenParen || $tokens[ $issetOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $issetOpenParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$issetCloseParen = $tokens[ $issetOpenParen ]['parenthesis_closer'];

		// Get the variable/expression inside isset().
		$issetContent = $this->getParenthesesContent( $phpcsFile, $issetOpenParen, $issetCloseParen );

		if ( empty( $issetContent ) ) {
			return;
		}

		// Find the && operator after isset().
		$andOperator = $phpcsFile->findNext( T_WHITESPACE, $issetCloseParen + 1, null, true );

		if ( false === $andOperator || $tokens[ $andOperator ]['code'] !== T_BOOLEAN_AND ) {
			return;
		}

		// Find empty() after &&.
		$emptyToken = $phpcsFile->findNext( T_WHITESPACE, $andOperator + 1, null, true );

		if ( false === $emptyToken || $tokens[ $emptyToken ]['code'] !== T_EMPTY ) {
			return;
		}

		// Find the opening parenthesis after empty.
		$emptyOpenParen = $phpcsFile->findNext( T_WHITESPACE, $emptyToken + 1, null, true );

		if ( false === $emptyOpenParen || $tokens[ $emptyOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $emptyOpenParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$emptyCloseParen = $tokens[ $emptyOpenParen ]['parenthesis_closer'];

		// Get the variable/expression inside empty().
		$emptyContent = $this->getParenthesesContent( $phpcsFile, $emptyOpenParen, $emptyCloseParen );

		if ( empty( $emptyContent ) ) {
			return;
		}

		// Check if the variables match.
		if ( $emptyContent['content'] !== $issetContent['content'] ) {
			return;
		}

		// We have a match! Report the error.
		$fix = $phpcsFile->addFixableError(
			'Simplify "isset( %s ) && empty( %s )" to "isset( %s ) && ! %s"',
			$emptyToken,
			'Found',
			array( $issetContent['content'], $emptyContent['content'], $issetContent['content'], $issetContent['content'] )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Replace "empty" with "! $variable" (the whole expression).
			$phpcsFile->fixer->replaceToken( $emptyToken, '! ' . $emptyContent['content'] );

			// Remove everything from after "empty" to end of closing paren.
			for ( $i = $emptyToken + 1; $i <= $emptyCloseParen; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Get the content inside parentheses as a normalized string.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The position of the opening parenthesis.
	 * @param int  $closeParen The position of the closing parenthesis.
	 *
	 * @return array|false Array with 'content', 'start', 'end' keys, or false on failure.
	 */
	private function getParenthesesContent( File $phpcsFile, $openParen, $closeParen ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';
		$start   = null;
		$end     = null;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			if ( null === $start ) {
				$start = $i;
			}

			$end      = $i;
			$content .= $tokens[ $i ]['content'];
		}

		if ( empty( $content ) ) {
			return false;
		}

		return array(
			'content' => $content,
			'start'   => $start,
			'end'     => $end,
		);
	}
}
