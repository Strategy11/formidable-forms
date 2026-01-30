<?php
/**
 * Promote guard clauses when else block only returns.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Converts patterns like:
 *
 * if ( $condition ) {
 *     // logic...
 * } else {
 *     return $value;
 * }
 *
 * Into:
 *
 * if ( ! $condition ) {
 *     return $value;
 * }
 *
 * // logic...
 */
class PreferGuardClauseForReturnElseSniff implements Sniff {

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		return array( T_IF );
	}

	/**
	 * {@inheritDoc}
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'], $tokens[ $stackPtr ]['scope_closer'], $tokens[ $stackPtr ]['parenthesis_opener'], $tokens[ $stackPtr ]['parenthesis_closer'] ) ) {
			return;
		}

		// Skip elseif blocks.
		$previous = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $previous && $tokens[ $previous ]['code'] === T_ELSEIF ) {
			return;
		}

		$ifCloser = $tokens[ $stackPtr ]['scope_closer'];
		$elsePtr  = $phpcsFile->findNext( T_WHITESPACE, $ifCloser + 1, null, true );

		if ( false === $elsePtr || $tokens[ $elsePtr ]['code'] !== T_ELSE ) {
			return;
		}

		// Ignore elseif (single token) usage.
		$afterElse = $phpcsFile->findNext( T_WHITESPACE, $elsePtr + 1, null, true );

		if ( false !== $afterElse && $tokens[ $afterElse ]['code'] === T_IF ) {
			return;
		}

		if ( ! isset( $tokens[ $elsePtr ]['scope_opener'], $tokens[ $elsePtr ]['scope_closer'] ) ) {
			return;
		}

		$elseOpener = $tokens[ $elsePtr ]['scope_opener'];
		$elseCloser = $tokens[ $elsePtr ]['scope_closer'];

		$returnPtr = $phpcsFile->findNext( T_WHITESPACE, $elseOpener + 1, $elseCloser, true );

		if ( false === $returnPtr || $tokens[ $returnPtr ]['code'] !== T_RETURN ) {
			return;
		}

		$semicolonPtr = $phpcsFile->findNext( T_SEMICOLON, $returnPtr + 1, $elseCloser );

		if ( false === $semicolonPtr ) {
			return;
		}

		$afterReturn = $phpcsFile->findNext(
			array(
				T_WHITESPACE,
				T_COMMENT,
				T_DOC_COMMENT,
				T_DOC_COMMENT_OPEN_TAG,
				T_DOC_COMMENT_CLOSE_TAG,
				T_DOC_COMMENT_STAR,
				T_DOC_COMMENT_STRING,
				T_DOC_COMMENT_TAG,
				T_DOC_COMMENT_WHITESPACE,
			),
			$semicolonPtr + 1,
			$elseCloser,
			true
		);

		if ( false !== $afterReturn ) {
			return;
		}

		$condition = $this->getConditionString( $phpcsFile, $stackPtr );
		$indent    = $this->getIndentation( $phpcsFile, $stackPtr );
		$ifBody    = $this->getScopeBody( $phpcsFile, $tokens[ $stackPtr ]['scope_opener'], $tokens[ $stackPtr ]['scope_closer'] );
		$ifBody    = $this->dedentCode( $ifBody, $phpcsFile->eolChar );
		$ifBody    = $this->trimEmptyEdges( $ifBody, $phpcsFile->eolChar );
		$ifBody    = $this->indentCode( $ifBody, $indent, $phpcsFile->eolChar );

		$returnStatement = trim( $phpcsFile->getTokensAsString( $returnPtr, $semicolonPtr - $returnPtr + 1 ) );

		if ( '' === $returnStatement ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Prefer a guard clause that returns early when the condition fails instead of using an else block that only returns.',
			$stackPtr,
			'PreferGuardClause'
		);

		if ( true !== $fix ) {
			return;
		}

		$negatedCondition = $this->negateCondition( $condition );
		$newCode          = 'if ( ' . $negatedCondition . ' ) {' . $phpcsFile->eolChar;
		$newCode         .= $indent . "\t" . $returnStatement . $phpcsFile->eolChar;
		$newCode         .= $indent . '}' . $phpcsFile->eolChar . $phpcsFile->eolChar;
		$newCode         .= $ifBody;

		$phpcsFile->fixer->beginChangeset();

		for ( $i = $stackPtr; $i <= $elseCloser; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		$phpcsFile->fixer->addContent( $stackPtr, $newCode );

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Fetch the code inside a scoped block (without braces).
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $scopeOpener Pointer to the scope opener.
	 * @param int  $scopeCloser Pointer to the scope closer.
	 *
	 * @return string
	 */
	private function getScopeBody( File $phpcsFile, $scopeOpener, $scopeCloser ) {
		if ( $scopeCloser <= $scopeOpener + 1 ) {
			return '';
		}

		return $phpcsFile->getTokensAsString( $scopeOpener + 1, $scopeCloser - $scopeOpener - 1 );
	}

	/**
	 * Remove empty lines at the start or end of a code block.
	 *
	 * @param string $code    Code block.
	 * @param string $eolChar End of line character.
	 *
	 * @return string
	 */
	private function trimEmptyEdges( $code, $eolChar ) {
		$normalized = str_replace( array( "\r\n", "\r" ), "\n", $code );
		$lines      = explode( "\n", $normalized );

		while ( ! empty( $lines ) && '' === trim( reset( $lines ) ) ) {
			array_shift( $lines );
		}

		while ( ! empty( $lines ) && '' === trim( end( $lines ) ) ) {
			array_pop( $lines );
		}

		$joined = implode( "\n", $lines );

		return str_replace( "\n", $eolChar, $joined );
	}

	/**
	 * Remove one level of indentation from a block of code.
	 *
	 * @param string $code    The code to dedent.
	 * @param string $eolChar End of line character.
	 *
	 * @return string
	 */
	private function dedentCode( $code, $eolChar ) {
		$normalized = str_replace( array( "\r\n", "\r" ), "\n", $code );
		$lines      = explode( "\n", $normalized );

		foreach ( $lines as &$line ) {
			if ( strpos( $line, "\t" ) === 0 ) {
				$line = substr( $line, 1 );
			} elseif ( strpos( $line, '    ' ) === 0 ) {
				$line = substr( $line, 4 );
			}
		}

		$dedented = implode( "\n", $lines );

		return str_replace( "\n", $eolChar, $dedented );
	}

	/**
	 * Add indentation back to a code block.
	 *
	 * @param string $code    The code to indent.
	 * @param string $indent  Indentation prefix.
	 * @param string $eolChar End of line character.
	 *
	 * @return string
	 */
	private function indentCode( $code, $indent, $eolChar ) {
		if ( '' === trim( $code ) ) {
			return '';
		}

		$normalized = str_replace( array( "\r\n", "\r" ), "\n", $code );
		$lines      = explode( "\n", $normalized );

		foreach ( $lines as &$line ) {
			if ( '' === $line ) {
				$line = $indent . $line;
			} else {
				$line = $indent . $line;
			}
		}

		$indented = implode( "\n", $lines );

		return str_replace( "\n", $eolChar, $indented );
	}

	/**
	 * Get indentation string for a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Token pointer.
	 *
	 * @return string
	 */
	private function getIndentation( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$line   = $tokens[ $stackPtr ]['line'];

		for ( $ptr = $stackPtr; $ptr >= 0; $ptr-- ) {
			if ( $tokens[ $ptr ]['line'] !== $line ) {
				++$ptr;
				break;
			}
		}

		if ( $ptr < 0 ) {
			$ptr = 0;
		}

		return ( $tokens[ $ptr ]['code'] === T_WHITESPACE ) ? $tokens[ $ptr ]['content'] : '';
	}

	/**
	 * Extract the condition string from an if statement.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $ifPtr     Pointer to the if token.
	 *
	 * @return string
	 */
	private function getConditionString( File $phpcsFile, $ifPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $ifPtr ]['parenthesis_opener'], $tokens[ $ifPtr ]['parenthesis_closer'] ) ) {
			return '...';
		}

		$condition = '';

		for ( $i = $tokens[ $ifPtr ]['parenthesis_opener'] + 1; $i < $tokens[ $ifPtr ]['parenthesis_closer']; $i++ ) {
			$condition .= $tokens[ $i ]['content'];
		}

		$condition = trim( $condition );

		if ( strlen( $condition ) > 80 ) {
			$condition = substr( $condition, 0, 77 ) . '...';
		}

		return $condition;
	}

	/**
	 * Negate a condition string.
	 *
	 * @param string $condition Original condition.
	 *
	 * @return string
	 */
	private function negateCondition( $condition ) {
		$condition = trim( $condition );

		if ( '' === $condition ) {
			return '! ( ' . $condition . ' )';
		}

		if ( $this->hasTopLevelOperator( $condition ) ) {
			return '! ( ' . $condition . ' )';
		}

		if ( 0 === strpos( $condition, '! ' ) ) {
			return substr( $condition, 2 );
		}

		if ( 0 === strpos( $condition, '!' ) && 0 !== strpos( $condition, '!=' ) ) {
			return substr( $condition, 1 );
		}

		$flipped = $this->flipComparisonOperator( $condition );

		if ( false !== $flipped ) {
			return $flipped;
		}

		return '! ' . $condition;
	}

	/**
	 * Determine if a condition has top-level && or || operators.
	 *
	 * @param string $condition Condition string.
	 *
	 * @return bool
	 */
	private function hasTopLevelOperator( $condition ) {
		$depth = 0;
		$len   = strlen( $condition );

		for ( $i = 0; $i < $len; $i++ ) {
			$char = $condition[ $i ];

			if ( '(' === $char ) {
				++$depth;
				continue;
			}

			if ( ')' === $char ) {
				--$depth;
				continue;
			}

			if ( 0 !== $depth ) {
				continue;
			}

			if ( $i < $len - 1 ) {
				$pair = $condition[ $i ] . $condition[ $i + 1 ];

				if ( '&&' === $pair || '||' === $pair ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Attempt to flip a comparison operator for negation.
	 *
	 * @param string $condition Condition string.
	 *
	 * @return string|false
	 */
	private function flipComparisonOperator( $condition ) {
		$map = array(
			'!==' => '===',
			'===' => '!==',
			'!='  => '==',
			'=='  => '!=',
			'>='  => '<',
			'<='  => '>',
		);

		foreach ( $map as $operator => $replacement ) {
			$pos = strpos( $condition, $operator );

			if ( false !== $pos ) {
				return substr( $condition, 0, $pos ) . $replacement . substr( $condition, $pos + strlen( $operator ) );
			}
		}

		$pos = $this->findStandaloneComparison( $condition, '>' );

		if ( false !== $pos ) {
			return substr( $condition, 0, $pos ) . '<=' . substr( $condition, $pos + 1 );
		}

		$pos = $this->findStandaloneComparison( $condition, '<' );

		if ( false !== $pos ) {
			return substr( $condition, 0, $pos ) . '>=' . substr( $condition, $pos + 1 );
		}

		return false;
	}

	/**
	 * Find comparison operator positions ignoring -> or => constructs.
	 *
	 * @param string $condition Condition string.
	 * @param string $operator  Operator to match.
	 *
	 * @return int|false
	 */
	private function findStandaloneComparison( $condition, $operator ) {
		$pos = 0;

		while ( ( $pos = strpos( $condition, $operator, $pos ) ) !== false ) {
			$before = ( $pos > 0 ) ? $condition[ $pos - 1 ] : '';
			$after  = ( $pos < strlen( $condition ) - 1 ) ? $condition[ $pos + 1 ] : '';

			if ( in_array( $before, array( '-', '=', '!', '<', '>' ), true ) || '=' === $after || '>' === $after ) {
				++$pos;
				continue;
			}

			return $pos;
		}

		return false;
	}
}
