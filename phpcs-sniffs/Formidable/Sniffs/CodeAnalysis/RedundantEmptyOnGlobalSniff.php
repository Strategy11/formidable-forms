<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantEmptyOnGlobalSniff
 *
 * Detects redundant `! empty()` checks on global variables.
 * Since global variables are always defined after `global $var`, empty() is unnecessary
 * and can be simplified to just checking the variable directly.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant empty() checks on global variables.
 *
 * Bad:
 * global $post;
 * if ( is_singular() && ! empty( $post ) ) { ... }
 *
 * Good:
 * global $post;
 * if ( is_singular() && $post ) { ... }
 *
 * Since global variables are always defined (even if null), empty() is redundant
 * for checking if they have a value. A simple truthy check is sufficient.
 */
class RedundantEmptyOnGlobalSniff implements Sniff {

	/**
	 * Track global variables declared in the current file, keyed by scope.
	 *
	 * @var array
	 */
	private $globalVariables = array();

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_GLOBAL, T_EMPTY );
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

		if ( $tokens[ $stackPtr ]['code'] === T_GLOBAL ) {
			$this->processGlobalDeclaration( $phpcsFile, $stackPtr );
			return;
		}

		if ( $tokens[ $stackPtr ]['code'] === T_EMPTY ) {
			$this->processEmptyCall( $phpcsFile, $stackPtr );
		}
	}

	/**
	 * Get the scope key for a token position (function/method start position).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the token.
	 *
	 * @return string The scope key.
	 */
	private function getScopeKey( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the enclosing function/method.
		$scopeTokens = array( T_FUNCTION, T_CLOSURE );

		foreach ( $tokens[ $stackPtr ]['conditions'] as $ptr => $type ) {
			if ( in_array( $type, $scopeTokens, true ) ) {
				return 'scope_' . $ptr;
			}
		}

		// Global scope.
		return 'global';
	}

	/**
	 * Process a global declaration to track the variables.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the global keyword.
	 *
	 * @return void
	 */
	private function processGlobalDeclaration( File $phpcsFile, $stackPtr ) {
		$tokens   = $phpcsFile->getTokens();
		$scopeKey = $this->getScopeKey( $phpcsFile, $stackPtr );

		// Find all variables in the global statement until semicolon.
		$endOfStatement = $phpcsFile->findNext( T_SEMICOLON, $stackPtr + 1 );

		if ( false === $endOfStatement ) {
			return;
		}

		if ( ! isset( $this->globalVariables[ $scopeKey ] ) ) {
			$this->globalVariables[ $scopeKey ] = array();
		}

		for ( $i = $stackPtr + 1; $i < $endOfStatement; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE ) {
				$varName = $tokens[ $i ]['content'];
				$this->globalVariables[ $scopeKey ][ $varName ] = $stackPtr;
			}
		}
	}

	/**
	 * Process an empty() call to check if it's redundant.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the empty keyword.
	 *
	 * @return void
	 */
	private function processEmptyCall( File $phpcsFile, $stackPtr ) {
		$tokens   = $phpcsFile->getTokens();
		$scopeKey = $this->getScopeKey( $phpcsFile, $stackPtr );

		// Check if this empty() is preceded by a negation (!).
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken || $tokens[ $prevToken ]['code'] !== T_BOOLEAN_NOT ) {
			return;
		}

		$notToken = $prevToken;

		// Find the opening parenthesis after empty.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Get the content inside empty().
		$content = $this->getParenthesesContent( $phpcsFile, $openParen, $closeParen );

		if ( false === $content ) {
			return;
		}

		// Check if it's a simple variable (not an array access or property).
		if ( ! $this->isSimpleVariable( $content['content'] ) ) {
			return;
		}

		$varName = $content['content'];

		// Check if this variable was declared as global in the same scope.
		if ( ! isset( $this->globalVariables[ $scopeKey ][ $varName ] ) ) {
			return;
		}

		// Make sure the global declaration comes before this usage.
		if ( $this->globalVariables[ $scopeKey ][ $varName ] >= $stackPtr ) {
			return;
		}

		// We have a match! Report the error.
		$fix = $phpcsFile->addFixableError(
			'Redundant "! empty( %s )" on global variable. Use "%s" instead.',
			$stackPtr,
			'Found',
			array( $varName, $varName )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Remove the "!" token.
			$phpcsFile->fixer->replaceToken( $notToken, '' );

			// Remove whitespace between ! and empty if present.
			for ( $i = $notToken + 1; $i < $stackPtr; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}

			// Replace "empty( $var )" with just "$var".
			$phpcsFile->fixer->replaceToken( $stackPtr, $varName );

			// Remove everything from after "empty" to end of closing paren.
			for ( $i = $stackPtr + 1; $i <= $closeParen; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Check if the content is a simple variable (e.g., $post, not $post->ID or $arr['key']).
	 *
	 * @param string $content The content to check.
	 *
	 * @return bool
	 */
	private function isSimpleVariable( $content ) {
		// A simple variable starts with $ and contains only alphanumeric and underscore.
		return (bool) preg_match( '/^\$[a-zA-Z_][a-zA-Z0-9_]*$/', $content );
	}

	/**
	 * Get the content inside parentheses as a normalized string.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The position of the opening parenthesis.
	 * @param int  $closeParen The position of the closing parenthesis.
	 *
	 * @return array|false Array with 'content', 'start', 'end' keys, or false on failure.
	 */
	private function getParenthesesContent( File $phpcsFile, $openParen, $closeParen ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';
		$start   = null;
		$end     = null;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			if ( null === $start ) {
				$start = $i;
			}

			$end      = $i;
			$content .= $tokens[ $i ]['content'];
		}

		if ( empty( $content ) ) {
			return false;
		}

		return array(
			'content' => $content,
			'start'   => $start,
			'end'     => $end,
		);
	}
}
