<?php
/**
 * Formidable_Sniffs_CodeAnalysis_StrictComparisonForIntFunctionsSniff
 *
 * Enforces strict comparisons when comparing results of int-returning functions.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Enforces strict comparisons (=== or !==) when comparing strlen() or count() results to integers.
 *
 * Bad:
 * if ( strlen( $str ) == 0 ) { }
 * if ( count( $arr ) != 1 ) { }
 *
 * Good:
 * if ( strlen( $str ) === 0 ) { }
 * if ( count( $arr ) !== 1 ) { }
 */
class StrictComparisonForIntFunctionsSniff implements Sniff {

	/**
	 * Functions that always return integers.
	 *
	 * @var array
	 */
	private $intFunctions = array(
		'strlen',
		'count',
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(
			T_IS_EQUAL,
			T_IS_NOT_EQUAL,
		);
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

		// Check the left side of the comparison.
		$leftEnd   = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		$leftIsInt = $this->isIntExpression( $phpcsFile, $leftEnd );

		// Check the right side of the comparison.
		$rightStart = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		$rightIsInt = $this->isIntExpression( $phpcsFile, $rightStart );

		// We need one side to be an int function and the other to be an integer literal.
		$hasIntFunction = false;
		$hasIntLiteral  = false;

		if ( $leftIsInt === 'function' || $rightIsInt === 'function' ) {
			$hasIntFunction = true;
		}

		if ( $leftIsInt === 'literal' || $rightIsInt === 'literal' ) {
			$hasIntLiteral = true;
		}

		if ( ! $hasIntFunction || ! $hasIntLiteral ) {
			return;
		}

		$isEqual       = $tokens[ $stackPtr ]['code'] === T_IS_EQUAL;
		$replacement   = $isEqual ? '===' : '!==';
		$operatorName  = $isEqual ? '==' : '!=';

		$fix = $phpcsFile->addFixableError(
			'Use strict comparison (%s) when comparing int-returning functions like strlen() or count() to integers.',
			$stackPtr,
			'Found',
			array( $replacement )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->replaceToken( $stackPtr, $replacement );
		}
	}

	/**
	 * Check if an expression is an integer-returning function call or an integer literal.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $tokenPtr  The position of the token to check.
	 *
	 * @return string|false 'function' if it's an int function, 'literal' if it's an int literal, false otherwise.
	 */
	private function isIntExpression( File $phpcsFile, $tokenPtr ) {
		if ( false === $tokenPtr ) {
			return false;
		}

		$tokens = $phpcsFile->getTokens();

		// Check for integer literal.
		if ( $tokens[ $tokenPtr ]['code'] === T_LNUMBER ) {
			return 'literal';
		}

		// Check for closing parenthesis (could be end of function call).
		if ( $tokens[ $tokenPtr ]['code'] === T_CLOSE_PARENTHESIS ) {
			return $this->isIntFunctionCall( $phpcsFile, $tokenPtr );
		}

		// Check for function name followed by parenthesis (right side of comparison).
		if ( $tokens[ $tokenPtr ]['code'] === T_STRING ) {
			$funcName = strtolower( $tokens[ $tokenPtr ]['content'] );

			if ( in_array( $funcName, $this->intFunctions, true ) ) {
				$nextToken = $phpcsFile->findNext( T_WHITESPACE, $tokenPtr + 1, null, true );

				if ( false !== $nextToken && $tokens[ $nextToken ]['code'] === T_OPEN_PARENTHESIS ) {
					return 'function';
				}
			}
		}

		return false;
	}

	/**
	 * Check if a closing parenthesis belongs to an int-returning function call.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $closePtr  The position of the closing parenthesis.
	 *
	 * @return string|false 'function' if it's an int function call, false otherwise.
	 */
	private function isIntFunctionCall( File $phpcsFile, $closePtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $closePtr ]['parenthesis_opener'] ) ) {
			return false;
		}

		$openPtr = $tokens[ $closePtr ]['parenthesis_opener'];

		// Find the token before the opening parenthesis.
		$funcToken = $phpcsFile->findPrevious( T_WHITESPACE, $openPtr - 1, null, true );

		if ( false === $funcToken || $tokens[ $funcToken ]['code'] !== T_STRING ) {
			return false;
		}

		$funcName = strtolower( $tokens[ $funcToken ]['content'] );

		if ( in_array( $funcName, $this->intFunctions, true ) ) {
			return 'function';
		}

		return false;
	}
}
