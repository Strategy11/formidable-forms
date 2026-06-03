<?php
/**
 * Sniff to detect FrmProHtmlHelper method calls that should use FrmHtmlHelper instead.
 *
 * FrmProHtmlHelper::admin_toggle is just a wrapper that calls the FrmHtmlHelper::toggle
 * equivalent. Using FrmHtmlHelper directly eliminates the overhead.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects and fixes FrmProHtmlHelper calls that should use FrmHtmlHelper.
 */
class PreferFrmHtmlHelperSniff implements Sniff {

	/**
	 * Methods that should be called on FrmHtmlHelper instead of FrmProHtmlHelper.
	 *
	 * @var array
	 */
	private $methods = array(
		'admin_toggle',
	);

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

		// Check if this is FrmProHtmlHelper::.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken || $tokens[ $prevToken ]['code'] !== T_STRING ) {
			return;
		}

		if ( $tokens[ $prevToken ]['content'] !== 'FrmProHtmlHelper' ) {
			return;
		}

		// Check the method name after ::.
		$methodToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $methodToken || $tokens[ $methodToken ]['code'] !== T_STRING ) {
			return;
		}

		$methodName = $tokens[ $methodToken ]['content'];

		if ( ! in_array( $methodName, $this->methods, true ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use FrmHtmlHelper::%s instead of FrmProHtmlHelper::%s to avoid wrapper overhead.',
			$prevToken,
			'UseHtmlHelper',
			array( $methodName, $methodName )
		);

		if ( $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $prevToken, 'FrmHtmlHelper' );
			$phpcsFile->fixer->replaceToken( $methodToken, 'toggle' );
			$phpcsFile->fixer->endChangeset();
		}
	}
}
