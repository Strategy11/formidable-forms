<?php
/**
 * Sniff to detect foreach loops that only copy one field out of each item, which wp_list_pluck() or array_column() already do.
 *
 * Detects patterns like:
 * $names = array();
 * foreach ( $fields as $field ) {
 *     $names[ $field->id ] = $field->name;
 * }
 *
 * These can be simplified to: $names = wp_list_pluck( $fields, 'name', 'id' );
 *
 * The replacement depends on how the loop builds its keys:
 * - $out[ $key ] = $item->field, keeping the loop's own keys, becomes wp_list_pluck( $list, 'field' ).
 * - $out[ $item->id ] = $item->field becomes wp_list_pluck( $list, 'field', 'id' ).
 * - $out[] = $item->field, renumbering from zero, becomes array_column( $list, 'field' ).
 *
 * Detection is deliberately narrow so the rewrite is faithful:
 * - The result variable must be set to an empty array in the statement immediately before the loop,
 *   otherwise the loop may be appending to existing values.
 * - The list must be a plain variable, property or array element. Function and method calls are
 *   skipped because they may return a generator or other Traversable, which both functions reject.
 * - The loop body must be the single assignment, with no comments anywhere in the rewritten range.
 * - The loop's key and value variables must not be read after the loop, since a foreach leaves them set.
 *
 * array_column() reads object properties only when they are public or exposed through both __isset()
 * and __get(), so the renumbering case has its own error code, ArrayColumn, for plugins that want to
 * exclude it.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

declare(strict_types=1);

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Detects and fixes foreach loops that can be replaced with wp_list_pluck() or array_column().
 */
class PreferListPluckSniff implements Sniff {

