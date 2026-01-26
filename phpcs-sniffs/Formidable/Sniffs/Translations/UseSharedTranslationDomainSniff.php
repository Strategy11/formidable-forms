<?php
/**
 * Sniff to enforce using the shared 'formidable' text domain for strings that exist in Lite.
 *
 * @package Formidable\Sniffs\Translations
 */

namespace Formidable\Sniffs\Translations;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects translation calls where the string exists in the Lite plugin's .pot file
 * and enforces using the 'formidable' text domain instead of a plugin-specific one.
 */
class UseSharedTranslationDomainSniff implements Sniff {

	/**
	 * Path to the Lite plugin's .pot file.
	 *
	 * Can be configured in ruleset.xml:
	 * <rule ref="Formidable.Translations.UseSharedTranslationDomain">
	 *     <properties>
	 *         <property name="potFilePath" value="/path/to/formidable/languages/formidable.pot" />
	 *     </properties>
	 * </rule>
	 *
	 * @var string
	 */
	public $potFilePath = '';

	/**
	 * The target text domain that should be used for shared strings.
	 *
	 * @var string
	 */
	public $targetDomain = 'formidable';

	/**
	 * The plugin-specific text domain for strings that don't exist in Lite.
	 *
	 * Can be configured in ruleset.xml:
	 * <rule ref="Formidable.Translations.UseSharedTranslationDomain">
	 *     <properties>
	 *         <property name="pluginDomain" value="formidable-pro" />
	 *     </properties>
	 * </rule>
	 *
	 * @var string
	 */
	public $pluginDomain = '';

	/**
	 * Cache of strings from the .pot file.
	 *
	 * @var array<string, bool>|null
	 */
	private static $potStrings = null;

	/**
	 * Translation functions to check.
	 *
	 * Key is function name, value is array with:
	 * - 'string' => argument position of the translatable string (0-indexed)
	 * - 'domain' => argument position of the text domain (0-indexed)
	 *
	 * @var array<string, array{string: int, domain: int}>
	 */
	private $translationFunctions = array(
		'__'                    => array(
			'string' => 0,
			'domain' => 1,
		),
		'_e'                    => array(
			'string' => 0,
			'domain' => 1,
		),
		'_x'                    => array(
			'string' => 0,
			'domain' => 2,
		),
		'_ex'                   => array(
			'string' => 0,
			'domain' => 2,
		),
		'_n'                    => array(
			'string' => 0,
			'domain' => 4,
		),
		'_nx'                   => array(
			'string' => 0,
			'domain' => 5,
		),
		'esc_html__'            => array(
			'string' => 0,
			'domain' => 1,
		),
		'esc_html_e'            => array(
			'string' => 0,
			'domain' => 1,
		),
		'esc_html_x'            => array(
			'string' => 0,
			'domain' => 2,
		),
		'esc_attr__'            => array(
			'string' => 0,
			'domain' => 1,
		),
		'esc_attr_e'            => array(
			'string' => 0,
			'domain' => 1,
		),
		'esc_attr_x'            => array(
			'string' => 0,
			'domain' => 2,
		),
		'_n_noop'               => array(
			'string' => 0,
			'domain' => 2,
		),
		'_nx_noop'              => array(
			'string' => 0,
			'domain' => 3,
		),
	);

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return array( T_STRING );
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$functionName = $tokens[ $stackPtr ]['content'];

		// Check if this is a translation function we care about.
		if ( ! isset( $this->translationFunctions[ $functionName ] ) ) {
			return;
		}

