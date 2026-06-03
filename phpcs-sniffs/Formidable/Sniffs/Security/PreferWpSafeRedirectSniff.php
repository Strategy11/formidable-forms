<?php
/**
 * Sniff to convert wp_redirect() calls that point to local URLs into wp_safe_redirect().
 *
 * @package Formidable\Sniffs\Security
 */

namespace Formidable\Sniffs\Security;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags wp_redirect() when the destination is built from clearly local helpers.
 */
class PreferWpSafeRedirectSniff implements Sniff {

	/**
	 * Functions that always return a site-local URL.
	 *
	 * @var string[]
	 */
	private $safeFunctions = array(
		'home_url',
		'site_url',
		'admin_url',
	);

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		return array( T_STRING );
	}

	/**
	 * Scan the argument tokens for variables that originate from local helper calls.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  First token of the argument.
	 * @param int  $endPtr    Last token of the argument.
	 * @param int  $stackPtr  The wp_redirect token pointer.
	 *
	 * @return string|null
	 */
	private function findSafeVariableInArgument( File $phpcsFile, $startPtr, $endPtr, $stackPtr ) {
		$tokens  = $phpcsFile->getTokens();
		$checked = array();

		for ( $ptr = $startPtr; $ptr <= $endPtr && $ptr < $phpcsFile->numTokens; $ptr++ ) {
			if ( T_VARIABLE !== $tokens[ $ptr ]['code'] ) {
				continue;
			}

			$varName = $tokens[ $ptr ]['content'];

			if ( isset( $checked[ $varName ] ) ) {
				continue;
			}

			$checked[ $varName ] = true;
			$safeSource          = $this->findSafeVariableSource( $phpcsFile, $ptr, $stackPtr );

			if ( null !== $safeSource ) {
				return $safeSource;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( 'wp_redirect' !== strtolower( $tokens[ $stackPtr ]['content'] ) ) {
			return;
		}

		// Skip method calls like $this->wp_redirect().
		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON ), true ) ) {
			return;
		}

		$openParen = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] ) {
			return;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$firstArg = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $tokens[ $openParen ]['parenthesis_closer'], true );

		if ( false === $firstArg ) {
			return;
		}

		$argumentEnd = $this->getFirstArgumentEnd( $phpcsFile, $openParen );

		if ( null === $argumentEnd ) {
			return;
		}

		$safeSource = $this->findLocalFunctionInRange( $phpcsFile, $firstArg, $argumentEnd );

		if ( null === $safeSource ) {
			$safeSource = $this->findSafeVariableInArgument( $phpcsFile, $firstArg, $argumentEnd, $stackPtr );
		}

		if ( null === $safeSource ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use wp_safe_redirect() when redirecting to %s().',
			$stackPtr,
			'UseSafeRedirect',
			array( $safeSource )
		);

		if ( $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $stackPtr, 'wp_safe_redirect' );
			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Find the closing boundary for the first argument.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $openParen The opening parentheses token for wp_redirect().
	 *
	 * @return int|null
	 */
	private function getFirstArgumentEnd( File $phpcsFile, $openParen ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return null;
		}

		$close = $tokens[ $openParen ]['parenthesis_closer'];
		$depth = 0;

		for ( $ptr = $openParen + 1; $ptr < $close; $ptr++ ) {
			$code = $tokens[ $ptr ]['code'];

			if ( T_OPEN_PARENTHESIS === $code ) {
				$depth++;
				continue;
			}

			if ( T_CLOSE_PARENTHESIS === $code ) {
				if ( $depth > 0 ) {
					$depth--;
				}
				continue;
			}

			if ( T_COMMA === $code && 0 === $depth ) {
				return $ptr - 1;
			}
		}

		return $close - 1;
	}

	/**
	 * Determine if a safe helper is used within a range of tokens.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  The first token within the first argument.
	 * @param int  $endPtr    The last token belonging to the first argument.
	 *
	 * @return string|null The helper name if found, otherwise null.
	 */
	private function findLocalFunctionInRange( File $phpcsFile, $startPtr, $endPtr ) {
		$tokens = $phpcsFile->getTokens();

		for ( $ptr = $startPtr; $ptr <= $endPtr && $ptr < $phpcsFile->numTokens; $ptr++ ) {
			if ( T_STRING !== $tokens[ $ptr ]['code'] ) {
				continue;
			}

			$functionName = strtolower( $tokens[ $ptr ]['content'] );

			if ( in_array( $functionName, $this->safeFunctions, true ) ) {
				$prev = $phpcsFile->findPrevious( T_WHITESPACE, $ptr - 1, $startPtr - 1, true );

				if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON ), true ) ) {
					continue;
				}

				$next = $phpcsFile->findNext( T_WHITESPACE, $ptr + 1, $endPtr + 1, true );

				if ( false === $next || T_OPEN_PARENTHESIS !== $tokens[ $next ]['code'] ) {
					continue;
				}

				return $tokens[ $ptr ]['content'];
			}

			if ( 'add_query_arg' !== $functionName ) {
				continue;
			}

			if ( $this->isSafeAddQueryArgCall( $phpcsFile, $ptr, $endPtr ) ) {
				return $tokens[ $ptr ]['content'];
			}
		}

		return null;
	}

	/**
	 * Determine if this add_query_arg() call is guaranteed to target the current site.
	 *
	 * The call is considered safe when:
	 * - It is a direct function call (not a method/static call).
	 * - It has exactly two parameters (no explicit base URL argument).
	 * - The first parameter is a string literal (query var name).
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $functionPtr Position of the add_query_arg token.
	 * @param int  $argumentEnd Last token belonging to wp_redirect's first argument.
	 *
	 * @return bool
	 */
	private function isSafeAddQueryArgCall( File $phpcsFile, $functionPtr, $argumentEnd ) {
		$tokens = $phpcsFile->getTokens();

		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $functionPtr - 1, null, true );

		if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON ), true ) ) {
			return false;
		}

		$openParen = $phpcsFile->findNext( T_WHITESPACE, $functionPtr + 1, $argumentEnd + 1, true );

		if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] ) {
			return false;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return false;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		if ( $closeParen > $argumentEnd + 1 ) {
			return false;
		}

		$firstArg = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, $closeParen, true );

		if ( false === $firstArg || T_CONSTANT_ENCAPSED_STRING !== $tokens[ $firstArg ]['code'] ) {
			return false;
		}

		$argumentCount = $this->countArgumentsInCall( $phpcsFile, $openParen );

		return 2 === $argumentCount;
	}

	/**
	 * Count the number of arguments inside a function call.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $openParenPt Token pointer to the opening parenthesis.
	 *
	 * @return int
	 */
	private function countArgumentsInCall( File $phpcsFile, $openParenPt ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $openParenPt ]['parenthesis_closer'] ) ) {
			return 0;
		}

		$closeParen = $tokens[ $openParenPt ]['parenthesis_closer'];
		$firstArg   = $phpcsFile->findNext( T_WHITESPACE, $openParenPt + 1, $closeParen, true );

		if ( false === $firstArg ) {
			return 0;
		}

		$count      = 1;
		$depth      = 0;
		$openTokens = $this->getOpenBracketTokens();
		$closeTokens = $this->getCloseBracketTokens();

		for ( $ptr = $openParenPt + 1; $ptr < $closeParen; $ptr++ ) {
			$code = $tokens[ $ptr ]['code'];

			if ( in_array( $code, $openTokens, true ) ) {
				$depth++;
				continue;
			}

			if ( in_array( $code, $closeTokens, true ) ) {
				if ( $depth > 0 ) {
					$depth--;
				}
				continue;
			}

			if ( T_COMMA === $code && 0 === $depth ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Attempt to trace a variable argument back to a local helper assignment.
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $variablePtr Token pointer for the variable used in wp_redirect().
	 * @param int  $stackPtr    Token pointer for the wp_redirect() call.
	 *
	 * @return string|null
	 */
	private function findSafeVariableSource( File $phpcsFile, $variablePtr, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( T_VARIABLE !== $tokens[ $variablePtr ]['code'] ) {
			return null;
		}

		$scope = $this->getEnclosingFunctionScope( $phpcsFile, $stackPtr );

		if ( null === $scope ) {
			return null;
		}

		$varName   = $tokens[ $variablePtr ]['content'];
		$searchPos = $variablePtr - 1;

		while ( $searchPos > $scope['opener'] ) {
			$prevVar = $phpcsFile->findPrevious( T_VARIABLE, $searchPos, $scope['opener'] );

			if ( false === $prevVar ) {
				break;
			}

			if ( $tokens[ $prevVar ]['content'] !== $varName ) {
				$searchPos = $prevVar - 1;
				continue;
			}

			$assignment = $this->findAssignmentRange( $phpcsFile, $prevVar );

			if ( null === $assignment || $assignment['statement_end'] >= $variablePtr ) {
				$searchPos = $prevVar - 1;
				continue;
			}

			$safeSource = $this->findLocalFunctionInRange( $phpcsFile, $assignment['start'], $assignment['end'] );

			if ( null === $safeSource ) {
				$searchPos = $prevVar - 1;
				continue;
			}

			if ( $this->variableModifiedBetween( $phpcsFile, $assignment['statement_end'], $variablePtr, $varName, $scope['closer'] ) ) {
				$searchPos = $prevVar - 1;
				continue;
			}

			return $safeSource;
		}

		return null;
	}

	/**
	 * Locate the enclosing function or closure scope for a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Current token pointer.
	 *
	 * @return array|null Array with 'opener' and 'closer' keys.
	 */
	private function getEnclosingFunctionScope( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( empty( $tokens[ $stackPtr ]['conditions'] ) ) {
			return null;
		}

		$conditions = array_keys( $tokens[ $stackPtr ]['conditions'] );
		$conditions = array_reverse( $conditions );

		foreach ( $conditions as $conditionPtr ) {
			$code = $tokens[ $conditionPtr ]['code'];

			if ( T_FUNCTION !== $code && T_CLOSURE !== $code ) {
				continue;
			}

			if ( ! isset( $tokens[ $conditionPtr ]['scope_opener'], $tokens[ $conditionPtr ]['scope_closer'] ) ) {
				continue;
			}

			return array(
				'opener' => $tokens[ $conditionPtr ]['scope_opener'],
				'closer' => $tokens[ $conditionPtr ]['scope_closer'],
			);
		}

		return null;
	}

	/**
	 * Find the assignment expression range for a given variable token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $variablePtr Token pointer for the variable.
	 *
	 * @return array|null Array with 'start', 'end', and 'statement_end' pointers.
	 */
	private function findAssignmentRange( File $phpcsFile, $variablePtr ) {
		$tokens = $phpcsFile->getTokens();

		$next = $phpcsFile->findNext( T_WHITESPACE, $variablePtr + 1, null, true );

		if ( false === $next || T_EQUAL !== $tokens[ $next ]['code'] ) {
			return null;
		}

		$exprStart = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );

		if ( false === $exprStart ) {
			return null;
		}

		$statementEnd = $phpcsFile->findNext( T_SEMICOLON, $next + 1 );

		if ( false === $statementEnd ) {
			return null;
		}

		return array(
			'start'         => $exprStart,
			'end'           => $statementEnd - 1,
			'statement_end' => $statementEnd,
		);
	}

	/**
	 * Check if a variable is modified between two pointers.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $startPtr  Starting pointer (typically end of assignment).
	 * @param int    $endPtr    Ending pointer (the wp_redirect argument).
	 * @param string $varName   Variable name (including $).
	 * @param int    $scopeEnd  Scope closer to avoid scanning past the function.
	 *
	 * @return bool
	 */
	private function variableModifiedBetween( File $phpcsFile, $startPtr, $endPtr, $varName, $scopeEnd ) {
		$tokens            = $phpcsFile->getTokens();
		$assignmentTokens  = $this->getAssignmentTokens();
		$searchLimit       = min( $endPtr, $scopeEnd );
		$current           = $startPtr + 1;

		while ( $current < $searchLimit ) {
			$current = $phpcsFile->findNext( T_VARIABLE, $current, $searchLimit );

			if ( false === $current ) {
				break;
			}

			if ( $tokens[ $current ]['content'] !== $varName ) {
				$current++;
				continue;
			}

			$prev = $phpcsFile->findPrevious( T_WHITESPACE, $current - 1, null, true );

			if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_INC, T_DEC ), true ) ) {
				return true;
			}

			$next = $phpcsFile->findNext( T_WHITESPACE, $current + 1, null, true );

			if ( false !== $next && in_array( $tokens[ $next ]['code'], $assignmentTokens, true ) ) {
				return true;
			}

			$current++;
		}

		return false;
	}

	/**
	 * Tokens that constitute modifying assignments for variables.
	 *
	 * @return array
	 */
	private function getAssignmentTokens() {
		return array(
			T_EQUAL,
			T_PLUS_EQUAL,
			T_MINUS_EQUAL,
			T_MUL_EQUAL,
			T_DIV_EQUAL,
			T_MOD_EQUAL,
			T_CONCAT_EQUAL,
			T_AND_EQUAL,
			T_OR_EQUAL,
			T_XOR_EQUAL,
			T_SL_EQUAL,
			T_SR_EQUAL,
			T_INC,
			T_DEC,
		);
	}

	/**
	 * Tokens that open nested structures.
	 *
	 * @return array
	 */
	private function getOpenBracketTokens() {
		$tokens = array(
			T_OPEN_PARENTHESIS,
			T_OPEN_SQUARE_BRACKET,
		);

		if ( defined( 'T_OPEN_SHORT_ARRAY' ) ) {
			$tokens[] = T_OPEN_SHORT_ARRAY;
		}

		return $tokens;
	}

	/**
	 * Tokens that close nested structures.
	 *
	 * @return array
	 */
	private function getCloseBracketTokens() {
		$tokens = array(
			T_CLOSE_PARENTHESIS,
			T_CLOSE_SQUARE_BRACKET,
		);

		if ( defined( 'T_CLOSE_SHORT_ARRAY' ) ) {
			$tokens[] = T_CLOSE_SHORT_ARRAY;
		}

		return $tokens;
	}
}
