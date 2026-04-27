<?php
/**
 * Sniff to prefer array_key_first/array_key_last over array_keys with index access.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects array_keys usage where only the first or last key is accessed.
 *
 * Bad:
 * $keys = array_keys( $array );
 * $first = $keys[0];
 * // or
 * $last = end( $keys );
 *
 * Good:
 * $first = array_key_first( $array );
 * $last = array_key_last( $array );
 */
class PreferArrayKeyFirstLastSniff implements Sniff {

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
		$tokens = $phpcsFile->getTokens();

		// Check if this is array_keys.
		if ( $tokens[ $stackPtr ]['content'] !== 'array_keys' ) {
			return;
		}

		// Find the opening parenthesis.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Make sure this is an assignment: $var = array_keys( ... );
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken || $tokens[ $prevToken ]['code'] !== T_EQUAL ) {
			return;
		}

		$varToken = $phpcsFile->findPrevious( T_WHITESPACE, $prevToken - 1, null, true );

		if ( false === $varToken || $tokens[ $varToken ]['code'] !== T_VARIABLE ) {
			return;
		}

		$variableName = $tokens[ $varToken ]['content'];

		// Get the array_keys argument.
		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen    = $tokens[ $openParen ]['parenthesis_closer'];
		$arrayArgStart = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $arrayArgStart ) {
			return;
		}

		// Get the full array argument.
		$arrayArg = trim( $phpcsFile->getTokensAsString( $arrayArgStart, $closeParen - $arrayArgStart ) );

		// Find the semicolon ending this statement.
		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $closeParen + 1 );

		if ( false === $semicolon ) {
			return;
		}

		// Find the function/method scope.
		$functionPtr = $phpcsFile->findPrevious( array( T_FUNCTION, T_CLOSURE ), $stackPtr - 1 );
		$scopeEnd    = null;

		if ( false !== $functionPtr && isset( $tokens[ $functionPtr ]['scope_closer'] ) ) {
			$scopeEnd = $tokens[ $functionPtr ]['scope_closer'];
		} else {
			// File scope - use end of file.
			$scopeEnd = $phpcsFile->numTokens - 1;
		}

		// Track all usages of this variable after the assignment.
		$usages          = $this->findVariableUsages( $phpcsFile, $variableName, $semicolon + 1, $scopeEnd );
		$hasInclude      = $this->hasIncludeAfter( $phpcsFile, $semicolon + 1, $scopeEnd );
		$usageCount      = count( $usages );
		$firstIndexUsage = null;
		$lastIndexUsage  = null;
		$endUsage        = null;
		$resetUsage      = null;

		foreach ( $usages as $usage ) {
			$type = $usage['type'];

			if ( $type === 'first_index' ) {
				$firstIndexUsage = $usage;
			} elseif ( $type === 'end_call' ) {
				$endUsage = $usage;
			} elseif ( $type === 'reset_call' ) {
				$resetUsage = $usage;
			} elseif ( $type === 'last_index' ) {
				$lastIndexUsage = $usage;
			}
		}

		// If there's an include/require after, assume variable might be used in view.
		if ( $hasInclude ) {
			return;
		}

		// Check if the only usage is accessing [0] (first key).
		if ( $usageCount === 1 && null !== $firstIndexUsage ) {
			$fix = $phpcsFile->addFixableError(
				'Use array_key_first( %s ) instead of array_keys( %s )[0].',
				$stackPtr,
				'UseArrayKeyFirst',
				array( $arrayArg, $arrayArg )
			);

			if ( true === $fix ) {
				$this->applyFix( $phpcsFile, $varToken, $semicolon, $firstIndexUsage, 'array_key_first', $arrayArg );
			}

			return;
		}

		// Check if the only usage is reset( $keys ) (first key).
		if ( $usageCount === 1 && null !== $resetUsage ) {
			$fix = $phpcsFile->addFixableError(
				'Use array_key_first( %s ) instead of reset( array_keys( %s ) ).',
				$stackPtr,
				'UseArrayKeyFirstReset',
				array( $arrayArg, $arrayArg )
			);

			if ( true === $fix ) {
				$this->applyFix( $phpcsFile, $varToken, $semicolon, $resetUsage, 'array_key_first', $arrayArg );
			}

			return;
		}

		// Check if the only usage is end( $keys ) (last key).
		if ( $usageCount === 1 && null !== $endUsage ) {
			$fix = $phpcsFile->addFixableError(
				'Use array_key_last( %s ) instead of end( array_keys( %s ) ).',
				$stackPtr,
				'UseArrayKeyLast',
				array( $arrayArg, $arrayArg )
			);

			if ( true === $fix ) {
				$this->applyFix( $phpcsFile, $varToken, $semicolon, $endUsage, 'array_key_last', $arrayArg );
			}

			return;
		}

		// Check if the only usage is $keys[ count( $keys ) - 1 ] (last key).
		if ( $usageCount === 2 && null !== $lastIndexUsage ) {
			$fix = $phpcsFile->addFixableError(
				'Use array_key_last( %s ) instead of array_keys with count - 1 index.',
				$stackPtr,
				'UseArrayKeyLastCount',
				array( $arrayArg )
			);

			if ( true === $fix ) {
				$this->applyFix( $phpcsFile, $varToken, $semicolon, $lastIndexUsage, 'array_key_last', $arrayArg );
			}
		}
	}

	/**
	 * Find all usages of a variable in a scope.
	 *
	 * @param File   $phpcsFile    The file being scanned.
	 * @param string $variableName The variable name to find.
	 * @param int    $start        Start position.
	 * @param int    $end          End position.
	 *
	 * @return array Array of usage info.
	 */
	private function findVariableUsages( File $phpcsFile, $variableName, $start, $end ) {
		$tokens = $phpcsFile->getTokens();
		$usages = array();

		for ( $i = $start; $i < $end; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE || $tokens[ $i ]['content'] !== $variableName ) {
				continue;
			}

			$usage = array(
				'ptr'  => $i,
				'type' => 'other',
			);

			// Check what comes after the variable.
			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false !== $nextToken ) {
				// Check for [0] access.
				if ( $tokens[ $nextToken ]['code'] === T_OPEN_SQUARE_BRACKET ) {
					$indexToken = $phpcsFile->findNext( T_WHITESPACE, $nextToken + 1, null, true );

					if ( false !== $indexToken && $tokens[ $indexToken ]['code'] === T_LNUMBER && $tokens[ $indexToken ]['content'] === '0' ) {
						$usage['type']       = 'first_index';
						$usage['bracket']    = $nextToken;
						$usage['closeBracket'] = $tokens[ $nextToken ]['bracket_closer'] ?? null;
					} elseif ( false !== $indexToken && $tokens[ $indexToken ]['code'] === T_STRING && $tokens[ $indexToken ]['content'] === 'count' ) {
						// Check for count( $var ) - 1 pattern.
						$usage['type'] = 'last_index';
						$usage['bracket'] = $nextToken;
						$usage['closeBracket'] = $tokens[ $nextToken ]['bracket_closer'] ?? null;
					}
				}
			}

			// Check if this variable is passed to end() or reset().
			$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $i - 1, null, true );

			if ( false !== $prevToken && $tokens[ $prevToken ]['code'] === T_OPEN_PARENTHESIS ) {
				$funcToken = $phpcsFile->findPrevious( T_WHITESPACE, $prevToken - 1, null, true );

				if ( false !== $funcToken && $tokens[ $funcToken ]['code'] === T_STRING ) {
					$funcName = $tokens[ $funcToken ]['content'];

					if ( $funcName === 'end' ) {
						$usage['type']     = 'end_call';
						$usage['funcPtr']  = $funcToken;
						$usage['closeParen'] = $tokens[ $prevToken ]['parenthesis_closer'] ?? null;
					} elseif ( $funcName === 'reset' ) {
						$usage['type']     = 'reset_call';
						$usage['funcPtr']  = $funcToken;
						$usage['closeParen'] = $tokens[ $prevToken ]['parenthesis_closer'] ?? null;
					}
				}
			}

			$usages[] = $usage;
		}

		return $usages;
	}

	/**
	 * Check if there's an include/require after a position.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return bool
	 */
	private function hasIncludeAfter( File $phpcsFile, $start, $end ) {
		$tokens       = $phpcsFile->getTokens();
		$includeTypes = array( T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE );

		for ( $i = $start; $i < $end; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], $includeTypes, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Apply the fix.
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param int    $varToken        The variable token position.
	 * @param int    $semicolon       The semicolon position.
	 * @param array  $usage           The usage info.
	 * @param string $replacementFunc The replacement function name.
	 * @param string $arrayArg        The array argument.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $varToken, $semicolon, $usage, $replacementFunc, $arrayArg ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$fixer->beginChangeset();

		// Get the variable being assigned to at the usage site.
		$usagePtr = $usage['ptr'];

		// Find what variable the result is assigned to.
		if ( $usage['type'] === 'end_call' || $usage['type'] === 'reset_call' ) {
			// Pattern: $result = end( $keys );
			// Find the assignment before the function call.
			$assignPtr = $phpcsFile->findPrevious( T_EQUAL, $usage['funcPtr'] - 1, null );

			if ( false !== $assignPtr ) {
				$resultVarPtr = $phpcsFile->findPrevious( T_VARIABLE, $assignPtr - 1, null );

				if ( false !== $resultVarPtr ) {
					$resultVar = $tokens[ $resultVarPtr ]['content'];

					// Find the semicolon after the function call.
					$usageSemicolon = $phpcsFile->findNext( T_SEMICOLON, $usage['closeParen'] + 1 );

					// Remove the original array_keys line.
					for ( $i = $varToken; $i <= $semicolon; $i++ ) {
						$fixer->replaceToken( $i, '' );
					}

					// Replace the end/reset line with the new function.
					for ( $i = $resultVarPtr; $i <= $usageSemicolon; $i++ ) {
						$fixer->replaceToken( $i, '' );
					}

					$fixer->addContent( $resultVarPtr, $resultVar . ' = ' . $replacementFunc . '( ' . $arrayArg . ' );' );
				}
			}
		} elseif ( $usage['type'] === 'first_index' ) {
			// Pattern: $result = $keys[0];
			$assignPtr = $phpcsFile->findPrevious( T_EQUAL, $usagePtr - 1, null );

			if ( false !== $assignPtr ) {
				$resultVarPtr = $phpcsFile->findPrevious( T_VARIABLE, $assignPtr - 1, null );

				if ( false !== $resultVarPtr ) {
					$resultVar = $tokens[ $resultVarPtr ]['content'];

					// Find the semicolon after the index access.
					$usageSemicolon = $phpcsFile->findNext( T_SEMICOLON, $usage['closeBracket'] + 1 );

					// Remove the original array_keys line.
					for ( $i = $varToken; $i <= $semicolon; $i++ ) {
						$fixer->replaceToken( $i, '' );
					}

					// Replace the index access line with the new function.
					for ( $i = $resultVarPtr; $i <= $usageSemicolon; $i++ ) {
						$fixer->replaceToken( $i, '' );
					}

					$fixer->addContent( $resultVarPtr, $resultVar . ' = ' . $replacementFunc . '( ' . $arrayArg . ' );' );
				}
			}
		}

		$fixer->endChangeset();
	}
}
