<?php
/**
 * Ensures inline filter docblocks reuse the function's @param types when the
 * referenced parameter hasn't been modified before the filter call.
 *
 * @package Formidable\Sniffs\Commenting
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects inline filter docblocks where @param types diverge from the function docblock.
 */
class AlignFilterParamTypesWithFunctionSniff implements Sniff {

	/**
	 * Filter helper functions we care about.
	 *
	 * @var array
	 */
	private $filterFunctions = array(
		'apply_filters',
		'apply_filters_ref_array',
		'apply_filters_deprecated',
	);

	/**
	 * Tokens that represent assignments.
	 *
	 * @var array
	 */
	private $assignmentTokens = array(
		T_EQUAL,
		T_PLUS_EQUAL,
		T_MINUS_EQUAL,
		T_MUL_EQUAL,
		T_DIV_EQUAL,
		T_CONCAT_EQUAL,
		T_MOD_EQUAL,
		T_AND_EQUAL,
		T_OR_EQUAL,
		T_XOR_EQUAL,
		T_SL_EQUAL,
		T_SR_EQUAL,
	);

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		return array( T_FUNCTION );
	}

	/**
	 * {@inheritDoc}
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'], $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$functionDocblock = $this->findFunctionDocblock( $phpcsFile, $stackPtr );

		if ( false === $functionDocblock ) {
			return;
		}

		$functionParams = $this->getDocblockParamTags( $phpcsFile, $functionDocblock );

		if ( empty( $functionParams ) ) {
			return;
		}

		$scopeStart = $tokens[ $stackPtr ]['scope_opener'];
		$scopeEnd   = $tokens[ $stackPtr ]['scope_closer'];

		for ( $i = $scopeStart + 1; $i < $scopeEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_DOC_COMMENT_OPEN_TAG ) {
				continue;
			}

			if ( ! isset( $tokens[ $i ]['comment_closer'] ) ) {
				continue;
			}

			$docblockClose = $tokens[ $i ]['comment_closer'];
			$nextToken     = $phpcsFile->findNext( T_WHITESPACE, $docblockClose + 1, $scopeEnd, true );

			if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_RETURN ) {
				$i = $docblockClose;
				continue;
			}

			$filterCall = $this->findFilterCallAfterReturn( $phpcsFile, $nextToken, $scopeEnd );

			if ( false === $filterCall ) {
				$i = $docblockClose;
				continue;
			}

			$inlineParams = $this->getDocblockParamTags( $phpcsFile, $i );

			if ( empty( $inlineParams ) ) {
				$i = $docblockClose;
				continue;
			}

			foreach ( $inlineParams as $paramName => $paramInfo ) {
				if ( ! isset( $functionParams[ $paramName ] ) ) {
					continue;
				}

				if ( empty( $functionParams[ $paramName ]['normalized'] ) || empty( $paramInfo['normalized'] ) ) {
					continue;
				}

				if ( $this->variableModifiedBeforeDocblock( $phpcsFile, $paramName, $scopeStart, $i ) ) {
					continue;
				}

				if ( $this->typesMatch( $functionParams[ $paramName ]['normalized'], $paramInfo['normalized'] ) ) {
					continue;
				}

				$fix = $phpcsFile->addFixableError(
					'Filter docblock type for %s should match function @param type "%s" since the variable is unmodified before the filter.',
					$paramInfo['token'],
					'FilterParamTypeMismatch',
					array(
						$paramName,
						$functionParams[ $paramName ]['type'],
					)
				);

				if ( true === $fix ) {
					$this->replaceParamType( $phpcsFile, $paramInfo['token'], $functionParams[ $paramName ]['type'] );
				}
			}

			$i = $docblockClose;
		}
	}

	/**
	 * Find the docblock that documents the function itself.
	 *
	 * @param File $phpcsFile File reference.
	 * @param int  $stackPtr  Function pointer.
	 *
	 * @return false|int
	 */
	private function findFunctionDocblock( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$ignore = array(
			T_WHITESPACE,
			T_STATIC,
			T_PUBLIC,
			T_PRIVATE,
			T_PROTECTED,
			T_ABSTRACT,
			T_FINAL,
			T_ATTRIBUTE,
		);

		$prev = $phpcsFile->findPrevious( $ignore, $stackPtr - 1, null, true );

		if ( false !== $prev && $tokens[ $prev ]['code'] === T_DOC_COMMENT_CLOSE_TAG ) {
			return $tokens[ $prev ]['comment_opener'];
		}

		return false;
	}

	/**
	 * Parse @param tags within a docblock.
	 *
	 * @param File $phpcsFile  File reference.
	 * @param int  $docblockPt Docblock opener pointer.
	 *
	 * @return array
	 */
	private function getDocblockParamTags( File $phpcsFile, $docblockPt ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $docblockPt ]['comment_closer'] ) ) {
			return array();
		}

		$closer = $tokens[ $docblockPt ]['comment_closer'];
		$params = array();

		for ( $i = $docblockPt; $i <= $closer; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_DOC_COMMENT_TAG || '@param' !== $tokens[ $i ]['content'] ) {
				continue;
			}

			$stringPtr = $phpcsFile->findNext( T_DOC_COMMENT_STRING, $i + 1, $closer );

			if ( false === $stringPtr ) {
				continue;
			}

			$content = trim( $tokens[ $stringPtr ]['content'] );

			if ( ! preg_match( '/^([^\s]+)\s+(\$\w+)/', $content, $matches ) ) {
				continue;
			}

			$type      = trim( $matches[1] );
			$paramName = $matches[2];

			$params[ $paramName ] = array(
				'type'       => $type,
				'normalized' => $this->normalizeTypeList( $type ),
				'token'      => $stringPtr,
			);
		}

		return $params;
	}

	/**
	 * Find the filter call after a return statement.
	 *
	 * @param File $phpcsFile File reference.
	 * @param int  $returnPtr Return token pointer.
	 * @param int  $scopeEnd  Function scope end.
	 *
	 * @return false|int
	 */
	private function findFilterCallAfterReturn( File $phpcsFile, $returnPtr, $scopeEnd ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $returnPtr + 1; $i < $scopeEnd; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_SEMICOLON ) {
				break;
			}

			if ( $code === T_STRING && in_array( $tokens[ $i ]['content'], $this->filterFunctions, true ) ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Determine if a variable is modified before a certain docblock.
	 *
	 * @param File   $phpcsFile  File reference.
	 * @param string $paramName  Parameter name (with $).
	 * @param int    $scopeStart Function scope start brace.
	 * @param int    $docblockPt Docblock pointer.
	 *
	 * @return bool
	 */
	private function variableModifiedBeforeDocblock( File $phpcsFile, $paramName, $scopeStart, $docblockPt ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $scopeStart + 1; $i < $docblockPt; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE || $tokens[ $i ]['content'] !== $paramName ) {
				continue;
			}

			$prev = $phpcsFile->findPrevious( T_WHITESPACE, $i - 1, null, true );

			if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_INC, T_DEC ), true ) ) {
				return true;
			}

			$next = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false === $next ) {
				continue;
			}

			if ( in_array( $tokens[ $next ]['code'], $this->assignmentTokens, true ) ) {
				return true;
			}

			if ( in_array( $tokens[ $next ]['code'], array( T_INC, T_DEC ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalize a set of type strings.
	 *
	 * @param string $typeString Type string from a docblock.
	 *
	 * @return array
	 */
	private function normalizeTypeList( $typeString ) {
		$typeString = trim( $typeString );

		if ( '' === $typeString ) {
			return array();
		}

		if ( strpos( $typeString, '?' ) === 0 ) {
			$typeString = 'null|' . substr( $typeString, 1 );
		}

		$parts      = explode( '|', $typeString );
		$normalized = array();

		foreach ( $parts as $part ) {
			$single = $this->normalizeType( $part );

			if ( '' !== $single ) {
				$normalized[] = $single;
			}
		}

		$normalized = array_values( array_unique( $normalized ) );
		sort( $normalized );

		return $normalized;
	}

	/**
	 * Normalize a single type entry for comparison.
	 *
	 * @param string $type Type fragment.
	 *
	 * @return string
	 */
	private function normalizeType( $type ) {
		$type = trim( $type );

		if ( '' === $type ) {
			return '';
		}

		if ( substr( $type, -2 ) === '[]' ) {
			return 'array';
		}

		if ( preg_match( '/^array\s*[<{]/i', $type ) ) {
			return 'array';
		}

		$lower = strtolower( ltrim( $type, '\\' ) );

		$aliases = array(
			'integer' => 'int',
			'boolean' => 'bool',
			'double'  => 'float',
			'real'    => 'float',
			'number'  => 'float',
			'true'    => 'bool',
		);

		if ( isset( $aliases[ $lower ] ) ) {
			return $aliases[ $lower ];
		}

		return $lower;
	}

	/**
	 * Compare two normalized type lists.
	 *
	 * @param array $functionTypes Function doc types.
	 * @param array $inlineTypes   Inline doc types.
	 *
	 * @return bool
	 */
	private function typesMatch( array $functionTypes, array $inlineTypes ) {
		return $functionTypes === $inlineTypes;
	}

	/**
	 * Replace the @param type in an inline docblock.
	 *
	 * @param File   $phpcsFile File reference.
	 * @param int    $tokenPtr  Doc comment string pointer.
	 * @param string $newType   Replacement type string.
	 *
	 * @return void
	 */
	private function replaceParamType( File $phpcsFile, $tokenPtr, $newType ) {
		$tokens   = $phpcsFile->getTokens();
		$content  = $tokens[ $tokenPtr ]['content'];
		$newValue = preg_replace( '/^[^\s]+/', $newType, $content, 1 );

		if ( null === $newValue ) {
			return;
		}

		$phpcsFile->fixer->beginChangeset();
		$phpcsFile->fixer->replaceToken( $tokenPtr, $newValue );
		$phpcsFile->fixer->endChangeset();
	}
}
