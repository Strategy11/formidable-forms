<?php
/**
 * Formidable_Sniffs_Commenting_AddMissingTypeCommentsSniff
 *
 * Detects functions missing docblocks and adds type annotations for obvious cases:
 * - @return array for functions that only return array literals
 * - @return string for functions that only return hard-coded strings
 * - @param array for parameters named $args or $atts
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Adds missing type comments for obvious cases.
 */
class AddMissingTypeCommentsSniff implements Sniff {

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

		// Get function parameters.
		$params = $this->getFunctionParams( $phpcsFile, $stackPtr );

		// Get return type info.
		$returnType = $this->detectReturnType( $phpcsFile, $stackPtr );

		// Check for params that need @param array ($args, $atts).
		$missingParamDocs = $this->getMissingParamDocs( $phpcsFile, $docblock, $params );

		// Check for missing @return.
		$missingReturn = $this->getMissingReturn( $phpcsFile, $docblock, $returnType );

		if ( empty( $missingParamDocs ) && empty( $missingReturn ) ) {
			return;
		}

		// Build the error message.
		$errorParts = array();

		if ( ! empty( $missingParamDocs ) ) {
			$errorParts[] = '@param array for: ' . implode( ', ', array_keys( $missingParamDocs ) );
		}

		if ( ! empty( $missingReturn ) ) {
			$errorParts[] = '@return ' . $missingReturn;
		}

		$errorMessage = 'Missing type comments: ' . implode( '; ', $errorParts );

		$fix = $phpcsFile->addFixableError(
			$errorMessage,
			$stackPtr,
			'MissingTypeComments'
		);

		if ( true === $fix ) {
			$this->addMissingDocblock( $phpcsFile, $stackPtr, $docblock, $missingParamDocs, $missingReturn );
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
	 * @return array Array of parameter names.
	 */
	private function getFunctionParams( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$params = array();

		if ( ! isset( $tokens[ $stackPtr ]['parenthesis_opener'] ) || ! isset( $tokens[ $stackPtr ]['parenthesis_closer'] ) ) {
			return $params;
		}

		$opener = $tokens[ $stackPtr ]['parenthesis_opener'];
		$closer = $tokens[ $stackPtr ]['parenthesis_closer'];

		for ( $i = $opener + 1; $i < $closer; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_VARIABLE ) {
				$params[] = $tokens[ $i ]['content'];
			}
		}

		return $params;
	}

