<?php
/**
 * Sniff to detect ternaries returning opposite boolean literals from boolean conditions.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

declare(strict_types=1);

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\ConditionHelper;
use SlevomatCodingStandard\Helpers\TernaryOperatorHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;

/**
 * Detects and fixes ternaries returning opposite boolean literals from boolean conditions.
 */
class SimplifyBooleanTernarySniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_INLINE_THEN );
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
		$then   = TokenHelper::findNextEffective( $phpcsFile, $stackPtr + 1 );
		$colon  = null !== $then ? TokenHelper::findNextEffective( $phpcsFile, $then + 1 ) : null;
		$else   = null !== $colon ? TokenHelper::findNextEffective( $phpcsFile, $colon + 1 ) : null;

		if ( null === $else || ! in_array( $tokens[ $then ]['code'], array( T_TRUE, T_FALSE ), true ) || T_INLINE_ELSE !== $tokens[ $colon ]['code'] || ! in_array( $tokens[ $else ]['code'], array( T_TRUE, T_FALSE ), true ) || $tokens[ $then ]['code'] === $tokens[ $else ]['code'] ) {
			return;
		}
		$end          = TernaryOperatorHelper::getEndPointer( $phpcsFile, $stackPtr, $colon );
		$start        = TernaryOperatorHelper::getStartPointer( $phpcsFile, $stackPtr );
		$conditionEnd = TokenHelper::findPreviousEffective( $phpcsFile, $stackPtr - 1 );

		if ( $end !== $else || ! ConditionHelper::conditionReturnsBoolean( $phpcsFile, $start, $conditionEnd ) ) {
			return;
		}
		$condition = trim( $phpcsFile->getTokensAsString( $start, $conditionEnd - $start + 1 ) );

		if ( preg_match( '/\/\*|\/\/|#/', $phpcsFile->getTokensAsString( $start, $else - $start + 1 ) ) ) {
			return;
		}
		$replacement = T_TRUE === $tokens[ $then ]['code'] ? $condition : $this->addSpaceAfterNegation( ConditionHelper::getNegativeCondition( $phpcsFile, $start, $conditionEnd ) );

		if ( ! $this->isStandaloneExpression( $phpcsFile, $start, $else ) ) {
			$replacement = '( ' . $replacement . ' )';
		}
		$fix         = $phpcsFile->addFixableError( 'Replace the boolean ternary with its boolean condition.', $stackPtr, 'Found' );

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $start, $replacement );

			for ( $i = $start + 1; $i <= $else; ++$i ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Checks whether the ternary is a whole value, so the condition needs no wrapping parentheses.
	 *
	 * This covers the right side of an assignment, an array value, a return, and a function argument.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     The first token of the ternary.
	 * @param int  $end       The last token of the ternary.
	 *
	 * @return bool
	 */
	private function isStandaloneExpression( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();
		$before = TokenHelper::findPreviousEffective( $phpcsFile, $start - 1 );
		$after  = TokenHelper::findNextEffective( $phpcsFile, $end + 1 );

		if ( null === $before || null === $after ) {
			return false;
		}

		$validBefore = array( T_EQUAL, T_PLUS_EQUAL, T_MINUS_EQUAL, T_CONCAT_EQUAL, T_COALESCE_EQUAL, T_DOUBLE_ARROW, T_RETURN, T_COMMA, T_OPEN_PARENTHESIS );
		$validAfter  = array( T_SEMICOLON, T_COMMA, T_CLOSE_PARENTHESIS );

		return in_array( $tokens[ $before ]['code'], $validBefore, true ) && in_array( $tokens[ $after ]['code'], $validAfter, true );
	}

	/**
	 * Adds the space the WordPress standard requires after each logical not operator.
	 *
	 * @param string $code The negated condition.
	 *
	 * @return string
	 */
	private function addSpaceAfterNegation( $code ) {
		$parts  = token_get_all( '<?php ' . $code );
		$result = '';

		array_shift( $parts );

		foreach ( $parts as $index => $part ) {
			$text    = is_array( $part ) ? $part[1] : $part;
			$result .= $text;
			$next    = $parts[ $index + 1 ] ?? null;

			if ( '!' === $part && null !== $next && ! ( is_array( $next ) && T_WHITESPACE === $next[0] ) ) {
				$result .= ' ';
			}
		}
		return $result;
	}
}
