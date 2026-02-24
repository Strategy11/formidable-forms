<?php
/**
 * Formidable_Sniffs_Security_EscAttrInHtmlContentSniff
 *
 * Detects when esc_attr() is used in HTML text content (between tags) where
 * esc_html() should be used instead. Only applies to inline PHP in template files.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\Security;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects esc_attr() used outside of HTML attribute values.
 *
 * In PHP template files, esc_attr() is for attribute values and esc_html() is
 * for text content between HTML tags.
 *
 * Bad:
 * <option value="<?php echo esc_attr( $id ); ?>">
 *     <?php echo esc_attr( $name ); ?>
 * </option>
 *
 * Good:
 * <option value="<?php echo esc_attr( $id ); ?>">
 *     <?php echo esc_html( $name ); ?>
 * </option>
 */
class EscAttrInHtmlContentSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING );
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

		if ( $tokens[ $stackPtr ]['content'] !== 'esc_attr' ) {
			return;
		}

		// Confirm this is a function call (followed by open parenthesis).
		$nextNonWs = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		if ( false === $nextNonWs || $tokens[ $nextNonWs ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Confirm this is preceded by echo (template output context).
		if ( ! $this->isEchoContext( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Find the preceding inline HTML to determine the HTML context.
		$htmlContext = $this->getHtmlContextFromInlineHtml( $phpcsFile, $stackPtr );

		if ( 'content' !== $htmlContext ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use esc_html() instead of esc_attr() when outputting text content between HTML tags.',
			$stackPtr,
			'Found'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->replaceToken( $stackPtr, 'esc_html' );
		}
	}

	/**
	 * Check if the esc_attr call is in a direct template echo context.
	 *
	 * This must be a simple inline PHP echo: <?php echo esc_attr(...); ?>
	 * The echo must be the first statement after the open tag, and
	 * there must be inline HTML directly before the open tag.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the esc_attr token.
	 *
	 * @return bool True if this is a direct template echo.
	 */
	private function isEchoContext( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Must be preceded by echo.
		$echoPtr = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $echoPtr || $tokens[ $echoPtr ]['code'] !== T_ECHO ) {
			return false;
		}

		// The echo must be the first statement after a <?php open tag.
		$openTagPtr = $phpcsFile->findPrevious( T_WHITESPACE, $echoPtr - 1, null, true );

		if ( false === $openTagPtr || $tokens[ $openTagPtr ]['code'] !== T_OPEN_TAG ) {
			return false;
		}

		return true;
	}

	/**
	 * Determine the HTML context by collecting preceding inline HTML fragments.
	 *
	 * Since inline HTML between PHP blocks can be split across multiple
	 * T_INLINE_HTML tokens (e.g., `<select name="<?php ... ?>" id="<?php ...`),
	 * we must collect all preceding fragments back to the last complete tag
	 * boundary to correctly determine if we are in an attribute or text content.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return string 'attribute', 'content', or 'unknown'.
	 */
	private function getHtmlContextFromInlineHtml( File $phpcsFile, $stackPtr ) {
		$tokens    = $phpcsFile->getTokens();
		$fragments = array();

		// Since isEchoContext guarantees: <?php echo esc_attr(
		// Find the open tag, then collect inline HTML fragments backwards.
		$echoPtr    = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		$openTagPtr = $phpcsFile->findPrevious( T_WHITESPACE, $echoPtr - 1, null, true );

		// Collect inline HTML fragments walking backwards from the open tag.
		// Between inline HTML fragments there may be full PHP blocks.
		// For example: name="<?php echo esc_attr( $x ); ?>" id="<?php
		// We skip over those PHP blocks to join the HTML fragments.
		// Stop when we have enough context (found a '<' tag boundary).
		for ( $i = $openTagPtr - 1; $i >= 0; $i-- ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_INLINE_HTML ) {
				array_unshift( $fragments, $tokens[ $i ]['content'] );

				// If this fragment contains '<', we have a full tag boundary.
				if ( strpos( $tokens[ $i ]['content'], '<' ) !== false ) {
					break;
				}

				continue;
			}

			// Skip PHP close tags to cross over PHP blocks.
			if ( $code === T_CLOSE_TAG ) {
				// Walk backwards through the PHP block to find the open tag.
				$matchingOpen = $this->findMatchingOpenTag( $phpcsFile, $i );
				if ( false === $matchingOpen ) {
					break;
				}

				$i = $matchingOpen;
				continue;
			}

			// Any other token type means we can't determine context.
			break;
		}

		if ( empty( $fragments ) ) {
			return 'unknown';
		}

		$html = implode( '', $fragments );

		return $this->analyzeHtmlContext( $html );
	}

	/**
	 * Analyze the HTML content to determine the context at the end of the string.
	 *
	 * Parses the HTML to determine if the end of the string is inside an attribute
	 * value (between quotes after =) or in text content (after >).
	 *
	 * @param string $html The inline HTML content.
	 *
	 * @return string 'attribute' or 'content'.
	 */
	private function analyzeHtmlContext( $html ) {
		// Trim trailing whitespace/newlines for analysis.
		$trimmed = rtrim( $html );

		if ( '' === $trimmed ) {
			return 'unknown';
		}

		// Walk through the HTML character by character to track state.
		$inTag     = false;
		$inAttr    = false;
		$attrQuote = '';
		$lastTag   = '';

		$length = strlen( $trimmed );

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $trimmed[ $i ];

			if ( $inAttr ) {
				// We're inside a quoted attribute value.
				if ( $char === $attrQuote ) {
					$inAttr    = false;
					$attrQuote = '';
				}
				continue;
			}

			if ( $inTag ) {
				if ( $char === '>' ) {
					$inTag = false;
					continue;
				}

				// Check for attribute value start: = followed by quote.
				if ( $char === '=' ) {
					// Look ahead for the quote character.
					$next = $this->nextNonSpace( $trimmed, $i + 1 );

					if ( false !== $next && ( $trimmed[ $next ] === '"' || $trimmed[ $next ] === "'" ) ) {
						$inAttr    = true;
						$attrQuote = $trimmed[ $next ];
						$i         = $next; // Skip to the quote.
					}
				}

				continue;
			}

			// Not in a tag, not in an attribute.
			if ( $char === '<' ) {
				$inTag = true;

				// Extract the tag name (skip closing slash if present).
				if ( preg_match( '/^<\/?([a-zA-Z][a-zA-Z0-9]*)/', substr( $trimmed, $i ), $m ) ) {
					$lastTag = strtolower( $m[1] );
				}
			}
		}

		if ( $inAttr ) {
			return 'attribute';
		}

		if ( $inTag ) {
			// We're inside a tag but not in a quoted attribute value.
			// Could be an unquoted attribute or tag name area.
			// Be conservative and treat as attribute context.
			return 'attribute';
		}

		// Inside <style> or <script> blocks, esc_attr is acceptable.
		if ( in_array( $lastTag, array( 'style', 'script' ), true ) ) {
			return 'unknown';
		}

		// We're outside of any tag, this is text content.
		return 'content';
	}

	/**
	 * Find the next non-space character position in a string.
	 *
	 * @param string $string The string to search.
	 * @param int    $start  The start position.
	 *
	 * @return false|int The position of the next non-space character, or false.
	 */
	private function nextNonSpace( $string, $start ) {
		$length = strlen( $string );

		for ( $i = $start; $i < $length; $i++ ) {
			if ( $string[ $i ] !== ' ' && $string[ $i ] !== "\t" ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Find the matching T_OPEN_TAG for a T_CLOSE_TAG by walking backwards.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $closeTagPtr  The position of the close tag.
	 *
	 * @return false|int The position of the matching open tag, or false.
	 */
	private function findMatchingOpenTag( File $phpcsFile, $closeTagPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $closeTagPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_OPEN_TAG ) {
				return $i;
			}
		}

		return false;
	}
}
