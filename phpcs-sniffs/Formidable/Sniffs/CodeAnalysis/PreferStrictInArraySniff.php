<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferStrictInArraySniff
 *
 * Adds $strict = true to in_array() calls when the haystack is an array of safe strings.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Adds strict mode to in_array() when the haystack contains only safe string literals.
 *
 * Bad:
 * in_array( $var, array( 'foo', 'bar', 'baz' ) )
 *
 * Good:
 * in_array( $var, array( 'foo', 'bar', 'baz' ), true )
 *
 * Only applies when the array contains only non-empty, non-numeric string literals.
 */
class PreferStrictInArraySniff implements Sniff {

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

		// Check if this is an in_array call.
		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'in_array' ) {
			return;
		}

		// Check if there's a phpcs:ignore comment for this line.
		if ( $this->hasIgnoreComment( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Find the opening parenthesis.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Parse the arguments.
		$args = $this->parseArguments( $phpcsFile, $openParen, $closeParen );

		// in_array needs at least 2 arguments.
		if ( count( $args ) < 2 ) {
			return;
		}

		// If there's already a third argument, skip.
		if ( count( $args ) >= 3 ) {
			return;
		}

		// Check if the second argument (haystack) is an array of safe strings.
		$haystackArg = $args[1];

		if ( ! $this->isArrayOfSafeStrings( $phpcsFile, $haystackArg['start'], $haystackArg['end'] ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Add strict mode (true) to in_array() when comparing against an array of strings',
			$stackPtr,
			'Found'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			// Find the position just before the closing parenthesis.
			$insertPos = $phpcsFile->findPrevious( T_WHITESPACE, $closeParen - 1, null, true );

			// Add ", true" after the last argument.
			$phpcsFile->fixer->addContent( $insertPos, ', true' );

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Parse the arguments of a function call.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The position of the opening parenthesis.
	 * @param int  $closeParen The position of the closing parenthesis.
	 *
	 * @return array Array of arguments, each with 'start' and 'end' positions.
	 */
	private function parseArguments( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();
		$args   = array();
		$start  = $openParen + 1;
		$depth  = 0;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Track nesting depth using bracket/paren closers when available.
			if ( $code === T_OPEN_PARENTHESIS && isset( $tokens[ $i ]['parenthesis_closer'] ) ) {
				++$depth;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				--$depth;
				continue;
			}

			if ( $code === T_OPEN_SHORT_ARRAY && isset( $tokens[ $i ]['bracket_closer'] ) ) {
				++$depth;
				continue;
			}

			if ( $code === T_CLOSE_SHORT_ARRAY ) {
				--$depth;
				continue;
			}

			if ( $code === T_OPEN_SQUARE_BRACKET ) {
				++$depth;
				continue;
			}

			if ( $code === T_CLOSE_SQUARE_BRACKET ) {
				--$depth;
				continue;
			}

			// Comma at depth 0 separates arguments.
			if ( $code === T_COMMA && $depth === 0 ) {
				$args[] = array(
					'start' => $start,
					'end'   => $i - 1,
				);
				$start  = $i + 1;
			}
		}

		// Add the last argument.
		if ( $start < $closeParen ) {
			$args[] = array(
				'start' => $start,
				'end'   => $closeParen - 1,
			);
		}

		return $args;
	}

	/**
	 * Check if the given range contains an array of safe strings.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     The start position.
	 * @param int  $end       The end position.
	 *
	 * @return bool
	 */
	private function isArrayOfSafeStrings( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();

		// Find the array start.
		$arrayStart = $phpcsFile->findNext( T_WHITESPACE, $start, $end + 1, true );

		if ( false === $arrayStart ) {
			return false;
		}

		$arrayOpen  = false;
		$arrayClose = false;

		// Check for short array syntax [ ].
		if ( $tokens[ $arrayStart ]['code'] === T_OPEN_SHORT_ARRAY ) {
			$arrayOpen = $arrayStart;
			if ( isset( $tokens[ $arrayStart ]['bracket_closer'] ) ) {
				$arrayClose = $tokens[ $arrayStart ]['bracket_closer'];
			}
		}

		// Check for long array syntax array( ).
		if ( $tokens[ $arrayStart ]['code'] === T_ARRAY ) {
			$parenOpen = $phpcsFile->findNext( T_WHITESPACE, $arrayStart + 1, null, true );
			if ( false !== $parenOpen && $tokens[ $parenOpen ]['code'] === T_OPEN_PARENTHESIS ) {
				$arrayOpen = $parenOpen;
				if ( isset( $tokens[ $parenOpen ]['parenthesis_closer'] ) ) {
					$arrayClose = $tokens[ $parenOpen ]['parenthesis_closer'];
				}
			}
		}

		if ( false === $arrayOpen || false === $arrayClose ) {
			return false;
		}

		// Check all elements in the array.
		$hasElements = false;

		for ( $i = $arrayOpen + 1; $i < $arrayClose; $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Skip whitespace and commas.
			if ( in_array( $code, array( T_WHITESPACE, T_COMMA ), true ) ) {
				continue;
			}

			// Skip nested structures.
			if ( in_array( $code, array( T_OPEN_PARENTHESIS, T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET ), true ) ) {
				// Skip to the closing bracket.
				if ( isset( $tokens[ $i ]['parenthesis_closer'] ) ) {
					$i = $tokens[ $i ]['parenthesis_closer'];
				} elseif ( isset( $tokens[ $i ]['bracket_closer'] ) ) {
					$i = $tokens[ $i ]['bracket_closer'];
				}
				continue;
			}

			// Must be a string literal.
			if ( $code !== T_CONSTANT_ENCAPSED_STRING ) {
				return false;
			}

			// Check if the string is safe.
			if ( ! $this->isSafeString( $tokens[ $i ]['content'] ) ) {
				return false;
			}

			$hasElements = true;
		}

		// Must have at least one element.
		return $hasElements;
	}

	/**
	 * Check if a string value is safe for strict comparison.
	 *
	 * @param string $stringValue The string token content (including quotes).
	 *
	 * @return bool
	 */
	private function isSafeString( $stringValue ) {
		// Remove quotes.
		$content = substr( $stringValue, 1, -1 );

		// Empty string is not safe.
		if ( $content === '' ) {
			return false;
		}

		// Numeric strings are not safe.
		if ( is_numeric( $content ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if there's a phpcs:ignore comment for this line.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return bool
	 */
	private function hasIgnoreComment( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$line   = $tokens[ $stackPtr ]['line'];

		// Check the previous line for a phpcs:ignore comment.
		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $line - 1 ) {
				break;
			}

			if ( $tokens[ $i ]['line'] === $line - 1 || $tokens[ $i ]['line'] === $line ) {
				if ( in_array( $tokens[ $i ]['code'], array( T_COMMENT, T_DOC_COMMENT_STRING ), true ) ) {
					if ( strpos( $tokens[ $i ]['content'], 'phpcs:ignore' ) !== false ) {
						// Check if it ignores StrictInArray or our sniff.
						if ( strpos( $tokens[ $i ]['content'], 'StrictInArray' ) !== false ||
							strpos( $tokens[ $i ]['content'], 'PreferStrictInArray' ) !== false ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}
}
