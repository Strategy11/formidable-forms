<?php
/**
 * Formidable_Sniffs_Security_PreferIdentifierPlaceholderSniff
 *
 * Detects table names interpolated or concatenated into $wpdb query SQL where the %i identifier placeholder should be used instead.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\Security;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Flags table identifiers built into SQL strings passed to $wpdb methods and prefers the %i placeholder.
 *
 * Bad:
 * $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}frm_items WHERE id = %d", $id ) );
 * $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_items WHERE id = %d', $id ) );
 * $wpdb->query( 'DROP TABLE IF EXISTS ' . $this->table );
 *
 * Good:
 * $wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE id = %d', $wpdb->prefix . 'frm_items', $id ) );
 * $wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $this->table ) );
 *
 * CREATE TABLE statements are exempt because they are built for dbDelta(), which cannot use prepare().
 */
class PreferIdentifierPlaceholderSniff implements Sniff {

	/**
	 * SQL keywords that are directly followed by a table identifier.
	 *
	 * A bare ON is deliberately excluded because join conditions follow it with column
	 * references. Only the CREATE INDEX ... ON form takes a table there.
	 *
	 * @var string
	 */
	const TABLE_KEYWORDS = 'FROM|JOIN|INTO|UPDATE|TABLE|EXISTS|INDEX\s+\S+\s+ON';

	/**
	 * Regex fragment matching one interpolated segment inside a double quoted string.
	 *
	 * @var string
	 */
	const INTERP_SEGMENT = '(?:\{\$[^}]+\}|\$[A-Za-z_]\w*(?:->\w+)*)';

	/**
	 * $wpdb methods that receive raw SQL as their first argument.
	 *
	 * @var array
	 */
	private $queryMethods = array(
		'query',
		'get_var',
		'get_row',
		'get_col',
		'get_results',
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_VARIABLE );
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

		if ( $tokens[ $stackPtr ]['content'] !== '$wpdb' ) {
			return;
		}

