<?php
/**
 * Sniff to detect FrmAppHelper::icon_by_class calls with redundant 'echo' => true.
 *
 * Since icon_by_class echoes by default, 'echo' => true is redundant and should be removed.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects and fixes redundant 'echo' => true in FrmAppHelper::icon_by_class calls.
 */
class RedundantIconEchoArgSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_DOUBLE_COLON );
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Check if this is FrmAppHelper::icon_by_class.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken || $tokens[ $prevToken ]['code'] !== T_STRING ) {
			return;
		}

		if ( $tokens[ $prevToken ]['content'] !== 'FrmAppHelper' ) {
			return;
		}

		// Check the method name after ::.
		$methodToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $methodToken || $tokens[ $methodToken ]['code'] !== T_STRING ) {
			return;
		}

		if ( $tokens[ $methodToken ]['content'] !== 'icon_by_class' ) {
			return;
		}

		// Find the opening parenthesis.
		$openParen = $phpcsFile->findNext( T_WHITESPACE, $methodToken + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		// Find 'echo' => true within the parentheses.
		$echoInfo = $this->findEchoTrueArg( $phpcsFile, $openParen, $closeParen );

		if ( false === $echoInfo ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			"Redundant 'echo' => true in FrmAppHelper::icon_by_class. The method echoes by default.",
			$echoInfo['start'],
			'RedundantEchoArg'
		);

		if ( $fix ) {
			$this->applyFix( $phpcsFile, $echoInfo );
		}
	}

	/**
	 * Find 'echo' => true in the arguments.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The position of the opening parenthesis.
	 * @param int  $closeParen The position of the closing parenthesis.
	 *
	 * @return array|false Array with start/end positions or false if not found.
	 */
	private function findEchoTrueArg( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		// Look for array( or [ that contains 'echo' => true.
		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			// Look for 'echo' string.
			if ( $tokens[ $i ]['code'] !== T_CONSTANT_ENCAPSED_STRING ) {
				continue;
			}

			$content = trim( $tokens[ $i ]['content'], "\"'" );

			if ( $content !== 'echo' ) {
				continue;
			}

			// Find the double arrow after 'echo'.
			$arrow = $phpcsFile->findNext( T_WHITESPACE, $i + 1, $closeParen, true );

			if ( false === $arrow || $tokens[ $arrow ]['code'] !== T_DOUBLE_ARROW ) {
				continue;
			}

			// Find the value after =>.
			$value = $phpcsFile->findNext( T_WHITESPACE, $arrow + 1, $closeParen, true );

			if ( false === $value || $tokens[ $value ]['code'] !== T_TRUE ) {
				continue;
			}

			// Found 'echo' => true. Now determine the range to remove.
			// First, find the array that contains this key-value pair.
			$arrayInfo = $this->findContainingArray( $phpcsFile, $i, $openParen, $closeParen );

			// Check if 'echo' => true is the only element in the array.
			$isOnlyElement = $this->isOnlyArrayElement( $phpcsFile, $arrayInfo, $i, $value );

			if ( $isOnlyElement ) {
				// Remove the entire second argument (comma + array).
				return array(
					'removeEntireArg' => true,
					'arrayStart'      => $arrayInfo['start'],
					'arrayEnd'        => $arrayInfo['end'],
					'start'           => $i,
				);
			}

			// Check if there's a comma before or after.
			$start = $i;
			$end   = $value;

			// Look for leading comma (this arg comes after another).
			$beforeStart = $phpcsFile->findPrevious( T_WHITESPACE, $i - 1, $openParen, true );
			$hasLeadingComma = ( false !== $beforeStart && $tokens[ $beforeStart ]['code'] === T_COMMA );

			// Look for trailing comma.
			$afterEnd = $phpcsFile->findNext( T_WHITESPACE, $value + 1, $closeParen, true );
			$hasTrailingComma = ( false !== $afterEnd && $tokens[ $afterEnd ]['code'] === T_COMMA );

			if ( $hasLeadingComma ) {
				// Include the leading comma and whitespace.
				$start = $beforeStart;
			} elseif ( $hasTrailingComma ) {
				// Include the trailing comma.
				$end = $afterEnd;
			}

			return array(
				'removeEntireArg'   => false,
				'start'             => $start,
				'end'               => $end,
				'hasLeadingComma'   => $hasLeadingComma,
				'hasTrailingComma'  => $hasTrailingComma,
				'echoKeyPos'        => $i,
				'trueValuePos'      => $value,
			);
		}

		return false;
	}

	/**
	 * Find the array that contains the given position.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $pos        Position inside the array.
	 * @param int  $openParen  The function's opening parenthesis.
	 * @param int  $closeParen The function's closing parenthesis.
	 *
	 * @return array Array with 'start' and 'end' positions.
	 */
	private function findContainingArray( File $phpcsFile, $pos, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		// Look backwards for array( or [.
		for ( $i = $pos - 1; $i > $openParen; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_OPEN_SHORT_ARRAY ) {
				// Short array syntax [].
				return array(
					'start' => $i,
					'end'   => $tokens[ $i ]['bracket_closer'],
				);
			}

			if ( $tokens[ $i ]['code'] === T_ARRAY ) {
				// Long array syntax array().
				$arrayOpen = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $i + 1, $closeParen );

				if ( false !== $arrayOpen ) {
					return array(
						'start' => $i,
						'end'   => $tokens[ $arrayOpen ]['parenthesis_closer'],
					);
				}
			}
		}

		return array(
			'start' => $openParen,
			'end'   => $closeParen,
		);
	}

	/**
	 * Check if 'echo' => true is the only element in the array.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $arrayInfo Array start/end info.
	 * @param int   $keyPos    Position of the 'echo' key.
	 * @param int   $valuePos  Position of the true value.
	 *
	 * @return bool
	 */
	private function isOnlyArrayElement( File $phpcsFile, $arrayInfo, $keyPos, $valuePos ) {
		$tokens = $phpcsFile->getTokens();

		// Determine the actual content start (after array( or [).
		$contentStart = $arrayInfo['start'] + 1;

		if ( $tokens[ $arrayInfo['start'] ]['code'] === T_ARRAY ) {
			$contentStart = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $arrayInfo['start'] + 1 ) + 1;
		}

		$contentEnd = $arrayInfo['end'] - 1;

		// Check if there are any other non-whitespace tokens besides 'echo' => true.
		for ( $i = $contentStart; $i <= $contentEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			// Skip the 'echo' => true tokens.
			if ( $i >= $keyPos && $i <= $valuePos ) {
				continue;
			}

			// Skip tokens between key and value (whitespace and =>).
			if ( $i > $keyPos && $i < $valuePos ) {
				continue;
			}

			// Found another token - not the only element.
			return false;
		}

		return true;
	}

	/**
	 * Apply the fix by removing the 'echo' => true argument.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $echoInfo  Information about the echo arg position.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $echoInfo ) {
		$tokens = $phpcsFile->getTokens();
		$phpcsFile->fixer->beginChangeset();

		if ( ! empty( $echoInfo['removeEntireArg'] ) ) {
			// Remove the entire second argument including the comma before it.
			$arrayStart = $echoInfo['arrayStart'];
			$arrayEnd   = $echoInfo['arrayEnd'];

			// Find the comma before the array.
			$comma = $phpcsFile->findPrevious( T_WHITESPACE, $arrayStart - 1, null, true );

			if ( false !== $comma && $tokens[ $comma ]['code'] === T_COMMA ) {
				// Remove from comma to end of array.
				for ( $i = $comma; $i <= $arrayEnd; $i++ ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			} else {
				// Just remove the array.
				for ( $i = $arrayStart; $i <= $arrayEnd; $i++ ) {
					$phpcsFile->fixer->replaceToken( $i, '' );
				}
			}
		} else {
			// Remove just the 'echo' => true element.
			for ( $i = $echoInfo['start']; $i <= $echoInfo['end']; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			// Also remove any whitespace after the removed content if we removed a trailing comma.
			if ( ! empty( $echoInfo['hasTrailingComma'] ) ) {
				$next = $echoInfo['end'] + 1;

				while ( isset( $tokens[ $next ] ) && $tokens[ $next ]['code'] === T_WHITESPACE ) {
					// Only remove if it's just spaces, not newlines that are part of formatting.
					if ( strpos( $tokens[ $next ]['content'], "\n" ) === false ) {
						$phpcsFile->fixer->replaceToken( $next, '' );
					}
					$next++;
				}
			}
		}

		$phpcsFile->fixer->endChangeset();
	}
}
