<?php
/**
 * Formidable_Sniffs_CodeAnalysis_FlipIfElseToEarlyReturnSniff
 *
 * Detects void functions where the entire body is an if/else and the else is large.
 * Adds return at end of if block and removes the else wrapper.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Converts if/else to early return in void functions.
 *
 * Bad:
 * function example($value) {
 *     if ($condition) {
 *         // short code
 *     } else {
 *         // 5+ lines of code
 *     }
 * }
 *
 * Good:
 * function example($value) {
 *     if ($condition) {
 *         // short code
 *         return;
 *     }
 *     // 5+ lines of code
 * }
 */
class FlipIfElseToEarlyReturnSniff implements Sniff {

	/**
	 * Minimum number of lines inside the else body to trigger this sniff.
	 *
	 * @var int
	 */
	public $minElseLines = 5;

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

		// Check if function returns void.
		if ( ! $this->isVoidFunction( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Get the function's scope opener and closer.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$funcOpener = $tokens[ $stackPtr ]['scope_opener'];
		$funcCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find the first non-whitespace, non-comment token inside the function.
		$skipTokens = array(
			T_WHITESPACE,
			T_COMMENT,
			T_DOC_COMMENT,
			T_DOC_COMMENT_OPEN_TAG,
			T_DOC_COMMENT_CLOSE_TAG,
			T_DOC_COMMENT_STAR,
			T_DOC_COMMENT_WHITESPACE,
			T_DOC_COMMENT_STRING,
			T_DOC_COMMENT_TAG,
		);

		$firstStatement = $funcOpener + 1;

		while ( $firstStatement < $funcCloser && in_array( $tokens[ $firstStatement ]['code'], $skipTokens, true ) ) {
			++$firstStatement;
		}

		if ( $firstStatement >= $funcCloser ) {
			return;
		}

		// Check if the first statement is an if.
		if ( $tokens[ $firstStatement ]['code'] !== T_IF ) {
			return;
		}

		$ifToken = $firstStatement;

		// Get the if's scope closer.
		if ( ! isset( $tokens[ $ifToken ]['scope_closer'] ) ) {
			return;
		}

		$ifCloser = $tokens[ $ifToken ]['scope_closer'];

		// Check if there's an else after the if.
		$elseToken = $phpcsFile->findNext( T_WHITESPACE, $ifCloser + 1, null, true );

		if ( false === $elseToken || $tokens[ $elseToken ]['code'] !== T_ELSE ) {
			return;
		}

		// Get the else's scope opener and closer.
		if ( ! isset( $tokens[ $elseToken ]['scope_opener'] ) || ! isset( $tokens[ $elseToken ]['scope_closer'] ) ) {
			return;
		}

		$elseOpener = $tokens[ $elseToken ]['scope_opener'];
		$elseCloser = $tokens[ $elseToken ]['scope_closer'];

		// Check if the else is the last thing in the function (ignoring comments).
		$skipTokens = array(
			T_WHITESPACE,
			T_COMMENT,
		);
		$afterElse = $elseCloser + 1;

		while ( $afterElse < $funcCloser && in_array( $tokens[ $afterElse ]['code'], $skipTokens, true ) ) {
			++$afterElse;
		}

		if ( $afterElse !== $funcCloser ) {
			return;
		}

		// Count lines in the else block.
		$elseLineCount = $this->countLinesInScope( $phpcsFile, $elseOpener, $elseCloser );

		if ( $elseLineCount < $this->minElseLines ) {
			return;
		}

		// Check that the if block doesn't already end with return.
		if ( $this->blockEndsWithReturn( $phpcsFile, $ifToken ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Consider using early return pattern. Add return at end of if block and remove else wrapper.',
			$elseToken,
			'Found'
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $ifToken, $elseToken, $elseOpener, $elseCloser, $funcCloser );
		}
	}

	/**
	 * Check if a function is void (has @return void or no return with value).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 *
	 * @return bool
	 */
	private function isVoidFunction( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Check for @return void in docblock.
		$docComment = $phpcsFile->findPrevious( T_DOC_COMMENT_CLOSE_TAG, $stackPtr - 1 );

		if ( false !== $docComment ) {
			$docStart = $tokens[ $docComment ]['comment_opener'];

			for ( $i = $docStart; $i <= $docComment; $i++ ) {
				if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG && $tokens[ $i ]['content'] === '@return' ) {
					// Check the next string token for 'void'.
					$returnType = $phpcsFile->findNext( T_DOC_COMMENT_STRING, $i + 1, $docComment );

					if ( false !== $returnType ) {
						$typeContent = strtolower( trim( $tokens[ $returnType ]['content'] ) );

						if ( strpos( $typeContent, 'void' ) === 0 ) {
							return true;
						}

						// If there's a return type and it's not void, it's not void.
						return false;
					}
				}
			}
		}

		// If no docblock or no @return tag, check if function has any return with value.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return false;
		}

		$funcOpener = $tokens[ $stackPtr ]['scope_opener'];
		$funcCloser = $tokens[ $stackPtr ]['scope_closer'];

