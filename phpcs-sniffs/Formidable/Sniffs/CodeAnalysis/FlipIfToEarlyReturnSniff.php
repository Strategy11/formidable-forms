<?php
/**
 * Formidable_Sniffs_CodeAnalysis_FlipIfToEarlyReturnSniff
 *
 * Detects functions where the entire body is inside a single if statement
 * and suggests flipping to an early return pattern.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects functions that should use early return pattern.
 *
 * Bad:
 * function example($value) {
 *     if ($value) {
 *         // 5+ lines of code
 *         return $result;
 *     }
 * }
 *
 * Good:
 * function example($value) {
 *     if (! $value) {
 *         return;
 *     }
 *     // 5+ lines of code
 *     return $result;
 * }
 */
class FlipIfToEarlyReturnSniff implements Sniff {

	/**
	 * Minimum number of statements inside the if body to trigger this sniff.
	 *
	 * @var int
	 */
	public $minStatements = 5;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FUNCTION, T_CLOSURE );
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

		// Skip closures.
		if ( $tokens[ $stackPtr ]['code'] === T_CLOSURE ) {
			return;
		}

		// Get the function's scope opener and closer.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find the first non-whitespace token inside the function.
		$firstStatement = $phpcsFile->findNext(
			T_WHITESPACE,
			$scopeOpener + 1,
			$scopeCloser,
			true
		);

		if ( false === $firstStatement ) {
			return;
		}

		// Check if the first statement is an if.
		if ( $tokens[ $firstStatement ]['code'] !== T_IF ) {
			return;
		}

		$ifToken = $firstStatement;

		// Check if this if has an else or elseif - skip those.
		if ( isset( $tokens[ $ifToken ]['scope_closer'] ) ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $tokens[ $ifToken ]['scope_closer'] + 1, null, true );
			if ( false !== $next && in_array( $tokens[ $next ]['code'], array( T_ELSE, T_ELSEIF ), true ) ) {
				return;
			}
		}

		// Get the if's scope closer.
		if ( ! isset( $tokens[ $ifToken ]['scope_closer'] ) ) {
			return;
		}

		$ifScopeCloser = $tokens[ $ifToken ]['scope_closer'];

		// Check if the if statement is the only thing in the function body.
		// The next non-whitespace token after the if's closing brace should be the function's closing brace.
		$afterIf = $phpcsFile->findNext(
			T_WHITESPACE,
			$ifScopeCloser + 1,
			null,
			true
		);

		// If the next token after the if isn't the function's closing brace, there's more code.
		if ( false === $afterIf || $afterIf !== $scopeCloser ) {
			return;
		}

		// Count statements inside the if body.
		$statementCount = $this->countStatementsInScope( $phpcsFile, $ifToken );

		if ( $statementCount < $this->minStatements ) {
			return;
		}

		// Get the condition for the error message.
		$condition = $this->getConditionString( $phpcsFile, $ifToken );

		$fix = $phpcsFile->addFixableError(
			'Consider using early return pattern. Flip the condition "if (%s)" to "if (! %s) { return; }" and move the body outside.',
			$ifToken,
			'Found',
			array( $condition, $condition )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $ifToken, $scopeOpener, $scopeCloser );
		}
	}

	/**
	 * Apply the fix to flip the if statement to early return.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $ifToken     The position of the if token.
	 * @param int  $funcOpener  The function's scope opener.
	 * @param int  $funcCloser  The function's scope closer.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $ifToken, $funcOpener, $funcCloser ) {
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

		// Get the if body content (without the braces), and dedent by one level.
		$ifBodyContent = '';
		for ( $i = $ifOpener + 1; $i < $ifCloser; $i++ ) {
			$ifBodyContent .= $tokens[ $i ]['content'];
		}

		// Remove one level of indentation from the body.
		$ifBodyContent = $this->dedentCode( $ifBodyContent );

		// Determine the indentation.
		$indent = $this->getIndentation( $phpcsFile, $ifToken );

		$fixer->beginChangeset();

		// Remove everything from the if token to the if's closing brace.
		for ( $i = $ifToken; $i <= $ifCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Build the new code: early return + body.
		$newCode  = 'if ( ' . $negatedCondition . ' ) {' . $phpcsFile->eolChar;
		$newCode .= $indent . "\treturn;" . $phpcsFile->eolChar;
		$newCode .= $indent . '}' . $phpcsFile->eolChar;
		$newCode .= $phpcsFile->eolChar;
		$newCode .= $indent . ltrim( $ifBodyContent );

		// Insert the new code at the if token position.
		$fixer->addContent( $ifToken, $newCode );

		$fixer->endChangeset();
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
		if ( strpos( $condition, '!' ) === 0 ) {
			return substr( $condition, 1 );
		}

		// Otherwise, add negation.
		// If condition is simple (no spaces or operators at top level), just add !
		// If complex, wrap in parentheses.
		if ( $this->isSimpleCondition( $condition ) ) {
			return '! ' . $condition;
		}

		return '! ( ' . $condition . ' )';
	}

	/**
	 * Check if a condition is simple (single variable/function call).
	 *
	 * @param string $condition The condition to check.
	 *
	 * @return bool
	 */
	private function isSimpleCondition( $condition ) {
		// Simple conditions: $var, function(), $obj->method(), etc.
		// Complex conditions contain: &&, ||, and, or, comparisons.
		$complexPatterns = array( '&&', '||', ' and ', ' or ', '===', '!==', '==', '!=', '<=', '>=', '<', '>' );

		foreach ( $complexPatterns as $pattern ) {
			if ( strpos( $condition, $pattern ) !== false ) {
				return false;
			}
		}

		return true;
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
	 * Check if an if statement has an else or elseif clause.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $ifToken   The position of the if token.
	 *
	 * @return bool
	 */
	private function hasElseOrElseif( File $phpcsFile, $ifToken ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $ifToken ]['scope_closer'] ) ) {
			return false;
		}

		$scopeCloser = $tokens[ $ifToken ]['scope_closer'];

		// Look for else or elseif after the if's closing brace.
		$next = $phpcsFile->findNext( T_WHITESPACE, $scopeCloser + 1, null, true );

		if ( false === $next ) {
			return false;
		}

		return in_array( $tokens[ $next ]['code'], array( T_ELSE, T_ELSEIF ), true );
	}

	/**
	 * Count the number of statements inside a scope.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $scopeToken The token with the scope to count.
	 *
	 * @return int
	 */
	private function countStatementsInScope( File $phpcsFile, $scopeToken ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $scopeToken ]['scope_opener'] ) || ! isset( $tokens[ $scopeToken ]['scope_closer'] ) ) {
			return 0;
		}

		$scopeOpener = $tokens[ $scopeToken ]['scope_opener'];
		$scopeCloser = $tokens[ $scopeToken ]['scope_closer'];

		// The target level is the level inside the scope (opener level + 1).
		$targetLevel = $tokens[ $scopeOpener ]['level'] + 1;

		$count = 0;

		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			// Only count semicolons at the immediate scope level (not nested).
			if ( $tokens[ $i ]['code'] === T_SEMICOLON && $tokens[ $i ]['level'] === $targetLevel ) {
				++$count;
			}

			// Also count control structures as statements.
			if ( in_array( $tokens[ $i ]['code'], array( T_IF, T_FOREACH, T_FOR, T_WHILE, T_SWITCH, T_TRY ), true ) ) {
				if ( $tokens[ $i ]['level'] === $targetLevel ) {
					++$count;
					// Skip to the end of this control structure.
					if ( isset( $tokens[ $i ]['scope_closer'] ) ) {
						$i = $tokens[ $i ]['scope_closer'];
					}
				}
			}
		}

		return $count;
	}

	/**
	 * Get the condition string from an if statement.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $ifToken   The position of the if token.
	 *
	 * @return string
	 */
	private function getConditionString( File $phpcsFile, $ifToken ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $ifToken ]['parenthesis_opener'] ) || ! isset( $tokens[ $ifToken ]['parenthesis_closer'] ) ) {
			return '...';
		}

		$opener = $tokens[ $ifToken ]['parenthesis_opener'];
		$closer = $tokens[ $ifToken ]['parenthesis_closer'];

		$condition = '';

		for ( $i = $opener + 1; $i < $closer; $i++ ) {
			$condition .= $tokens[ $i ]['content'];
		}

		// Trim and truncate if too long.
		$condition = trim( $condition );

		if ( strlen( $condition ) > 50 ) {
			$condition = substr( $condition, 0, 47 ) . '...';
		}

		return $condition;
	}
}
