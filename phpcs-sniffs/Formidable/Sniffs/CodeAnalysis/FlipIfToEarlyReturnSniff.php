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
 * Also catches:
 * function example($value) {
 *     $var = get_value();
 *     if ($var && $condition) {
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
		$firstStatement = $this->findNextSignificantToken( $phpcsFile, $scopeOpener + 1, $scopeCloser );

		if ( false === $firstStatement ) {
			return;
		}

		$firstStatement = $this->skipGuardClauses( $phpcsFile, $firstStatement, $scopeCloser );

		if ( false === $firstStatement ) {
			return;
		}

		// Check if the first statement is an if.
		if ( $tokens[ $firstStatement ]['code'] !== T_IF ) {
			// If the first statement isn't an if, check if it's a simple assignment
			// followed by an if statement (pattern: $var = value; if ($var && condition) {...})
			$ifToken = $this->findIfAfterSimpleSetup( $phpcsFile, $firstStatement, $scopeCloser );

			if ( false === $ifToken ) {
				return;
			}
		} else {
			$ifToken = $firstStatement;
		}

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

		// Check if the if statement is the main logic in the function body.
		// The next non-whitespace/non-comment token after the if's closing brace should be the function's closing brace.
		$afterIf = $this->findNextSignificantToken( $phpcsFile, $ifScopeCloser + 1, null );

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
		// Map of operators to their opposites (check longer ones first).
		$operatorMap = array(
			'!==' => '===',
			'===' => '!==',
			'!='  => '==',
			'=='  => '!=',
			'>='  => '<',
			'<='  => '>',
		);

		// Make sure this is a simple comparison (no && or ||).
		if ( strpos( $condition, '&&' ) !== false || strpos( $condition, '||' ) !== false ) {
			return false;
		}

		// Check for each operator (check longer ones first).
		foreach ( $operatorMap as $op => $opposite ) {
			$pos = strpos( $condition, $op );

			if ( false !== $pos ) {
				return substr( $condition, 0, $pos ) . $opposite . substr( $condition, $pos + strlen( $op ) );
			}
		}

		// Handle single < and > separately to avoid matching -> or =>.
		$pos = $this->findComparisonOperator( $condition, '>' );

		if ( false !== $pos ) {
			return substr( $condition, 0, $pos ) . '<=' . substr( $condition, $pos + 1 );
		}

		$pos = $this->findComparisonOperator( $condition, '<' );

		if ( false !== $pos ) {
			return substr( $condition, 0, $pos ) . '>=' . substr( $condition, $pos + 1 );
		}

		return false;
	}

	/**
	 * Find a single comparison operator (< or >) that is not part of -> or =>.
	 *
	 * @param string $condition The condition string.
	 * @param string $operator  The operator to find (< or >).
	 *
	 * @return false|int The position of the operator, or false if not found.
	 */
	private function findComparisonOperator( $condition, $operator ) {
		$pos = 0;

		while ( ( $pos = strpos( $condition, $operator, $pos ) ) !== false ) {
			// Check character before to avoid matching -> or =>.
			if ( $pos > 0 ) {
				$charBefore = $condition[ $pos - 1 ];

				// Skip if this is part of ->, =>, <=, >=, or a multi-char comparison.
				if ( $charBefore === '-' || $charBefore === '=' || $charBefore === '<' || $charBefore === '>' || $charBefore === '!' ) {
					++$pos;
					continue;
				}
			}

			// Check character after to avoid matching <=, >=, =>.
			if ( $pos < strlen( $condition ) - 1 ) {
				$charAfter = $condition[ $pos + 1 ];

				if ( $charAfter === '=' || $charAfter === '>' ) {
					++$pos;
					continue;
				}
			}

			return $pos;
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
	 * Count the number of statements inside a scope (recursively).
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

		$count             = 0;
		$structureTokens   = array(
			T_IF,
			T_ELSE,
			T_ELSEIF,
			T_SWITCH,
			T_CASE,
			T_DEFAULT,
			T_FOR,
			T_FOREACH,
			T_WHILE,
			T_DO,
			T_TRY,
			T_CATCH,
			T_FINALLY,
			T_RETURN,
			T_THROW,
			T_BREAK,
			T_CONTINUE,
			T_EXIT,
			T_ECHO,
			T_PRINT,
			T_GLOBAL,
			T_STATIC,
			T_UNSET,
		);

		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( T_SEMICOLON === $code ) {
				++$count;
				continue;
			}

			if ( in_array( $code, $structureTokens, true ) ) {
				++$count;

				if ( isset( $tokens[ $i ]['scope_closer'] ) ) {
					$count += $this->countStatementsInScope( $phpcsFile, $i );
					$i      = $tokens[ $i ]['scope_closer'];
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

	/**
	 * Find an if statement that comes after simple setup code.
	 *
	 * @param File $phpcsFile     The file being scanned.
	 * @param int  $firstToken    The first token in the function.
	 * @param int  $scopeCloser   The function's scope closer.
	 *
	 * @return false|int Position of the if token, or false if not found.
	 */
	private function findIfAfterSimpleSetup( File $phpcsFile, $firstToken, $scopeCloser ) {
		$tokens = $phpcsFile->getTokens();

		// Allow simple assignments: $var = value;
		$current = $firstToken;
		$setupStatements = 0;
		$maxSetupStatements = 3; // Allow up to 3 simple setup statements

		while ( $current < $scopeCloser && $setupStatements < $maxSetupStatements ) {
			// Skip whitespace
			if ( $tokens[ $current ]['code'] === T_WHITESPACE ) {
				$current++;
				continue;
			}

			// Check if this is a simple assignment
			if ( $this->isSimpleAssignment( $phpcsFile, $current ) ) {
				$setupStatements++;
				// Move to the end of this statement (semicolon)
				$semicolon = $phpcsFile->findNext( T_SEMICOLON, $current + 1, $scopeCloser );

				if ( false === $semicolon ) {
					return false;
				}
				$current = $semicolon + 1;
				continue;
			}

			// If we find an if after the setup, return it
			if ( $tokens[ $current ]['code'] === T_IF ) {
				return $current;
			}

			// Anything else means this pattern doesn't match
			return false;
		}

		return false;
	}

	/**
	 * Check if a token represents a simple assignment statement.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the token to check.
	 *
	 * @return bool True if this is a simple assignment.
	 */
	private function isSimpleAssignment( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Look for pattern: $variable = value;
		if ( $tokens[ $stackPtr ]['code'] !== T_VARIABLE ) {
			return false;
		}

		// Next non-whitespace should be =
		$next = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $next || $tokens[ $next ]['code'] !== T_EQUAL ) {
			return false;
		}

		// Find the semicolon to confirm this is a complete assignment
		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $next + 1, null );
		return false !== $semicolon;
	}

	/**
	 * Skip over leading guard clauses (returns/throws) before analyzing the primary if.
	 *
	 * @param File $phpcsFile
	 * @param int  $current
	 * @param int  $scopeCloser
	 *
	 * @return false|int
	 */
	private function skipGuardClauses( File $phpcsFile, $current, $scopeCloser ) {
		while ( false !== $current && $current < $scopeCloser ) {
			if ( $this->isGuardStatement( $phpcsFile, $current, $scopeCloser ) ) {
				$semicolon = $phpcsFile->findNext( T_SEMICOLON, $current + 1, $scopeCloser );

				if ( false === $semicolon ) {
					return $current;
				}

				$current = $this->findNextSignificantToken( $phpcsFile, $semicolon + 1, $scopeCloser );
				continue;
			}

			$tokens = $phpcsFile->getTokens();

			if ( T_IF === $tokens[ $current ]['code'] && $this->isGuardClauseIf( $phpcsFile, $current ) ) {
				$afterGuard = $this->findNextSignificantToken( $phpcsFile, $tokens[ $current ]['scope_closer'] + 1, $scopeCloser );
				$current    = $afterGuard;
				continue;
			}

			break;
		}

		return $current;
	}

	/**
	 * Determine if the token represents a guard statement like return or throw.
	 *
	 * @param File $phpcsFile
	 * @param int  $stackPtr
	 * @param int  $scopeCloser
	 *
	 * @return bool
	 */
	private function isGuardStatement( File $phpcsFile, $stackPtr, $scopeCloser ) {
		$tokens      = $phpcsFile->getTokens();
		$guardTokens = array( T_RETURN, T_THROW, T_BREAK, T_CONTINUE );

		if ( ! in_array( $tokens[ $stackPtr ]['code'], $guardTokens, true ) ) {
			return false;
		}

		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $stackPtr + 1, $scopeCloser );

		return false !== $semicolon;
	}

	/**
	 * Determine if an if statement is a guard clause (e.g., if ( ! cond ) { return; }).
	 *
	 * @param File $phpcsFile
	 * @param int  $ifToken
	 *
	 * @return bool
	 */
	private function isGuardClauseIf( File $phpcsFile, $ifToken ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $ifToken ]['scope_opener'] ) || ! isset( $tokens[ $ifToken ]['scope_closer'] ) ) {
			return false;
		}

		// Skip if there is an else/elseif attached.
		$afterIf = $this->findNextSignificantToken( $phpcsFile, $tokens[ $ifToken ]['scope_closer'] + 1, null );

		if ( false !== $afterIf ) {
			$code = $tokens[ $afterIf ]['code'];

			if ( T_ELSE === $code || T_ELSEIF === $code ) {
				return false;
			}
		}

		$bodyStart = $this->findNextSignificantToken( $phpcsFile, $tokens[ $ifToken ]['scope_opener'] + 1, $tokens[ $ifToken ]['scope_closer'] );

		if ( false === $bodyStart ) {
			return false;
		}

		if ( ! $this->isGuardStatement( $phpcsFile, $bodyStart, $tokens[ $ifToken ]['scope_closer'] ) ) {
			return false;
		}

		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $bodyStart + 1, $tokens[ $ifToken ]['scope_closer'] );

		if ( false === $semicolon ) {
			return false;
		}

		$afterBody = $this->findNextSignificantToken( $phpcsFile, $semicolon + 1, $tokens[ $ifToken ]['scope_closer'] );

		return false === $afterBody;
	}

	/**
	 * Find the next non-whitespace/comment token.
	 *
	 * @param File   $phpcsFile
	 * @param int    $start
	 * @param int|false $end
	 *
	 * @return false|int
	 */
	private function findNextSignificantToken( File $phpcsFile, $start, $end = false ) {
		$skip = array(
			T_WHITESPACE,
			T_COMMENT,
			T_DOC_COMMENT,
			T_DOC_COMMENT_OPEN_TAG,
			T_DOC_COMMENT_CLOSE_TAG,
			T_DOC_COMMENT_STAR,
			T_DOC_COMMENT_STRING,
			T_DOC_COMMENT_TAG,
			T_DOC_COMMENT_WHITESPACE,
		);

		return $phpcsFile->findNext( $skip, $start, $end, true );
	}
}