	/**
	 * Detect the return type of a function.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 *
	 * @return string The detected return type ('array', 'string', or empty).
	 */
	private function detectReturnType( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		$returnStatements = array();
		$current          = $scopeOpener;

		while ( $current < $scopeCloser ) {
			$return = $phpcsFile->findNext( T_RETURN, $current + 1, $scopeCloser );

			if ( false === $return ) {
				break;
			}

			// Check if this return is inside a nested closure/function.
			if ( $this->isInsideNestedScope( $phpcsFile, $return, $stackPtr ) ) {
				$current = $return;
				continue;
			}

			$nextToken = $phpcsFile->findNext( T_WHITESPACE, $return + 1, null, true );

			if ( false === $nextToken || $tokens[ $nextToken ]['code'] === T_SEMICOLON ) {
				// Empty return.
				$returnStatements[] = 'void';
			} elseif ( $tokens[ $nextToken ]['code'] === T_ARRAY || $tokens[ $nextToken ]['code'] === T_OPEN_SHORT_ARRAY ) {
				$returnStatements[] = 'array';
			} elseif ( $tokens[ $nextToken ]['code'] === T_CONSTANT_ENCAPSED_STRING ) {
				$returnStatements[] = 'string';
			} else {
				$returnStatements[] = 'unknown';
			}

			$current = $return;
		}

		if ( empty( $returnStatements ) ) {
			return '';
		}

		// All returns must be the same type.
		$uniqueTypes = array_unique( $returnStatements );

		if ( count( $uniqueTypes ) === 1 ) {
			$type = $uniqueTypes[0];

			if ( 'array' === $type || 'string' === $type ) {
				return $type;
			}
		}

		return '';
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
	 * Get parameters that need @param array documentation.
	 *
	 * @param File      $phpcsFile The file being scanned.
	 * @param false|int $docblock  The docblock opener position, or false.
	 * @param array     $params    Array of parameter names.
	 *
	 * @return array Array of parameter names that need documentation.
	 */
	private function getMissingParamDocs( File $phpcsFile, $docblock, $params ) {
		$needsDocs   = array();
		$targetNames = array( '$args', '$atts' );

		foreach ( $params as $param ) {
			if ( ! in_array( $param, $targetNames, true ) ) {
				continue;
			}

			// Check if already documented.
			if ( false !== $docblock && $this->hasParamDoc( $phpcsFile, $docblock, $param ) ) {
				continue;
			}

			$needsDocs[ $param ] = 'array';
		}

		return $needsDocs;
	}

	/**
	 * Check if a parameter is documented in the docblock.
	 *
	 * @param File $phpcsFile     The file being scanned.
	 * @param int  $docblockStart The docblock opener position.
	 * @param string $paramName   The parameter name to check.
	 *
	 * @return bool
	 */
	private function hasParamDoc( File $phpcsFile, $docblockStart, $paramName ) {
		$tokens      = $phpcsFile->getTokens();
		$docblockEnd = $tokens[ $docblockStart ]['comment_closer'];

		for ( $i = $docblockStart; $i < $docblockEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG && $tokens[ $i ]['content'] === '@param' ) {
				// Check the content after @param for the variable name.
				$contentToken = $phpcsFile->findNext( T_DOC_COMMENT_STRING, $i + 1, $docblockEnd );

				if ( false !== $contentToken && strpos( $tokens[ $contentToken ]['content'], $paramName ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get missing @return type if applicable.
	 *
	 * @param File      $phpcsFile  The file being scanned.
	 * @param false|int $docblock   The docblock opener position, or false.
	 * @param string    $returnType The detected return type.
	 *
	 * @return string The missing return type, or empty string.
	 */
	private function getMissingReturn( File $phpcsFile, $docblock, $returnType ) {
		if ( empty( $returnType ) ) {
			return '';
		}

		// Check if already has @return.
		if ( false !== $docblock && $this->hasReturnDoc( $phpcsFile, $docblock ) ) {
			return '';
		}

		return $returnType;
	}

	/**
	 * Check if the docblock has a @return tag.
	 *
	 * @param File $phpcsFile     The file being scanned.
	 * @param int  $docblockStart The docblock opener position.
	 *
	 * @return bool
	 */
	private function hasReturnDoc( File $phpcsFile, $docblockStart ) {
		$tokens      = $phpcsFile->getTokens();
		$docblockEnd = $tokens[ $docblockStart ]['comment_closer'];

		for ( $i = $docblockStart; $i < $docblockEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG && $tokens[ $i ]['content'] === '@return' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add or update the docblock with missing type comments.
	 *
	 * @param File      $phpcsFile       The file being scanned.
	 * @param int       $stackPtr        The function token position.
	 * @param false|int $docblock        The existing docblock opener, or false.
	 * @param array     $missingParamDocs Parameters needing documentation.
	 * @param string    $missingReturn   The missing return type.
	 *
	 * @return void
	 */
	private function addMissingDocblock( File $phpcsFile, $stackPtr, $docblock, $missingParamDocs, $missingReturn ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		if ( false === $docblock ) {
			// No existing docblock - create a new one.
			$this->createNewDocblock( $phpcsFile, $stackPtr, $missingParamDocs, $missingReturn );
		} else {
			// Existing docblock - add missing entries.
			$this->updateExistingDocblock( $phpcsFile, $docblock, $missingParamDocs, $missingReturn );
		}
	}

	/**
	 * Create a new docblock for a function.
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param int    $stackPtr        The function token position.
	 * @param array  $missingParamDocs Parameters needing documentation.
	 * @param string $missingReturn   The missing return type.
	 *
	 * @return void
	 */
	private function createNewDocblock( File $phpcsFile, $stackPtr, $missingParamDocs, $missingReturn ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		// Find the indentation of the function.
		$indent = $this->getIndentation( $phpcsFile, $stackPtr );

		// Build the docblock.
		$docblock = $indent . "/**\n";

		foreach ( $missingParamDocs as $paramName => $paramType ) {
			$docblock .= $indent . " * @param {$paramType} {$paramName}\n";
		}

		if ( ! empty( $missingParamDocs ) && ! empty( $missingReturn ) ) {
			$docblock .= $indent . " *\n";
		}

		if ( ! empty( $missingReturn ) ) {
			$docblock .= $indent . " * @return {$missingReturn}\n";
		}

		$docblock .= $indent . " */\n";

		// Find the position to insert (before any modifiers like public/private/static).
		$insertPos = $this->findInsertPosition( $phpcsFile, $stackPtr );

		$fixer->addContentBefore( $insertPos, $docblock );
	}

	/**
	 * Update an existing docblock with missing entries.
	 *
	 * @param File   $phpcsFile       The file being scanned.
	 * @param int    $docblockStart   The docblock opener position.
	 * @param array  $missingParamDocs Parameters needing documentation.
	 * @param string $missingReturn   The missing return type.
	 *
	 * @return void
	 */
	private function updateExistingDocblock( File $phpcsFile, $docblockStart, $missingParamDocs, $missingReturn ) {
		$tokens       = $phpcsFile->getTokens();
		$fixer        = $phpcsFile->fixer;
		$docblockEnd  = $tokens[ $docblockStart ]['comment_closer'];

		// Find the indentation from the docblock.
		$indent = $this->getIndentation( $phpcsFile, $docblockStart );

		// Find the last @param or last tag position.
		$lastParamPos = false;
		$lastTagPos   = false;

		for ( $i = $docblockStart; $i < $docblockEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_TAG ) {
				$lastTagPos = $i;

				if ( $tokens[ $i ]['content'] === '@param' ) {
					$lastParamPos = $i;
				}
			}
		}

		// Add missing @param entries after the last @param or before closing.
		if ( ! empty( $missingParamDocs ) ) {
			$insertAfter = false !== $lastParamPos ? $this->findEndOfLine( $phpcsFile, $lastParamPos, $docblockEnd ) : false;

			if ( false === $insertAfter ) {
				$insertAfter = $docblockEnd - 1;
			}

			$paramContent = '';

			foreach ( $missingParamDocs as $paramName => $paramType ) {
				$paramContent .= "\n" . $indent . " * @param {$paramType} {$paramName}";
			}

			$fixer->addContent( $insertAfter, $paramContent );
		}

		// Add missing @return before the closing tag.
		if ( ! empty( $missingReturn ) ) {
			// We need to insert before the closing */ but on a new line.
			// Find the whitespace token that contains the newline before */.
			$beforeClose = $docblockEnd - 1;

			if ( $tokens[ $beforeClose ]['code'] === T_DOC_COMMENT_WHITESPACE ) {
				// Replace the whitespace with our new content plus proper closing.
				// The existing whitespace already has a newline, so we start with the blank line marker.
				$returnContent = $indent . " *\n" . $indent . " * @return {$missingReturn}\n" . $indent . ' ';
				$fixer->replaceToken( $beforeClose, $returnContent );
			}
		}
	}

	/**
	 * Get the indentation for a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The token position.
	 *
	 * @return string The indentation string.
	 */
	private function getIndentation( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Find the start of the line.
		$lineStart = $stackPtr;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			$lineStart--;
		}

		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			return $tokens[ $lineStart ]['content'];
		}

		return '';
	}

	/**
	 * Find the position to insert a new docblock.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The function token position.
	 *
	 * @return int The position to insert before.
	 */
	private function findInsertPosition( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$modifiers = array(
			T_PUBLIC,
			T_PRIVATE,
			T_PROTECTED,
			T_STATIC,
			T_ABSTRACT,
			T_FINAL,
		);

		$pos = $stackPtr;

		while ( $pos > 0 ) {
			$prev = $phpcsFile->findPrevious( T_WHITESPACE, $pos - 1, null, true );

			if ( false === $prev || ! in_array( $tokens[ $prev ]['code'], $modifiers, true ) ) {
				break;
			}

			$pos = $prev;
		}

		// Find the start of the line.
		while ( $pos > 0 && $tokens[ $pos - 1 ]['line'] === $tokens[ $pos ]['line'] ) {
			$pos--;
		}

		return $pos;
	}

	/**
	 * Find the end of a line in a docblock.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $startPos     The starting position.
	 * @param int  $docblockEnd  The docblock end position.
	 *
	 * @return false|int The position at the end of the line, or false.
	 */
	private function findEndOfLine( File $phpcsFile, $startPos, $docblockEnd ) {
		$tokens      = $phpcsFile->getTokens();
		$currentLine = $tokens[ $startPos ]['line'];

		for ( $i = $startPos; $i < $docblockEnd; $i++ ) {
			if ( $tokens[ $i ]['line'] > $currentLine ) {
				return $i - 1;
			}
		}

		return false;
	}
}
