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
		$replacement = T_TRUE === $tokens[ $then ]['code'] ? '( ' . $condition . ' )' : '( ' . ConditionHelper::getNegativeCondition( $phpcsFile, $start, $conditionEnd ) . ' )';
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
}
