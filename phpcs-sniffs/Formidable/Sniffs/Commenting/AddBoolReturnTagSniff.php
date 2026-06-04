<?php
/**
 * Add @return bool to functions that clearly return boolean expressions.
 */

namespace Formidable\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class AddBoolReturnTagSniff implements Sniff {
	/**
	 * {@inheritdoc}
	 */
	public function register() {
		return array( T_FUNCTION );
	}

	/**
	 * {@inheritdoc}
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $stackPtr ]['scope_opener'] ) || ! isset( $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		if ( ! $this->returnsGuaranteedBool( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$docblock = $this->findDocblock( $phpcsFile, $stackPtr );

		if ( false === $docblock ) {
			$fix = $phpcsFile->addFixableError( 'Missing docblock with @return bool.', $stackPtr, 'MissingDocblock' );

			if ( $fix ) {
				$this->addDocblock( $phpcsFile, $stackPtr );
			}

			return;
		}

		if ( $this->hasReturnTag( $phpcsFile, $docblock ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError( 'Docblock missing @return bool.', $docblock, 'MissingReturnBool' );

		if ( $fix ) {
			$this->addReturnAnnotation( $phpcsFile, $docblock );
		}
	}

	/**
	 * Determine if every top-level return in the function is guaranteed boolean.
	 */
	private function returnsGuaranteedBool( File $phpcsFile, $stackPtr ) {
		$tokens      = $phpcsFile->getTokens();
		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];
		$current     = $scopeOpener;
		$foundReturn = false;

		while ( $current < $scopeCloser ) {
			$returnPtr = $phpcsFile->findNext( T_RETURN, $current + 1, $scopeCloser );

			if ( false === $returnPtr ) {
				break;
			}

			if ( $this->isInsideNestedScope( $phpcsFile, $returnPtr, $stackPtr ) ) {
				$current = $returnPtr;
				continue;
			}

			$foundReturn = true;

			if ( ! $this->isBoolReturnStatement( $phpcsFile, $returnPtr, $scopeCloser ) ) {
				return false;
			}

			$current = $returnPtr;
		}

		return $foundReturn;
	}

	/**
	 * Check whether a return statement results in a boolean value.
	 */
	private function isBoolReturnStatement( File $phpcsFile, $returnPtr, $scopeCloser ) {
		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $returnPtr + 1, $scopeCloser );

		if ( false === $semicolon ) {
			return false;
		}

		// Skip ternaries entirely.
		$ternary = $phpcsFile->findNext( T_INLINE_THEN, $returnPtr + 1, $semicolon );

		if ( false !== $ternary ) {
			return false;
		}

		$valuePtr = $phpcsFile->findNext( T_WHITESPACE, $returnPtr + 1, $semicolon, true );

		if ( false === $valuePtr ) {
			return false;
		}

		$tokens = $phpcsFile->getTokens();
		$code   = $tokens[ $valuePtr ]['code'];

		if ( $code === T_TRUE || $code === T_FALSE ) {
			$afterBool = $phpcsFile->findNext( T_WHITESPACE, $valuePtr + 1, $semicolon, true );
			return false === $afterBool;
		}

		return $this->isBooleanChainExpression( $phpcsFile, $valuePtr, $semicolon );
	}

	/**
	 * Determine if an expression is a boolean chain using logical operators.
	 */
	private function isBooleanChainExpression( File $phpcsFile, $startPtr, $endPtr ) {
		$tokens             = $phpcsFile->getTokens();
		$hasBooleanOperator = false;
		$assignmentTokens   = array(
			T_EQUAL,
			T_AND_EQUAL,
			T_OR_EQUAL,
			T_PLUS_EQUAL,
			T_MINUS_EQUAL,
			T_MUL_EQUAL,
			T_DIV_EQUAL,
			T_MOD_EQUAL,
			T_POW_EQUAL,
			T_CONCAT_EQUAL,
		);

		for ( $i = $startPtr; $i < $endPtr; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( isset( Tokens::$emptyTokens[ $code ] ) ) {
				continue;
			}

			if ( in_array( $code, array( T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR ), true ) ) {
				$hasBooleanOperator = true;
				continue;
			}

			if ( in_array( $code, $assignmentTokens, true ) ) {
				return false;
			}

			if ( $code === T_INLINE_THEN || $code === T_INLINE_ELSE ) {
				return false;
			}
		}

		return $hasBooleanOperator;
	}

	/**
	 * Find the docblock attached to this function.
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
	 * Check if a docblock already has a @return tag.
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
	 * Add an entire docblock with @return bool.
	 */
	private function addDocblock( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;
		$line   = $stackPtr;

		while ( $line > 0 && $tokens[ $line - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			--$line;
		}

		$indent = '';

		if ( $tokens[ $line ]['code'] === T_WHITESPACE ) {
			$indent = $tokens[ $line ]['content'];
		}

		$docblock  = $indent . "/**\n";
		$docblock .= $indent . " * @return bool\n";
		$docblock .= $indent . " */\n";

		$fixer->beginChangeset();
		$fixer->addContentBefore( $line, $docblock );
		$fixer->endChangeset();
	}

	/**
	 * Insert @return bool into an existing docblock.
	 */
	private function addReturnAnnotation( File $phpcsFile, $docblock ) {
		$tokens = $phpcsFile->getTokens();
		$fixer  = $phpcsFile->fixer;

		if ( ! isset( $tokens[ $docblock ]['comment_closer'] ) ) {
			return;
		}

		$closer = $tokens[ $docblock ]['comment_closer'];
		$line   = $docblock;

		while ( $line > 0 && $tokens[ $line - 1 ]['line'] === $tokens[ $docblock ]['line'] ) {
			--$line;
		}

		$indent = '';

		if ( $tokens[ $line ]['code'] === T_WHITESPACE ) {
			$indent = $tokens[ $line ]['content'];
		}

		$fixer->beginChangeset();
		$fixer->addContentBefore( $closer, "\n" . $indent . " * @return bool" . "\n" . $indent . ' ' );
		$fixer->endChangeset();
	}

	/**
	 * Check if a token is inside a nested closure/function.
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
}
