<?php
/**
 * Sniff to add missing types to @param comments based on literal values at call sites.
 *
 * If a function is called with literal values (strings, arrays, integers, etc.),
 * and the @param type doesn't include that type, this sniff will add it.
 *
 * @package Formidable\Sniffs\Commenting
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Adds missing types to @param based on literal values at call sites.
 */
class AddMissingParamTypeFromCallsSniff implements Sniff {

	/**
	 * Cache of function definitions in the current file.
	 *
	 * @var array
	 */
	private $functionDefinitions = array();

	/**
	 * The current file being processed.
	 *
	 * @var string
	 */
	private $currentFile = '';

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FUNCTION );
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
		$tokens   = $phpcsFile->getTokens();
		$filename = $phpcsFile->getFilename();

		// Reset cache if we're processing a new file.
		if ( $this->currentFile !== $filename ) {
			$this->currentFile         = $filename;
			$this->functionDefinitions = array();
		}

		// Get function name.
		$functionNamePtr = $phpcsFile->findNext( T_STRING, $stackPtr + 1 );

		if ( false === $functionNamePtr ) {
			return;
		}

		$functionName = $tokens[ $functionNamePtr ]['content'];

		// Check if function has a docblock.
		$docblock = $this->findDocblock( $phpcsFile, $stackPtr );

		if ( false === $docblock ) {
			return;
		}

		// Get function parameters.
		$params = $this->getParameters( $phpcsFile, $stackPtr );

		if ( empty( $params ) ) {
			return;
		}

		// Get existing @param tags with their types.
		$existingParams = $this->getExistingParamTags( $phpcsFile, $docblock );

		// Find parameters that are missing @param comments entirely.
		$missingParamComments = array();

		foreach ( $params as $index => $param ) {
			if ( ! isset( $existingParams[ $param['name'] ] ) ) {
				$missingParamComments[ $index ] = $param;
			}
		}

		// Only proceed if there are parameters missing @param comments.
		if ( empty( $missingParamComments ) ) {
			return;
		}

		// Determine if this is a method or a function.
		$isMethod  = false;
		$className = '';

		$classPtr = $phpcsFile->findPrevious( array( T_CLASS, T_TRAIT ), $stackPtr - 1 );

		if ( false !== $classPtr ) {
			$classNamePtr = $phpcsFile->findNext( T_STRING, $classPtr + 1 );

			if ( false !== $classNamePtr ) {
				$isMethod  = true;
				$className = $tokens[ $classNamePtr ]['content'];
			}
		}

		// Find all call sites for this function in the file.
		$callSiteTypes = $this->findCallSiteTypes( $phpcsFile, $functionName, $isMethod, $className, count( $params ) );

		if ( empty( $callSiteTypes ) ) {
			return;
		}

		// Check each missing parameter for literal types from call sites.
		$paramsToAdd = array();

		foreach ( $missingParamComments as $index => $param ) {
			$paramName = $param['name'];

			if ( ! isset( $callSiteTypes[ $index ] ) ) {
				continue;
			}

			$literalTypes = array_unique( $callSiteTypes[ $index ] );

			if ( ! empty( $literalTypes ) ) {
				$paramsToAdd[ $paramName ] = array(
					'types' => $literalTypes,
					'index' => $index,
				);
			}
		}

		if ( empty( $paramsToAdd ) ) {
			return;
		}

		// Report and fix each missing @param.
		foreach ( $paramsToAdd as $paramName => $info ) {
			$typeString = implode( '|', $info['types'] );

			$fix = $phpcsFile->addFixableError(
				'Missing @param %s %s (detected from call site literals)',
				$docblock,
				'MissingParamFromCalls',
				array( $typeString, $paramName )
			);

			if ( $fix ) {
				$this->addParamTag( $phpcsFile, $docblock, $typeString, $paramName );
			}
		}
	}

	/**
	 * Find the docblock for a function.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 *
	 * @return false|int The docblock opener position, or false.
	 */
	private function findDocblock( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$ignore = array(
			T_WHITESPACE,
			T_STATIC,
			T_PUBLIC,
			T_PRIVATE,
			T_PROTECTED,
			T_ABSTRACT,
			T_FINAL,
		);

		$prev = $phpcsFile->findPrevious( $ignore, $stackPtr - 1, null, true );

		if ( false !== $prev && $tokens[ $prev ]['code'] === T_DOC_COMMENT_CLOSE_TAG ) {
			return $tokens[ $prev ]['comment_opener'];
		}

		return false;
	}

	/**
	 * Get function parameters.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 *
	 * @return array Array of parameter info.
	 */
	private function getParameters( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$params = array();

		if ( ! isset( $tokens[ $stackPtr ]['parenthesis_opener'] ) ) {
			return $params;
		}

		$opener = $tokens[ $stackPtr ]['parenthesis_opener'];
		$closer = $tokens[ $stackPtr ]['parenthesis_closer'];

		for ( $i = $opener + 1; $i < $closer; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE ) {
				$params[] = array(
					'name'  => $tokens[ $i ]['content'],
					'token' => $i,
				);
			}
		}

		return $params;
	}

	/**
	 * Get existing @param tags from docblock with their types.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $docblock  The docblock opener position.
	 *
	 * @return array Associative array of param name => array('type' => string, 'token' => int).
	 */
	private function getExistingParamTags( File $phpcsFile, $docblock ) {
		$tokens   = $phpcsFile->getTokens();
		$existing = array();

		if ( ! isset( $tokens[ $docblock ]['comment_closer'] ) ) {
			return $existing;
		}

		$closer = $tokens[ $docblock ]['comment_closer'];

		for ( $i = $docblock; $i < $closer; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_DOC_COMMENT_TAG ) {
				continue;
			}

			if ( $tokens[ $i ]['content'] !== '@param' ) {
				continue;
			}

			// Find the type and parameter name in this @param line.
			for ( $j = $i + 1; $j < $closer; $j++ ) {
				if ( $tokens[ $j ]['code'] === T_DOC_COMMENT_STRING ) {
					$content = $tokens[ $j ]['content'];

					// Parse "type $varName description" format.
					if ( preg_match( '/^([^\s]+)\s+(\$\w+)/', $content, $matches ) ) {
						$existing[ $matches[2] ] = array(
							'type'  => $matches[1],
							'token' => $j,
						);
					}
					break;
				}

				if ( $tokens[ $j ]['code'] === T_DOC_COMMENT_TAG ) {
					break;
				}
			}
		}

		return $existing;
	}

	/**
	 * Find all call sites for a function and determine literal types passed.
	 *
	 * @param File   $phpcsFile   The file being scanned.
	 * @param string $functionName The function name to search for.
	 * @param bool   $isMethod    Whether this is a method.
	 * @param string $className   The class name if this is a method.
	 * @param int    $paramCount  The number of parameters.
	 *
	 * @return array Array of parameter index => array of literal types.
	 */
	private function findCallSiteTypes( File $phpcsFile, $functionName, $isMethod, $className, $paramCount ) {
		$tokens    = $phpcsFile->getTokens();
		$types     = array();
		$fileEnd   = count( $tokens );

		for ( $i = 0; $i < $fileEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_STRING ) {
				continue;
			}

			if ( $tokens[ $i ]['content'] !== $functionName ) {
				continue;
			}

			// Check if this is a function call (followed by parenthesis).
			$nextNonWhitespace = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false === $nextNonWhitespace || $tokens[ $nextNonWhitespace ]['code'] !== T_OPEN_PARENTHESIS ) {
				continue;
			}

			// For methods, check if it's called on the right class or $this.
			if ( $isMethod ) {
				$prevNonWhitespace = $phpcsFile->findPrevious( T_WHITESPACE, $i - 1, null, true );

				if ( false === $prevNonWhitespace ) {
					continue;
				}

				$prevCode = $tokens[ $prevNonWhitespace ]['code'];

				// Must be preceded by -> or ::.
				if ( $prevCode !== T_OBJECT_OPERATOR && $prevCode !== T_NULLSAFE_OBJECT_OPERATOR && $prevCode !== T_DOUBLE_COLON ) {
					continue;
				}

				// For ::, check if it's self::, static::, or ClassName::.
				if ( $prevCode === T_DOUBLE_COLON ) {
					$classRef = $phpcsFile->findPrevious( T_WHITESPACE, $prevNonWhitespace - 1, null, true );

					if ( false === $classRef ) {
						continue;
					}

					$classRefContent = $tokens[ $classRef ]['content'];

					if ( $classRefContent !== 'self' && $classRefContent !== 'static' && $classRefContent !== $className ) {
						continue;
					}
				}
			}

			// Get the arguments passed to this call.
			$openParen  = $nextNonWhitespace;
			$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

			$argTypes = $this->getArgumentLiteralTypes( $phpcsFile, $openParen, $closeParen );

			foreach ( $argTypes as $argIndex => $argType ) {
				if ( null === $argType ) {
					continue;
				}

				if ( ! isset( $types[ $argIndex ] ) ) {
					$types[ $argIndex ] = array();
				}

				if ( ! in_array( $argType, $types[ $argIndex ], true ) ) {
					$types[ $argIndex ][] = $argType;
				}
			}
		}

		return $types;
	}

	/**
	 * Get the literal types of arguments in a function call.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The opening parenthesis position.
	 * @param int  $closeParen The closing parenthesis position.
	 *
	 * @return array Array of argument index => literal type (or null if not a literal).
	 */
	private function getArgumentLiteralTypes( File $phpcsFile, $openParen, $closeParen ) {
		$tokens   = $phpcsFile->getTokens();
		$argTypes = array();
		$argIndex = 0;
		$depth    = 0;

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			$code    = $tokens[ $i ]['code'];
			$content = $tokens[ $i ]['content'];

			// Track nested parentheses/brackets.
			if ( in_array( $code, array( T_OPEN_PARENTHESIS, T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET ), true ) ) {
				++$depth;
			} elseif ( in_array( $code, array( T_CLOSE_PARENTHESIS, T_CLOSE_SHORT_ARRAY, T_CLOSE_SQUARE_BRACKET ), true ) ) {
				--$depth;
			}

			// Comma at depth 0 means next argument.
			if ( $code === T_COMMA && $depth === 0 ) {
				++$argIndex;
				continue;
			}

			// Skip if we're inside nested structure.
			if ( $depth > 0 ) {
				continue;
			}

			// Skip whitespace.
			if ( $code === T_WHITESPACE ) {
				continue;
			}

			// Only record if we haven't set a type for this argument yet.
			if ( isset( $argTypes[ $argIndex ] ) ) {
				continue;
			}

			// Determine literal type.
			$literalType = $this->getLiteralType( $code, $content );
			$argTypes[ $argIndex ] = $literalType;
		}

		return $argTypes;
	}

	/**
	 * Get the type of a literal token.
	 *
	 * @param int    $code    The token code.
	 * @param string $content The token content.
	 *
	 * @return string|null The literal type, or null if not a recognized literal.
	 */
	private function getLiteralType( $code, $content ) {
		// String literals.
		if ( $code === T_CONSTANT_ENCAPSED_STRING ) {
			return 'string';
		}

		// Integer literals.
		if ( $code === T_LNUMBER ) {
			return 'int';
		}

		// Float literals.
		if ( $code === T_DNUMBER ) {
			return 'float';
		}

		// Array literals.
		if ( $code === T_ARRAY || $code === T_OPEN_SHORT_ARRAY ) {
			return 'array';
		}

		// Boolean literals.
		if ( $code === T_TRUE || $code === T_FALSE ) {
			return 'bool';
		}

		// Null.
		if ( $code === T_NULL ) {
			return 'null';
		}

		return null;
	}

	/**
	 * Add a @param tag to the docblock.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $docblock  The docblock opener position.
	 * @param string $type      The type string.
	 * @param string $paramName The parameter name.
	 *
	 * @return void
	 */
	private function addParamTag( File $phpcsFile, $docblock, $type, $paramName ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $docblock ]['comment_closer'] ) ) {
			return;
		}

		$closer = $tokens[ $docblock ]['comment_closer'];

		// Find the last @param tag to insert after.
		$lastParamEnd = null;

		for ( $i = $docblock; $i < $closer; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_DOC_COMMENT_TAG ) {
				continue;
			}

			if ( $tokens[ $i ]['content'] === '@param' ) {
				// Find the end of this @param's content (the T_DOC_COMMENT_STRING).
				for ( $j = $i + 1; $j < $closer; $j++ ) {
					if ( $tokens[ $j ]['code'] === T_DOC_COMMENT_STRING ) {
						$lastParamEnd = $j;
						break;
					}

					if ( $tokens[ $j ]['code'] === T_DOC_COMMENT_TAG ) {
						break;
					}
				}
			}
		}

		if ( null === $lastParamEnd ) {
			// No existing @param tags - don't add (let other sniffs handle this).
			return;
		}

		// Append to the last @param's content string.
		$existingContent = $tokens[ $lastParamEnd ]['content'];
		$newContent      = $existingContent . "\n\t * @param " . $type . ' ' . $paramName;

		$phpcsFile->fixer->beginChangeset();
		$phpcsFile->fixer->replaceToken( $lastParamEnd, $newContent );
		$phpcsFile->fixer->endChangeset();
	}
}
