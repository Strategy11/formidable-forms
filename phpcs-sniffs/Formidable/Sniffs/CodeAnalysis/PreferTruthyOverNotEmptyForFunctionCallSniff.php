<?php
/**
 * Formidable_Sniffs_CodeAnalysis_PreferTruthyOverNotEmptyForFunctionCallSniff
 *
 * Detects "! empty( function_call() )" patterns and recommends using the
 * function result directly because it is inherently "set".
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Encourage direct truthy checks for function return values.
 */
class PreferTruthyOverNotEmptyForFunctionCallSniff implements Sniff {

	/** @inheritDoc */
	public function register() {
		return array( T_EMPTY );
	}

	/** @inheritDoc */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$notToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $notToken || T_BOOLEAN_NOT !== $tokens[ $notToken ]['code'] ) {
			return;
		}

		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] ) {
			return;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$innerStart = $openParen + 1;
		$innerEnd   = $closeParen - 1;

		$firstInner = $phpcsFile->findNext( T_WHITESPACE, $innerStart, $closeParen, true );

		if ( false === $firstInner ) {
			return;
		}

		$lastInner = $phpcsFile->findPrevious( T_WHITESPACE, $innerEnd, $firstInner - 1, true );

		if ( false === $lastInner ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $firstInner, $lastInner ) ) {
			return;
		}

		if ( $this->endsWithArrayAccess( $phpcsFile, $firstInner, $lastInner ) ) {
			return;
		}

		if ( ! $this->isInBooleanExpression( $phpcsFile, $stackPtr, $closeParen ) ) {
			return;
		}

		$expressionContent = $this->getContentBetween( $phpcsFile, $firstInner, $lastInner );

		if ( '' === trim( $expressionContent ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use the function return value directly instead of "! empty()" when checking truthiness.',
			$stackPtr,
			'PreferTruthyOverNotEmpty',
			array( $expressionContent )
		);

		if ( true !== $fix ) {
			return;
		}

		$phpcsFile->fixer->beginChangeset();

		// Remove "! empty(" and any whitespace after opening paren.
		for ( $i = $notToken; $i <= $openParen; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Remove whitespace between opening paren and first inner token.
		for ( $i = $openParen + 1; $i < $firstInner; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Remove whitespace between last inner token and closing paren.
		for ( $i = $lastInner + 1; $i < $closeParen; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		$phpcsFile->fixer->replaceToken( $closeParen, '' );

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Check if the inner expression is a function or method call result.
	 *
	 * @param File $phpcsFile File being scanned.
	 * @param int  $start     First non-whitespace token inside empty().
	 * @param int  $end       Last non-whitespace token inside empty().
	 *
	 * @return bool
	 */
	private function isFunctionCall( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();

		// Plain function_name( ... )
		if ( $tokens[ $start ]['code'] === T_STRING ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );

			if ( false !== $next && T_OPEN_PARENTHESIS === $tokens[ $next ]['code'] ) {
				return true;
			}
		}

		// Static method call Class::method(
		if ( in_array( $tokens[ $start ]['code'], array( T_STRING, T_SELF, T_STATIC, T_PARENT ), true ) ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $start + 1, null, true );

			if ( false !== $next && T_DOUBLE_COLON === $tokens[ $next ]['code'] ) {
				$method = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );

				if ( false !== $method && T_STRING === $tokens[ $method ]['code'] ) {
					$openParen = $phpcsFile->findNext( T_WHITESPACE, $method + 1, null, true );

					if ( false !== $openParen && T_OPEN_PARENTHESIS === $tokens[ $openParen ]['code'] ) {
						return $this->doesCallEndBefore( $phpcsFile, $openParen, $end );
					}
				}
			}
		}

		// Object method call $obj->method(
		if ( T_VARIABLE === $tokens[ $start ]['code'] ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $start + 1, $end + 1, true );

			if ( false !== $next && T_OBJECT_OPERATOR === $tokens[ $next ]['code'] ) {
				return $this->isMethodCallChain( $phpcsFile, $next, $end );
			}
		}

		return false;
	}

	/**
	 * Detect trailing array access (function()['key']).
	 *
	 * @param File $phpcsFile File being scanned.
	 * @param int  $start     Start of the expression.
	 * @param int  $end       End of the expression.
	 *
	 * @return bool
	 */
	private function endsWithArrayAccess( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();
		$last   = $end;

		while ( $last > $start && T_WHITESPACE === $tokens[ $last ]['code'] ) {
			--$last;
		}

		return T_CLOSE_SQUARE_BRACKET === $tokens[ $last ]['code'];
	}

	/**
	 * Determine whether an object operator chain ends with a method call.
	 *
	 * @param File $phpcsFile      File being scanned.
	 * @param int  $objectOperator Position of the T_OBJECT_OPERATOR token.
	 * @param int  $end            Expression end.
	 *
	 * @return bool
	 */
	private function isMethodCallChain( File $phpcsFile, $objectOperator, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$current = $objectOperator;

		while ( $current <= $end ) {
			$memberName = $phpcsFile->findNext( T_WHITESPACE, $current + 1, $end + 1, true );

			if ( false === $memberName || T_STRING !== $tokens[ $memberName ]['code'] ) {
				return false;
			}

			$afterMember = $phpcsFile->findNext( T_WHITESPACE, $memberName + 1, $end + 1, true );

			if ( false === $afterMember ) {
				return false;
			}

			if ( T_OPEN_PARENTHESIS === $tokens[ $afterMember ]['code'] ) {
				return $this->doesCallEndBefore( $phpcsFile, $afterMember, $end );
			}

			if ( T_OBJECT_OPERATOR === $tokens[ $afterMember ]['code'] ) {
				$current = $afterMember;
				continue;
			}

			return false;
		}

		return false;
	}

	/**
	 * Ensure the method/function call closes before $end without extra accessors.
	 *
	 * @param File $phpcsFile File being scanned.
	 * @param int  $openParen Position of the opening parenthesis of the call.
	 * @param int  $end       Expression end token.
	 *
	 * @return bool
	 */
	private function doesCallEndBefore( File $phpcsFile, $openParen, $end ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$afterCall  = $phpcsFile->findNext( T_WHITESPACE, $closeParen + 1, $end + 1, true );

		if ( false === $afterCall ) {
			return true;
		}

		return in_array( $tokens[ $afterCall ]['code'], array( T_SEMICOLON, T_BOOLEAN_AND, T_BOOLEAN_OR, T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET, T_COMMA, T_INLINE_THEN ), true );
	}

	/**
	 * Collect the original expression content for messages/analysis.
	 *
	 * @param File $phpcsFile File being scanned.
	 * @param int  $start     Start position.
	 * @param int  $end       End position.
	 *
	 * @return string
	 */
	private function getContentBetween( File $phpcsFile, $start, $end ) {
		$tokens  = $phpcsFile->getTokens();
		$content = '';

		for ( $i = $start; $i <= $end; $i++ ) {
			$content .= $tokens[ $i ]['content'];
		}

		return rtrim( $content );
	}

	/**
	 * Determine if the empty() call is part of a boolean expression (|| / &&).
	 *
	 * @param File $phpcsFile File being scanned.
	 * @param int  $stackPtr  Position of the T_EMPTY token.
	 *
	 * @return bool
	 */
	private function isInBooleanExpression( File $phpcsFile, $stackPtr, $emptyCloseParen ) {
		$tokens = $phpcsFile->getTokens();

		// Look backwards for || or && before hitting statement boundary.
		$prev = $stackPtr;

		while ( $prev > 0 ) {
			$prev = $phpcsFile->findPrevious( T_WHITESPACE, $prev - 1, null, true );

			if ( false === $prev ) {
				break;
			}

			if ( in_array( $tokens[ $prev ]['code'], array( T_BOOLEAN_OR, T_BOOLEAN_AND ), true ) ) {
				return true;
			}

			// Stop at statement boundaries but allow = (assignment).
			if ( in_array( $tokens[ $prev ]['code'], array( T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_COLON ), true ) ) {
				break;
			}

			// Stop at open parenthesis unless it's preceded by if/while/elseif.
			if ( T_OPEN_PARENTHESIS === $tokens[ $prev ]['code'] ) {
				$beforeParen = $phpcsFile->findPrevious( T_WHITESPACE, $prev - 1, null, true );

				if ( false !== $beforeParen && in_array( $tokens[ $beforeParen ]['code'], array( T_IF, T_ELSEIF, T_WHILE ), true ) ) {
					// Continue scanning - we're inside a condition.
					continue;
				}

				break;
			}
		}

		// Look forwards starting AFTER the empty() closing paren.
		$next = $emptyCloseParen;
		$tokenCount = count( $tokens );

		while ( $next < ( $tokenCount - 1 ) ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );

			if ( false === $next ) {
				break;
			}

			if ( in_array( $tokens[ $next ]['code'], array( T_BOOLEAN_OR, T_BOOLEAN_AND ), true ) ) {
				return true;
			}

			if ( in_array( $tokens[ $next ]['code'], array( T_SEMICOLON, T_CLOSE_PARENTHESIS, T_CLOSE_CURLY_BRACKET ), true ) ) {
				break;
			}
		}

		return false;
	}
}
