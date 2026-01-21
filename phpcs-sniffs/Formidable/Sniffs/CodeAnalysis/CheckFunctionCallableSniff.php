<?php
/**
 * Formidable_Sniffs_CodeAnalysis_CheckFunctionCallableSniff
 *
 * Detects calls to functions that could be disabled and should be wrapped in function_exists().
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects calls to functions that could be disabled and should be wrapped in function_exists().
 *
 * Bad:
 * set_time_limit( 0 );
 *
 * Good:
 * if ( function_exists( 'set_time_limit' ) ) {
 *     set_time_limit( 0 );
 * }
 */
class CheckFunctionCallableSniff implements Sniff {

	/**
	 * Functions that could be disabled and should be checked.
	 *
	 * @var array
	 */
	private $disableableFunctions = array(
		'set_time_limit',
		'mime_content_type',
		// ctype functions.
		'ctype_alnum',
		'ctype_alpha',
		'ctype_cntrl',
		'ctype_digit',
		'ctype_graph',
		'ctype_lower',
		'ctype_print',
		'ctype_punct',
		'ctype_space',
		'ctype_upper',
		'ctype_xdigit',
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING );
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
		$tokens       = $phpcsFile->getTokens();
		$functionName = $tokens[ $stackPtr ]['content'];

		// Check if this is one of the functions we're looking for.
		if ( ! in_array( $functionName, $this->disableableFunctions, true ) ) {
			return;
		}

