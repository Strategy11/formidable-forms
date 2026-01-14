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

		// Check what's after the closing parenthesis.
		$afterClose = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false === $afterClose ) {
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

		$fix = $phpcsFile->addFixableError(
			'Redundant parentheses around simple expression',
			$stackPtr,
			'Found'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove opening parenthesis.
			$phpcsFile->fixer->replaceToken( $stackPtr, '' );

			// Remove whitespace after opening parenthesis.
			$next = $stackPtr + 1;

			while ( $next < $closeParen && $tokens[ $next ]['code'] === T_WHITESPACE ) {
				$phpcsFile->fixer->replaceToken( $next, '' );
				++$next;
			}

			// Remove whitespace before closing parenthesis.
			$prev = $closeParen - 1;

			while ( $prev > $stackPtr && $tokens[ $prev ]['code'] === T_WHITESPACE ) {
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
