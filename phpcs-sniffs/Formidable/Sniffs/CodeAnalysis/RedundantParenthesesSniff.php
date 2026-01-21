<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantParenthesesSniff
 *
 * Detects redundant parentheses around simple expressions in assignments.
 * For example: $var = ( $a ?? $b ); can be simplified to $var = $a ?? $b;
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant parentheses around simple expressions in assignments.
 *
 * Bad:
 * $var = ( $post_values['key'] ?? $default );
 *
 * Good:
 * $var = $post_values['key'] ?? $default;
 */
class RedundantParenthesesSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_OPEN_PARENTHESIS );
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

		// Must have a closing parenthesis.
		if ( ! isset( $tokens[ $stackPtr ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $stackPtr ]['parenthesis_closer'];

		// Check what's before the opening parenthesis.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken ) {
			return;
		}

		// Check what's after the closing parenthesis.
		$afterClose = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false === $afterClose ) {
			return;
		}

		// Check for redundant parentheses around negated function calls in conditions.
		// Pattern: ( ! empty( $x ) ) or ( ! isset( $x ) ) within a larger condition.
		if ( $this->isRedundantNegatedFunctionCall( $phpcsFile, $stackPtr, $closeParen, $prevToken, $afterClose ) ) {
			$this->reportAndFix( $phpcsFile, $stackPtr, $closeParen );
			return;
		}

		// We only care about parentheses after an assignment operator, array arrow, or return.
		$validPrecedingTokens = array(
			T_EQUAL,
			T_DOUBLE_ARROW,
			T_COALESCE_EQUAL,
			T_PLUS_EQUAL,
			T_MINUS_EQUAL,
			T_CONCAT_EQUAL,
			T_RETURN,
		);

		if ( ! in_array( $tokens[ $prevToken ]['code'], $validPrecedingTokens, true ) ) {
			return;
		}

		// The expression should end with a semicolon, comma (for array elements), close parenthesis, or ternary operator.
		$validFollowingTokens = array(
			T_SEMICOLON,
			T_COMMA,
			T_CLOSE_PARENTHESIS,
			T_INLINE_THEN,
		);

		if ( ! in_array( $tokens[ $afterClose ]['code'], $validFollowingTokens, true ) ) {
			return;
		}

		// Check if the content inside is a simple expression (no complex operators that would need grouping).
		// Exception: if this is a direct assignment followed by semicolon, arithmetic is fine too.
		$isDirectAssignment = in_array( $tokens[ $prevToken ]['code'], array( T_EQUAL, T_PLUS_EQUAL, T_MINUS_EQUAL, T_CONCAT_EQUAL, T_COALESCE_EQUAL ), true )
			&& $tokens[ $afterClose ]['code'] === T_SEMICOLON;

		if ( ! $this->isSimpleExpression( $phpcsFile, $stackPtr, $closeParen, $isDirectAssignment ) ) {
			return;
		}

		$this->reportAndFix( $phpcsFile, $stackPtr, $closeParen );
	}

	/**
	 * Check if this is a redundant parentheses around a negated function call.
	 *
	 * Pattern: ( ! empty( $x ) ) or ( ! isset( $x ) ) or ( ! function( $x ) )
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The position of the opening parenthesis.
	 * @param int  $closeParen The position of the closing parenthesis.
	 * @param int  $prevToken  The token before the opening parenthesis.
	 * @param int  $afterClose The token after the closing parenthesis.
	 *
	 * @return bool
	 */
	private function isRedundantNegatedFunctionCall( File $phpcsFile, $openParen, $closeParen, $prevToken, $afterClose ) {
		$tokens = $phpcsFile->getTokens();

		// Must be preceded by a logical operator (&&, ||) or another open parenthesis.
		$validPreceding = array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_OPEN_PARENTHESIS );

		if ( ! in_array( $tokens[ $prevToken ]['code'], $validPreceding, true ) ) {
			return false;
		}

		// Must be followed by a logical operator (&&, ||) or a close parenthesis.
		$validFollowing = array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_CLOSE_PARENTHESIS );

		if ( ! in_array( $tokens[ $afterClose ]['code'], $validFollowing, true ) ) {
			return false;
		}

		// Check the content inside: should be ! followed by a function call with no other operators.
		$firstInside = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $firstInside ) {
			return false;
		}

		// Should start with !
		if ( $tokens[ $firstInside ]['code'] !== T_BOOLEAN_NOT ) {
			return false;
		}

		// Next should be a function call (empty, isset, or a T_STRING function).
		$funcToken = $phpcsFile->findNext( T_WHITESPACE, $firstInside + 1, $closeParen, true );

		if ( false === $funcToken ) {
			return false;
		}

		$validFuncTokens = array( T_EMPTY, T_ISSET, T_STRING );

		if ( ! in_array( $tokens[ $funcToken ]['code'], $validFuncTokens, true ) ) {
			return false;
		}

		// Find the function's opening parenthesis.
		$funcOpenParen = $phpcsFile->findNext( T_WHITESPACE, $funcToken + 1, $closeParen, true );

		if ( false === $funcOpenParen || $tokens[ $funcOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		if ( ! isset( $tokens[ $funcOpenParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$funcCloseParen = $tokens[ $funcOpenParen ]['parenthesis_closer'];

		// The function's closing paren should be followed only by whitespace until our outer closing paren.
		$afterFuncClose = $phpcsFile->findNext( T_WHITESPACE, $funcCloseParen + 1, $closeParen, true );

		// If there's anything else between the function close and our close, it's not a simple pattern.
		if ( false !== $afterFuncClose ) {
			return false;
		}

		return true;
	}

	/**
	 * Report the error and apply the fix.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The position of the opening parenthesis.
	 * @param int  $closeParen The position of the closing parenthesis.
	 *
	 * @return void
	 */
	private function reportAndFix( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		$fix = $phpcsFile->addFixableError(
			'Redundant parentheses around simple expression',
			$openParen,
			'Found'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove opening parenthesis.
			$phpcsFile->fixer->replaceToken( $openParen, '' );

			// Remove whitespace after opening parenthesis.
			$next = $openParen + 1;

			while ( $next < $closeParen && $tokens[ $next ]['code'] === T_WHITESPACE ) {
				$phpcsFile->fixer->replaceToken( $next, '' );
				++$next;
			}

			// Remove whitespace before closing parenthesis.
			$prev = $closeParen - 1;

			while ( $prev > $openParen && $tokens[ $prev ]['code'] === T_WHITESPACE ) {
				$phpcsFile->fixer->replaceToken( $prev, '' );
				--$prev;
			}

			// Remove closing parenthesis.
			$phpcsFile->fixer->replaceToken( $closeParen, '' );

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Check if the expression inside parentheses is simple enough that parentheses are redundant.
	 *
	 * A simple expression is one that:
	 * - Contains only a null coalescing operator (??)
	 * - Contains only a ternary operator
	 * - Is a single value/variable/function call
	 *
	 * @param File $phpcsFile          The file being scanned.
	 * @param int  $openParen          The position of the opening parenthesis.
	 * @param int  $closeParen         The position of the closing parenthesis.
	 * @param bool $allowArithmetic    Whether to allow arithmetic operators (for direct assignments).
	 *
	 * @return bool True if the expression is simple, false otherwise.
	 */
	private function isSimpleExpression( File $phpcsFile, $openParen, $closeParen, $allowArithmetic = false ) {
		$tokens = $phpcsFile->getTokens();

		// Count operators that would make this a complex expression.
		$hasCoalesce      = false;
		$hasTernary       = false;
		$hasLogicalOp     = false;
		$hasComparisonOp  = false;
		$hasArithmeticOp  = false;
		$nestedParenDepth = 0;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Track nested parentheses - we only care about the top level.
			if ( $code === T_OPEN_PARENTHESIS ) {
				++$nestedParenDepth;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				--$nestedParenDepth;
				continue;
			}

			// Skip tokens inside nested parentheses.
			if ( $nestedParenDepth > 0 ) {
				continue;
			}

			// Check for various operators.
			if ( $code === T_COALESCE ) {
				$hasCoalesce = true;
			} elseif ( $code === T_INLINE_THEN || $code === T_INLINE_ELSE ) {
				$hasTernary = true;
			} elseif ( in_array( $code, array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR ), true ) ) {
				$hasLogicalOp = true;
			} elseif ( $code === T_LOGICAL_XOR ) {
				// xor has unusual precedence, so parentheses are helpful for clarity.
				return false;
			} elseif ( in_array( $code, array( T_IS_EQUAL, T_IS_NOT_EQUAL, T_IS_IDENTICAL, T_IS_NOT_IDENTICAL, T_LESS_THAN, T_GREATER_THAN, T_IS_SMALLER_OR_EQUAL, T_IS_GREATER_OR_EQUAL, T_SPACESHIP ), true ) ) {
				$hasComparisonOp = true;
			} elseif ( in_array( $code, array( T_PLUS, T_MINUS, T_MULTIPLY, T_DIVIDE, T_MODULUS ), true ) ) {
				$hasArithmeticOp = true;
			}
		}

		// If there are arithmetic operators, it might need parentheses for precedence.
		// Exception: direct assignments don't need grouping.
		if ( $hasArithmeticOp && ! $allowArithmetic ) {
			return false;
		}

		// Logical and comparison operators don't need parentheses in a simple assignment.
		// The parentheses are only needed when there's an operator precedence issue outside.
		// Since we already checked that this is followed by ; or , the parentheses are redundant.
		return true;
	}
}
