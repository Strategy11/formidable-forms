<?php
/**
 * Formidable_Sniffs_PHPUnit_PreferAssertContainsSniff
 *
 * Detects $this->assertTrue(in_array(...)) and converts to $this->assertContains(...).
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\PHPUnit;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Converts assertTrue(in_array()) to assertContains().
 *
 * Bad:
 * $this->assertTrue( in_array( $needle, $haystack ) );
 * $this->assertTrue( in_array( $needle, $haystack, true ) );
 *
 * Good:
 * $this->assertContains( $needle, $haystack );
 */
class PreferAssertContainsSniff implements Sniff {

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

		// Check if first argument is in_array function call.
		if ( $tokens[ $firstArg ]['code'] !== T_STRING ) {
			return;
		}

		$functionName = strtolower( $tokens[ $firstArg ]['content'] );

		if ( 'in_array' !== $functionName ) {
			return;
		}

		// Find the opening parenthesis of in_array.
		$funcOpenParen = $phpcsFile->findNext( T_WHITESPACE, $firstArg + 1, null, true );

		if ( false === $funcOpenParen || $tokens[ $funcOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Find the matching closing parenthesis of in_array.
		if ( ! isset( $tokens[ $funcOpenParen ]['parenthesis_closer'] ) ) {
			return;
		}
		$funcCloseParen = $tokens[ $funcOpenParen ]['parenthesis_closer'];

		// Find the closing parenthesis of assertTrue/assertFalse.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}
		$assertCloseParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Parse the arguments of in_array to get needle and haystack.
		$args = $this->parseInArrayArgs( $phpcsFile, $funcOpenParen, $funcCloseParen );

		if ( false === $args ) {
			return;
		}

		// Determine the new method name.
		$newMethodName = 'asserttrue' === $methodName ? 'assertContains' : 'assertNotContains';

		$fix = $phpcsFile->addFixableError(
			'Use %s() instead of %s(in_array()).',
			$stackPtr,
			'Found',
			array( $newMethodName, $token['content'] )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $openParen, $firstArg, $funcOpenParen, $funcCloseParen, $assertCloseParen, $newMethodName, $args );
		}
	}

	/**
	 * Parse the arguments of in_array() to extract needle and haystack.
	 *
	 * @param File $phpcsFile      The file being scanned.
	 * @param int  $funcOpenParen  The opening parenthesis of in_array.
	 * @param int  $funcCloseParen The closing parenthesis of in_array.
	 *
	 * @return array|false Array with 'needle_start', 'needle_end', 'haystack_start', 'haystack_end', or false.
	 */
	private function parseInArrayArgs( File $phpcsFile, $funcOpenParen, $funcCloseParen ) {
		$tokens = $phpcsFile->getTokens();

		// Find the first comma (separates needle from haystack).
		$commaPos    = null;
		$parenDepth  = 0;
		$bracketDepth = 0;

		for ( $i = $funcOpenParen + 1; $i < $funcCloseParen; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
			} elseif ( $code === T_CLOSE_PARENTHESIS ) {
				--$parenDepth;
			} elseif ( $code === T_OPEN_SQUARE_BRACKET || $code === T_OPEN_SHORT_ARRAY ) {
				++$bracketDepth;
			} elseif ( $code === T_CLOSE_SQUARE_BRACKET || $code === T_CLOSE_SHORT_ARRAY ) {
				--$bracketDepth;
			} elseif ( $code === T_COMMA && 0 === $parenDepth && 0 === $bracketDepth ) {
				if ( null === $commaPos ) {
					$commaPos = $i;
				}
			}
		}

		if ( null === $commaPos ) {
			return false;
		}

		// Find needle bounds (first arg).
		$needleStart = $phpcsFile->findNext( T_WHITESPACE, $funcOpenParen + 1, $commaPos, true );

		if ( false === $needleStart ) {
			return false;
		}

		$needleEnd = $phpcsFile->findPrevious( T_WHITESPACE, $commaPos - 1, $needleStart, true );

