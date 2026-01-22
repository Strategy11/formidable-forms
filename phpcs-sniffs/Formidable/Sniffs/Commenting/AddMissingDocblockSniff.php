<?php
/**
 * Sniff to add missing docblocks with @param and @return comments.
 *
 * Only adds types when we're certain:
 * - @return: string (hardcoded), array (hardcoded), bool (true/false)
 * - @param array: when variable uses [] or is named $atts/$args
 *
 * @package Formidable\Sniffs\Commenting
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Adds missing docblocks with certain type annotations.
 */
class AddMissingDocblockSniff implements Sniff {

	/**
	 * Parameter names that are always arrays.
	 *
	 * @var array
	 */
	private $arrayParamNames = array(
		'$atts',
		'$args',
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

		// Check if function already has a docblock.
		$existingDocblock = $this->findDocblock( $phpcsFile, $stackPtr );

		if ( false !== $existingDocblock ) {
			// Has docblock - check for missing @param or @return.
			$this->processMissingAnnotations( $phpcsFile, $stackPtr, $existingDocblock );
			return;
		}

		// No docblock - determine what we can add with certainty.
		$params     = $this->getParameters( $phpcsFile, $stackPtr );
		$returnType = $this->detectCertainReturnType( $phpcsFile, $stackPtr );

		// Get certain param types.
		$certainParams = $this->getCertainParamTypes( $phpcsFile, $stackPtr, $params );

		// Only add docblock if we have something certain to add.
		if ( empty( $certainParams ) && null === $returnType ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Missing docblock for function.',
			$stackPtr,
			'MissingDocblock'
		);

		if ( $fix ) {
			$this->addDocblock( $phpcsFile, $stackPtr, $params, $certainParams, $returnType );
		}
	}

