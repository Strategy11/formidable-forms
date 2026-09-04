<?php
/**
 * Sniff to detect direct request superglobal reads that should use the Formidable input helpers.
 *
 * FrmAppHelper::get_post_param(), FrmAppHelper::simple_get() and FrmAppHelper::get_param()
 * handle the isset() check, wp_unslash() and sanitizing in one call.
 *
 * Detects patterns like:
 * $title = sanitize_text_field( wp_unslash( $_POST['title'] ) );
 * $page  = $_GET['page'];
 *
 * These become:
 * $title = FrmAppHelper::get_post_param( 'title', '', 'sanitize_text_field' );
 * $page  = FrmAppHelper::simple_get( 'page', 'sanitize_text_field' );
 *
 * By default the sniff only reports reads it can rewrite faithfully, so phpcbf output is always
 * behaviour preserving. That means the read has to be wrapped in a sanitizer already, which the
 * fix then reuses. Three shapes are skipped:
 *
 * - a read with no sanitizer to copy, because the fix would have to invent one. Enable
 *   $includeUnsanitizedReads to report and fix these under the separate Unsanitized* codes.
 * - a read whose line carries a phpcs:ignore for InputNotSanitized or MissingUnslash, where not
 *   sanitizing was a deliberate decision.
 * - a read passed straight to is_array() or similar, where the helper cannot change the answer
 *   and would recursively sanitize the whole payload for nothing.
 *
 * @package Formidable\Sniffs\Security
 */

namespace Formidable\Sniffs\Security;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Detects and fixes subscripted reads of $_POST, $_GET and $_REQUEST.
 */
class PreferInputHelperSniff implements Sniff {

	/**
	 * Superglobals that have a Formidable helper.
	 *
	 * 'code' is the error code to report under. 'call' is a sprintf template for the
	 * replacement, taking the key expression and then the sanitizer.
	 *
	 * @var array<string, array<string, string>>
	 */
	private $superglobals = array(
		'$_POST'    => array(
			'code' => 'PostSuperglobal',
			'call' => 'FrmAppHelper::get_post_param( %1$s, \'\', \'%2$s\' )',
		),
		'$_GET'     => array(
			'code' => 'GetSuperglobal',
			'call' => 'FrmAppHelper::simple_get( %1$s, \'%2$s\' )',
		),
		'$_REQUEST' => array(
			'code' => 'RequestSuperglobal',
			'call' => 'FrmAppHelper::get_param( %1$s, \'\', \'request\', \'%2$s\' )',
		),
	);

	/**
	 * Wrapper calls that the helper performs itself, so the fix can absorb them.
	 *
	 * @var string[]
	 */
	private $unslashers = array(
		'wp_unslash',
		'stripslashes',
		'stripslashes_deep',
	);

	/**
	 * Sanitizers that the fix will copy out of the existing code when it wraps the read.
	 *
	 * @var string[]
	 */
	public $sanitizers = array(
		'absint',
		'esc_url_raw',
		'floatval',
		'intval',
		'sanitize_email',
		'sanitize_file_name',
		'sanitize_html_class',
		'sanitize_key',
		'sanitize_text_field',
		'sanitize_textarea_field',
		'sanitize_title',
		'sanitize_url',
		'wp_kses_post',
	);

	/**
	 * Sanitizer to use when the read is not already wrapped in one.
	 *
	 * Only used when $includeUnsanitizedReads is enabled.
	 *
	 * @var string
	 */
	public $defaultSanitize = 'sanitize_text_field';

	/**
	 * Whether to report reads that have no sanitizer for the fix to copy.
	 *
	 * Off by default. When the read is already wrapped in a sanitizer the fix reuses it and is
	 * faithful by construction. With nothing to copy the fix has to pick $defaultSanitize
	 * instead, which changes behaviour: a payload that has to survive intact, JSON or an email
	 * body or custom CSS, gets flattened by sanitize_text_field. Rather than report something it
	 * cannot fix faithfully, the sniff stays quiet. Turn this on deliberately, then read every
	 * hunk phpcbf produces under the Unsanitized* codes:
	 *
	 * <rule ref="Formidable.Security.PreferInputHelper">
	 *     <properties>
	 *         <property name="includeUnsanitizedReads" value="true" />
	 *     </properties>
	 * </rule>
	 *
	 * @var bool
	 */
	public $includeUnsanitizedReads = false;

	/**
	 * phpcs:ignore codes that mark a read as deliberately left unsanitized.
	 *
	 * Where a developer has silenced the WordPress input sniffs on a line, not sanitizing was a
	 * decision, and swapping in a sanitizing helper would undo it.
	 *
	 * @var string[]
	 */
	private $sanitizeIgnoreMarkers = array(
		'InputNotSanitized',
		'MissingUnslash',
	);

