<?php
/**
 * Sniff to add missing types to @param comments based on default values.
 *
 * If a function parameter has a default value (e.g., $param = array()),
 * and the @param type doesn't include that type, this sniff will add it.
 *
 * @package Formidable\Sniffs\Commenting
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Adds missing types to @param based on default values.
 */
class AddMissingParamTypeFromDefaultSniff implements Sniff {

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

		// Check if function has a docblock.
		$docblock = $this->findDocblock( $phpcsFile, $stackPtr );

		if ( false === $docblock ) {
			return;
		}

		// Get function parameters with their default values.
		$params = $this->getParametersWithDefaults( $phpcsFile, $stackPtr );

		if ( empty( $params ) ) {
			return;
		}

		// Get existing @param tags with their types.
		$existingParams = $this->getExistingParamTags( $phpcsFile, $docblock );

		if ( empty( $existingParams ) ) {
			return;
		}

		// Find missing types based on default values.
		$missingTypes = array();

		foreach ( $params as $param ) {
			$paramName = $param['name'];

			if ( ! isset( $existingParams[ $paramName ] ) ) {
				continue;
			}

			if ( null === $param['default_type'] ) {
				continue;
			}

			$existingType = $existingParams[ $paramName ]['type'];
			$defaultType  = $param['default_type'];

			if ( ! $this->typeIncludesType( $existingType, $defaultType ) ) {
				$missingTypes[ $paramName ] = array(
					'existing' => $existingType,
					'missing'  => $defaultType,
					'token'    => $existingParams[ $paramName ]['token'],
				);
			}
		}

		if ( empty( $missingTypes ) ) {
			return;
		}

