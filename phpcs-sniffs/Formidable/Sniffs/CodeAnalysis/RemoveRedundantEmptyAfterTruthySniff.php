<?php
/**
 * Sniff to remove redundant empty() checks when a variable is already being checked for truthiness.
 *
 * @package Formidable\Sniffs\CodeQuality
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects and fixes redundant empty() checks like:
 * - `if ( $var && ! empty( $var ) )` -> `if ( $var )`
 *
 * Only handles the case where the truthy check comes first.
 */
class RemoveRedundantEmptyAfterTruthySniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return array( T_BOOLEAN_AND );
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Get the left and right sides of the && operator.
		$leftSide  = $this->getLeftOperand( $phpcsFile, $stackPtr );
		$rightSide = $this->getRightOperand( $phpcsFile, $stackPtr );

		if ( null === $leftSide || null === $rightSide ) {
			return;
		}

		// Check for pattern: $var && ! empty( $var )
		$leftVar = $this->getSimpleVariable( $phpcsFile, $leftSide );

		if ( null !== $leftVar ) {
			$rightEmpty = $this->getNotEmptyVariable( $phpcsFile, $rightSide );

			if ( null !== $rightEmpty && $leftVar === $rightEmpty['variable'] ) {
				$this->reportAndFix(
					$phpcsFile,
					$stackPtr,
					$leftVar,
					$rightEmpty['start'],
					$rightEmpty['end'],
					'right'
				);

				return;
			}
		}

	}

	/**
	 * Report the error and fix if requested.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param int    $stackPtr     The position of the && token.
	 * @param string $variable     The variable name.
	 * @param int    $removeStart  Start position of tokens to remove.
	 * @param int    $removeEnd    End position of tokens to remove.
	 * @param string $side         Which side has the redundant check ('left' or 'right').
	 *
	 * @return void
	 */
	private function reportAndFix( File $phpcsFile, $stackPtr, $variable, $removeStart, $removeEnd, $side ) {
		$error = sprintf(
			'Redundant empty() check. "%s && ! empty( %s )" can be simplified to "%s".',
			$variable,
			$variable,
			$variable
		);

		$fix = $phpcsFile->addFixableError( $error, $stackPtr, 'RedundantEmptyCheck' );

		if ( $fix ) {
			$tokens = $phpcsFile->getTokens();
			$phpcsFile->fixer->beginChangeset();

			if ( 'right' === $side ) {
				// Remove && and the ! empty( $var ) part.
				// Find where to start removing (from the &&).
				for ( $i = $stackPtr; $i <= $removeEnd; $i++ ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}

				// Also remove any trailing whitespace before the closing paren.
				$next = $removeEnd + 1;

				if ( isset( $tokens[ $next ] ) && $tokens[ $next ]['code'] === T_WHITESPACE ) {
					$phpcsFile->fixer->replaceToken( $next, '' );
				}
			} else {
				// Remove the ! empty( $var ) && part.
				// Remove from the start of ! empty to the && (inclusive).
				for ( $i = $removeStart; $i <= $stackPtr; $i++ ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}

				// Also remove any leading whitespace after the &&.
				$next = $stackPtr + 1;

				if ( isset( $tokens[ $next ] ) && $tokens[ $next ]['code'] === T_WHITESPACE ) {
					$phpcsFile->fixer->replaceToken( $next, '' );
				}
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Get the left operand of a && operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the && token.
	 *
	 * @return array{start: int, end: int}|null The operand bounds or null.
	 */
	private function getLeftOperand( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the start of the left operand.
		$end = $stackPtr - 1;

		// Skip whitespace.
		while ( $end > 0 && $tokens[ $end ]['code'] === T_WHITESPACE ) {
			--$end;
		}

		if ( $end < 0 ) {
			return null;
		}

		// Handle closing parenthesis - find matching open.
		if ( $tokens[ $end ]['code'] === T_CLOSE_PARENTHESIS ) {
			if ( ! isset( $tokens[ $end ]['parenthesis_opener'] ) ) {
				return null;
			}

			$start = $tokens[ $end ]['parenthesis_opener'];

			// Check if there's a function name or ! before the paren.
			$before = $phpcsFile->findPrevious( T_WHITESPACE, $start - 1, null, true );

			if ( false !== $before ) {
				if ( $tokens[ $before ]['code'] === T_STRING ) {
					// Function call like empty($var).
					$start = $before;

					// Check for ! before the function.
					$beforeFunc = $phpcsFile->findPrevious( T_WHITESPACE, $start - 1, null, true );

					if ( false !== $beforeFunc && $tokens[ $beforeFunc ]['code'] === T_BOOLEAN_NOT ) {
						$start = $beforeFunc;
					}
				} elseif ( $tokens[ $before ]['code'] === T_BOOLEAN_NOT ) {
					$start = $before;
				}
			}

			return array(
				'start' => $start,
				'end'   => $end,
			);
		}

		// Simple variable.
		if ( $tokens[ $end ]['code'] === T_VARIABLE ) {
			return array(
				'start' => $end,
				'end'   => $end,
			);
		}

		return null;
	}

	/**
	 * Get the right operand of a && operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the && token.
	 *
	 * @return array{start: int, end: int}|null The operand bounds or null.
	 */
	private function getRightOperand( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the start of the right operand.
		$start = $stackPtr + 1;

		// Skip whitespace.
		while ( isset( $tokens[ $start ] ) && $tokens[ $start ]['code'] === T_WHITESPACE ) {
			++$start;
		}

		if ( ! isset( $tokens[ $start ] ) ) {
			return null;
		}

		// Handle ! empty( $var ).
		// Check for T_BOOLEAN_NOT (PHPCS token) or '!' content.
		$isBooleanNot = ( $tokens[ $start ]['code'] === T_BOOLEAN_NOT )
			|| ( $tokens[ $start ]['content'] === '!' );

		if ( $isBooleanNot ) {
			$funcName = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );

			if ( false !== $funcName && $tokens[ $funcName ]['code'] === T_EMPTY ) {
				$openParen = $phpcsFile->findNext( T_WHITESPACE, $funcName + 1, null, true );

				if ( false !== $openParen && $tokens[ $openParen ]['code'] === T_OPEN_PARENTHESIS ) {
					if ( isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
						return array(
							'start' => $start,
							'end'   => $tokens[ $openParen ]['parenthesis_closer'],
						);
					}
				}
			}
		}

		// Simple variable.
		if ( $tokens[ $start ]['code'] === T_VARIABLE ) {
			return array(
				'start' => $start,
				'end'   => $start,
			);
		}

		return null;
	}

	/**
	 * Check if an operand is a simple variable and return its name.
	 *
	 * @param File                        $phpcsFile The file being scanned.
	 * @param array{start: int, end: int} $operand   The operand bounds.
	 *
	 * @return string|null The variable name or null.
	 */
	private function getSimpleVariable( File $phpcsFile, $operand ) {
		$tokens = $phpcsFile->getTokens();

		if ( $operand['start'] !== $operand['end'] ) {
			return null;
		}

		if ( $tokens[ $operand['start'] ]['code'] !== T_VARIABLE ) {
			return null;
		}

		return $tokens[ $operand['start'] ]['content'];
	}

	/**
	 * Check if an operand is `! empty( $var )` and return the variable info.
	 *
	 * @param File                        $phpcsFile The file being scanned.
	 * @param array{start: int, end: int} $operand   The operand bounds.
	 *
	 * @return array{variable: string, start: int, end: int}|null The variable info or null.
	 */
	private function getNotEmptyVariable( File $phpcsFile, $operand ) {
		$tokens = $phpcsFile->getTokens();
		$start  = $operand['start'];
		$end    = $operand['end'];

		// Must start with ! (check both T_BOOLEAN_NOT and '!' content).
		$isBooleanNot = ( $tokens[ $start ]['code'] === T_BOOLEAN_NOT )
			|| ( $tokens[ $start ]['content'] === '!' );

		if ( ! $isBooleanNot ) {
			return null;
		}

		// Find 'empty'.
		$funcName = $phpcsFile->findNext( T_WHITESPACE, $start + 1, $end + 1, true );

		if ( false === $funcName || $tokens[ $funcName ]['code'] !== T_EMPTY ) {
			return null;
		}

		// Find opening paren.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $funcName + 1, $end + 1, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return null;
		}

		// Find the variable inside.
		$varToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $end, true );

		if ( false === $varToken || $tokens[ $varToken ]['code'] !== T_VARIABLE ) {
			return null;
		}

		// Make sure there's nothing else significant inside the parens.
		$afterVar = $phpcsFile->findNext( T_WHITESPACE, $varToken + 1, $end, true );

		if ( false !== $afterVar && $tokens[ $afterVar ]['code'] !== T_CLOSE_PARENTHESIS ) {
			// There's something else inside, like an array access.
			return null;
		}

		return array(
			'variable' => $tokens[ $varToken ]['content'],
			'start'    => $start,
			'end'      => $end,
		);
	}
}
