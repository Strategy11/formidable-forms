<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantEmptyOnParameterSniff
 *
 * Detects redundant empty() calls on function parameters.
 * Since function parameters are always set (even with default values),
 * empty($param) can be simplified to just !$param or $param.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant empty() calls on function parameters.
 *
 * Bad:
 * function example( $cta = array() ) {
 *     if ( ! empty( $cta ) ) { ... }
 *     if ( empty( $cta ) ) { ... }
 * }
 *
 * Good:
 * function example( $cta = array() ) {
 *     if ( $cta ) { ... }
 *     if ( ! $cta ) { ... }
 * }
 */
class RedundantEmptyOnParameterSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_EMPTY );
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

		// Only process if empty() is in a boolean context (if/elseif condition or with || / &&).
		if ( ! $this->isInBooleanContext( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Find the opening parenthesis after empty.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the variable inside empty().
		$variablePtr = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, null, true );

		if ( false === $variablePtr || $tokens[ $variablePtr ]['code'] !== T_VARIABLE ) {
			return;
		}

		$variableName = $tokens[ $variablePtr ]['content'];

		// Check if there's anything else inside the empty() call (like array access).
		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$nextToken  = $phpcsFile->findNext( T_WHITESPACE, $variablePtr + 1, $closeParen, true );

		if ( false !== $nextToken ) {
			// There's something after the variable (like array access $var['key']).
			// Skip this case as it's more complex.
			return;
		}

		// Find the containing function.
		$functionToken = $this->findContainingFunction( $phpcsFile, $stackPtr );

		if ( false === $functionToken ) {
			return;
		}

		// Get the function parameters.
		$parameters = $this->getFunctionParameters( $phpcsFile, $functionToken );

		// Check if the variable is a function parameter.
		if ( ! in_array( $variableName, $parameters, true ) ) {
			return;
		}

		// Check if there's a boolean NOT before empty.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		$isNegated = ( false !== $prevToken && $tokens[ $prevToken ]['code'] === T_BOOLEAN_NOT );

		// Determine the suggested replacement.
		if ( $isNegated ) {
			$suggestion = $variableName;
			$message    = 'Redundant empty() on function parameter %s. Use "%s" instead of "! empty( %s )"';
		} else {
			$suggestion = '! ' . $variableName;
			$message    = 'Redundant empty() on function parameter %s. Use "%s" instead of "empty( %s )"';
		}

		$fix = $phpcsFile->addFixableError(
			$message,
			$stackPtr,
			'Found',
			array( $variableName, $suggestion, $variableName )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			if ( $isNegated ) {
				// Remove the "!" before empty.
				$phpcsFile->fixer->replaceToken( $prevToken, '' );

				// Remove any whitespace between "!" and "empty".
				for ( $i = $prevToken + 1; $i < $stackPtr; $i++ ) {
					if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
						$phpcsFile->fixer->replaceToken( $i, '' );
					}
				}
			}

			// Replace empty( $var ) with $var or ! $var.
			$phpcsFile->fixer->replaceToken( $stackPtr, '' );

			// Remove whitespace after empty.
			for ( $i = $stackPtr + 1; $i < $openParen; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}

			// Replace opening paren.
			$phpcsFile->fixer->replaceToken( $openParen, '' );

			// Remove whitespace after opening paren.
			$nextAfterOpen = $openParen + 1;

			while ( $nextAfterOpen < $variablePtr && $tokens[ $nextAfterOpen ]['code'] === T_WHITESPACE ) {
				$phpcsFile->fixer->replaceToken( $nextAfterOpen, '' );
				++$nextAfterOpen;
			}

			// Keep the variable, but add "! " prefix if not negated.
			if ( ! $isNegated ) {
				$phpcsFile->fixer->addContentBefore( $variablePtr, '! ' );
			}

			// Remove whitespace before closing paren.
			$prevBeforeClose = $closeParen - 1;

			while ( $prevBeforeClose > $variablePtr && $tokens[ $prevBeforeClose ]['code'] === T_WHITESPACE ) {
				$phpcsFile->fixer->replaceToken( $prevBeforeClose, '' );
				--$prevBeforeClose;
			}

			// Replace closing paren.
			$phpcsFile->fixer->replaceToken( $closeParen, '' );

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Find the function or method that contains the given token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return false|int The position of the function token, or false if not found.
	 */
	private function findContainingFunction( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_FUNCTION || $tokens[ $i ]['code'] === T_CLOSURE ) {
				// Check if the stackPtr is within this function's scope.
				if ( isset( $tokens[ $i ]['scope_opener'], $tokens[ $i ]['scope_closer'] ) ) {
					if ( $stackPtr > $tokens[ $i ]['scope_opener'] && $stackPtr < $tokens[ $i ]['scope_closer'] ) {
						return $i;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if empty() is used as the condition of a ternary (?:) expression.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty token.
	 *
	 * @return bool
	 */
	private function isPartOfTernaryCondition( File $phpcsFile, $stackPtr ) {
		$tokens    = $phpcsFile->getTokens();
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$nextPtr = $tokens[ $openParen ]['parenthesis_closer'] + 1;

		while ( $nextPtr < count( $tokens ) ) {
			$code = $tokens[ $nextPtr ]['code'];

			if ( $code === T_WHITESPACE || $code === T_CLOSE_PARENTHESIS ) {
				++$nextPtr;
				continue;
			}

			if ( $code === T_COMMENT || $code === T_DOC_COMMENT_OPEN_TAG || $code === T_DOC_COMMENT_CLOSE_TAG || $code === T_DOC_COMMENT_STRING || $code === T_DOC_COMMENT_WHITESPACE || $code === T_DOC_COMMENT_STAR ) {
				++$nextPtr;
				continue;
			}

			return $code === T_INLINE_THEN;
		}

		return false;
	}

	/**
	 * Get the parameter names for a function.
	 *
	 * @param File $phpcsFile     The file being scanned.
	 * @param int  $functionToken The position of the function token.
	 *
	 * @return array List of parameter variable names (e.g., ['$param1', '$param2']).
	 */
	private function getFunctionParameters( File $phpcsFile, $functionToken ) {
		$tokens     = $phpcsFile->getTokens();
		$parameters = array();

		if ( ! isset( $tokens[ $functionToken ]['parenthesis_opener'], $tokens[ $functionToken ]['parenthesis_closer'] ) ) {
			return $parameters;
		}

		$openParen  = $tokens[ $functionToken ]['parenthesis_opener'];
		$closeParen = $tokens[ $functionToken ]['parenthesis_closer'];

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE ) {
				$parameters[] = $tokens[ $i ]['content'];
			}
		}

		return $parameters;
	}

	/**
	 * Check if the empty() call is in a boolean context (if/elseif condition or with || / &&).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty token.
	 *
	 * @return bool True if in a boolean context, false otherwise.
	 */
	private function isInBooleanContext( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Check if empty() is used with || or && (before or after).
		if ( $this->hasAdjacentLogicalOperator( $phpcsFile, $stackPtr ) ) {
			return true;
		}

		// Check if empty() is being used as the condition of a ternary operator.
		if ( $this->isPartOfTernaryCondition( $phpcsFile, $stackPtr ) ) {
			return true;
		}

		// Track parenthesis nesting to find the outermost condition parenthesis.
		$parenDepth = 0;

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			// Track parenthesis nesting.
			if ( $code === T_CLOSE_PARENTHESIS ) {
				++$parenDepth;
				continue;
			}

			if ( $code === T_OPEN_PARENTHESIS ) {
				if ( $parenDepth > 0 ) {
					--$parenDepth;
					continue;
				}

				// This is an unmatched open paren - check if it belongs to if/elseif.
				$beforeParen = $phpcsFile->findPrevious( T_WHITESPACE, $i - 1, null, true );

				if ( false !== $beforeParen ) {
					$beforeCode = $tokens[ $beforeParen ]['code'];

					if ( $beforeCode === T_IF || $beforeCode === T_ELSEIF ) {
						return true;
					}
				}

				// This parenthesis doesn't belong to if/elseif.
				return false;
			}

			// Skip tokens that are valid inside an if condition.
			if ( $code === T_WHITESPACE
				|| $code === T_BOOLEAN_NOT
				|| $code === T_BOOLEAN_AND
				|| $code === T_BOOLEAN_OR
				|| $code === T_LOGICAL_AND
				|| $code === T_LOGICAL_OR
				|| $code === T_VARIABLE
				|| $code === T_STRING
				|| $code === T_LNUMBER
				|| $code === T_DNUMBER
				|| $code === T_CONSTANT_ENCAPSED_STRING
				|| $code === T_TRUE
				|| $code === T_FALSE
				|| $code === T_NULL
				|| $code === T_ISSET
				|| $code === T_EMPTY
				|| $code === T_IS_EQUAL
				|| $code === T_IS_NOT_EQUAL
				|| $code === T_IS_IDENTICAL
				|| $code === T_IS_NOT_IDENTICAL
				|| $code === T_GREATER_THAN
				|| $code === T_LESS_THAN
				|| $code === T_IS_GREATER_OR_EQUAL
				|| $code === T_IS_SMALLER_OR_EQUAL
				|| $code === T_OBJECT_OPERATOR
				|| $code === T_NULLSAFE_OBJECT_OPERATOR
				|| $code === T_OPEN_SQUARE_BRACKET
				|| $code === T_CLOSE_SQUARE_BRACKET
				|| $code === T_DOUBLE_COLON
			) {
				continue;
			}

			// Hit something unexpected, stop searching.
			return false;
		}

		return false;
	}

	/**
	 * Check if empty() has an adjacent logical operator (|| or &&).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty token.
	 *
	 * @return bool True if there's an adjacent logical operator.
	 */
	private function hasAdjacentLogicalOperator( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the closing paren of empty().
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		$logicalOperators = array(
			T_BOOLEAN_AND,
			T_BOOLEAN_OR,
			T_LOGICAL_AND,
			T_LOGICAL_OR,
		);

		// Check before empty() (skip ! if present).
		$beforeEmpty = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $beforeEmpty && $tokens[ $beforeEmpty ]['code'] === T_BOOLEAN_NOT ) {
			$beforeEmpty = $phpcsFile->findPrevious( T_WHITESPACE, $beforeEmpty - 1, null, true );
		}

		if ( false !== $beforeEmpty && in_array( $tokens[ $beforeEmpty ]['code'], $logicalOperators, true ) ) {
			return true;
		}

		// Check after empty().
		$afterEmpty = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false !== $afterEmpty && in_array( $tokens[ $afterEmpty ]['code'], $logicalOperators, true ) ) {
			return true;
		}

		return false;
	}
}
