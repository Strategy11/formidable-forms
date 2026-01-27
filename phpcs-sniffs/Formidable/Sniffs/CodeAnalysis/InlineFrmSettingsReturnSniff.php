<?php
/**
 * Ensures FrmAppHelper::get_settings() is returned inline when immediately used once.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects patterns like:
 *
 * $settings = FrmAppHelper::get_settings();
 * return $settings->foo;
 *
 * And replaces them with:
 *
 * return FrmAppHelper::get_settings()->foo;
 */
class InlineFrmSettingsReturnSniff implements Sniff {

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		return array( T_EQUAL );
	}

	/**
	 * {@inheritDoc}
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$variablePtr = $this->getAssignedVariablePointer( $phpcsFile, $stackPtr );

		if ( false === $variablePtr ) {
			return;
		}

		$settingsCall = $this->getFrmSettingsCallInfo( $phpcsFile, $stackPtr );

		if ( false === $settingsCall ) {
			return;
		}

		$assignmentEnd = $phpcsFile->findNext( T_SEMICOLON, $settingsCall['close_paren'] + 1 );

		if ( false === $assignmentEnd ) {
			return;
		}

		$returnPtr = $this->findNextEffective( $phpcsFile, $assignmentEnd + 1 );

		if ( false === $returnPtr || $tokens[ $returnPtr ]['code'] !== T_RETURN ) {
			return;
		}

		$returnVarPtr = $this->findNextEffective( $phpcsFile, $returnPtr + 1 );

		if ( false === $returnVarPtr || $tokens[ $returnVarPtr ]['code'] !== T_VARIABLE ) {
			return;
		}

		if ( $tokens[ $returnVarPtr ]['content'] !== $tokens[ $variablePtr ]['content'] ) {
			return;
		}

		$operatorPtr = $this->findNextEffective( $phpcsFile, $returnVarPtr + 1 );

		if ( false === $operatorPtr || ! in_array(
			$tokens[ $operatorPtr ]['code'],
			array( T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ),
			true
		) ) {
			return;
		}

		$returnEnd = $phpcsFile->findNext( T_SEMICOLON, $returnPtr );

		if ( false === $returnEnd ) {
			return;
		}

		if ( $this->variableUsedInRange( $phpcsFile, $tokens[ $returnVarPtr ]['content'], $returnVarPtr + 1, $returnEnd - 1 ) ) {
			return;
		}

		$suffix = $phpcsFile->getTokensAsString( $returnVarPtr + 1, $returnEnd - $returnVarPtr - 1 );
		$lineStart = $this->getLineStartPtr( $tokens, $variablePtr );
		$indent    = $this->getIndentForLine( $tokens, $lineStart );

		$fixable = $phpcsFile->addFixableError(
			'Return FrmAppHelper::get_settings() inline instead of assigning to %s first.',
			$variablePtr,
			'InlineReturn',
			array( $tokens[ $variablePtr ]['content'] )
		);

		if ( false === $fixable ) {
			return;
		}

		$newContent = $indent . 'return FrmAppHelper::get_settings()' . $suffix . ';';

		$phpcsFile->fixer->beginChangeset();

		$phpcsFile->fixer->replaceToken( $lineStart, $newContent );

		for ( $i = $lineStart + 1; $i <= $returnEnd; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Find the pointer to the variable being assigned.
	 *
	 * @param File $phpcsFile File instance.
	 * @param int  $stackPtr  The equals token pointer.
	 *
	 * @return false|int
	 */
	private function getAssignedVariablePointer( File $phpcsFile, $stackPtr ) {
		$tokens   = $phpcsFile->getTokens();
		$variable = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $variable || $tokens[ $variable ]['code'] !== T_VARIABLE ) {
			return false;
		}

		return $variable;
	}

	/**
	 * Check if the assignment is exactly FrmAppHelper::get_settings().
	 *
	 * @param File $phpcsFile File instance.
	 * @param int  $stackPtr  The equals token pointer.
	 *
	 * @return array|false
	 */
	private function getFrmSettingsCallInfo( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$start  = $this->findNextEffective( $phpcsFile, $stackPtr + 1 );

		if ( false === $start ) {
			return false;
		}

		if ( $tokens[ $start ]['code'] === T_NS_SEPARATOR ) {
			$start = $this->findNextEffective( $phpcsFile, $start + 1 );
		}

		if ( false === $start || $tokens[ $start ]['code'] !== T_STRING || 'FrmAppHelper' !== $tokens[ $start ]['content'] ) {
			return false;
		}

		$doubleColon = $this->findNextEffective( $phpcsFile, $start + 1 );

		if ( false === $doubleColon || $tokens[ $doubleColon ]['code'] !== T_DOUBLE_COLON ) {
			return false;
		}

		$method = $this->findNextEffective( $phpcsFile, $doubleColon + 1 );

		if ( false === $method || $tokens[ $method ]['code'] !== T_STRING || 'get_settings' !== $tokens[ $method ]['content'] ) {
			return false;
		}

		$openParen = $this->findNextEffective( $phpcsFile, $method + 1 );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		if ( ! $this->argumentsAreEmpty( $phpcsFile, $openParen, $closeParen ) ) {
			return false;
		}

		return array(
			'open_paren'  => $openParen,
			'close_paren' => $closeParen,
		);
	}

	/**
	 * Check whether there are non-whitespace tokens between parentheses.
	 *
	 * @param File $phpcsFile File reference.
	 * @param int  $openParen Opening parenthesis index.
	 * @param int  $closeParen Closing parenthesis index.
	 *
	 * @return bool
	 */
	private function argumentsAreEmpty( File $phpcsFile, $openParen, $closeParen ) {
		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			$code = $phpcsFile->getTokens()[ $i ]['code'];

			if ( ! in_array( $code, array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Find the first token on the given token's line.
	 *
	 * @param array $tokens   Token stack.
	 * @param int   $stackPtr Pointer within the stack.
	 *
	 * @return int
	 */
	private function getLineStartPtr( array $tokens, $stackPtr ) {
		$line = $tokens[ $stackPtr ]['line'];

		while ( $stackPtr > 0 && $tokens[ $stackPtr - 1 ]['line'] === $line ) {
			--$stackPtr;
		}

		return $stackPtr;
	}

	/**
	 * Retrieve the indentation for the given line start pointer.
	 *
	 * @param array $tokens    Token stack.
	 * @param int   $lineStart Pointer to the first token on the line.
	 *
	 * @return string
	 */
	private function getIndentForLine( array $tokens, $lineStart ) {
		if ( $tokens[ $lineStart ]['code'] === T_WHITESPACE ) {
			return $tokens[ $lineStart ]['content'];
		}

		return '';
	}

	/**
	 * Find the next non-empty token.
	 *
	 * @param File    $phpcsFile File reference.
	 * @param int     $stackPtr  Starting position.
	 * @param integer $end       Optional end pointer.
	 *
	 * @return false|int
	 */
	private function findNextEffective( File $phpcsFile, $stackPtr, $end = null ) {
		$tokens = $phpcsFile->getTokens();
		$skip   = array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT );

		for ( $i = $stackPtr; null === $end ? isset( $tokens[ $i ] ) : $i <= $end; $i++ ) {
			if ( ! isset( $tokens[ $i ] ) ) {
				break;
			}

			if ( ! in_array( $tokens[ $i ]['code'], $skip, true ) ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Check if the variable appears again within a range.
	 *
	 * @param File   $phpcsFile File reference.
	 * @param string $variable  Variable name (including $).
	 * @param int    $start     Start pointer.
	 * @param int    $end       End pointer.
	 *
	 * @return bool
	 */
	private function variableUsedInRange( File $phpcsFile, $variable, $start, $end ) {
		for ( $i = $start; $i <= $end; $i++ ) {
			$token = $phpcsFile->getTokens()[ $i ];

			if ( $token['code'] === T_VARIABLE && $token['content'] === $variable ) {
				return true;
			}
		}

		return false;
	}
}
