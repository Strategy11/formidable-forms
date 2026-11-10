<?php
/**
 * Sniff to detect useless parentheses, wrapping SlevomatCodingStandard.PHP.UselessParentheses
 * so its fixer can no longer change what a ternary condition evaluates to.
 *
 * Slevomat removes the parentheses around a ternary condition whenever the token before them
 * is not a comparison, a boolean operator or `!`. Any other operator is let through, so phpcbf
 * rewrites the arithmetic itself:
 * $r = $a >= 1.05 / ( $l + 0.05 ) ? 1 : 0;  becomes  $r = $a >= 1.05 / $l + 0.05 ? 1 : 0;
 * $r = -( $a + 1 ) ? 1 : 0;                 becomes  $r = -$a + 1 ? 1 : 0;
 * $r = ( $a += 1 ) ? 1 : 0;                 becomes  $r = $a += 1 ? 1 : 0;
 *
 * This sniff hands every parenthesis to the Slevomat sniff except one that sits right before
 * a `?` and is not safe to unwrap. It is safe only when the group is the whole condition, which
 * means the token before it starts an expression (`=`, `return`, `(`, `,` and similar), and the
 * group holds no assignment and no `print`. Everything else Slevomat reports is unchanged.
 *
 * Plugins opt in by replacing the Slevomat rule in their phpcs.xml:
 * <rule ref="Formidable.CodeAnalysis.RemoveUselessParentheses" />
 *
 * If SlevomatCodingStandard is not installed, the sniff does nothing.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Detects and fixes useless parentheses, without the Slevomat fixer's ternary condition bug.
 */
class RemoveUselessParenthesesSniff implements Sniff {

	/**
	 * The Slevomat sniff that does the detection and the fix.
	 *
	 * @var string
	 */
	private $innerClass = 'SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff';

	/**
	 * Passed through to the Slevomat sniff property of the same name.
	 *
	 * <rule ref="Formidable.CodeAnalysis.RemoveUselessParentheses">
	 *     <properties>
	 *         <property name="ignoreComplexTernaryConditions" value="true" />
	 *     </properties>
	 * </rule>
	 *
	 * @var bool
	 */
	public $ignoreComplexTernaryConditions = false;

	/**
	 * The Slevomat sniff instance, false when it is not installed, null until first use.
	 *
	 * @var object|false|null
	 */
	private $inner;

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_OPEN_PARENTHESIS );
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

		if ( ! isset( $tokens[ $stackPtr ]['parenthesis_closer'] ) ) {
			return;
		}

		$inner = $this->getInner();

		if ( false === $inner ) {
			return;
		}

		if ( $this->isUnsafeTernaryCondition( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$inner->ignoreComplexTernaryConditions = (bool) $this->ignoreComplexTernaryConditions;
		$inner->process( $phpcsFile, $stackPtr );
	}

	/**
	 * Load the Slevomat sniff once.
	 *
	 * @return object|false
	 */
	private function getInner() {
		if ( null === $this->inner ) {
			$this->inner = class_exists( $this->innerClass ) ? new $this->innerClass() : false;
		}

		return $this->inner;
	}

	/**
	 * Check if these parentheses come right before a `?` and removing them would change the condition.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $openParen The opening parenthesis.
	 *
	 * @return bool
	 */
	private function isUnsafeTernaryCondition( File $phpcsFile, $openParen ) {
		$tokens     = $phpcsFile->getTokens();
		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$next       = $phpcsFile->findNext( Tokens::$emptyTokens, $closeParen + 1, null, true );

		if ( false === $next || T_INLINE_THEN !== $tokens[ $next ]['code'] ) {
			return false;
		}

		$prev = $phpcsFile->findPrevious( Tokens::$emptyTokens, $openParen - 1, null, true );

		if ( false === $prev || ! isset( $this->getExpressionStartTokens()[ $tokens[ $prev ]['code'] ] ) ) {
			return true;
		}

		$risky = Tokens::$assignmentTokens;

		$risky[ T_PRINT ] = T_PRINT;

		return false !== $phpcsFile->findNext( $risky, $openParen + 1, $closeParen );
	}

	/**
	 * Tokens after which a parenthesised group followed by `?` is the whole ternary condition.
	 *
	 * @return array<int|string, int|string>
	 */
	private function getExpressionStartTokens() {
		$start = Tokens::$assignmentTokens;

		foreach ( array( T_RETURN, T_ECHO, T_OPEN_PARENTHESIS, T_COMMA, T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET, T_DOUBLE_ARROW, T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET, T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO ) as $code ) {
			$start[ $code ] = $code;
		}

		return $start;
	}
}
