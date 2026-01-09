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
	 * Maximum number of assignments to group together.
	 *
	 * @var int
	 */
	const MAX_GROUP_SIZE = 5;

	/**
	 * Track processed tokens to avoid duplicate errors.
	 *
	 * @var array
	 */
	private $processedTokens = array();

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
		$tokens   = $phpcsFile->getTokens();
		$filename = $phpcsFile->getFilename();

		// Skip if we've already processed this token as part of a group.
		$tokenKey = $filename . ':' . $stackPtr;

		if ( isset( $this->processedTokens[ $tokenKey ] ) ) {
			return;
		}

		// Check if this is a simple assignment (variable at start of statement).
		if ( ! $this->isSimpleAssignmentStart( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Collect the entire group of consecutive simple assignments.
		$group = $this->collectAssignmentGroup( $phpcsFile, $stackPtr );

		// Mark all tokens in the group as processed.
		foreach ( $group as $assignment ) {
			$key = $filename . ':' . $assignment['variable'];
			$this->processedTokens[ $key ] = true;
		}

		// If group is too large (more than MAX_GROUP_SIZE), don't suggest grouping.
		if ( count( $group ) > self::MAX_GROUP_SIZE ) {
			return;
		}

		// Single-item groups don't need checking.
		if ( count( $group ) < 2 ) {
			return;
		}

		// Check if the same variable is assigned multiple times.
		if ( $this->hasDuplicateVariables( $phpcsFile, $group ) ) {
			return;
		}

		// Now check for blank lines between consecutive assignments in the group.
		for ( $i = 0; $i < count( $group ) - 1; $i++ ) {
			$current = $group[ $i ];
			$next    = $group[ $i + 1 ];

			$currentLine = $tokens[ $current['semicolon'] ]['line'];
			$nextLine    = $tokens[ $next['variable'] ]['line'];

			// If there's more than one line difference, there are blank lines.
			if ( $nextLine - $currentLine <= 1 ) {
				continue;
			}

			$fix = $phpcsFile->addFixableError(
				'Blank lines found between consecutive simple assignments. Remove blank lines to group related assignments.',
				$next['variable'],
				'BlankLineBetweenAssignments'
			);

			if ( true === $fix ) {
				$this->removeBlankLines( $phpcsFile, $current['semicolon'], $next['variable'] );
			}
		}
	}

	/**
	 * Collect all consecutive simple one-line assignments starting from the given token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the first variable token.
	 *
	 * @return array Array of assignments with 'variable' and 'semicolon' keys.
	 */
	private function collectAssignmentGroup( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$group  = array();

		$currentVar = $stackPtr;

		while ( false !== $currentVar ) {
			// Check if this is a simple assignment.
			if ( ! $this->isSimpleAssignmentStart( $phpcsFile, $currentVar ) ) {
				break;
			}

			$semicolon = $this->findAssignmentEnd( $phpcsFile, $currentVar );

			if ( false === $semicolon ) {
				break;
			}

			// Must be single line.
			if ( ! $this->isSingleLineAssignment( $phpcsFile, $currentVar, $semicolon ) ) {
				break;
			}

			// Check if there's a comment before this assignment.
			// If so, this assignment should start a new group, not continue the current one.
			if ( count( $group ) > 0 && $this->hasCommentBeforeVariable( $phpcsFile, $currentVar ) ) {
				break;
			}

			$group[] = array(
				'variable'  => $currentVar,
				'semicolon' => $semicolon,
			);

			// Find the next statement.
			$nextStatement = $this->findNextStatement( $phpcsFile, $semicolon );

			if ( false === $nextStatement ) {
				break;
			}

			// Check if there's a comment between this assignment and the next.
			// If so, stop the group here - comments indicate intentional separation.
			if ( $this->hasCommentBetween( $phpcsFile, $semicolon, $nextStatement ) ) {
				break;
			}

			// Check if there's a blank line - if not, they're already grouped, keep collecting.
			// If there is a blank line, we still want to collect to check the full group size.
			$currentLine = $tokens[ $semicolon ]['line'];
			$nextLine    = $tokens[ $nextStatement ]['line'];

			// If more than 2 blank lines, consider it a separate group.
			if ( $nextLine - $currentLine > 2 ) {
				break;
			}

			$currentVar = $nextStatement;
		}

		return $group;
	}

	/**
	 * Check if the group has duplicate variable assignments (same variable assigned multiple times).
	 *
	 * This checks for:
	 * 1. Same variable assigned multiple times (e.g., $styles[] = ... multiple times)
	 * 2. Base variable and array push (e.g., $styles = array(); followed by $styles[] = ...)
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $group     The group of assignments.
	 *
	 * @return bool True if there are duplicate variables.
	 */
	private function hasDuplicateVariables( File $phpcsFile, $group ) {
		$tokens        = $phpcsFile->getTokens();
		$baseVariables = array();

		foreach ( $group as $assignment ) {
			$varPtr      = $assignment['variable'];
			$baseVarName = $tokens[ $varPtr ]['content'];

			// Check if we've seen this base variable before.
			// This catches $styles = array(); followed by $styles[] = ...
			// as well as multiple $styles[] = ... assignments.
			if ( isset( $baseVariables[ $baseVarName ] ) ) {
				return true;
			}

			$baseVariables[ $baseVarName ] = true;
		}

		return false;
	}

	/**
	 * Check if there's a comment before the variable on the same or previous lines.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $varPtr    The position of the variable token.
	 *
	 * @return bool True if there's a comment before the variable.
	 */
	private function hasCommentBeforeVariable( File $phpcsFile, $varPtr ) {
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

		// Look backwards from the variable to find the previous semicolon or brace.
		$prevStatement = $phpcsFile->findPrevious( array( T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET ), $varPtr - 1 );

		if ( false === $prevStatement ) {
			return false;
		}

		// Check if there's a comment between the previous statement and this variable.
		for ( $i = $prevStatement + 1; $i < $varPtr; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], $commentTypes, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if there's a comment between two token positions.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  The start position.
	 * @param int  $endPtr    The end position.
	 *
	 * @return bool True if there's a comment between the positions.
	 */
	private function hasCommentBetween( File $phpcsFile, $startPtr, $endPtr ) {
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

		for ( $i = $startPtr + 1; $i < $endPtr; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], $commentTypes, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a signature for the variable being assigned.
	 * This includes array access like $styles[] or $data['key'].
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $varPtr    The position of the variable token.
	 *
	 * @return string The variable signature.
	 */
	private function getVariableSignature( File $phpcsFile, $varPtr ) {
		$tokens    = $phpcsFile->getTokens();
		$signature = $tokens[ $varPtr ]['content'];

		// Check for array access after the variable.
		$next = $phpcsFile->findNext( T_WHITESPACE, $varPtr + 1, null, true );

		if ( false !== $next && $tokens[ $next ]['code'] === T_OPEN_SQUARE_BRACKET ) {
			// Include the array access in the signature.
			$closeBracket = $tokens[ $next ]['bracket_closer'];

			for ( $i = $next; $i <= $closeBracket; $i++ ) {
				$signature .= $tokens[ $i ]['content'];
			}
		}

		return $signature;
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
		// Skip whitespace and comments.
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

		$prevToken = $phpcsFile->findPrevious( $skip, $stackPtr - 1, null, true );

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

		// Check what's after the variable - should be an assignment operator or array access.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken ) {
			return false;
		}

		// Handle array access like $styles[] or $data['key'].
		if ( $tokens[ $nextToken ]['code'] === T_OPEN_SQUARE_BRACKET ) {
			// Skip past the array access to find the assignment operator.
			if ( ! isset( $tokens[ $nextToken ]['bracket_closer'] ) ) {
				return false;
			}

			$closeBracket = $tokens[ $nextToken ]['bracket_closer'];
			$nextToken    = $phpcsFile->findNext( T_WHITESPACE, $closeBracket + 1, null, true );

			if ( false === $nextToken ) {
				return false;
			}
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
