<?php
/**
 * Sniff to add missing types to @param comments based on is_* checks in function body.
 *
 * If a function uses is_array($param), is_object($param), is_int($param), etc.,
 * and the @param type doesn't include that type, this sniff will add it.
 *
 * @package Formidable\Sniffs\Commenting
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Adds missing types to @param based on is_* checks.
 */
class AddMissingParamTypeSniff implements Sniff {

	/**
	 * Mapping of is_* functions to their corresponding types.
	 *
	 * @var array
	 */
	private $isCheckToType = array(
		'is_array'    => 'array',
		'is_object'   => 'object',
		'is_int'      => 'int',
		'is_integer'  => 'int',
		'is_long'     => 'int',
		'is_float'    => 'float',
		'is_double'   => 'float',
		'is_real'     => 'float',
		'is_string'   => 'string',
		'is_bool'     => 'bool',
		'is_null'     => 'null',
		'is_numeric'  => 'string',
		'is_callable' => 'callable',
		'is_iterable' => 'iterable',
		'is_resource' => 'resource',
	);

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
		$tokens = $phpcsFile->getTokens();

		// Skip if no scope (abstract method, interface method).
		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

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

		if ( empty( $existingParams ) ) {
			return;
		}

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		// Find is_* checks for each parameter.
		$missingTypes = array();

		foreach ( $params as $param ) {
			$paramName = $param['name'];

			if ( ! isset( $existingParams[ $paramName ] ) ) {
				continue;
			}

			$existingType = $existingParams[ $paramName ]['type'];
			$checkedTypes = $this->findIsChecksForParam( $phpcsFile, $paramName, $scopeOpener, $scopeCloser );

			foreach ( $checkedTypes as $checkedType ) {
				if ( ! $this->typeIncludesType( $existingType, $checkedType ) ) {
					if ( ! isset( $missingTypes[ $paramName ] ) ) {
						$missingTypes[ $paramName ] = array(
							'existing' => $existingType,
							'missing'  => array(),
							'token'    => $existingParams[ $paramName ]['token'],
						);
					}
					$missingTypes[ $paramName ]['missing'][] = $checkedType;
				}
			}
		}

		if ( empty( $missingTypes ) ) {
			return;
		}

		// Report and fix each missing type.
		foreach ( $missingTypes as $paramName => $info ) {
			$missingList = array_unique( $info['missing'] );
			$newType     = $info['existing'] . '|' . implode( '|', $missingList );

			$fix = $phpcsFile->addFixableError(
				'@param %s is missing type(s) %s based on is_* checks in function body',
				$info['token'],
				'MissingParamType',
				array( $paramName, implode( ', ', $missingList ) )
			);

			if ( $fix ) {
				$this->fixParamType( $phpcsFile, $info['token'], $info['existing'], $newType );
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
	 * Find is_* checks for a specific parameter in the function body.
	 *
	 * @param File   $phpcsFile   The file being scanned.
	 * @param string $varName     The variable name.
	 * @param int    $scopeOpener The function scope opener.
	 * @param int    $scopeCloser The function scope closer.
	 *
	 * @return array Array of types that are checked.
	 */
	private function findIsChecksForParam( File $phpcsFile, $varName, $scopeOpener, $scopeCloser ) {
		$tokens       = $phpcsFile->getTokens();
		$checkedTypes = array();

		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_STRING ) {
				continue;
			}

			$functionName = $tokens[ $i ]['content'];

			if ( ! isset( $this->isCheckToType[ $functionName ] ) ) {
				continue;
			}

			// Check if followed by ( $varName ).
			$openParen = $phpcsFile->findNext( T_WHITESPACE, $i + 1, $scopeCloser, true );

			if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
				continue;
			}

			$varToken = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $scopeCloser, true );

			if ( false === $varToken || $tokens[ $varToken ]['code'] !== T_VARIABLE || $tokens[ $varToken ]['content'] !== $varName ) {
				continue;
			}

			// Make sure the variable is used directly, not as array access or object property.
			$afterVar = $phpcsFile->findNext( T_WHITESPACE, $varToken + 1, $scopeCloser, true );

			if ( false !== $afterVar ) {
				$afterCode = $tokens[ $afterVar ]['code'];

				// Skip $var[...] (array access) or $var->... (object property).
				if ( $afterCode === T_OPEN_SQUARE_BRACKET || $afterCode === T_OBJECT_OPERATOR || $afterCode === T_NULLSAFE_OBJECT_OPERATOR ) {
					continue;
				}
			}

			$checkedTypes[] = $this->isCheckToType[ $functionName ];
		}

		return array_unique( $checkedTypes );
	}

	/**
	 * Check if an existing type string includes a specific type.
	 *
	 * @param string $existingType The existing type string (e.g., "string|array").
	 * @param string $checkType    The type to check for.
	 *
	 * @return bool
	 */
	private function typeIncludesType( $existingType, $checkType ) {
		// Handle compound types from is_numeric.
		$checkTypes = explode( '|', $checkType );

		// Split existing type by | for checking.
		$existingTypes = explode( '|', $existingType );

		// If callable is in existing types, treat array and string as covered (callable can be an array or string).
		if ( $checkType === 'array' || $checkType === 'string' ) {
			foreach ( $existingTypes as $existing ) {
				$normalizedExisting = $this->normalizeType( $existing );

				if ( $normalizedExisting === 'callable' ) {
					return true;
				}
			}
		}

		foreach ( $checkTypes as $singleType ) {
			foreach ( $existingTypes as $existing ) {
				// Normalize types for comparison.
				$normalizedExisting = $this->normalizeType( $existing );
				$normalizedCheck    = $this->normalizeType( $singleType );

				if ( $normalizedExisting === $normalizedCheck ) {
					return true;
				}

				// Check if 'mixed' covers everything.
				if ( $normalizedExisting === 'mixed' ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Normalize a type for comparison.
	 *
	 * @param string $type The type to normalize.
	 *
	 * @return string
	 */
	private function normalizeType( $type ) {
		$type = strtolower( trim( $type ) );

		// Normalize common aliases.
		$aliases = array(
			'integer'  => 'int',
			'boolean'  => 'bool',
			'double'   => 'float',
			'real'     => 'float',
			'long'     => 'int',
			'stdclass' => 'object',
			'closure'  => 'callable',
		);

		if ( isset( $aliases[ $type ] ) ) {
			return $aliases[ $type ];
		}

		return $type;
	}

	/**
	 * Fix the @param type by replacing the old type with the new type.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $tokenPtr  The token position of the docblock string.
	 * @param string $oldType   The old type string.
	 * @param string $newType   The new type string.
	 *
	 * @return void
	 */
	private function fixParamType( File $phpcsFile, $tokenPtr, $oldType, $newType ) {
		$tokens  = $phpcsFile->getTokens();
		$content = $tokens[ $tokenPtr ]['content'];

		// Replace the old type with the new type.
		$newContent = preg_replace(
			'/^' . preg_quote( $oldType, '/' ) . '/',
			$newType,
			$content,
			1
		);

		$phpcsFile->fixer->beginChangeset();
		$phpcsFile->fixer->replaceToken( $tokenPtr, $newContent );
		$phpcsFile->fixer->endChangeset();
	}
}
