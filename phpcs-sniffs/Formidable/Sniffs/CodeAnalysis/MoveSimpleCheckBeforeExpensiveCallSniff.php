<?php
/**
 * Formidable_Sniffs_CodeAnalysis_MoveSimpleCheckBeforeExpensiveCallSniff
 *
 * Detects when a simple/cheap check (like empty(), isset()) is combined with
 * an expensive function call in an early return condition, and suggests
 * moving the cheap check first.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects conditions where a cheap check should be moved before an expensive call.
 *
 * Bad:
 * $action = expensive_function();
 * if ( ! $action || empty( $args['id'] ) ) {
 *     return;
 * }
 *
 * Good:
 * if ( empty( $args['id'] ) ) {
 *     return;
 * }
 * $action = expensive_function();
 * if ( ! $action ) {
 *     return;
 * }
 */
class MoveSimpleCheckBeforeExpensiveCallSniff implements Sniff {

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

		// Must have parentheses.
		if ( ! isset( $tokens[ $stackPtr ]['parenthesis_opener'] ) || ! isset( $tokens[ $stackPtr ]['parenthesis_closer'] ) ) {
			return;
		}

		// Must have a scope (braces).
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		// Check if this is an early return pattern.
		if ( ! $this->isEarlyReturnPattern( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$conditionOpener = $tokens[ $stackPtr ]['parenthesis_opener'];
		$conditionCloser = $tokens[ $stackPtr ]['parenthesis_closer'];

		// Look for || (T_BOOLEAN_OR) in the condition.
		$orOperator = $phpcsFile->findNext( T_BOOLEAN_OR, $conditionOpener + 1, $conditionCloser );

		if ( false === $orOperator ) {
			return;
		}

		// Get the parts before and after the ||.
		$leftPart  = $this->getConditionPart( $phpcsFile, $conditionOpener + 1, $orOperator );
		$rightPart = $this->getConditionPart( $phpcsFile, $orOperator + 1, $conditionCloser );

		// Check if one part is a simple check and the other references a variable assigned just before.
		$this->checkForOptimization( $phpcsFile, $stackPtr, $leftPart, $rightPart, $conditionOpener, $conditionCloser );
	}

	/**
	 * Check if this is an early return pattern (if body only contains return).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The if token position.
	 *
	 * @return bool
	 */
	private function isEarlyReturnPattern( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find the first non-whitespace token inside the if.
		$first = $phpcsFile->findNext( T_WHITESPACE, $scopeOpener + 1, $scopeCloser, true );

		if ( false === $first ) {
			return false;
		}

		// Should be return.
		if ( $tokens[ $first ]['code'] !== T_RETURN ) {
			return false;
		}

		// Find the semicolon.
		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $first + 1, $scopeCloser );

		if ( false === $semicolon ) {
			return false;
		}

		// Check nothing else after the semicolon (except whitespace).
		$afterSemicolon = $phpcsFile->findNext( T_WHITESPACE, $semicolon + 1, $scopeCloser, true );

		return false === $afterSemicolon;
	}

	/**
	 * Get a condition part as an array of tokens.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return array Array with 'start', 'end', 'content', 'tokens'.
	 */
	private function getConditionPart( File $phpcsFile, $start, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';
		$partTokens = array();

		// Skip leading whitespace.
		while ( $start < $end && $tokens[ $start ]['code'] === T_WHITESPACE ) {
			++$start;
		}

		// Skip trailing whitespace.
		$realEnd = $end - 1;

		while ( $realEnd > $start && $tokens[ $realEnd ]['code'] === T_WHITESPACE ) {
			--$realEnd;
		}

		for ( $i = $start; $i <= $realEnd; $i++ ) {
			$content .= $tokens[ $i ]['content'];
			$partTokens[] = $i;
		}

		return array(
			'start'   => $start,
			'end'     => $realEnd,
			'content' => trim( $content ),
			'tokens'  => $partTokens,
		);
	}