		// Report and fix each missing type.
		foreach ( $missingTypes as $paramName => $info ) {
			$existingType = $info['existing'];
			$missingType  = $info['missing'];

			// If adding 'bool', remove 'false' from the existing type.
			if ( $missingType === 'bool' ) {
				$existingType = $this->removeFalseFromType( $existingType );
			}

			$newType = $existingType . '|' . $missingType;

			$fix = $phpcsFile->addFixableError(
				'@param %s is missing type "%s" based on default value',
				$info['token'],
				'MissingParamTypeFromDefault',
				array( $paramName, $missingType )
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
	 * Get function parameters with their default values.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 *
	 * @return array Array of parameter info including default type.
	 */
	private function getParametersWithDefaults( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$params = array();

		if ( ! isset( $tokens[ $stackPtr ]['parenthesis_opener'] ) ) {
			return $params;
		}

		$opener = $tokens[ $stackPtr ]['parenthesis_opener'];
		$closer = $tokens[ $stackPtr ]['parenthesis_closer'];

		for ( $i = $opener + 1; $i < $closer; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE ) {
				continue;
			}

			$paramName    = $tokens[ $i ]['content'];
			$defaultType  = null;

			// Look for = after the variable.
			$next = $phpcsFile->findNext( T_WHITESPACE, $i + 1, $closer, true );

			if ( false !== $next && $tokens[ $next ]['code'] === T_EQUAL ) {
				$defaultType = $this->getDefaultValueType( $phpcsFile, $next + 1, $closer );
			}

			$params[] = array(
				'name'         => $paramName,
				'token'        => $i,
				'default_type' => $defaultType,
			);
		}

		return $params;
	}

	/**
	 * Determine the type of a default value.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  The position after the = sign.
	 * @param int  $endPtr    The end of the parameter list.
	 *
	 * @return string|null The type of the default value, or null if unknown.
	 */
	private function getDefaultValueType( File $phpcsFile, $startPtr, $endPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the first non-whitespace token after =.
		$valueToken = $phpcsFile->findNext( T_WHITESPACE, $startPtr, $endPtr, true );

		if ( false === $valueToken ) {
			return null;
		}

		$code    = $tokens[ $valueToken ]['code'];
		$content = $tokens[ $valueToken ]['content'];

		// Array literals.
		if ( $code === T_ARRAY || $code === T_OPEN_SHORT_ARRAY ) {
			return 'array';
		}

		// Null.
		if ( $code === T_NULL ) {
			return 'null';
		}

		// Boolean true - add bool type.
		if ( $code === T_TRUE ) {
			return 'bool';
		}

		// Boolean false - only add if 'false' is not already in the type.
		if ( $code === T_FALSE ) {
			return 'false';
		}

		// Integer.
		if ( $code === T_LNUMBER ) {
			return 'int';
		}

		// Float.
		if ( $code === T_DNUMBER ) {
			return 'float';
		}

		// String (single or double quoted, or heredoc/nowdoc).
		if ( $code === T_CONSTANT_ENCAPSED_STRING || $code === T_START_HEREDOC || $code === T_START_NOWDOC ) {
			return 'string';
		}

		// Empty string ''.
		if ( $content === "''" || $content === '""' ) {
			return 'string';
		}

		return null;
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
	 * Check if an existing type string includes a specific type.
	 *
	 * @param string $existingType The existing type string (e.g., "string|array").
	 * @param string $checkType    The type to check for.
	 *
	 * @return bool
	 */
	private function typeIncludesType( $existingType, $checkType ) {
		// If existing type is 'mixed', everything is covered.
		if ( strtolower( trim( $existingType ) ) === 'mixed' ) {
			return true;
		}

		// Split existing type by | for checking.
		$existingTypes = explode( '|', $existingType );

		// If checking for 'array', also check for typed arrays and shorthand notation.
		if ( $checkType === 'array' ) {
			foreach ( $existingTypes as $existing ) {
				$normalized = $this->normalizeType( $existing );

				if ( $normalized === 'array' ) {
					return true;
				}
			}

			return false;
		}

		// If checking for 'object', treat any class name as covering it.
		if ( $checkType === 'object' ) {
			foreach ( $existingTypes as $existing ) {
				$normalized = $this->normalizeType( $existing );

				if ( $normalized === 'object' || $this->isClassName( $existing ) ) {
					return true;
				}
			}

			return false;
		}

		// If checking for 'bool', accept 'bool' or 'true' as covering it (but not 'false' alone).
		if ( $checkType === 'bool' ) {
			foreach ( $existingTypes as $existing ) {
				$normalized = $this->normalizeType( $existing );

				if ( $normalized === 'bool' || $normalized === 'true' ) {
					return true;
				}
			}

			return false;
		}

		// If checking for 'false', accept 'false' or 'bool' as covering it.
		if ( $checkType === 'false' ) {
			foreach ( $existingTypes as $existing ) {
				$normalized = $this->normalizeType( $existing );

				if ( $normalized === 'false' || $normalized === 'bool' ) {
					return true;
				}
			}

			return false;
		}

		foreach ( $existingTypes as $existing ) {
			$normalizedExisting = $this->normalizeType( $existing );
			$normalizedCheck    = $this->normalizeType( $checkType );

			if ( $normalizedExisting === $normalizedCheck ) {
				return true;
			}

			// Check if 'mixed' covers everything.
			if ( $normalizedExisting === 'mixed' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a type string looks like a class name.
	 *
	 * @param string $type The type to check.
	 *
	 * @return bool
	 */
	private function isClassName( $type ) {
		$type = trim( $type );

		// Skip primitive types and known non-class types.
		$primitives = array(
			'int', 'integer', 'float', 'double', 'real', 'string', 'bool', 'boolean',
			'array', 'object', 'null', 'mixed', 'void', 'callable', 'iterable',
			'resource', 'true', 'false', 'self', 'static', 'parent',
		);

		$lowerType = strtolower( $type );

		if ( in_array( $lowerType, $primitives, true ) ) {
			return false;
		}

		// Skip if it's an array notation.
		if ( preg_match( '/\[\]$/', $type ) || preg_match( '/^array\s*[<{]/', $lowerType ) ) {
			return false;
		}

		// If it starts with uppercase or contains backslash (namespace), it's likely a class.
		if ( preg_match( '/^[A-Z]/', $type ) || strpos( $type, '\\' ) !== false ) {
			return true;
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

		// Normalize typed arrays (array<string>, array<int>, etc.) to just 'array'.
		if ( preg_match( '/^array\s*</', $type ) || preg_match( '/^array\s*\{/', $type ) ) {
			return 'array';
		}

		// Normalize shorthand array notation (int[], string[], etc.) to just 'array'.
		if ( preg_match( '/\[\]$/', $type ) ) {
			return 'array';
		}

		return $type;
	}

	/**
	 * Remove 'false' from a type string.
	 *
	 * @param string $type The type string.
	 *
	 * @return string The type string without 'false'.
	 */
	private function removeFalseFromType( $type ) {
		$types = explode( '|', $type );
		$types = array_filter(
			$types,
			function ( $t ) {
				return strtolower( trim( $t ) ) !== 'false';
			}
		);

		return implode( '|', $types );
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