		if ( false === $needleEnd ) {
			$needleEnd = $needleStart;
		}

		// Find haystack bounds (second arg - everything after first comma until close paren or second comma).
		$haystackStart = $phpcsFile->findNext( T_WHITESPACE, $commaPos + 1, $funcCloseParen, true );

		if ( false === $haystackStart ) {
			return false;
		}

		// Find the end of haystack (before third arg if exists, or before close paren).
		$secondComma = null;
		$parenDepth  = 0;
		$bracketDepth = 0;

		for ( $i = $haystackStart; $i < $funcCloseParen; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
			} elseif ( $code === T_CLOSE_PARENTHESIS ) {
				--$parenDepth;
			} elseif ( $code === T_OPEN_SQUARE_BRACKET || $code === T_OPEN_SHORT_ARRAY ) {
				++$bracketDepth;
			} elseif ( $code === T_CLOSE_SQUARE_BRACKET || $code === T_CLOSE_SHORT_ARRAY ) {
				--$bracketDepth;
			} elseif ( $code === T_COMMA && 0 === $parenDepth && 0 === $bracketDepth ) {
				$secondComma = $i;
				break;
			}
		}

		if ( null !== $secondComma ) {
			$haystackEnd = $phpcsFile->findPrevious( T_WHITESPACE, $secondComma - 1, $haystackStart, true );
		} else {
			$haystackEnd = $phpcsFile->findPrevious( T_WHITESPACE, $funcCloseParen - 1, $haystackStart, true );
		}

		if ( false === $haystackEnd ) {
			$haystackEnd = $haystackStart;
		}

		return array(
			'needle_start'   => $needleStart,
			'needle_end'     => $needleEnd,
			'haystack_start' => $haystackStart,
			'haystack_end'   => $haystackEnd,
		);
	}

	/**
	 * Apply the fix to convert assertTrue(in_array($needle, $haystack)) to assertContains($needle, $haystack).
	 *
	 * @param File   $phpcsFile        The file being scanned.
	 * @param int    $methodNamePtr    The assertTrue/assertFalse token position.
	 * @param int    $openParen        The opening parenthesis of assertTrue.
	 * @param int    $funcPtr          The in_array token position.
	 * @param int    $funcOpenParen    The opening parenthesis of in_array.
	 * @param int    $funcCloseParen   The closing parenthesis of in_array.
	 * @param int    $assertCloseParen The closing parenthesis of assertTrue.
	 * @param string $newMethodName    The new method name.
	 * @param array  $args             The parsed arguments.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $methodNamePtr, $openParen, $funcPtr, $funcOpenParen, $funcCloseParen, $assertCloseParen, $newMethodName, $args ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		// Build the needle content.
		$needleContent = '';

		for ( $i = $args['needle_start']; $i <= $args['needle_end']; $i++ ) {
			$needleContent .= $tokens[ $i ]['content'];
		}

		// Build the haystack content.
		$haystackContent = '';

		for ( $i = $args['haystack_start']; $i <= $args['haystack_end']; $i++ ) {
			$haystackContent .= $tokens[ $i ]['content'];
		}

		$fixer->beginChangeset();

		// Replace the method name.
		$fixer->replaceToken( $methodNamePtr, $newMethodName );

		// Remove everything from after ( to before the inner content of in_array.
		// We want to keep: assertContains( <inner content> )
		// Remove: in_array(
		for ( $i = $openParen + 1; $i <= $funcOpenParen; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Replace the content inside in_array with just needle, haystack (no third arg).
		// First, clear everything from after funcOpenParen to funcCloseParen.
		for ( $i = $funcOpenParen + 1; $i < $funcCloseParen; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Add the new content after the opening paren.
		$fixer->addContent( $funcOpenParen, ' ' . $needleContent . ', ' . $haystackContent . ' ' );

		// Remove the closing parenthesis of in_array.
		$fixer->replaceToken( $funcCloseParen, '' );

		// Remove whitespace between in_array's closing paren and assertTrue's closing paren.
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
