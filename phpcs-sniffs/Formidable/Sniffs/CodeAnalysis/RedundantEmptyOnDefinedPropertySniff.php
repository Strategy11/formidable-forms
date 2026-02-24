<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantEmptyOnDefinedPropertySniff
 *
 * Detects redundant empty() calls on $this->property where the property
 * is defined in the class. Since defined class properties are always set,
 * empty() can be simplified to a falsy check.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant empty() calls on defined class properties.
 *
 * Bad:
 * if ( empty( $this->plugin_slug ) ) { ... }
 * if ( ! empty( $this->plugin_slug ) ) { ... }
 *
 * Good:
 * if ( ! $this->plugin_slug ) { ... }
 * if ( $this->plugin_slug ) { ... }
 */
class RedundantEmptyOnDefinedPropertySniff implements Sniff {

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

		// Only process if empty() is in a boolean context.
		if ( ! $this->isInBooleanContext( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Find the opening parenthesis after empty.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Check if the argument is $this->property.
		$propertyName = $this->getThisPropertyAccess( $phpcsFile, $openParen, $closeParen );

		if ( false === $propertyName ) {
			return;
		}

		// Find the containing class.
		$classToken = $this->findContainingClass( $phpcsFile, $stackPtr );

		if ( false === $classToken ) {
			return;
		}

		// Check if the property is defined in the class (or parent classes via inheritance).
		if ( ! $this->isDefinedProperty( $phpcsFile, $classToken, $propertyName ) ) {
			return;
		}

		// Check if there's a boolean NOT before empty.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		$isNegated = false !== $prevToken && $tokens[ $prevToken ]['code'] === T_BOOLEAN_NOT;

		$propertyAccess = '$this->' . $propertyName;

		// Determine the suggested replacement.
		if ( $isNegated ) {
			$suggestion = $propertyAccess;
			$message    = 'Redundant empty() on defined property %s. Use "%s" instead of "! empty( %s )"';
		} else {
			$suggestion = '! ' . $propertyAccess;
			$message    = 'Redundant empty() on defined property %s. Use "%s" instead of "empty( %s )"';
		}

		$fix = $phpcsFile->addFixableError(
			$message,
			$stackPtr,
			'Found',
			array( $propertyAccess, $suggestion, $propertyAccess )
		);

		if ( true !== $fix ) {
			return;
		}

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

		// Replace empty( $this->prop ) with $this->prop or ! $this->prop.
		// Remove "empty" keyword.
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
		$firstContent = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false !== $firstContent ) {
			for ( $i = $openParen + 1; $i < $firstContent; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}
		}

		// Add "! " prefix if not negated.
		if ( ! $isNegated && false !== $firstContent ) {
			$phpcsFile->fixer->addContentBefore( $firstContent, '! ' );
		}

		// Remove whitespace before closing paren.
		$lastContent = $phpcsFile->findPrevious( T_WHITESPACE, $closeParen - 1, $openParen, true );

		if ( false !== $lastContent ) {
			for ( $i = $lastContent + 1; $i < $closeParen; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}
		}

		// Replace closing paren.
		$phpcsFile->fixer->replaceToken( $closeParen, '' );

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Check if the content inside empty() is a simple $this->property access.
	 *
	 * Returns the property name if it matches, false otherwise.
	 * Skips array access like $this->prop['key'] or chained access like $this->obj->prop.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The opening parenthesis position.
	 * @param int  $closeParen The closing parenthesis position.
	 *
	 * @return false|string The property name, or false if not a simple $this->property.
	 */
	private function getThisPropertyAccess( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		// Find $this.
		$thisToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $thisToken || $tokens[ $thisToken ]['code'] !== T_VARIABLE || $tokens[ $thisToken ]['content'] !== '$this' ) {
			return false;
		}

		// Find the -> operator.
		$arrowToken = $phpcsFile->findNext( T_WHITESPACE, $thisToken + 1, $closeParen, true );

		if ( false === $arrowToken || $tokens[ $arrowToken ]['code'] !== T_OBJECT_OPERATOR ) {
			return false;
		}

		// Find the property name.
		$propToken = $phpcsFile->findNext( T_WHITESPACE, $arrowToken + 1, $closeParen, true );

		if ( false === $propToken || $tokens[ $propToken ]['code'] !== T_STRING ) {
			return false;
		}

		$propertyName = $tokens[ $propToken ]['content'];

		// Make sure there's nothing else after the property name (no array access, no chaining).
		$afterProp = $phpcsFile->findNext( T_WHITESPACE, $propToken + 1, $closeParen, true );

		if ( false !== $afterProp ) {
			return false;
		}

		return $propertyName;
	}

	/**
	 * Find the class that contains the given token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return false|int The position of the class token, or false if not found.
	 */
	private function findContainingClass( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] !== T_CLASS ) {
				continue;
			}

			if ( ! isset( $tokens[ $i ]['scope_opener'], $tokens[ $i ]['scope_closer'] ) ) {
				continue;
			}

			if ( $stackPtr > $tokens[ $i ]['scope_opener'] && $stackPtr < $tokens[ $i ]['scope_closer'] ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Check if a property is defined in the class.
	 *
	 * Scans the class body for property declarations (public/protected/private/var).
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param int    $classToken   The position of the class token.
	 * @param string $propertyName The property name to look for (without $).
	 *
	 * @return bool True if the property is defined in the class.
	 */
	private function isDefinedProperty( File $phpcsFile, $classToken, $propertyName ) {
		$tokens      = $phpcsFile->getTokens();
		$classOpener = $tokens[ $classToken ]['scope_opener'];
		$classCloser = $tokens[ $classToken ]['scope_closer'];

		for ( $i = $classOpener + 1; $i < $classCloser; $i++ ) {
			// Skip nested class/function scopes.
			if ( in_array( $tokens[ $i ]['code'], array( T_FUNCTION, T_CLOSURE ), true ) ) {
				if ( isset( $tokens[ $i ]['scope_closer'] ) ) {
					$i = $tokens[ $i ]['scope_closer'];
				}
				continue;
			}

			// Look for property declarations: visibility modifier or var followed by $variable.
			if ( ! in_array( $tokens[ $i ]['code'], array( T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR, T_STATIC ), true ) ) {
				continue;
			}

			// Find the next variable token after the modifier.
			$varToken = $phpcsFile->findNext(
				array( T_WHITESPACE, T_STATIC, T_READONLY, T_STRING, T_NULLABLE, T_TYPE_UNION, T_TYPE_INTERSECTION, T_NULL, T_SELF, T_PARENT, T_ARRAY, T_CALLABLE, T_NS_SEPARATOR ),
				$i + 1,
				$classCloser,
				true
			);

			if ( false === $varToken || $tokens[ $varToken ]['code'] !== T_VARIABLE ) {
				continue;
			}

			// Check if the variable name matches (strip the $).
			$varName = ltrim( $tokens[ $varToken ]['content'], '$' );

			if ( $varName === $propertyName ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the empty() call is in a boolean context (if/elseif condition, ternary, or logical operator).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty token.
	 *
	 * @return bool True if in a boolean context.
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

				return false;
			}

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

			return false;
		}

		return false;
	}

	/**
	 * Check if empty() is used as the condition of a ternary expression.
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

			return $code === T_INLINE_THEN;
		}

		return false;
	}

	/**
	 * Check if empty() has an adjacent logical operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty token.
	 *
	 * @return bool True if there's an adjacent logical operator.
	 */
	private function hasAdjacentLogicalOperator( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

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
