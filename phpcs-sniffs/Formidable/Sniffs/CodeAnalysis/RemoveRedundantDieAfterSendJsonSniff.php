<?php
/**
 * Sniff to detect wp_die(), die or exit directly after a wp_send_json() call that already terminates the request.
 *
 * Detects patterns like:
 * wp_send_json_success( $data );
 * wp_die();
 *
 * These can be simplified to: wp_send_json_success( $data );
 *
 * wp_send_json(), wp_send_json_success() and wp_send_json_error() never return. They call
 * wp_die() during an AJAX request and die otherwise, so the statement after them cannot run.
 * Only a terminator with no arguments that directly follows the call as its own statement, in
 * the same block, is removed. A call that is the body of a brace-less control structure is
 * left alone, because the terminator after it runs when the condition is false.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Detects and removes a terminator that directly follows a wp_send_json() family call.
 */
class RemoveRedundantDieAfterSendJsonSniff implements Sniff {

	/**
	 * Functions that send a JSON response and terminate the request.
	 *
	 * @var array<string, bool>
	 */
	private $sendFunctions = array(
		'wp_send_json'         => true,
		'wp_send_json_success' => true,
		'wp_send_json_error'   => true,
	);

	/**
	 * Tokens that may end the statement before a standalone call.
	 *
	 * @var array<int|string, bool>
	 */
	private $statementStarts = array(
		T_SEMICOLON           => true,
		T_OPEN_CURLY_BRACKET  => true,
		T_CLOSE_CURLY_BRACKET => true,
		T_COLON               => true,
		T_OPEN_TAG            => true,
	);

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING );
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

		if ( ! isset( $this->sendFunctions[ strtolower( $tokens[ $stackPtr ]['content'] ) ] ) ) {
			return;
		}

		if ( ! $this->isStandaloneStatement( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$openParen = $phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true );

		if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] || ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$semicolon = $phpcsFile->findNext( Tokens::$emptyTokens, $tokens[ $openParen ]['parenthesis_closer'] + 1, null, true );

		if ( false === $semicolon || T_SEMICOLON !== $tokens[ $semicolon ]['code'] ) {
			return;
		}

		// Only whitespace may separate the two statements, so the fix never deletes a comment.
		$terminator = $phpcsFile->findNext( T_WHITESPACE, $semicolon + 1, null, true );

		if ( false === $terminator ) {
			return;
		}

		$terminatorEnd = $this->getTerminatorEnd( $phpcsFile, $terminator );

		if ( false === $terminatorEnd ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Remove the %s after %s(), which already terminates the request.',
			$terminator,
			'Found',
			array(
				trim( $phpcsFile->getTokensAsString( $terminator, $terminatorEnd - $terminator ) ),
				$tokens[ $stackPtr ]['content'],
			)
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			for ( $i = $semicolon + 1; $i <= $terminatorEnd; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Check that the call is a global function call that starts its own statement.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the function name.
	 *
	 * @return bool
	 */
	private function isStandaloneStatement( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$prev   = $phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true );

		// A fully qualified \wp_send_json() is the same function, a namespaced Foo\wp_send_json() is not.
		if ( false !== $prev && T_NS_SEPARATOR === $tokens[ $prev ]['code'] ) {
			$prev = $phpcsFile->findPrevious( Tokens::$emptyTokens, $prev - 1, null, true );
		}

		return false !== $prev && isset( $this->statementStarts[ $tokens[ $prev ]['code'] ] );
	}

	/**
	 * Find the semicolon that ends a terminator statement with no arguments.
	 *
	 * Matches wp_die();, die;, die();, exit; and exit();.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $terminator The first token of the statement.
	 *
	 * @return false|int
	 */
	private function getTerminatorEnd( File $phpcsFile, $terminator ) {
		$tokens = $phpcsFile->getTokens();
		$next   = $phpcsFile->findNext( T_WHITESPACE, $terminator + 1, null, true );

		if ( false === $next ) {
			return false;
		}

		if ( T_STRING === $tokens[ $terminator ]['code'] ) {
			if ( 'wp_die' !== strtolower( $tokens[ $terminator ]['content'] ) || T_OPEN_PARENTHESIS !== $tokens[ $next ]['code'] ) {
				return false;
			}
		} elseif ( T_EXIT !== $tokens[ $terminator ]['code'] ) {
			return false;
		}

		if ( T_OPEN_PARENTHESIS === $tokens[ $next ]['code'] ) {
			$closeParen = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );

			if ( false === $closeParen || T_CLOSE_PARENTHESIS !== $tokens[ $closeParen ]['code'] ) {
				return false;
			}

			$next = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, null, true );
		}

		if ( false === $next || T_SEMICOLON !== $tokens[ $next ]['code'] ) {
			return false;
		}

		return $next;
	}
}
