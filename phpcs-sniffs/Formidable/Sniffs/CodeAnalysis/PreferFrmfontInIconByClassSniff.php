<?php
/**
 * Sniff to detect frm_icon_font usage in FrmAppHelper::icon_by_class calls.
 *
 * The frmfont class should be used instead of frm_icon_font when calling
 * FrmAppHelper::icon_by_class. This makes it easier to detect deprecated
 * frm_icon_font icons that don't use the helper method. It also reduces the
 * length of the code by using a shorter string.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects and fixes frm_icon_font in FrmAppHelper::icon_by_class calls.
 */
class PreferFrmfontInIconByClassSniff implements Sniff {

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

		// Check if this is FrmAppHelper::.
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

		// Find the first string argument inside the parentheses.
		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$firstArg   = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $firstArg || $tokens[ $firstArg ]['code'] !== T_CONSTANT_ENCAPSED_STRING ) {
			return;
		}

		$argContent = $tokens[ $firstArg ]['content'];

		if ( strpos( $argContent, 'frm_icon_font' ) === false ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use "frmfont" instead of "frm_icon_font" in FrmAppHelper::icon_by_class calls.',
			$firstArg,
			'UseFrmfont'
		);

		if ( $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken(
				$firstArg,
				str_replace( 'frm_icon_font', 'frmfont', $argContent )
			);
			$phpcsFile->fixer->endChangeset();
		}
	}
}
