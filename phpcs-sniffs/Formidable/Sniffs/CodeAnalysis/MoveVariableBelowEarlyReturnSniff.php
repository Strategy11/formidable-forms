<?php
/**
 * Formidable_Sniffs_CodeAnalysis_MoveVariableBelowEarlyReturnSniff
 *
 * Detects variable declarations that should be moved below early return conditions.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects variable declarations that should be moved below early return conditions.
 *
 * Bad:
 * function example($form) {
 *     $style = 1;
 *
 *     if ( ! $form ) {
 *         return;
 *     }
 *
 *     return $style;
 * }
 *
 * Good:
 * function example($form) {
 *     if ( ! $form ) {
 *         return;
 *     }
 *
 *     $style = 1;
 *
 *     return $style;
 * }
 */
class MoveVariableBelowEarlyReturnSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FUNCTION, T_CLOSURE );
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

		// Make sure this function has a scope.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$functionOpener = $tokens[ $stackPtr ]['scope_opener'];
		$functionCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find the first statement in the function.
		$firstStatement = $phpcsFile->findNext( T_WHITESPACE, $functionOpener + 1, $functionCloser, true );

		if ( false === $firstStatement ) {
			return;
		}

		// Check if the first statement is a variable assignment.
		if ( $tokens[ $firstStatement ]['code'] !== T_VARIABLE ) {
			return;
		}

		// Get the variable name.
		$variableName = $tokens[ $firstStatement ]['content'];

		// Check if this variable is a function parameter (skip those, especially by-reference params).
		if ( $this->isVariableAParameter( $phpcsFile, $tokens, $stackPtr, $variableName ) ) {
			return;
		}

		// Find the semicolon ending this assignment.
		$assignmentEnd = $phpcsFile->findNext( T_SEMICOLON, $firstStatement + 1, $functionCloser );

		if ( false === $assignmentEnd ) {
			return;
		}

		// Find the next statement after the assignment.
		$nextStatement = $phpcsFile->findNext( T_WHITESPACE, $assignmentEnd + 1, $functionCloser, true );

		if ( false === $nextStatement ) {
			return;
		}

		// Check if the next statement is an if with an early return.
		if ( $tokens[ $nextStatement ]['code'] !== T_IF ) {
			return;
		}

		// Make sure the if has a scope.
		if ( ! isset( $tokens[ $nextStatement ]['scope_opener'] ) || ! isset( $tokens[ $nextStatement ]['scope_closer'] ) ) {
			return;
		}

		$ifOpener = $tokens[ $nextStatement ]['scope_opener'];
		$ifCloser = $tokens[ $nextStatement ]['scope_closer'];

		// Check if the if body contains only a return statement.
		$ifFirstStatement = $phpcsFile->findNext( T_WHITESPACE, $ifOpener + 1, $ifCloser, true );

		if ( false === $ifFirstStatement || $tokens[ $ifFirstStatement ]['code'] !== T_RETURN ) {
			return;
		}

		// Check if there's anything else in the if body after the return.
		$returnEnd = $phpcsFile->findNext( T_SEMICOLON, $ifFirstStatement + 1, $ifCloser );

		if ( false === $returnEnd ) {
			return;
		}

		$afterReturn = $phpcsFile->findNext( T_WHITESPACE, $returnEnd + 1, $ifCloser, true );

		if ( false !== $afterReturn ) {
			// There's more than just a return in the if body.
			return;
		}

		// Check if the if has an else/elseif - skip those.
		$afterIfClose = $phpcsFile->findNext( T_WHITESPACE, $ifCloser + 1, $functionCloser, true );

		if ( false !== $afterIfClose && in_array( $tokens[ $afterIfClose ]['code'], array( T_ELSE, T_ELSEIF ), true ) ) {
			return;
		}

		// Now check if the variable is used in the if condition or the return statement.
		$variableUsedInCondition = $this->isVariableUsedInRange(
			$phpcsFile,
			$tokens,
			$nextStatement,
			$ifOpener,
			$variableName
		);

		$variableUsedInReturn = $this->isVariableUsedInRange(
			$phpcsFile,
			$tokens,
			$ifFirstStatement,
			$returnEnd,
			$variableName
		);

		if ( $variableUsedInCondition || $variableUsedInReturn ) {
			// The variable is used in the early return check, so it's fine where it is.
			return;
		}

		// The variable is not used in the early return - it should be moved below.
		$fix = $phpcsFile->addFixableError(
			'Variable %s should be declared after the early return condition, not before.',
			$firstStatement,
			'VariableBeforeEarlyReturn',
			array( $variableName )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $tokens, $firstStatement, $assignmentEnd, $ifCloser );
		}
	}

	/**
	 * Apply the fix to move the variable declaration below the early return.
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param array $tokens         The token stack.
	 * @param int   $varStart       Start of the variable declaration.
	 * @param int   $assignmentEnd  End of the assignment (semicolon).
	 * @param int   $ifCloser       The closing brace of the if statement.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, array $tokens, $varStart, $assignmentEnd, $ifCloser ) {
		$phpcsFile->fixer->beginChangeset();

		// Get the variable declaration content.
		$varDeclaration = '';

		for ( $i = $varStart; $i <= $assignmentEnd; $i++ ) {
			$varDeclaration .= $tokens[ $i ]['content'];
		}

		// Find the start of the line for the variable (to get indentation).
		$lineStart = $varStart;

		for ( $i = $varStart - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $tokens[ $varStart ]['line'] ) {
				break;
			}
			$lineStart = $i;
		}

		// Get the indentation based on the variable's column position.
		// Column is 1-indexed, so column 2 means 1 character of indentation.
		$column   = $tokens[ $varStart ]['column'];
		$tabCount = (int) floor( ( $column - 1 ) / 4 );
		$indent   = str_repeat( "\t", $tabCount );

		// Remove the variable declaration line (from line start to semicolon).
		for ( $i = $lineStart; $i <= $assignmentEnd; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Also remove the newline after the semicolon if it exists.
		if ( isset( $tokens[ $assignmentEnd + 1 ] ) && $tokens[ $assignmentEnd + 1 ]['code'] === T_WHITESPACE ) {
			$wsContent = $tokens[ $assignmentEnd + 1 ]['content'];

			// Remove one newline from the whitespace.
			if ( strpos( $wsContent, "\n" ) === 0 ) {
				$phpcsFile->fixer->replaceToken( $assignmentEnd + 1, substr( $wsContent, 1 ) );
			}
		}

		// Add the variable declaration after the if closing brace.
		// Find the whitespace after the if closer.
		$afterIfCloser = $ifCloser + 1;

		if ( isset( $tokens[ $afterIfCloser ] ) && $tokens[ $afterIfCloser ]['code'] === T_WHITESPACE ) {
			$existingWs = $tokens[ $afterIfCloser ]['content'];
			// Add the variable declaration with proper formatting.
			// Keep the existing whitespace structure but insert the variable declaration.
			$newContent = "\n\n" . $indent . $varDeclaration . $existingWs;
			$phpcsFile->fixer->replaceToken( $afterIfCloser, $newContent );
		} else {
			// No whitespace after if closer, add it.
			$phpcsFile->fixer->addContent( $ifCloser, "\n\n" . $indent . $varDeclaration );
		}

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Check if a variable is used within a range of tokens.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $tokens       The token stack.
	 * @param int    $start        Start position.
	 * @param int    $end          End position.
	 * @param string $variableName The variable name to look for.
	 *
	 * @return bool
	 */
	private function isVariableUsedInRange( File $phpcsFile, array $tokens, $start, $end, $variableName ) {
		for ( $i = $start; $i <= $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === $variableName ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a variable is a function parameter.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $tokens       The token stack.
	 * @param int    $functionPtr  The function token position.
	 * @param string $variableName The variable name to check.
	 *
	 * @return bool
	 */
	private function isVariableAParameter( File $phpcsFile, array $tokens, $functionPtr, $variableName ) {
		// Find the opening parenthesis of the function.
		$openParen = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $functionPtr + 1 );

		if ( false === $openParen || ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Look for the variable in the parameter list.
		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === $variableName ) {
				return true;
			}
		}

		return false;
	}
}
