<?php
/**
 * Formidable_Sniffs_CodeAnalysis_SimplifyDualConditionToTernarySniff
 *
 * Detects patterns like (A && B) || (!A && C) that can be simplified to A ? B : C.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects dual condition patterns that can be simplified to a ternary.
 *
 * Bad:
 * ( is_array( $var ) && in_array( $x, $var ) ) || ( ! is_array( $var ) && $var == $x )
 *
 * Good:
 * is_array( $var ) ? in_array( $x, $var ) : $var == $x
 */
class SimplifyDualConditionToTernarySniff implements Sniff {

	/**
	 * Functions that return boolean values.
	 *
	 * @var array
	 */
	private $booleanFunctions = array(
		'is_array',
		'is_string',
		'is_int',
		'is_integer',
		'is_bool',
		'is_float',
		'is_numeric',
		'is_object',
		'is_null',
		'is_callable',
		'is_resource',
		'isset',
		'empty',
		'in_array',
		'array_key_exists',
		'key_exists',
		'property_exists',
		'method_exists',
		'class_exists',
		'function_exists',
		'defined',
		'file_exists',
		'is_file',
		'is_dir',
		'is_readable',
		'is_writable',
		'is_writeable',
		'is_uploaded_file',
		'preg_match',
		'strpos',
		'stripos',
		'has_filter',
		'has_action',
		'did_action',
		'doing_action',
		'doing_filter',
		'wp_doing_ajax',
		'is_admin',
		'is_user_logged_in',
		'current_user_can',
		'user_can',
		'check_ajax_referer',
		'wp_verify_nonce',
	);

	/**
	 * Comparison operators that indicate a boolean expression.
	 *
	 * @var array
	 */
	private $comparisonOperators = array(
		T_IS_EQUAL,
		T_IS_NOT_EQUAL,
		T_IS_IDENTICAL,
		T_IS_NOT_IDENTICAL,
		T_IS_SMALLER_OR_EQUAL,
		T_IS_GREATER_OR_EQUAL,
		T_LESS_THAN,
		T_GREATER_THAN,
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_BOOLEAN_OR );
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

