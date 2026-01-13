<?php
/**
 * Sniff to detect ternary conditions using !== and flip them to use ===.
 *
 * Detects patterns like:
 * return null !== $var ? $value1 : $value2;
 *
 * These should be flipped to: return null === $var ? $value2 : $value1;
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects ternary conditions using !== and flips them to use ===.
 */
class FlipNegativeTernarySniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_INLINE_THEN );
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token (the ?).
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the start of this statement (return, assignment, etc.).
		$statementStart = $this->findStatementStart( $phpcsFile, $stackPtr );

		if ( false === $statementStart ) {
			return;
		}

		// Look for !== or != in the condition (between statement start and ?).
		$negativeOperator = $this->findNegativeOperator( $phpcsFile, $statementStart, $stackPtr );

		if ( false === $negativeOperator ) {
			return;
		}

		// Skip if the comparison is against an excluded value (empty string, false, or null).
		if ( $this->isExcludedComparison( $phpcsFile, $negativeOperator ) ) {
			return;
		}

		// Make sure there's no && or || in the condition (complex conditions).
		if ( $this->hasLogicalOperators( $phpcsFile, $statementStart, $stackPtr ) ) {
			return;
		}

		// Find the : that separates true/false branches.
		$colonPtr = $this->findTernaryColon( $phpcsFile, $stackPtr );

		if ( false === $colonPtr ) {
			return;
		}

		// Find the end of the ternary (semicolon or closing paren/bracket).
		$ternaryEnd = $this->findTernaryEnd( $phpcsFile, $colonPtr );

		if ( false === $ternaryEnd ) {
			return;
		}

		// Get the true and false branch content.
		$trueBranch  = $this->getTokensAsString( $phpcsFile, $stackPtr + 1, $colonPtr - 1 );
		$falseBranch = $this->getTokensAsString( $phpcsFile, $colonPtr + 1, $ternaryEnd - 1 );

		// Skip if branches are complex (contain nested ternaries).
		if ( $this->hasNestedTernary( $phpcsFile, $stackPtr + 1, $colonPtr - 1 ) ) {
			return;
		}

		if ( $this->hasNestedTernary( $phpcsFile, $colonPtr + 1, $ternaryEnd - 1 ) ) {
			return;
		}

		// Determine the positive operator to use.
		$positiveOperator = $tokens[ $negativeOperator ]['code'] === T_IS_NOT_IDENTICAL ? '===' : '==';

		$fix = $phpcsFile->addFixableError(
			'Ternary condition uses negative comparison (%s). Flip to use positive comparison (%s) instead.',
			$negativeOperator,
			'NegativeTernary',
			array( $tokens[ $negativeOperator ]['content'], $positiveOperator )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Replace the negative operator with positive.
			$phpcsFile->fixer->replaceToken( $negativeOperator, $positiveOperator );

			// Clear the true branch tokens.
			for ( $i = $stackPtr + 1; $i < $colonPtr; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Clear the false branch tokens.
			for ( $i = $colonPtr + 1; $i < $ternaryEnd; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Insert swapped branches.
			$phpcsFile->fixer->addContent( $stackPtr, ' ' . trim( $falseBranch ) . ' ' );
			$phpcsFile->fixer->addContent( $colonPtr, ' ' . trim( $trueBranch ) . ' ' );

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Find the start of the statement containing the ternary.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the ? token.
	 *
	 * @return false|int
	 */
	private function findStatementStart( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Walk backwards to find return, =, or start of expression.
		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			// Found statement start.
			if ( in_array( $code, array( T_RETURN, T_ECHO, T_PRINT ), true ) ) {
				return $i;
			}

			// Found assignment.
			if ( in_array( $code, array( T_EQUAL, T_DOUBLE_ARROW ), true ) ) {
				return $i;
			}

			// Hit a semicolon or opening brace - we've gone too far.
			if ( in_array( $code, array( T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_OPEN_PARENTHESIS, T_COMMA ), true ) ) {
				return $i + 1;
			}
		}

		return false;
	}

	/**
	 * Find a negative comparison operator (!== or !=) in the condition.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position to search.
	 * @param int  $end       End position to search.
	 *
	 * @return false|int
	 */
	private function findNegativeOperator( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $start; $i < $end; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], array( T_IS_NOT_IDENTICAL, T_IS_NOT_EQUAL ), true ) ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Check if the condition has logical operators (&& or ||).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position to search.
	 * @param int  $end       End position to search.
	 *
	 * @return bool
	 */
	private function hasLogicalOperators( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $start; $i < $end; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Find the colon that separates ternary branches.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the ? token.
	 *
	 * @return false|int
	 */
	private function findTernaryColon( File $phpcsFile, $stackPtr ) {
		$tokens      = $phpcsFile->getTokens();
		$nestedLevel = 0;

		for ( $i = $stackPtr + 1; $i < count( $tokens ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Track nested ternaries.
			if ( $code === T_INLINE_THEN ) {
				++$nestedLevel;
			} elseif ( $code === T_INLINE_ELSE ) {
				if ( $nestedLevel === 0 ) {
					return $i;
				}
				--$nestedLevel;
			}

			// Hit end of statement without finding colon.
			if ( $code === T_SEMICOLON ) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Find the end of the ternary expression.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $colonPtr  The position of the : token.
	 *
	 * @return false|int
	 */
	private function findTernaryEnd( File $phpcsFile, $colonPtr ) {
		$tokens       = $phpcsFile->getTokens();
		$parenDepth   = 0;
		$bracketDepth = 0;

		for ( $i = $colonPtr + 1; $i < count( $tokens ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Track parentheses depth.
			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
			} elseif ( $code === T_CLOSE_PARENTHESIS ) {
				if ( $parenDepth === 0 ) {
					return $i;
				}
				--$parenDepth;
			}

			// Track bracket depth.
			if ( $code === T_OPEN_SHORT_ARRAY || $code === T_OPEN_SQUARE_BRACKET ) {
				++$bracketDepth;
			} elseif ( $code === T_CLOSE_SHORT_ARRAY || $code === T_CLOSE_SQUARE_BRACKET ) {
				if ( $bracketDepth === 0 ) {
					return $i;
				}
				--$bracketDepth;
			}

			// End of statement.
			if ( $code === T_SEMICOLON && $parenDepth === 0 && $bracketDepth === 0 ) {
				return $i;
			}

			// Comma at depth 0 ends the ternary (e.g., in function args).
			if ( $code === T_COMMA && $parenDepth === 0 && $bracketDepth === 0 ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Get tokens as a string between two positions.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return string
	 */
	private function getTokensAsString( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();
		$result = '';

		for ( $i = $start; $i <= $end; $i++ ) {
			$result .= $tokens[ $i ]['content'];
		}

		return $result;
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
	 * Check if the comparison is against an excluded value (empty string or false).
	 *
	 * @param File $phpcsFile        The file being scanned.
	 * @param int  $negativeOperator The position of the !== or != token.
	 *
	 * @return bool
	 */
	private function isExcludedComparison( File $phpcsFile, $negativeOperator ) {
		$tokens = $phpcsFile->getTokens();

		// Check the token before the operator (skip whitespace).
		$before = $phpcsFile->findPrevious( T_WHITESPACE, $negativeOperator - 1, null, true );

		if ( false !== $before && $this->isExcludedValue( $tokens[ $before ] ) ) {
			return true;
		}

		// Check the token after the operator (skip whitespace).
		$after = $phpcsFile->findNext( T_WHITESPACE, $negativeOperator + 1, null, true );

		if ( false !== $after && $this->isExcludedValue( $tokens[ $after ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a token represents an excluded value (empty string, false, or null).
	 *
	 * @param array $token The token to check.
	 *
	 * @return bool
	 */
	private function isExcludedValue( $token ) {
		// Check for empty string.
		if ( $token['code'] === T_CONSTANT_ENCAPSED_STRING ) {
			$content = $token['content'];

			if ( $content === "''" || $content === '""' ) {
				return true;
			}
		}

		// Check for false or null.
		if ( in_array( $token['code'], array( T_FALSE, T_NULL ), true ) ) {
			return true;
		}

		return false;
	}
}
