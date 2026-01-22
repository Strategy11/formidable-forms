<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferPositiveTernaryConditionSniff
 *
 * Detects ternary expressions with negated conditions and flips them to positive.
 * Excludes empty() checks and comparisons against falsy values.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Prefers positive ternary conditions over negated ones.
 *
 * Bad:
 * ! $this->method() ? $a : $b
 * ! function_call() ? $a : $b
 * ! $var ? $a : $b
 *
 * Good:
 * $this->method() ? $b : $a
 * function_call() ? $b : $a
 * $var ? $b : $a
 *
 * Excludes:
 * - ! empty() checks (common pattern)
 * - ! isset() checks
 * - ! is_null() checks (falsy check pattern)
 * - Compound conditions with && or ||
 */
class PreferPositiveTernaryConditionSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_BOOLEAN_NOT );
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

		// Find what follows the negation.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken ) {
			return;
		}

		// Skip empty() - it's a common pattern that shouldn't be flipped.
		if ( $tokens[ $nextToken ]['code'] === T_EMPTY ) {
			return;
		}

		// Skip isset() - also common.
		if ( $tokens[ $nextToken ]['code'] === T_ISSET ) {
			return;
		}

		// Skip is_null() - it's a falsy check pattern.
		if ( $tokens[ $nextToken ]['code'] === T_STRING && strtolower( $tokens[ $nextToken ]['content'] ) === 'is_null' ) {
			return;
		}

		// Find the end of the condition (what's being negated).
		$conditionEnd = $this->findConditionEnd( $phpcsFile, $nextToken );

		if ( false === $conditionEnd ) {
			return;
		}

		// Find the ternary operator (?) after the condition.
		$ternaryOperator = $phpcsFile->findNext( T_WHITESPACE, $conditionEnd + 1, null, true );

		if ( false === $ternaryOperator || $tokens[ $ternaryOperator ]['code'] !== T_INLINE_THEN ) {
			return;
		}

		// Make sure there's nothing between condition end and ternary operator except whitespace.
		// Also skip if there are logical operators (compound conditions).
		for ( $i = $conditionEnd + 1; $i < $ternaryOperator; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
				return;
			}
		}

		// Skip compound conditions - find the start of the full condition and check for logical operators.
		$conditionStart = $this->findConditionStart( $phpcsFile, $stackPtr );

		if ( false !== $conditionStart && $this->hasLogicalOperators( $phpcsFile, $conditionStart, $ternaryOperator ) ) {
			return;
		}

		// Find the colon (:) for the else part.
		$colonOperator = $this->findTernaryColon( $phpcsFile, $ternaryOperator + 1 );

		if ( false === $colonOperator ) {
			return;
		}

		// Find the end of the ternary expression.
		$ternaryEnd = $this->findTernaryEnd( $phpcsFile, $colonOperator + 1 );

		if ( false === $ternaryEnd ) {
			return;
		}

		// Get the condition content (without the !).
		$conditionContent = $this->getTokensContent( $phpcsFile, $nextToken, $conditionEnd );

		// Get the "then" part content.
		$thenContent = $this->getTokensContent( $phpcsFile, $ternaryOperator + 1, $colonOperator - 1 );

		// Get the "else" part content.
		$elseContent = $this->getTokensContent( $phpcsFile, $colonOperator + 1, $ternaryEnd );

		if ( empty( $conditionContent ) || empty( $thenContent ) || empty( $elseContent ) ) {
			return;
		}

		// Skip if either branch contains a nested ternary.
		if ( $this->hasNestedTernary( $phpcsFile, $ternaryOperator + 1, $colonOperator - 1 ) ) {
			return;
		}

		if ( $this->hasNestedTernary( $phpcsFile, $colonOperator + 1, $ternaryEnd ) ) {
			return;
		}

		// We have a match: ! condition ? A : B -> condition ? B : A
		$fix = $phpcsFile->addFixableError(
			'Prefer positive ternary condition. Use "%s ? %s : %s" instead of "! %s ? %s : %s".',
			$stackPtr,
			'Found',
			array(
				trim( $conditionContent ),
				trim( $elseContent ),
				trim( $thenContent ),
				trim( $conditionContent ),
				trim( $thenContent ),
				trim( $elseContent ),
			)
		);

		if ( true === $fix ) {
			$this->applyFix(
				$phpcsFile,
				$stackPtr,
				$nextToken,
				$ternaryOperator,
				$colonOperator,
				$ternaryEnd,
				$thenContent,
				$elseContent
			);
		}
	}

	/**
	 * Find the end of the condition being negated.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position (first token after !).
	 *
	 * @return false|int
	 */
	private function findConditionEnd( File $phpcsFile, $start ) {
		$tokens = $phpcsFile->getTokens();

		// Handle variable: ! $var
		if ( $tokens[ $start ]['code'] === T_VARIABLE ) {
			// Check if it's followed by object operator or array access.
			$next = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );

			if ( false === $next ) {
				return $start;
			}

			// Handle $var->method() or $var->property.
			if ( $tokens[ $next ]['code'] === T_OBJECT_OPERATOR || $tokens[ $next ]['code'] === T_NULLSAFE_OBJECT_OPERATOR ) {
				return $this->findMethodCallEnd( $phpcsFile, $next );
			}

			// Handle $var['key'].
			if ( $tokens[ $next ]['code'] === T_OPEN_SQUARE_BRACKET ) {
				if ( isset( $tokens[ $next ]['bracket_closer'] ) ) {
					return $tokens[ $next ]['bracket_closer'];
				}
			}

			return $start;
		}

		// Handle $this->method() or $this->property.
		if ( $tokens[ $start ]['code'] === T_VARIABLE && $tokens[ $start ]['content'] === '$this' ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );

			if ( false !== $next && ( $tokens[ $next ]['code'] === T_OBJECT_OPERATOR || $tokens[ $next ]['code'] === T_NULLSAFE_OBJECT_OPERATOR ) ) {
				return $this->findMethodCallEnd( $phpcsFile, $next );
			}

			return $start;
		}

		// Handle function call: ! function_name().
		if ( $tokens[ $start ]['code'] === T_STRING ) {
			$openParen = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );

			if ( false !== $openParen && $tokens[ $openParen ]['code'] === T_OPEN_PARENTHESIS ) {
				if ( isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
					return $tokens[ $openParen ]['parenthesis_closer'];
				}
			}

			return $start;
		}

		// Handle static method call: ! ClassName::method().
		if ( $tokens[ $start ]['code'] === T_STRING || $tokens[ $start ]['code'] === T_STATIC ) {
			$doubleColon = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );

			if ( false !== $doubleColon && $tokens[ $doubleColon ]['code'] === T_DOUBLE_COLON ) {
				$methodName = $phpcsFile->findNext( T_WHITESPACE, $doubleColon + 1, null, true );

				if ( false !== $methodName && $tokens[ $methodName ]['code'] === T_STRING ) {
					$openParen = $phpcsFile->findNext( T_WHITESPACE, $methodName + 1, null, true );

					if ( false !== $openParen && $tokens[ $openParen ]['code'] === T_OPEN_PARENTHESIS ) {
						if ( isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
							return $tokens[ $openParen ]['parenthesis_closer'];
						}
					}

					return $methodName;
				}
			}
		}

		// Handle parenthesized expression: ! (expression).
		if ( $tokens[ $start ]['code'] === T_OPEN_PARENTHESIS ) {
			if ( isset( $tokens[ $start ]['parenthesis_closer'] ) ) {
				return $tokens[ $start ]['parenthesis_closer'];
			}
		}

		return false;
	}

	/**
	 * Find the end of a method call chain.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Position of the object operator.
	 *
	 * @return int
	 */
	private function findMethodCallEnd( File $phpcsFile, $start ) {
		$tokens = $phpcsFile->getTokens();
		$pos    = $start;

		while ( true ) {
			// Find the method/property name.
			$name = $phpcsFile->findNext( T_WHITESPACE, $pos + 1, null, true );

			if ( false === $name || $tokens[ $name ]['code'] !== T_STRING ) {
				return $pos;
			}

			// Check for method call (parentheses).
			$next = $phpcsFile->findNext( T_WHITESPACE, $name + 1, null, true );

			if ( false === $next ) {
				return $name;
			}

			if ( $tokens[ $next ]['code'] === T_OPEN_PARENTHESIS ) {
				if ( isset( $tokens[ $next ]['parenthesis_closer'] ) ) {
					$pos = $tokens[ $next ]['parenthesis_closer'];

					// Check for chained call.
					$afterParen = $phpcsFile->findNext( T_WHITESPACE, $pos + 1, null, true );

					if ( false !== $afterParen && ( $tokens[ $afterParen ]['code'] === T_OBJECT_OPERATOR || $tokens[ $afterParen ]['code'] === T_NULLSAFE_OBJECT_OPERATOR ) ) {
						$pos = $afterParen;
						continue;
					}

					return $pos;
				}

				return $name;
			}

			// Check for chained property/method.
			if ( $tokens[ $next ]['code'] === T_OBJECT_OPERATOR || $tokens[ $next ]['code'] === T_NULLSAFE_OBJECT_OPERATOR ) {
				$pos = $next;
				continue;
			}

			return $name;
		}
	}

	/**
	 * Find the colon operator for a ternary, handling nested ternaries.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position to search from.
	 *
	 * @return false|int
	 */
	private function findTernaryColon( File $phpcsFile, $start ) {
		$tokens     = $phpcsFile->getTokens();
		$depth      = 0;
		$parenDepth = 0;

		for ( $i = $start; $i < count( $tokens ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Track parentheses.
			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				--$parenDepth;
				continue;
			}

			// Only process at the same parenthesis level.
			if ( $parenDepth > 0 ) {
				continue;
			}

			// Track nested ternaries.
			if ( $code === T_INLINE_THEN ) {
				++$depth;
				continue;
			}

			if ( $code === T_INLINE_ELSE ) {
				if ( $depth > 0 ) {
					--$depth;
					continue;
				}

				return $i;
			}

			// Stop at statement terminators.
			if ( in_array( $code, array( T_SEMICOLON, T_CLOSE_TAG, T_COMMA ), true ) ) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Find the end of a ternary expression.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position to search from.
	 *
	 * @return false|int
	 */
	private function findTernaryEnd( File $phpcsFile, $start ) {
		$tokens     = $phpcsFile->getTokens();
		$parenDepth = 0;
		$end        = false;

		for ( $i = $start; $i < count( $tokens ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Track parentheses.
			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
				if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
					$end = $i;
				}
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				if ( $parenDepth > 0 ) {
					--$parenDepth;
					if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
						$end = $i;
					}
					continue;
				}

				// This closes the containing expression.
				return $end;
			}

			// Only process at the same parenthesis level.
			if ( $parenDepth > 0 ) {
				if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
					$end = $i;
				}
				continue;
			}

			// Stop at statement terminators.
			if ( in_array( $code, array( T_SEMICOLON, T_CLOSE_TAG, T_COMMA, T_INLINE_THEN, T_INLINE_ELSE ), true ) ) {
				return $end;
			}

			if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
				$end = $i;
			}
		}

		return $end;
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

	/**
	 * Check if a range contains a nested ternary.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return bool
	 */
	private function hasNestedTernary( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $start; $i <= $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_INLINE_THEN ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Find the start of the condition (after assignment or return).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the ! token.
	 *
	 * @return false|int
	 */
	private function findConditionStart( File $phpcsFile, $stackPtr ) {
		$tokens     = $phpcsFile->getTokens();
		$parenDepth = 0;

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			// Track parentheses (going backwards).
			if ( $code === T_CLOSE_PARENTHESIS ) {
				++$parenDepth;
				continue;
			}

			if ( $code === T_OPEN_PARENTHESIS ) {
				if ( $parenDepth > 0 ) {
					--$parenDepth;
					continue;
				}

				return $i + 1;
			}

			if ( $parenDepth > 0 ) {
				continue;
			}

			// Found statement start.
			if ( in_array( $code, array( T_RETURN, T_ECHO, T_PRINT, T_EQUAL, T_DOUBLE_ARROW, T_CONCAT_EQUAL ), true ) ) {
				return $i + 1;
			}

			// Hit a semicolon or opening brace.
			if ( in_array( $code, array( T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_COMMA ), true ) ) {
				return $i + 1;
			}
		}

		return 0;
	}

	/**
	 * Check if a range contains logical operators (&& or ||).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return bool
	 */
	private function hasLogicalOperators( File $phpcsFile, $start, $end ) {
		$tokens     = $phpcsFile->getTokens();
		$parenDepth = 0;

		for ( $i = $start; $i < $end; $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Track parentheses.
			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				--$parenDepth;
				continue;
			}

			// Only check at the top level (not inside function calls).
			if ( $parenDepth > 0 ) {
				continue;
			}

			if ( in_array( $code, array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Apply the fix.
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param int    $notToken        Position of the ! token.
	 * @param int    $conditionStart  Start of the condition.
	 * @param int    $ternaryOperator Position of ?.
	 * @param int    $colonOperator   Position of :.
	 * @param int    $ternaryEnd      Position of end of ternary.
	 * @param string $thenContent     The original then content.
	 * @param string $elseContent     The original else content.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $notToken, $conditionStart, $ternaryOperator, $colonOperator, $ternaryEnd, $thenContent, $elseContent ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$fixer->beginChangeset();

		// Remove the ! token.
		$fixer->replaceToken( $notToken, '' );

		// Remove whitespace after ! if present.
		if ( $tokens[ $notToken + 1 ]['code'] === T_WHITESPACE ) {
			$fixer->replaceToken( $notToken + 1, '' );
		}

		// Clear the then part.
		$thenStart = $phpcsFile->findNext( T_WHITESPACE, $ternaryOperator + 1, $colonOperator, true );
		$thenEnd   = $phpcsFile->findPrevious( T_WHITESPACE, $colonOperator - 1, $ternaryOperator, true );

		if ( false !== $thenStart && false !== $thenEnd ) {
			for ( $i = $thenStart; $i <= $thenEnd; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}
			// Add the else content in place of then.
			$fixer->addContent( $thenStart - 1, ' ' . trim( $elseContent ) . ' ' );
		}

		// Clear the else part.
		$elseStart = $phpcsFile->findNext( T_WHITESPACE, $colonOperator + 1, $ternaryEnd + 1, true );

		if ( false !== $elseStart ) {
			for ( $i = $elseStart; $i <= $ternaryEnd; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}
			// Add the then content in place of else.
			$fixer->addContent( $elseStart - 1, ' ' . trim( $thenContent ) );
		}

		$fixer->endChangeset();
	}
}
