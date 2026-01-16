<?php
/**
 * Sniff to simplify redundant empty() checks after other checks on the same variable.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects patterns like check($x) && !empty($x) that can be simplified.
 *
 * Since the first check already confirms the variable is set, the empty() check
 * is redundant for checking if it's set. We can simplify to just checking
 * the truthiness of the variable.
 *
 * Bad:
 * if ( is_array( $x ) && ! empty( $x ) ) {
 * if ( is_string( $x ) && ! empty( $x ) ) {
 * if ( isset( $x ) && ! empty( $x ) ) {
 *
 * Good:
 * if ( is_array( $x ) && $x ) {
 * if ( is_string( $x ) && $x ) {
 * if ( isset( $x ) && $x ) {
 */
class RedundantEmptyAfterTypeCheckSniff implements Sniff {

	/**
	 * Functions that confirm a variable is set.
	 *
	 * @var array
	 */
	private $checkFunctions = array(
		'is_array',
		'is_string',
		'is_int',
		'is_integer',
		'is_float',
		'is_double',
		'is_bool',
		'is_object',
		'is_numeric',
		'is_callable',
		'is_resource',
		'is_iterable',
		'is_countable',
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING, T_ISSET );
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

		// Check if this is a recognized check function or isset.
		$funcName = $tokens[ $stackPtr ]['content'];

		if ( $tokens[ $stackPtr ]['code'] === T_ISSET ) {
			$funcName = 'isset';
		} elseif ( ! in_array( $funcName, $this->checkFunctions, true ) ) {
			return;
		}

		// Find the opening parenthesis.
		$checkOpenParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $checkOpenParen || $tokens[ $checkOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $checkOpenParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$checkCloseParen = $tokens[ $checkOpenParen ]['parenthesis_closer'];

		// Get the argument to the check function.
		$checkArg = $this->getArgumentString( $phpcsFile, $checkOpenParen + 1, $checkCloseParen );

		if ( empty( $checkArg ) ) {
			return;
		}

		// Look for && after the check.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $checkCloseParen + 1, null, true );

		// Check if we're inside a larger parenthesized expression.
		if ( false !== $nextToken && $tokens[ $nextToken ]['code'] === T_CLOSE_PARENTHESIS ) {
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $nextToken + 1, null, true );
		}

		if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_BOOLEAN_AND ) {
			return;
		}

		// Look for ! empty( ... ) after &&.
		$afterAnd = $phpcsFile->findNext( T_WHITESPACE, $nextToken + 1, null, true );

		if ( false === $afterAnd ) {
			return;
		}

		// Check for negation.
		if ( $tokens[ $afterAnd ]['code'] !== T_BOOLEAN_NOT ) {
			return;
		}

		$emptyToken = $phpcsFile->findNext( T_WHITESPACE, $afterAnd + 1, null, true );

		if ( false === $emptyToken || $tokens[ $emptyToken ]['code'] !== T_EMPTY ) {
			return;
		}

		// Find the opening parenthesis of empty.
		$emptyOpenParen = $phpcsFile->findNext( T_WHITESPACE, $emptyToken + 1, null, true );

		if ( false === $emptyOpenParen || $tokens[ $emptyOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $emptyOpenParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$emptyCloseParen = $tokens[ $emptyOpenParen ]['parenthesis_closer'];

		// Get the argument to empty.
		$emptyArg = $this->getArgumentString( $phpcsFile, $emptyOpenParen + 1, $emptyCloseParen );

		if ( empty( $emptyArg ) ) {
			return;
		}

		// Check if the arguments match.
		if ( $checkArg !== $emptyArg ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Redundant empty() after %s(). Use "%s( %s ) && %s" instead of "%s( %s ) && ! empty( %s )".',
			$emptyToken,
			'RedundantEmpty',
			array( $funcName, $funcName, $checkArg, $checkArg, $funcName, $checkArg, $checkArg )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove "! empty( " and keep just the argument content.
			// Remove the "! " (negation and space after it).
			$phpcsFile->fixer->replaceToken( $afterAnd, '' );

			// Remove whitespace between ! and empty.
			for ( $i = $afterAnd + 1; $i < $emptyToken; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Remove "empty".
			$phpcsFile->fixer->replaceToken( $emptyToken, '' );

			// Remove whitespace and opening paren.
			for ( $i = $emptyToken + 1; $i <= $emptyOpenParen; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Remove the first whitespace inside the parenthesis (after open paren).
			$firstArgToken = $phpcsFile->findNext( T_WHITESPACE, $emptyOpenParen + 1, $emptyCloseParen, true );

			if ( false !== $firstArgToken ) {
				for ( $i = $emptyOpenParen + 1; $i < $firstArgToken; $i++ ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}

			// Remove whitespace before closing paren and the closing paren itself.
			$lastArgToken = $phpcsFile->findPrevious( T_WHITESPACE, $emptyCloseParen - 1, $emptyOpenParen, true );

			if ( false !== $lastArgToken ) {
				for ( $i = $lastArgToken + 1; $i <= $emptyCloseParen; $i++ ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Get the argument string from between parentheses.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position (after open paren).
	 * @param int  $end       End position (close paren).
	 *
	 * @return string
	 */
	private function getArgumentString( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();
		$arg    = '';

		for ( $i = $start; $i < $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			$arg .= $tokens[ $i ]['content'];
		}

		return $arg;
	}
}
