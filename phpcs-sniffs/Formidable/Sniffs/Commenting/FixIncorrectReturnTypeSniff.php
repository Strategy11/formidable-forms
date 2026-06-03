<?php
/**
 * Formidable_Sniffs_Commenting_FixIncorrectReturnTypeSniff
 *
 * Detects functions with @return docblock that have no return statement
 * and changes them to @return void.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects incorrect @return types when function has no return statement.
 *
 * Bad:
 * /**
 *  * @return string
 *  * /
 * function example() {
 *     $value = 'test';
 * }
 *
 * Good:
 *  * @return void
 *  * /
 * function example() {
 *     $value = 'test';
 * }
 */
class FixIncorrectReturnTypeSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FUNCTION );
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

		// Skip if no scope (abstract method, interface method).
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		// Check if the function has a return statement with a value.
		$hasReturnWithValue = $this->hasReturnWithValue( $phpcsFile, $stackPtr );

		if ( $hasReturnWithValue ) {
			return;
		}

		// Find the docblock for this function.
		$docblock = $this->findDocblock( $phpcsFile, $stackPtr );

		if ( false === $docblock ) {
			return;
		}

		// Check if the docblock has a @return tag that is not void.
		$returnTag = $this->findReturnTag( $phpcsFile, $docblock );

		if ( false === $returnTag ) {
			return;
		}

		$returnType = $this->getReturnType( $phpcsFile, $returnTag );

		// Skip if already void.
		if ( 'void' === $returnType ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Function has no return statement but @return is "%s". Should be @return void.',
			$returnTag,
			'IncorrectReturnType',
			array( $returnType )
		);

		if ( true === $fix ) {
			$this->fixReturnType( $phpcsFile, $returnTag );
		}
	}

	/**
	 * Check if a function has a return statement with a value.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 *
	 * @return bool
	 */
	private function hasReturnWithValue( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find all return statements in the function.
		$current = $scopeOpener;

		while ( $current < $scopeCloser ) {
			$return = $phpcsFile->findNext( T_RETURN, $current + 1, $scopeCloser );

			if ( false === $return ) {
				break;
			}

			// Check if this return is inside a nested closure/function.
			if ( $this->isInsideNestedScope( $phpcsFile, $return, $stackPtr ) ) {
				$current = $return;
				continue;
			}

			// Check if the return has a value (not just "return;").
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $return + 1, null, true );

			if ( false !== $nextToken && $tokens[ $nextToken ]['code'] !== T_SEMICOLON ) {
				return true;
			}

			$current = $return;
		}

		return false;
	}

	/**
	 * Check if a token is inside a nested closure or function.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $tokenPtr     The token to check.
	 * @param int  $functionPtr  The outer function token.
	 *
	 * @return bool
	 */
	private function isInsideNestedScope( File $phpcsFile, $tokenPtr, $functionPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Check if the token is inside a closure or anonymous function.
		if ( isset( $tokens[ $tokenPtr ]['conditions'] ) ) {
			foreach ( $tokens[ $tokenPtr ]['conditions'] as $scopePtr => $scopeType ) {
				if ( $scopePtr !== $functionPtr && in_array( $scopeType, array( T_CLOSURE, T_FUNCTION ), true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Find the docblock for a function.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 *
	 * @return false|int The docblock opener position, or false.
	 */
	private function findDocblock( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Look backwards for a doc comment.
		$ignore = array(
			T_WHITESPACE,
			T_STATIC,
			T_PUBLIC,
			T_PRIVATE,
			T_PROTECTED,
			T_ABSTRACT,
			T_FINAL,
		);

		$prev = $phpcsFile->findPrevious( $ignore, $stackPtr - 1, null, true );

		if ( false !== $prev && $tokens[ $prev ]['code'] === T_DOC_COMMENT_CLOSE_TAG ) {
			return $tokens[ $prev ]['comment_opener'];
		}

		return false;
	}

	/**
	 * Find the @return tag in a docblock.
	 *
	 * @param File $phpcsFile     The file being scanned.
	 * @param int  $docblockStart The docblock opener position.
	 *
	 * @return false|int The @return tag position, or false.
	 */
	private function findReturnTag( File $phpcsFile, $docblockStart ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $docblockStart ]['comment_closer'] ) ) {
			return false;
		}

		$docblockEnd = $tokens[ $docblockStart ]['comment_closer'];

		for ( $i = $docblockStart; $i < $docblockEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG && $tokens[ $i ]['content'] === '@return' ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Get the return type from a @return tag.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $returnTag The @return tag position.
	 *
	 * @return string The return type.
	 */
	private function getReturnType( File $phpcsFile, $returnTag ) {
		$tokens = $phpcsFile->getTokens();

		// The type should be the next non-whitespace token.
		$typeToken = $phpcsFile->findNext( T_DOC_COMMENT_WHITESPACE, $returnTag + 1, null, true );

		if ( false !== $typeToken && $tokens[ $typeToken ]['code'] === T_DOC_COMMENT_STRING ) {
			$content = $tokens[ $typeToken ]['content'];
			// Get just the type (first word).
			$parts = preg_split( '/\s+/', $content, 2 );

			return $parts[0];
		}

		return '';
	}

	/**
	 * Fix the @return type to void.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $returnTag The @return tag position.
	 *
	 * @return void
	 */
	private function fixReturnType( File $phpcsFile, $returnTag ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		// Find the type token.
		$typeToken = $phpcsFile->findNext( T_DOC_COMMENT_WHITESPACE, $returnTag + 1, null, true );

		if ( false !== $typeToken && $tokens[ $typeToken ]['code'] === T_DOC_COMMENT_STRING ) {
			$content = $tokens[ $typeToken ]['content'];
			// Replace just the type, keeping any description.
			$parts = preg_split( '/\s+/', $content, 2 );

			if ( count( $parts ) > 1 ) {
				$fixer->replaceToken( $typeToken, 'void ' . $parts[1] );
			} else {
				$fixer->replaceToken( $typeToken, 'void' );
			}
		}
	}
}
