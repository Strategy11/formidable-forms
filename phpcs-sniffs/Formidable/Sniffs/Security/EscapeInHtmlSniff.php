<?php
/**
 * Formidable_Sniffs_Security_EscapeInHtmlSniff
 *
 * Detects when translation functions are used within HTML strings without proper escaping.
 * Enforces esc_html__ for HTML content and esc_attr__ for HTML attributes.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\Security;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects unescaped translations in HTML strings.
 *
 * Bad:
 * $html = '<div>' . __('text', 'domain') . '</div>';
 * $html = '<div class="' . __('class', 'domain') . '">';
 *
 * Good:
 * $html = '<div>' . esc_html__('text', 'domain') . '</div>';
 * $html = '<div class="' . esc_attr__('class', 'domain') . '">';
 */
class EscapeInHtmlSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING_CONCAT );
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

		// Look for patterns: HTML string . __('text', 'domain') OR __('text', 'domain') . HTML string
		$leftSide  = $this->getLeftSideToken( $phpcsFile, $stackPtr );
		$rightSide = $this->getRightSideToken( $phpcsFile, $stackPtr );

		if ( false === $leftSide || false === $rightSide ) {
			return;
		}

		$leftIsTranslation  = $this->isTranslationFunction( $phpcsFile, $leftSide );
		$rightIsTranslation = $this->isTranslationFunction( $phpcsFile, $rightSide );

		// Check if either side is a translation function and the other side contains HTML
		if ( $leftIsTranslation && $this->containsHtml( $phpcsFile, $rightSide ) ) {
			// Pattern: __('text') . HTML string
			$context      = $this->getHtmlContext( $phpcsFile, $rightSide );
			$functionName = $this->getFunctionName( $phpcsFile, $leftSide );
			$targetToken  = $leftSide;
		} elseif ( $rightIsTranslation && $this->containsHtml( $phpcsFile, $leftSide ) ) {
			// Pattern: HTML string . __('text')
			$context      = $this->getHtmlContext( $phpcsFile, $leftSide );
			$functionName = $this->getFunctionName( $phpcsFile, $rightSide );
			$targetToken  = $rightSide;
		} else {
			return;
		}

		
		$expectedFunc = $this->getExpectedFunction( $context );

		if ( $functionName !== $expectedFunc ) {
			$fix = $phpcsFile->addFixableError(
				'Use %s() instead of %s() when including translations in %s.',
				$targetToken,
				'UnescapedTranslation',
				array( $expectedFunc, $functionName, $context )
			);

			if ( true === $fix ) {
				$this->applyFix( $phpcsFile, $targetToken, $functionName, $expectedFunc );
			}
		}
	}

	/**
	 * Get the token on the left side of a concatenation.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the concatenation token.
	 *
	 * @return false|int Token position or false if not found.
	 */
	private function getLeftSideToken( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the previous non-whitespace token
		return $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
	}

	/**
	 * Get the token on the right side of a concatenation.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the concatenation token.
	 *
	 * @return false|int Token position or false if not found.
	 */
	private function getRightSideToken( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the next non-whitespace token
		return $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
	}

	/**
	 * Check if a token represents a translation function call.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the token to check.
	 *
	 * @return bool True if this is a translation function.
	 */
	private function isTranslationFunction( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Look for __() or _x() function calls
		if ( $tokens[ $stackPtr ]['code'] !== T_STRING ) {
			return false;
		}

		$functionName = $tokens[ $stackPtr ]['content'];
		return in_array( $functionName, array( '__', '_x', '_n', '_nx' ), true );
	}

	/**
	 * Check if a token contains HTML.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the token to check.
	 *
	 * @return bool True if the token contains HTML.
	 */
	private function containsHtml( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$token  = $tokens[ $stackPtr ];

		// Check if it's a string containing HTML tags or HTML fragments
		if ( $token['code'] === T_CONSTANT_ENCAPSED_STRING ) {
			$content = $token['content'];
			// Remove quotes and check for HTML tags or HTML fragments
			$content = trim( $content, "'\"" );
			return preg_match( '/<[^>]*>/', $content ) > 0 || preg_match( '/>/', $content ) > 0 || preg_match( '/</', $content ) > 0;
		}

		// Also check if it's a function call that might return HTML
		if ( $token['code'] === T_STRING ) {
			// Check if this is a function that returns HTML
			$htmlFunctions = array( 'esc_url', 'esc_html', 'esc_attr' );
			return in_array( $token['content'], $htmlFunctions, true );
		}

		// Also check if it's a variable that might contain HTML
		if ( $token['code'] === T_VARIABLE ) {
			// Look backwards to see if this variable was assigned HTML
			return $this->checkVariableForHtml( $phpcsFile, $stackPtr );
		}

		return false;
	}

	/**
	 * Determine the HTML context (attribute vs content).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the HTML token.
	 *
	 * @return string 'attribute' or 'content'
	 */
	private function getHtmlContext( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$token  = $tokens[ $stackPtr ];

		if ( $token['code'] !== T_CONSTANT_ENCAPSED_STRING ) {
			return 'content'; // Default to content for variables
		}

		$content = trim( $token['content'], "'\"" );

		// Check for attribute patterns: class=", href=", etc.
		// This should match things like: class="value" or href="value"
		if ( preg_match( '/^\w+\s*=\s*["\'][^"\']*["\']?$/', $content ) ) {
			return 'attribute';
		}

		// Special case: screen-reader-text spans are always content, even if they have attributes
		if ( strpos( $content, 'screen-reader-text' ) !== false ) {
			return 'content';
		}

		// Check if we're inside an opening HTML tag but NOT in a closing tag or content
		// This should match things like: <div class="value"> but NOT <span>content</span>
		if ( preg_match( '/^<[^\/>][^>]*["\'][^"\']*["\']?$/', $content ) ) {
			return 'attribute';
		}

		// Check if this looks like the start of HTML content (opening tag followed by >)
		if ( preg_match( '/^<[^>]+>/', $content ) ) {
			return 'content';
		}

		// Check if this looks like HTML content (between > and <)
		if ( preg_match( '/>/', $content ) && ! preg_match( '/<[^\/]*$/', $content ) ) {
			return 'content';
		}

		return 'content';
	}

	/**
	 * Get the function name from a function call token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the function call.
	 *
	 * @return string The function name.
	 */
	private function getFunctionName( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		return $tokens[ $stackPtr ]['content'];
	}

	/**
	 * Get the expected escaping function based on context.
	 *
	 * @param string $context The HTML context.
	 *
	 * @return string The expected function name.
	 */
	private function getExpectedFunction( $context ) {
		if ( $context === 'attribute' ) {
			return 'esc_attr__';
		}
		return 'esc_html__';
	}

	/**
	 * Apply the fix to replace the function name.
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param int    $stackPtr      The position of the function call.
	 * @param string $oldFunction   The old function name.
	 * @param string $newFunction   The new function name.
	 *
	 * @return void
	 */
	private function applyFix( File $phpcsFile, $stackPtr, $oldFunction, $newFunction ) {
		$fixer = $phpcsFile->fixer;
		$fixer->replaceToken( $stackPtr, $newFunction );
	}

	/**
	 * Check if a variable was assigned HTML content.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the variable.
	 *
	 * @return bool True if the variable contains HTML.
	 */
	private function checkVariableForHtml( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$varName = $tokens[ $stackPtr ]['content'];

		// Look backwards for assignment to this variable
		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE && $tokens[ $i ]['content'] === $varName ) {
				// Found another usage of this variable, continue looking
				continue;
			}

			if ( $tokens[ $i ]['code'] === T_EQUAL ) {
				// Found assignment, check what's being assigned
				$next = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

				if ( false !== $next && $this->containsHtml( $phpcsFile, $next ) ) {
					return true;
				}
			}

			// Stop at semicolon (end of statement)
			if ( $tokens[ $i ]['code'] === T_SEMICOLON ) {
				break;
			}
		}

		return false;
	}
}
