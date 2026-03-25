<?php
/**
 * Formidable_Sniffs_CodeAnalysis_MergeNestedIfSniff
 *
 * Detects nested if statements that can be merged into a single condition
 * using the && operator.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Merges nested if statements into a single combined condition.
 *
 * Only applies when the outer if body contains exactly the inner if
 * and nothing else, and neither if has an else or elseif.
 *
 * Skips cases where comments exist between the two if statements,
 * since those comments likely describe intent that would be lost.
 *
 * Bad:
 * if ( $a ) {
 *     if ( $b ) {
 *         do_something();
 *     }
 * }
 *
 * Good:
 * if ( $a && $b ) {
 *     do_something();
 * }
 */
class MergeNestedIfSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_IF );
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

		// Must have scope opener/closer and parentheses.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'], $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		if ( ! isset( $tokens[ $stackPtr ]['parenthesis_opener'], $tokens[ $stackPtr ]['parenthesis_closer'] ) ) {
			return;
		}

		$outerOpener = $tokens[ $stackPtr ]['scope_opener'];
		$outerCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Check that the outer if has no else or elseif.
		$afterOuterCloser = $phpcsFile->findNext( T_WHITESPACE, $outerCloser + 1, null, true );

		if ( false !== $afterOuterCloser && in_array( $tokens[ $afterOuterCloser ]['code'], array( T_ELSE, T_ELSEIF ), true ) ) {
			return;
		}

		// Find the first non-whitespace token inside the outer if body.
		$firstInBody = $phpcsFile->findNext( T_WHITESPACE, $outerOpener + 1, $outerCloser, true );

		if ( false === $firstInBody || $tokens[ $firstInBody ]['code'] !== T_IF ) {
			return;
		}

		$innerIf = $firstInBody;

		// The inner if must have scope and parentheses.
		if ( ! isset( $tokens[ $innerIf ]['scope_opener'], $tokens[ $innerIf ]['scope_closer'] ) ) {
			return;
		}

		if ( ! isset( $tokens[ $innerIf ]['parenthesis_opener'], $tokens[ $innerIf ]['parenthesis_closer'] ) ) {
			return;
		}

		$innerCloser = $tokens[ $innerIf ]['scope_closer'];

		// Check that the inner if has no else or elseif.
		$afterInnerCloser = $phpcsFile->findNext( T_WHITESPACE, $innerCloser + 1, $outerCloser, true );

		if ( false !== $afterInnerCloser ) {
			if ( in_array( $tokens[ $afterInnerCloser ]['code'], array( T_ELSE, T_ELSEIF ), true ) ) {
				return;
			}

			// There is other code after the inner if inside the outer if body.
			return;
		}

		// Check for comments between the outer if opening brace and the inner if.
		// If comments exist, skip to preserve developer intent.
		if ( $this->hasCommentsBetween( $phpcsFile, $outerOpener, $innerIf ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Nested if statements can be merged into a single condition using &&.',
			$stackPtr,
			'Found'
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $innerIf );
		}
	}

	/**
	 * Check if there are comments between two token positions.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     The start token position (exclusive).
	 * @param int  $end       The end token position (exclusive).
	 *
	 * @return bool True if comments exist between the positions.
	 */
	private function hasCommentsBetween( File $phpcsFile, $start, $end ) {
		$tokens       = $phpcsFile->getTokens();
		$commentTypes = array(
			T_COMMENT,
			T_DOC_COMMENT,
			T_DOC_COMMENT_OPEN_TAG,
			T_DOC_COMMENT_CLOSE_TAG,
			T_DOC_COMMENT_STAR,
			T_DOC_COMMENT_STRING,
			T_DOC_COMMENT_TAG,
			T_DOC_COMMENT_WHITESPACE,
		);

		for ( $i = $start + 1; $i < $end; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], $commentTypes, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Apply the fix by merging the two if conditions.
	 *
	 * Combines the outer and inner conditions with && and replaces the
	 * outer if with a single if using the inner if's body. The inner
	 * body is dedented by one level.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $outerIf   The outer if token position.
	 * @param int  $innerIf   The inner if token position.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $outerIf, $innerIf ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$outerCondOpener = $tokens[ $outerIf ]['parenthesis_opener'];
		$outerCondCloser = $tokens[ $outerIf ]['parenthesis_closer'];
		$outerOpener     = $tokens[ $outerIf ]['scope_opener'];
		$outerCloser     = $tokens[ $outerIf ]['scope_closer'];

		$innerCondOpener = $tokens[ $innerIf ]['parenthesis_opener'];
		$innerCondCloser = $tokens[ $innerIf ]['parenthesis_closer'];
		$innerOpener     = $tokens[ $innerIf ]['scope_opener'];
		$innerCloser     = $tokens[ $innerIf ]['scope_closer'];

		// Extract outer condition.
		$outerCondition = $this->getTokenContent( $phpcsFile, $outerCondOpener + 1, $outerCondCloser );
		$outerCondition = trim( $outerCondition );

		// Extract inner condition.
		$innerCondition = $this->getTokenContent( $phpcsFile, $innerCondOpener + 1, $innerCondCloser );
		$innerCondition = trim( $innerCondition );

		// Extract inner body content.
		$innerBody = $this->getTokenContent( $phpcsFile, $innerOpener + 1, $innerCloser );
		$innerBody = $this->dedentCode( $innerBody );

		// Remove trailing whitespace (indentation of the inner closing brace).
		$innerBody = rtrim( $innerBody );

		// Get indentation of the outer if.
		$indent = $this->getIndentation( $phpcsFile, $outerIf );
		$eol    = $phpcsFile->eolChar;

		$fixer->beginChangeset();

		// Clear all tokens from the outer if to the outer closing brace.
		for ( $i = $outerIf; $i <= $outerCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Build the merged if statement.
		$newCode  = 'if ( ' . $outerCondition . ' && ' . $innerCondition . ' ) {';
		$newCode .= $innerBody;
		$newCode .= $eol . $indent . '}';

		$fixer->addContent( $outerIf, $newCode );

		$fixer->endChangeset();
	}

	/**
	 * Get concatenated token content between two positions.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     The start position (inclusive).
	 * @param int  $end       The end position (exclusive).
	 *
	 * @return string The concatenated content.
	 */
	private function getTokenContent( File $phpcsFile, $start, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';

		for ( $i = $start; $i < $end; $i++ ) {
			$content .= $tokens[ $i ]['content'];
		}

		return $content;
	}

	/**
	 * Remove one level of indentation from code.
	 *
	 * @param string $code The code to dedent.
	 *
	 * @return string The dedented code.
	 */
	private function dedentCode( $code ) {
		$lines  = explode( "\n", $code );
		$result = array();

		foreach ( $lines as $line ) {
			if ( strpos( $line, "\t" ) === 0 ) {
				$line = substr( $line, 1 );
			} elseif ( strpos( $line, '    ' ) === 0 ) {
				$line = substr( $line, 4 );
			}
			$result[] = $line;
		}

		return implode( "\n", $result );
	}

	/**
	 * Get the indentation of a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The token position.
	 *
	 * @return string The indentation string.
	 */
	private function getIndentation( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the first token on this line.
		$lineStart = $stackPtr;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			--$lineStart;
		}

		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			return $tokens[ $lineStart ]['content'];
		}

		return '';
	}
}