		// Make sure it's a function call.
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_OPEN_PARENTHESIS ) {
			return;
		}

		// Load the .pot file if not already loaded.
		if ( null === self::$potStrings ) {
			self::$potStrings = $this->loadPotFile();
		}

		// If we couldn't load the .pot file, skip.
		if ( empty( self::$potStrings ) ) {
			return;
		}

		$functionConfig = $this->translationFunctions[ $functionName ];
		$arguments      = $this->getFunctionArguments( $phpcsFile, $nextToken );

		// Check if we have enough arguments.
		if ( count( $arguments ) <= $functionConfig['domain'] ) {
			return;
		}

		$stringArg = $arguments[ $functionConfig['string'] ] ?? null;
		$domainArg = $arguments[ $functionConfig['domain'] ] ?? null;

		if ( null === $stringArg || null === $domainArg ) {
			return;
		}

		// Get the string value.
		$stringValue = $this->getStringValue( $phpcsFile, $stringArg );

		if ( null === $stringValue ) {
			return;
		}

		// Get the domain value.
		$domainValue = $this->getStringValue( $phpcsFile, $domainArg );

		if ( null === $domainValue ) {
			return;
		}

		$stringExistsInLite = isset( self::$potStrings[ $stringValue ] );

		// Case 1: String exists in Lite but uses wrong domain -> use 'formidable'.
		if ( $stringExistsInLite && $domainValue !== $this->targetDomain ) {
			$error = sprintf(
				'String "%s" exists in Lite plugin. Use text domain "%s" instead of "%s".',
				strlen( $stringValue ) > 50 ? substr( $stringValue, 0, 47 ) . '...' : $stringValue,
				$this->targetDomain,
				$domainValue
			);

			$fix = $phpcsFile->addFixableError( $error, $domainArg['start'], 'UseSharedDomain' );

			if ( $fix ) {
				$this->fixTextDomain( $phpcsFile, $tokens, $domainArg, $this->targetDomain );
			}

			return;
		}

		// Case 2: String does NOT exist in Lite but uses 'formidable' -> use plugin domain.
		if ( ! $stringExistsInLite && $domainValue === $this->targetDomain && ! empty( $this->pluginDomain ) ) {
			$error = sprintf(
				'String "%s" does not exist in Lite plugin. Use text domain "%s" instead of "%s".',
				strlen( $stringValue ) > 50 ? substr( $stringValue, 0, 47 ) . '...' : $stringValue,
				$this->pluginDomain,
				$domainValue
			);

			$fix = $phpcsFile->addFixableError( $error, $domainArg['start'], 'UsePluginDomain' );

			if ( $fix ) {
				$this->fixTextDomain( $phpcsFile, $tokens, $domainArg, $this->pluginDomain );
			}
		}
	}

	/**
	 * Fix the text domain in a translation call.
	 *
	 * @param File                        $phpcsFile   The file being scanned.
	 * @param array                       $tokens      The tokens array.
	 * @param array{start: int, end: int} $domainArg   The domain argument info.
	 * @param string                      $newDomain   The new text domain to use.
	 *
	 * @return void
	 */
	private function fixTextDomain( File $phpcsFile, array $tokens, array $domainArg, string $newDomain ) {
		$phpcsFile->fixer->beginChangeset();

		// Find the actual string token within the argument.
		for ( $i = $domainArg['start']; $i <= $domainArg['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_CONSTANT_ENCAPSED_STRING ) {
				$quote      = substr( $tokens[ $i ]['content'], 0, 1 );
				$newContent = $quote . $newDomain . $quote;
				$phpcsFile->fixer->replaceToken( $i, $newContent );
				break;
			}
		}

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Load and parse the .pot file.
	 *
	 * @return array<string, bool> Array of msgid strings as keys.
	 */
	private function loadPotFile() {
		if ( empty( $this->potFilePath ) || ! file_exists( $this->potFilePath ) ) {
			return array();
		}

		$content = file_get_contents( $this->potFilePath );

		if ( false === $content ) {
			return array();
		}

		$strings = array();

		// Parse msgid entries from the .pot file.
		// Match msgid "string" or msgid "" followed by continuation strings.
		preg_match_all( '/^msgid\s+"(.*)"/m', $content, $matches );

		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $string ) {
				// Skip empty strings (header).
				if ( '' === $string ) {
					continue;
				}

				// Unescape the string.
				$string = $this->unescapePotString( $string );

				$strings[ $string ] = true;
			}
		}

		return $strings;
	}

	/**
	 * Unescape a string from .pot file format.
	 *
	 * @param string $string The escaped string.
	 *
	 * @return string The unescaped string.
	 */
	private function unescapePotString( $string ) {
		$string = str_replace( '\\n', "\n", $string );
		$string = str_replace( '\\t', "\t", $string );
		$string = str_replace( '\\"', '"', $string );
		$string = str_replace( '\\\\', '\\', $string );

		return $string;
	}

	/**
	 * Get the arguments of a function call.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The position of the opening parenthesis.
	 *
	 * @return array<int, array{start: int, end: int}> Array of argument info.
	 */
	private function getFunctionArguments( File $phpcsFile, $openParen ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return array();
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$arguments  = array();
		$argStart   = $openParen + 1;
		$depth      = 0;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_OPEN_PARENTHESIS || $code === T_OPEN_SQUARE_BRACKET || $code === T_OPEN_CURLY_BRACKET ) {
				++$depth;
			} elseif ( $code === T_CLOSE_PARENTHESIS || $code === T_CLOSE_SQUARE_BRACKET || $code === T_CLOSE_CURLY_BRACKET ) {
				--$depth;
			} elseif ( $code === T_COMMA && 0 === $depth ) {
				$arguments[] = array(
					'start' => $argStart,
					'end'   => $i - 1,
				);
				$argStart    = $i + 1;
			}
		}

		// Add the last argument.
		if ( $argStart < $closeParen ) {
			$arguments[] = array(
				'start' => $argStart,
				'end'   => $closeParen - 1,
			);
		}

		return $arguments;
	}

	/**
	 * Get the string value from an argument.
	 *
	 * @param File                      $phpcsFile The file being scanned.
	 * @param array{start: int, end: int} $arg       The argument info.
	 *
	 * @return string|null The string value or null if not a simple string.
	 */
	private function getStringValue( File $phpcsFile, $arg ) {
		$tokens = $phpcsFile->getTokens();

		// Find the first non-whitespace token in the argument.
		$stringToken = null;

		for ( $i = $arg['start']; $i <= $arg['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			if ( $tokens[ $i ]['code'] === T_CONSTANT_ENCAPSED_STRING ) {
				$stringToken = $i;
				break;
			}

			// Not a simple string literal.
			return null;
		}

		if ( null === $stringToken ) {
			return null;
		}

		$content = $tokens[ $stringToken ]['content'];

		// Remove quotes.
		$quote = substr( $content, 0, 1 );

		if ( $quote === '"' || $quote === "'" ) {
			$content = substr( $content, 1, -1 );
		}

		return $content;
	}
}
