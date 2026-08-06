<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferNegationOverEmptyForFunctionCallSniff
 *
 * Detects empty() calls where the argument is a function call, and suggests
 * using negation instead since function return values are always "set".
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects empty(function()) patterns that should be !function().
 *
 * Bad:
 * if ( empty( FrmField::get_option( $field, 'placeholder' ) ) ) {
 *
 * Good:
 * if ( ! FrmField::get_option( $field, 'placeholder' ) ) {
 */
class PreferNegationOverEmptyForFunctionCallSniff implements Sniff {

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

		// Skip "! empty()" patterns - the alternative (bool) isn't better.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prevToken && $tokens[ $prevToken ]['code'] === T_BOOLEAN_NOT ) {
			return;
		}

		// Find the opening parenthesis.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the matching closing parenthesis.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Get the content inside empty().
		$innerStart = $openParen + 1;
		$innerEnd   = $closeParen - 1;

		// Skip leading whitespace.
		$firstInner = $phpcsFile->findNext( T_WHITESPACE, $innerStart, $closeParen, true );

		if ( false === $firstInner ) {
			return;
		}

		// Check if the inner content starts with a function call.
		if ( ! $this->isFunctionCall( $phpcsFile, $firstInner, $innerEnd ) ) {
			return;
		}

		// Skip if the expression ends with array access like function()['key'] - the key might not exist.
		if ( $this->endsWithArrayAccess( $phpcsFile, $firstInner, $innerEnd ) ) {
			return;
		}

		// Get the function call content.
		$functionCallContent = $this->getContentBetween( $phpcsFile, $firstInner, $innerEnd );

		$fix = $phpcsFile->addFixableError(
			'Use negation instead of empty() for function calls. Change "empty( %s )" to "! %s".',
			$stackPtr,
			'Found',
			array( $functionCallContent, $functionCallContent )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $openParen, $closeParen, $firstInner, $innerEnd );
		}
	}

	/**
	 * Check if the content starting at $start is a function call.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return bool
	 */
	private function isFunctionCall( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();

		// Check for regular function call: function_name(
		if ( $tokens[ $start ]['code'] === T_STRING ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );

			if ( false !== $next && $tokens[ $next ]['code'] === T_OPEN_PARENTHESIS ) {
				return true;
			}
		}

		// Check for static method call: Class::method( or self::method( or static::method(
		// Skip if the result is used for property access: Class::method()->property
		if ( in_array( $tokens[ $start ]['code'], array( T_STRING, T_SELF, T_STATIC, T_PARENT ), true ) ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );

			if ( false !== $next && $tokens[ $next ]['code'] === T_DOUBLE_COLON ) {
				$methodName = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );

				if ( false !== $methodName && $tokens[ $methodName ]['code'] === T_STRING ) {
					$openParen = $phpcsFile->findNext( T_WHITESPACE, $methodName + 1, null, true );

					if ( false !== $openParen && $tokens[ $openParen ]['code'] === T_OPEN_PARENTHESIS ) {
						// Check if there's property/method access after the method call.
						if ( isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
							$closeParen   = $tokens[ $openParen ]['parenthesis_closer'];
							$afterMethod  = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, $end + 1, true );

							if ( false !== $afterMethod && $tokens[ $afterMethod ]['code'] === T_OBJECT_OPERATOR ) {
								// Static method followed by ->; check if it ends with property access.
								return $this->isMethodCallChain( $phpcsFile, $afterMethod, $end );
							}
						}

						return true;
					}
				}
			}
		}

		// Check for object method call: $obj->method(
		// Skip property access like $obj->property (no parentheses).
		if ( $tokens[ $start ]['code'] === T_VARIABLE ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $start + 1, $end + 1, true );

			// If there's an object operator, this could be property access or method call.
			// Only flag as function call if it ends with a method call (has parentheses).
			if ( false !== $next && $tokens[ $next ]['code'] === T_OBJECT_OPERATOR ) {
				// Walk through potential chained access to find if it ends with a method call.
				return $this->isMethodCallChain( $phpcsFile, $next, $end );
			}
		}

		return false;
	}

	/**
	 * Check if the expression ends with array access like function()['key'].
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return bool True if expression ends with array access.
	 */
	private function endsWithArrayAccess( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();

		// Find the last non-whitespace token.
		$lastToken = $end;

		while ( $lastToken > $start && $tokens[ $lastToken ]['code'] === T_WHITESPACE ) {
			--$lastToken;
		}

		// Check if it ends with ] (closing square bracket).
		if ( $tokens[ $lastToken ]['code'] === T_CLOSE_SQUARE_BRACKET ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if an object operator chain ends with a method call (has parentheses).
	 *
	 * This distinguishes between:
	 * - $obj->property (property access - should NOT be flagged)
	 * - $obj->method() (method call - should be flagged)
	 * - $obj->prop->method() (chained ending in method - should be flagged)
	 *
	 * @param File $phpcsFile       The file being scanned.
	 * @param int  $objectOperator  Position of the T_OBJECT_OPERATOR token.
	 * @param int  $end             End position to search within.
	 *
	 * @return bool True if the chain ends with a method call.
	 */
	private function isMethodCallChain( File $phpcsFile, $objectOperator, $end ) {
		$tokens = $phpcsFile->getTokens();

		$current = $objectOperator;

		while ( $current <= $end ) {
			// After ->, expect a property/method name.
			$memberName = $phpcsFile->findNext( T_WHITESPACE, $current + 1, $end + 1, true );

			if ( false === $memberName || $tokens[ $memberName ]['code'] !== T_STRING ) {
				return false;
			}

			// Check what comes after the member name.
			$afterMember = $phpcsFile->findNext( T_WHITESPACE, $memberName + 1, $end + 1, true );

			if ( false === $afterMember ) {
				// Nothing after member name - this is property access.
				return false;
			}

			if ( $tokens[ $afterMember ]['code'] === T_OPEN_PARENTHESIS ) {
				// This is a method call. Check if there's more after the closing paren.
				if ( ! isset( $tokens[ $afterMember ]['parenthesis_closer'] ) ) {
					return true;
				}

				$closeParen  = $tokens[ $afterMember ]['parenthesis_closer'];
				$afterMethod = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, $end + 1, true );

				if ( false === $afterMethod ) {
					// Method call is the last thing - this is a function call.
					return true;
				}

				if ( $tokens[ $afterMethod ]['code'] === T_OBJECT_OPERATOR ) {
					// Chained call, continue checking.
					$current = $afterMethod;
					continue;
				}

				// Something else after method - still counts as method call.
				return true;
			}

			if ( $tokens[ $afterMember ]['code'] === T_OBJECT_OPERATOR ) {
				// Chained property access, continue checking.
				$current = $afterMember;
				continue;
			}

			// Something else (like end of expression) - this was property access.
			return false;
		}

		return false;
	}

	/**
	 * Get the content between two positions.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return string
	 */
	private function getContentBetween( File $phpcsFile, $start, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';

		// Skip trailing whitespace.
		while ( $end > $start && $tokens[ $end ]['code'] === T_WHITESPACE ) {
			--$end;
		}

		for ( $i = $start; $i <= $end; $i++ ) {
			$content .= $tokens[ $i ]['content'];
		}

		return trim( $content );
	}

	/**
	 * Apply the fix to replace empty(function()) with !function().
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $stackPtr   The empty token position.
	 * @param int  $openParen  The opening parenthesis of empty().
	 * @param int  $closeParen The closing parenthesis of empty().
	 * @param int  $innerStart The start of the inner content.
	 * @param int  $innerEnd   The end of the inner content.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $stackPtr, $openParen, $closeParen, $innerStart, $innerEnd ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		// Get the function call content (trimmed).
		$functionCall = $this->getContentBetween( $phpcsFile, $innerStart, $innerEnd );

		$fixer->beginChangeset();

		// Replace empty( with ! (with space for WordPress style).
		$fixer->replaceToken( $stackPtr, '! ' );

		// Remove the opening parenthesis and any whitespace after empty.
		for ( $i = $stackPtr + 1; $i <= $openParen; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Keep the inner content but remove the WordPress-style spacing.
		// Find the actual start of the function call (skip leading whitespace).
		for ( $i = $openParen + 1; $i < $innerStart; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Remove trailing whitespace and closing paren.
		// First, find where the actual content ends.
		$actualEnd = $innerEnd;

		while ( $actualEnd > $innerStart && $tokens[ $actualEnd ]['code'] === T_WHITESPACE ) {
			--$actualEnd;
		}

		// Remove whitespace between content and closing paren.
		for ( $i = $actualEnd + 1; $i < $closeParen; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Remove the closing parenthesis.
		$fixer->replaceToken( $closeParen, '' );

		$fixer->endChangeset();
	}
}