		// Find the closing paren before || (end of first group).
		$leftGroupEnd = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $leftGroupEnd || $tokens[ $leftGroupEnd ]['code'] !== T_CLOSE_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $leftGroupEnd ]['parenthesis_opener'] ) ) {
			return;
		}

		$leftGroupStart = $tokens[ $leftGroupEnd ]['parenthesis_opener'];

		// Find the opening paren after || (start of second group).
		$rightGroupStart = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $rightGroupStart || $tokens[ $rightGroupStart ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $rightGroupStart ]['parenthesis_closer'] ) ) {
			return;
		}

		$rightGroupEnd = $tokens[ $rightGroupStart ]['parenthesis_closer'];

		// Parse the first condition from each group.
		$leftFirstCondition  = $this->getFirstCondition( $phpcsFile, $leftGroupStart, $leftGroupEnd );
		$rightFirstCondition = $this->getFirstCondition( $phpcsFile, $rightGroupStart, $rightGroupEnd );

		if ( false === $leftFirstCondition || false === $rightFirstCondition ) {
			return;
		}

		// Check if one is the negation of the other.
		if ( ! $this->areOppositeConditions( $leftFirstCondition, $rightFirstCondition ) ) {
			return;
		}

		// Determine which group has the positive condition and which has the negative.
		$positiveGroupStart = $leftFirstCondition['negated'] ? $rightGroupStart : $leftGroupStart;
		$positiveGroupEnd   = $leftFirstCondition['negated'] ? $rightGroupEnd : $leftGroupEnd;
		$negativeGroupStart = $leftFirstCondition['negated'] ? $leftGroupStart : $rightGroupStart;
		$negativeGroupEnd   = $leftFirstCondition['negated'] ? $leftGroupEnd : $rightGroupEnd;

		$positiveCondition = $leftFirstCondition['negated'] ? $rightFirstCondition : $leftFirstCondition;

		// Get the "rest" of each condition (the part after &&).
		$trueExpr  = $this->getRestOfCondition( $phpcsFile, $positiveGroupStart, $positiveGroupEnd );
		$falseExpr = $this->getRestOfCondition( $phpcsFile, $negativeGroupStart, $negativeGroupEnd );

		if ( false === $trueExpr || false === $falseExpr ) {
			return;
		}

		// Ensure both B and C expressions are boolean (comparisons or bool-returning functions).
		if ( ! $this->isBooleanExpression( $phpcsFile, $positiveGroupStart, $positiveGroupEnd ) ) {
			return;
		}

		if ( ! $this->isBooleanExpression( $phpcsFile, $negativeGroupStart, $negativeGroupEnd ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Simplify to a ternary: %s ? %s : %s',
			$stackPtr,
			'Found',
			array( $positiveCondition['content'], $trueExpr, $falseExpr )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Build the ternary expression.
			$ternary = $positiveCondition['content'] . ' ? ' . $trueExpr . ' : ' . $falseExpr;

			// Replace from the start of the first group to the end of the second group.
			$replaceStart = $leftGroupStart;
			$replaceEnd   = $rightGroupEnd;

			// Replace the first token with the ternary.
			$phpcsFile->fixer->replaceToken( $replaceStart, $ternary );

			// Remove all other tokens in the range.
			for ( $i = $replaceStart + 1; $i <= $replaceEnd; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Get the rest of the condition after the first check (after &&).
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  Opening parenthesis position.
	 * @param int  $closeParen Closing parenthesis position.
	 *
	 * @return false|string The rest of the condition or false.
	 */
	private function getRestOfCondition( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		// Find first non-whitespace token inside the group.
		$firstToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $firstToken ) {
			return false;
		}

		// Skip negation if present.
		if ( $tokens[ $firstToken ]['code'] === T_BOOLEAN_NOT ) {
			$firstToken = $phpcsFile->findNext( T_WHITESPACE, $firstToken + 1, $closeParen, true );

			if ( false === $firstToken ) {
				return false;
			}
		}

		// Find the && operator.
		$andOperator = $this->findAndOperator( $phpcsFile, $firstToken, $closeParen );

		if ( false === $andOperator ) {
			return false;
		}

		// Get content after && (the rest of the condition).
		$restStart = $phpcsFile->findNext( T_WHITESPACE, $andOperator + 1, $closeParen, true );

		if ( false === $restStart ) {
			return false;
		}

		// Find the end (before closing paren, excluding trailing whitespace).
		$restEnd = $phpcsFile->findPrevious( T_WHITESPACE, $closeParen - 1, $restStart, true );

		if ( false === $restEnd ) {
			return false;
		}

		return $this->getTokensContent( $phpcsFile, $restStart, $restEnd );
	}

	/**
	 * Get the first condition from a parenthesized group.
	 *
	 * Looks for pattern: ( condition && rest ) or ( ! condition && rest )
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  Opening parenthesis position.
	 * @param int  $closeParen Closing parenthesis position.
	 *
	 * @return array|false Array with 'content', 'negated', 'end' keys, or false.
	 */
	private function getFirstCondition( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		// Find first non-whitespace token inside the group.
		$firstToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $firstToken ) {
			return false;
		}

		$negated = false;

		// Check for negation.
		if ( $tokens[ $firstToken ]['code'] === T_BOOLEAN_NOT ) {
			$negated    = true;
			$firstToken = $phpcsFile->findNext( T_WHITESPACE, $firstToken + 1, $closeParen, true );

			if ( false === $firstToken ) {
				return false;
			}
		}

		// Now find the && operator to determine where the first condition ends.
		$conditionEnd = $this->findAndOperator( $phpcsFile, $firstToken, $closeParen );

		if ( false === $conditionEnd ) {
			return false;
		}

		// Get the content of the first condition (excluding the && and any trailing whitespace).
		$contentEnd = $phpcsFile->findPrevious( T_WHITESPACE, $conditionEnd - 1, $firstToken, true );

		if ( false === $contentEnd ) {
			return false;
		}

		$content = $this->getTokensContent( $phpcsFile, $firstToken, $contentEnd );

		return array(
			'content' => trim( $content ),
			'negated' => $negated,
			'start'   => $firstToken,
			'end'     => $contentEnd,
		);
	}

	/**
	 * Find the && operator at the top level of nesting.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  Starting position.
	 * @param int  $endPtr    Ending position.
	 *
	 * @return false|int Position of && or false.
	 */
	private function findAndOperator( File $phpcsFile, $startPtr, $endPtr ) {
		$tokens = $phpcsFile->getTokens();
		$depth  = 0;

		for ( $i = $startPtr; $i < $endPtr; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_OPEN_PARENTHESIS ) {
				++$depth;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				--$depth;
				continue;
			}

			if ( $depth === 0 && $code === T_BOOLEAN_AND ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Check if two conditions are opposites (one is negated version of the other).
	 *
	 * @param array $cond1 First condition.
	 * @param array $cond2 Second condition.
	 *
	 * @return bool
	 */
	private function areOppositeConditions( $cond1, $cond2 ) {
		// One must be negated, the other not.
		if ( $cond1['negated'] === $cond2['negated'] ) {
			return false;
		}

		// The content must match.
		return $cond1['content'] === $cond2['content'];
	}

	/**
	 * Check if the "rest" part of a condition group (B or C) is a boolean expression.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  Opening parenthesis of the group.
	 * @param int  $closeParen Closing parenthesis of the group.
	 *
	 * @return bool
	 */
	private function isBooleanExpression( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		// Find first non-whitespace token inside the group.
		$firstToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $firstToken ) {
			return false;
		}

		// Skip negation if present.
		if ( $tokens[ $firstToken ]['code'] === T_BOOLEAN_NOT ) {
			$firstToken = $phpcsFile->findNext( T_WHITESPACE, $firstToken + 1, $closeParen, true );

			if ( false === $firstToken ) {
				return false;
			}
		}

		// Find the && operator.
		$andOperator = $this->findAndOperator( $phpcsFile, $firstToken, $closeParen );

		if ( false === $andOperator ) {
			return false;
		}

		// Get the range of the "rest" expression (after &&).
		$restStart = $phpcsFile->findNext( T_WHITESPACE, $andOperator + 1, $closeParen, true );

		if ( false === $restStart ) {
			return false;
		}

		$restEnd = $phpcsFile->findPrevious( T_WHITESPACE, $closeParen - 1, $restStart, true );

		if ( false === $restEnd ) {
			return false;
		}

		// Check for comparison operators in the rest expression (at top level).
		$depth = 0;

		for ( $i = $restStart; $i <= $restEnd; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_OPEN_PARENTHESIS ) {
				++$depth;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				--$depth;
				continue;
			}

			// Check for comparison operators at top level.
			if ( $depth === 0 && in_array( $code, $this->comparisonOperators, true ) ) {
				return true;
			}
		}

		// Check if the expression starts with a boolean-returning function.
		if ( $tokens[ $restStart ]['code'] === T_STRING ) {
			$funcName = strtolower( $tokens[ $restStart ]['content'] );

			if ( in_array( $funcName, $this->booleanFunctions, true ) ) {
				return true;
			}
		}

		// Check for negated function call: ! funcName().
		if ( $tokens[ $restStart ]['code'] === T_BOOLEAN_NOT ) {
			$afterNot = $phpcsFile->findNext( T_WHITESPACE, $restStart + 1, $restEnd + 1, true );

			if ( false !== $afterNot && $tokens[ $afterNot ]['code'] === T_STRING ) {
				$funcName = strtolower( $tokens[ $afterNot ]['content'] );

				if ( in_array( $funcName, $this->booleanFunctions, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get the content of tokens between two positions.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return string
	 */
	private function getTokensContent( File $phpcsFile, $start, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';

		for ( $i = $start; $i <= $end; $i++ ) {
			$content .= $tokens[ $i ]['content'];
		}

		return $content;
	}
}
