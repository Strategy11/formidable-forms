<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferStrcasecmpSniff
 *
 * Detects comparisons that call strtolower()/strtoupper() on both sides
 * and recommends strcasecmp() instead.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Encourages using strcasecmp() for case-insensitive comparisons.
 */
class PreferStrcasecmpSniff implements Sniff {

	/**
	 * Supported case conversion functions.
	 *
	 * @var array
	 */
	private $caseFunctions = array(
		'strtolower',
		'strtoupper',
	);

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		return array(
			T_IS_EQUAL,
			T_IS_NOT_EQUAL,
			T_IS_IDENTICAL,
			T_IS_NOT_IDENTICAL,
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack.
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$leftCall = $this->getCaseConversionCallOnLeft( $phpcsFile, $stackPtr );

		if ( false === $leftCall ) {
			return;
		}

		$rightCall = $this->getCaseConversionCallOnRight( $phpcsFile, $stackPtr );

		if ( false === $rightCall ) {
			return;
		}

		// Require both sides to use the same function to avoid false positives.
		if ( $leftCall['function'] !== $rightCall['function'] ) {
			return;
		}

		$tokens       = $phpcsFile->getTokens();
		$operatorCode = $tokens[ $stackPtr ]['code'];

		$fix = $phpcsFile->addFixableError(
			'Use strcasecmp() for case-insensitive comparisons instead of %s() on both sides.',
			$stackPtr,
			'Found',
			array( $leftCall['function'] )
		);

		if ( true !== $fix ) {
			return;
		}

		$replacement = $this->buildStrcasecmpComparison(
			$leftCall['argument'],
			$rightCall['argument'],
			$operatorCode
		);

		$phpcsFile->fixer->beginChangeset();

		$phpcsFile->fixer->replaceToken( $leftCall['start'], $replacement );

		for ( $i = $leftCall['start'] + 1; $i <= $rightCall['end']; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Build the replacement comparison using strcasecmp().
	 *
	 * @param string $leftArgument  Original left argument.
	 * @param string $rightArgument Original right argument.
	 * @param int    $operatorCode  Comparison operator token code.
	 *
	 * @return string
	 */
	private function buildStrcasecmpComparison( $leftArgument, $rightArgument, $operatorCode ) {
		$operator = in_array( $operatorCode, array( T_IS_EQUAL, T_IS_IDENTICAL ), true ) ? '===' : '!==';

		return sprintf(
			'0 %s strcasecmp( %s, %s )',
			$operator,
			$leftArgument,
			$rightArgument
		);
	}

	/**
	 * Locate a case conversion function call on the left side of the operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The operator position.
	 *
	 * @return array|false
	 */
	private function getCaseConversionCallOnLeft( File $phpcsFile, $stackPtr ) {
		$tokens  = $phpcsFile->getTokens();
		$current = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		while ( false !== $current ) {
			if ( $tokens[ $current ]['code'] !== T_CLOSE_PARENTHESIS ) {
				return false;
			}

			$call = $this->buildCallFromClosingParenthesis( $phpcsFile, $current );

			if ( false !== $call ) {
				return $call;
			}

			if ( ! isset( $tokens[ $current ]['parenthesis_opener'] ) ) {
				return false;
			}

			$current = $phpcsFile->findPrevious(
				T_WHITESPACE,
				$tokens[ $current ]['parenthesis_opener'] - 1,
				null,
				true
			);
		}

		return false;
	}

	/**
	 * Locate a case conversion function call on the right side of the operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The operator position.
	 *
	 * @return array|false
	 */
	private function getCaseConversionCallOnRight( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$start  = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		while ( false !== $start && $tokens[ $start ]['code'] === T_OPEN_PARENTHESIS ) {
			if ( ! isset( $tokens[ $start ]['parenthesis_closer'] ) ) {
				return false;
			}

			$start = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );
		}

		if ( false === $start ) {
			return false;
		}

		// Allow for leading namespace separators.
		if ( $tokens[ $start ]['code'] === T_NS_SEPARATOR ) {
			$start = $phpcsFile->findNext( T_STRING, $start + 1 );
		}

		if ( false === $start || $tokens[ $start ]['code'] !== T_STRING ) {
			return false;
		}

		return $this->buildCallFromFunctionPointer( $phpcsFile, $start );
	}

	/**
	 * Build call data when we know the closing parenthesis pointer.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $closePtr  The closing parenthesis token.
	 *
	 * @return array|false
	 */
	private function buildCallFromClosingParenthesis( File $phpcsFile, $closePtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $closePtr ]['parenthesis_opener'] ) ) {
			return false;
		}

		$openPtr     = $tokens[ $closePtr ]['parenthesis_opener'];
		$functionPtr = $phpcsFile->findPrevious( T_WHITESPACE, $openPtr - 1, null, true );

		if ( false === $functionPtr ) {
			return false;
		}

		if ( $tokens[ $functionPtr ]['code'] !== T_STRING ) {
			return false;
		}

		return $this->buildCallInfo( $phpcsFile, $functionPtr, $openPtr, $closePtr );
	}

	/**
	 * Build call data from the function name pointer.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $functionPtr The T_STRING token containing the function name.
	 *
	 * @return array|false
	 */
	private function buildCallFromFunctionPointer( File $phpcsFile, $functionPtr ) {
		$tokens = $phpcsFile->getTokens();
		$open   = $phpcsFile->findNext( T_WHITESPACE, $functionPtr + 1, null, true );

		if ( false === $open || $tokens[ $open ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		if ( ! isset( $tokens[ $open ]['parenthesis_closer'] ) ) {
			return false;
		}

		$close = $tokens[ $open ]['parenthesis_closer'];

		return $this->buildCallInfo( $phpcsFile, $functionPtr, $open, $close );
	}

	/**
	 * Build the normalized call information if the function is supported.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $functionPtr The T_STRING token for the function.
	 * @param int  $openPtr     The opening parenthesis.
	 * @param int  $closePtr    The closing parenthesis.
	 *
	 * @return array|false
	 */
	private function buildCallInfo( File $phpcsFile, $functionPtr, $openPtr, $closePtr ) {
		$tokens   = $phpcsFile->getTokens();
		$function = strtolower( $tokens[ $functionPtr ]['content'] );

		if ( ! in_array( $function, $this->caseFunctions, true ) ) {
			return false;
		}

		$start = $this->findFunctionStart( $phpcsFile, $functionPtr );

		$beforeStart = $phpcsFile->findPrevious( T_WHITESPACE, $start - 1, null, true );

		if ( false !== $beforeStart && in_array( $tokens[ $beforeStart ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON ), true ) ) {
			return false;
		}

		$argument = trim(
			$phpcsFile->getTokensAsString(
				$openPtr + 1,
				$closePtr - $openPtr - 1
			)
		);

		if ( '' === $argument ) {
			return false;
		}

		return array(
			'function' => $function,
			'start'    => $start,
			'end'      => $closePtr,
			'argument' => $argument,
		);
	}

	/**
	 * Find the start of the fully-qualified function name.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $functionPtr The T_STRING token for the function.
	 *
	 * @return int
	 */
	private function findFunctionStart( File $phpcsFile, $functionPtr ) {
		$tokens = $phpcsFile->getTokens();
		$start  = $functionPtr;

		while ( true ) {
			$previous = $phpcsFile->findPrevious( T_WHITESPACE, $start - 1, null, true );

			if ( false === $previous ) {
				break;
			}

			if ( ! in_array( $tokens[ $previous ]['code'], array( T_STRING, T_NS_SEPARATOR ), true ) ) {
				break;
			}

			$start = $previous;
		}

		return $start;
	}
}