		// Make sure this is actually a function call (followed by open parenthesis).
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Check if this is a method call (preceded by -> or ::) or a function/method definition (preceded by function keyword).
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prevToken && in_array( $tokens[ $prevToken ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION ), true ) ) {
			return;
		}

		// Check if this function call is already wrapped in a function_exists check.
		if ( $this->isAlreadyChecked( $phpcsFile, $tokens, $stackPtr, $functionName ) ) {
			return;
		}

		// Find the full statement to wrap.
		$statementInfo = $this->getStatementInfo( $phpcsFile, $tokens, $stackPtr );

		if ( false === $statementInfo ) {
			$phpcsFile->addError(
				'Function %s() could be disabled. Wrap in if ( function_exists( \'%s\' ) ) check.',
				$stackPtr,
				'UncheckedCall',
				array( $functionName, $functionName )
			);
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Function %s() could be disabled. Wrap in if ( function_exists( \'%s\' ) ) check.',
			$stackPtr,
			'UncheckedCall',
			array( $functionName, $functionName )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $tokens, $statementInfo, $functionName );
		}
	}

	/**
	 * Check if the function call is already wrapped in a function_exists check.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $tokens       The token stack.
	 * @param int    $stackPtr     The position of the function call.
	 * @param string $functionName The function name being called.
	 *
	 * @return bool
	 */
	private function isAlreadyChecked( File $phpcsFile, array $tokens, $stackPtr, $functionName ) {
		// Check if we're inside an if condition that already has a function_exists check.
		$ifConditionInfo = $this->getIfConditionInfo( $phpcsFile, $tokens, $stackPtr );

		if ( false !== $ifConditionInfo ) {
			// We're in an if condition - check if function_exists is already in this condition.
			if ( $this->conditionChecksFunction( $phpcsFile, $tokens, $ifConditionInfo['if_token'], $functionName ) ) {
				return true;
			}
		}

		// Check if we're inside an if statement's body that checks for this function.
		if ( ! isset( $tokens[ $stackPtr ]['conditions'] ) ) {
			return false;
		}

		foreach ( $tokens[ $stackPtr ]['conditions'] as $scopePtr => $scopeType ) {
			if ( $scopeType !== T_IF ) {
				continue;
			}

			if ( $this->conditionChecksFunction( $phpcsFile, $tokens, $scopePtr, $functionName ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if an if condition contains a function_exists check for the given function.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param array  $tokens       The token stack.
	 * @param int    $ifToken      The if token position.
	 * @param string $functionName The function name to check for.
	 *
	 * @return bool
	 */
	private function conditionChecksFunction( File $phpcsFile, array $tokens, $ifToken, $functionName ) {
		if ( ! isset( $tokens[ $ifToken ]['parenthesis_opener'] ) || ! isset( $tokens[ $ifToken ]['parenthesis_closer'] ) ) {
			return false;
		}

		$conditionStart = $tokens[ $ifToken ]['parenthesis_opener'];
		$conditionEnd   = $tokens[ $ifToken ]['parenthesis_closer'];

		// Look for function_exists in the condition.
		for ( $i = $conditionStart; $i <= $conditionEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_STRING || $tokens[ $i ]['content'] !== 'function_exists' ) {
				continue;
			}

			// Found function_exists, check if it's checking our function.
			$openParen = $phpcsFile->findNext( T_WHITESPACE, $i + 1, $conditionEnd, true );

			if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
				continue;
			}

			$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

			// Look for the function name as a string.
			for ( $j = $openParen + 1; $j < $closeParen; $j++ ) {
				if ( $tokens[ $j ]['code'] === T_CONSTANT_ENCAPSED_STRING ) {
					$checkedName = trim( $tokens[ $j ]['content'], '"\'' );

					if ( $checkedName === $functionName ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get information about the statement containing the function call.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  The position of the function call.
	 *
	 * @return array|false Array with 'start', 'end', 'indent', and 'type' keys, or false.
	 */
	private function getStatementInfo( File $phpcsFile, array $tokens, $stackPtr ) {
		// Check if this function call is inside an if condition.
		$ifConditionInfo = $this->getIfConditionInfo( $phpcsFile, $tokens, $stackPtr );

		if ( false !== $ifConditionInfo ) {
			return $ifConditionInfo;
		}

		// Find the start of the line.
		$lineStart = $stackPtr;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			--$lineStart;
		}

		// Get indentation.
		$indent = '';

		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			$indent = $tokens[ $lineStart ]['content'];
		}

		// Find the semicolon ending this statement.
		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $stackPtr + 1 );

		if ( false === $semicolon ) {
			return false;
		}

		// Make sure the semicolon is on the same or following lines (not a different statement).
		if ( $tokens[ $semicolon ]['line'] > $tokens[ $stackPtr ]['line'] + 5 ) {
			return false;
		}

		return array(
			'type'   => 'statement',
			'start'  => $lineStart,
			'end'    => $semicolon,
			'indent' => $indent,
		);
	}

	/**
	 * Check if the function call is inside an if condition and return info about it.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  The position of the function call.
	 *
	 * @return array|false Array with condition info, or false if not in an if condition.
	 */
	private function getIfConditionInfo( File $phpcsFile, array $tokens, $stackPtr ) {
		// Look backwards for an if token on the same line or recent lines.
		for ( $i = $stackPtr - 1; $i > 0 && $tokens[ $i ]['line'] >= $tokens[ $stackPtr ]['line'] - 1; $i-- ) {
			if ( $tokens[ $i ]['code'] !== T_IF ) {
				continue;
			}

			// Found an if, check if our function call is inside its condition.
			if ( ! isset( $tokens[ $i ]['parenthesis_opener'] ) || ! isset( $tokens[ $i ]['parenthesis_closer'] ) ) {
				continue;
			}

			$conditionStart = $tokens[ $i ]['parenthesis_opener'];
			$conditionEnd   = $tokens[ $i ]['parenthesis_closer'];

			if ( $stackPtr > $conditionStart && $stackPtr < $conditionEnd ) {
				// The function call is inside this if condition.
				return array(
					'type'            => 'if_condition',
					'if_token'        => $i,
					'condition_start' => $conditionStart,
					'condition_end'   => $conditionEnd,
				);
			}
		}

		return false;
	}

	/**
	 * Apply the fix to wrap the function call in a function_exists check.
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param array  $tokens        The token stack.
	 * @param array  $statementInfo Statement info from getStatementInfo().
	 * @param string $functionName  The function name.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, array $tokens, array $statementInfo, $functionName ) {
		if ( $statementInfo['type'] === 'if_condition' ) {
			$this->applyIfConditionFix( $phpcsFile, $tokens, $statementInfo, $functionName );
			return;
		}

		$fixer = $phpcsFile->fixer;

		$start  = $statementInfo['start'];
		$end    = $statementInfo['end'];
		$indent = $statementInfo['indent'];

		// Get the statement content.
		$statementContent = '';

		for ( $i = $start; $i <= $end; $i++ ) {
			$statementContent .= $tokens[ $i ]['content'];
		}

		$statementContent = trim( $statementContent );

		$fixer->beginChangeset();

		// Remove the original statement.
		for ( $i = $start; $i <= $end; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Build the new code with function_exists wrapper.
		$newCode  = $indent . 'if ( function_exists( \'' . $functionName . '\' ) ) {' . $phpcsFile->eolChar;
		$newCode .= $indent . "\t" . $statementContent . $phpcsFile->eolChar;
		$newCode .= $indent . '}';

		$fixer->addContent( $start, $newCode );

		$fixer->endChangeset();
	}

	/**
	 * Apply fix for function calls inside if conditions by adding && function_exists check.
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param array  $tokens        The token stack.
	 * @param array  $statementInfo Statement info.
	 * @param string $functionName  The function name.
	 *
	 * @return void
	 */
	private function applyIfConditionFix( File $phpcsFile, array $tokens, array $statementInfo, $functionName ) {
		$fixer = $phpcsFile->fixer;

		$conditionStart = $statementInfo['condition_start'];

		$fixer->beginChangeset();

		// Add function_exists check at the start of the condition with &&.
		$fixer->addContent( $conditionStart, ' function_exists( \'' . $functionName . '\' ) &&' );

		$fixer->endChangeset();
	}
}
