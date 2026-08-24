<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantTruthyBeforeStringComparisonSniff
 *
 * Detects patterns like `$var && $var === 'string'` where the truthy check is redundant.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant truthy checks before string comparisons.
 *
 * Bad:
 * return $field_type && $field_type === 'data';
 * if ( $var && $var === 'something' ) { ... }
 *
 * Good:
 * return $field_type === 'data';
 * if ( $var === 'something' ) { ... }
 *
 * When comparing a variable to a non-empty string with ===, the truthy check
 * is redundant because if $var is falsy, it can't equal a non-empty string.
 */
class RedundantTruthyBeforeStringComparisonSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_BOOLEAN_AND );
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

		// Find the expression before &&.
		$beforeAnd = $this->getExpressionBefore( $phpcsFile, $stackPtr );

		if ( false === $beforeAnd ) {
			return;
		}

		// Check if the expression before && is a simple variable (truthy check).
		if ( ! $this->isSimpleVariable( $phpcsFile, $beforeAnd ) ) {
			return;
		}

		$variableName = $this->getVariableName( $phpcsFile, $beforeAnd );

		if ( empty( $variableName ) ) {
			return;
		}

		// Find the expression after &&.
		$afterAnd = $this->getExpressionAfter( $phpcsFile, $stackPtr );

		if ( false === $afterAnd ) {
			return;
		}

		// Check if the expression after && is a string comparison with the same variable.
		$comparisonInfo = $this->isStringComparisonWithVariable( $phpcsFile, $afterAnd, $variableName );

		if ( false === $comparisonInfo ) {
			return;
		}

		// We have a match! Report the error.
		$fix = $phpcsFile->addFixableError(
			'Redundant truthy check before string comparison. Simplify "%s && %s === \'%s\'" to "%s === \'%s\'"',
			$stackPtr,
			'Found',
			array(
				$variableName,
				$variableName,
				$comparisonInfo['string'],
				$variableName,
				$comparisonInfo['string'],
			)
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove the truthy check and the && operator.
			// Remove from start of the variable to end of &&.
			for ( $i = $beforeAnd['start']; $i <= $stackPtr; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Also remove whitespace after &&.
			$afterAndToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

			for ( $i = $stackPtr + 1; $i < $afterAndToken; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Get the expression before the && operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $andPtr    The position of the && operator.
	 *
	 * @return array|false Array with 'start' and 'end' keys, or false on failure.
	 */
	private function getExpressionBefore( File $phpcsFile, $andPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the last non-whitespace token before &&.
		$end = $phpcsFile->findPrevious( T_WHITESPACE, $andPtr - 1, null, true );

		if ( false === $end ) {
			return false;
		}

		// Find the start of the expression.
		// Look backwards for tokens that could be part of a variable expression.
		$start            = $end;
		$validTokens      = array(
			T_VARIABLE,
			T_STRING,
			T_OBJECT_OPERATOR,
			T_NULLSAFE_OBJECT_OPERATOR,
			T_OPEN_SQUARE_BRACKET,
			T_CLOSE_SQUARE_BRACKET,
			T_CONSTANT_ENCAPSED_STRING,
			T_LNUMBER,
			T_DNUMBER,
		);
		$bracketDepth     = 0;
		$stopTokens       = array(
			T_OPEN_PARENTHESIS,
			T_BOOLEAN_AND,
			T_BOOLEAN_OR,
			T_LOGICAL_AND,
			T_LOGICAL_OR,
			T_SEMICOLON,
			T_OPEN_CURLY_BRACKET,
			T_RETURN,
			T_IF,
			T_ELSEIF,
			T_COMMA,
		);

		for ( $i = $end; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			// Skip whitespace.
			if ( $code === T_WHITESPACE ) {
				continue;
			}

			// Track bracket depth.
			if ( $code === T_CLOSE_SQUARE_BRACKET ) {
				++$bracketDepth;
				$start = $i;
				continue;
			}

			if ( $code === T_OPEN_SQUARE_BRACKET ) {
				--$bracketDepth;
				$start = $i;
				continue;
			}

			// If we're inside brackets, continue.
			if ( $bracketDepth > 0 ) {
				$start = $i;
				continue;
			}

			// Stop at certain tokens.
			if ( in_array( $code, $stopTokens, true ) ) {
				break;
			}

			// Accept valid expression tokens.
			if ( in_array( $code, $validTokens, true ) ) {
				$start = $i;
				continue;
			}

			// Unknown token, stop.
			break;
		}

		return array(
			'start' => $start,
			'end'   => $end,
		);
	}

	/**
	 * Get the expression after the && operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $andPtr    The position of the && operator.
	 *
	 * @return array|false Array with 'start' and 'end' keys, or false on failure.
	 */
	private function getExpressionAfter( File $phpcsFile, $andPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the first non-whitespace token after &&.
		$start = $phpcsFile->findNext( T_WHITESPACE, $andPtr + 1, null, true );

		if ( false === $start ) {
			return false;
		}

		// Find the end of the expression (look for the comparison and value).
		$end         = $start;
		$stopTokens  = array(
			T_BOOLEAN_AND,
			T_BOOLEAN_OR,
			T_LOGICAL_AND,
			T_LOGICAL_OR,
			T_SEMICOLON,
			T_CLOSE_PARENTHESIS,
			T_CLOSE_CURLY_BRACKET,
			T_INLINE_THEN,
			T_COMMA,
		);

		$parenDepth   = 0;
		$bracketDepth = 0;

		for ( $i = $start; $i < count( $tokens ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Track parenthesis depth.
			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
				$end = $i;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				if ( $parenDepth === 0 ) {
					break;
				}
				--$parenDepth;
				$end = $i;
				continue;
			}

			// Track bracket depth.
			if ( $code === T_OPEN_SQUARE_BRACKET ) {
				++$bracketDepth;
				$end = $i;
				continue;
			}

			if ( $code === T_CLOSE_SQUARE_BRACKET ) {
				--$bracketDepth;
				$end = $i;
				continue;
			}

			// If we're inside parens or brackets, continue.
			if ( $parenDepth > 0 || $bracketDepth > 0 ) {
				$end = $i;
				continue;
			}

			// Skip whitespace.
			if ( $code === T_WHITESPACE ) {
				continue;
			}

			// Stop at certain tokens.
			if ( in_array( $code, $stopTokens, true ) ) {
				break;
			}

			$end = $i;
		}

		return array(
			'start' => $start,
			'end'   => $end,
		);
	}

	/**
	 * Check if the expression is a simple variable (not array access or method call).
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $expr      The expression info with 'start' and 'end'.
	 *
	 * @return bool
	 */
	private function isSimpleVariable( File $phpcsFile, $expr ) {
		$tokens = $phpcsFile->getTokens();

		// The expression should be just a single variable token.
		$nonWhitespaceCount = 0;
		$hasVariable        = false;

		for ( $i = $expr['start']; $i <= $expr['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			++$nonWhitespaceCount;

			if ( $tokens[ $i ]['code'] === T_VARIABLE ) {
				$hasVariable = true;
			}
		}

		// Must be exactly one token and it must be a variable.
		return $nonWhitespaceCount === 1 && $hasVariable;
	}

	/**
	 * Get the variable name from an expression.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $expr      The expression info with 'start' and 'end'.
	 *
	 * @return false|string
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
	 * Check if the expression is a string comparison with the given variable.
	 *
	 * Returns info about the comparison if it matches, false otherwise.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $expr         The expression info with 'start' and 'end'.
	 * @param string $variableName The variable name to look for.
	 *
	 * @return array|false Array with 'string' key containing the compared string, or false.
	 */
	private function isStringComparisonWithVariable( File $phpcsFile, $expr, $variableName ) {
		$tokens = $phpcsFile->getTokens();

		// Look for === or == in the expression.
		$comparisonPos = false;

		for ( $i = $expr['start']; $i <= $expr['end']; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_IS_IDENTICAL || $code === T_IS_EQUAL ) {
				$comparisonPos = $i;
				break;
			}
		}

		if ( false === $comparisonPos ) {
			return false;
		}

		// Check if the variable is on one side of the comparison.
		$leftSide  = $phpcsFile->findPrevious( T_WHITESPACE, $comparisonPos - 1, $expr['start'] - 1, true );
		$rightSide = $phpcsFile->findNext( T_WHITESPACE, $comparisonPos + 1, $expr['end'] + 1, true );

		$variableOnLeft  = false !== $leftSide && $tokens[ $leftSide ]['code'] === T_VARIABLE && $tokens[ $leftSide ]['content'] === $variableName;
		$variableOnRight = false !== $rightSide && $tokens[ $rightSide ]['code'] === T_VARIABLE && $tokens[ $rightSide ]['content'] === $variableName;

		if ( ! $variableOnLeft && ! $variableOnRight ) {
			return false;
		}

		// Ensure the variable is used alone (not with property/array access).
		// Check for tokens like -> or [ after the variable.
		$variablePos = $variableOnLeft ? $leftSide : $rightSide;
		$afterVar    = $phpcsFile->findNext( T_WHITESPACE, $variablePos + 1, $expr['end'] + 1, true );

		if ( false !== $afterVar && $afterVar !== $comparisonPos ) {
			$afterCode = $tokens[ $afterVar ]['code'];

			// If there's something between the variable and the comparison operator,
			// check if it's property/array access.
			if ( in_array( $afterCode, array( T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_OPEN_SQUARE_BRACKET, T_DOUBLE_COLON ), true ) ) {
				return false;
			}
		}

		// Also check before the variable (for cases like 'string' === $var->prop).
		if ( $variableOnRight ) {
			$beforeVar = $phpcsFile->findPrevious( T_WHITESPACE, $variablePos - 1, $comparisonPos, true );

			if ( false !== $beforeVar && $beforeVar !== $comparisonPos ) {
				return false;
			}
		}

		// Find the string on the other side.
		$stringSide = $variableOnLeft ? $rightSide : $leftSide;

		if ( false === $stringSide || $tokens[ $stringSide ]['code'] !== T_CONSTANT_ENCAPSED_STRING ) {
			return false;
		}

		$stringValue = $tokens[ $stringSide ]['content'];

		// Remove quotes to get the actual string value.
		$stringContent = substr( $stringValue, 1, -1 );

		// Skip empty strings - truthy check is NOT redundant for empty string comparison.
		if ( $stringContent === '' ) {
			return false;
		}

		// Skip numeric strings - truthy check might not be redundant (e.g., '0' is falsy).
		if ( is_numeric( $stringContent ) ) {
			return false;
		}

		return array(
			'string' => $stringContent,
		);
	}
}
