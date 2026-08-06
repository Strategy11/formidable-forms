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
use PHP_CodeSniffer\Util\Tokens;

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
	 * Detect parentheses that only wrap a simple comparison within a logical expression.
	 *
	 * Example: ( $start_year <= $year ) where the surrounding tokens are ||/&& or parentheses.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  Opening parenthesis token.
	 * @param int  $closeParen Closing parenthesis token.
	 * @param int  $prevToken  Token before the opening parenthesis.
	 * @param int  $afterClose Token after the closing parenthesis.
	 *
	 * @return bool
	 */
	private function isRedundantComparisonInLogicalExpression( File $phpcsFile, $openParen, $closeParen, $prevToken, $afterClose ) {
		$tokens = $phpcsFile->getTokens();

		$validPreceding = array(
			T_BOOLEAN_AND,
			T_BOOLEAN_OR,
			T_LOGICAL_AND,
			T_LOGICAL_OR,
			T_OPEN_PARENTHESIS,
		);

		if ( ! in_array( $tokens[ $prevToken ]['code'], $validPreceding, true ) ) {
			return false;
		}

		$validFollowing = array(
			T_BOOLEAN_AND,
			T_BOOLEAN_OR,
			T_LOGICAL_AND,
			T_LOGICAL_OR,
			T_CLOSE_PARENTHESIS,
			T_SEMICOLON,
			T_COMMA,
			T_INLINE_THEN,
		);

		if ( ! in_array( $tokens[ $afterClose ]['code'], $validFollowing, true ) ) {
			return false;
		}

		return $this->isSimpleComparisonExpression( $phpcsFile, $openParen, $closeParen );
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
		$prevToken = $phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true );

		if ( false === $prevToken ) {
			return;
		}

		// Check what's after the closing parenthesis.
		$afterClose = $phpcsFile->findNext( Tokens::$emptyTokens, $closeParen + 1, null, true );

		if ( false === $afterClose ) {
			return;
		}

		// Check for redundant parentheses around negated function calls in conditions.
		// Pattern: ( ! empty( $x ) ) or ( ! isset( $x ) ) within a larger condition.
		if ( $this->isRedundantNegatedFunctionCall( $phpcsFile, $stackPtr, $closeParen, $prevToken, $afterClose ) ) {
			$this->reportAndFix( $phpcsFile, $stackPtr, $closeParen );
			return;
		}

		// Check for redundant parentheses wrapping a standalone function call inside a logical expression.
		if ( $this->isRedundantFunctionCallInLogicalExpression( $phpcsFile, $stackPtr, $closeParen, $prevToken, $afterClose ) ) {
			$this->reportAndFix( $phpcsFile, $stackPtr, $closeParen );
			return;
		}

		// Check for redundant parentheses around a simple comparison inside a logical expression.
		if ( $this->isRedundantComparisonInLogicalExpression( $phpcsFile, $stackPtr, $closeParen, $prevToken, $afterClose ) ) {
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
		$validPreceding = array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR, T_OPEN_PARENTHESIS );

		if ( ! in_array( $tokens[ $prevToken ]['code'], $validPreceding, true ) ) {
			return false;
		}

		// Must be followed by a logical operator (&&, ||) or a close parenthesis.
		$validFollowing = array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR, T_CLOSE_PARENTHESIS );

		if ( ! in_array( $tokens[ $afterClose ]['code'], $validFollowing, true ) ) {
			return false;
		}

		// Check the content inside: should be ! followed by a function call with no other operators.
		$firstInside = $phpcsFile->findNext( Tokens::$emptyTokens, $openParen + 1, $closeParen, true );

		if ( false === $firstInside ) {
			return false;
		}

		// Check for simple comparison expression: ( array() === $var ) or ( $var === array() ).
		if ( $this->isSimpleComparisonExpression( $phpcsFile, $openParen, $closeParen ) ) {
			return true;
		}

		// Should start with !
		if ( $tokens[ $firstInside ]['code'] !== T_BOOLEAN_NOT ) {
			return false;
		}

		// Next should be a function call (empty, isset, or a T_STRING function).
		$funcToken = $phpcsFile->findNext( Tokens::$emptyTokens, $firstInside + 1, $closeParen, true );

		if ( false === $funcToken ) {
			return false;
		}

		$validFuncTokens = array( T_EMPTY, T_ISSET, T_STRING );

		if ( ! in_array( $tokens[ $funcToken ]['code'], $validFuncTokens, true ) ) {
			return false;
		}

		// Find the function's opening parenthesis.
		$funcOpenParen = $phpcsFile->findNext( Tokens::$emptyTokens, $funcToken + 1, $closeParen, true );

		if ( false === $funcOpenParen || $tokens[ $funcOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		if ( ! isset( $tokens[ $funcOpenParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$funcCloseParen = $tokens[ $funcOpenParen ]['parenthesis_closer'];

		// The function's closing paren should be followed only by whitespace until our outer closing paren.
		$afterFuncClose = $phpcsFile->findNext( Tokens::$emptyTokens, $funcCloseParen + 1, $closeParen, true );

		// If there's anything else between the function close and our close, it's not a simple pattern.
		if ( false !== $afterFuncClose ) {
			return false;
		}

		return true;
	}

	/**
	 * Check for redundant parentheses around a simple function (or static/object) call.
	 *
	 * Example: ( empty( $var ) ) when surrounded by logical operators.
	 *
	 * @param File $phpcsFile  File reference.
	 * @param int  $openParen  Position of opening parenthesis.
	 * @param int  $closeParen Closing parenthesis.
	 * @param int  $prevToken  Previous meaningful token.
	 * @param int  $afterClose Next meaningful token.
	 *
	 * @return bool
	 */
	private function isRedundantFunctionCallInLogicalExpression( File $phpcsFile, $openParen, $closeParen, $prevToken, $afterClose ) {
		$tokens = $phpcsFile->getTokens();

		$validPreceding = array(
			T_BOOLEAN_AND,
			T_BOOLEAN_OR,
			T_LOGICAL_AND,
			T_LOGICAL_OR,
			T_OPEN_PARENTHESIS,
		);

		if ( ! in_array( $tokens[ $prevToken ]['code'], $validPreceding, true ) ) {
			return false;
		}

		$validFollowing = array(
			T_BOOLEAN_AND,
			T_BOOLEAN_OR,
			T_LOGICAL_AND,
			T_LOGICAL_OR,
			T_CLOSE_PARENTHESIS,
			T_SEMICOLON,
			T_COMMA,
			T_INLINE_THEN,
		);

		if ( ! in_array( $tokens[ $afterClose ]['code'], $validFollowing, true ) ) {
			return false;
		}

		return $this->isSimpleFunctionCall( $phpcsFile, $openParen, $closeParen );
	}

	/**
	 * Check if this is a simple comparison expression that doesn't need parentheses.
	 *
	 * Pattern: ( array() === $var ) or ( $var === array() ) or similar simple comparisons.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The position of the opening parenthesis.
	 * @param int  $closeParen The position of the closing parenthesis.
	 *
	 * @return bool
	 */
	private function isSimpleComparisonExpression( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		// Look for exactly one comparison operator at the top level.
		$comparisonCount  = 0;
		$logicalCount     = 0;
		$nestedParenDepth = 0;
		$arithmeticCount  = 0;
		$hasTernary       = false;

		$comparisonTokens = array(
			T_IS_EQUAL,
			T_IS_NOT_EQUAL,
			T_IS_IDENTICAL,
			T_IS_NOT_IDENTICAL,
			T_IS_SMALLER_OR_EQUAL,
			T_IS_GREATER_OR_EQUAL,
		);

		$arithmeticTokens = array(
			T_PLUS,
			T_MINUS,
			T_MULTIPLY,
			T_DIVIDE,
			T_MODULUS,
		);

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			$code = $tokens[ $i ]['code'];

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

			// Count comparison operators.
			if ( in_array( $code, $comparisonTokens, true ) ) {
				++$comparisonCount;
			}

			// Check for logical operators - if present, parentheses might be needed.
			if ( in_array( $code, array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR ), true ) ) {
				++$logicalCount;
			}

			// Arithmetic on either side means the grouping might be required.
			if ( in_array( $code, $arithmeticTokens, true ) ) {
				++$arithmeticCount;
			}

			// Presence of a ternary operator means parentheses are required.
			if ( T_INLINE_THEN === $code || T_INLINE_ELSE === $code ) {
				$hasTernary = true;
			}
		}

		// Simple comparison: exactly one comparison operator and no logical operators/arithmetic/ternary.
		return 1 === $comparisonCount && 0 === $logicalCount && 0 === $arithmeticCount && false === $hasTernary;
	}

	/**
	 * Determine if the contents are exactly a function (or method/static) call.
	 *
	 * @param File $phpcsFile File reference.
	 * @param int  $openParen Opening parenthesis token.
	 * @param int  $closeParen Closing parenthesis token.
	 *
	 * @return bool
	 */
	private function isSimpleFunctionCall( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		$first = $phpcsFile->findNext( Tokens::$emptyTokens, $openParen + 1, $closeParen, true );

		if ( false === $first ) {
			return false;
		}

		$allowedCallableTokens = array(
			T_STRING,
			T_NS_SEPARATOR,
			T_DOUBLE_COLON,
			T_OBJECT_OPERATOR,
			T_VARIABLE,
			T_SELF,
			T_STATIC,
			T_PARENT,
			T_EMPTY,
			T_ISSET,
		);

		$callOpenParen = null;

		for ( $i = $first; $i < $closeParen; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( isset( Tokens::$emptyTokens[ $code ] ) ) {
				continue;
			}

			if ( T_OPEN_PARENTHESIS === $code ) {
				$callOpenParen = $i;
				break;
			}

			if ( ! in_array( $code, $allowedCallableTokens, true ) ) {
				return false;
			}
		}

		if ( null === $callOpenParen ) {
			return false;
		}

		if ( ! isset( $tokens[ $callOpenParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$callCloseParen = $tokens[ $callOpenParen ]['parenthesis_closer'];

		if ( $callCloseParen >= $closeParen ) {
			return false;
		}

		// Ensure nothing but whitespace remains between the function close and our close.
		$afterCall = $phpcsFile->findNext( Tokens::$emptyTokens, $callCloseParen + 1, $closeParen, true );

		return false === $afterCall;
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
