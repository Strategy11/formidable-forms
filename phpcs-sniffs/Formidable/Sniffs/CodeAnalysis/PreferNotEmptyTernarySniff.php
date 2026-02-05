<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferNotEmptyTernarySniff
 *
 * Detects ternary expressions using `empty( A ) ? B : A` and converts them
 * to the preferred form `! empty( A ) ? A : B`.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Prefers `! empty( A ) ? A : B` over `empty( A ) ? B : A`.
 *
 * Bad:
 * empty( $var ) ? $default : $var
 *
 * Good:
 * ! empty( $var ) ? $var : $default
 */
class PreferNotEmptyTernarySniff implements Sniff {

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

		// Check if this empty() is NOT preceded by a negation (!).
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prevToken && $tokens[ $prevToken ]['code'] === T_BOOLEAN_NOT ) {
			// This is ! empty(), not what we're looking for.
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

		// Get the content inside empty() - this is "A".
		$emptyContent = $this->getTokensContentNormalized( $phpcsFile, $openParen + 1, $closeParen - 1 );

		if ( empty( $emptyContent['content'] ) ) {
			return;
		}

		// Find the ternary operator (?) after the empty().
		$ternaryOperator = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false === $ternaryOperator || $tokens[ $ternaryOperator ]['code'] !== T_INLINE_THEN ) {
			return;
		}

		// Find the colon (:) for the else part.
		$colonOperator = $this->findTernaryColon( $phpcsFile, $ternaryOperator + 1 );

		if ( false === $colonOperator ) {
			return;
		}

		// Get the "then" part (B in: empty(A) ? B : A).
		$thenContent = $this->getTokensContentNormalized( $phpcsFile, $ternaryOperator + 1, $colonOperator - 1 );

		if ( empty( $thenContent['content'] ) ) {
			return;
		}

		// Find the end of the ternary expression.
		$ternaryEnd = $this->findTernaryEnd( $phpcsFile, $colonOperator + 1 );

		if ( false === $ternaryEnd ) {
			return;
		}

		// Get the "else" part (A in: empty(A) ? B : A).
		$elseContent = $this->getTokensContentNormalized( $phpcsFile, $colonOperator + 1, $ternaryEnd );

		if ( empty( $elseContent['content'] ) ) {
			return;
		}

		// Check if the else part matches the empty() argument.
		if ( $elseContent['content'] !== $emptyContent['content'] ) {
			return;
		}

		// We have a match: empty( A ) ? B : A
		// Should become: ! empty( A ) ? A : B
		$fix = $phpcsFile->addFixableError(
			'Prefer "! empty( %s ) ? %s : %s" over "empty( %s ) ? %s : %s".',
			$stackPtr,
			'Found',
			array(
				$emptyContent['content'],
				$emptyContent['content'],
				$thenContent['content'],
				$emptyContent['content'],
				$thenContent['content'],
				$elseContent['content'],
			)
		);

		if ( true === $fix ) {
			$this->applyFix(
				$phpcsFile,
				$stackPtr,
				$closeParen,
				$ternaryOperator,
				$colonOperator,
				$ternaryEnd,
				$emptyContent,
				$thenContent
			);
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
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				if ( $parenDepth > 0 ) {
					--$parenDepth;
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

			// Stop at statement terminators or operators that end the ternary.
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
	 * Get the content of tokens between two positions, normalized (no extra whitespace).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return array Array with 'content', 'start', 'end', 'raw' keys.
	 */
	private function getTokensContentNormalized( File $phpcsFile, $start, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';
		$raw     = '';
		$first   = null;
		$last    = null;

		for ( $i = $start; $i <= $end; $i++ ) {
			$raw .= $tokens[ $i ]['content'];

			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			if ( null === $first ) {
				$first = $i;
			}

			$last     = $i;
			$content .= $tokens[ $i ]['content'];
		}

		return array(
			'content' => $content,
			'start'   => $first,
			'end'     => $last,
			'raw'     => trim( $raw ),
		);
	}

	/**
	 * Apply the fix to convert empty(A) ? B : A to ! empty(A) ? A : B.
	 *
	 * @param File  $phpcsFile       The file being scanned.
	 * @param int   $emptyToken      Position of empty keyword.
	 * @param int   $closeParen      Position of closing paren after empty.
	 * @param int   $ternaryOperator Position of ?.
	 * @param int   $colonOperator   Position of :.
	 * @param int   $ternaryEnd      Position of end of ternary.
	 * @param array $emptyContent    The A content.
	 * @param array $thenContent     The B content.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $emptyToken, $closeParen, $ternaryOperator, $colonOperator, $ternaryEnd, $emptyContent, $thenContent ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$fixer->beginChangeset();

		// Add "! " before empty.
		$fixer->addContentBefore( $emptyToken, '! ' );

		// Replace the "then" part (B) with A.
		$thenStart = $phpcsFile->findNext( T_WHITESPACE, $ternaryOperator + 1, $colonOperator, true );
		$thenEnd   = $phpcsFile->findPrevious( T_WHITESPACE, $colonOperator - 1, $ternaryOperator, true );

		if ( false !== $thenStart && false !== $thenEnd ) {
			// Clear the then part.
			for ( $i = $thenStart; $i <= $thenEnd; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}
			// Add A in place of B.
			$fixer->addContent( $thenStart - 1, ' ' . $emptyContent['raw'] . ' ' );
		}

		// Replace the "else" part (A) with B.
		// $ternaryEnd is the last token of the else expression (before semicolon/terminator).
		$elseStart = $phpcsFile->findNext( T_WHITESPACE, $colonOperator + 1, $ternaryEnd + 1, true );

		if ( false !== $elseStart ) {
			// Clear the else part (from elseStart to ternaryEnd inclusive).
			for ( $i = $elseStart; $i <= $ternaryEnd; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}
			// Add B in place of A.
			$fixer->addContent( $elseStart - 1, ' ' . $thenContent['raw'] );
		}

		$fixer->endChangeset();
	}
}
