<?php
/**
 * Sniff to detect redundant truthy checks before identical comparisons.
 *
 * Detects patterns like:
 * if ( $var && $var === 'value' )
 *
 * Since we're checking that $var equals something exactly, the truthy check is redundant.
 * Can be simplified to: if ( $var === 'value' )
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects and fixes redundant truthy checks before identical comparisons.
 */
class RedundantTruthyBeforeIdenticalSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_BOOLEAN_AND );
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token (the &&).
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the variable before the &&.
		$beforeAnd = $this->findExpressionBefore( $phpcsFile, $stackPtr );

		if ( false === $beforeAnd ) {
			return;
		}

		// The expression before && should be a simple variable (truthy check).
		if ( ! $this->isSimpleTruthyCheck( $phpcsFile, $beforeAnd ) ) {
			return;
		}

		$variableName = $this->getVariableName( $phpcsFile, $beforeAnd );

		if ( false === $variableName ) {
			return;
		}

		// Find the expression after the &&.
		$afterAnd = $this->findExpressionAfter( $phpcsFile, $stackPtr );

		if ( false === $afterAnd ) {
			return;
		}

		// Check if the expression after && is an identical comparison using the same variable.
		$comparisonInfo = $this->isIdenticalComparisonWithVariable( $phpcsFile, $afterAnd, $variableName );

		if ( false === $comparisonInfo ) {
			return;
		}

		// We found a redundant pattern!
		$fix = $phpcsFile->addFixableError(
			'Redundant truthy check before identical comparison. "%s && %s === ..." can be simplified to "%s === ..."',
			$beforeAnd['start'],
			'RedundantTruthyCheck',
			array( $variableName, $variableName, $variableName )
		);

		if ( $fix ) {
			$this->applyFix( $phpcsFile, $beforeAnd, $stackPtr );
		}
	}

	/**
	 * Find the expression before the && operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $andPtr    The position of the && token.
	 *
	 * @return array|false Array with 'start' and 'end' positions, or false.
	 */
	private function findExpressionBefore( File $phpcsFile, $andPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the first non-whitespace token before &&.
		$end = $phpcsFile->findPrevious( T_WHITESPACE, $andPtr - 1, null, true );

		if ( false === $end ) {
			return false;
		}

		// Walk backwards to find the start of this expression.
		// Stop at ( or another && or ||.
		$start       = $end;
		$parenDepth  = 0;
		$bracketDepth = 0;

		for ( $i = $end; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			// Track nested structures.
			if ( $code === T_CLOSE_PARENTHESIS ) {
				++$parenDepth;
			} elseif ( $code === T_OPEN_PARENTHESIS ) {
				if ( $parenDepth === 0 ) {
					// This is the opening paren of the if condition.
					break;
				}
				--$parenDepth;
			} elseif ( $code === T_CLOSE_SQUARE_BRACKET ) {
				++$bracketDepth;
			} elseif ( $code === T_OPEN_SQUARE_BRACKET ) {
				--$bracketDepth;
			}

			// Stop at logical operators at depth 0.
			if ( $parenDepth === 0 && $bracketDepth === 0 ) {
				if ( in_array( $code, array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR ), true ) ) {
					break;
				}
			}

			if ( $code !== T_WHITESPACE ) {
				$start = $i;
			}
		}

		return array(
			'start' => $start,
			'end'   => $end,
		);
	}

	/**
	 * Find the expression after the && operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $andPtr    The position of the && token.
	 *
	 * @return array|false Array with 'start' and 'end' positions, or false.
	 */
	private function findExpressionAfter( File $phpcsFile, $andPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the first non-whitespace token after &&.
		$start = $phpcsFile->findNext( T_WHITESPACE, $andPtr + 1, null, true );

		if ( false === $start ) {
			return false;
		}

		// Walk forward to find the end of this expression.
		// Stop at ) or another && or ||.
		$end          = $start;
		$parenDepth   = 0;
		$bracketDepth = 0;

		for ( $i = $start; $i < count( $tokens ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Track nested structures.
			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
			} elseif ( $code === T_CLOSE_PARENTHESIS ) {
				if ( $parenDepth === 0 ) {
					// This is the closing paren of the if condition.
					break;
				}
				--$parenDepth;
			} elseif ( $code === T_OPEN_SQUARE_BRACKET ) {
				++$bracketDepth;
			} elseif ( $code === T_CLOSE_SQUARE_BRACKET ) {
				--$bracketDepth;
			}

			// Stop at logical operators at depth 0.
			if ( $parenDepth === 0 && $bracketDepth === 0 ) {
				if ( in_array( $code, array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR ), true ) ) {
					break;
				}
			}

			if ( $code !== T_WHITESPACE ) {
				$end = $i;
			}
		}

		return array(
			'start' => $start,
			'end'   => $end,
		);
	}

	/**
	 * Check if the expression is a simple truthy check (just a variable).
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $expr      The expression info with 'start' and 'end'.
	 *
	 * @return bool
	 */
	private function isSimpleTruthyCheck( File $phpcsFile, $expr ) {
		$tokens = $phpcsFile->getTokens();

		// Collect non-whitespace tokens in the expression.
		$exprTokens = array();

		for ( $i = $expr['start']; $i <= $expr['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
				$exprTokens[] = $tokens[ $i ];
			}
		}

		// A simple truthy check is just a variable.
		if ( count( $exprTokens ) === 1 && $exprTokens[0]['code'] === T_VARIABLE ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the variable name from a simple truthy check expression.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $expr      The expression info with 'start' and 'end'.
	 *
	 * @return string|false
	 */
	private function getVariableName( File $phpcsFile, $expr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $expr['start']; $i <= $expr['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE ) {
				return $tokens[ $i ]['content'];
			}
		}

		return false;
	}

	/**
	 * Check if the expression is an identical comparison using the given variable.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $expr         The expression info with 'start' and 'end'.
	 * @param string $variableName The variable name to look for.
	 *
	 * @return bool
	 */
	private function isIdenticalComparisonWithVariable( File $phpcsFile, $expr, $variableName ) {
		$tokens = $phpcsFile->getTokens();

		// Look for === or == in the expression.
		$hasIdentical = false;
		$hasVariable  = false;

		for ( $i = $expr['start']; $i <= $expr['end']; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_IS_IDENTICAL || $code === T_IS_EQUAL ) {
				$hasIdentical = true;
			}

			if ( $code === T_VARIABLE && $tokens[ $i ]['content'] === $variableName ) {
				$hasVariable = true;
			}
		}

		return $hasIdentical && $hasVariable;
	}

	/**
	 * Apply the fix by removing the truthy check and the &&.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $beforeAnd The expression before &&.
	 * @param int   $andPtr    The position of the && token.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $beforeAnd, $andPtr ) {
		$tokens = $phpcsFile->getTokens();

		$phpcsFile->fixer->beginChangeset();

		// Remove the truthy check expression.
		for ( $i = $beforeAnd['start']; $i <= $beforeAnd['end']; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Remove the && and any whitespace around it.
		$phpcsFile->fixer->replaceToken( $andPtr, '' );

		// Remove whitespace between the truthy check and &&.
		for ( $i = $beforeAnd['end'] + 1; $i < $andPtr; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
		}

		// Remove whitespace after &&.
		$afterAnd = $andPtr + 1;
		while ( isset( $tokens[ $afterAnd ] ) && $tokens[ $afterAnd ]['code'] === T_WHITESPACE ) {
			$phpcsFile->fixer->replaceToken( $afterAnd, '' );
			++$afterAnd;
		}

		$phpcsFile->fixer->endChangeset();
	}
}