	/**
	 * Tokens allowed in the list expression being looped over.
	 *
	 * @var array
	 */
	private $sourceTokens = array(
		T_VARIABLE,
		T_OBJECT_OPERATOR,
		T_STRING,
		T_OPEN_SQUARE_BRACKET,
		T_CLOSE_SQUARE_BRACKET,
		T_CONSTANT_ENCAPSED_STRING,
		T_LNUMBER,
		T_DOUBLE_COLON,
		T_SELF,
		T_STATIC,
		T_ARRAY_CAST,
		T_WHITESPACE,
	);

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_FOREACH );
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

		if ( ! isset( $tokens[ $stackPtr ]['parenthesis_opener'], $tokens[ $stackPtr ]['parenthesis_closer'], $tokens[ $stackPtr ]['scope_opener'], $tokens[ $stackPtr ]['scope_closer'] ) ) {
			return;
		}

		$scopeOpener = $tokens[ $stackPtr ]['scope_opener'];
		$scopeCloser = $tokens[ $stackPtr ]['scope_closer'];

		if ( T_OPEN_CURLY_BRACKET !== $tokens[ $scopeOpener ]['code'] ) {
			return;
		}

		$loop = $this->parseLoopHeader( $phpcsFile, $stackPtr );

		if ( false === $loop ) {
			return;
		}

		$body = $this->parseBody( $phpcsFile, $scopeOpener, $scopeCloser, $loop );

		if ( false === $body ) {
			return;
		}

		$initStart = $this->findEmptyArrayInit( $phpcsFile, $stackPtr, $body['target'] );

		if ( false === $initStart ) {
			return;
		}

		if ( in_array( $body['target'], $loop['variables'], true ) ) {
			return;
		}

		foreach ( array( $loop['value'], $loop['key'] ) as $loopVariable ) {
			if ( null !== $loopVariable && $this->isReadAfterLoop( $phpcsFile, $stackPtr, $loopVariable ) ) {
				return;
			}
		}

		if ( 'array_column' === $body['function'] ) {
			$call      = 'array_column( ' . $loop['source'] . ', ' . $body['field'] . ' )';
			$errorCode = 'ArrayColumn';
		} else {
			$call      = 'wp_list_pluck( ' . $loop['source'] . ', ' . $body['field'] . ( null === $body['index'] ? '' : ', ' . $body['index'] ) . ' )';
			$errorCode = 'WpListPluck';
		}

		$fix = $phpcsFile->addFixableError(
			'Use %s instead of a foreach that copies one field from each item.',
			$stackPtr,
			$errorCode,
			array( $call )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			// Keep the assignment's own spacing so it stays aligned with its neighbours.
			$phpcsFile->fixer->replaceToken( $initStart, $call . ';' );

			for ( $i = $initStart + 1; $i <= $scopeCloser; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Reads the list expression and loop variables from the foreach parentheses.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the foreach token.
	 *
	 * @return array|false Array with source, variables, key and value, or false when the header is not a plain loop.
	 */
	private function parseLoopHeader( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$open   = $tokens[ $stackPtr ]['parenthesis_opener'];
		$close  = $tokens[ $stackPtr ]['parenthesis_closer'];
		$as     = $phpcsFile->findNext( T_AS, $open + 1, $close );

		if ( false === $as ) {
			return false;
		}

		$sourceVariables = array();

		for ( $i = $open + 1; $i < $as; $i++ ) {
			if ( ! in_array( $tokens[ $i ]['code'], $this->sourceTokens, true ) ) {
				return false;
			}

			if ( T_VARIABLE === $tokens[ $i ]['code'] ) {
				$sourceVariables[] = $tokens[ $i ]['content'];
			}
		}

		if ( array() === $sourceVariables ) {
			return false;
		}

		$parts = $this->meaningfulTokens( $phpcsFile, $as + 1, $close );

		if ( 1 === count( $parts ) && T_VARIABLE === $tokens[ $parts[0] ]['code'] ) {
			$key   = null;
			$value = $tokens[ $parts[0] ]['content'];
		} elseif ( 3 === count( $parts ) && T_VARIABLE === $tokens[ $parts[0] ]['code'] && T_DOUBLE_ARROW === $tokens[ $parts[1] ]['code'] && T_VARIABLE === $tokens[ $parts[2] ]['code'] ) {
			$key   = $tokens[ $parts[0] ]['content'];
			$value = $tokens[ $parts[2] ]['content'];
		} else {
			return false;
		}

		$source = trim( $phpcsFile->getTokensAsString( $open + 1, $as - $open - 1 ) );

		if ( in_array( $value, $sourceVariables, true ) || in_array( $key, $sourceVariables, true ) ) {
			return false;
		}

		return array(
			'source'    => $source,
			'variables' => $sourceVariables,
			'key'       => $key,
			'value'     => $value,
		);
	}

	/**
	 * Matches a loop body made of a single $target[ index ] = $value->field; statement.
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param int   $scopeOpener The loop's opening brace.
	 * @param int   $scopeCloser The loop's closing brace.
	 * @param array $loop        The parsed loop header.
	 *
	 * @return array|false Array with target, function, field and index, or false when the body does not match.
	 */
	private function parseBody( File $phpcsFile, $scopeOpener, $scopeCloser, $loop ) {
		$tokens = $phpcsFile->getTokens();
		$parts  = $this->meaningfulTokens( $phpcsFile, $scopeOpener + 1, $scopeCloser );
		$count  = count( $parts );

		if ( $count < 7 || T_VARIABLE !== $tokens[ $parts[0] ]['code'] || T_OPEN_SQUARE_BRACKET !== $tokens[ $parts[1] ]['code'] || T_SEMICOLON !== $tokens[ $parts[ $count - 1 ] ]['code'] ) {
			return false;
		}

		$target = $tokens[ $parts[0] ]['content'];

		if ( $target === $loop['value'] || $target === $loop['key'] || ! isset( $tokens[ $parts[1] ]['bracket_closer'] ) ) {
			return false;
		}

		$closeIndex = array_search( $tokens[ $parts[1] ]['bracket_closer'], $parts, true );

		if ( false === $closeIndex || ! isset( $parts[ $closeIndex + 1 ] ) || T_EQUAL !== $tokens[ $parts[ $closeIndex + 1 ] ]['code'] ) {
			return false;
		}

		$field = $this->parseFieldAccess( $phpcsFile, array_slice( $parts, $closeIndex + 2, $count - $closeIndex - 3 ), $loop['value'] );

		if ( false === $field ) {
			return false;
		}

		$indexParts = array_slice( $parts, 2, $closeIndex - 2 );

		if ( array() === $indexParts ) {
			return array(
				'target'   => $target,
				'function' => 'array_column',
				'field'    => $field['literal'],
				'index'    => null,
			);
		}

		if ( 1 === count( $indexParts ) && null !== $loop['key'] && $tokens[ $indexParts[0] ]['content'] === $loop['key'] ) {
			return array(
				'target'   => $target,
				'function' => 'wp_list_pluck',
				'field'    => $field['literal'],
				'index'    => null,
			);
		}

		$index = $this->parseFieldAccess( $phpcsFile, $indexParts, $loop['value'] );

		// wp_list_pluck() ignores a falsy index key, and both accesses must agree on objects versus arrays.
		if ( false === $index || $index['object'] !== $field['object'] || in_array( $index['literal'], array( '0', "'0'", '"0"', "''", '""' ), true ) ) {
			return false;
		}

		return array(
			'target'   => $target,
			'function' => 'wp_list_pluck',
			'field'    => $field['literal'],
			'index'    => $index['literal'],
		);
	}

	/**
	 * Matches $value->name or $value['name'] and returns the field as a literal.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param array  $parts     Meaningful token positions of the expression.
	 * @param string $value     The loop's value variable.
	 *
	 * @return array|false Array with literal and object flag, or false when the expression is not a single field read.
	 */
	private function parseFieldAccess( File $phpcsFile, $parts, $value ) {
		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $parts[0] ) || T_VARIABLE !== $tokens[ $parts[0] ]['code'] || $tokens[ $parts[0] ]['content'] !== $value ) {
			return false;
		}

		if ( 3 === count( $parts ) && T_OBJECT_OPERATOR === $tokens[ $parts[1] ]['code'] && T_STRING === $tokens[ $parts[2] ]['code'] ) {
			return array(
				'literal' => "'" . $tokens[ $parts[2] ]['content'] . "'",
				'object'  => true,
			);
		}

		if ( 4 === count( $parts ) && T_OPEN_SQUARE_BRACKET === $tokens[ $parts[1] ]['code'] && in_array( $tokens[ $parts[2] ]['code'], array( T_CONSTANT_ENCAPSED_STRING, T_LNUMBER ), true ) && T_CLOSE_SQUARE_BRACKET === $tokens[ $parts[3] ]['code'] ) {
			return array(
				'literal' => $tokens[ $parts[2] ]['content'],
				'object'  => false,
			);
		}

		return false;
	}

	/**
	 * Finds a $target = array(); statement directly before the loop.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the foreach token.
	 * @param string $target    The variable the loop fills.
	 *
	 * @return int|false Position of the empty array's opening token, or false when the loop is not preceded by an empty array assignment.
	 */
	private function findEmptyArrayInit( File $phpcsFile, $stackPtr, $target ) {
		$tokens    = $phpcsFile->getTokens();
		$semicolon = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $semicolon || T_SEMICOLON !== $tokens[ $semicolon ]['code'] ) {
			return false;
		}

		$close = $phpcsFile->findPrevious( T_WHITESPACE, $semicolon - 1, null, true );

		if ( false === $close ) {
			return false;
		}

		if ( T_CLOSE_SHORT_ARRAY === $tokens[ $close ]['code'] ) {
			$open = $phpcsFile->findPrevious( T_WHITESPACE, $close - 1, null, true );

			if ( false === $open || T_OPEN_SHORT_ARRAY !== $tokens[ $open ]['code'] ) {
				return false;
			}
		} elseif ( T_CLOSE_PARENTHESIS === $tokens[ $close ]['code'] ) {
			$paren = $phpcsFile->findPrevious( T_WHITESPACE, $close - 1, null, true );
			$open  = false !== $paren ? $phpcsFile->findPrevious( T_WHITESPACE, $paren - 1, null, true ) : false;

			if ( false === $open || T_OPEN_PARENTHESIS !== $tokens[ $paren ]['code'] || T_ARRAY !== $tokens[ $open ]['code'] ) {
				return false;
			}
		} else {
			return false;
		}

		$equal    = $phpcsFile->findPrevious( T_WHITESPACE, $open - 1, null, true );
		$variable = false !== $equal ? $phpcsFile->findPrevious( T_WHITESPACE, $equal - 1, null, true ) : false;

		if ( false === $variable || T_EQUAL !== $tokens[ $equal ]['code'] || T_VARIABLE !== $tokens[ $variable ]['code'] || $tokens[ $variable ]['content'] !== $target ) {
			return false;
		}

		$before = $phpcsFile->findPrevious( T_WHITESPACE, $variable - 1, null, true );

		if ( false === $before || ! in_array( $tokens[ $before ]['code'], array( T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET, T_OPEN_TAG, T_COLON, T_COMMENT, T_DOC_COMMENT_CLOSE_TAG ), true ) ) {
			return false;
		}

		return $open;
	}

	/**
	 * Checks whether a loop variable is read after the loop, before being assigned again.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the foreach token.
	 * @param string $name      The variable name, including the dollar sign.
	 *
	 * @return bool
	 */
	private function isReadAfterLoop( File $phpcsFile, $stackPtr, $name ) {
		$tokens = $phpcsFile->getTokens();
		$end    = $phpcsFile->numTokens;

		foreach ( array_reverse( $tokens[ $stackPtr ]['conditions'], true ) as $conditionPtr => $conditionCode ) {
			if ( in_array( $conditionCode, array( T_FUNCTION, T_CLOSURE ), true ) && isset( $tokens[ $conditionPtr ]['scope_closer'] ) ) {
				$end = $tokens[ $conditionPtr ]['scope_closer'];
				break;
			}
		}

		$next = $phpcsFile->findNext( T_VARIABLE, $tokens[ $stackPtr ]['scope_closer'] + 1, $end, false, $name );

		if ( false === $next ) {
			return false;
		}

		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $next - 1, null, true );

		// Reused as another foreach's key or value.
		if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_AS, T_DOUBLE_ARROW ), true ) && $this->isInForeachHeader( $phpcsFile, $next ) ) {
			return false;
		}

		$after = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );

		// Plainly reassigned before any read.
		return false === $after || T_EQUAL !== $tokens[ $after ]['code'];
	}

	/**
	 * Checks whether a token sits directly inside a foreach's parentheses.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $ptr       The token position.
	 *
	 * @return bool
	 */
	private function isInForeachHeader( File $phpcsFile, $ptr ) {
		$tokens = $phpcsFile->getTokens();

		if ( empty( $tokens[ $ptr ]['nested_parenthesis'] ) ) {
			return false;
		}

		$openers = array_keys( $tokens[ $ptr ]['nested_parenthesis'] );
		$opener  = end( $openers );

		return isset( $tokens[ $opener ]['parenthesis_owner'] ) && T_FOREACH === $tokens[ $tokens[ $opener ]['parenthesis_owner'] ]['code'];
	}

	/**
	 * Returns the non-whitespace token positions in a range, or an empty list if the range holds anything else.
	 *
	 * Comments make the whole range unusable so the fix never deletes one.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     First position, inclusive.
	 * @param int  $end       Last position, exclusive.
	 *
	 * @return array
	 */
	private function meaningfulTokens( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();
		$parts  = array();

		for ( $i = $start; $i < $end; $i++ ) {
			if ( T_WHITESPACE === $tokens[ $i ]['code'] ) {
				continue;
			}

			if ( isset( Tokens::$commentTokens[ $tokens[ $i ]['code'] ] ) ) {
				return array();
			}

			$parts[] = $i;
		}

		return $parts;
	}
}
