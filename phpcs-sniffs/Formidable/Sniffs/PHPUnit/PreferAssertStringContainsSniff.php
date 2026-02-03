<?php
/**
 * Formidable_Sniffs_PHPUnit_PreferAssertStringContainsSniff
 *
 * Detects $this->assertTrue(str_contains(...)) and converts to $this->assertStringContainsString(...).
 * Note: str_contains($haystack, $needle) becomes assertStringContainsString($needle, $haystack).
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\PHPUnit;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Converts assertTrue(str_contains()) and assertNotFalse(strpos()) to assertStringContainsString().
 *
 * Bad:
 * $this->assertTrue( str_contains( $haystack, $needle ) );
 * $this->assertNotFalse( strpos( $haystack, $needle ) );
 *
 * Good:
 * $this->assertStringContainsString( $needle, $haystack );
 */
class PreferAssertStringContainsSniff implements Sniff {

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

		// Check for assertTrue, assertFalse, assertNotFalse.
		$methodName = strtolower( $token['content'] );

		$validMethods = array( 'asserttrue', 'assertfalse', 'assertnotfalse' );

		if ( ! in_array( $methodName, $validMethods, true ) ) {
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

		// Check for negation (! str_contains).
		$isNegated = false;

		if ( $tokens[ $firstArg ]['code'] === T_BOOLEAN_NOT ) {
			$isNegated = true;
			$firstArg  = $phpcsFile->findNext( T_WHITESPACE, $firstArg + 1, null, true );

			if ( false === $firstArg ) {
				return;
			}
		}

		// Check if first argument is str_contains or strpos function call.
		if ( $tokens[ $firstArg ]['code'] !== T_STRING ) {
			return;
		}

		$functionName = strtolower( $tokens[ $firstArg ]['content'] );

		if ( 'str_contains' !== $functionName && 'strpos' !== $functionName ) {
			return;
		}

		// Find the opening parenthesis of str_contains.
		$funcOpenParen = $phpcsFile->findNext( T_WHITESPACE, $firstArg + 1, null, true );

		if ( false === $funcOpenParen || $tokens[ $funcOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the matching closing parenthesis of str_contains.
		if ( ! isset( $tokens[ $funcOpenParen ]['parenthesis_closer'] ) ) {
			return;
		}
		$funcCloseParen = $tokens[ $funcOpenParen ]['parenthesis_closer'];

		// Find the closing parenthesis of assertTrue/assertFalse.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}
		$assertCloseParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Check what comes after str_contains - allow comma for message argument, but not && or ||.
		$nextAfterFunc = $phpcsFile->findNext( T_WHITESPACE, $funcCloseParen + 1, $assertCloseParen, true );
		$messageArg    = '';

		if ( false !== $nextAfterFunc ) {
			// If it's a comma, there might be a message argument - that's OK.
			if ( $tokens[ $nextAfterFunc ]['code'] === T_COMMA ) {
				// Capture the message argument.
				$messageStart = $phpcsFile->findNext( T_WHITESPACE, $nextAfterFunc + 1, $assertCloseParen, true );

				if ( false !== $messageStart ) {
					for ( $i = $messageStart; $i < $assertCloseParen; $i++ ) {
						$messageArg .= $tokens[ $i ]['content'];
					}
					$messageArg = trim( $messageArg );
				}
			} elseif ( in_array( $tokens[ $nextAfterFunc ]['code'], array( T_BOOLEAN_AND, T_BOOLEAN_OR ), true ) ) {
				// There's a boolean operator - skip this complex expression.
				return;
			}
		}

		// Parse the two arguments of str_contains.
		$args = $this->parseArguments( $phpcsFile, $funcOpenParen, $funcCloseParen );

		if ( count( $args ) !== 2 ) {
			return;
		}

		// Determine the new method name.
		// assertTrue(str_contains()) -> assertStringContainsString
		// assertTrue(! str_contains()) -> assertStringNotContainsString
		// assertFalse(str_contains()) -> assertStringNotContainsString
		// assertNotFalse(strpos()) -> assertStringContainsString
		// assertFalse(strpos()) -> assertStringNotContainsString
		$wantsContains = ( 'asserttrue' === $methodName || 'assertnotfalse' === $methodName );

		// Negation flips the result.
		if ( $isNegated ) {
			$wantsContains = ! $wantsContains;
		}

		$newMethodName = $wantsContains ? 'assertStringContainsString' : 'assertStringNotContainsString';

		$fix = $phpcsFile->addFixableError(
			'Use %s() instead of %s(%s()).',
			$stackPtr,
			'Found',
			array( $newMethodName, $token['content'], $functionName )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $openParen, $assertCloseParen, $newMethodName, $args, $messageArg );
		}
	}

	/**
	 * Parse the arguments of a function call.
	 *
	 * @param File $phpcsFile      The file being scanned.
	 * @param int  $funcOpenParen  The opening parenthesis position.
	 * @param int  $funcCloseParen The closing parenthesis position.
	 *
	 * @return array Array of argument strings.
	 */
	private function parseArguments( File $phpcsFile, $funcOpenParen, $funcCloseParen ) {
		$tokens = $phpcsFile->getTokens();
		$args   = array();
		$start  = $funcOpenParen + 1;
		$depth  = 0;

		$currentArg = '';

		for ( $i = $start; $i < $funcCloseParen; $i++ ) {
			$tokenCode = $tokens[ $i ]['code'];

			// Track nested parentheses.
			if ( $tokenCode === T_OPEN_PARENTHESIS || $tokenCode === T_OPEN_SQUARE_BRACKET ) {
				$depth++;
				$currentArg .= $tokens[ $i ]['content'];
			} elseif ( $tokenCode === T_CLOSE_PARENTHESIS || $tokenCode === T_CLOSE_SQUARE_BRACKET ) {
				$depth--;
				$currentArg .= $tokens[ $i ]['content'];
			} elseif ( $tokenCode === T_COMMA && 0 === $depth ) {
				// End of argument.
				$args[]     = trim( $currentArg );
				$currentArg = '';
			} else {
				$currentArg .= $tokens[ $i ]['content'];
			}
		}

		// Add the last argument.
		$trimmed = trim( $currentArg );

		if ( '' !== $trimmed ) {
			$args[] = $trimmed;
		}

		return $args;
	}

	/**
	 * Apply the fix to convert assertTrue(str_contains($haystack, $needle)) to assertStringContainsString($needle, $haystack).
	 *
	 * @param File   $phpcsFile        The file being scanned.
	 * @param int    $methodNamePtr    The assertTrue/assertFalse token position.
	 * @param int    $openParen        The opening parenthesis of assertTrue.
	 * @param int    $assertCloseParen The closing parenthesis of assertTrue.
	 * @param string $newMethodName    The new method name.
	 * @param array  $args             The parsed arguments (haystack, needle).
	 * @param string $messageArg       Optional message argument.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $methodNamePtr, $openParen, $assertCloseParen, $newMethodName, $args, $messageArg = '' ) {
		$fixer = $phpcsFile->fixer;

		$fixer->beginChangeset();

		// Replace the method name.
		$fixer->replaceToken( $methodNamePtr, $newMethodName );

		// Replace everything between the parentheses with swapped arguments.
		// str_contains($haystack, $needle) -> assertStringContainsString($needle, $haystack)
		$haystack = $args[0];
		$needle   = $args[1];

		// Remove all tokens between open and close paren.
		for ( $i = $openParen + 1; $i < $assertCloseParen; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Build the new content.
		$newContent = ' ' . $needle . ', ' . $haystack;

		if ( '' !== $messageArg ) {
			$newContent .= ', ' . $messageArg;
		}

		$newContent .= ' ';

		// Insert the new content after the opening paren.
		$fixer->addContent( $openParen, $newContent );

		$fixer->endChangeset();
	}
}
