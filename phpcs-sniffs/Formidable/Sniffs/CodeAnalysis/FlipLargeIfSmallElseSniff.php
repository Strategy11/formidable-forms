<?php
/**
 * Formidable_Sniffs_CodeAnalysis_FlipLargeIfSmallElseSniff
 *
 * Detects if/else where the if is large, the else is small, and only a return follows.
 * Suggests negating the condition and returning early from the else to reduce indentation.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects large if + small else + trailing return pattern.
 *
 * Bad:
 * if ($condition) {
 *     // 5+ lines of code
 *     $result = do_something();
 * } else {
 *     // 1-2 lines
 *     $result = default_value();
 * }
 * return $result;
 *
 * Good:
 * if (! $condition) {
 *     // 1-2 lines
 *     return default_value();
 * }
 * // 5+ lines of code (now dedented)
 * $result = do_something();
 * return $result;
 */
class FlipLargeIfSmallElseSniff implements Sniff {

	/**
	 * Minimum number of lines inside the if body to consider it "large".
	 *
	 * @var int
	 */
	public $minIfLines = 5;

	/**
	 * Maximum number of lines inside the else body to consider it "small".
	 *
	 * @var int
	 */
	public $maxElseLines = 3;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_IF );
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

		// Must have scope opener/closer.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$ifOpener = $tokens[ $stackPtr ]['scope_opener'];
		$ifCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Check if there's an else after the if.
		$elseToken = $phpcsFile->findNext( T_WHITESPACE, $ifCloser + 1, null, true );

		if ( false === $elseToken || $tokens[ $elseToken ]['code'] !== T_ELSE ) {
			return;
		}

		// Skip if there's an elseif (we only handle simple if/else).
		$afterIfCloser = $phpcsFile->findNext( T_WHITESPACE, $ifCloser + 1, null, true );

		if ( false !== $afterIfCloser && $tokens[ $afterIfCloser ]['code'] === T_ELSEIF ) {
			return;
		}

		// Get the else's scope opener and closer.
		if ( ! isset( $tokens[ $elseToken ]['scope_opener'] ) || ! isset( $tokens[ $elseToken ]['scope_closer'] ) ) {
			return;
		}

		$elseOpener = $tokens[ $elseToken ]['scope_opener'];
		$elseCloser = $tokens[ $elseToken ]['scope_closer'];

		// Check what comes after the else block.
		$afterElse = $phpcsFile->findNext( T_WHITESPACE, $elseCloser + 1, null, true );

		if ( false === $afterElse ) {
			return;
		}

		// Must be a return statement after the if/else.
		if ( $tokens[ $afterElse ]['code'] !== T_RETURN ) {
			return;
		}

		// Find the semicolon ending the return statement.
		$returnSemicolon = $phpcsFile->findNext( T_SEMICOLON, $afterElse + 1 );

		if ( false === $returnSemicolon ) {
			return;
		}

		// Check that after the return statement, there's only whitespace/comments until end of scope or file.
		$afterReturn = $phpcsFile->findNext(
			array( T_WHITESPACE, T_COMMENT ),
			$returnSemicolon + 1,
			null,
			true
		);

		// The return should be the last statement before a closing brace (function/method end).
		if ( false !== $afterReturn && $tokens[ $afterReturn ]['code'] !== T_CLOSE_CURLY_BRACKET ) {
			return;
		}

		// Count lines in if and else blocks.
		$ifLineCount   = $this->countLinesInScope( $phpcsFile, $ifOpener, $ifCloser );
		$elseLineCount = $this->countLinesInScope( $phpcsFile, $elseOpener, $elseCloser );

		// Check if if is large and else is small.
		if ( $ifLineCount < $this->minIfLines || $elseLineCount > $this->maxElseLines ) {
			return;
		}

		// Don't trigger if the else is larger than or equal to the if.
		if ( $elseLineCount >= $ifLineCount ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Large if (%d lines) with small else (%d lines) followed by return. Consider negating the condition and returning early to reduce indentation.',
			$stackPtr,
			'Found',
			array( $ifLineCount, $elseLineCount )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $elseToken, $afterElse, $returnSemicolon );
		}
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
	 * Apply the fix.
	 *
	 * @param File $phpcsFile       The file being scanned.
	 * @param int  $ifToken         The if token position.
	 * @param int  $elseToken       The else token position.
	 * @param int  $returnToken     The return token position.
	 * @param int  $returnSemicolon The return semicolon position.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $ifToken, $elseToken, $returnToken, $returnSemicolon ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$ifOpener        = $tokens[ $ifToken ]['scope_opener'];
		$ifCloser        = $tokens[ $ifToken ]['scope_closer'];
		$elseOpener      = $tokens[ $elseToken ]['scope_opener'];
		$elseCloser      = $tokens[ $elseToken ]['scope_closer'];
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

		// Get the return statement.
		$returnStatement = '';

		for ( $i = $returnToken; $i <= $returnSemicolon; $i++ ) {
			$returnStatement .= $tokens[ $i ]['content'];
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

		// Replace the if body with the else body + return.
		$newIfBody = rtrim( $elseBodyContent ) . $phpcsFile->eolChar . $ifIndent . "\t" . $returnStatement;

		for ( $i = $ifOpener + 1; $i < $ifCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
		$fixer->addContent( $ifOpener, $phpcsFile->eolChar . $newIfBody . $phpcsFile->eolChar . $ifIndent );

		// Remove the else keyword, braces, and content.
		for ( $i = $ifCloser + 1; $i <= $elseCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Remove the trailing return statement (we'll add it back at the end).
		for ( $i = $elseCloser + 1; $i <= $returnSemicolon; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Add the dedented if body after the if's closing brace, followed by the return statement.
		$ifBodyContent = rtrim( $ifBodyContent );
		$fixer->addContent(
			$ifCloser,
			$phpcsFile->eolChar . $phpcsFile->eolChar .
			$ifIndent . ltrim( $ifBodyContent ) . $phpcsFile->eolChar .
			$phpcsFile->eolChar .
			$ifIndent . $returnStatement . $phpcsFile->eolChar
		);

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

		// Check if this is a compound condition (has && or || at top level).
		$isCompound = $this->hasTopLevelOperator( $condition );

		// Only remove leading ! if it's a simple condition (not compound).
		if ( ! $isCompound ) {
			// If condition starts with !, remove it.
			if ( strpos( $condition, '! ' ) === 0 ) {
				return substr( $condition, 2 );
			}

			if ( strpos( $condition, '!' ) === 0 && strpos( $condition, '!=' ) !== 0 ) {
				return substr( $condition, 1 );
			}

			// Try to flip comparison operators.
			$flipped = $this->flipComparisonOperator( $condition );

			if ( $flipped !== false ) {
				return $flipped;
			}

			// Simple condition, just add !
			return '! ' . $condition;
		}

		// For compound conditions, wrap in parentheses and negate.
		return '! ( ' . $condition . ' )';
	}

	/**
	 * Check if a condition has && or || at the top level (not inside parentheses).
	 *
	 * @param string $condition The condition to check.
	 *
	 * @return bool
	 */
	private function hasTopLevelOperator( $condition ) {
		$parenDepth = 0;
		$len        = strlen( $condition );

		for ( $i = 0; $i < $len; $i++ ) {
			$char = $condition[ $i ];

			if ( $char === '(' ) {
				++$parenDepth;
				continue;
			}

			if ( $char === ')' ) {
				--$parenDepth;
				continue;
			}

			// Only check at top level.
			if ( $parenDepth !== 0 ) {
				continue;
			}

			// Check for && or ||.
			if ( $i < $len - 1 ) {
				$twoChars = $condition[ $i ] . $condition[ $i + 1 ];

				if ( $twoChars === '&&' || $twoChars === '||' ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Try to flip a comparison operator in a condition.
	 *
	 * @param string $condition The condition to flip.
	 *
	 * @return false|string The flipped condition, or false if not a simple comparison.
	 */
	private function flipComparisonOperator( $condition ) {
		// Map of operators to their opposites.
		$operatorMap = array(
			'!==' => '===',
			'===' => '!==',
			'!='  => '==',
			'=='  => '!=',
			'>='  => '<',
			'<='  => '>',
			'>'   => '<=',
			'<'   => '>=',
		);

		// Check for each operator (check longer ones first).
		foreach ( $operatorMap as $op => $opposite ) {
			$pos = strpos( $condition, $op );

			if ( $pos !== false ) {
				// Make sure this is a simple comparison (no && or ||).
				if ( strpos( $condition, '&&' ) !== false || strpos( $condition, '||' ) !== false ) {
					return false;
				}

				return substr( $condition, 0, $pos ) . $opposite . substr( $condition, $pos + strlen( $op ) );
			}
		}

		return false;
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
