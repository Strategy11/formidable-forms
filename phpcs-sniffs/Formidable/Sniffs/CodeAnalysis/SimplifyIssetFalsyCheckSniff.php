<?php
/**
 * Formidable_Sniffs_CodeAnalysis_SimplifyIssetFalsyCheckSniff
 *
 * Detects patterns like `! isset( $x ) || ! $x` that can be simplified to `empty( $x )`.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects patterns that can be simplified using empty().
 *
 * Bad:
 * if ( ! isset( $arr['key'] ) || ! $arr['key'] ) { ... }
 *
 * Good:
 * if ( empty( $arr['key'] ) ) { ... }
 */
class SimplifyIssetFalsyCheckSniff implements Sniff {

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

		// Check if isset is negated with "!".
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken || $tokens[ $prevToken ]['code'] !== T_BOOLEAN_NOT ) {
			return;
		}

		$notToken = $prevToken;

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

		// Find the || operator after isset().
		$orOperator = $phpcsFile->findNext( T_WHITESPACE, $issetCloseParen + 1, null, true );

		if ( false === $orOperator || $tokens[ $orOperator ]['code'] !== T_BOOLEAN_OR ) {
			return;
		}

		// Find the "!" after ||.
		$secondNot = $phpcsFile->findNext( T_WHITESPACE, $orOperator + 1, null, true );

		if ( false === $secondNot || $tokens[ $secondNot ]['code'] !== T_BOOLEAN_NOT ) {
			return;
		}

		// Find the variable after the second "!".
		$secondVarStart = $phpcsFile->findNext( T_WHITESPACE, $secondNot + 1, null, true );

		if ( false === $secondVarStart ) {
			return;
		}

		// Get the second expression and compare with the first.
		$secondContent = $this->getExpressionContent( $phpcsFile, $secondVarStart );

		if ( empty( $secondContent ) || $secondContent['content'] !== $issetContent['content'] ) {
			return;
		}

		// We have a match! Report the error.
		$fix = $phpcsFile->addFixableError(
			'Simplify "! isset( %s ) || ! %s" to "empty( %s )"',
			$stackPtr,
			'Found',
			array( $issetContent['content'], $secondContent['content'], $issetContent['content'] )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove the "!" before isset.
			$phpcsFile->fixer->replaceToken( $notToken, '' );

			// Remove whitespace between "!" and "isset".
			for ( $i = $notToken + 1; $i < $stackPtr; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}

			// Replace "isset" with "empty".
			$phpcsFile->fixer->replaceToken( $stackPtr, 'empty' );

			// Remove everything from after isset's closing paren to end of second expression.
			$endToken = $secondContent['end'];

			for ( $i = $issetCloseParen + 1; $i <= $endToken; $i++ ) {
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

	/**
	 * Get an expression starting from a given token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  The starting position.
	 *
	 * @return array|false Array with 'content', 'start', 'end' keys, or false on failure.
	 */
	private function getExpressionContent( File $phpcsFile, $startPtr ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';
		$end     = $startPtr;

		// Valid tokens in an expression like $arr['key'].
		$validTokens = array(
			T_VARIABLE,
			T_STRING,
			T_CONSTANT_ENCAPSED_STRING,
			T_LNUMBER,
			T_DNUMBER,
			T_OPEN_SQUARE_BRACKET,
			T_CLOSE_SQUARE_BRACKET,
			T_OBJECT_OPERATOR,
			T_NULLSAFE_OBJECT_OPERATOR,
			T_DOUBLE_COLON,
		);

		$bracketDepth = 0;

		for ( $i = $startPtr; $i < count( $tokens ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Skip whitespace.
			if ( $code === T_WHITESPACE ) {
				continue;
			}

			// Track bracket depth.
			if ( $code === T_OPEN_SQUARE_BRACKET ) {
				++$bracketDepth;
				$content .= $tokens[ $i ]['content'];
				$end      = $i;
				continue;
			}

			if ( $code === T_CLOSE_SQUARE_BRACKET ) {
				--$bracketDepth;
				$content .= $tokens[ $i ]['content'];
				$end      = $i;
				continue;
			}

			// If we're inside brackets, accept more token types.
			if ( $bracketDepth > 0 ) {
				$content .= $tokens[ $i ]['content'];
				$end      = $i;
				continue;
			}

			// Outside brackets, only accept valid expression tokens.
			if ( ! in_array( $code, $validTokens, true ) ) {
				break;
			}

			$content .= $tokens[ $i ]['content'];
			$end      = $i;
		}

		if ( empty( $content ) ) {
			return false;
		}

		return array(
			'content' => $content,
			'start'   => $startPtr,
			'end'     => $end,
		);
	}
}
