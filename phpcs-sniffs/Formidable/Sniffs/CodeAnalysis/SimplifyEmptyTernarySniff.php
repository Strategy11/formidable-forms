<?php
/**
 * Sniff to simplify empty() ternaries with function parameters.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects empty($param) ? default : $param and converts to $param ? $param : default.
 *
 * Bad:
 * $prefix = empty( $name ) ? 'item_meta' : $name;
 *
 * Good:
 * $prefix = $name ? $name : 'item_meta';
 *
 * This works because function parameters are always set, so empty() is redundant.
 */
class SimplifyEmptyTernarySniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_EMPTY );
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

		// Find the opening parenthesis after empty.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the closing parenthesis.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Get the variable inside empty().
		$varToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $varToken || $tokens[ $varToken ]['code'] !== T_VARIABLE ) {
			return;
		}

		$variableName = $tokens[ $varToken ]['content'];

		// Check if there's only the variable inside empty() (no array access, etc.).
		$nextInParen = $phpcsFile->findNext( T_WHITESPACE, $varToken + 1, $closeParen, true );

		if ( false !== $nextInParen ) {
			// There's something else inside empty(), skip.
			return;
		}

		// Find the ternary operator after empty().
		$ternaryOp = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false === $ternaryOp || $tokens[ $ternaryOp ]['code'] !== T_INLINE_THEN ) {
			return;
		}

		// Find the colon.
		$colonOp = $phpcsFile->findNext( T_INLINE_ELSE, $ternaryOp + 1 );

		if ( false === $colonOp ) {
			return;
		}

		// Get the "then" part (between ? and :).
		$thenStart = $phpcsFile->findNext( T_WHITESPACE, $ternaryOp + 1, $colonOp, true );

		if ( false === $thenStart ) {
			return;
		}

		// Find the end of the ternary (semicolon or other terminator).
		$ternaryEnd = $phpcsFile->findNext( array( T_SEMICOLON, T_COMMA, T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET ), $colonOp + 1 );

		if ( false === $ternaryEnd ) {
			return;
		}

		// Get the "else" part (between : and end).
		$elseStart = $phpcsFile->findNext( T_WHITESPACE, $colonOp + 1, $ternaryEnd, true );

		if ( false === $elseStart ) {
			return;
		}

		// Check if the else part is just the same variable.
		if ( $tokens[ $elseStart ]['code'] !== T_VARIABLE || $tokens[ $elseStart ]['content'] !== $variableName ) {
			return;
		}

		// Check there's nothing else in the else part.
		$nextInElse = $phpcsFile->findNext( T_WHITESPACE, $elseStart + 1, $ternaryEnd, true );

		if ( false !== $nextInElse ) {
			// There's something else in the else part, skip.
			return;
		}

		// Get the default value (the "then" part).
		$defaultValue = $phpcsFile->getTokensAsString( $thenStart, $colonOp - $thenStart );
		$defaultValue = trim( $defaultValue );

		$fix = $phpcsFile->addFixableError(
			'Simplify empty( %s ) ? %s : %s to %s ? %s : %s.',
			$stackPtr,
			'SimplifyEmptyTernary',
			array( $variableName, $defaultValue, $variableName, $variableName, $variableName, $defaultValue )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove everything from empty to the end of the ternary.
			for ( $i = $stackPtr; $i < $ternaryEnd; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Add the new simplified ternary.
			$phpcsFile->fixer->addContentBefore( $ternaryEnd, $variableName . ' ? ' . $variableName . ' : ' . $defaultValue );

			$phpcsFile->fixer->endChangeset();
		}
	}
}
