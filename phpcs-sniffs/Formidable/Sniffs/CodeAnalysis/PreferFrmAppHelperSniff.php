<?php
/**
 * Sniff to detect FrmProAppHelper method calls that should use FrmAppHelper instead.
 *
 * FrmProAppHelper::icon_by_class and FrmProAppHelper::unserialize_or_decode are just
 * wrappers that call the FrmAppHelper equivalents. Using FrmAppHelper directly
 * eliminates the overhead.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects and fixes FrmProAppHelper calls that should use FrmAppHelper.
 */
class PreferFrmAppHelperSniff implements Sniff {

	/**
	 * Methods that should be called on FrmAppHelper instead of FrmProAppHelper.
	 *
	 * @var array
	 */
	private $methods = array(
		'icon_by_class',
		'unserialize_or_decode',
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

		// Check if this is FrmProAppHelper::.
		$prevToken = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prevToken || $tokens[ $prevToken ]['code'] !== T_STRING ) {
			return;
		}

		if ( $tokens[ $prevToken ]['content'] !== 'FrmProAppHelper' ) {
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
			'Use FrmAppHelper::%s instead of FrmProAppHelper::%s to avoid wrapper overhead.',
			$prevToken,
			'UseAppHelper',
			array( $methodName, $methodName )
		);

		if ( $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $prevToken, 'FrmAppHelper' );
			$phpcsFile->fixer->endChangeset();
		}
	}
}
