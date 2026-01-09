<?php
/**
 * Formidable_Sniffs_WhiteSpace_ConsecutiveAssignmentSpacingSniff
 *
 * Detects blank lines between consecutive simple variable assignments.
 * Groups of simple one-line assignments should not have blank lines between them.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects blank lines between consecutive simple variable assignments.
 *
 * Bad:
 * $slug = $id;
 *
 * $base_file = $addon['file'] ?? 'formidable-' . $slug;
 *
 * $file_name = $base_file . '/' . $base_file . '.php';
 *
 * Good:
 * $slug = $id;
 * $base_file = $addon['file'] ?? 'formidable-' . $slug;
 * $file_name = $base_file . '/' . $base_file . '.php';
 */
class ConsecutiveAssignmentSpacingSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_VARIABLE );
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

		// Check if this is a simple assignment (variable at start of statement).
		if ( ! $this->isSimpleAssignmentStart( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Find the end of this assignment (the semicolon).
		$semicolon = $this->findAssignmentEnd( $phpcsFile, $stackPtr );

		if ( false === $semicolon ) {
			return;
		}

		// Check if this assignment spans multiple lines (not simple).
		if ( ! $this->isSingleLineAssignment( $phpcsFile, $stackPtr, $semicolon ) ) {
			return;
		}

		// Look for the next statement.
		$nextStatement = $this->findNextStatement( $phpcsFile, $semicolon );

		if ( false === $nextStatement ) {
			return;
		}

		// Check if the next statement is also a simple one-line assignment.
		if ( ! $this->isSimpleAssignmentStart( $phpcsFile, $nextStatement ) ) {
			return;
		}

		$nextSemicolon = $this->findAssignmentEnd( $phpcsFile, $nextStatement );

		if ( false === $nextSemicolon ) {
			return;
		}

		if ( ! $this->isSingleLineAssignment( $phpcsFile, $nextStatement, $nextSemicolon ) ) {
			return;
		}

		// Check if there are blank lines between the two assignments.
		$currentLine = $tokens[ $semicolon ]['line'];
		$nextLine    = $tokens[ $nextStatement ]['line'];

		// If there's more than one line difference, there are blank lines.
		if ( $nextLine - $currentLine <= 1 ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Blank lines found between consecutive simple assignments. Remove blank lines to group related assignments.',
			$nextStatement,
			'BlankLineBetweenAssignments'
		);

		if ( true === $fix ) {
			$this->removeBlankLines( $phpcsFile, $semicolon, $nextStatement );
		}
	}

	/**
	 * Check if the token is the start of a simple variable assignment.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the variable token.
	 *
	 * @return bool
	 */
	private function isSimpleAssignmentStart( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Must be a variable.
		if ( $tokens[ $stackPtr ]['code'] !== T_VARIABLE ) {
			return false;
		}

		// Check what's before the variable - should be start of statement.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken ) {
			return true;
		}

		// Valid tokens before a statement-starting variable.
		$validBefore = array(
			T_OPEN_CURLY_BRACKET,
			T_CLOSE_CURLY_BRACKET,
			T_SEMICOLON,
			T_COLON,
			T_OPEN_TAG,
			T_CLOSE_TAG,
		);

		if ( ! in_array( $tokens[ $prevToken ]['code'], $validBefore, true ) ) {
			return false;
		}

		// Check what's after the variable - should be an assignment operator.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken ) {
			return false;
		}

		// Must be followed by an assignment operator.
		$assignmentOps = array(
			\T_EQUAL,
			\T_PLUS_EQUAL,
			\T_MINUS_EQUAL,
			\T_MUL_EQUAL,
			\T_DIV_EQUAL,
			\T_CONCAT_EQUAL,
			\T_COALESCE_EQUAL,
		);

		return in_array( $tokens[ $nextToken ]['code'], $assignmentOps, true );
	}

	/**
	 * Find the semicolon that ends this assignment.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the variable token.
	 *
	 * @return false|int
	 */
	private function findAssignmentEnd( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the semicolon, but be careful of nested structures.
		$nestingLevel = 0;

		for ( $i = $stackPtr; $i < $phpcsFile->numTokens; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( in_array( $code, array( T_OPEN_PARENTHESIS, T_OPEN_SQUARE_BRACKET, T_OPEN_CURLY_BRACKET ), true ) ) {
				++$nestingLevel;
			} elseif ( in_array( $code, array( T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET, T_CLOSE_CURLY_BRACKET ), true ) ) {
				--$nestingLevel;
			} elseif ( $code === T_SEMICOLON && $nestingLevel === 0 ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Check if the assignment is on a single line.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  The position of the variable token.
	 * @param int  $endPtr    The position of the semicolon.
	 *
	 * @return bool
	 */
	private function isSingleLineAssignment( File $phpcsFile, $startPtr, $endPtr ) {
		$tokens    = $phpcsFile->getTokens();
		$startLine = $tokens[ $startPtr ]['line'];
		$endLine   = $tokens[ $endPtr ]['line'];

		return $startLine === $endLine;
	}

	/**
	 * Find the next statement after the semicolon.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $semicolon The position of the semicolon.
	 *
	 * @return false|int
	 */
	private function findNextStatement( File $phpcsFile, $semicolon ) {
		$tokens = $phpcsFile->getTokens();

		// Skip whitespace and comments to find the next meaningful token.
		$skip = array(
			T_WHITESPACE,
			T_COMMENT,
			T_DOC_COMMENT,
			T_DOC_COMMENT_OPEN_TAG,
			T_DOC_COMMENT_CLOSE_TAG,
			T_DOC_COMMENT_STAR,
			T_DOC_COMMENT_STRING,
			T_DOC_COMMENT_TAG,
			T_DOC_COMMENT_WHITESPACE,
		);

		$next = $phpcsFile->findNext( $skip, $semicolon + 1, null, true );

		if ( false === $next ) {
			return false;
		}

		// If there's a comment between the statements, don't flag this.
		for ( $i = $semicolon + 1; $i < $next; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], array( T_COMMENT, T_DOC_COMMENT, T_DOC_COMMENT_OPEN_TAG ), true ) ) {
				return false;
			}
		}

		// Must be a variable for us to consider it.
		if ( $tokens[ $next ]['code'] !== T_VARIABLE ) {
			return false;
		}

		return $next;
	}

	/**
	 * Remove blank lines between two tokens.
	 *
	 * @param File $phpcsFile     The file being scanned.
	 * @param int  $semicolon     The position of the first semicolon.
	 * @param int  $nextStatement The position of the next statement.
	 *
	 * @return void
	 */
	private function removeBlankLines( File $phpcsFile, $semicolon, $nextStatement ) {
		$tokens = $phpcsFile->getTokens();

		$phpcsFile->fixer->beginChangeset();

		// Get the indentation of the next statement.
		$indent = '';

		if ( $tokens[ $nextStatement ]['column'] > 1 ) {
			// Look at the whitespace token right before the next statement to determine indent style.
			$prevToken = $nextStatement - 1;

			if ( $tokens[ $prevToken ]['code'] === T_WHITESPACE ) {
				$wsContent = $tokens[ $prevToken ]['content'];

				// Extract just the indentation part (after the last newline).
				$lastNewline = strrpos( $wsContent, "\n" );

				if ( false !== $lastNewline ) {
					$indent = substr( $wsContent, $lastNewline + 1 );
				} else {
					$indent = $wsContent;
				}
			}
		}

		// Replace all whitespace between semicolon and next statement with single newline + indent.
		$first = true;

		for ( $i = $semicolon + 1; $i < $nextStatement; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				if ( $first ) {
					$phpcsFile->fixer->replaceToken( $i, "\n" . $indent );
					$first = false;
				} else {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}
		}

		$phpcsFile->fixer->endChangeset();
	}
}
