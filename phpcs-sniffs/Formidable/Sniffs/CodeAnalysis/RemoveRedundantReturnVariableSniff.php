<?php
/**
 * Detects functions that assign to a temporary variable, override it once, and immediately return it.
 * Suggests converting these patterns into early returns.
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class RemoveRedundantReturnVariableSniff implements Sniff {

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

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		$lastReturn = $this->findLastTopLevelReturn( $phpcsFile, $tokens, $scopeOpener, $scopeCloser );

		if ( false === $lastReturn ) {
			return;
		}

		$returnInfo = $this->getReturnVariableInfo( $phpcsFile, $tokens, $lastReturn, $scopeCloser );

		if ( false === $returnInfo ) {
			return;
		}

		$variableName     = $returnInfo['name'];
		$defaultAssignment = $this->findDefaultAssignment( $phpcsFile, $tokens, $scopeOpener, $lastReturn, $variableName );

		if ( false === $defaultAssignment ) {
			return;
		}

		$branchAssignment = $this->findBranchAssignment( $phpcsFile, $tokens, $scopeOpener, $scopeCloser, $variableName, array( $defaultAssignment['var_ptr'], $returnInfo['var_ptr'] ) );

		if ( false === $branchAssignment ) {
			return;
		}

		if ( ! $this->assignmentInsideSimpleIf( $tokens, $branchAssignment ) ) {
			return;
		}

		if ( ! $this->onlyReturnAfterAssignment( $phpcsFile, $tokens, $branchAssignment['semicolon'], $lastReturn ) ) {
			return;
		}

		if ( $this->isVariableUsedElsewhere( $tokens, $scopeOpener, $scopeCloser, $variableName, array( $defaultAssignment['var_ptr'], $branchAssignment['var_ptr'], $returnInfo['var_ptr'] ) ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Return variable %s can be replaced with direct returns.',
			$defaultAssignment['var_ptr'],
			'RedundantReturnVariable',
			array( $variableName )
		);

		if ( true === $fix ) {
			$this->applyFix( $phpcsFile, $defaultAssignment, $branchAssignment, $returnInfo );
		}
	}

	/**
	 * Find the last top-level return token.
	 */
	private function findLastTopLevelReturn( File $phpcsFile, array $tokens, $scopeOpener, $scopeCloser ) {
		$current = $scopeCloser - 1;

		while ( $current > $scopeOpener ) {
			$current = $phpcsFile->findPrevious( T_RETURN, $current, $scopeOpener );

			if ( false === $current ) {
				return false;
			}

			if ( $this->isAtFunctionTopLevel( $tokens, $current, $scopeOpener ) ) {
				return $current;
			}

			--$current;
		}

		return false;
	}

	/**
	 * Check if a token is at the function's top level (not inside other braces).
	 */
	private function isAtFunctionTopLevel( array $tokens, $stackPtr, $scopeOpener ) {
		$depth = 0;

		for ( $i = $scopeOpener + 1; $i < $stackPtr; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_OPEN_CURLY_BRACKET ) {
				++$depth;
			} elseif ( $tokens[ $i ]['code'] === T_CLOSE_CURLY_BRACKET ) {
				--$depth;
			}
		}

		return 0 === $depth;
	}

	/**
	 * Get info about the variable being returned.
	 */
	private function getReturnVariableInfo( File $phpcsFile, array $tokens, $returnPtr, $scopeCloser ) {
		$valuePtr = $phpcsFile->findNext( T_WHITESPACE, $returnPtr + 1, $scopeCloser, true );

		if ( false === $valuePtr || $tokens[ $valuePtr ]['code'] !== T_VARIABLE ) {
			return false;
		}

		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $valuePtr + 1, $scopeCloser );

		if ( false === $semicolon ) {
			return false;
		}

		$afterVar = $phpcsFile->findNext( T_WHITESPACE, $valuePtr + 1, $scopeCloser, true );

		if ( false === $afterVar || $afterVar !== $semicolon ) {
			return false;
		}

		return array(
			'name'       => $tokens[ $valuePtr ]['content'],
			'var_ptr'    => $valuePtr,
			'return_ptr' => $returnPtr,
			'semicolon'  => $semicolon,
			'line_start' => $this->findLineStart( $phpcsFile, $returnPtr ),
			'indent'     => $this->getIndentation( $phpcsFile, $returnPtr ),
		);
	}

	/**
	 * Locate the top-level default assignment for the variable.
	 */
	private function findDefaultAssignment( File $phpcsFile, array $tokens, $scopeOpener, $returnPtr, $variableName ) {
		for ( $i = $scopeOpener + 1; $i < $returnPtr; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE || $tokens[ $i ]['content'] !== $variableName ) {
				continue;
			}

			if ( ! $this->isAtFunctionTopLevel( $tokens, $i, $scopeOpener ) ) {
				continue;
			}

			$assignment = $this->getAssignmentInfo( $phpcsFile, $tokens, $i );

			if ( false === $assignment ) {
				continue;
			}

			return $assignment;
		}

		return false;
	}

	/**
	 * Find the single branch assignment.
	 */
	private function findBranchAssignment( File $phpcsFile, array $tokens, $scopeOpener, $scopeCloser, $variableName, array $ignoredPtrs ) {
		$assignment = false;

		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE || $tokens[ $i ]['content'] !== $variableName ) {
				continue;
			}

			if ( in_array( $i, $ignoredPtrs, true ) ) {
				continue;
			}

			$info = $this->getAssignmentInfo( $phpcsFile, $tokens, $i );

			if ( false === $info ) {
				return false;
			}

			if ( false !== $assignment ) {
				return false;
			}

			$assignment = $info;
		}

		return $assignment;
	}

	/**
	 * Extract assignment metadata for the variable occurrence.
	 */
	private function getAssignmentInfo( File $phpcsFile, array $tokens, $varPtr ) {
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, $varPtr + 1, null, true );

		if ( false === $nextToken || $tokens[ $nextToken ]['code'] !== T_EQUAL ) {
			return false;
		}

		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $nextToken + 1 );

		if ( false === $semicolon ) {
			return false;
		}

		$valueStart = $phpcsFile->findNext( T_WHITESPACE, $nextToken + 1, $semicolon, true );

		if ( false === $valueStart ) {
			return false;
		}

		$valueContent = '';

		for ( $i = $valueStart; $i < $semicolon; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_COMMENT || $tokens[ $i ]['code'] === T_DOC_COMMENT_STRING ) {
				return false;
			}

			$valueContent .= $tokens[ $i ]['content'];
		}

		return array(
			'var_ptr'    => $varPtr,
			'equal_ptr'  => $nextToken,
			'value'      => trim( $valueContent ),
			'value_ptr'  => $valueStart,
			'semicolon'  => $semicolon,
			'line_start' => $this->findLineStart( $phpcsFile, $varPtr ),
			'indent'     => $this->getIndentation( $phpcsFile, $varPtr ),
		);
	}

	/**
	 * Ensure the assignment occurs inside an if block without else/elseif and is last statement.
	 */
	private function assignmentInsideSimpleIf( array $tokens, array $assignment ) {
		if ( ! isset( $tokens[ $assignment['var_ptr'] ]['conditions'] ) ) {
			return false;
		}

		$ownerIf = false;

		foreach ( $tokens[ $assignment['var_ptr'] ]['conditions'] as $ptr => $code ) {
			if ( T_IF === $code ) {
				$ownerIf = $ptr;
			}
		}

		if ( false === $ownerIf || ! isset( $tokens[ $ownerIf ]['scope_closer'] ) ) {
			return false;
		}

		if ( $this->hasElseOrElseif( $tokens, $ownerIf ) ) {
			return false;
		}

		$scopeCloser = $tokens[ $ownerIf ]['scope_closer'];

		return $this->assignmentIsLastInScope( $assignment['semicolon'], $scopeCloser, $tokens );
	}

	/**
	 * Check for else/elseif following an if block.
	 */
	private function hasElseOrElseif( array $tokens, $ifPtr ) {
		$scopeCloser = $tokens[ $ifPtr ]['scope_closer'];
		$next        = $scopeCloser + 1;

		while ( isset( $tokens[ $next ] ) && $tokens[ $next ]['code'] === T_WHITESPACE ) {
			++$next;
		}

		if ( ! isset( $tokens[ $next ] ) ) {
			return false;
		}

		return in_array( $tokens[ $next ]['code'], array( T_ELSE, T_ELSEIF ), true );
	}

	/**
	 * Confirm no additional statements occur before the scope closer.
	 */
	private function assignmentIsLastInScope( $semicolon, $scopeCloser, array $tokens ) {
		for ( $i = $semicolon + 1; $i < $scopeCloser; $i++ ) {
			if ( in_array( $tokens[ $i ]['code'], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT_OPEN_TAG, T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_STRING, T_DOC_COMMENT_WHITESPACE ), true ) ) {
				continue;
			}

			if ( $tokens[ $i ]['code'] === T_CLOSE_CURLY_BRACKET ) {
				continue;
			}

			return false;
		}

		return true;
	}

	/**
	 * Ensure no other statements exist before the final return.
	 */
	private function onlyReturnAfterAssignment( File $phpcsFile, array $tokens, $assignmentSemicolon, $returnPtr ) {
		$next = $phpcsFile->findNext( array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT_OPEN_TAG, T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_STRING, T_DOC_COMMENT_WHITESPACE, T_CLOSE_CURLY_BRACKET ), $assignmentSemicolon + 1, $returnPtr, true );

		return false === $next;
	}

	/**
	 * Detect other usages of the variable inside the function.
	 */
	private function isVariableUsedElsewhere( array $tokens, $scopeOpener, $scopeCloser, $variableName, array $allowedPtrs ) {
		for ( $i = $scopeOpener + 1; $i < $scopeCloser; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_VARIABLE || $tokens[ $i ]['content'] !== $variableName ) {
				continue;
			}

			if ( in_array( $i, $allowedPtrs, true ) ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Apply fixer changes.
	 */
	private function applyFix( File $phpcsFile, array $defaultAssignment, array $branchAssignment, array $returnInfo ) {
		$fixer = $phpcsFile->fixer;
		$fixer->beginChangeset();

		$this->removeStatement( $phpcsFile, $defaultAssignment );
		$this->replaceAssignmentWithReturn( $phpcsFile, $branchAssignment );
		$this->replaceReturnWithDefault( $phpcsFile, $returnInfo, $defaultAssignment['value'] );

		$fixer->endChangeset();
	}

	/**
	 * Remove the default assignment statement.
	 */
	private function removeStatement( File $phpcsFile, array $assignment ) {
		$fixer  = $phpcsFile->fixer;
		$tokens = $phpcsFile->getTokens();

		for ( $i = $assignment['line_start']; $i <= $assignment['semicolon']; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		$next = $assignment['semicolon'] + 1;

		if ( isset( $tokens[ $next ] ) && $tokens[ $next ]['code'] === T_WHITESPACE ) {
			$ws = $tokens[ $next ]['content'];

			if ( strpos( $ws, "\n" ) === 0 ) {
				$fixer->replaceToken( $next, substr( $ws, 1 ) );
			}
		}
	}

	/**
	 * Replace the branch assignment with a return statement.
	 */
	private function replaceAssignmentWithReturn( File $phpcsFile, array $assignment ) {
		$fixer  = $phpcsFile->fixer;
		$tokens = $phpcsFile->getTokens();

		$newCode = $assignment['indent'] . 'return ' . $assignment['value'] . ';' . $phpcsFile->eolChar;
		$fixer->replaceToken( $assignment['line_start'], $newCode );

		for ( $i = $assignment['line_start'] + 1; $i <= $assignment['semicolon']; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		$next = $assignment['semicolon'] + 1;

		if ( isset( $tokens[ $next ] ) && $tokens[ $next ]['code'] === T_WHITESPACE ) {
			$ws = $tokens[ $next ]['content'];

			if ( strpos( $ws, "\n" ) === 0 ) {
				$fixer->replaceToken( $next, substr( $ws, 1 ) );
			}
		}
	}

	/**
	 * Replace the final return statement with the default value.
	 */
	private function replaceReturnWithDefault( File $phpcsFile, array $returnInfo, $defaultValue ) {
		$fixer = $phpcsFile->fixer;
		$new  = $returnInfo['indent'] . 'return ' . $defaultValue . ';';

		$fixer->replaceToken( $returnInfo['line_start'], $new );

		for ( $i = $returnInfo['line_start'] + 1; $i <= $returnInfo['semicolon']; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
	}

	/**
	 * Find the first token on the line for a given pointer.
	 */
	private function findLineStart( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$lineStart = $stackPtr;

		while ( $lineStart > 0 && $tokens[ $lineStart - 1 ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			--$lineStart;
		}

		return $lineStart;
	}

	/**
	 * Get indentation string for a token.
	 */
	private function getIndentation( File $phpcsFile, $stackPtr ) {
		$tokens    = $phpcsFile->getTokens();
		$lineStart = $this->findLineStart( $phpcsFile, $stackPtr );

		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			return $tokens[ $lineStart ]['content'];
		}

		return '';
	}
}
