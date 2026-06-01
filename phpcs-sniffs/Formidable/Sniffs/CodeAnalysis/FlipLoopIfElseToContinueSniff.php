<?php
/**
 * Formidable_Sniffs_CodeAnalysis_FlipLoopIfElseToContinueSniff
 *
 * Detects if/else at the end of loops where the else is small.
 * Suggests flipping the condition, handling the else first with continue,
 * to reduce indentation of the larger if block.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects if/else at end of loops that should use continue.
 *
 * Bad:
 * foreach ($items as $item) {
 *     if (isset($data[$key])) {
 *         // 3+ lines of code
 *         if (is_array($data[$key])) {
 *             $data[$key][] = $value;
 *         } else {
 *             $data[$key] = array($data[$key], $value);
 *         }
 *     } else {
 *         $data[$key] = $value;
 *     }
 * }
 *
 * Good:
 * foreach ($items as $item) {
 *     if (! isset($data[$key])) {
 *         $data[$key] = $value;
 *         continue;
 *     }
 *     // 3+ lines of code (now dedented)
 *     if (is_array($data[$key])) {
 *         $data[$key][] = $value;
 *     } else {
 *         $data[$key] = array($data[$key], $value);
 *     }
 * }
 */
class FlipLoopIfElseToContinueSniff implements Sniff {

	/**
	 * Minimum number of lines inside the if body to consider it "large".
	 *
	 * @var int
	 */
	public $minIfLines = 3;

	/**
	 * Maximum number of lines inside the else body to consider it "small".
	 *
	 * @var int
	 */
	public $maxElseLines = 2;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FOREACH, T_FOR, T_WHILE, T_DO );
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

		$loopOpener = $tokens[ $stackPtr ]['scope_opener'];
		$loopCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find the last statement in the loop (should be an if/else).
		$lastToken = $phpcsFile->findPrevious( T_WHITESPACE, $loopCloser - 1, $loopOpener, true );

		if ( false === $lastToken ) {
			return;
		}

		// Should be a closing brace.
		if ( $tokens[ $lastToken ]['code'] !== T_CLOSE_CURLY_BRACKET ) {
			return;
		}

		// Find what this brace belongs to.
		if ( ! isset( $tokens[ $lastToken ]['scope_condition'] ) ) {
			return;
		}

		$scopeCondition = $tokens[ $lastToken ]['scope_condition'];

		// Must be an else.
		if ( $tokens[ $scopeCondition ]['code'] !== T_ELSE ) {
			return;
		}

		$elseToken  = $scopeCondition;
		$elseOpener = $tokens[ $elseToken ]['scope_opener'];
		$elseCloser = $tokens[ $elseToken ]['scope_closer'];

		// Find the if that this else belongs to.
		$ifToken = $this->findIfForElse( $phpcsFile, $elseToken );

		if ( false === $ifToken ) {
			return;
		}

		// Make sure there's no elseif between if and else.
		$ifCloser    = $tokens[ $ifToken ]['scope_closer'];
		$afterIfClose = $phpcsFile->findNext( T_WHITESPACE, $ifCloser + 1, null, true );

		if ( false !== $afterIfClose && $tokens[ $afterIfClose ]['code'] === T_ELSEIF ) {
			return;
		}

		$ifOpener = $tokens[ $ifToken ]['scope_opener'];

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

		// Check that the if is the last thing in the loop (nothing after the else closer except whitespace).
		$afterElse = $phpcsFile->findNext( T_WHITESPACE, $elseCloser + 1, $loopCloser, true );

		if ( false !== $afterElse ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Large if (%d lines) with small else (%d lines) at end of loop. Consider flipping the condition and using continue to reduce indentation.',
			$ifToken,
			'Found',
			array( $ifLineCount, $elseLineCount )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $ifToken, $elseToken );
		}
	}

	/**
	 * Find the if token that an else belongs to.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $elseToken  The else token position.
	 *
	 * @return false|int
	 */
	private function findIfForElse( File $phpcsFile, $elseToken ) {
		$tokens = $phpcsFile->getTokens();

		// Look backwards for the if.
		for ( $i = $elseToken - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_CLOSE_CURLY_BRACKET ) {
				if ( isset( $tokens[ $i ]['scope_condition'] ) ) {
					$condition = $tokens[ $i ]['scope_condition'];

					if ( $tokens[ $condition ]['code'] === T_IF ) {
						return $condition;
					}
				}

				break;
			}
		}

		return false;
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

		return max( 0, $endLine - $startLine - 1 );
	}

	/**
	 * Apply the fix.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $ifToken    The if token position.
	 * @param int  $elseToken  The else token position.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $ifToken, $elseToken ) {
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

		// Dedent the if body.
		$ifBodyContent = $this->dedentCode( $ifBodyContent );

		// Get the indentation.
		$ifIndent = $this->getIndentation( $phpcsFile, $ifToken );

		$fixer->beginChangeset();

		// Replace the condition with negated version.
		for ( $i = $conditionOpener + 1; $i < $conditionCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
		$fixer->addContent( $conditionOpener, ' ' . $negatedCondition . ' ' );

		// Replace the if body with the else body + continue.
		$newIfBody = rtrim( $elseBodyContent ) . $phpcsFile->eolChar . $ifIndent . "\t" . 'continue;';

		for ( $i = $ifOpener + 1; $i < $ifCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
		$fixer->addContent( $ifOpener, $phpcsFile->eolChar . $newIfBody . $phpcsFile->eolChar . $ifIndent );

		// Remove the else keyword, braces, and content.
		for ( $i = $ifCloser + 1; $i <= $elseCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Add the dedented if body after the if's closing brace.
		$ifBodyContent = rtrim( $ifBodyContent );
		$fixer->addContent(
			$ifCloser,
			$phpcsFile->eolChar . $phpcsFile->eolChar .
			$ifIndent . ltrim( $ifBodyContent ) . $phpcsFile->eolChar
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

		// Check if this is a compound condition.
		$isCompound = $this->hasTopLevelOperator( $condition );

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

			return '! ' . $condition;
		}

		return '! ( ' . $condition . ' )';
	}

	/**
	 * Check if a condition has && or || at the top level.
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

			if ( $parenDepth !== 0 ) {
				continue;
			}

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
	 * Try to flip a comparison operator.
	 *
	 * @param string $condition The condition to flip.
	 *
	 * @return false|string
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
	 * @return string
	 */
	private function getIndentation( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$lineStart = $stackPtr;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			--$lineStart;
		}

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
	 * @return string
	 */
	private function dedentCode( $code ) {
		$lines  = explode( "\n", $code );
		$result = array();

		foreach ( $lines as $line ) {
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