		for ( $i = $funcOpener + 1; $i < $funcCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_RETURN ) {
				// Check if there's a value after return.
				$next = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

				if ( false !== $next && $tokens[ $next ]['code'] !== T_SEMICOLON ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Count the number of lines inside a scope.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $scopeOpener The scope opener position.
	 * @param int  $scopeCloser The scope closer position.
	 *
	 * @return int
	 */
	private function countLinesInScope( File $phpcsFile, $scopeOpener, $scopeCloser ) {
		$tokens = $phpcsFile->getTokens();

		$startLine = $tokens[ $scopeOpener ]['line'];
		$endLine   = $tokens[ $scopeCloser ]['line'];

		// Subtract 2 for the opening and closing brace lines.
		return max( 0, $endLine - $startLine - 1 );
	}

	/**
	 * Check if a block ends with a return statement.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $ifToken   The if token position.
	 *
	 * @return bool
	 */
	private function blockEndsWithReturn( File $phpcsFile, $ifToken ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $ifToken ]['scope_closer'] ) ) {
			return false;
		}

		$scopeCloser = $tokens[ $ifToken ]['scope_closer'];

		// Find the last non-whitespace token before the closing brace.
		$lastToken = $phpcsFile->findPrevious( T_WHITESPACE, $scopeCloser - 1, null, true );

		if ( false === $lastToken ) {
			return false;
		}

		// Check if it's a semicolon after a return.
		if ( $tokens[ $lastToken ]['code'] === T_SEMICOLON ) {
			$beforeSemi = $phpcsFile->findPrevious( T_WHITESPACE, $lastToken - 1, null, true );

			if ( false !== $beforeSemi && $tokens[ $beforeSemi ]['code'] === T_RETURN ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Apply the fix.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $ifToken     The if token position.
	 * @param int  $elseToken   The else token position.
	 * @param int  $elseOpener  The else scope opener position.
	 * @param int  $elseCloser  The else scope closer position.
	 * @param int  $funcCloser  The function scope closer position.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $ifToken, $elseToken, $elseOpener, $elseCloser, $funcCloser ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$ifCloser = $tokens[ $ifToken ]['scope_closer'];
		$ifOpener = $tokens[ $ifToken ]['scope_opener'];

		// Get the indentation of the if statement (for the else body after dedent).
		$ifIndent = $this->getIndentation( $phpcsFile, $ifToken );

		// Find the first actual code token inside the if block to get proper indentation.
		$firstInIf = $phpcsFile->findNext(
			array( T_WHITESPACE, T_COMMENT ),
			$ifOpener + 1,
			$ifCloser,
			true
		);
		$innerIndent = $ifIndent . "\t";

		if ( false !== $firstInIf ) {
			$innerIndent = $this->getIndentation( $phpcsFile, $firstInIf );
		}

		// Get the else body content.
		$elseBodyContent = '';

		for ( $i = $elseOpener + 1; $i < $elseCloser; $i++ ) {
			$elseBodyContent .= $tokens[ $i ]['content'];
		}

		// Dedent the else body by one level.
		$elseBodyContent = $this->dedentCode( $elseBodyContent );

		$fixer->beginChangeset();

		// Find the last non-whitespace, non-comment token before the if's closing brace.
		$lastInIf = $phpcsFile->findPrevious(
			array( T_WHITESPACE, T_COMMENT ),
			$ifCloser - 1,
			$ifOpener,
			true
		);

		// Add return; before the if's closing brace.
		if ( false !== $lastInIf ) {
			$fixer->addContent( $lastInIf, $phpcsFile->eolChar . $phpcsFile->eolChar . $innerIndent . 'return;' );
		}

		// Remove everything from after the if's closing brace to the function's closing brace.
		// This includes the else keyword, else body, else closing brace, and any trailing comments.
		for ( $i = $ifCloser + 1; $i < $funcCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Add the dedented else body after the if's closing brace.
		$fixer->addContent( $ifCloser, $phpcsFile->eolChar . $phpcsFile->eolChar . $ifIndent . ltrim( $elseBodyContent ) );

		$fixer->endChangeset();
	}

	/**
	 * Get the indentation of a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The token position.
	 *
	 * @return string The indentation string.
	 */
	private function getIndentation( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the first token on this line.
		$lineStart = $stackPtr;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			--$lineStart;
		}

		// If the first token is whitespace, that's our indentation.
		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			return $tokens[ $lineStart ]['content'];
		}

		return '';
	}

	/**
	 * Remove one level of indentation from code.
	 *
	 * @param string $code The code to dedent.
	 *
	 * @return string The dedented code.
	 */
	private function dedentCode( $code ) {
		$lines  = explode( "\n", $code );
		$result = array();

		foreach ( $lines as $line ) {
			// Remove one tab or 4 spaces from the beginning.
			if ( strpos( $line, "\t" ) === 0 ) {
				$line = substr( $line, 1 );
			} elseif ( strpos( $line, '    ' ) === 0 ) {
				$line = substr( $line, 4 );
			}
			$result[] = $line;
		}

		return implode( "\n", $result );
	}
}