	/**
	 * Functions that only inspect the shape of a value, never use it.
	 *
	 * `is_array( $_POST['item_meta'] )` gets the same answer through the helper, at the cost of
	 * recursively sanitizing the whole payload and discarding it, so these are left alone.
	 *
	 * @var string[]
	 */
	private $typeChecks = array(
		'count',
		'is_array',
		'is_bool',
		'is_float',
		'is_int',
		'is_numeric',
		'is_object',
		'is_scalar',
		'is_string',
		'sizeof',
	);

	/**
	 * Base names of files that are allowed to read the superglobals directly.
	 *
	 * The helpers themselves have to touch the superglobals, so the file that defines them is
	 * skipped. Add-ons can extend this from a ruleset:
	 *
	 * <rule ref="Formidable.Security.PreferInputHelper">
	 *     <properties>
	 *         <property name="excludedFiles" type="array">
	 *             <element value="FrmAppHelper.php" />
	 *             <element value="FrmProAppHelper.php" />
	 *         </property>
	 *     </properties>
	 * </rule>
	 *
	 * @var string[]
	 */
	public $excludedFiles = array(
		'FrmAppHelper.php',
	);

	/**
	 * Whether to leave nested reads such as `$_POST['item_meta'][ $field_id ]` alone.
	 *
	 * The helpers replace a single-key read cleanly. Deep reads into a posted array are a
	 * different shape, so they are skipped by default. Set this to false from a ruleset to
	 * report them too:
	 *
	 * <rule ref="Formidable.Security.PreferInputHelper">
	 *     <properties>
	 *         <property name="ignoreNestedAccess" value="false" />
	 *     </properties>
	 * </rule>
	 *
	 * @var bool
	 */
	public $ignoreNestedAccess = true;

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_VARIABLE );
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
		$tokens      = $phpcsFile->getTokens();
		$superglobal = $tokens[ $stackPtr ]['content'];

		if ( ! isset( $this->superglobals[ $superglobal ] ) ) {
			return;
		}

		if ( $this->isExcludedFile( $phpcsFile ) ) {
			return;
		}

		$subscript = $this->getSubscriptChain( $phpcsFile, $stackPtr );

		// Without a key there is no helper equivalent, so leave whole-array access alone.
		if ( null === $subscript ) {
			return;
		}

		if ( $this->ignoreNestedAccess && $subscript['depth'] > 1 ) {
			return;
		}

		if ( $this->isWrite( $phpcsFile, $stackPtr, $subscript['end'] ) ) {
			return;
		}

		if ( $this->isInsideExistenceCheck( $phpcsFile, $stackPtr ) ) {
			return;
		}

		if ( $this->isTypeCheckArgument( $phpcsFile, $stackPtr, $subscript['end'] ) ) {
			return;
		}

		if ( $this->hasSanitizeIgnore( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$wrapper  = $this->getWrappingCalls( $phpcsFile, $stackPtr, $subscript['end'] );
		$guessing = null === $wrapper['sanitizer'];

		// Nothing to copy means the fix would have to invent a sanitizer, so stay quiet unless
		// that was asked for. Everything this sniff reports, it can fix faithfully.
		if ( $guessing && ! $this->includeUnsanitizedReads ) {
			return;
		}

		$sanitize = $guessing ? $this->defaultSanitize : $wrapper['sanitizer'];
		$keyExpr  = $this->getKeyExpression( $phpcsFile, $subscript['open'] );

		$replacement = sprintf( $this->superglobals[ $superglobal ]['call'], $keyExpr, $sanitize );

		// A read with no sanitizer to copy gets its own code, because the fix picks the
		// sanitizer rather than preserving one. Those hunks need a human to confirm the
		// choice suits the payload.
		$errorCode = $guessing
			? 'Unsanitized' . $this->superglobals[ $superglobal ]['code']
			: $this->superglobals[ $superglobal ]['code'];

		$fix = $phpcsFile->addFixableError(
			'Do not read %s directly. Use %s instead.',
			$stackPtr,
			$errorCode,
			array( $superglobal, $replacement )
		);

		if ( true === $fix ) {
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken( $wrapper['start'], $replacement );

			for ( $i = $wrapper['start'] + 1; $i <= $wrapper['end']; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

	/**
	 * Determine if the file being scanned is allowed to use the superglobals directly.
	 *
	 * @param File $phpcsFile The file being scanned.
	 *
	 * @return bool
	 */
	private function isExcludedFile( File $phpcsFile ) {
		$fileName = $phpcsFile->getFilename();

		if ( 'STDIN' === $fileName ) {
			return false;
		}

		return in_array( basename( $fileName ), (array) $this->excludedFiles, true );
	}

	/**
	 * Measure the subscript chain that follows the superglobal.
	 *
	 * For `$_POST['a']['b']` this returns the first opening bracket, the final closing bracket
	 * and a depth of 2.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The superglobal token.
	 *
	 * @return array|null Array with 'open', 'end' and 'depth' keys, or null when not subscripted.
	 */
	private function getSubscriptChain( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$open   = null;
		$end    = null;
		$depth  = 0;
		$next   = $phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true );

		while ( false !== $next && T_OPEN_SQUARE_BRACKET === $tokens[ $next ]['code'] ) {
			if ( ! isset( $tokens[ $next ]['bracket_closer'] ) ) {
				break;
			}

			if ( null === $open ) {
				$open = $next;
			}

			$depth++;
			$end  = $tokens[ $next ]['bracket_closer'];
			$next = $phpcsFile->findNext( Tokens::$emptyTokens, $end + 1, null, true );
		}

		if ( null === $end ) {
			return null;
		}

		return array(
			'open'  => $open,
			'end'   => $end,
			'depth' => $depth,
		);
	}

	/**
	 * Read the key expression out of the first subscript, source text and all.
	 *
	 * Copying the source verbatim keeps variable keys such as `$_REQUEST[ $param_name ]`
	 * working after the fix.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $openPtr   The first opening square bracket.
	 *
	 * @return string
	 */
	private function getKeyExpression( File $phpcsFile, $openPtr ) {
		$tokens = $phpcsFile->getTokens();
		$closer = $tokens[ $openPtr ]['bracket_closer'];

		return trim( $phpcsFile->getTokensAsString( $openPtr + 1, $closer - $openPtr - 1 ) );
	}

	/**
	 * Walk outwards from the read to absorb unslash and sanitize calls that wrap it.
	 *
	 * `sanitize_text_field( wp_unslash( $_POST['a'] ) )` returns the range covering the whole
	 * expression plus the sanitizer name, so the fix can replace all of it with one helper call.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The superglobal token.
	 * @param int  $subscriptEnd The last token of the subscript chain.
	 *
	 * @return array Array with 'start', 'end' and 'sanitizer' keys.
	 */
	private function getWrappingCalls( File $phpcsFile, $stackPtr, $subscriptEnd ) {
		$result = array(
			'start'     => $stackPtr,
			'end'       => $subscriptEnd,
			'sanitizer' => null,
		);

		// One pass for an unslasher, one for a sanitizer, in either order.
		for ( $pass = 0; $pass < 2; $pass++ ) {
			$call = $this->getEnclosingSingleArgumentCall( $phpcsFile, $result['start'], $result['end'] );

			if ( null === $call ) {
				break;
			}

			$isUnslasher = in_array( $call['name'], $this->unslashers, true );
			$isSanitizer = in_array( $call['name'], (array) $this->sanitizers, true );

			if ( ! $isUnslasher && ! $isSanitizer ) {
				break;
			}

			if ( $isSanitizer ) {
				if ( null !== $result['sanitizer'] ) {
					break;
				}

				$result['sanitizer'] = $call['name'];
			}

			$result['start'] = $call['start'];
			$result['end']   = $call['end'];
		}

		return $result;
	}

	/**
	 * Find a function call that wraps the given range as its only argument.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $startPtr  First token of the wrapped range.
	 * @param int  $endPtr    Last token of the wrapped range.
	 *
	 * @return array|null Array with 'name', 'start' and 'end' keys, or null when not wrapped.
	 */
	private function getEnclosingSingleArgumentCall( File $phpcsFile, $startPtr, $endPtr ) {
		$tokens    = $phpcsFile->getTokens();
		$openParen = $phpcsFile->findPrevious( Tokens::$emptyTokens, $startPtr - 1, null, true );

		if ( false === $openParen || T_OPEN_PARENTHESIS !== $tokens[ $openParen ]['code'] ) {
			return null;
		}

		if ( ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return null;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$afterRange = $phpcsFile->findNext( Tokens::$emptyTokens, $endPtr + 1, null, true );

		// The range has to be the entire argument, so the paren must close right after it.
		if ( $afterRange !== $closeParen ) {
			return null;
		}

		$namePtr = $phpcsFile->findPrevious( Tokens::$emptyTokens, $openParen - 1, null, true );

		if ( false === $namePtr || T_STRING !== $tokens[ $namePtr ]['code'] ) {
			return null;
		}

		// Skip method and static calls, which are not the global functions we absorb.
		$beforeName = $phpcsFile->findPrevious( Tokens::$emptyTokens, $namePtr - 1, null, true );

		if ( false !== $beforeName && in_array( $tokens[ $beforeName ]['code'], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON ), true ) ) {
			return null;
		}

		return array(
			'name'  => strtolower( $tokens[ $namePtr ]['content'] ),
			'start' => $namePtr,
			'end'   => $closeParen,
		);
	}

	/**
	 * Determine if the superglobal is being written to rather than read.
	 *
	 * Writes are left alone because no helper replaces them.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The superglobal token.
	 * @param int  $subscriptEnd The last token of the subscript chain.
	 *
	 * @return bool
	 */
	private function isWrite( File $phpcsFile, $stackPtr, $subscriptEnd ) {
		$tokens = $phpcsFile->getTokens();
		$prev   = $phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true );

		// Assigning by reference, as in `$value = &$_POST['a']`.
		if ( false !== $prev && T_BITWISE_AND === $tokens[ $prev ]['code'] ) {
			return true;
		}

		if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_INC, T_DEC ), true ) ) {
			return true;
		}

		$next = $phpcsFile->findNext( Tokens::$emptyTokens, $subscriptEnd + 1, null, true );

		if ( false === $next ) {
			return false;
		}

		return in_array( $tokens[ $next ]['code'], $this->getAssignmentTokens(), true );
	}

	/**
	 * Determine if the access sits inside a construct that only tests for the key.
	 *
	 * `isset( $_POST['a'] )`, `empty( $_POST['a'] )` and `unset( $_POST['a'] )` are not reads of
	 * the value, and the helpers are not drop-in replacements for them.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The superglobal token.
	 *
	 * @return bool
	 */
	private function isInsideExistenceCheck( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( empty( $tokens[ $stackPtr ]['nested_parenthesis'] ) ) {
			return false;
		}

		$checkTokens = array( T_ISSET, T_EMPTY, T_UNSET );

		foreach ( $tokens[ $stackPtr ]['nested_parenthesis'] as $opener => $closer ) {
			$before = $phpcsFile->findPrevious( Tokens::$emptyTokens, $opener - 1, null, true );

			if ( false === $before ) {
				continue;
			}

			if ( in_array( $tokens[ $before ]['code'], $checkTokens, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the read is the only argument to a function that just inspects its shape.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The superglobal token.
	 * @param int  $subscriptEnd The last token of the subscript chain.
	 *
	 * @return bool
	 */
	private function isTypeCheckArgument( File $phpcsFile, $stackPtr, $subscriptEnd ) {
		$call = $this->getEnclosingSingleArgumentCall( $phpcsFile, $stackPtr, $subscriptEnd );

		if ( null === $call ) {
			return false;
		}

		return in_array( $call['name'], $this->typeChecks, true );
	}

	/**
	 * Determine if a phpcs:ignore nearby marks the read as deliberately unsanitized.
	 *
	 * Looks at the read's own line and the line above it, which is where the annotation sits in
	 * practice, either trailing the statement or on its own line above.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The superglobal token.
	 *
	 * @return bool
	 */
	private function hasSanitizeIgnore( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$line   = $tokens[ $stackPtr ]['line'];

		for ( $ptr = $stackPtr; $ptr >= 0; $ptr-- ) {
			if ( $tokens[ $ptr ]['line'] < $line - 1 ) {
				break;
			}

			if ( $this->isSanitizeIgnoreComment( $tokens[ $ptr ] ) ) {
				return true;
			}
		}

		for ( $ptr = $stackPtr + 1; $ptr < $phpcsFile->numTokens; $ptr++ ) {
			if ( $tokens[ $ptr ]['line'] > $line ) {
				break;
			}

			if ( $this->isSanitizeIgnoreComment( $tokens[ $ptr ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a token is a phpcs:ignore comment naming one of the input sniffs.
	 *
	 * @param array $token The token to inspect.
	 *
	 * @return bool
	 */
	private function isSanitizeIgnoreComment( $token ) {
		$commentCodes = array( T_COMMENT );

		// PHPCS 3.2+ gives its own annotations a dedicated token.
		if ( defined( 'T_PHPCS_IGNORE' ) ) {
			$commentCodes[] = T_PHPCS_IGNORE;
		}

		if ( ! in_array( $token['code'], $commentCodes, true ) ) {
			return false;
		}

		if ( false === strpos( $token['content'], 'phpcs:ignore' ) ) {
			return false;
		}

		foreach ( $this->sanitizeIgnoreMarkers as $marker ) {
			if ( false !== strpos( $token['content'], $marker ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Tokens that mean the expression to their left is being assigned to.
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
			T_POW_EQUAL,
			T_CONCAT_EQUAL,
			T_AND_EQUAL,
			T_OR_EQUAL,
			T_XOR_EQUAL,
			T_SL_EQUAL,
			T_SR_EQUAL,
			T_COALESCE_EQUAL,
			T_INC,
			T_DEC,
		);
	}
}
