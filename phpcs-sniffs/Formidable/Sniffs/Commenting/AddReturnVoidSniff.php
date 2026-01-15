<?php
/**
 * Sniff to add @return void to functions that have no return statement.
 *
 * @package Formidable\Sniffs\Commenting
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Adds @return void to functions that:
 * - Have a docblock but no @return tag
 * - Have no return statements in the function body
 * - Do not contain die(), exit(), or wp_send_json_* calls
 */
class AddReturnVoidSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FUNCTION );
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

		// Must have a scope.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$funcOpener = $tokens[ $stackPtr ]['scope_opener'];
		$funcCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Skip constructors and test methods.
		$funcName = $phpcsFile->getDeclarationName( $stackPtr );

		if ( $funcName === '__construct' || strpos( $funcName, 'test_' ) === 0 ) {
			return;
		}

		// Skip all functions in test files.
		$fileName = $phpcsFile->getFilename();

		if ( strpos( $fileName, '/tests/' ) !== false || strpos( $fileName, 'test_' ) !== false ) {
			return;
		}

		// Check if function has any return statements.
		if ( $this->hasReturnStatement( $phpcsFile, $funcOpener, $funcCloser ) ) {
			return;
		}

		// Check if function has die/exit/wp_send_json_* calls.
		if ( $this->hasTerminatingCall( $phpcsFile, $funcOpener, $funcCloser ) ) {
			return;
		}

		// Find the docblock.
		$docComment = $phpcsFile->findPrevious( T_DOC_COMMENT_CLOSE_TAG, $stackPtr - 1 );

		if ( false === $docComment ) {
			return;
		}

		// Make sure the docblock belongs to this function.
		$docStart = $tokens[ $docComment ]['comment_opener'];

		// Check there's nothing but whitespace between docblock and function.
		$between = $phpcsFile->findNext( T_WHITESPACE, $docComment + 1, $stackPtr, true );

		// Allow for visibility keywords (public, private, protected, static) between docblock and function.
		$allowedBetween = array( T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_FINAL, T_ABSTRACT, T_FUNCTION );

		if ( false !== $between && ! in_array( $tokens[ $between ]['code'], $allowedBetween, true ) ) {
			return;
		}

		// Check if docblock already has @return tag.
		if ( $this->hasReturnTag( $phpcsFile, $docStart, $docComment ) ) {
			return;
		}

		// Find the position to insert @return void (before the closing */).
		$insertPosition = $this->findInsertPosition( $phpcsFile, $docStart, $docComment );

		if ( false === $insertPosition ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Function has no return statement and no @return tag. Add @return void.',
			$stackPtr,
			'MissingReturnVoid'
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $insertPosition, $docComment );
		}
	}

	/**
	 * Check if function has any return statements with values.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $funcOpener  The function scope opener position.
	 * @param int  $funcCloser  The function scope closer position.
	 *
	 * @return bool
	 */
	private function hasReturnStatement( File $phpcsFile, $funcOpener, $funcCloser ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $funcOpener + 1; $i < $funcCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_RETURN ) {
				return true;
			}

			// Skip nested functions/closures.
			if ( $tokens[ $i ]['code'] === T_FUNCTION || $tokens[ $i ]['code'] === T_CLOSURE ) {
				if ( isset( $tokens[ $i ]['scope_closer'] ) ) {
					$i = $tokens[ $i ]['scope_closer'];
				}
			}
		}

		return false;
	}

	/**
	 * Check if function has terminating calls (die, exit, wp_send_json_*).
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $funcOpener  The function scope opener position.
	 * @param int  $funcCloser  The function scope closer position.
	 *
	 * @return bool
	 */
	private function hasTerminatingCall( File $phpcsFile, $funcOpener, $funcCloser ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $funcOpener + 1; $i < $funcCloser; $i++ ) {
			// Check for die() and exit().
			if ( $tokens[ $i ]['code'] === T_EXIT ) {
				return true;
			}

			// Check for wp_send_json_* function calls.
			if ( $tokens[ $i ]['code'] === T_STRING ) {
				$funcName = strtolower( $tokens[ $i ]['content'] );

				if ( strpos( $funcName, 'wp_send_json' ) === 0 || $funcName === 'wp_die' ) {
					return true;
				}
			}

			// Skip nested functions/closures.
			if ( $tokens[ $i ]['code'] === T_FUNCTION || $tokens[ $i ]['code'] === T_CLOSURE ) {
				if ( isset( $tokens[ $i ]['scope_closer'] ) ) {
					$i = $tokens[ $i ]['scope_closer'];
				}
			}
		}

		return false;
	}

	/**
	 * Check if docblock has @return tag.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $docStart   The docblock opener position.
	 * @param int  $docEnd     The docblock closer position.
	 *
	 * @return bool
	 */
	private function hasReturnTag( File $phpcsFile, $docStart, $docEnd ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $docStart; $i <= $docEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG && $tokens[ $i ]['content'] === '@return' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Find the position to insert @return void.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $docStart   The docblock opener position.
	 * @param int  $docEnd     The docblock closer position.
	 *
	 * @return false|int The position to insert after, or false if not found.
	 */
	private function findInsertPosition( File $phpcsFile, $docStart, $docEnd ) {
		$tokens = $phpcsFile->getTokens();

		// Find the last content before the closing tag.
		$lastContent = $phpcsFile->findPrevious(
			array( T_DOC_COMMENT_WHITESPACE, T_DOC_COMMENT_CLOSE_TAG ),
			$docEnd - 1,
			$docStart,
			true
		);

		if ( false !== $lastContent ) {
			return $lastContent;
		}

		return false;
	}

	/**
	 * Apply the fix.
	 *
	 * @param File $phpcsFile      The file being scanned.
	 * @param int  $insertPosition The position to insert after.
	 * @param int  $docEnd         The docblock closer position.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $insertPosition, $docEnd ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		// Get the indentation from the docblock.
		$indent = $this->getDocblockIndent( $phpcsFile, $docEnd );

		$fixer->beginChangeset();

		// Add @return void before the closing tag.
		$fixer->addContent(
			$insertPosition,
			$phpcsFile->eolChar . $indent . ' *' . $phpcsFile->eolChar . $indent . ' * @return void'
		);

		$fixer->endChangeset();
	}

	/**
	 * Get the indentation of the docblock.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $docEnd    The docblock closer position.
	 *
	 * @return string The indentation string.
	 */
	private function getDocblockIndent( File $phpcsFile, $docEnd ) {
		$tokens = $phpcsFile->getTokens();

		// Find the start of the closing line.
		$lineStart = $docEnd;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $docEnd ]['line'] ) {
			--$lineStart;
		}

		if ( $tokens[ $lineStart ]['code'] === T_DOC_COMMENT_WHITESPACE ) {
			return $tokens[ $lineStart ]['content'];
		}

		return "\t";
	}
}