		$objectOp = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $objectOp || $tokens[ $objectOp ]['code'] !== T_OBJECT_OPERATOR ) {
			return;
		}

		$methodToken = $phpcsFile->findNext( T_WHITESPACE, $objectOp + 1, null, true );

		if ( false === $methodToken || $tokens[ $methodToken ]['code'] !== T_STRING ) {
			return;
		}

		$methodName = $tokens[ $methodToken ]['content'];
		$isPrepare  = 'prepare' === $methodName;

		if ( ! $isPrepare && ! in_array( $methodName, $this->queryMethods, true ) ) {
			return;
		}

		$openParen = $phpcsFile->findNext( T_WHITESPACE, $methodToken + 1, null, true );

		if ( false === $openParen || $tokens[ $openParen ]['code'] !== T_OPEN_PARENTHESIS || ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		if ( ! $isPrepare && $this->first_arg_is_wpdb_call( $phpcsFile, $openParen ) ) {
			// The inner $wpdb->prepare() call is processed on its own token.
			return;
		}

		$argEnd = $this->find_first_arg_end( $phpcsFile, $openParen, $closeParen );
		$parts  = $this->parse_concat_parts( $phpcsFile, $openParen + 1, $argEnd );

		if ( ! $parts ) {
			return;
		}

		if ( $this->is_create_table_statement( $parts ) ) {
			return;
		}

		$refs = $this->find_table_refs( $parts );

		if ( ! $refs ) {
			return;
		}

		if ( ! $isPrepare ) {
			$phpcsFile->addError(
				'Table name built into SQL passed to $wpdb->%s() without prepare(). Wrap the query in $wpdb->prepare() and use the %%i placeholder for the identifier.',
				$refs[0]['ptr'],
				'TableNotPrepared',
				array( $methodName )
			);

			return;
		}

		$this->handle_prepare_refs( $phpcsFile, $parts, $refs, $openParen, $argEnd, $closeParen );
	}

	/**
	 * Checks whether the first argument of a call starts with another $wpdb method call.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $openParen The opening parenthesis of the outer call.
	 *
	 * @return bool
	 */
	private function first_arg_is_wpdb_call( File $phpcsFile, $openParen ) {
		$tokens   = $phpcsFile->getTokens();
		$firstArg = $phpcsFile->findNext( T_WHITESPACE, $openParen + 1, null, true );

		return false !== $firstArg && $tokens[ $firstArg ]['code'] === T_VARIABLE && $tokens[ $firstArg ]['content'] === '$wpdb';
	}

	/**
	 * Finds the token position that ends the first argument (the first top level comma, or the closing parenthesis).
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $openParen  The opening parenthesis of the call.
	 * @param int  $closeParen The closing parenthesis of the call.
	 *
	 * @return int
	 */
	private function find_first_arg_end( File $phpcsFile, $openParen, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $openParen + 1; $i < $closeParen; $i++ ) {
			$i = $this->skip_nested( $tokens, $i );

			if ( $i >= $closeParen ) {
				break;
			}

			if ( $tokens[ $i ]['code'] === T_COMMA ) {
				return $i;
			}
		}

		return $closeParen;
	}

	/**
	 * Skips over nested parentheses and bracket structures.
	 *
	 * @param array $tokens The token stack.
	 * @param int   $i      The current token position.
	 *
	 * @return int The position to continue from (the closer when a nested structure starts at $i).
	 */
	private function skip_nested( $tokens, $i ) {
		if ( $tokens[ $i ]['code'] === T_OPEN_PARENTHESIS && isset( $tokens[ $i ]['parenthesis_closer'] ) ) {
			return $tokens[ $i ]['parenthesis_closer'];
		}

		if ( isset( $tokens[ $i ]['bracket_closer'] ) && in_array( $tokens[ $i ]['code'], array( T_OPEN_SQUARE_BRACKET, T_OPEN_SHORT_ARRAY, T_OPEN_CURLY_BRACKET ), true ) ) {
			return $tokens[ $i ]['bracket_closer'];
		}

		if ( $tokens[ $i ]['code'] === T_ARRAY && isset( $tokens[ $i ]['parenthesis_closer'] ) ) {
			return $tokens[ $i ]['parenthesis_closer'];
		}

		return $i;
	}

	/**
	 * Parses an expression token range into concatenation parts.
	 *
	 * Each part is array( 'type' => 'sq'|'dq'|'expr', 'start' => int, 'end' => int, 'content' => string ).
	 * For string parts, content is the inner string without the surrounding quotes.
	 * For expr parts, content is the raw PHP code.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     The first token of the expression.
	 * @param int  $end       The token after the last token of the expression.
	 *
	 * @return array|false
	 */
	private function parse_concat_parts( File $phpcsFile, $start, $end ) {
		$tokens = $phpcsFile->getTokens();
		$parts  = array();
		$run    = array();

		for ( $i = $start; $i < $end; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( T_WHITESPACE === $code || T_COMMENT === $code ) {
				continue;
			}

			if ( T_STRING_CONCAT === $code ) {
				$part = $this->close_part( $phpcsFile, $run );

				if ( ! $part ) {
					return false;
				}

				$parts[] = $part;
				$run     = array();
				continue;
			}

			$next = $this->skip_nested( $tokens, $i );

			for ( $j = $i; $j <= $next; $j++ ) {
				$run[] = $j;
			}

			$i = $next;
		}

		$part = $this->close_part( $phpcsFile, $run );

		if ( ! $part ) {
			return false;
		}

		$parts[] = $part;

		return $parts;
	}

	/**
	 * Converts a token run into a single concatenation part.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $run       Token positions collected for this part.
	 *
	 * @return array|false
	 */
	private function close_part( File $phpcsFile, $run ) {
		if ( ! $run ) {
			return false;
		}

		$tokens = $phpcsFile->getTokens();
		$start  = $run[0];
		$end    = $run[ count( $run ) - 1 ];

		// PHPCS splits multiline strings into one token per line, so a string part
		// is any run made up entirely of tokens of the same string type.
		$isSingleQuoted = true;
		$isDoubleQuoted = true;
		$raw            = '';

		foreach ( $run as $ptr ) {
			if ( $tokens[ $ptr ]['code'] !== T_CONSTANT_ENCAPSED_STRING ) {
				$isSingleQuoted = false;
			}

			if ( $tokens[ $ptr ]['code'] !== T_DOUBLE_QUOTED_STRING ) {
				$isDoubleQuoted = false;
			}

			$raw .= $tokens[ $ptr ]['content'];
		}

		if ( $isSingleQuoted || $isDoubleQuoted ) {
			$type = "'" === $raw[0] ? 'sq' : 'dq';

			return array(
				'type'    => $type,
				'start'   => $start,
				'end'     => $end,
				'content' => substr( $raw, 1, -1 ),
			);
		}

		return array(
			'type'    => 'expr',
			'start'   => $start,
			'end'     => $end,
			'content' => trim( $phpcsFile->getTokensAsString( $start, $end - $start + 1 ) ),
		);
	}

	/**
	 * Checks whether the SQL starts with CREATE TABLE, which is exempt (dbDelta schema DDL).
	 *
	 * @param array $parts The concatenation parts.
	 *
	 * @return bool
	 */
	private function is_create_table_statement( $parts ) {
		if ( 'expr' === $parts[0]['type'] ) {
			return false;
		}

		return (bool) preg_match( '/^\s*CREATE\s+TABLE/i', $parts[0]['content'] );
	}

	/**
	 * Finds table identifier references in the parsed parts.
	 *
	 * Each ref is array(
	 *   'type'      => 'in_string'|'cross_part',
	 *   'ptr'       => int (token to report on),
	 *   'part'      => int (part index for in_string refs, first part index for cross_part refs),
	 *   'offset'    => int (byte offset of the ref inside the string, in_string only),
	 *   'length'    => int (byte length including surrounding backticks, in_string only),
	 *   'text'      => string (the matched identifier text, in_string only),
	 *   'expr'      => string (PHP expression to move into the prepare arguments),
	 *   'parts'     => array (part indexes consumed, cross_part only),
	 *   'lead_word' => string (leading identifier characters absorbed from the following literal, cross_part only),
	 * ).
	 *
	 * @param array $parts The concatenation parts.
	 *
	 * @return array
	 */
	private function find_table_refs( $parts ) {
		$refs  = array();
		$count = count( $parts );

		for ( $p = 0; $p < $count; $p++ ) {
			$part = $parts[ $p ];

			if ( 'dq' === $part['type'] ) {
				$refs = array_merge( $refs, $this->find_in_string_refs( $part, $p ) );
			}

			if ( 'expr' === $part['type'] || $p + 1 >= $count || 'expr' !== $parts[ $p + 1 ]['type'] ) {
				continue;
			}

			if ( ! preg_match( '/\b(?:' . self::TABLE_KEYWORDS . ')\s+`?\s*$/i', $part['content'] ) ) {
				continue;
			}

			$ref = $this->collect_cross_part_ref( $parts, $p + 1 );

			if ( $ref ) {
				$refs[] = $ref;
			}
		}

		return $refs;
	}

	/**
	 * Finds table references fully contained inside a double quoted string part.
	 *
	 * @param array $part The dq part.
	 * @param int   $p    The part index.
	 *
	 * @return array
	 */
	private function find_in_string_refs( $part, $p ) {
		$interp = self::INTERP_SEGMENT;
		$refRun = '(?:[A-Za-z0-9_]+)?(?:' . $interp . '(?:[A-Za-z0-9_]+)?)+';

		if ( ! preg_match_all( '/\b(?:' . self::TABLE_KEYWORDS . ')\s+(`?)(' . $refRun . ')(`?)/i', $part['content'], $matches, PREG_OFFSET_CAPTURE ) ) {
			return array();
		}

		$refs = array();

		foreach ( $matches[2] as $index => $match ) {
			$text        = $match[0];
			$tickBefore  = $matches[1][ $index ][0];
			$tickAfter   = $matches[3][ $index ][0];
			$startOffset = $matches[1][ $index ][1];

			if ( '' !== $tickBefore && '' === $tickAfter ) {
				// Unbalanced backtick, the identifier continues in another part. Not safely fixable.
				$tickAfter = '';
			}

			$refs[] = array(
				'type'   => 'in_string',
				'ptr'    => $part['start'],
				'part'   => $p,
				'offset' => $startOffset,
				'length' => strlen( $tickBefore ) + strlen( $text ) + strlen( $tickAfter ),
				'text'   => $text,
				'expr'   => $this->segments_to_expression( $text ),
			);
		}

		return $refs;
	}

	/**
	 * Collects a table reference that spans concatenation parts, starting at an expr part.
	 *
	 * @param array $parts The concatenation parts.
	 * @param int   $start The index of the first expr part of the reference.
	 *
	 * @return array|false
	 */
	private function collect_cross_part_ref( $parts, $start ) {
		$count     = count( $parts );
		$exprBits  = array();
		$consumed  = array();
		$leadWord  = '';
		$p         = $start;

		while ( $p < $count ) {
			$part = $parts[ $p ];

			if ( 'expr' === $part['type'] ) {
				$exprBits[] = $part['content'];
				$consumed[] = $p;
				$p++;
				continue;
			}

			if ( 'sq' !== $part['type'] ) {
				break;
			}

			if ( ! preg_match( '/^([A-Za-z0-9_]+)/', $part['content'], $match ) ) {
				break;
			}

			if ( $match[1] === $part['content'] && $p + 1 < $count && 'expr' === $parts[ $p + 1 ]['type'] ) {
				// The whole literal is part of the identifier and it continues with another expression.
				$exprBits[] = "'" . $match[1] . "'";
				$consumed[] = $p;
				$p++;
				continue;
			}

			$exprBits[] = "'" . $match[1] . "'";
			$leadWord   = $match[1];
			break;
		}

		if ( ! $exprBits ) {
			return false;
		}

		return array(
			'type'      => 'cross_part',
			'ptr'       => $parts[ $start ]['start'],
			'part'      => $start,
			'parts'     => $consumed,
			'lead_word' => $leadWord,
			'expr'      => implode( ' . ', $exprBits ),
		);
	}

	/**
	 * Converts an interpolated identifier text into an equivalent PHP expression.
	 *
	 * @param string $text The identifier text, e.g. "{$wpdb->prefix}frm_items".
	 *
	 * @return string
	 */
	private function segments_to_expression( $text ) {
		preg_match_all( '/\{\$([^}]+)\}|\$[A-Za-z_]\w*(?:->\w+)*|[A-Za-z0-9_]+/', $text, $matches, PREG_SET_ORDER );

		$bits = array();

		foreach ( $matches as $match ) {
			if ( isset( $match[1] ) && '' !== $match[1] ) {
				$bits[] = '$' . $match[1];
			} elseif ( '$' === $match[0][0] ) {
				$bits[] = $match[0];
			} else {
				$bits[] = "'" . $match[0] . "'";
			}
		}

		return implode( ' . ', $bits );
	}

	/**
	 * Reports and, when safe, fixes table references inside a $wpdb->prepare() call.
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $parts      The concatenation parts of the first argument.
	 * @param array $refs       The detected table references.
	 * @param int   $openParen  The opening parenthesis of the prepare() call.
	 * @param int   $argEnd     The end of the first argument (comma or closing parenthesis).
	 * @param int   $closeParen The closing parenthesis of the prepare() call.
	 *
	 * @return void
	 */
	private function handle_prepare_refs( File $phpcsFile, $parts, $refs, $openParen, $argEnd, $closeParen ) {
		$fixable = $this->refs_are_fixable( $parts, $refs );
		$args    = $fixable ? $this->parse_args( $phpcsFile, $argEnd, $closeParen ) : false;

		if ( $fixable && false !== $args ) {
			$placeholderTotal = $this->count_placeholders_in_literals( $parts );
			$fixable          = count( $args ) === $placeholderTotal;
		}

		if ( ! $fixable || false === $args ) {
			foreach ( $refs as $ref ) {
				$phpcsFile->addError(
					'Table name built into $wpdb->prepare() SQL. Use the %i placeholder and pass the identifier as a prepare() argument.',
					$ref['ptr'],
					'TableInPrepare'
				);
			}

			return;
		}

		$fix = false;

		foreach ( $refs as $ref ) {
			$fix = $phpcsFile->addFixableError(
				'Table name built into $wpdb->prepare() SQL. Use the %i placeholder and pass the identifier as a prepare() argument.',
				$ref['ptr'],
				'TableInPrepare'
			) || $fix;
		}

		if ( ! $fix ) {
			return;
		}

		$this->apply_fix( $phpcsFile, $parts, $refs, $openParen, $argEnd, $closeParen, $args );
	}

	/**
	 * Determines whether the detected references can be fixed automatically.
	 *
	 * Fixing is only safe when the whole first argument is made of literals plus the
	 * reference expressions themselves, so every placeholder can be counted and every
	 * prepare() argument maps positionally.
	 *
	 * @param array $parts The concatenation parts.
	 * @param array $refs  The detected table references.
	 *
	 * @return bool
	 */
	private function refs_are_fixable( $parts, $refs ) {
		$refPartIndexes = array();

		foreach ( $refs as $ref ) {
			if ( 'cross_part' === $ref['type'] ) {
				foreach ( $ref['parts'] as $p ) {
					$refPartIndexes[ $p ] = true;
				}
			}
		}

		foreach ( $parts as $p => $part ) {
			if ( 'expr' === $part['type'] && ! isset( $refPartIndexes[ $p ] ) ) {
				return false;
			}

			if ( 'expr' !== $part['type'] && preg_match( '/%\d+\$/', $part['content'] ) ) {
				// Numbered placeholders change argument mapping. Not safely fixable.
				return false;
			}

			if ( 'dq' === $part['type'] && $this->dq_has_unhandled_interpolation( $parts, $refs, $p ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks whether a double quoted part contains interpolation that is not part of a detected reference.
	 *
	 * Leftover interpolation before a reference makes placeholder counting unreliable.
	 * Leftover interpolation after every reference is harmless for argument mapping only
	 * when it cannot contain placeholders, which cannot be known, so any leftover
	 * interpolation before the last reference blocks fixing. Leftover interpolation
	 * after the last reference in the same string is allowed because the placeholder
	 * positions for every reference are already determined by the literal text before them.
	 *
	 * @param array $parts The concatenation parts.
	 * @param array $refs  The detected table references.
	 * @param int   $p     The part index to inspect.
	 *
	 * @return bool
	 */
	private function dq_has_unhandled_interpolation( $parts, $refs, $p ) {
		$content = $parts[ $p ]['content'];

		if ( ! preg_match_all( '/' . self::INTERP_SEGMENT . '/', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
			return false;
		}

		$lastRefEnd = -1;

		foreach ( $refs as $ref ) {
			if ( 'in_string' === $ref['type'] && $ref['part'] === $p ) {
				$lastRefEnd = max( $lastRefEnd, $ref['offset'] + $ref['length'] );
			}
		}

		foreach ( $matches[0] as $match ) {
			$offset  = $match[1];
			$covered = false;

			foreach ( $refs as $ref ) {
				if ( 'in_string' === $ref['type'] && $ref['part'] === $p && $offset >= $ref['offset'] && $offset < $ref['offset'] + $ref['length'] ) {
					$covered = true;
					break;
				}
			}

			if ( ! $covered && $offset < $lastRefEnd ) {
				return true;
			}

			if ( ! $covered && -1 === $lastRefEnd ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Counts value placeholders in all literal parts.
	 *
	 * @param array $parts The concatenation parts.
	 *
	 * @return int
	 */
	private function count_placeholders_in_literals( $parts ) {
		$count = 0;

		foreach ( $parts as $part ) {
			if ( 'expr' !== $part['type'] ) {
				$count += $this->count_placeholders( $part['content'] );
			}
		}

		return $count;
	}

	/**
	 * Counts value placeholders in a piece of SQL text.
	 *
	 * @param string $text The SQL text.
	 *
	 * @return int
	 */
	private function count_placeholders( $text ) {
		$text = str_replace( '%%', '', $text );

		return preg_match_all( '/%[dfFsi]/', $text );
	}

	/**
	 * Splits the remaining prepare() arguments into token ranges.
	 *
	 * @param File $phpcsFile  The file being scanned.
	 * @param int  $argEnd     The end of the first argument (comma or closing parenthesis).
	 * @param int  $closeParen The closing parenthesis of the call.
	 *
	 * @return array|false Array of array( 'start' => int, 'end' => int ), or false when the arguments cannot be parsed.
	 */
	private function parse_args( File $phpcsFile, $argEnd, $closeParen ) {
		$tokens = $phpcsFile->getTokens();

		if ( $argEnd >= $closeParen ) {
			return array();
		}

		$args     = array();
		$argStart = false;
		$lastReal = false;

		for ( $i = $argEnd + 1; $i < $closeParen; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( T_WHITESPACE === $code || T_COMMENT === $code ) {
				continue;
			}

			$next = $this->skip_nested( $tokens, $i );

			if ( $next !== $i ) {
				if ( false === $argStart ) {
					$argStart = $i;
				}

				$lastReal = $next;
				$i        = $next;
				continue;
			}

			if ( T_COMMA === $code ) {
				if ( false === $argStart ) {
					return false;
				}

				$args[]   = array(
					'start' => $argStart,
					'end'   => $lastReal,
				);
				$argStart = false;
				$lastReal = false;
				continue;
			}

			if ( false === $argStart ) {
				$argStart = $i;
			}

			$lastReal = $i;
		}

		if ( false !== $argStart ) {
			$args[] = array(
				'start' => $argStart,
				'end'   => $lastReal,
			);
		}

		return $args;
	}

	/**
	 * Applies the %i fix: rewrites the SQL argument and inserts identifier expressions into the argument list.
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $parts      The concatenation parts.
	 * @param array $refs       The detected table references.
	 * @param int   $openParen  The opening parenthesis of the call.
	 * @param int   $argEnd     The end of the first argument.
	 * @param int   $closeParen The closing parenthesis of the call.
	 * @param array $args       The existing argument token ranges.
	 *
	 * @return void
	 */
	private function apply_fix( File $phpcsFile, $parts, $refs, $openParen, $argEnd, $closeParen, $args ) {
		$newParts   = $this->build_new_parts( $parts, $refs );
		$insertions = $this->calculate_insertions( $parts, $refs );
		$newSql     = $this->render_parts( $newParts );

		$phpcsFile->fixer->beginChangeset();

		$sqlStart = $parts[0]['start'];
		$sqlEnd   = $parts[ count( $parts ) - 1 ]['end'];

		$phpcsFile->fixer->replaceToken( $sqlStart, $newSql );

		for ( $i = $sqlStart + 1; $i <= $sqlEnd; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Group insertions by argument slot, keeping reference order.
		$bySlot = array();

		foreach ( $insertions as $insertion ) {
			$bySlot[ $insertion['slot'] ][] = $insertion['expr'];
		}

		foreach ( $bySlot as $slot => $exprs ) {
			$code = implode( ', ', $exprs );

			if ( isset( $args[ $slot ] ) ) {
				$phpcsFile->fixer->addContentBefore( $args[ $slot ]['start'], $code . ', ' );
			} elseif ( $args ) {
				$lastArg = $args[ count( $args ) - 1 ];
				$phpcsFile->fixer->addContent( $lastArg['end'], ', ' . $code );
			} else {
				$phpcsFile->fixer->addContent( $sqlEnd, ', ' . $code );
			}
		}

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Builds the substituted part contents with %i in place of each reference.
	 *
	 * @param array $parts The concatenation parts.
	 * @param array $refs  The detected table references.
	 *
	 * @return array Array of array( 'type' => 'sq'|'dq', 'content' => string ), expr parts consumed by refs are dropped.
	 */
	private function build_new_parts( $parts, $refs ) {
		$contents = array();
		$types    = array();
		$dropped  = array();
		$leadTrim = array();

		foreach ( $parts as $p => $part ) {
			$contents[ $p ] = $part['content'];
			$types[ $p ]    = $part['type'];
		}

		foreach ( $refs as $ref ) {
			if ( 'in_string' === $ref['type'] ) {
				continue;
			}

			$firstPart = $ref['parts'][0];
			$prevPart  = $firstPart - 1;

			// Replace the trailing whitespace/backtick after the keyword with a single space and %i.
			$hadBacktick           = (bool) preg_match( '/`\s*$/', $contents[ $prevPart ] );
			$contents[ $prevPart ] = rtrim( $contents[ $prevPart ], " \t\n`" ) . ' %i';

			foreach ( $ref['parts'] as $p ) {
				$dropped[ $p ] = true;
			}

			// Strip the absorbed leading word (and a possible closing backtick) from the following literal.
			$afterPart = $ref['parts'][ count( $ref['parts'] ) - 1 ] + 1;

			if ( isset( $contents[ $afterPart ] ) && ( '' !== $ref['lead_word'] || $hadBacktick ) ) {
				$leadTrim[ $afterPart ] = $ref['lead_word'];
			}
		}

		foreach ( $leadTrim as $p => $word ) {
			if ( '' === $word || 0 === strpos( $contents[ $p ], $word ) ) {
				$contents[ $p ] = ltrim( substr( $contents[ $p ], strlen( $word ) ), '`' );
			}

			// The literal was fully absorbed into the identifier expression.
			if ( '' === $contents[ $p ] ) {
				$dropped[ $p ] = true;
			}
		}

		// In-string substitutions, applied right to left so offsets stay valid.
		$byPart = array();

		foreach ( $refs as $ref ) {
			if ( 'in_string' === $ref['type'] ) {
				$byPart[ $ref['part'] ][] = $ref;
			}
		}

		foreach ( $byPart as $p => $partRefs ) {
			usort(
				$partRefs,
				function ( $a, $b ) {
					return $b['offset'] - $a['offset'];
				}
			);

			foreach ( $partRefs as $ref ) {
				$contents[ $p ] = substr_replace( $contents[ $p ], '%i', $ref['offset'], $ref['length'] );
			}
		}

		$newParts = array();

		foreach ( $contents as $p => $content ) {
			if ( isset( $dropped[ $p ] ) ) {
				continue;
			}

			$type = $types[ $p ];

			if ( 'dq' === $type && ! preg_match( '/[\$\\\\\']/', $content ) ) {
				$type = 'sq';
			}

			$newParts[] = array(
				'type'    => $type,
				'content' => $content,
			);
		}

		return $newParts;
	}

	/**
	 * Calculates where each reference expression must be inserted in the argument list.
	 *
	 * The slot is the number of original value placeholders that appear before the
	 * reference in the SQL, which equals the index of the original argument the
	 * expression must be inserted before.
	 *
	 * @param array $parts The concatenation parts.
	 * @param array $refs  The detected table references.
	 *
	 * @return array Array of array( 'slot' => int, 'expr' => string ) in reference order.
	 */
	private function calculate_insertions( $parts, $refs ) {
		$insertions = array();

		foreach ( $refs as $ref ) {
			$slot = 0;

			if ( 'in_string' === $ref['type'] ) {
				$limitPart   = $ref['part'];
				$limitOffset = $ref['offset'];
			} else {
				$limitPart   = $ref['parts'][0];
				$limitOffset = null;
			}

			foreach ( $parts as $p => $part ) {
				if ( 'expr' === $part['type'] ) {
					continue;
				}

				if ( $p > $limitPart ) {
					break;
				}

				if ( $p === $limitPart ) {
					if ( null !== $limitOffset ) {
						$slot += $this->count_placeholders( substr( $part['content'], 0, $limitOffset ) );
					}

					break;
				}

				$slot += $this->count_placeholders( $part['content'] );
			}

			$insertions[] = array(
				'slot' => $slot,
				'expr' => $ref['expr'],
			);
		}

		return $insertions;
	}

	/**
	 * Renders substituted parts back into a single PHP concatenation expression.
	 *
	 * @param array $newParts The substituted parts.
	 *
	 * @return string
	 */
	private function render_parts( $newParts ) {
		$rendered = array();

		foreach ( $newParts as $part ) {
			if ( 'sq' === $part['type'] ) {
				$code = "'" . $part['content'] . "'";
			} else {
				$code = '"' . $part['content'] . '"';
			}

			$last = count( $rendered ) - 1;

			if ( $last >= 0 && 'sq' === $part['type'] && "'" === substr( $rendered[ $last ], -1 ) && "'" === $rendered[ $last ][0] ) {
				$rendered[ $last ] = substr( $rendered[ $last ], 0, -1 ) . $part['content'] . "'";
				continue;
			}

			$rendered[] = $code;
		}

		return implode( ' . ', $rendered );
	}
}
