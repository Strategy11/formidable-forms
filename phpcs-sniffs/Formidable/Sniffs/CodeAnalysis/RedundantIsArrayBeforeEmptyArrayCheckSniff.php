<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantIsArrayBeforeEmptyArrayCheckSniff
 *
 * Detects is_array($var) && array() === $var and removes the redundant is_array check.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant is_array() before empty array comparison.
 *
 * Bad:
 * if ( is_array( $var ) && array() === $var ) {}
 * if ( is_array( $var ) && $var === array() ) {}
 *
 * Good:
 * if ( array() === $var ) {}
 * if ( $var === array() ) {}
 */
class RedundantIsArrayBeforeEmptyArrayCheckSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING );
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

		// Check if this is an is_array() function call.
		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'is_array' ) {
			return;
		}

		// Find the opening parenthesis.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the closing parenthesis.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Get the variable inside is_array().
		$varContent = $this->getVariableContent( $phpcsFile, $openParen + 1, $closeParen );

		if ( empty( $varContent ) ) {
			return;
		}

		// Check for && after is_array().
		$afterClose = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false === $afterClose || $tokens[ $afterClose ]['code'] !== T_BOOLEAN_AND ) {
			return;
		}

		// Check if the next part is an empty array comparison with the same variable.
		$afterAnd = $phpcsFile->findNext( T_WHITESPACE, $afterClose + 1, null, true );

		if ( false === $afterAnd ) {
			return;
		}

		// Check for array() === $var pattern.
		if ( $tokens[ $afterAnd ]['code'] === T_ARRAY ) {
			$result = $this->checkArrayEqualsVar( $phpcsFile, $afterAnd, $varContent );

			if ( $result ) {
				$this->reportAndFix( $phpcsFile, $stackPtr, $closeParen, $afterClose, $varContent );
			}

			return;
		}

		// Check for $var === array() pattern.
		$result = $this->checkVarEqualsArray( $phpcsFile, $afterAnd, $varContent );

		if ( $result ) {
			$this->reportAndFix( $phpcsFile, $stackPtr, $closeParen, $afterClose, $varContent );
		}
	}

	/**
	 * Get the variable content from inside a function call.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $start      The start position.
	 * @param int  $end        The end position.
	 *
	 * @return string The variable content.
	 */
	private function getVariableContent( File $phpcsFile, $start, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';

		for ( $i = $start; $i < $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				if ( empty( $content ) ) {
					continue;
				}
			}

			$content .= $tokens[ $i ]['content'];
		}

		return trim( $content );
	}

	/**
	 * Check for array() === $var pattern.
	 *
	 * @param File   $phpcsFile  The file being scanned.
	 * @param int    $arrayToken The array token position.
	 * @param string $varContent The variable to match.
	 *
	 * @return bool
	 */
	private function checkArrayEqualsVar( File $phpcsFile, $arrayToken, $varContent ) {
		$tokens = $phpcsFile->getTokens();

		// Find the opening parenthesis of array().
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $arrayToken + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		// Find the closing parenthesis.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Check that array() is empty.
		$insideArray = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false !== $insideArray ) {
			return false;
		}

		// Check for === or == after array().
		$afterArray = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );

		if ( false === $afterArray || ! in_array( $tokens[ $afterArray ]['code'], array( T_IS_IDENTICAL, T_IS_EQUAL ), true ) ) {
			return false;
		}

		// Check if the variable after === matches.
		$afterComparison = $phpcsFile->findNext( T_WHITESPACE, $afterArray + 1, null, true );

		if ( false === $afterComparison ) {
			return false;
		}

		// Find the end of the comparison (look for ) or other terminator).
		$endOfVar = $this->findEndOfVariable( $phpcsFile, $afterComparison );
		$foundVar = $this->getVariableContent( $phpcsFile, $afterComparison, $endOfVar + 1 );

		return $foundVar === $varContent;
	}

	/**
	 * Check for $var === array() pattern.
	 *
	 * @param File   $phpcsFile  The file being scanned.
	 * @param int    $varStart   The variable start position.
	 * @param string $varContent The variable to match.
	 *
	 * @return bool
	 */
	private function checkVarEqualsArray( File $phpcsFile, $varStart, $varContent ) {
		$tokens = $phpcsFile->getTokens();

		// Find the end of the variable.
		$endOfVar = $this->findEndOfVariable( $phpcsFile, $varStart );
		$foundVar = $this->getVariableContent( $phpcsFile, $varStart, $endOfVar + 1 );

		if ( $foundVar !== $varContent ) {
			return false;
		}

		// Check for === or == after the variable.
		$afterVar = $phpcsFile->findNext( T_WHITESPACE, $endOfVar + 1, null, true );

		if ( false === $afterVar || ! in_array( $tokens[ $afterVar ]['code'], array( T_IS_IDENTICAL, T_IS_EQUAL ), true ) ) {
			return false;
		}

		// Check for array() after ===.
		$afterComparison = $phpcsFile->findNext( T_WHITESPACE, $afterVar + 1, null, true );

		if ( false === $afterComparison || $tokens[ $afterComparison ]['code'] !== T_ARRAY ) {
			return false;
		}

		// Find the opening parenthesis of array().
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $afterComparison + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		// Find the closing parenthesis.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Check that array() is empty.
		$insideArray = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		return false === $insideArray;
	}

	/**
	 * Find the end of a variable expression.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     The start position.
	 *
	 * @return int The end position.
	 */
	private function findEndOfVariable( File $phpcsFile, $start ) {
		$tokens = $phpcsFile->getTokens();
		$end    = $start;

		$validTokens = array(
			T_VARIABLE,
			T_STRING,
			T_OBJECT_OPERATOR,
			T_NULLSAFE_OBJECT_OPERATOR,
			T_DOUBLE_COLON,
			T_OPEN_SQUARE_BRACKET,
			T_CLOSE_SQUARE_BRACKET,
			T_CONSTANT_ENCAPSED_STRING,
			T_LNUMBER,
		);

		while ( isset( $tokens[ $end + 1 ] ) ) {
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $end + 1, null, true );

			if ( false === $nextToken ) {
				break;
			}

			if ( ! in_array( $tokens[ $nextToken ]['code'], $validTokens, true ) ) {
				break;
			}

			$end = $nextToken;

			// Handle array access.
			if ( $tokens[ $end ]['code'] === T_OPEN_SQUARE_BRACKET ) {
				if ( isset( $tokens[ $end ]['bracket_closer'] ) ) {
					$end = $tokens[ $end ]['bracket_closer'];
				}
			}
		}

		return $end;
	}

	/**
	 * Report the error and fix it.
	 *
	 * @param File   $phpcsFile  The file being scanned.
	 * @param int    $isArrayStart The is_array start position.
	 * @param int    $isArrayEnd   The is_array closing paren position.
	 * @param int    $andToken     The && token position.
	 * @param string $varContent   The variable content.
	 *
	 * @return void
	 */
	private function reportAndFix( File $phpcsFile, $isArrayStart, $isArrayEnd, $andToken, $varContent ) {
		$fix = $phpcsFile->addFixableError(
			'Redundant is_array() check before empty array comparison. Use just the array comparison.',
			$isArrayStart,
			'RedundantIsArray'
		);

		if ( true === $fix ) {
			$fixer = $phpcsFile->fixer;
			$fixer->beginChangeset();

			// Remove is_array($var) &&.
			// Find the first whitespace after && to include it in removal.
			$afterAnd = $phpcsFile->findNext( T_WHITESPACE, $andToken + 1, null, true );

			for ( $i = $isArrayStart; $i < $afterAnd; $i++ ) {
				$fixer->replaceToken( $i, '' );
			}

			$fixer->endChangeset();
		}
	}
}
