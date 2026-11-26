<?php
/**
 * Sniff to detect a JSON echo followed by a terminator that can be replaced with wp_send_json().
 *
 * Detects patterns like:
 * echo wp_json_encode( $response );
 * wp_die();
 *
 * These can be simplified to: wp_send_json( $response );
 *
 * The echo must be a standalone statement that outputs only json_encode() or wp_json_encode()
 * with a single argument, directly followed in the same block by wp_die(), die or exit with
 * no arguments.
 *
 * This fix changes the HTTP response, so the rule is off in the shared ruleset and each plugin
 * opts in after checking the JavaScript that reads each response:
 * - wp_send_json() sends a Content-Type: application/json header. jQuery.ajax() without a
 *   dataType parses that response into an object, so a success callback that calls
 *   JSON.parse( response ) or response.replace() on it then throws. A consumer that uses
 *   dataType: 'json', fetch() with response.json(), or XMLHttpRequest.responseText is unaffected.
 * - Outside an AJAX request wp_send_json() ends with die, where wp_die() would print its HTML page.
 * - An echo preceded in the same function, closure or file scope by a header() call that may set a
 *   Content-Type is skipped, because wp_send_json() would replace that Content-Type with JSON.
 * - A json_encode() source is reported as UseWpSendJsonFromJsonEncode because wp_send_json()
 *   encodes with wp_json_encode(). The output is identical when json_encode() succeeds, and
 *   wp_json_encode() repairs invalid UTF-8 where json_encode() would have printed nothing.
 *
 * <rule ref="Formidable.CodeAnalysis.PreferWpSendJson">
 *     <severity>5</severity>
 * </rule>
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Detects and fixes echoed JSON followed by a terminator that wp_send_json() replaces.
 */
class PreferWpSendJsonSniff implements Sniff {

	/**
	 * Encoders that can be replaced, keyed to the error code they report.
	 *
	 * @var array<string, string>
	 */
	private $encoders = array(
		'wp_json_encode' => 'UseWpSendJson',
		'json_encode'    => 'UseWpSendJsonFromJsonEncode',
	);

	/**
	 * Tokens that may end the statement before a standalone echo.
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
		return array( T_ECHO );
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
		$prev   = $phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true );

		// The body of a brace-less control structure is a different block from the terminator after it.
		if ( false === $prev || ! isset( $this->statementStarts[ $tokens[ $prev ]['code'] ] ) ) {
			return;
		}

		$encoder = $phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true );

		if ( false !== $encoder && T_NS_SEPARATOR === $tokens[ $encoder ]['code'] ) {
			$encoder = $phpcsFile->findNext( Tokens::$emptyTokens, $encoder + 1, null, true );
		}

		if ( false === $encoder || T_STRING !== $tokens[ $encoder ]['code'] ) {
			return;
		}

		$encoderName = strtolower( $tokens[ $encoder ]['content'] );

		if ( ! isset( $this->encoders[ $encoderName ] ) ) {
			return;
		}

		$openParen = $phpcsFile->findNext( Tokens::$emptyTokens, $encoder + 1, null, true );

		if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] || ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Flags and depth have no faithful wp_send_json() equivalent here, so only one argument is rewritten.
		if ( 1 !== $this->countArguments( $phpcsFile, $openParen, $closeParen ) ) {
			return;
		}

		$echoEnd = $phpcsFile->findNext( Tokens::$emptyTokens, $closeParen + 1, null, true );

		if ( false === $echoEnd || T_SEMICOLON !== $tokens[ $echoEnd ]['code'] ) {
			return;
		}

		// Only whitespace may separate the two statements, so the fix never deletes a comment.
		$terminator = $phpcsFile->findNext( T_WHITESPACE, $echoEnd + 1, null, true );

		if ( false === $terminator ) {
			return;
		}

		$terminatorEnd = $this->getTerminatorEnd( $phpcsFile, $terminator );

		if ( false === $terminatorEnd ) {
			return;
		}

		// wp_send_json() would override a Content-Type the code chose on purpose.
		if ( $this->hasContentTypeHeaderBefore( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$replacement = 'wp_send_json(' . $phpcsFile->getTokensAsString( $openParen + 1, $closeParen - $openParen - 1 ) . ');';

		$fix = $phpcsFile->addFixableError(
			'Use wp_send_json() instead of echo %s() followed by %s.',
			$stackPtr,
			$this->encoders[ $encoderName ],
			array(
				$tokens[ $encoder ]['content'],
				trim( $phpcsFile->getTokensAsString( $terminator, $terminatorEnd - $terminator ) ),
			)
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $stackPtr, $replacement );

			for ( $i = $stackPtr + 1; $i <= $terminatorEnd; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Count the top-level arguments between a pair of parentheses.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The opening parenthesis.
	 * @param int  $closeParen The closing parenthesis.
	 *
	 * @return int
	 */
	private function countArguments( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		if ( false === $phpcsFile->findNext( Tokens::$emptyTokens, $openParen + 1, $closeParen, true ) ) {
			return 0;
		}

		$count = 1;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			// Skip nested parentheses, brackets and short arrays as a whole.
			if ( isset( $tokens[ $i ]['parenthesis_closer'] ) && $tokens[ $i ]['parenthesis_closer'] > $i ) {
				$i = $tokens[ $i ]['parenthesis_closer'];
				continue;
			}

			if ( isset( $tokens[ $i ]['bracket_closer'] ) && $tokens[ $i ]['bracket_closer'] > $i ) {
				$i = $tokens[ $i ]['bracket_closer'];
				continue;
			}

			if ( T_COMMA === $tokens[ $i ]['code'] ) {
				++$count;
			}
		}