	/**
	 * Check if we can optimize by moving a simple check before an expensive call.
	 *
	 * @param File  $phpcsFile       The file being scanned.
	 * @param int   $stackPtr        The if token position.
	 * @param array $leftPart        Left part of the condition.
	 * @param array $rightPart       Right part of the condition.
	 * @param int   $conditionOpener The condition opener position.
	 * @param int   $conditionCloser The condition closer position.
	 *
	 * @return void
	 */
	private function checkForOptimization( File $phpcsFile, $stackPtr, $leftPart, $rightPart, $conditionOpener, $conditionCloser ) {
		$tokens = $phpcsFile->getTokens();

		// Check if right part is a simple check (empty, isset, is_null, etc.).
		$simpleCheckPart   = null;
		$variableCheckPart = null;

		if ( $this->isSimpleCheck( $rightPart['content'] ) ) {
			$simpleCheckPart   = $rightPart;
			$variableCheckPart = $leftPart;
		} elseif ( $this->isSimpleCheck( $leftPart['content'] ) ) {
			// Simple check is already first - no optimization needed.
			return;
		} else {
			return;
		}

		// Check if the variable check part references a variable.
		$variableName = $this->extractVariableName( $variableCheckPart['content'] );

		if ( ! $variableName ) {
			return;
		}

		// Look for an assignment to this variable just before the if.
		$assignmentData = $this->findPrecedingAssignment( $phpcsFile, $stackPtr, $variableName );

		if ( false === $assignmentData ) {
			return;
		}

		// Check if the assignment is an expensive call (function call).
		if ( ! $this->isExpensiveAssignment( $phpcsFile, $assignmentData['variable'] ) ) {
			return;
		}

		// Check that the simple check doesn't use the assigned variable.
		if ( strpos( $simpleCheckPart['content'], $variableName ) !== false ) {
			return;
		}

		// Found an optimization opportunity!
		$fix = $phpcsFile->addFixableError(
			'Move the simple check "%s" before the expensive function call to avoid unnecessary execution.',
			$stackPtr,
			'Found',
			array( $simpleCheckPart['content'] )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $assignmentData, $simpleCheckPart, $variableCheckPart, $conditionOpener, $conditionCloser );
		}
	}

	/**
	 * Apply the fix to move the simple check before the expensive call.
	 *
	 * @param File  $phpcsFile         The file being scanned.
	 * @param int   $stackPtr          The if token position.
	 * @param array $assignmentData    Data about the assignment statement.
	 * @param array $simpleCheckPart   The simple check part of the condition.
	 * @param array $variableCheckPart The variable check part of the condition.
	 * @param int   $conditionOpener   The condition opener position.
	 * @param int   $conditionCloser   The condition closer position.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $stackPtr, $assignmentData, $simpleCheckPart, $variableCheckPart, $conditionOpener, $conditionCloser ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		// Get the if body (return statement).
		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Get the indentation from the original if statement.
		$indent = $this->getIndentation( $phpcsFile, $stackPtr );

		// Get the original return statement content.
		$returnStatement = $this->getReturnStatement( $phpcsFile, $scopeOpener, $scopeCloser );

		// Get the assignment statement content (trimmed).
		$assignmentContent = '';

		for ( $i = $assignmentData['start']; $i <= $assignmentData['semicolon']; $i++ ) {
			$assignmentContent .= $tokens[ $i ]['content'];
		}

		$assignmentContent = ltrim( $assignmentContent );

		$fixer->beginChangeset();

		// Remove the assignment statement.
		for ( $i = $assignmentData['start']; $i <= $assignmentData['semicolon']; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Remove whitespace/newlines between assignment and original if.
		for ( $i = $assignmentData['semicolon'] + 1; $i < $stackPtr; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Build the new code:
		// 1. New if with simple check and early return
		// 2. Original assignment
		// 3. Modified if with only the variable check
		$newCode  = $phpcsFile->eolChar;
		$newCode .= $indent . 'if ( ' . $simpleCheckPart['content'] . ' ) {' . $phpcsFile->eolChar;
		$newCode .= $indent . "\t" . $returnStatement . $phpcsFile->eolChar;
		$newCode .= $indent . '}' . $phpcsFile->eolChar;
		$newCode .= $phpcsFile->eolChar;
		$newCode .= $indent . $assignmentContent . $phpcsFile->eolChar;
		$newCode .= $phpcsFile->eolChar;
		$newCode .= $indent . 'if ( ' . $variableCheckPart['content'] . ' ) {' . $phpcsFile->eolChar;
		$newCode .= $indent . "\t" . $returnStatement . $phpcsFile->eolChar;
		$newCode .= $indent . '}';

		// Replace the original if statement with the new code.
		$fixer->replaceToken( $stackPtr, $newCode );

		// Remove the rest of the original if (condition and body).
		for ( $i = $stackPtr + 1; $i <= $scopeCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		$fixer->endChangeset();
	}

	/**
	 * Get the return statement from the if body.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $scopeOpener The scope opener position.
	 * @param int  $scopeCloser The scope closer position.
	 *
	 * @return string The return statement (e.g., "return;", "return false;").
	 */
	private function getReturnStatement( File $phpcsFile, $scopeOpener, $scopeCloser ) {
		$tokens = $phpcsFile->getTokens();

		// Find the return token.
		$returnToken = $phpcsFile->findNext( T_RETURN, $scopeOpener + 1, $scopeCloser );

		if ( false === $returnToken ) {
			return 'return;';
		}

		// Find the semicolon.
		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $returnToken + 1, $scopeCloser );

		if ( false === $semicolon ) {
			return 'return;';
		}

		// Get everything from return to semicolon.
		$statement = '';

		for ( $i = $returnToken; $i <= $semicolon; $i++ ) {
			$statement .= $tokens[ $i ]['content'];
		}

		return trim( $statement );
	}

	/**
	 * Get the indentation of a token's line.
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

		// If the first token is whitespace, that's our indentation.
		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			return $tokens[ $lineStart ]['content'];
		}

		return '';
	}

	/**
	 * Check if a condition part is a simple/cheap check.
	 *
	 * @param string $content The condition content.
	 *
	 * @return bool
	 */
	private function isSimpleCheck( $content ) {
		$content = trim( $content );

		// Remove leading ! or spaces.
		$content = ltrim( $content, '! ' );

		// Check for empty(), isset(), is_null(), etc.
		$simpleFunctions = array(
			'empty(',
			'isset(',
			'is_null(',
			'is_array(',
			'is_string(',
			'is_numeric(',
			'is_int(',
			'is_bool(',
			'is_object(',
		);

		foreach ( $simpleFunctions as $func ) {
			if ( strpos( $content, $func ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Extract the variable name from a condition part.
	 *
	 * @param string $content The condition content.
	 *
	 * @return string|false The variable name or false.
	 */
	private function extractVariableName( $content ) {
		$content = trim( $content );

		// Remove leading !.
		$content = ltrim( $content, '! ' );

		// Check if it's just a variable.
		if ( preg_match( '/^\$[a-zA-Z_][a-zA-Z0-9_]*$/', $content ) ) {
			return $content;
		}

		return false;
	}

	/**
	 * Find a preceding assignment to a variable.
	 *
	 * The assignment must be immediately before the if statement (only whitespace/newlines between).
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param int    $stackPtr     The if token position.
	 * @param string $variableName The variable name to find.
	 *
	 * @return array|false Array with 'variable', 'start', 'end', 'semicolon' or false.
	 */
	private function findPrecedingAssignment( File $phpcsFile, $stackPtr, $variableName ) {
		$tokens = $phpcsFile->getTokens();

		// Find the semicolon immediately before the if (skipping only whitespace).
		$current = $stackPtr - 1;

		while ( $current > 0 && $tokens[ $current ]['code'] === T_WHITESPACE ) {
			--$current;
		}

		// Must be a semicolon.
		if ( $tokens[ $current ]['code'] !== T_SEMICOLON ) {
			return false;
		}

		$semicolon = $current;

		// Find the start of this statement.
		$statementStart = $this->findStatementStart( $phpcsFile, $current );

		if ( false === $statementStart ) {
			return false;
		}

		// Check if this statement is an assignment to our variable.
		$firstToken = $phpcsFile->findNext( T_WHITESPACE, $statementStart, $current, true );

		if ( false === $firstToken || $tokens[ $firstToken ]['code'] !== T_VARIABLE ) {
			return false;
		}

		if ( $tokens[ $firstToken ]['content'] !== $variableName ) {
			return false;
		}

		// Check for = after the variable.
		$equals = $phpcsFile->findNext( T_WHITESPACE, $firstToken + 1, $current, true );

		if ( false === $equals || $tokens[ $equals ]['code'] !== T_EQUAL ) {
			return false;
		}

		return array(
			'variable'  => $firstToken,
			'start'     => $statementStart,
			'end'       => $semicolon,
			'semicolon' => $semicolon,
		);
	}

	/**
	 * Find the start of a statement.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $semicolon The semicolon position.
	 *
	 * @return int|false The statement start position or false.
	 */
	private function findStatementStart( File $phpcsFile, $semicolon ) {
		$tokens = $phpcsFile->getTokens();

		$current = $semicolon - 1;

		while ( $current > 0 ) {
			$code = $tokens[ $current ]['code'];

			// Stop at statement boundaries.
			if ( in_array( $code, array( T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET ), true ) ) {
				return $current + 1;
			}

			--$current;
		}

		return false;
	}

	/**
	 * Check if an assignment is to an expensive function call.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $assignment The assignment position.
	 *
	 * @return bool
	 */
	private function isExpensiveAssignment( File $phpcsFile, $assignment ) {
		$tokens = $phpcsFile->getTokens();

		// Find the = sign.
		$equals = $phpcsFile->findNext( T_EQUAL, $assignment + 1 );

		if ( false === $equals ) {
			return false;
		}

		// Find what comes after the =.
		$afterEquals = $phpcsFile->findNext( T_WHITESPACE, $equals + 1, null, true );

		if ( false === $afterEquals ) {
			return false;
		}

		// Check if it's a function call (T_STRING followed by open paren).
		if ( $tokens[ $afterEquals ]['code'] === T_STRING ) {
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $afterEquals + 1, null, true );

			if ( false !== $nextToken && $tokens[ $nextToken ]['code'] === T_OPEN_PARENTHESIS ) {
				return true;
			}
		}

		// Check for static method call (Class::method()).
		if ( $tokens[ $afterEquals ]['code'] === T_STRING || $tokens[ $afterEquals ]['code'] === T_SELF || $tokens[ $afterEquals ]['code'] === T_STATIC ) {
			$doubleColon = $phpcsFile->findNext( T_WHITESPACE, $afterEquals + 1, null, true );

			if ( false !== $doubleColon && $tokens[ $doubleColon ]['code'] === T_DOUBLE_COLON ) {
				return true;
			}
		}

		// Check for object method call ($obj->method()).
		if ( $tokens[ $afterEquals ]['code'] === T_VARIABLE ) {
			$arrow = $phpcsFile->findNext( T_WHITESPACE, $afterEquals + 1, null, true );

			if ( false !== $arrow && $tokens[ $arrow ]['code'] === T_OBJECT_OPERATOR ) {
				return true;
			}
		}

		return false;
	}
}
