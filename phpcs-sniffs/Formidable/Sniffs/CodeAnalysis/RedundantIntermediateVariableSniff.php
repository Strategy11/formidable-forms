<?php
/**
 * Formidable_Sniffs_CodeAnalysis_RedundantIntermediateVariableSniff
 *
 * Detects redundant intermediate variable assignments where a variable is assigned
 * and then immediately assigned to a class property or another variable without
 * being used again.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant intermediate variable assignments.
 *
 * Bad:
 * $comment_count = FrmDb::get_count(...);
 * self::$comment_count = $comment_count;
 *
 * Good:
 * self::$comment_count = FrmDb::get_count(...);
 */
class RedundantIntermediateVariableSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_VARIABLE );
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

		$variableName = $tokens[ $stackPtr ]['content'];

		// Skip $this.
		if ( '$this' === $variableName ) {
			return;
		}

		// Check if this is an assignment (next non-whitespace is =).
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_EQUAL ) {
			return;
		}

		// Find the semicolon ending this assignment.
		$assignmentEnd = $phpcsFile->findNext( T_SEMICOLON, $nextToken + 1 );

		if ( false === $assignmentEnd ) {
			return;
		}

		// Find the function scope we're in.
		$functionScope = $this->getFunctionScope( $phpcsFile, $stackPtr );

		if ( false === $functionScope ) {
			return;
		}

		$functionCloser = $tokens[ $functionScope ]['scope_closer'];

		// Find the next statement after this assignment.
		$nextStatement = $phpcsFile->findNext( T_WHITESPACE, $assignmentEnd + 1, $functionCloser, true );

		if ( false === $nextStatement ) {
			return;
		}

		// Check if the next statement is an assignment to self::$property, static::$property, or $this->property.
		$targetAssignment = $this->getTargetAssignment( $phpcsFile, $tokens, $nextStatement, $variableName );

		if ( false === $targetAssignment ) {
			return;
		}

		// $targetAssignment['end'] is the semicolon ending the target assignment.
		$targetAssignmentEnd = $targetAssignment['end'];

		// Check for require/include statements after the target assignment.
		if ( $this->hasRequireOrIncludeAfter( $phpcsFile, $tokens, $targetAssignmentEnd, $functionCloser ) ) {
			return;
		}

		// Check if the variable is used anywhere else in the function after the target assignment.
		if ( $this->isVariableUsedAfter( $phpcsFile, $tokens, $targetAssignmentEnd + 1, $functionCloser, $variableName ) ) {
			return;
		}

		// Check if the variable is used anywhere before this assignment (would indicate it's being reused).
		$functionOpener = $tokens[ $functionScope ]['scope_opener'];

		if ( $this->isVariableUsedBefore( $phpcsFile, $tokens, $functionOpener, $stackPtr, $variableName ) ) {
			return;
		}

		// This is a redundant intermediate variable.
		$fix = $phpcsFile->addFixableError(
			'Redundant intermediate variable %s. Assign directly to %s instead.',
			$stackPtr,
			'RedundantIntermediateVariable',
			array( $variableName, $targetAssignment['target'] )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $tokens, $stackPtr, $assignmentEnd, $nextStatement, $targetAssignment, $targetAssignmentEnd );
		}
	}

	/**
	 * Get the function scope for a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The token position.
	 *
	 * @return false|int The function token position or false.
	 */
	private function getFunctionScope( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Walk up to find the function we're in.
		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( in_array( $tokens[ $i ]['code'], array( T_FUNCTION, T_CLOSURE ), true ) ) {
				if ( isset( $tokens[ $i ]['scope_opener'] ) && isset( $tokens[ $i ]['scope_closer'] ) ) {
					// Make sure we're actually inside this function's scope.
					if ( $stackPtr > $tokens[ $i ]['scope_opener'] && $stackPtr < $tokens[ $i ]['scope_closer'] ) {
						return $i;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if the next statement is an assignment using the variable.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $tokens       The token stack.
	 * @param int    $nextStatement The next statement position.
	 * @param string $variableName The variable name to look for.
	 *
	 * @return array|false Array with 'target' and 'end' keys, or false.
	 */
	private function getTargetAssignment( File $phpcsFile, array $tokens, $nextStatement, $variableName ) {
		// Check for self::$property, static::$property, or $this->property.
		$target     = '';
		$targetEnd  = $nextStatement;
		$equalToken = false;

		// Check for self:: or static::.
		if ( in_array( $tokens[ $nextStatement ]['code'], array( T_SELF, T_STATIC ), true ) ) {
			$target = $tokens[ $nextStatement ]['content'];

			$doubleColon = $phpcsFile->findNext( T_WHITESPACE, $nextStatement + 1, null, true );

			if ( false === $doubleColon || $tokens[ $doubleColon ]['code'] !== T_DOUBLE_COLON ) {
				return false;
			}

			$target .= '::';

			$propertyToken = $phpcsFile->findNext( T_WHITESPACE, $doubleColon + 1, null, true );

			if ( false === $propertyToken || $tokens[ $propertyToken ]['code'] !== T_VARIABLE ) {
				return false;
			}

			$target   .= $tokens[ $propertyToken ]['content'];
			$targetEnd = $propertyToken;

			$equalToken = $phpcsFile->findNext( T_WHITESPACE, $propertyToken + 1, null, true );
		} elseif ( $tokens[ $nextStatement ]['code'] === T_VARIABLE && '$this' === $tokens[ $nextStatement ]['content'] ) {
			// Check for $this->property.
			$target = '$this';

			$objectOperator = $phpcsFile->findNext( T_WHITESPACE, $nextStatement + 1, null, true );

			if ( false === $objectOperator || $tokens[ $objectOperator ]['code'] !== T_OBJECT_OPERATOR ) {
				return false;
			}

			$target .= '->';

			$propertyToken = $phpcsFile->findNext( T_WHITESPACE, $objectOperator + 1, null, true );

			if ( false === $propertyToken || $tokens[ $propertyToken ]['code'] !== T_STRING ) {
				return false;
			}

			$target   .= $tokens[ $propertyToken ]['content'];
			$targetEnd = $propertyToken;

			$equalToken = $phpcsFile->findNext( T_WHITESPACE, $propertyToken + 1, null, true );
		} else {
			return false;
		}

		// Check for = sign.
		if ( false === $equalToken || $tokens[ $equalToken ]['code'] !== T_EQUAL ) {
			return false;
		}

		// Check if the value being assigned is our variable.
		$valueToken = $phpcsFile->findNext( T_WHITESPACE, $equalToken + 1, null, true );

		if ( false === $valueToken || $tokens[ $valueToken ]['code'] !== T_VARIABLE ) {
			return false;
		}

		if ( $tokens[ $valueToken ]['content'] !== $variableName ) {
			return false;
		}

		// Make sure the variable is the only thing being assigned (followed by semicolon).
		$afterValue = $phpcsFile->findNext( T_WHITESPACE, $valueToken + 1, null, true );

		if ( false === $afterValue || $tokens[ $afterValue ]['code'] !== T_SEMICOLON ) {
			return false;
		}

		return array(
			'target' => $target,
			'end'    => $afterValue,
		);
	}

	/**
	 * Check if there are require/include statements after a position.
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param array $tokens         The token stack.
	 * @param int   $start          Start position.
	 * @param int   $functionCloser Function closer position.
	 *
	 * @return bool
	 */
	private function hasRequireOrIncludeAfter( File $phpcsFile, array $tokens, $start, $functionCloser ) {
		$includeTokens = array( T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE );

		for ( $i = $start; $i < $functionCloser; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], $includeTokens, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a variable is used after a position.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $tokens       The token stack.
	 * @param int    $start        Start position.
	 * @param int    $end          End position.
	 * @param string $variableName The variable name.
	 *
	 * @return bool
	 */
	private function isVariableUsedAfter( File $phpcsFile, array $tokens, $start, $end, $variableName ) {
		for ( $i = $start; $i < $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === $variableName ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a variable is used before a position (excluding function parameters).
	 *
	 * @param File   $phpcsFile      The file being scanned.
	 * @param array  $tokens         The token stack.
	 * @param int    $functionOpener Function opener position.
	 * @param int    $end            End position.
	 * @param string $variableName   The variable name.
	 *
	 * @return bool
	 */
	private function isVariableUsedBefore( File $phpcsFile, array $tokens, $functionOpener, $end, $variableName ) {
		for ( $i = $functionOpener + 1; $i < $end; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === $variableName ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Apply the fix.
	 *
	 * @param File  $phpcsFile           The file being scanned.
	 * @param array $tokens              The token stack.
	 * @param int   $varStart            Start of variable declaration.
	 * @param int   $assignmentEnd       End of first assignment.
	 * @param int   $nextStatement       Start of target assignment.
	 * @param array $targetAssignment    Target assignment info.
	 * @param int   $targetAssignmentEnd End of target assignment.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, array $tokens, $varStart, $assignmentEnd, $nextStatement, array $targetAssignment, $targetAssignmentEnd ) {
		$phpcsFile->fixer->beginChangeset();

		// Get the value being assigned to the variable (everything between = and ;).
		$equalToken = $phpcsFile->findNext( T_EQUAL, $varStart + 1, $assignmentEnd );

		if ( false === $equalToken ) {
			$phpcsFile->fixer->endChangeset();
			return;
		}

		// Get the value content.
		$valueStart   = $phpcsFile->findNext( T_WHITESPACE, $equalToken + 1, $assignmentEnd, true );
		$valueContent = '';

		for ( $i = $valueStart; $i < $assignmentEnd; $i++ ) {
			$valueContent .= $tokens[ $i ]['content'];
		}

		// Find the start of the line for the variable declaration.
		$lineStart = $varStart;

		for ( $i = $varStart - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $tokens[ $varStart ]['line'] ) {
				break;
			}
			$lineStart = $i;
		}

		// Remove the variable declaration line.
		for ( $i = $lineStart; $i <= $assignmentEnd; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Remove the newline after the semicolon.
		if ( isset( $tokens[ $assignmentEnd + 1 ] ) && $tokens[ $assignmentEnd + 1 ]['code'] === T_WHITESPACE ) {
			$wsContent = $tokens[ $assignmentEnd + 1 ]['content'];

			if ( strpos( $wsContent, "\n" ) === 0 ) {
				$phpcsFile->fixer->replaceToken( $assignmentEnd + 1, substr( $wsContent, 1 ) );
			}
		}

		// Find the = in the target assignment and replace the variable with the value.
		$targetEqual = $phpcsFile->findNext( T_EQUAL, $nextStatement, $targetAssignment['end'] + 10 );

		if ( false === $targetEqual ) {
			$phpcsFile->fixer->endChangeset();
			return;
		}

		// Find the variable token in the target assignment.
		$targetVarToken = $phpcsFile->findNext( T_VARIABLE, $targetEqual + 1, $targetAssignment['end'] + 10 );

		if ( false === $targetVarToken ) {
			$phpcsFile->fixer->endChangeset();
			return;
		}

		// Replace the variable with the value.
		$phpcsFile->fixer->replaceToken( $targetVarToken, $valueContent );

		$phpcsFile->fixer->endChangeset();
	}
}
