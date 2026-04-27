<?php
/**
 * Formidable_Sniffs_CodeAnalysis_SimplifyRedundantFalsyCheckSniff
 *
 * Detects redundant falsy checks that can be simplified.
 * For example: $value == '' || ! $value can be simplified to just ! $value
 * since '' is falsy and covered by ! $value.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects redundant falsy checks.
 *
 * Bad:
 * if ($value == '' || ! $value) { }
 * if (! $value || $value == '') { }
 * if ($value === '' || ! $value) { }
 * if ($value == false || ! $value) { }
 * if ($value == 0 || ! $value) { }
 *
 * Good:
 * if (! $value) { }
 */
class SimplifyRedundantFalsyCheckSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_BOOLEAN_OR );
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

		// Find the condition boundaries (inside parentheses).
		$conditionStart = $this->findConditionStart( $phpcsFile, $stackPtr );
		$conditionEnd   = $this->findConditionEnd( $phpcsFile, $stackPtr );

		if ( false === $conditionStart || false === $conditionEnd ) {
			return;
		}

		// Parse the left side of ||.
		$leftSide = $this->parseConditionPart( $phpcsFile, $conditionStart, $stackPtr - 1 );

		// Parse the right side of ||.
		$rightSide = $this->parseConditionPart( $phpcsFile, $stackPtr + 1, $conditionEnd );

		if ( false === $leftSide || false === $rightSide ) {
			return;
		}

		// Check for pattern: $var == '' || ! $var (or similar falsy comparisons).
		$simplification = $this->findRedundantPattern( $leftSide, $rightSide );

		if ( false === $simplification ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Redundant falsy check. "%s" can be simplified to "%s".',
			$stackPtr,
			'Found',
			array( $simplification['original'], $simplification['simplified'] )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $conditionStart, $conditionEnd, $simplification['simplified'] );
		}
	}

	/**
	 * Find the start of the condition (after opening parenthesis).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the || token.
	 *
	 * @return false|int
	 */
	private function findConditionStart( File $phpcsFile, $stackPtr ) {
		$tokens     = $phpcsFile->getTokens();
		$parenDepth = 0;

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_CLOSE_PARENTHESIS ) {
				++$parenDepth;
				continue;
			}

			if ( $code === T_OPEN_PARENTHESIS ) {
				if ( $parenDepth > 0 ) {
					--$parenDepth;
					continue;
				}

				// Found the opening parenthesis.
				return $i + 1;
			}

			// Stop if we hit another || at the same level (compound condition).
			if ( $parenDepth === 0 && ( $code === T_BOOLEAN_OR || $code === T_BOOLEAN_AND ) ) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Find the end of the condition (before closing parenthesis).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the || token.
	 *
	 * @return false|int
	 */
	private function findConditionEnd( File $phpcsFile, $stackPtr ) {
		$tokens     = $phpcsFile->getTokens();
		$parenDepth = 0;

		for ( $i = $stackPtr + 1; $i < count( $tokens ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				if ( $parenDepth > 0 ) {
					--$parenDepth;
					continue;
				}

				// Found the closing parenthesis.
				return $i - 1;
			}

			// Stop if we hit another || at the same level (compound condition).
			if ( $parenDepth === 0 && ( $code === T_BOOLEAN_OR || $code === T_BOOLEAN_AND ) ) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Parse a condition part to extract variable and comparison info.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return array|false Array with 'type', 'variable', 'value' or false.
	 */
	private function parseConditionPart( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();

		// Skip whitespace at boundaries.
		$start = $phpcsFile->findNext( T_WHITESPACE, $start, $end + 1, true );
		$end   = $phpcsFile->findPrevious( T_WHITESPACE, $end, $start - 1, true );

		if ( false === $start || false === $end ) {
			return false;
		}

		// Check for ! $var pattern.
		if ( $tokens[ $start ]['code'] === T_BOOLEAN_NOT ) {
			$varToken = $phpcsFile->findNext( T_WHITESPACE, $start + 1, $end + 1, true );

			if ( false !== $varToken && $tokens[ $varToken ]['code'] === T_VARIABLE ) {
				// Make sure there's nothing after the variable.
				$afterVar = $phpcsFile->findNext( T_WHITESPACE, $varToken + 1, $end + 1, true );

				if ( false === $afterVar || $afterVar > $end ) {
					return array(
						'type'     => 'negation',
						'variable' => $tokens[ $varToken ]['content'],
						'raw'      => $this->getTokensContent( $phpcsFile, $start, $end ),
					);
				}
			}

			return false;
		}

		// Check for $var == '' or $var === '' or $var == false, etc.
		if ( $tokens[ $start ]['code'] === T_VARIABLE ) {
			$variable = $tokens[ $start ]['content'];

			// Find comparison operator.
			$opToken = $phpcsFile->findNext( T_WHITESPACE, $start + 1, $end + 1, true );

			if ( false === $opToken ) {
				return false;
			}

			$opCode = $tokens[ $opToken ]['code'];

			if ( ! in_array( $opCode, array( T_IS_EQUAL, T_IS_IDENTICAL ), true ) ) {
				return false;
			}

			// Find the value being compared.
			$valueToken = $phpcsFile->findNext( T_WHITESPACE, $opToken + 1, $end + 1, true );

			if ( false === $valueToken ) {
				return false;
			}

			// Check if it's a falsy value.
			$falsyValue = $this->isFalsyValue( $phpcsFile, $valueToken );

			if ( false === $falsyValue ) {
				return false;
			}

			// Make sure there's nothing after the value.
			$afterValue = $phpcsFile->findNext( T_WHITESPACE, $valueToken + 1, $end + 1, true );

			if ( false !== $afterValue && $afterValue <= $end ) {
				return false;
			}

			return array(
				'type'     => 'comparison',
				'variable' => $variable,
				'value'    => $falsyValue,
				'operator' => $tokens[ $opToken ]['content'],
				'raw'      => $this->getTokensContent( $phpcsFile, $start, $end ),
			);
		}

		return false;
	}

	/**
	 * Check if a token represents a falsy value.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $valueToken The token position.
	 *
	 * @return false|string The falsy value type or false.
	 */
	private function isFalsyValue( File $phpcsFile, $valueToken ) {
		$tokens = $phpcsFile->getTokens();
		$code   = $tokens[ $valueToken ]['code'];

		// Empty string ''.
		if ( $code === T_CONSTANT_ENCAPSED_STRING ) {
			$content = $tokens[ $valueToken ]['content'];

			if ( $content === "''" || $content === '""' ) {
				return 'empty_string';
			}

			return false;
		}

		// false.
		if ( $code === T_FALSE ) {
			return 'false';
		}

		// 0.
		if ( $code === T_LNUMBER && $tokens[ $valueToken ]['content'] === '0' ) {
			return 'zero';
		}

		// null.
		if ( $code === T_NULL ) {
			return 'null';
		}

		return false;
	}

	/**
	 * Find redundant pattern and return simplification info.
	 *
	 * @param array $leftSide  Left side of ||.
	 * @param array $rightSide Right side of ||.
	 *
	 * @return array|false Array with 'original' and 'simplified' or false.
	 */
	private function findRedundantPattern( $leftSide, $rightSide ) {
		// Pattern 1: $var == '' || ! $var.
		if ( $leftSide['type'] === 'comparison' && $rightSide['type'] === 'negation' ) {
			if ( $leftSide['variable'] === $rightSide['variable'] ) {
				return array(
					'original'   => $leftSide['raw'] . ' || ' . $rightSide['raw'],
					'simplified' => '! ' . $leftSide['variable'],
				);
			}
		}

		// Pattern 2: ! $var || $var == ''.
		if ( $leftSide['type'] === 'negation' && $rightSide['type'] === 'comparison' ) {
			if ( $leftSide['variable'] === $rightSide['variable'] ) {
				return array(
					'original'   => $leftSide['raw'] . ' || ' . $rightSide['raw'],
					'simplified' => '! ' . $rightSide['variable'],
				);
			}
		}

		return false;
	}

	/**
	 * Get the content of tokens between two positions.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return string
	 */
	private function getTokensContent( File $phpcsFile, $start, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';

		for ( $i = $start; $i <= $end; $i++ ) {
			$content .= $tokens[ $i ]['content'];
		}

		return trim( $content );
	}

	/**
	 * Apply the fix.
	 *
	 * @param File   $phpcsFile      The file being scanned.
	 * @param int    $conditionStart Start of the condition.
	 * @param int    $conditionEnd   End of the condition.
	 * @param string $simplified     The simplified condition.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $conditionStart, $conditionEnd, $simplified ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$fixer->beginChangeset();

		// Find actual start (skip leading whitespace).
		$actualStart = $phpcsFile->findNext( T_WHITESPACE, $conditionStart, $conditionEnd + 1, true );

		// Find actual end (skip trailing whitespace).
		$actualEnd = $phpcsFile->findPrevious( T_WHITESPACE, $conditionEnd, $actualStart - 1, true );

		// Remove all tokens in the condition.
		for ( $i = $actualStart; $i <= $actualEnd; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// Add the simplified condition.
		$fixer->addContent( $actualStart, $simplified );

		$fixer->endChangeset();
	}
}
