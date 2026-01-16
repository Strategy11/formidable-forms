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
 * Converts if/else to early return in functions.
 *
 * Case 1 - Void function, else is large:
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
 *
 * Case 2 - Else ends with return, if doesn't:
 * Bad:
 * function example($value) {
 *     $opt = 'create';
 *     if ($condition) {
 *         do_something();
 *     } else {
 *         // code that returns
 *         return $result;
 *     }
 * }
 *
 * Good:
 * function example($value) {
 *     $opt = 'create';
 *     if (! $condition) {
 *         // code that returns
 *         return $result;
 *     }
 *     do_something();
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

		// Get the function's scope opener and closer.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$funcOpener = $tokens[ $stackPtr ]['scope_opener'];
		$funcCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find an if statement that has an else as the last thing in the function.
		$ifToken = $this->findIfElseAtEndOfFunction( $phpcsFile, $funcOpener, $funcCloser );

		if ( false === $ifToken ) {
			return;
		}

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

		// Check if the else block ends with a return.
		$elseEndsWithReturn = $this->blockEndsWithReturn( $phpcsFile, $elseToken );
		$ifEndsWithReturn   = $this->blockEndsWithReturn( $phpcsFile, $ifToken );

		// Case 1: Void function where else is large - add return to if, unwrap else.
		// Case 2: Else ends with return, if doesn't - flip the condition.
		if ( $elseEndsWithReturn && ! $ifEndsWithReturn ) {
			// Case 2: Flip the if/else so else returns early.
			$elseLineCount = $this->countLinesInScope( $phpcsFile, $elseOpener, $elseCloser );

			if ( $elseLineCount < $this->minElseLines ) {
				return;
			}

			$fix = $phpcsFile->addFixableError(
				'Consider using early return pattern. Flip the condition so the else returns early.',
				$elseToken,
				'FlipElseReturn'
			);

			if ( true === $fix ) {
				$this->applyFlipFix( $phpcsFile, $ifToken, $elseToken, $elseOpener, $elseCloser );
			}

			return;
		}

		// Case 1: Void function, else is large.
		if ( ! $this->isVoidFunction( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Count lines in the else block.
		$elseLineCount = $this->countLinesInScope( $phpcsFile, $elseOpener, $elseCloser );

		if ( $elseLineCount < $this->minElseLines ) {
			return;
		}

		// Check that the if block doesn't already end with return.
		if ( $ifEndsWithReturn ) {
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
	 * Find an if statement with an else that is the last thing in the function.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $funcOpener  The function scope opener.
	 * @param int  $funcCloser  The function scope closer.
	 *
	 * @return int|false The if token position, or false if not found.
	 */
	private function findIfElseAtEndOfFunction( File $phpcsFile, $funcOpener, $funcCloser ) {
		$tokens = $phpcsFile->getTokens();

		// Search backwards from the function closer to find the last if/else.
		$current = $funcCloser - 1;

		// Skip whitespace and comments.
		while ( $current > $funcOpener && in_array( $tokens[ $current ]['code'], array( T_WHITESPACE, T_COMMENT ), true ) ) {
			--$current;
		}

		// Should be a closing brace.
		if ( $tokens[ $current ]['code'] !== T_CLOSE_CURLY_BRACKET ) {
			return false;
		}

		// Find what this brace belongs to.
		if ( ! isset( $tokens[ $current ]['scope_condition'] ) ) {
			return false;
		}

		$scopeCondition = $tokens[ $current ]['scope_condition'];

		// Must be an else.
		if ( $tokens[ $scopeCondition ]['code'] !== T_ELSE ) {
			return false;
		}

		// Find the if that this else belongs to.
		$elseToken = $scopeCondition;

		// Look backwards from the else to find the if's closing brace.
		$beforeElse = $phpcsFile->findPrevious( T_WHITESPACE, $elseToken - 1, null, true );

		if ( false === $beforeElse || $tokens[ $beforeElse ]['code'] !== T_CLOSE_CURLY_BRACKET ) {
			return false;
		}

		// Find the if token.
		if ( ! isset( $tokens[ $beforeElse ]['scope_condition'] ) ) {
			return false;
		}

		$ifToken = $tokens[ $beforeElse ]['scope_condition'];

		// Must be an if (not elseif).
		if ( $tokens[ $ifToken ]['code'] !== T_IF ) {
			return false;
		}

		return $ifToken;
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
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $blockToken The if or else token position.
	 *
	 * @return bool
	 */
	private function blockEndsWithReturn( File $phpcsFile, $blockToken ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $blockToken ]['scope_closer'] ) ) {
			return false;
		}

		$scopeCloser = $tokens[ $blockToken ]['scope_closer'];
		$scopeOpener = $tokens[ $blockToken ]['scope_opener'];

		// Find the last non-whitespace token before the closing brace.
		$lastToken = $phpcsFile->findPrevious( T_WHITESPACE, $scopeCloser - 1, $scopeOpener, true );

		if ( false === $lastToken ) {
			return false;
		}

		// Check if it's a semicolon.
		if ( $tokens[ $lastToken ]['code'] !== T_SEMICOLON ) {
			return false;
		}

		// Find the statement before the semicolon.
		$statementEnd = $lastToken - 1;

		// Skip whitespace.
		while ( $statementEnd > $scopeOpener && $tokens[ $statementEnd ]['code'] === T_WHITESPACE ) {
			--$statementEnd;
		}

		// Look for a return token on this statement's line or find the return keyword.
		$returnToken = $phpcsFile->findPrevious(
			T_RETURN,
			$lastToken - 1,
			$scopeOpener
		);

		if ( false === $returnToken ) {
			return false;
		}

		// Make sure the return is on the same statement (no semicolons between return and our semicolon).
		$semiCheck = $phpcsFile->findNext( T_SEMICOLON, $returnToken + 1, $lastToken );

		return false === $semiCheck;
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

		// Clear tokens between last statement and closing brace, then add return with proper formatting.
		for ( $i = $lastInIf + 1; $i < $ifCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Add return; after the last statement, with newline so brace is on its own line.
		if ( false !== $lastInIf ) {
			$fixer->addContent( $lastInIf, $phpcsFile->eolChar . $phpcsFile->eolChar . $innerIndent . 'return;' . $phpcsFile->eolChar . $ifIndent );
		}

		// Remove everything from after the if's closing brace to the function's closing brace.
		// This includes the else keyword, else body, else closing brace, and any trailing comments.
		for ( $i = $ifCloser + 1; $i < $funcCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Add the dedented else body after the if's closing brace.
		// Get function indentation for the closing brace (one level less than if).
		$funcIndent = $this->getIndentation( $phpcsFile, $funcCloser );
		$fixer->addContent( $ifCloser, $phpcsFile->eolChar . $phpcsFile->eolChar . $ifIndent . ltrim( rtrim( $elseBodyContent ) ) . $phpcsFile->eolChar . $funcIndent );

		$fixer->endChangeset();
	}

	/**
	 * Apply the flip fix - negate condition, swap if/else bodies.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $ifToken     The if token position.
	 * @param int  $elseToken   The else token position.
	 * @param int  $elseOpener  The else scope opener position.
	 * @param int  $elseCloser  The else scope closer position.
	 *
	 * @return void
	 */
	private function applyFlipFix( File $phpcsFile, $ifToken, $elseToken, $elseOpener, $elseCloser ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$ifOpener        = $tokens[ $ifToken ]['scope_opener'];
		$ifCloser        = $tokens[ $ifToken ]['scope_closer'];
		$conditionOpener = $tokens[ $ifToken ]['parenthesis_opener'];
		$conditionCloser = $tokens[ $ifToken ]['parenthesis_closer'];

		// Get the original condition.
		$originalCondition = '';

		for ( $i = $conditionOpener + 1; $i < $conditionCloser; $i++ ) {
			$originalCondition .= $tokens[ $i ]['content'];
		}
		$originalCondition = trim( $originalCondition );

		// Negate the condition.
		$negatedCondition = $this->negateCondition( $originalCondition );

		// Get the if body content.
		$ifBodyContent = '';

		for ( $i = $ifOpener + 1; $i < $ifCloser; $i++ ) {
			$ifBodyContent .= $tokens[ $i ]['content'];
		}

		// Get the else body content.
		$elseBodyContent = '';

		for ( $i = $elseOpener + 1; $i < $elseCloser; $i++ ) {
			$elseBodyContent .= $tokens[ $i ]['content'];
		}

		// Dedent the if body (it will become the code after the early return).
		$ifBodyContent = $this->dedentCode( $ifBodyContent );

		// Get the indentation.
		$ifIndent = $this->getIndentation( $phpcsFile, $ifToken );

		$fixer->beginChangeset();

		// Replace the condition.
		for ( $i = $conditionOpener + 1; $i < $conditionCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
		$fixer->addContent( $conditionOpener, ' ' . $negatedCondition . ' ' );

		// Replace the if body with the else body.
		// We need to ensure the closing brace ends up on its own line.
		$elseBodyContent = rtrim( $elseBodyContent );
		for ( $i = $ifOpener + 1; $i < $ifCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
		$fixer->addContent( $ifOpener, $phpcsFile->eolChar . $elseBodyContent . $phpcsFile->eolChar . $ifIndent );

		// Remove the else keyword and braces, add the dedented if body.
		for ( $i = $ifCloser + 1; $i <= $elseCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Add the dedented if body after the if's closing brace.
		$ifBodyContent = rtrim( $ifBodyContent );
		$fixer->addContent( $ifCloser, $phpcsFile->eolChar . $phpcsFile->eolChar . $ifIndent . ltrim( $ifBodyContent ) . $phpcsFile->eolChar . $ifIndent );

		$fixer->endChangeset();
	}

	/**
	 * Negate a condition string.
	 *
	 * @param string $condition The original condition.
	 *
	 * @return string The negated condition.
	 */
	private function negateCondition( $condition ) {
		$condition = trim( $condition );

		// If condition starts with !, remove it.
		if ( strpos( $condition, '! ' ) === 0 ) {
			return substr( $condition, 2 );
		}

		if ( strpos( $condition, '!' ) === 0 && strpos( $condition, '!=' ) !== 0 ) {
			return substr( $condition, 1 );
		}

		// For complex conditions, wrap in parentheses and negate.
		return '! ( ' . $condition . ' )';
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