		return $count;
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

	/**
	 * Check whether a header() call that may set a Content-Type precedes a token in its scope.
	 *
	 * The scope is the innermost enclosing function or closure, or the file outside any function.
	 * Nested function and closure bodies are skipped, because their headers are not sent by this code.
	 * A header() whose first argument is not built from string literals is treated as a Content-Type,
	 * since the sniff cannot see its value.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The token to look back from.
	 *
	 * @return bool
	 */
	private function hasContentTypeHeaderBefore( File $phpcsFile, $stackPtr ) {
		$tokens     = $phpcsFile->getTokens();
		$scopeStart = 0;

		foreach ( array_reverse( $tokens[ $stackPtr ]['conditions'], true ) as $conditionPtr => $conditionCode ) {
			if ( T_FUNCTION === $conditionCode || T_CLOSURE === $conditionCode ) {
				$scopeStart = $tokens[ $conditionPtr ]['scope_opener'] + 1;
				break;
			}
		}

		for ( $i = $scopeStart; $i < $stackPtr; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( ( T_FUNCTION === $code || T_CLOSURE === $code || T_FN === $code ) && isset( $tokens[ $i ]['scope_closer'] ) ) {
				$i = $tokens[ $i ]['scope_closer'];
				continue;
			}

			if ( T_STRING !== $code || 'header' !== strtolower( $tokens[ $i ]['content'] ) ) {
				continue;
			}

			$prev = $phpcsFile->findPrevious( Tokens::$emptyTokens, $i - 1, null, true );

			if ( false !== $prev && T_NS_SEPARATOR === $tokens[ $prev ]['code'] ) {
				$prev = $phpcsFile->findPrevious( Tokens::$emptyTokens, $prev - 1, null, true );
			}

			if ( false !== $prev && isset( Tokens::$objectOperators[ $tokens[ $prev ]['code'] ] ) ) {
				continue;
			}

			if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_FUNCTION, T_NEW, T_CONST ), true ) ) {
				continue;
			}

			$openParen = $phpcsFile->findNext( Tokens::$emptyTokens, $i + 1, null, true );

			if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] || ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
				continue;
			}

			if ( $this->firstArgumentMaySetContentType( $phpcsFile, $openParen ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether the first argument of a header() call may set a Content-Type.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $openParen The opening parenthesis of the call.
	 *
	 * @return bool
	 */
	private function firstArgumentMaySetContentType( File $phpcsFile, $openParen ) {
		$tokens     = $phpcsFile->getTokens();
		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$hasLiteral = false;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			if ( isset( $tokens[ $i ]['parenthesis_closer'] ) && $tokens[ $i ]['parenthesis_closer'] > $i ) {
				$i = $tokens[ $i ]['parenthesis_closer'];
				continue;
			}

			if ( isset( $tokens[ $i ]['bracket_closer'] ) && $tokens[ $i ]['bracket_closer'] > $i ) {
				$i = $tokens[ $i ]['bracket_closer'];
				continue;
			}

			if ( T_COMMA === $tokens[ $i ]['code'] ) {
				break;
			}

			if ( isset( Tokens::$stringTokens[ $tokens[ $i ]['code'] ] ) || T_DOUBLE_QUOTED_STRING === $tokens[ $i ]['code'] ) {
				$hasLiteral = true;

				if ( false !== stripos( $tokens[ $i ]['content'], 'content-type' ) ) {
					return true;
				}
			}
		}

		return ! $hasLiteral;
	}
}
