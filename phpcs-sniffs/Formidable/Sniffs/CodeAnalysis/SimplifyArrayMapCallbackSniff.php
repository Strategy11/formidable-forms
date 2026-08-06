<?php
/**
 * Formidable_Sniffs_CodeAnalysis_SimplifyArrayMapCallbackSniff
 *
 * Detects array_map calls where the callback is an anonymous function
 * that only returns a single function call passing its parameter through.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects array_map callbacks that can be simplified to a callable string.
 *
 * Bad:
 * array_map( function( $val ) { return floatval( $val ); }, $values );
 * array_map( static function ( $val ) { return intval( $val ); }, $values );
 *
 * Good:
 * array_map( 'floatval', $values );
 * array_map( 'intval', $values );
 */
class SimplifyArrayMapCallbackSniff implements Sniff {

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

		if ( $tokens[ $stackPtr ]['content'] !== 'array_map' ) {
			return;
		}

		// Make sure this is a function call (followed by open parenthesis).
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Make sure this is not a method call or function definition.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prevToken && in_array( $tokens[ $prevToken ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION ), true ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Find the first non-whitespace token inside array_map().
		$firstArg = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $firstArg ) {
			return;
		}

		// Handle `static function` - skip the static keyword.
		$closureStart = $firstArg;

		if ( $tokens[ $firstArg ]['code'] === T_STATIC ) {
			$closureStart = $phpcsFile->findNext( T_WHITESPACE, $firstArg + 1, $closeParen, true );

			if ( false === $closureStart ) {
				return;
			}
		}

		// The token should be T_CLOSURE (function keyword for anonymous functions).
		if ( $tokens[ $closureStart ]['code'] !== T_CLOSURE ) {
			return;
		}

		$callbackInfo = $this->analyzeCallback( $phpcsFile, $tokens, $closureStart );

		if ( false === $callbackInfo ) {
			return;
		}

		$functionName = $callbackInfo['function_name'];

		// Find the end of the closure (scope_closer is the closing brace).
		$closureEnd = $tokens[ $closureStart ]['scope_closer'];

		// Find the comma after the closure.
		$commaAfterClosure = $phpcsFile->findNext( T_WHITESPACE, $closureEnd + 1, $closeParen, true );

		if ( false === $commaAfterClosure || $tokens[ $commaAfterClosure ]['code'] !== T_COMMA ) {
			return;
		}

		// Get everything after the comma (the remaining arguments to array_map).
		$remainingArgsStart = $phpcsFile->findNext( T_WHITESPACE, $commaAfterClosure + 1, $closeParen, true );

		if ( false === $remainingArgsStart ) {
			return;
		}

		$remainingArgs = trim( $phpcsFile->getTokensAsString( $remainingArgsStart, $closeParen - $remainingArgsStart ) );

		$fix = $phpcsFile->addFixableError(
			'Simplify array_map callback to \'%s\' instead of wrapping in an anonymous function.',
			$stackPtr,
			'SimplifyCallback',
			array( $functionName )
		);

		if ( true !== $fix ) {
			return;
		}

		$phpcsFile->fixer->beginChangeset();

