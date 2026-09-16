<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferStrictInArrayNeedleSniff
 *
 * Adds $strict = true to in_array() calls whose needle is a safe string literal.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Adds strict mode to in_array() when the needle is a non-empty, non-numeric string literal.
 *
 * Bad:
 * in_array( 'pending', (array) $user->roles )
 *
 * Good:
 * in_array( 'pending', (array) $user->roles, true )
 *
 * This is the mirror of PreferStrictInArray, which only fires when the *haystack* is an array
 * literal of safe strings. Here the haystack is any expression and the needle carries the proof:
 * a non-empty, non-numeric string literal cannot be coerced into matching a number, null or the
 * empty string under either PHP 7 or PHP 8 comparison rules.
 *
 * What the needle does not rule out is a haystack element that is loosely but not strictly equal
 * to it: a boolean ( 'pending' == true ), an object with __toString(), or -- on PHP 7.0, which
 * these plugins still support -- the integer 0, since 'pending' casts to 0 there. Every one of
 * those is a bug in the haystack rather than intent, and WPCS already reports the whole family:
 * Lite's phpcs.xml promotes WordPress.PHP.StrictInArray.MissingTrueStrict to an error, so a
 * non-strict in_array() is a build failure with or without this sniff. What WPCS has no fixer
 * for is the fix, which is what this rule adds for the subset where the needle proves it.
 */
class PreferStrictInArrayNeedleSniff implements Sniff {

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

		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'in_array' ) {
			return;
		}

		// A method or static call that happens to be named in_array is not the global function.
		$before = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $before ) {
			$skip = array( T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW );

			if ( in_array( $tokens[ $before ]['code'], $skip, true ) ) {
				return;
			}
		}

		if ( $this->hasIgnoreComment( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$args       = $this->parseArguments( $phpcsFile, $openParen, $closeParen );

		// Only a two argument call can be missing $strict.
		if ( count( $args ) !== 2 ) {
			return;
		}

		if ( ! $this->isSafeStringLiteral( $phpcsFile, $args[0]['start'], $args[0]['end'] ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Add strict mode (true) to in_array() when the needle is a string literal',
			$stackPtr,
			'Found'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();

			$insertPos = $phpcsFile->findPrevious( T_WHITESPACE, $closeParen - 1, null, true );
			$phpcsFile->fixer->addContent( $insertPos, ', true' );

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Check whether a range is exactly one safe string literal.
	 *
	 * Anything else in the range -- a concatenation, a variable, a constant, a function call --
	 * means the needle's runtime value is not known here, so the rule does not apply.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     The start position.
	 * @param int  $end       The end position.
	 *
	 * @return bool
	 */
	private function isSafeStringLiteral( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();
		$found  = false;

		for ( $i = $start; $i <= $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			if ( $tokens[ $i ]['code'] !== T_CONSTANT_ENCAPSED_STRING || true === $found ) {
				return false;
			}

			if ( ! $this->isSafeString( $tokens[ $i ]['content'] ) ) {
				return false;
			}

			$found = true;
		}

		return $found;
	}

	/**
	 * Check if a string value is safe for strict comparison.
	 *
	 * @param string $stringValue The string token content (including quotes).
	 *
	 * @return bool
	 */
	private function isSafeString( $stringValue ) {
		$content = substr( $stringValue, 1, -1 );

		// An empty string is loosely equal to null and false, so strict mode changes its meaning.
		if ( $content === '' ) {
			return false;
		}

		// A numeric string is loosely equal to the matching number.
		if ( is_numeric( $content ) ) {
			return false;
		}

		// An escape sequence means the quoted content is not the runtime value.
		if ( strpos( $content, '\\' ) !== false ) {
			return false;
		}

		return true;
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

			if ( $code === T_COMMA && $depth === 0 ) {
				$args[] = array(
					'start' => $start,
					'end'   => $i - 1,
				);
				$start  = $i + 1;
			}
		}

		if ( $start < $closeParen ) {
			$args[] = array(
				'start' => $start,
				'end'   => $closeParen - 1,
			);
		}

		return $args;
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

		for ( $i = $stackPtr; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $line - 1 ) {
				break;
			}

			if ( $tokens[ $i ]['code'] !== T_COMMENT && $tokens[ $i ]['code'] !== T_PHPCS_IGNORE ) {
				continue;
			}

			if ( strpos( $tokens[ $i ]['content'], 'phpcs:ignore' ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
