<?php
/**
 * Formidable_Sniffs_Commenting_ConvertReturnVariableToDescriptionSniff
 *
 * Detects @return tags that include a variable name and converts it to a readable description.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects @return tags with variable names and converts them to descriptions.
 *
 * Bad:
 * @return string $classes
 * @return bool|int $entry_id
 * @return array $new_value
 *
 * Good:
 * @return string Classes.
 * @return bool|int Entry ID.
 * @return array New value.
 */
class ConvertReturnVariableToDescriptionSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_DOC_COMMENT_TAG );
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

		// Only process @return tags.
		if ( $tokens[ $stackPtr ]['content'] !== '@return' ) {
			return;
		}

		// Find the next token (should be whitespace, then the type string).
		$typeToken = $phpcsFile->findNext( T_DOC_COMMENT_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $typeToken || $tokens[ $typeToken ]['code'] !== T_DOC_COMMENT_STRING ) {
			return;
		}

		$content = $tokens[ $typeToken ]['content'];

		// Check if the content is ONLY a type followed immediately by a variable name.
		// Pattern: type(s) followed by whitespace and $variable_name (and nothing else, or just trailing whitespace).
		// This ensures we only match "@return type $var" not "@return type some text $var".
		if ( ! preg_match( '/^([a-zA-Z0-9_|\\\\]+)\s+(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*$/', $content, $matches ) ) {
			return;
		}

		$type         = trim( $matches[1] );
		$variableName = $matches[2];

		// Convert variable name to readable description.
		$description = $this->variableToDescription( $variableName );

		$fix = $phpcsFile->addFixableError(
			'@return variable name "%s" should be converted to description. Use "@return %s %s" instead.',
			$typeToken,
			'VariableNameInReturn',
			array( $variableName, $type, $description )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->replaceToken( $typeToken, $type . ' ' . $description );
		}
	}

	/**
	 * Convert a variable name to a readable description.
	 *
	 * Examples:
	 * - $entry_id -> "Entry ID."
	 * - $new_value -> "New value."
	 * - $classes -> "Classes."
	 * - $_data -> "Data."
	 *
	 * @param string $variableName The variable name (including $).
	 *
	 * @return string The readable description.
	 */
	private function variableToDescription( $variableName ) {
		// Remove the $ prefix and any leading underscores.
		$name = ltrim( $variableName, '$_' );

		// Split by underscores.
		$parts = explode( '_', $name );

		// Process each part.
		$words = array();

		foreach ( $parts as $index => $part ) {
			if ( empty( $part ) ) {
				continue;
			}

			// Check for common abbreviations that should be uppercase.
			$upperAbbreviations = array( 'id', 'url', 'html', 'css', 'js', 'api', 'db', 'sql', 'xml', 'json', 'php', 'wp', 'csv', 'http', 'https', 'ajax', 'dom', 'ui', 'ux', 'ip', 'ftp', 'smtp', 'ssl', 'tls', 'pdf', 'svg', 'png', 'jpg', 'gif' );

			if ( in_array( strtolower( $part ), $upperAbbreviations, true ) ) {
				$words[] = strtoupper( $part );
			} elseif ( $index === 0 ) {
				// First word is capitalized.
				$words[] = ucfirst( strtolower( $part ) );
			} else {
				// Other words are lowercase.
				$words[] = strtolower( $part );
			}
		}

		// Join words and add period.
		$description = implode( ' ', $words );

		if ( ! empty( $description ) ) {
			$description .= '.';
		}

		return $description;
	}
}
