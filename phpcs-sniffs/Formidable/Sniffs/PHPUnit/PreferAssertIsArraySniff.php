<?php
/**
 * Formidable_Sniffs_PHPUnit_PreferAssertIsArraySniff
 *
 * Detects $this->assertTrue(is_array(...)) and converts to $this->assertIsArray(...).
 * Also handles is_object, is_string, and assertFalse variants.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\PHPUnit;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Converts assertTrue(is_array/is_object/is_string()) to specific assert methods.
 *
 * Bad:
 * $this->assertTrue( is_array( $value ) );
 * $this->assertTrue( is_object( $value ) );
 * $this->assertTrue( is_string( $value ) );
 *
 * Good:
 * $this->assertIsArray( $value );
 * $this->assertIsObject( $value );
 * $this->assertIsString( $value );
 */
class PreferAssertIsArraySniff implements Sniff {

	/**
	 * Mapping of is_* functions to their assert method names.
	 *
	 * @var array
	 */
	const FUNCTION_MAP = array(
		'is_array'   => array( 'assertIsArray', 'assertIsNotArray' ),
		'is_object'  => array( 'assertIsObject', 'assertIsNotObject' ),
		'is_string'  => array( 'assertIsString', 'assertIsNotString' ),
		'is_numeric' => array( 'assertIsNumeric', 'assertIsNotNumeric' ),
	);

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

		// Check if first argument is a supported is_* function call.
		if ( $tokens[ $firstArg ]['code'] !== T_STRING ) {
			return;
		}

		$functionName = strtolower( $tokens[ $firstArg ]['content'] );

		if ( ! isset( self::FUNCTION_MAP[ $functionName ] ) ) {
			return;
		}

		// Find the opening parenthesis of the is_* function.
		$isFuncOpenParen = $phpcsFile->findNext( T_WHITESPACE, $firstArg + 1, null, true );

		if ( false === $isFuncOpenParen || $tokens[ $isFuncOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the matching closing parenthesis of the is_* function.
		if ( ! isset( $tokens[ $isFuncOpenParen ]['parenthesis_closer'] ) ) {
			return;
		}
		$isFuncCloseParen = $tokens[ $isFuncOpenParen ]['parenthesis_closer'];

		// Find the closing parenthesis of assertTrue/assertFalse.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}
		$assertCloseParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Determine the new method name.
		$assertMethods = self::FUNCTION_MAP[ $functionName ];
		$newMethodName = 'asserttrue' === $methodName ? $assertMethods[0] : $assertMethods[1];

		$fix = $phpcsFile->addFixableError(
			'Use %s() instead of %s(%s()).',
			$stackPtr,
			'Found',
			array( $newMethodName, $token['content'], $functionName )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $openParen, $firstArg, $isFuncOpenParen, $isFuncCloseParen, $assertCloseParen, $newMethodName );
		}
	}

	/**
	 * Apply the fix to convert assertTrue(is_*($x)) to assert*($x).
	 *
	 * @param File   $phpcsFile        The file being scanned.
	 * @param int    $methodNamePtr    The assertTrue/assertFalse token position.
	 * @param int    $openParen        The opening parenthesis of assertTrue.
	 * @param int    $isFuncPtr        The is_* function token position.
	 * @param int    $isFuncOpenParen  The opening parenthesis of is_*.
	 * @param int    $isFuncCloseParen The closing parenthesis of is_*.
	 * @param int    $assertCloseParen The closing parenthesis of assertTrue.
	 * @param string $newMethodName    The new method name.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $methodNamePtr, $openParen, $isFuncPtr, $isFuncOpenParen, $isFuncCloseParen, $assertCloseParen, $newMethodName ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$fixer->beginChangeset();

		// Replace the method name.
		$fixer->replaceToken( $methodNamePtr, $newMethodName );

		// Remove everything from after ( to before the inner content of is_*.
		// We want to keep: assertIs*( <inner content> )
		// Remove: is_*(
		for ( $i = $openParen + 1; $i <= $isFuncOpenParen; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Remove the closing parenthesis of is_* and any whitespace after it.
		$fixer->replaceToken( $isFuncCloseParen, '' );

		// Remove whitespace between is_*'s closing paren and assertTrue's closing paren.
		for ( $i = $isFuncCloseParen + 1; $i < $assertCloseParen; $i++ ) {
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
