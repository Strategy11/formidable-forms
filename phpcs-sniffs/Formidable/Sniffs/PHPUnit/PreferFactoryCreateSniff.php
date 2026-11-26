<?php
/**
 * Sniff to detect test factory create_and_get() calls whose return value is discarded.
 *
 * Detects patterns like:
 * $this->factory->field->create_and_get( array( 'form_id' => $form_id ) );
 *
 * These can be simplified to: $this->factory->field->create( array( 'form_id' => $form_id ) );
 *
 * WP_UnitTest_Factory_For_Thing::create_and_get() is create() followed by get_object_by_id(),
 * so when the result is thrown away the extra read is wasted work. Only bare statements that
 * start with a factory chain are reported, so any use of the value (assignment, return,
 * argument, array element, arrow function body) is left alone.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\PHPUnit;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Detects and fixes unused factory create_and_get() calls by switching them to create().
 */
class PreferFactoryCreateSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING );
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'create_and_get' ) {
			return;
		}

		$prev = $phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true );

		if ( false === $prev || $tokens[ $prev ]['code'] !== T_OBJECT_OPERATOR ) {
			return;
		}

		$openParen = $phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS || ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		// The call must end the statement, so nothing chains off or consumes the result.
		$afterClose = $phpcsFile->findNext( Tokens::$emptyTokens, $tokens[ $openParen ]['parenthesis_closer'] + 1, null, true );

		if ( false === $afterClose || ( $tokens[ $afterClose ]['code'] !== T_SEMICOLON && $tokens[ $afterClose ]['code'] !== T_CLOSE_TAG ) ) {
			return;
		}

		$chainStart = $this->findChainStart( $phpcsFile, $prev );

		if ( false === $chainStart ) {
			return;
		}

		if ( ! $this->isStatementStart( $phpcsFile, $chainStart ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use create() instead of create_and_get() when the return value is not used.',
			$stackPtr,
			'Found'
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $stackPtr, 'create' );
			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Matches the receiver chain before ->create_and_get() against the factory shapes.
	 *
	 * Accepts $this->factory->thing, $this->factory()->thing, self::factory()->thing
	 * and static::factory()->thing.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $operator  The object operator before create_and_get.
	 *
	 * @return false|int The first token of the chain, or false when it is not a factory chain.
	 */
	private function findChainStart( File $phpcsFile, $operator ) {
		$tokens = $phpcsFile->getTokens();

		// The factory type, e.g. "field".
		$thing = $phpcsFile->findPrevious( Tokens::$emptyTokens, $operator - 1, null, true );

		if ( false === $thing || $tokens[ $thing ]['code'] !== T_STRING ) {
			return false;
		}

		$operator = $phpcsFile->findPrevious( Tokens::$emptyTokens, $thing - 1, null, true );

		if ( false === $operator || $tokens[ $operator ]['code'] !== T_OBJECT_OPERATOR ) {
			return false;
		}

		$factory = $phpcsFile->findPrevious( Tokens::$emptyTokens, $operator - 1, null, true );

		if ( false === $factory ) {
			return false;
		}

		$isCall = false;

		if ( $tokens[ $factory ]['code'] === T_CLOSE_PARENTHESIS ) {
			$open = $phpcsFile->findPrevious( Tokens::$emptyTokens, $factory - 1, null, true );

			if ( false === $open || $tokens[ $open ]['code'] !== T_OPEN_PARENTHESIS ) {
				return false;
			}

			$factory = $phpcsFile->findPrevious( Tokens::$emptyTokens, $open - 1, null, true );
			$isCall  = true;
		}

		if ( false === $factory || $tokens[ $factory ]['code'] !== T_STRING || strtolower( $tokens[ $factory ]['content'] ) !== 'factory' ) {
			return false;
		}

		$separator = $phpcsFile->findPrevious( Tokens::$emptyTokens, $factory - 1, null, true );

		if ( false === $separator ) {
			return false;
		}

		$receiver = $phpcsFile->findPrevious( Tokens::$emptyTokens, $separator - 1, null, true );

		if ( false === $receiver ) {
			return false;
		}

		if ( $tokens[ $separator ]['code'] === T_OBJECT_OPERATOR ) {
			return $tokens[ $receiver ]['code'] === T_VARIABLE && '$this' === $tokens[ $receiver ]['content'] ? $receiver : false;
		}

		if ( $isCall && $tokens[ $separator ]['code'] === T_DOUBLE_COLON && in_array( $tokens[ $receiver ]['code'], array( T_SELF, T_STATIC ), true ) ) {
			return $receiver;
		}

		return false;
	}

	/**
	 * Checks whether a token begins a statement rather than sitting inside an expression.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $ptr       The token to check.
	 *
	 * @return bool
	 */
	private function isStatementStart( File $phpcsFile, $ptr ) {
		$tokens = $phpcsFile->getTokens();
		$before = $phpcsFile->findPrevious( Tokens::$emptyTokens, $ptr - 1, null, true );

		if ( false === $before ) {
			return false;
		}

		$code = $tokens[ $before ]['code'];

		if ( in_array( $code, array( T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET, T_OPEN_TAG ), true ) ) {
			return true;
		}

		// A colon only starts a statement after case/default or an alternative-syntax opener, not in a ternary.
		if ( T_COLON === $code ) {
			return isset( $tokens[ $before ]['scope_condition'] ) || isset( $tokens[ $before ]['parenthesis_owner'] ) || $this->isAlternativeSyntaxColon( $phpcsFile, $before );
		}

		return false;
	}

	/**
	 * Checks whether a colon opens an alternative-syntax control structure or a case.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $colon     The colon token.
	 *
	 * @return bool
	 */
	private function isAlternativeSyntaxColon( File $phpcsFile, $colon ) {
		$tokens = $phpcsFile->getTokens();
		$prev   = $phpcsFile->findPrevious( Tokens::$emptyTokens, $colon - 1, null, true );

		if ( false === $prev ) {
			return false;
		}

		if ( T_ELSE === $tokens[ $prev ]['code'] ) {
			return true;
		}

		if ( T_CLOSE_PARENTHESIS === $tokens[ $prev ]['code'] && isset( $tokens[ $prev ]['parenthesis_owner'] ) ) {
			$owner = $tokens[ $tokens[ $prev ]['parenthesis_owner'] ]['code'];

			return in_array( $owner, array( T_IF, T_ELSEIF, T_FOREACH, T_FOR, T_WHILE, T_SWITCH ), true );
		}

		return false;
	}
}
