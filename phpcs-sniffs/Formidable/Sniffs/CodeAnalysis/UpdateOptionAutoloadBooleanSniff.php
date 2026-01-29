<?php
/**
 * Ensure update_option autoload flag uses booleans instead of strings.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Converts `update_option( ..., 'yes' )`/`'no'` autoload values to bool true/false.
 */
class UpdateOptionAutoloadBooleanSniff implements Sniff {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		return array( T_STRING );
	}

	/**
	 * {@inheritdoc}
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'update_option' ) {
			return;
		}

		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		$arguments = $this->getFunctionArguments( $phpcsFile, $openParen );

		if ( count( $arguments ) < 3 ) {
			return;
		}

		$autoloadArg = $arguments[2];
		$value       = $this->normalizeLiteral( $phpcsFile, $autoloadArg );

		if ( null === $value ) {
			return;
		}

		$replacement = ( $value === 'yes' ) ? 'true' : 'false';

		$fix = $phpcsFile->addFixableError(
			sprintf( 'update_option autoload flag should be boolean, found string \'%s\'.', $value ),
			$autoloadArg['start'],
			'AutoloadString'
		);

		if ( true === $fix ) {
			$this->replaceArgumentWithBoolean( $phpcsFile, $autoloadArg, $replacement );
		}
	}

	/**
	 * Convert a literal argument to yes/no string if applicable.
	 *
	 * @param File $phpcsFile File reference.
	 * @param array $argument Argument boundaries.
	 *
	 * @return string|null
	 */
	private function normalizeLiteral( File $phpcsFile, array $argument ) {
		$content = trim( $phpcsFile->getTokensAsString( $argument['start'], $argument['end'] - $argument['start'] + 1 ) );

		// Only match quoted strings.
		if ( strlen( $content ) < 2 || $content[0] !== $content[ strlen( $content ) - 1 ] || ( $content[0] !== '\'' && $content[0] !== '"' ) ) {
			return null;
		}

		$value = trim( substr( $content, 1, -1 ) );
		$value = strtolower( $value );

		if ( in_array( $value, array( 'yes', 'no' ), true ) ) {
			return $value;
		}

		return null;
	}

	/**
	 * Replace the argument tokens with the boolean literal.
	 *
	 * @param File  $phpcsFile  File reference.
	 * @param array $argument   Argument boundaries.
	 * @param string $replacement Replacement text.
	 *
	 * @return void
	 */
	private function replaceArgumentWithBoolean( File $phpcsFile, array $argument, $replacement ) {
		$fixer = $phpcsFile->fixer;
		$fixer->beginChangeset();

		for ( $i = $argument['start']; $i <= $argument['end']; $i++ ) {
			if ( $i === $argument['start'] ) {
				$fixer->replaceToken( $i, $replacement );
				continue;
			}

			$fixer->replaceToken( $i, '' );
		}

		$fixer->endChangeset();
	}

	/**
	 * Basic function argument parser.
	 *
	 * @param File $phpcsFile  File reference.
	 * @param int  $openParen  Position of the opening parenthesis.
	 *
	 * @return array<int, array{start:int,end:int}>
	 */
	private function getFunctionArguments( File $phpcsFile, $openParen ) {
		$tokens   = $phpcsFile->getTokens();
		$arguments = array();

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return $arguments;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$argStart   = null;

		$level = 0;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_OPEN_PARENTHESIS || $code === T_OPEN_SHORT_ARRAY || $code === T_OPEN_SQUARE_BRACKET ) {
				$level++;
			} elseif ( $code === T_CLOSE_PARENTHESIS || $code === T_CLOSE_SQUARE_BRACKET ) {
				$level--;
			}

			$nextIsSeparator = ( $code === T_COMMA && 0 === $level );

			if ( false === $argStart ) {
				continue;
			}

			if ( $nextIsSeparator || $i === $closeParen - 1 ) {
				$end = $nextIsSeparator ? $i - 1 : $i;

				while ( $end >= $argStart && $tokens[ $end ]['code'] === T_WHITESPACE ) {
					$end--;
				}

				if ( $argStart <= $end && $tokens[ $argStart ]['code'] !== T_COMMA ) {
					$arguments[] = array(
						'start' => $argStart,
						'end'   => $end,
					);
				}

				$argStart = null;
			} elseif ( null === $argStart && $tokens[ $i ]['code'] !== T_WHITESPACE ) {
				$argStart = $i;
			}
		}

		return $arguments;
	}
}
