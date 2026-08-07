<?php
/**
 * Formidable_Sniffs_Security_UnescapedUrlInHtmlSniff
 *
 * Detects when URL attribute values (href, src, action, formaction) in
 * concatenated HTML strings are not wrapped in esc_url() or esc_url_raw().
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\Security;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects unescaped URL values in HTML attributes built via string concatenation.
 *
 * Bad:
 * '<a href="' . $url . '">'
 * '<img src="' . $url . '">'
 * '<form action="' . $url . '">'
 *
 * Good:
 * '<a href="' . esc_url( $url ) . '">'
 * '<img src="' . esc_url( $url ) . '">'
 * '<form action="' . esc_url( $url ) . '">'
 */
class UnescapedUrlInHtmlSniff implements Sniff {

	/**
	 * URL attributes to check.
	 *
	 * @var array
	 */
	private $urlAttributes = array(
		'href',
		'src',
		'action',
		'formaction',
		'poster',
		'data',
		'codebase',
		'cite',
		'background',
		'srcset',
	);

	/**
	 * Functions considered safe for URL escaping.
	 *
	 * @var array
	 */
	private $safeFunctions = array(
		'esc_url',
		'esc_url_raw',
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_CONSTANT_ENCAPSED_STRING );
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
		$string = $tokens[ $stackPtr ]['content'];

		// Check if this string ends with a URL attribute pattern.
		// Patterns: href="' or href="  (at the end of the string, before closing quote).
		$attribute = $this->getUrlAttributeAtEnd( $string );

		if ( false === $attribute ) {
			return;
		}

		// Find the concatenation operator after this string.
		$concatPtr = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $concatPtr || $tokens[ $concatPtr ]['code'] !== T_STRING_CONCAT ) {
			return;
		}

		// Find the expression after the concatenation.
		$exprStart = $phpcsFile->findNext( T_WHITESPACE, $concatPtr + 1, null, true );

		if ( false === $exprStart ) {
			return;
		}

		// Check if the expression is already wrapped in a safe function.
		if ( $this->isWrappedInSafeFunction( $phpcsFile, $exprStart ) ) {
			return;
		}

		// Find the end of the concatenated expression (up to the next . concat operator).
		$exprEnd = $this->findExpressionEnd( $phpcsFile, $exprStart );

		if ( false === $exprEnd ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'URL attribute "%s" value should be escaped with esc_url().',
			$exprStart,
			'Found',
			array( $attribute )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $exprStart, $exprEnd );
		}
	}

	/**
	 * Check if a string ends with a URL attribute assignment pattern.
	 *
	 * Matches patterns like: href="' or href=' or src="' etc.
	 * The string must end with an attribute name followed by =" or =' and then the closing string quote.
	 *
	 * @param string $string The string token content including quotes.
	 *
	 * @return false|string The attribute name if found, false otherwise.
	 */
	private function getUrlAttributeAtEnd( $string ) {
		// Remove the wrapping quotes from the string.
		$content = substr( $string, 1, -1 );

		// Normalize escaped quotes in the raw token content.
		$content = str_replace( array( '\"', "\\'" ), array( '"', "'" ), $content );

		// Build a regex pattern to match URL attributes at the end of the string.
		$attrPattern = implode( '|', $this->urlAttributes );

		// Match: attribute="  or attribute='  at the end of the string content.
		// The inner quote can be either " or ' (whichever is NOT the PHP string delimiter).
		// Use a word boundary or start-of-string/whitespace to avoid matching data-action as action.
		if ( preg_match( '/(?:^|\s)(' . $attrPattern . ')\s*=\s*["\']$/', $content, $matches ) ) {
			return $matches[1];
		}

		return false;
	}

	/**
	 * Check if an expression is wrapped in a safe URL escaping function.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $exprStart The position of the first token of the expression.
	 *
	 * @return bool True if the expression is wrapped in esc_url() or similar.
	 */
	private function isWrappedInSafeFunction( File $phpcsFile, $exprStart ) {
		$tokens = $phpcsFile->getTokens();

		if ( $tokens[ $exprStart ]['code'] !== T_STRING ) {
			return false;
		}

		return in_array( $tokens[ $exprStart ]['content'], $this->safeFunctions, true );
	}

	/**
	 * Find the end of the concatenated expression.
	 *
	 * Walks forward from the expression start, tracking parentheses depth,
	 * until it finds the next concatenation operator at depth 0.
	 * Returns the last token of the expression (before the concat operator).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $exprStart The position of the first token of the expression.
	 *
	 * @return false|int The position of the last token of the expression, or false.
	 */
	private function findExpressionEnd( File $phpcsFile, $exprStart ) {
		$tokens     = $phpcsFile->getTokens();
		$parenDepth = 0;
		$lastNonWs  = $exprStart;

		for ( $i = $exprStart; $i < $phpcsFile->numTokens; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_OPEN_PARENTHESIS ) {
				++$parenDepth;
				continue;
			}

			if ( $code === T_CLOSE_PARENTHESIS ) {
				--$parenDepth;

				if ( $parenDepth < 0 ) {
					return false;
				}

				$lastNonWs = $i;
				continue;
			}

			// At depth 0, a concatenation operator or semicolon ends the expression.
			if ( 0 === $parenDepth && ( $code === T_STRING_CONCAT || $code === T_SEMICOLON ) ) {
				return $lastNonWs;
			}

			if ( $code !== T_WHITESPACE ) {
				$lastNonWs = $i;
			}
		}

		return false;
	}

	/**
	 * Apply the fix by wrapping the expression in esc_url().
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $exprStart The position of the first token of the expression.
	 * @param int  $exprEnd   The position of the last token of the expression.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $exprStart, $exprEnd ) {
		$fixer = $phpcsFile->fixer;

		$fixer->beginChangeset();
		$fixer->addContentBefore( $exprStart, 'esc_url( ' );
		$fixer->addContent( $exprEnd, ' )' );
		$fixer->endChangeset();
	}
}