	/**
	 * Process existing docblock for missing annotations.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 * @param int  $docblock  The docblock opener position.
	 *
	 * @return void
	 */
	private function processMissingAnnotations( File $phpcsFile, $stackPtr, $docblock ) {
		$tokens = $phpcsFile->getTokens();

		$params         = $this->getParameters( $phpcsFile, $stackPtr );
		$certainParams  = $this->getCertainParamTypes( $phpcsFile, $stackPtr, $params );
		$existingParams = $this->getExistingParamTags( $phpcsFile, $docblock );
		$hasReturnTag   = $this->hasReturnTag( $phpcsFile, $docblock );
		$returnType     = $this->detectCertainReturnType( $phpcsFile, $stackPtr );

		// Check for missing @param tags.
		$missingParams = array();

		foreach ( $certainParams as $paramName => $paramType ) {
			if ( ! isset( $existingParams[ $paramName ] ) ) {
				$missingParams[ $paramName ] = $paramType;
			}
		}

		// Check for missing @return tag.
		$missingReturn = ( ! $hasReturnTag && null !== $returnType );

		if ( empty( $missingParams ) && ! $missingReturn ) {
			return;
		}

		$message = 'Missing';

		if ( ! empty( $missingParams ) ) {
			$message .= ' @param for: ' . implode( ', ', array_keys( $missingParams ) );
		}

		if ( $missingReturn ) {
			$message .= ( ! empty( $missingParams ) ? ' and' : '' ) . ' @return ' . $returnType;
		}

		$fix = $phpcsFile->addFixableError(
			$message,
			$docblock,
			'MissingAnnotation'
		);

		if ( $fix ) {
			$this->addMissingAnnotations( $phpcsFile, $docblock, $missingParams, $missingReturn ? $returnType : null );
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
	 * Get certain param types based on usage or naming.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The function token position.
	 * @param array $params    The function parameters.
	 *
	 * @return array Associative array of param name => type.
	 */
	private function getCertainParamTypes( File $phpcsFile, $stackPtr, $params ) {
		$tokens       = $phpcsFile->getTokens();
		$certainTypes = array();

		if ( empty( $params ) ) {
			return $certainTypes;
		}

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		foreach ( $params as $param ) {
			$paramName = $param['name'];

			// Check if param name is in our known array names.
			if ( in_array( $paramName, $this->arrayParamNames, true ) ) {
				$certainTypes[ $paramName ] = 'array';
				continue;
			}

			// Check if param is used with [] syntax in function body.
			if ( $this->isUsedAsArray( $phpcsFile, $paramName, $scopeOpener, $scopeCloser ) ) {
				$certainTypes[ $paramName ] = 'array';
			}
		}

		return $certainTypes;
	}

	/**
	 * Check if a variable is used as an array (with [] syntax).
	 *
	 * @param File   $phpcsFile   The file being scanned.
	 * @param string $varName     The variable name.
	 * @param int    $scopeOpener The function scope opener.
	 * @param int    $scopeCloser The function scope closer.
	 *
	 * @return bool
	 */
	private function isUsedAsArray( File $phpcsFile, $varName, $scopeOpener, $scopeCloser ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE ) {
				continue;
			}

			if ( $tokens[ $i ]['content'] !== $varName ) {
				continue;
			}

			// Check if followed by [.
			$next = $phpcsFile->findNext( T_WHITESPACE, $i + 1, $scopeCloser, true );

			if ( false !== $next && $tokens[ $next ]['code'] === T_OPEN_SQUARE_BRACKET ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Detect certain return type from function body.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 *
	 * @return string|null The certain return type, or null if uncertain.
	 */
	private function detectCertainReturnType( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		$returnTypes = array();
		$current     = $scopeOpener;

		while ( $current < $scopeCloser ) {
			$return = $phpcsFile->findNext( T_RETURN, $current + 1, $scopeCloser );

			if ( false === $return ) {
				break;
			}

			// Skip if inside nested closure/function.
			if ( $this->isInsideNestedScope( $phpcsFile, $return, $stackPtr ) ) {
				$current = $return;
				continue;
			}

			$type = $this->getReturnValueType( $phpcsFile, $return, $scopeCloser );

			if ( null !== $type ) {
				$returnTypes[] = $type;
			}

			$current = $return;
		}

		if ( empty( $returnTypes ) ) {
			return null;
		}

		// All returns must be the same certain type.
		$uniqueTypes = array_unique( $returnTypes );

		if ( count( $uniqueTypes ) === 1 ) {
			return $uniqueTypes[0];
		}

		// Multiple types - check if they're compatible.
		// For now, only return if all are the same.
		return null;
	}

	/**
	 * Get the type of a return value.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $returnPtr   The return token position.
	 * @param int  $scopeCloser The function scope closer.
	 *
	 * @return string|null The type if certain, null otherwise.
	 */
	private function getReturnValueType( File $phpcsFile, $returnPtr, $scopeCloser ) {
		$tokens = $phpcsFile->getTokens();

		$next = $phpcsFile->findNext( T_WHITESPACE, $returnPtr + 1, $scopeCloser, true );

		if ( false === $next ) {
			return null;
		}

		$code = $tokens[ $next ]['code'];

		// Hardcoded string.
		if ( $code === T_CONSTANT_ENCAPSED_STRING ) {
			return 'string';
		}

		// Hardcoded array.
		if ( $code === T_ARRAY || $code === T_OPEN_SHORT_ARRAY ) {
			return 'array';
		}

		// Hardcoded boolean.
		if ( $code === T_TRUE || $code === T_FALSE ) {
			return 'bool';
		}

		// Hardcoded integer.
		if ( $code === T_LNUMBER ) {
			return 'int';
		}

		// Hardcoded float.
		if ( $code === T_DNUMBER ) {
			return 'float';
		}

		return null;
	}

	/**
	 * Check if a token is inside a nested closure or function.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $tokenPtr    The token to check.
	 * @param int  $functionPtr The outer function token.
	 *
	 * @return bool
	 */
	private function isInsideNestedScope( File $phpcsFile, $tokenPtr, $functionPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( isset( $tokens[ $tokenPtr ]['conditions'] ) ) {
			foreach ( $tokens[ $tokenPtr ]['conditions'] as $scopePtr => $scopeType ) {
				if ( $scopePtr !== $functionPtr && in_array( $scopeType, array( T_CLOSURE, T_FUNCTION ), true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get existing @param tags from docblock.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $docblock  The docblock opener position.
	 *
	 * @return array Associative array of param name => true.
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

			// Find the parameter name in this @param line.
			for ( $j = $i + 1; $j < $closer; $j++ ) {
				if ( $tokens[ $j ]['code'] === T_DOC_COMMENT_STRING ) {
					// Extract variable name from the string.
					if ( preg_match( '/(\$\w+)/', $tokens[ $j ]['content'], $matches ) ) {
						$existing[ $matches[1] ] = true;
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
	 * Check if docblock has @return tag.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $docblock  The docblock opener position.
	 *
	 * @return bool
	 */
	private function hasReturnTag( File $phpcsFile, $docblock ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $docblock ]['comment_closer'] ) ) {
			return false;
		}

		$closer = $tokens[ $docblock ]['comment_closer'];

		for ( $i = $docblock; $i < $closer; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG && $tokens[ $i ]['content'] === '@return' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add a new docblock to a function.
	 *
	 * @param File        $phpcsFile     The file being scanned.
	 * @param int         $stackPtr      The function token position.
	 * @param array       $params        All function parameters.
	 * @param array       $certainParams Certain param types.
	 * @param string|null $returnType    The return type, or null.
	 *
	 * @return void
	 */
	private function addDocblock( File $phpcsFile, $stackPtr, $params, $certainParams, $returnType ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		// Find the line start.
		$lineStart = $stackPtr;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			$lineStart--;
		}

		// Get indentation.
		$indent = '';

		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			$indent = $tokens[ $lineStart ]['content'];
		}

		// Build docblock.
		$docblock = $indent . "/**\n";

		// Add @param tags for certain types only.
		foreach ( $params as $param ) {
			$paramName = $param['name'];

			if ( isset( $certainParams[ $paramName ] ) ) {
				$docblock .= $indent . " * @param " . $certainParams[ $paramName ] . " " . $paramName . "\n";
			}
		}

		// Add @return if certain.
		if ( null !== $returnType ) {
			if ( ! empty( $certainParams ) ) {
				$docblock .= $indent . " *\n";
			}
			$docblock .= $indent . " * @return " . $returnType . "\n";
		}

		$docblock .= $indent . " */\n";

		$fixer->beginChangeset();
		$fixer->addContentBefore( $lineStart, $docblock );
		$fixer->endChangeset();
	}

	/**
	 * Add missing annotations to existing docblock.
	 *
	 * @param File        $phpcsFile     The file being scanned.
	 * @param int         $docblock      The docblock opener position.
	 * @param array       $missingParams Missing param types.
	 * @param string|null $returnType    Missing return type, or null.
	 *
	 * @return void
	 */
	private function addMissingAnnotations( File $phpcsFile, $docblock, $missingParams, $returnType ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		$closer = $tokens[ $docblock ]['comment_closer'];

		// Find the last line before closing.
		$lastContentLine = $tokens[ $closer ]['line'] - 1;

		// Get indentation from docblock opener.
		$indent = '';
		$lineStart = $docblock;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $docblock ]['line'] ) {
			$lineStart--;
		}

		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			$indent = $tokens[ $lineStart ]['content'];
		}

		// Find position to insert (before the closing tag).
		$insertBefore = $closer;

		// Look for existing @return or @param to insert after.
		$lastTag = null;

		for ( $i = $docblock; $i < $closer; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG ) {
				$lastTag = $i;
			}
		}

		$fixer->beginChangeset();

		$content = '';

		// Add missing @param tags.
		foreach ( $missingParams as $paramName => $paramType ) {
			$content .= "\n" . $indent . " * @param " . $paramType . " " . $paramName;
		}

		// Add missing @return.
		if ( null !== $returnType ) {
			if ( ! empty( $missingParams ) ) {
				$content .= "\n" . $indent . " *";
			}
			$content .= "\n" . $indent . " * @return " . $returnType;
		}

		// Find the token just before the closing */ to insert after.
		$insertAfter = $closer - 1;

		while ( $insertAfter > $docblock && $tokens[ $insertAfter ]['code'] === T_DOC_COMMENT_WHITESPACE ) {
			$insertAfter--;
		}

		$fixer->addContent( $insertAfter, $content );
		$fixer->endChangeset();
	}
}
