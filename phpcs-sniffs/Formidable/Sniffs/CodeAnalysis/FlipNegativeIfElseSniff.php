<?php
/**
 * Formidable_Sniffs_CodeAnalysis_FlipNegativeIfElseSniff
 *
 * Detects if/else where the if condition is negative and flips them
 * so the positive condition comes first.
 *
 * Only handles simple if/else (no elseif).
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Flips negative if/else so the positive condition comes first.
 *
 * Bad:
 * if ( ! $condition ) {
 *     do_something();
 * } else {
 *     do_other();
 * }
 *
 * Good:
 * if ( $condition ) {
 *     do_other();
 * } else {
 *     do_something();
 * }
 *
 * Also handles !== and != operators:
 *
 * Bad:
 * if ( $type !== 'post' ) {
 *     handle_other();
 * } else {
 *     handle_post();
 * }
 *
 * Good:
 * if ( $type === 'post' ) {
 *     handle_post();
 * } else {
 *     handle_other();
 * }
 */
class FlipNegativeIfElseSniff implements Sniff {

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

		// Must have scope opener/closer and parentheses.
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		if ( ! isset( $tokens[ $stackPtr ]['parenthesis_opener'] ) || ! isset( $tokens[ $stackPtr ]['parenthesis_closer'] ) ) {
			return;
		}

		$ifCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Check if there's an else after the if (skip whitespace).
		$afterIfCloser = $phpcsFile->findNext( T_WHITESPACE, $ifCloser + 1, null, true );

		if ( false === $afterIfCloser ) {
			return;
		}

		// Must be an else, not an elseif.
		if ( $tokens[ $afterIfCloser ]['code'] !== T_ELSE ) {
			return;
		}

		$elseToken = $afterIfCloser;

		// Get the else's scope opener and closer.
		if ( ! isset( $tokens[ $elseToken ]['scope_opener'] ) || ! isset( $tokens[ $elseToken ]['scope_closer'] ) ) {
			return;
		}

		$conditionOpener = $tokens[ $stackPtr ]['parenthesis_opener'];
		$conditionCloser = $tokens[ $stackPtr ]['parenthesis_closer'];

		// Check if the condition is negative.
		$negationType = $this->findNegativeCondition( $phpcsFile, $conditionOpener, $conditionCloser );