		// Replace everything from array_map open paren to close paren.
		for ( $i = $openParen; $i <= $closeParen; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		$phpcsFile->fixer->addContent( $openParen, '( \'' . $functionName . '\', ' . $remainingArgs . ' )' );

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Analyze a closure to determine if it is a simple single-function pass-through.
	 *
	 * Checks that the closure:
	 * 1. Has exactly one parameter with no type hint or default value.
	 * 2. Has a body consisting of a single `return func( $param );` statement.
	 * 3. The inner function call receives only the closure parameter as its argument.
	 *
	 * @param File  $phpcsFile    The file being scanned.
	 * @param array $tokens       The token stack.
	 * @param int   $closureStart The position of the T_CLOSURE token.
	 *
	 * @return array|false Array with 'function_name' key on success, false otherwise.
	 */
	private function analyzeCallback( File $phpcsFile, array $tokens, $closureStart ) {
		// Get the closure's parameter list.
		if ( ! isset( $tokens[ $closureStart ]['parenthesis_opener'], $tokens[ $closureStart ]['parenthesis_closer'] ) ) {
			return false;
		}

		$paramOpen  = $tokens[ $closureStart ]['parenthesis_opener'];
		$paramClose = $tokens[ $closureStart ]['parenthesis_closer'];

		// Find the single parameter variable.
		$paramInfo = $this->getSingleParameter( $phpcsFile, $tokens, $paramOpen, $paramClose );

		if ( false === $paramInfo ) {
			return false;
		}

		$paramName = $paramInfo['name'];

		// Get the closure body.
		if ( ! isset( $tokens[ $closureStart ]['scope_opener'], $tokens[ $closureStart ]['scope_closer'] ) ) {
			return false;
		}

		$bodyOpen  = $tokens[ $closureStart ]['scope_opener'];
		$bodyClose = $tokens[ $closureStart ]['scope_closer'];

		// The body should contain exactly one statement: return func( $param );
		return $this->getSingleReturnFunctionCall( $phpcsFile, $tokens, $bodyOpen, $bodyClose, $paramName );
	}

	/**
	 * Check that the closure has exactly one parameter with no type hint or default.
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $tokens     The token stack.
	 * @param int   $paramOpen  The opening parenthesis of the parameter list.
	 * @param int   $paramClose The closing parenthesis of the parameter list.
	 *
	 * @return array|false Array with 'name' key, or false.
	 */
	private function getSingleParameter( File $phpcsFile, array $tokens, $paramOpen, $paramClose ) {
		$variables    = array();
		$hasTypeHint  = false;
		$hasDefault   = false;
		$hasReference = false;
		$hasVariadic  = false;

		for ( $i = $paramOpen + 1; $i < $paramClose; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_VARIABLE ) {
				$variables[] = $tokens[ $i ]['content'];
			} elseif ( $code === T_COMMA ) {
				// More than one parameter.
				return false;
			} elseif ( $code === T_EQUAL ) {
				$hasDefault = true;
			} elseif ( in_array( $code, array( T_STRING, T_ARRAY_HINT, T_CALLABLE, T_NULLABLE, T_NULL, T_FALSE, T_TRUE, T_TYPE_UNION, T_TYPE_INTERSECTION, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED ), true ) ) {
				$hasTypeHint = true;
			} elseif ( $code === T_BITWISE_AND ) {
				$hasReference = true;
			} elseif ( $code === T_ELLIPSIS ) {
				$hasVariadic = true;
			}
		}

		// Must have exactly one variable, no type hint, no default, no reference, no variadic.
		if ( count( $variables ) !== 1 || $hasTypeHint || $hasDefault || $hasReference || $hasVariadic ) {
			return false;
		}

		return array( 'name' => $variables[0] );
	}

	/**
	 * Check the closure body for a single `return func_name( $param );` statement.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param array  $tokens    The token stack.
	 * @param int    $bodyOpen  The opening brace of the closure body.
	 * @param int    $bodyClose The closing brace of the closure body.
	 * @param string $paramName The closure parameter name (e.g. '$val').
	 *
	 * @return array|false Array with 'function_name' key, or false.
	 */
	private function getSingleReturnFunctionCall( File $phpcsFile, array $tokens, $bodyOpen, $bodyClose, $paramName ) {
		// Find the first non-whitespace token in the body.
		$firstToken = $phpcsFile->findNext( T_WHITESPACE, $bodyOpen + 1, $bodyClose, true );

		if ( false === $firstToken || $tokens[ $firstToken ]['code'] !== T_RETURN ) {
			return false;
		}

		// After return, find the function name.
		$funcNamePtr = $phpcsFile->findNext( T_WHITESPACE, $firstToken + 1, $bodyClose, true );

		if ( false === $funcNamePtr || $tokens[ $funcNamePtr ]['code'] !== T_STRING ) {
			return false;
		}

		$functionName = $tokens[ $funcNamePtr ]['content'];

		// Next should be open parenthesis.
		$innerOpenParen = $phpcsFile->findNext( T_WHITESPACE, $funcNamePtr + 1, $bodyClose, true );

		if ( false === $innerOpenParen || $tokens[ $innerOpenParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		if ( ! isset( $tokens[ $innerOpenParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$innerCloseParen = $tokens[ $innerOpenParen ]['parenthesis_closer'];

		// The only argument inside the inner function call should be the closure parameter.
		$innerArg = $phpcsFile->findNext( T_WHITESPACE, $innerOpenParen + 1, $innerCloseParen, true );

		if ( false === $innerArg || $tokens[ $innerArg ]['code'] !== T_VARIABLE || $tokens[ $innerArg ]['content'] !== $paramName ) {
			return false;
		}

		// Make sure there's nothing else (no second argument) before the close paren.
		$afterArg = $phpcsFile->findNext( T_WHITESPACE, $innerArg + 1, $innerCloseParen, true );

		if ( false !== $afterArg ) {
			return false;
		}

		// After the inner close paren, expect a semicolon.
		$semicolon = $phpcsFile->findNext( T_WHITESPACE, $innerCloseParen + 1, $bodyClose, true );

		if ( false === $semicolon || $tokens[ $semicolon ]['code'] !== T_SEMICOLON ) {
			return false;
		}

		// Make sure there's nothing else in the body after the semicolon.
		$afterSemicolon = $phpcsFile->findNext( T_WHITESPACE, $semicolon + 1, $bodyClose, true );

		if ( false !== $afterSemicolon ) {
			return false;
		}

		return array( 'function_name' => $functionName );
	}
}
