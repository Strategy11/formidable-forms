<?php
/**
 * Sniff to detect if-return patterns that can be simplified to ternary returns.
 *
 * Detects patterns like:
 * if ( $condition ) {
 *     return $value1;
 * }
 * return $value2;
 *
 * These can be simplified to: return $condition ? $value1 : $value2;
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects if-return patterns that can be simplified to ternary returns.
 */
class SimplifyIfReturnSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_IF );
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Make sure this if has a scope (curly braces).
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Check if the if block contains only a single return statement.
		$firstStatement = $phpcsFile->findNext( T_WHITESPACE, $scopeOpener + 1, $scopeCloser, true );

		if ( false === $firstStatement || $tokens[ $firstStatement ]['code'] !== T_RETURN ) {
			return;
		}

		// Find the semicolon ending the return inside the if.
		$returnEnd = $phpcsFile->findNext( T_SEMICOLON, $firstStatement + 1, $scopeCloser );

		if ( false === $returnEnd ) {
			return;
		}

		// Check there's nothing else in the if block after the return.
		$afterReturn = $phpcsFile->findNext( T_WHITESPACE, $returnEnd + 1, $scopeCloser, true );

		if ( false !== $afterReturn ) {
			return;
		}

		// Now check what comes after the if block.
		$afterIf = $phpcsFile->findNext( T_WHITESPACE, $scopeCloser + 1, null, true );

		if ( false === $afterIf ) {
			return;
		}

		// Check for else/elseif - skip these.
		if ( in_array( $tokens[ $afterIf ]['code'], array( T_ELSE, T_ELSEIF ), true ) ) {
			return;
		}

		// Must be followed by a return statement.
		if ( $tokens[ $afterIf ]['code'] !== T_RETURN ) {
			return;
		}

		// Find the semicolon ending this return.
		$secondReturnEnd = $phpcsFile->findNext( T_SEMICOLON, $afterIf + 1 );

		if ( false === $secondReturnEnd ) {
			return;
		}

		// Check if the return values are simple.
		if ( ! $this->isSimpleReturn( $phpcsFile, $firstStatement, $returnEnd ) ) {
			return;
		}

		if ( ! $this->isSimpleReturn( $phpcsFile, $afterIf, $secondReturnEnd ) ) {
			return;
		}

		// Check if the condition is simple (no && or ||).
		if ( ! $this->isSimpleCondition( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Get the condition and return values to check line length.
		$conditionStart = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $stackPtr + 1 );
		$conditionEnd   = $tokens[ $conditionStart ]['parenthesis_closer'];
		$condition      = trim( $phpcsFile->getTokensAsString( $conditionStart + 1, $conditionEnd - $conditionStart - 1 ) );

		$firstReturnValue  = trim( $phpcsFile->getTokensAsString( $firstStatement + 1, $returnEnd - $firstStatement - 1 ) );
		$secondReturnValue = trim( $phpcsFile->getTokensAsString( $afterIf + 1, $secondReturnEnd - $afterIf - 1 ) );

		// Check if condition starts with ! and flip if so.
		$isNegated = false;
		$firstNonWs = $phpcsFile->findNext( T_WHITESPACE, $conditionStart + 1, $conditionEnd, true );
		if ( false !== $firstNonWs && $tokens[ $firstNonWs ]['code'] === T_BOOLEAN_NOT ) {
			$isNegated = true;
			// Get condition without the leading !
			$condition = trim( $phpcsFile->getTokensAsString( $firstNonWs + 1, $conditionEnd - $firstNonWs - 1 ) );
			// Swap the return values.
			$temp              = $firstReturnValue;
			$firstReturnValue  = $secondReturnValue;
			$secondReturnValue = $temp;
		}

		// Build the ternary to check length.
		$ternary = 'return ' . $condition . ' ? ' . $firstReturnValue . ' : ' . $secondReturnValue . ';';

		// Skip if the ternary would be over 75 characters.
		if ( strlen( $ternary ) > 75 ) {
			return;
		}

		// We found a pattern that can be simplified.
		$fix = $phpcsFile->addFixableError(
			'This if-return pattern can be simplified to a ternary return statement.',
			$stackPtr,
			'SimplifiableIfReturn'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove everything from the if to the second return's semicolon.
			for ( $i = $stackPtr; $i <= $secondReturnEnd; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Insert the ternary at the if's position.
			$phpcsFile->fixer->addContent( $stackPtr, $ternary );

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Check if a return statement is simple (short expression).
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $returnStart The position of the return token.
	 * @param int  $returnEnd   The position of the semicolon.
	 *
	 * @return bool
	 */
	private function isSimpleReturn( File $phpcsFile, $returnStart, $returnEnd ) {
		$tokens = $phpcsFile->getTokens();

		// Count non-whitespace tokens in the return value.
		$tokenCount = 0;
		$hasComplex = false;

		for ( $i = $returnStart + 1; $i < $returnEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			++$tokenCount;

			// Check for complex constructs that shouldn't be in a ternary.
			$complexTokens = array(
				T_CLOSURE,
				T_FUNCTION,
				T_CLASS,
				T_ARRAY,       // array() syntax - could be long.
				T_OPEN_SHORT_ARRAY, // [] syntax - could be long.
			);

			if ( in_array( $tokens[ $i ]['code'], $complexTokens, true ) ) {
				$hasComplex = true;
				break;
			}
		}

		// Allow returns with up to ~10 tokens (simple expressions).
		return ! $hasComplex && $tokenCount <= 10;
	}

	/**
	 * Check if the if condition is simple.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $ifPtr     The position of the if token.
	 *
	 * @return bool
	 */
	private function isSimpleCondition( File $phpcsFile, $ifPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the opening parenthesis of the if condition.
		$openParen = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $ifPtr + 1 );

		if ( false === $openParen || ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Count non-whitespace tokens in the condition.
		$tokenCount = 0;
		$hasComplex = false;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			++$tokenCount;

			// Check for complex constructs.
			$complexTokens = array(
				T_CLOSURE,
				T_FUNCTION,
				T_CLASS,
				T_BOOLEAN_AND, // && - multiple conditions.
				T_BOOLEAN_OR,  // || - multiple conditions.
				T_LOGICAL_AND, // and.
				T_LOGICAL_OR,  // or.
			);

			if ( in_array( $tokens[ $i ]['code'], $complexTokens, true ) ) {
				$hasComplex = true;
				break;
			}
		}

		// Allow conditions with up to ~15 tokens (simple expressions).
		return ! $hasComplex && $tokenCount <= 15;
	}
}