		if ( false === $negationType ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Negative if/else condition. Flip to use positive condition first.',
			$stackPtr,
			'Found'
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $stackPtr, $elseToken, $negationType );
		}
	}

	/**
	 * Check if the condition inside parentheses is negative.
	 *
	 * Detects:
	 * - Leading ! operator: if ( ! $condition )
	 * - !== operator: if ( $a !== $b )
	 * - != operator: if ( $a != $b )
	 *
	 * Skips compound conditions with && or || at the top level.
	 *
	 * @param File $phpcsFile       The file being scanned.
	 * @param int  $conditionOpener The opening parenthesis position.
	 * @param int  $conditionCloser The closing parenthesis position.
	 *
	 * @return array|false Array with negation info, or false if not negative.
	 */
	private function findNegativeCondition( File $phpcsFile, $conditionOpener, $conditionCloser ) {
		$tokens = $phpcsFile->getTokens();

		// Skip compound conditions with && or || at the top level.
		if ( $this->hasTopLevelLogicalOperator( $phpcsFile, $conditionOpener, $conditionCloser ) ) {
			return false;
		}

		// Check for leading ! operator.
		$firstToken = $phpcsFile->findNext( T_WHITESPACE, $conditionOpener + 1, $conditionCloser, true );

		if ( false !== $firstToken && $tokens[ $firstToken ]['code'] === T_BOOLEAN_NOT ) {
			// Skip ! empty() - this is a common pattern that shouldn't be flipped.
			$afterNot = $phpcsFile->findNext( T_WHITESPACE, $firstToken + 1, $conditionCloser, true );

			if ( false !== $afterNot && $tokens[ $afterNot ]['code'] === T_EMPTY ) {
				return false;
			}

			return array(
				'type'     => 'boolean_not',
				'position' => $firstToken,
			);
		}

		// Check for !== or != operators.
		for ( $i = $conditionOpener + 1; $i < $conditionCloser; $i++ ) {
			// Skip nested parentheses.
			if ( $tokens[ $i ]['code'] === T_OPEN_PARENTHESIS && isset( $tokens[ $i ]['parenthesis_closer'] ) ) {
				$i = $tokens[ $i ]['parenthesis_closer'];
				continue;
			}

			if ( $tokens[ $i ]['code'] === T_IS_NOT_IDENTICAL ) {
				return array(
					'type'     => 'not_identical',
					'position' => $i,
				);
			}

			if ( $tokens[ $i ]['code'] === T_IS_NOT_EQUAL ) {
				return array(
					'type'     => 'not_equal',
					'position' => $i,
				);
			}
		}

		return false;
	}

	/**
	 * Check if the condition has && or || at the top level (not inside nested parentheses).
	 *
	 * @param File $phpcsFile       The file being scanned.
	 * @param int  $conditionOpener The opening parenthesis position.
	 * @param int  $conditionCloser The closing parenthesis position.
	 *
	 * @return bool
	 */
	private function hasTopLevelLogicalOperator( File $phpcsFile, $conditionOpener, $conditionCloser ) {
		$tokens     = $phpcsFile->getTokens();
		$parenDepth = 0;

		for ( $i = $conditionOpener + 1; $i < $conditionCloser; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				--$parenDepth;
				continue;
			}

			// Only check at top level.
			if ( $parenDepth !== 0 ) {
				continue;
			}

			if ( in_array( $code, array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Apply the fix by swapping if/else bodies and flipping the condition.
	 *
	 * @param File  $phpcsFile    The file being scanned.
	 * @param int   $ifToken      The if token position.
	 * @param int   $elseToken    The else token position.
	 * @param array $negationType Info about the negation type and position.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $ifToken, $elseToken, $negationType ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$ifOpener        = $tokens[ $ifToken ]['scope_opener'];
		$ifCloser        = $tokens[ $ifToken ]['scope_closer'];
		$elseOpener      = $tokens[ $elseToken ]['scope_opener'];
		$elseCloser      = $tokens[ $elseToken ]['scope_closer'];
		$conditionOpener = $tokens[ $ifToken ]['parenthesis_opener'];
		$conditionCloser = $tokens[ $ifToken ]['parenthesis_closer'];

		// Collect token contents for each body as arrays.
		$ifBodyTokens = array();

		for ( $i = $ifOpener + 1; $i < $ifCloser; $i++ ) {
			$ifBodyTokens[] = $tokens[ $i ]['content'];
		}

		$elseBodyTokens = array();

		for ( $i = $elseOpener + 1; $i < $elseCloser; $i++ ) {
			$elseBodyTokens[] = $tokens[ $i ]['content'];
		}

		// Negate the condition based on type.
		$newCondition = $this->flipCondition( $phpcsFile, $conditionOpener, $conditionCloser, $negationType );

		$fixer->beginChangeset();

		// Replace the condition.
		for ( $i = $conditionOpener + 1; $i < $conditionCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
		$fixer->addContent( $conditionOpener, ' ' . $newCondition . ' ' );

		// Replace the if body tokens: first token gets the else body, rest are cleared.
		$elseBodyString = implode( '', $elseBodyTokens );
		$fixer->replaceToken( $ifOpener + 1, $elseBodyString );

		for ( $i = $ifOpener + 2; $i < $ifCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Replace the else body tokens: first token gets the if body, rest are cleared.
		$ifBodyString = implode( '', $ifBodyTokens );
		$fixer->replaceToken( $elseOpener + 1, $ifBodyString );

		for ( $i = $elseOpener + 2; $i < $elseCloser; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		$fixer->endChangeset();
	}

	/**
	 * Flip the condition to its positive form.
	 *
	 * @param File  $phpcsFile       The file being scanned.
	 * @param int   $conditionOpener The opening parenthesis position.
	 * @param int   $conditionCloser The closing parenthesis position.
	 * @param array $negationType    Info about the negation type and position.
	 *
	 * @return string The flipped condition.
	 */
	private function flipCondition( File $phpcsFile, $conditionOpener, $conditionCloser, $negationType ) {
		$tokens = $phpcsFile->getTokens();

		if ( $negationType['type'] === 'boolean_not' ) {
			// Remove the ! and any trailing whitespace.
			$result = '';
			$skipTo = $negationType['position'] + 1;

			// Skip whitespace after !.
			if ( $skipTo < $conditionCloser && $tokens[ $skipTo ]['code'] === T_WHITESPACE ) {
				++$skipTo;
			}

			for ( $i = $skipTo; $i < $conditionCloser; $i++ ) {
				$result .= $tokens[ $i ]['content'];
			}

			return trim( $result );
		}

		// For !== and !=, replace with === and == respectively.
		$operatorMap = array(
			'not_identical' => '===',
			'not_equal'     => '==',
		);

		$replacement = $operatorMap[ $negationType['type'] ];
		$result      = '';

		for ( $i = $conditionOpener + 1; $i < $conditionCloser; $i++ ) {
			if ( $i === $negationType['position'] ) {
				$result .= $replacement;
			} else {
				$result .= $tokens[ $i ]['content'];
			}
		}

		return trim( $result );
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
}
