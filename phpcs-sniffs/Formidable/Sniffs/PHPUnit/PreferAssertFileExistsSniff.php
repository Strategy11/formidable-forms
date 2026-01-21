<?php
/**
 * Formidable_Sniffs_PHPUnit_PreferAssertFileExistsSniff
 *
 * Detects $this->assertTrue(file_exists(...)) and converts to $this->assertFileExists(...).
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\PHPUnit;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Converts assertTrue(file_exists()) to assertFileExists().
 *
 * Bad:
 * $this->assertTrue( file_exists( $path ) );
 *
 * Good:
 * $this->assertFileExists( $path );
 */
class PreferAssertFileExistsSniff implements Sniff {

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
		$token  = $tokens[ $stackPtr ];

		// Check for assertTrue or assertFalse.
		$methodName = strtolower( $token['content'] );

		if ( 'asserttrue' !== $methodName && 'assertfalse' !== $methodName ) {
			return;
		}

		// Check that this is a method call ($this-> or self:: or static::).
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken ) {
			return;
		}

		if ( $tokens[ $prevToken ]['code'] !== T_OBJECT_OPERATOR && $tokens[ $prevToken ]['code'] !== T_DOUBLE_COLON ) {
			return;
		}

		// Find the opening parenthesis.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the first argument (skip whitespace).
		$firstArg = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, null, true );

		if ( false === $firstArg ) {
			return;
		}

		// Check if first argument is file_exists function call.
		if ( $tokens[ $firstArg ]['code'] !== T_STRING ) {
			return;
		}

		$functionName = strtolower( $tokens[ $firstArg ]['content'] );

		if ( 'file_exists' !== $functionName ) {
			return;
		}

		// Find the opening parenthesis of file_exists.
		$funcOpenParen = $phpcsFile->findNext( T_WHITESPACE, $firstArg + 1, null, true );

		if ( false === $funcOpenParen || $tokens[ $funcOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the matching closing parenthesis of file_exists.
		if ( ! isset( $tokens[ $funcOpenParen ]['parenthesis_closer'] ) ) {
			return;
		}
		$funcCloseParen = $tokens[ $funcOpenParen ]['parenthesis_closer'];

		// Find the closing parenthesis of assertTrue/assertFalse.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}
		$assertCloseParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Determine the new method name.
		$newMethodName = 'asserttrue' === $methodName ? 'assertFileExists' : 'assertFileDoesNotExist';

		$fix = $phpcsFile->addFixableError(
			'Use %s() instead of %s(file_exists()).',
			$stackPtr,
			'Found',
			array( $newMethodName, $token['content'] )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $openParen, $firstArg, $funcOpenParen, $funcCloseParen, $assertCloseParen, $newMethodName );
		}
	}

	/**
	 * Apply the fix to convert assertTrue(file_exists($path)) to assertFileExists($path).
	 *
	 * @param File   $phpcsFile        The file being scanned.
	 * @param int    $methodNamePtr    The assertTrue/assertFalse token position.
	 * @param int    $openParen        The opening parenthesis of assertTrue.
	 * @param int    $funcPtr          The file_exists token position.
	 * @param int    $funcOpenParen    The opening parenthesis of file_exists.
	 * @param int    $funcCloseParen   The closing parenthesis of file_exists.
	 * @param int    $assertCloseParen The closing parenthesis of assertTrue.
	 * @param string $newMethodName    The new method name.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $methodNamePtr, $openParen, $funcPtr, $funcOpenParen, $funcCloseParen, $assertCloseParen, $newMethodName ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$fixer->beginChangeset();

		// Replace the method name.
		$fixer->replaceToken( $methodNamePtr, $newMethodName );

		// Remove everything from after ( to before the inner content of file_exists.
		// We want to keep: assertFileExists( <inner content> )
		// Remove: file_exists(
		for ( $i = $openParen + 1; $i <= $funcOpenParen; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Remove the closing parenthesis of file_exists.
		$fixer->replaceToken( $funcCloseParen, '' );

		// Remove whitespace between file_exists's closing paren and assertTrue's closing paren.
		for ( $i = $funcCloseParen + 1; $i < $assertCloseParen; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				$fixer->replaceToken( $i, '' );
			} else {
				// Stop if we hit a non-whitespace token (like a comma for a second argument).
				break;
			}
		}

		$fixer->endChangeset();
	}
}
