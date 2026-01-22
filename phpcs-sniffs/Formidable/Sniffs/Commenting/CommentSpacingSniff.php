<?php
/**
 * Formidable_Sniffs_Commenting_CommentSpacingSniff
 *
 * Ensures single-line comments have a space after //.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures single-line comments have a space after // and start with a capital letter.
 *
 * Bad:
 * //This is a comment
 * // this is a comment
 *
 * Good:
 * // This is a comment
 *
 * Exception:
 * //end if (kept as-is)
 * //end foreach (kept as-is)
 * // $variable (variables/code references kept as-is)
 */
class CommentSpacingSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_COMMENT );
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
		$tokens  = $phpcsFile->getTokens();
		$content = $tokens[ $stackPtr ]['content'];

		// Only process // style comments.
		if ( strpos( $content, '//' ) !== 0 ) {
			return;
		}

		// Skip //end comments.
		if ( preg_match( '/^\/\/\s*end\b/i', $content ) ) {
			return;
		}

		// Get the comment text after //.
		$commentText = substr( $content, 2 );

		// Skip empty comments (just //).
		if ( trim( $commentText ) === '' ) {
			return;
		}

		$hasSpaceAfterSlash = strpos( $content, '// ' ) === 0;
		$trimmedText        = ltrim( $commentText );
		$firstChar          = substr( $trimmedText, 0, 1 );

		// Check if first character should be capitalized.
		// Skip if it starts with: $variable, @annotation, numbers, special chars, looks like code, single word, underscore word, abbreviations, or is a continuation comment.
		$looksLikeCode            = $this->looksLikeCode( $trimmedText );
		$isSingleWord             = $this->isSingleWord( $trimmedText );
		$startsWithUnderscoreWord = $this->startsWithUnderscoreWord( $trimmedText );
		$isContinuationComment    = $this->isContinuationComment( $phpcsFile, $stackPtr );
		$shouldCapitalize = $hasSpaceAfterSlash
			&& preg_match( '/^[a-z]/', $firstChar )
			&& ! $looksLikeCode
			&& ! $isSingleWord
			&& ! $startsWithUnderscoreWord
			&& ! $isContinuationComment
			&& ! preg_match( '/^(end\b|phpcs:|eslint|TODO|FIXME|translators:|e\.g\.|i\.e\.|etc\.|iDeal)/i', $trimmedText );

		if ( ! $hasSpaceAfterSlash ) {
			$fix = $phpcsFile->addFixableError(
				'Single-line comment must have a space after //.',
				$stackPtr,
				'NoSpaceAfterDoubleSlash'
			);

			if ( true === $fix ) {
				$newText = $trimmedText;

				// Also capitalize if needed (but not for code-like comments, single words, underscore words, abbreviations, or continuation comments).
				if ( preg_match( '/^[a-z]/', $firstChar ) && ! $looksLikeCode && ! $isSingleWord && ! $startsWithUnderscoreWord && ! $isContinuationComment && ! preg_match( '/^(end\b|phpcs:|eslint|translators:|e\.g\.|i\.e\.|etc\.|iDeal)/i', $trimmedText ) ) {
					$newText = ucfirst( $trimmedText );
				}

				$newContent = '// ' . $newText;
				$phpcsFile->fixer->replaceToken( $stackPtr, $newContent );
			}

			return;
		}

		if ( $shouldCapitalize ) {
			$fix = $phpcsFile->addFixableError(
				'Single-line comment must start with a capital letter.',
				$stackPtr,
				'LowercaseComment'
			);

			if ( true === $fix ) {
				$newContent = '// ' . ucfirst( $trimmedText );
				$phpcsFile->fixer->replaceToken( $stackPtr, $newContent );
			}
		}
	}

	/**
	 * Check if the comment text looks like code.
	 *
	 * @param string $text The comment text to check.
	 *
	 * @return bool True if the text looks like code.
	 */
	private function looksLikeCode( $text ) {
		// Starts with $ (variable).
		if ( strpos( $text, '$' ) === 0 ) {
			return true;
		}

		// Starts with @ (annotation).
		if ( strpos( $text, '@' ) === 0 ) {
			return true;
		}

		// Contains -> or :: (object/static access).
		if ( strpos( $text, '->' ) !== false || strpos( $text, '::' ) !== false ) {
			return true;
		}

		// Contains = (assignment).
		if ( preg_match( '/\s*=\s*/', $text ) ) {
			return true;
		}

		// Contains () (function call).
		if ( preg_match( '/\w+\s*\(/', $text ) ) {
			return true;
		}

		// Contains [] (array access).
		if ( strpos( $text, '[' ) !== false && strpos( $text, ']' ) !== false ) {
			return true;
		}

		// Starts with a function/method name pattern like "function_name" or contains underscores typical of code.
		if ( preg_match( '/^[a-z_]+\s*$/', $text ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the comment is a single word.
	 *
	 * @param string $text The comment text to check.
	 *
	 * @return bool True if the text is a single word.
	 */
	private function isSingleWord( $text ) {
		$text = rtrim( $text );

		// Single word: no spaces, just letters/numbers/underscores.
		return preg_match( '/^\S+$/', $text ) && ! preg_match( '/\s/', $text );
	}

	/**
	 * Check if the comment starts with a word containing underscores (function name).
	 *
	 * @param string $text The comment text to check.
	 *
	 * @return bool True if the first word contains underscores.
	 */
	private function startsWithUnderscoreWord( $text ) {
		// Get the first word.
		$parts = preg_split( '/\s+/', $text, 2 );

		if ( empty( $parts[0] ) ) {
			return false;
		}

		$firstWord = $parts[0];

		// Check if the first word contains an underscore.
		return strpos( $firstWord, '_' ) !== false;
	}

	/**
	 * Check if this comment is a continuation of a previous comment (multiline comment using //).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return bool True if this comment follows another comment on the previous line.
	 */
	private function isContinuationComment( File $phpcsFile, $stackPtr ) {
		$tokens      = $phpcsFile->getTokens();
		$currentLine = $tokens[ $stackPtr ]['line'];

		// Look for a comment on the previous line.
		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			$tokenLine = $tokens[ $i ]['line'];

			// Stop if we've gone back more than one line.
			if ( $tokenLine < $currentLine - 1 ) {
				return false;
			}

			// Skip whitespace on the same or previous line.
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			// If we find a comment on the previous line, this is a continuation.
			if ( $tokenLine === $currentLine - 1 && $tokens[ $i ]['code'] === T_COMMENT ) {
				// Make sure it's also a // style comment.
				if ( strpos( $tokens[ $i ]['content'], '//' ) === 0 ) {
					return true;
				}
			}

			// Found something else, not a continuation.
			return false;
		}

		return false;
	}
}
