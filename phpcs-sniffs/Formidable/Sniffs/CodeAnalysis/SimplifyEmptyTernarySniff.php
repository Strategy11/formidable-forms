<?php
/**
 * Sniff to simplify empty() ternaries with function parameters.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects empty($param) ? default : $param and converts to $param ? $param : default.
 *
 * Bad:
 * $prefix = empty( $name ) ? 'item_meta' : $name;
 *
 * Good:
 * $prefix = $name ? $name : 'item_meta';
 *
 * This works because function parameters are always set, so empty() is redundant.
 */
class SimplifyEmptyTernarySniff implements Sniff {

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

		// Find the opening parenthesis after empty.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the closing parenthesis.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Get the variable inside empty().
		$varToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $varToken || $tokens[ $varToken ]['code'] !== T_VARIABLE ) {
			return;
		}

		$variableName = $tokens[ $varToken ]['content'];

		// Only apply this transformation if we can verify the variable is definitely set.
		// This is safe for function parameters, but not for arbitrary variables.
		if ( ! $this->isVariableDefinitelySet( $phpcsFile, $stackPtr, $variableName ) ) {
			return;
		}

		// Check if there's only the variable inside empty() (no array access, etc.).
		$nextInParen = $phpcsFile->findNext( T_WHITESPACE, $varToken + 1, $closeParen, true );

		if ( false !== $nextInParen ) {
			// There's something else inside empty(), skip.
			return;
		}

		// Find the ternary operator after empty().
		$ternaryOp = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false === $ternaryOp || $tokens[ $ternaryOp ]['code'] !== T_INLINE_THEN ) {
			return;
		}

		// Find the colon.
		$colonOp = $phpcsFile->findNext( T_INLINE_ELSE, $ternaryOp + 1 );

		if ( false === $colonOp ) {
			return;
		}

		// Get the "then" part (between ? and :).
		$thenStart = $phpcsFile->findNext( T_WHITESPACE, $ternaryOp + 1, $colonOp, true );

		if ( false === $thenStart ) {
			return;
		}

		// Find the end of the ternary (semicolon or other terminator).
		$ternaryEnd = $phpcsFile->findNext( array( T_SEMICOLON, T_COMMA, T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET ), $colonOp + 1 );

		if ( false === $ternaryEnd ) {
			return;
		}

		// Get the "else" part (between : and end).
		$elseStart = $phpcsFile->findNext( T_WHITESPACE, $colonOp + 1, $ternaryEnd, true );

		if ( false === $elseStart ) {
			return;
		}

		// Check if the else part is just the same variable.
		if ( $tokens[ $elseStart ]['code'] !== T_VARIABLE || $tokens[ $elseStart ]['content'] !== $variableName ) {
			return;
		}

		// Check there's nothing else in the else part.
		$nextInElse = $phpcsFile->findNext( T_WHITESPACE, $elseStart + 1, $ternaryEnd, true );

		if ( false !== $nextInElse ) {
			// There's something else in the else part, skip.
			return;
		}

		// Get the default value (the "then" part).
		$defaultValue = $phpcsFile->getTokensAsString( $thenStart, $colonOp - $thenStart );
		$defaultValue = trim( $defaultValue );

		$fix = $phpcsFile->addFixableError(
			'Simplify empty( %s ) ? %s : %s to %s ? %s : %s.',
			$stackPtr,
			'SimplifyEmptyTernary',
			array( $variableName, $defaultValue, $variableName, $variableName, $variableName, $defaultValue )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove everything from empty to the end of the ternary.
			for ( $i = $stackPtr; $i < $ternaryEnd; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Add the new simplified ternary.
			$phpcsFile->fixer->addContentBefore( $ternaryEnd, $variableName . ' ? ' . $variableName . ' : ' . $defaultValue );

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Check if a variable is definitely set (e.g., it's a function parameter).
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param int    $stackPtr     The current token position.
	 * @param string $variableName The variable name to check.
	 *
	 * @return bool True if the variable is definitely set.
	 */
	private function isVariableDefinitelySet( File $phpcsFile, $stackPtr, $variableName ) {
		$tokens = $phpcsFile->getTokens();

		// Find the containing function.
		$functionToken = $this->findContainingFunction( $phpcsFile, $stackPtr );

		if ( false === $functionToken ) {
			// Not inside a function - can't verify variable is set.
			return false;
		}

		// Check if the variable is a function parameter.
		if ( $this->isFunctionParameter( $phpcsFile, $functionToken, $variableName ) ) {
			return true;
		}

		// Check if the variable is unconditionally assigned before this point.
		if ( $this->isUnconditionallyAssigned( $phpcsFile, $functionToken, $stackPtr, $variableName ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Find the containing function for a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The token position.
	 *
	 * @return false|int The function token position or false.
	 */
	private function findContainingFunction( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $stackPtr ]['conditions'] ) ) {
			return false;
		}

		$conditions = $tokens[ $stackPtr ]['conditions'];

		foreach ( $conditions as $conditionPtr => $conditionType ) {
			if ( $conditionType === T_FUNCTION || $conditionType === T_CLOSURE ) {
				return $conditionPtr;
			}
		}

		return false;
	}

	/**
	 * Check if a variable is a parameter of the given function.
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param int    $functionToken The function token position.
	 * @param string $variableName  The variable name to check.
	 *
	 * @return bool True if the variable is a function parameter.
	 */
	private function isFunctionParameter( File $phpcsFile, $functionToken, $variableName ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $functionToken ]['parenthesis_opener'] ) || ! isset( $tokens[ $functionToken ]['parenthesis_closer'] ) ) {
			return false;
		}

		$opener = $tokens[ $functionToken ]['parenthesis_opener'];
		$closer = $tokens[ $functionToken ]['parenthesis_closer'];

		// Find all variables in the parameter list.
		for ( $i = $opener + 1; $i < $closer; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === $variableName ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a variable is unconditionally assigned before a given position.
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param int    $functionToken The function token position.
	 * @param int    $stackPtr      The current token position.
	 * @param string $variableName  The variable name to check.
	 *
	 * @return bool True if the variable is unconditionally assigned.
	 */
	private function isUnconditionallyAssigned( File $phpcsFile, $functionToken, $stackPtr, $variableName ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $functionToken ]['scope_opener'] ) ) {
			return false;
		}

		$scopeOpener = $tokens[ $functionToken ]['scope_opener'];

		// Look for assignments of this variable between function start and current position.
		for ( $i = $scopeOpener + 1; $i < $stackPtr; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE || $tokens[ $i ]['content'] !== $variableName ) {
				continue;
			}

			// Check if this is an assignment (variable followed by =).
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_EQUAL ) {
				continue;
			}

			// Check if this assignment is at the function's top level (not inside a condition).
			if ( $this->isAtFunctionTopLevel( $phpcsFile, $i, $functionToken ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a token is at the function's top level (not inside a condition).
	 *
	 * @param File $phpcsFile     The file being scanned.
	 * @param int  $stackPtr      The token position.
	 * @param int  $functionToken The function token position.
	 *
	 * @return bool True if the token is at the function's top level.
	 */
	private function isAtFunctionTopLevel( File $phpcsFile, $stackPtr, $functionToken ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $stackPtr ]['conditions'] ) ) {
			return false;
		}

		$conditions = $tokens[ $stackPtr ]['conditions'];

		// The only condition should be the function itself.
		// If there are other conditions (if, foreach, etc.), it's not at top level.
		foreach ( $conditions as $conditionPtr => $conditionType ) {
			if ( $conditionPtr === $functionToken ) {
				continue;
			}

			// Any other condition means it's not at top level.
			if ( in_array( $conditionType, array( T_IF, T_ELSEIF, T_ELSE, T_FOREACH, T_FOR, T_WHILE, T_DO, T_SWITCH, T_TRY, T_CATCH ), true ) ) {
				return false;
			}
		}

		return true;
	}
}
