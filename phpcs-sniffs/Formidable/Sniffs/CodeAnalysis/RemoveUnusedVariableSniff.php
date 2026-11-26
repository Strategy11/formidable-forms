<?php
/**
 * Sniff to detect local variable assignments whose value is never read anywhere in their scope.
 *
 * Detects patterns like:
 * function save( $id ) {
 *     $label  = __( 'Saved', 'formidable' );
 *     $result = update_thing( $id );
 *     return $id;
 * }
 *
 * These are fixed to:
 * function save( $id ) {
 *     update_thing( $id );
 *     return $id;
 * }
 *
 * Replaces VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable, which reports unused
 * parameters (removing them breaks hook callbacks and overrides) and flags variables that a view
 * prepares for the sub-view it includes. This sniff never reports parameters, and counts a
 * variable as read when it appears in any file the scope includes.
 *
 * Scope is the enclosing function, method or closure body. Outside a function it is the whole
 * file, excluding nested function and class bodies. Order is ignored, because a loop can run an
 * include before the assignment. Any compact(), extract(), get_defined_vars(), eval, variable
 * variable, or include whose path cannot be resolved statically makes the scope report nothing.
 *
 * Include paths resolve from these shapes only. FrmAppHelper::plugin_path() and the other helpers
 * listed in pathHelpers, __DIR__, dirname( __FILE__ ), a quoted literal, and a variable assigned
 * exactly once in the same scope to one of those. Included files are searched recursively, up to
 * five levels, for the variable name.
 *
 * At file scope the includer can read a variable after the include returns, so a file scope
 * variable is kept when any other file in the plugin mentions it and the files that include this
 * one (found by file name) read it, include a sibling that reads it, or cannot be analysed.
 * Limitation: other plugins, for example Pro including a Lite view, are not visible to this sniff.
 *
 * Two error codes, split by what the fix does:
 * - UnusedPureAssignment: the value has no side effects, the whole statement is deleted.
 * - UnusedAssignment: the value is a call, so only "$name = " is deleted and the call is kept.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Detects and removes assignments to local variables that are never read.
 */
class RemoveUnusedVariableSniff implements Sniff {

	/**
	 * Plugin root used to resolve plugin_path() helpers and to find includers.
	 *
	 * Detected by walking up from the scanned file to the first directory with a phpcs.xml,
	 * then to the first with a composer.json. Set it when that guess is wrong.
	 *
	 * <rule ref="Formidable.CodeAnalysis.RemoveUnusedVariable">
	 *     <properties>
	 *         <property name="pluginRoot" value="/path/to/plugin" />
	 *     </properties>
	 * </rule>
	 *
	 * @var string
	 */
	public $pluginRoot = '';

	/**
	 * Extra static plugin_path() helpers, as class name => directory.
	 *
	 * A relative directory is resolved against the plugin root, an absolute one is used as is.
	 * Entries set here are trusted. Use it in an add-on to point FrmAppHelper at Lite.
	 *
	 * <rule ref="Formidable.CodeAnalysis.RemoveUnusedVariable">
	 *     <properties>
	 *         <property name="pathHelpers" type="array">
	 *             <element key="FrmProAppHelper" value="" />
	 *             <element key="FrmAppHelper" value="../formidable" />
	 *         </property>
	 *     </properties>
	 * </rule>
	 *
	 * @var array
	 */
	public $pathHelpers = array();

	/**
	 * Lite's own plugin_path() helpers. Each is used only when its class file exists in the plugin.
	 *
	 * @var array
	 */
	private $defaultPathHelpers = array(
		'FrmAppHelper'           => '',
		'FrmTransLiteAppHelper'  => 'stripe',
		'FrmStrpLiteAppHelper'   => 'stripe',
		'FrmSquareLiteAppHelper' => 'square',
		'FrmPayPalLiteAppHelper' => 'paypal',
	);

	/**
	 * Variables that are never local.
	 *
	 * @var array
	 */
	private $ignoredNames = array(
		'$this'                 => true,
		'$GLOBALS'              => true,
		'$_GET'                 => true,
		'$_POST'                => true,
		'$_REQUEST'             => true,
		'$_COOKIE'              => true,
		'$_SERVER'              => true,
		'$_FILES'               => true,
		'$_ENV'                 => true,
		'$_SESSION'             => true,
		'$http_response_header' => true,
		'$php_errormsg'         => true,
		'$argv'                 => true,
		'$argc'                 => true,
	);

	/**
	 * Calls treated as side effect free when they appear in an assigned value.
	 *
	 * @var array
	 */
	private $pureCalls = array(
		'__'          => true,
		'esc_html__'  => true,
		'esc_attr__'  => true,
		'_x'          => true,
		'esc_html_x'  => true,
		'esc_attr_x'  => true,
		'_n'          => true,
		'_nx'         => true,
		'_n_noop'     => true,
		'_nx_noop'    => true,
	);

	/**
	 * Calls that read or write the whole local symbol table.
	 *
	 * @var array
	 */
	private $scopeCalls = array(
		'compact'          => true,
		'extract'          => true,
		'get_defined_vars' => true,
	);

	/**
	 * How deep included files and includers are followed.
	 *
	 * @var int
	 */
	private $maxDepth = 5;

	/**
	 * Parsed files, keyed by real path.
	 *
	 * @var array
	 */
	private static $fileInfoCache = array();

	/**
	 * PHP files under each plugin root.
	 *
	 * @var array
	 */
	private static $rootFilesCache = array();

	/**
	 * Detected plugin root per directory.
	 *
	 * @var array
	 */
	private static $rootCache = array();

	/**
	 * Returns an array of tokens this sniff wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_OPEN_TAG );
	}

	/**
	 * Processes this sniff when one of its tokens is encountered.
	 *
	 * The whole file is analysed on the first open tag, because a scope has to be read in full
	 * before any assignment in it can be called unused.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return int
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$scopes = $this->collectScopes( $phpcsFile, $tokens );

		foreach ( $scopes as $scopePtr => $scope ) {
			$this->processScope( $phpcsFile, $tokens, $scopePtr, $scope );
		}

		return $phpcsFile->numTokens;
	}

	/**
	 * Walk the file once and gather reads, plain assignments, includes and abort conditions per scope.
	 *
	 * Scope -1 is the file scope. Other keys are T_FUNCTION or T_CLOSURE pointers.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 *
	 * @return array
	 */
	private function collectScopes( File $phpcsFile, array $tokens ) {
		$scopes = array( -1 => $this->emptyScope() );

		for ( $i = 0; $i < $phpcsFile->numTokens; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( ( T_FUNCTION === $code || T_CLOSURE === $code ) && isset( $tokens[ $i ]['scope_opener'] ) ) {
				$scopes[ $i ] = $this->emptyScope();
				$this->addSignatureNames( $phpcsFile, $tokens, $i, $scopes[ $i ] );
			}

			$owner = $this->getOwnerScope( $tokens, $i );

			if ( false === $owner || ! isset( $scopes[ $owner ] ) ) {
				continue;
			}

			if ( T_VARIABLE === $code ) {
				$name = $tokens[ $i ]['content'];
				$scopes[ $owner ]['occurrences'][ $name ][] = $i;

				if ( false !== $this->getPlainAssignmentEnd( $phpcsFile, $tokens, $i, $owner ) ) {
					$scopes[ $owner ]['assignments'][ $name ][] = $i;
				} else {
					$scopes[ $owner ]['reads'][ $name ] = true;
				}
				continue;
			}

			if ( in_array( $code, array( T_DOUBLE_QUOTED_STRING, T_HEREDOC ), true ) ) {
				foreach ( $this->getInterpolatedNames( $tokens[ $i ]['content'] ) as $name ) {
					$scopes[ $owner ]['reads'][ $name ] = true;
				}
				continue;
			}

			if ( in_array( $code, array( T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE ), true ) ) {
				$scopes[ $owner ]['includes'][] = $i;
				continue;
			}

			if ( T_EVAL === $code || T_DOLLAR === $code ) {
				$scopes[ $owner ]['abort'] = true;
				continue;
			}

			if ( T_STRING === $code && isset( $this->scopeCalls[ strtolower( $tokens[ $i ]['content'] ) ] ) && $this->isFunctionCall( $phpcsFile, $tokens, $i ) ) {
				$scopes[ $owner ]['abort'] = true;
			}
		}

		return $scopes;
	}

	/**
	 * Build the empty data for one scope.
	 *
	 * @return array
	 */
	private function emptyScope() {
		return array(
			'reads'       => array(),
			'assignments' => array(),
			'occurrences' => array(),
			'params'      => array(),
			'includes'    => array(),
			'abort'       => false,
		);
	}

	/**
	 * Parameters and closure use variables are written outside the body, so mark them read in the body.
	 *
	 * @param File  $phpcsFile   The file being scanned.
	 * @param array $tokens      The token stack.
	 * @param int   $functionPtr T_FUNCTION or T_CLOSURE pointer.
	 * @param array $scope       Scope data, updated in place.
	 *
	 * @return void
	 */
	private function addSignatureNames( File $phpcsFile, array $tokens, $functionPtr, array &$scope ) {
		$opener = $tokens[ $functionPtr ]['scope_opener'];

		// Everything between the keyword and the body is signature: parameters, use list, return type.
		for ( $i = $functionPtr + 1; $i < $opener; $i++ ) {
			if ( T_VARIABLE === $tokens[ $i ]['code'] ) {
				$scope['reads'][ $tokens[ $i ]['content'] ]  = true;
				$scope['params'][ $tokens[ $i ]['content'] ] = true;
			}
		}
	}

	/**
	 * Find the scope that owns a token.
	 *
	 * @param array $tokens   The token stack.
	 * @param int   $stackPtr Token position.
	 *
	 * @return false|int Function or closure pointer, -1 for file scope, false for class bodies.
	 */
	private function getOwnerScope( array $tokens, $stackPtr ) {
		if ( empty( $tokens[ $stackPtr ]['conditions'] ) ) {
			return -1;
		}

		$classLike = array( T_CLASS, T_ANON_CLASS, T_INTERFACE, T_TRAIT );

		if ( defined( 'T_ENUM' ) ) {
			$classLike[] = T_ENUM;
		}

		foreach ( array_reverse( $tokens[ $stackPtr ]['conditions'], true ) as $conditionPtr => $conditionCode ) {
			if ( T_FUNCTION === $conditionCode || T_CLOSURE === $conditionCode ) {
				return $conditionPtr;
			}

			if ( in_array( $conditionCode, $classLike, true ) ) {
				return false;
			}
		}

		return -1;
	}

	/**
	 * Check whether a variable is the whole left side of a plain "$name = value;" statement.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  T_VARIABLE pointer.
	 * @param int   $owner     Owning scope pointer, -1 for file scope.
	 *
	 * @return false|int The closing semicolon pointer, or false.
	 */
	private function getPlainAssignmentEnd( File $phpcsFile, array $tokens, $stackPtr, $owner ) {
		if ( isset( $this->ignoredNames[ $tokens[ $stackPtr ]['content'] ] ) ) {
			return false;
		}

		$equal = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $equal || T_EQUAL !== $tokens[ $equal ]['code'] ) {
			return false;
		}

		$value = $phpcsFile->findNext( Tokens::$emptyTokens, $equal + 1, null, true );

		// "$a = &$b" binds a reference, a later write through it is a side effect.
		if ( false === $value || T_BITWISE_AND === $tokens[ $value ]['code'] ) {
			return false;
		}

		$prev     = $phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true );
		$starters = array( T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET, T_OPEN_TAG, T_COLON );

		// A brace-less "if ( x ) $a = 1;" ends in a parenthesis or else, so it never qualifies.
		if ( false === $prev || ! in_array( $tokens[ $prev ]['code'], $starters, true ) ) {
			return false;
		}

		// Statements inside for(), call arguments or named arguments sit deeper than the scope body.
		$scopeParens = array();

		if ( -1 !== $owner && isset( $tokens[ $tokens[ $owner ]['scope_opener'] ]['nested_parenthesis'] ) ) {
			$scopeParens = $tokens[ $tokens[ $owner ]['scope_opener'] ]['nested_parenthesis'];
		}

		$ownParens = isset( $tokens[ $stackPtr ]['nested_parenthesis'] ) ? $tokens[ $stackPtr ]['nested_parenthesis'] : array();

		if ( $ownParens !== $scopeParens ) {
			return false;
		}

		$end = $phpcsFile->findEndOfStatement( $stackPtr );

		if ( false === $end || T_SEMICOLON !== $tokens[ $end ]['code'] ) {
			return false;
		}

		return $end;
	}

	/**
	 * Check whether a T_STRING is a plain function call, not a method, declaration or constant.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  T_STRING pointer.
	 *
	 * @return bool
	 */
	private function isFunctionCall( File $phpcsFile, array $tokens, $stackPtr ) {
		$next = $phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true );

		if ( false === $next || T_OPEN_PARENTHESIS !== $tokens[ $next ]['code'] ) {
			return false;
		}

		$prev = $phpcsFile->findPrevious( Tokens::$emptyTokens, $stackPtr - 1, null, true );

		return false === $prev || ! in_array( $tokens[ $prev ]['code'], $this->getNonCallPrefixes(), true );
	}

	/**
	 * Tokens that make a following "name(" something other than a global function call.
	 *
	 * @return array
	 */
	private function getNonCallPrefixes() {
		$prefixes = array( T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW );

		if ( defined( 'T_NULLSAFE_OBJECT_OPERATOR' ) ) {
			$prefixes[] = T_NULLSAFE_OBJECT_OPERATOR;
		}

		return $prefixes;
	}

	/**
	 * Report every unused plain assignment in one scope.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $scopePtr  Scope pointer, -1 for file scope.
	 * @param array $scope     Scope data.
	 *
	 * @return void
	 */
	private function processScope( File $phpcsFile, array $tokens, $scopePtr, array $scope ) {
		if ( $scope['abort'] || ! $scope['assignments'] ) {
			return;
		}

		$candidates = array_diff_key( $scope['assignments'], $scope['reads'] );

		if ( ! $candidates ) {
			return;
		}

		$root          = $this->getPluginRoot( $phpcsFile );
		$includedNames = array();

		foreach ( $scope['includes'] as $includePtr ) {
			$path = $this->resolveIncludeInFile( $phpcsFile, $tokens, $includePtr, $scope, $root );

			if ( null === $path ) {
				return;
			}

			$included = $this->getIncludedNames( $path, $root, 1, array( $path => true ) );

			if ( true === $included ) {
				return;
			}

			$includedNames += $included;
		}

		foreach ( $candidates as $name => $assignmentPtrs ) {
			if ( isset( $includedNames[ substr( $name, 1 ) ] ) ) {
				continue;
			}

			if ( -1 === $scopePtr && $this->isReadOutsideFile( $phpcsFile, $root, substr( $name, 1 ) ) ) {
				continue;
			}

			foreach ( $assignmentPtrs as $assignmentPtr ) {
				$this->reportAssignment( $phpcsFile, $tokens, $assignmentPtr, $scopePtr );
			}
		}
	}

	/**
	 * Report and fix one unused assignment.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  T_VARIABLE pointer.
	 * @param int   $scopePtr  Scope pointer, -1 for file scope.
	 *
	 * @return void
	 */
	private function reportAssignment( File $phpcsFile, array $tokens, $stackPtr, $scopePtr ) {
		$end = $this->getPlainAssignmentEnd( $phpcsFile, $tokens, $stackPtr, $scopePtr );

		if ( false === $end || $this->hasSuppression( $tokens, $stackPtr, $end ) ) {
			return;
		}

		// A comment after the statement may explain it, so leave the line to a human.
		if ( $this->hasTrailingComment( $tokens, $end ) ) {
			return;
		}

		$equal      = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		$valueStart = $phpcsFile->findNext( Tokens::$emptyTokens, $equal + 1, null, true );

		if ( false === $valueStart || $valueStart >= $end ) {
			return;
		}

		if ( $this->isPureValue( $phpcsFile, $tokens, $valueStart, $end ) ) {
			$code = 'UnusedPureAssignment';
		} elseif ( $this->isStandaloneCall( $phpcsFile, $tokens, $valueStart, $end ) ) {
			$code = 'UnusedAssignment';
		} else {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Remove unused variable %s.',
			$stackPtr,
			$code,
			array( $tokens[ $stackPtr ]['content'] )
		);

		if ( true !== $fix ) {
			return;
		}

		$phpcsFile->fixer->beginChangeset();

		if ( 'UnusedAssignment' === $code ) {
			// Keep the call, drop "$name = ".
			for ( $i = $stackPtr; $i < $valueStart; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
		} else {
			list( $first, $last ) = $this->getStatementLineRange( $tokens, $stackPtr, $end );

			for ( $i = $first; $i <= $last; $i++ ) {
				$phpcsFile->fixer->replaceToken( $i, '' );
			}
		}

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Skip a statement whose line, or the line above, carries a phpcs:ignore for this rule or VariableAnalysis.
	 *
	 * @param array $tokens   The token stack.
	 * @param int   $stackPtr Statement start.
	 * @param int   $end      Statement end.
	 *
	 * @return bool
	 */
	private function hasSuppression( array $tokens, $stackPtr, $end ) {
		$firstLine = $tokens[ $stackPtr ]['line'] - 1;
		$lastLine  = $tokens[ $end ]['line'];
		$start     = $stackPtr;

		while ( $start > 0 && $tokens[ $start - 1 ]['line'] >= $firstLine ) {
			--$start;
		}

		for ( $i = $start; isset( $tokens[ $i ] ) && $tokens[ $i ]['line'] <= $lastLine; $i++ ) {
			if ( ! isset( Tokens::$commentTokens[ $tokens[ $i ]['code'] ] ) ) {
				continue;
			}

			$content = $tokens[ $i ]['content'];

			if ( strpos( $content, 'phpcs:ignore' ) === false ) {
				continue;
			}

			if ( strpos( $content, 'VariableAnalysis' ) !== false || strpos( $content, 'RemoveUnusedVariable' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for a comment on the same line after the statement.
	 *
	 * @param array $tokens The token stack.
	 * @param int   $end    Statement end.
	 *
	 * @return bool
	 */
	private function hasTrailingComment( array $tokens, $end ) {
		for ( $i = $end + 1; isset( $tokens[ $i ] ) && $tokens[ $i ]['line'] === $tokens[ $end ]['line']; $i++ ) {
			if ( isset( Tokens::$commentTokens[ $tokens[ $i ]['code'] ] ) ) {
				return true;
			}

			if ( T_WHITESPACE !== $tokens[ $i ]['code'] ) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Check that an assigned value has no side effects.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $start     First value token.
	 * @param int   $end       Closing semicolon.
	 *
	 * @return bool
	 */
	private function isPureValue( File $phpcsFile, array $tokens, $start, $end ) {
		$allowed = $this->getPureTokens();
		$callers = array( T_VARIABLE, T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET, T_CLOSE_CURLY_BRACKET );

		for ( $i = $start; $i < $end; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( isset( Tokens::$emptyTokens[ $code ] ) ) {
				continue;
			}

			if ( ! isset( $allowed[ $code ] ) ) {
				return false;
			}

			if ( T_STRING === $code && $this->isCallName( $phpcsFile, $tokens, $i ) ) {
				$prev = $phpcsFile->findPrevious( Tokens::$emptyTokens, $i - 1, null, true );

				if ( in_array( $tokens[ $prev ]['code'], $this->getNonCallPrefixes(), true ) || ! isset( $this->pureCalls[ strtolower( $tokens[ $i ]['content'] ) ] ) ) {
					return false;
				}
			}

			if ( T_OPEN_PARENTHESIS === $code ) {
				$prev = $phpcsFile->findPrevious( Tokens::$emptyTokens, $i - 1, null, true );

				// "$fn()", "foo()()" and "$a['cb']()" are calls.
				if ( in_array( $tokens[ $prev ]['code'], $callers, true ) ) {
					return false;
				}
			}

			// "{$obj->method()}" inside a string runs the method.
			if ( in_array( $code, array( T_DOUBLE_QUOTED_STRING, T_HEREDOC ), true ) && preg_match( '/\{\$[^}]*\(/', $tokens[ $i ]['content'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check whether a T_STRING is followed by an opening parenthesis.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  T_STRING pointer.
	 *
	 * @return bool
	 */
	private function isCallName( File $phpcsFile, array $tokens, $stackPtr ) {
		$next = $phpcsFile->findNext( Tokens::$emptyTokens, $stackPtr + 1, null, true );

		return false !== $next && T_OPEN_PARENTHESIS === $tokens[ $next ]['code'];
	}

	/**
	 * Tokens allowed in a side effect free value.
	 *
	 * @return array
	 */
	private function getPureTokens() {
		$codes = array(
			T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING, T_START_HEREDOC, T_HEREDOC, T_END_HEREDOC,
			T_START_NOWDOC, T_NOWDOC, T_END_NOWDOC, T_LNUMBER, T_DNUMBER, T_TRUE, T_FALSE, T_NULL,
			T_VARIABLE, T_STRING, T_NS_SEPARATOR, T_SELF, T_STATIC, T_PARENT, T_DOUBLE_COLON, T_OBJECT_OPERATOR,
			T_OPEN_SQUARE_BRACKET, T_CLOSE_SQUARE_BRACKET, T_OPEN_SHORT_ARRAY, T_CLOSE_SHORT_ARRAY, T_ARRAY,
			T_OPEN_PARENTHESIS, T_CLOSE_PARENTHESIS, T_COMMA, T_DOUBLE_ARROW, T_ISSET, T_EMPTY,
			T_PLUS, T_MINUS, T_MULTIPLY, T_DIVIDE, T_MODULUS, T_POW, T_STRING_CONCAT, T_INLINE_THEN, T_INLINE_ELSE,
			T_COALESCE, T_BOOLEAN_AND, T_BOOLEAN_OR, T_BOOLEAN_NOT, T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR,
			T_IS_EQUAL, T_IS_NOT_EQUAL, T_IS_IDENTICAL, T_IS_NOT_IDENTICAL, T_LESS_THAN, T_GREATER_THAN,
			T_IS_SMALLER_OR_EQUAL, T_IS_GREATER_OR_EQUAL, T_SPACESHIP, T_BITWISE_AND, T_BITWISE_OR, T_BITWISE_XOR,
			T_BITWISE_NOT, T_SL, T_SR, T_INT_CAST, T_STRING_CAST, T_ARRAY_CAST, T_BOOL_CAST, T_DOUBLE_CAST,
			T_OBJECT_CAST, T_DIR, T_FILE, T_LINE, T_CLASS_C, T_FUNC_C, T_METHOD_C, T_NS_C, T_TRAIT_C, T_INSTANCEOF,
		);

		if ( defined( 'T_NULLSAFE_OBJECT_OPERATOR' ) ) {
			$codes[] = T_NULLSAFE_OBJECT_OPERATOR;
		}

		return array_flip( $codes );
	}

	/**
	 * Check that a value is a single call, new or include, so "value;" stands alone as a statement.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $start     First value token.
	 * @param int   $end       Closing semicolon.
	 *
	 * @return bool
	 */
	private function isStandaloneCall( File $phpcsFile, array $tokens, $start, $end ) {
		if ( T_ASPERAND === $tokens[ $start ]['code'] ) {
			$start = $phpcsFile->findNext( Tokens::$emptyTokens, $start + 1, $end, true );

			if ( false === $start ) {
				return false;
			}
		}

		$code = $tokens[ $start ]['code'];

		if ( in_array( $code, array( T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE ), true ) ) {
			return true;
		}

		// "$a = $b = foo();" keeps "$b = foo();".
		if ( T_VARIABLE === $code ) {
			$next = $phpcsFile->findNext( Tokens::$emptyTokens, $start + 1, $end, true );

			if ( false !== $next && T_EQUAL === $tokens[ $next ]['code'] ) {
				return true;
			}
		}

		$isNew = T_NEW === $code;

		if ( $isNew ) {
			$start = $phpcsFile->findNext( Tokens::$emptyTokens, $start + 1, $end, true );

			if ( false === $start ) {
				return false;
			}
		}

		$chain = array( T_STRING, T_NS_SEPARATOR, T_VARIABLE, T_SELF, T_STATIC, T_PARENT, T_DOUBLE_COLON, T_OBJECT_OPERATOR );

		if ( defined( 'T_NULLSAFE_OBJECT_OPERATOR' ) ) {
			$chain[] = T_NULLSAFE_OBJECT_OPERATOR;
		}

		$last = false;

		for ( $i = $start; $i < $end; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( isset( Tokens::$emptyTokens[ $code ] ) ) {
				continue;
			}

			$last = $i;

			if ( T_OPEN_PARENTHESIS === $code && isset( $tokens[ $i ]['parenthesis_closer'] ) ) {
				$i    = $tokens[ $i ]['parenthesis_closer'];
				$last = $i;
				continue;
			}

			if ( T_OPEN_SQUARE_BRACKET === $code && isset( $tokens[ $i ]['bracket_closer'] ) ) {
				$i    = $tokens[ $i ]['bracket_closer'];
				$last = $i;
				continue;
			}

			if ( ! in_array( $code, $chain, true ) ) {
				return false;
			}
		}

		if ( false === $last ) {
			return false;
		}

		if ( $isNew ) {
			return true;
		}

		// The chain has to end in a call, "$obj->prop;" or "$a['k'];" alone does nothing.
		if ( T_CLOSE_PARENTHESIS !== $tokens[ $last ]['code'] || ! isset( $tokens[ $last ]['parenthesis_opener'] ) ) {
			return false;
		}

		$beforeOpen = $phpcsFile->findPrevious( Tokens::$emptyTokens, $tokens[ $last ]['parenthesis_opener'] - 1, null, true );

		return false !== $beforeOpen && $beforeOpen >= $start && in_array( $tokens[ $beforeOpen ]['code'], array( T_STRING, T_VARIABLE, T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET, T_SELF, T_STATIC, T_PARENT ), true );
	}

	/**
	 * Range to delete for a whole statement: its indentation and line break when it fills its lines.
	 *
	 * @param array $tokens   The token stack.
	 * @param int   $stackPtr Statement start.
	 * @param int   $end      Closing semicolon.
	 *
	 * @return array First and last pointer.
	 */
	private function getStatementLineRange( array $tokens, $stackPtr, $end ) {
		$first       = $stackPtr;
		$startsLine  = 1 === $tokens[ $stackPtr ]['column'];
		$indentation = $stackPtr - 1;

		if ( ! $startsLine && isset( $tokens[ $indentation ] ) && T_WHITESPACE === $tokens[ $indentation ]['code'] && 1 === $tokens[ $indentation ]['column'] && $tokens[ $indentation ]['line'] === $tokens[ $stackPtr ]['line'] ) {
			$startsLine = true;
			$first      = $indentation;
		}

		$last          = $end;
		$endsWithBreak = false;

		for ( $i = $end + 1; isset( $tokens[ $i ] ) && T_WHITESPACE === $tokens[ $i ]['code'] && $tokens[ $i ]['line'] === $tokens[ $end ]['line']; $i++ ) {
			$last = $i;

			if ( strpos( $tokens[ $i ]['content'], "\n" ) !== false || strpos( $tokens[ $i ]['content'], "\r" ) !== false ) {
				$endsWithBreak = true;
				break;
			}
		}

		// Something else shares the line, so keep its indentation and line break.
		if ( ! $startsLine || ! $endsWithBreak ) {
			$first = $stackPtr;

			if ( $endsWithBreak ) {
				$last = $end;
			}
		}

		return array( $first, $last );
	}

	/**
	 * Names interpolated in a double quoted string or heredoc, without the dollar sign.
	 *
	 * @param string $content String token content.
	 *
	 * @return array Names with the dollar sign.
	 */
	private function getInterpolatedNames( $content ) {
		preg_match_all( '/\$\{?([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)/', $content, $matches );

		return array_map(
			static function ( $name ) {
				return '$' . $name;
			},
			$matches[1]
		);
	}

	/**
	 * Resolve an include in the scanned file to a real path.
	 *
	 * @param File        $phpcsFile  The file being scanned.
	 * @param array       $tokens     The token stack.
	 * @param int         $includePtr Include keyword pointer.
	 * @param array       $scope      Scope data.
	 * @param string|null $root       Plugin root.
	 *
	 * @return string|null
	 */
	private function resolveIncludeInFile( File $phpcsFile, array $tokens, $includePtr, array $scope, $root ) {
		$fileDir = dirname( $phpcsFile->getFilename() );
		$parts   = array();
		$depth   = 0;

		for ( $i = $includePtr + 1; isset( $tokens[ $i ] ); $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( in_array( $code, array( T_OPEN_PARENTHESIS, T_OPEN_SQUARE_BRACKET ), true ) ) {
				++$depth;
			} elseif ( in_array( $code, array( T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET ), true ) ) {
				--$depth;
			}

			if ( $depth < 0 || ( 0 === $depth && in_array( $code, array( T_SEMICOLON, T_CLOSE_TAG, T_COMMA ), true ) ) ) {
				break;
			}

			if ( ! isset( Tokens::$emptyTokens[ $code ] ) ) {
				$parts[] = $this->simplifyToken( $code, $tokens[ $i ]['content'], 'phpcs' );
			}
		}

		$resolveVariable = function ( $name ) use ( $phpcsFile, $tokens, $scope, $fileDir, $root ) {
			return $this->resolveVariableInFile( $phpcsFile, $tokens, $scope, $name, $fileDir, $root );
		};

		return $this->resolvePath( $this->evaluatePath( $parts, $fileDir, $root, $resolveVariable ), $fileDir );
	}

	/**
	 * Resolve a path variable assigned exactly once in the scope of the scanned file.
	 *
	 * @param File        $phpcsFile The file being scanned.
	 * @param array       $tokens    The token stack.
	 * @param array       $scope     Scope data.
	 * @param string      $name      Variable name with the dollar sign.
	 * @param string      $fileDir   Directory of the scanned file.
	 * @param string|null $root      Plugin root.
	 *
	 * @return string|null
	 */
	private function resolveVariableInFile( File $phpcsFile, array $tokens, array $scope, $name, $fileDir, $root ) {
		if ( isset( $scope['params'][ $name ] ) || empty( $scope['assignments'][ $name ] ) || count( $scope['assignments'][ $name ] ) !== 1 ) {
			return null;
		}

		$assignment = $scope['assignments'][ $name ][0];

		// Every other occurrence must be a plain read, never a second write of any kind.
		foreach ( $scope['occurrences'][ $name ] as $occurrence ) {
			if ( $occurrence === $assignment ) {
				continue;
			}

			$prev = $phpcsFile->findPrevious( Tokens::$emptyTokens, $occurrence - 1, null, true );
			$next = $phpcsFile->findNext( Tokens::$emptyTokens, $occurrence + 1, null, true );

			if ( false !== $prev && in_array( $tokens[ $prev ]['code'], array( T_AS, T_BITWISE_AND, T_INC, T_DEC, T_GLOBAL, T_STATIC ), true ) ) {
				return null;
			}

			if ( false !== $next && ( isset( Tokens::$assignmentTokens[ $tokens[ $next ]['code'] ] ) || T_INC === $tokens[ $next ]['code'] || T_DEC === $tokens[ $next ]['code'] ) ) {
				return null;
			}

			if ( $this->isDestructuringTarget( $phpcsFile, $tokens, $occurrence ) ) {
				return null;
			}
		}

		$end   = $phpcsFile->findEndOfStatement( $assignment );
		$equal = $phpcsFile->findNext( T_WHITESPACE, $assignment + 1, null, true );
		$parts = array();

		for ( $i = $equal + 1; $i < $end; $i++ ) {
			if ( ! isset( Tokens::$emptyTokens[ $tokens[ $i ]['code'] ] ) ) {
				$parts[] = $this->simplifyToken( $tokens[ $i ]['code'], $tokens[ $i ]['content'], 'phpcs' );
			}
		}

		return $this->evaluatePath( $parts, $fileDir, $root, null );
	}

	/**
	 * Check whether a variable is a slot in list(), a short list or a foreach target.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  T_VARIABLE pointer.
	 *
	 * @return bool
	 */
	private function isDestructuringTarget( File $phpcsFile, array $tokens, $stackPtr ) {
		if ( ! empty( $tokens[ $stackPtr ]['nested_parenthesis'] ) ) {
			foreach ( array_keys( $tokens[ $stackPtr ]['nested_parenthesis'] ) as $opener ) {
				if ( ! isset( $tokens[ $opener ]['parenthesis_owner'] ) ) {
					continue;
				}

				if ( in_array( $tokens[ $tokens[ $opener ]['parenthesis_owner'] ]['code'], array( T_LIST, T_FOREACH ), true ) ) {
					return true;
				}
			}
		}

		$start = $phpcsFile->findStartOfStatement( $stackPtr );

		for ( $i = $stackPtr - 1; $i >= $start; $i-- ) {
			if ( T_OPEN_SHORT_ARRAY !== $tokens[ $i ]['code'] || ! isset( $tokens[ $i ]['bracket_closer'] ) || $tokens[ $i ]['bracket_closer'] < $stackPtr ) {
				continue;
			}

			$after = $phpcsFile->findNext( Tokens::$emptyTokens, $tokens[ $i ]['bracket_closer'] + 1, null, true );

			if ( false !== $after && T_EQUAL === $tokens[ $after ]['code'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Reduce a token to the handful of kinds a static path can be made of.
	 *
	 * @param int|string $code    Token code.
	 * @param string     $content Token content.
	 * @param string     $source  'phpcs' for PHPCS tokens, 'php' for token_get_all().
	 *
	 * @return array Kind and value.
	 */
	private function simplifyToken( $code, $content, $source ) {
		if ( 'phpcs' === $source ) {
			$map = array(
				T_CONSTANT_ENCAPSED_STRING => 'str',
				T_DIR                      => 'dir',
				T_FILE                     => 'file',
				T_STRING                   => 'id',
				T_DOUBLE_COLON             => '::',
				T_STRING_CONCAT            => '.',
				T_OPEN_PARENTHESIS         => '(',
				T_CLOSE_PARENTHESIS        => ')',
				T_VARIABLE                 => 'var',
				T_NS_SEPARATOR             => 'ns',
			);
		} else {
			$map = array(
				T_CONSTANT_ENCAPSED_STRING => 'str',
				T_DIR                      => 'dir',
				T_FILE                     => 'file',
				T_STRING                   => 'id',
				T_DOUBLE_COLON             => '::',
				'.'                        => '.',
				'('                        => '(',
				')'                        => ')',
				T_VARIABLE                 => 'var',
				T_NS_SEPARATOR             => 'ns',
			);

			if ( defined( 'T_NAME_FULLY_QUALIFIED' ) ) {
				$map[ T_NAME_FULLY_QUALIFIED ] = 'id';
			}
		}

		if ( ! isset( $map[ $code ] ) ) {
			return array( 'x', $content );
		}

		$kind = $map[ $code ];

		if ( 'id' === $kind ) {
			return array( 'id', ltrim( $content, '\\' ) );
		}

		if ( 'str' === $kind ) {
			$value = $this->decodeStringLiteral( $content );

			return null === $value ? array( 'x', $content ) : array( 'str', $value );
		}

		return array( $kind, $content );
	}

	/**
	 * Decode a quoted literal, or null when it interpolates or escapes anything.
	 *
	 * @param string $literal Quoted string.
	 *
	 * @return string|null
	 */
	private function decodeStringLiteral( $literal ) {
		$quote = substr( $literal, 0, 1 );
		$inner = substr( $literal, 1, -1 );

		if ( "'" === $quote ) {
			return str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), $inner );
		}

		if ( '"' === $quote && strpbrk( $inner, '$\\' ) === false ) {
			return $inner;
		}

		return null;
	}

	/**
	 * Evaluate a concatenation of static path parts.
	 *
	 * @param array         $parts           Simplified tokens.
	 * @param string        $fileDir         Directory of the file the expression is in.
	 * @param string|null   $root            Plugin root.
	 * @param callable|null $resolveVariable Resolver for a leading path variable, or null to refuse variables.
	 *
	 * @return string|null
	 */
	private function evaluatePath( array $parts, $fileDir, $root, $resolveVariable ) {
		$parts = array_values(
			array_filter(
				$parts,
				static function ( $part ) {
					return 'ns' !== $part[0];
				}
			)
		);

		// Strip parentheses that wrap the whole expression.
		while ( count( $parts ) > 1 && '(' === $parts[0][0] && $this->findMatchingParen( $parts, 0 ) === count( $parts ) - 1 ) {
			$parts = array_slice( $parts, 1, -1 );
		}

		$segments = array( array() );
		$depth    = 0;

		foreach ( $parts as $part ) {
			if ( '(' === $part[0] ) {
				++$depth;
			} elseif ( ')' === $part[0] ) {
				--$depth;
			}

			if ( 0 === $depth && '.' === $part[0] ) {
				$segments[] = array();
				continue;
			}

			$segments[ count( $segments ) - 1 ][] = $part;
		}

		$path = '';

		foreach ( $segments as $index => $segment ) {
			$value = $this->evaluateSegment( $segment, $fileDir, $root, 0 === $index ? $resolveVariable : null );

			if ( null === $value ) {
				return null;
			}

			$path .= $value;
		}

		return $path;
	}

	/**
	 * Find the index of the parenthesis that closes the one at $start.
	 *
	 * @param array $parts Simplified tokens.
	 * @param int   $start Index of an opening parenthesis.
	 *
	 * @return int|null
	 */
	private function findMatchingParen( array $parts, $start ) {
		$depth = 0;

		foreach ( array_slice( $parts, $start, null, true ) as $index => $part ) {
			if ( '(' === $part[0] ) {
				++$depth;
			} elseif ( ')' === $part[0] ) {
				--$depth;

				if ( 0 === $depth ) {
					return $index;
				}
			}
		}

		return null;
	}

	/**
	 * Evaluate one concatenated piece of a path.
	 *
	 * @param array         $segment         Simplified tokens.
	 * @param string        $fileDir         Directory of the file the expression is in.
	 * @param string|null   $root            Plugin root.
	 * @param callable|null $resolveVariable Resolver for a path variable, or null.
	 *
	 * @return string|null
	 */
	private function evaluateSegment( array $segment, $fileDir, $root, $resolveVariable ) {
		$shape = implode(
			' ',
			array_map(
				static function ( $part ) {
					return 'id' === $part[0] ? 'id:' . strtolower( $part[1] ) : $part[0];
				},
				$segment
			)
		);

		if ( 'str' === $shape ) {
			return $segment[0][1];
		}

		if ( 'dir' === $shape || 'id:dirname ( file )' === $shape ) {
			return $fileDir;
		}

		if ( 'var' === $shape && null !== $resolveVariable ) {
			return $resolveVariable( $segment[0][1] );
		}

		if ( preg_match( '/^id:[a-z0-9_]+ :: id:plugin_path \( \)$/', $shape ) ) {
			return $this->getHelperPath( $segment[0][1], $root );
		}

		return null;
	}

	/**
	 * The directory a plugin_path() helper returns, with a trailing slash.
	 *
	 * @param string      $className Helper class.
	 * @param string|null $root      Plugin root.
	 *
	 * @return string|null
	 */
	private function getHelperPath( $className, $root ) {
		if ( null === $root ) {
			return null;
		}

		$configured = (array) $this->pathHelpers;

		if ( isset( $configured[ $className ] ) ) {
			$dir = (string) $configured[ $className ];
		} elseif ( isset( $this->defaultPathHelpers[ $className ] ) && $this->rootDefinesClass( $root, $className ) ) {
			$dir = $this->defaultPathHelpers[ $className ];
		} else {
			return null;
		}

		if ( '' !== $dir && '/' === $dir[0] ) {
			return rtrim( $dir, '/' ) . '/';
		}

		return rtrim( $root . '/' . $dir, '/' ) . '/';
	}

	/**
	 * Check whether the plugin contains the file that defines a helper class.
	 *
	 * @param string $root      Plugin root.
	 * @param string $className Class name.
	 *
	 * @return bool
	 */
	private function rootDefinesClass( $root, $className ) {
		foreach ( $this->getRootFiles( $root ) as $path ) {
			if ( basename( $path ) === $className . '.php' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Turn an evaluated path into an existing real file.
	 *
	 * @param string|null $path    Evaluated path.
	 * @param string      $fileDir Directory of the including file, for relative literals.
	 *
	 * @return string|null
	 */
	private function resolvePath( $path, $fileDir ) {
		if ( null === $path || '' === $path ) {
			return null;
		}

		if ( '/' !== $path[0] ) {
			$path = $fileDir . '/' . $path;
		}

		$real = realpath( preg_replace( '#/+#', '/', $path ) );

		return false !== $real && is_file( $real ) ? $real : null;
	}

	/**
	 * Find the plugin root for the scanned file.
	 *
	 * @param File $phpcsFile The file being scanned.
	 *
	 * @return string|null
	 */
	private function getPluginRoot( File $phpcsFile ) {
		if ( '' !== $this->pluginRoot ) {
			$root = realpath( $this->pluginRoot );

			return false === $root ? null : $root;
		}

		$filename = $phpcsFile->getFilename();

		if ( 'STDIN' === $filename ) {
			return null;
		}

		$dir = realpath( dirname( $filename ) );

		if ( false === $dir ) {
			return null;
		}

		if ( array_key_exists( $dir, self::$rootCache ) ) {
			return self::$rootCache[ $dir ];
		}

		$root = null;

		foreach ( array( array( 'phpcs.xml', 'phpcs.xml.dist' ), array( 'composer.json' ) ) as $markers ) {
			for ( $current = $dir; null === $root; $current = dirname( $current ) ) {
				foreach ( $markers as $marker ) {
					if ( file_exists( $current . '/' . $marker ) ) {
						$root = $current;
					}
				}

				if ( dirname( $current ) === $current ) {
					break;
				}
			}
		}

		self::$rootCache[ $dir ] = $root;

		return $root;
	}

	/**
	 * Every PHP file under a plugin root, skipping dependencies and hidden directories.
	 *
	 * @param string $root Plugin root.
	 *
	 * @return array Real paths.
	 */
	private function getRootFiles( $root ) {
		if ( isset( self::$rootFilesCache[ $root ] ) ) {
			return self::$rootFilesCache[ $root ];
		}

		$files    = array();
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveCallbackFilterIterator(
				new \RecursiveDirectoryIterator( $root, \FilesystemIterator::SKIP_DOTS ),
				static function ( $file ) {
					if ( $file->isDir() ) {
						$name = $file->getFilename();

						return '.' !== $name[0] && ! in_array( $name, array( 'vendor', 'node_modules' ), true );
					}

					return 'php' === strtolower( $file->getExtension() );
				}
			)
		);

		foreach ( $iterator as $file ) {
			$real = $file->getRealPath();

			if ( false !== $real ) {
				$files[] = $real;
			}
		}

		self::$rootFilesCache[ $root ] = $files;

		return $files;
	}

	/**
	 * Names a file can read when it is included, transitively, without the dollar sign.
	 *
	 * @param string      $path     Real path.
	 * @param string|null $root     Plugin root.
	 * @param int         $depth    Current depth.
	 * @param array       $visiting Paths on the current chain.
	 *
	 * @return array|true Name set, or true when it may read anything.
	 */
	private function getIncludedNames( $path, $root, $depth, array $visiting ) {
		$info = $this->getFileInfo( $path, $root );

		if ( null === $info || $depth > $this->maxDepth || $info['scopes'][0]['abort'] ) {
			return true;
		}

		$names = $info['names'];

		foreach ( $info['scopes'][0]['sites'] as $site ) {
			if ( null === $site ) {
				return true;
			}

			if ( isset( $visiting[ $site ] ) ) {
				continue;
			}

			$nested = $this->getIncludedNames( $site, $root, $depth + 1, $visiting + array( $site => true ) );

			if ( true === $nested ) {
				return true;
			}

			$names += $nested;
		}

		return $names;
	}

	/**
	 * At file scope, check whether a file that includes this one, or a sibling it includes, reads the variable.
	 *
	 * @param File        $phpcsFile The file being scanned.
	 * @param string|null $root      Plugin root.
	 * @param string      $name      Variable name without the dollar sign.
	 *
	 * @return bool
	 */
	private function isReadOutsideFile( File $phpcsFile, $root, $name ) {
		$self = realpath( $phpcsFile->getFilename() );

		if ( null === $root || false === $self ) {
			return true;
		}

		$mentioned = false;

		foreach ( $this->getRootFiles( $root ) as $path ) {
			if ( $path === $self ) {
				continue;
			}

			$info = $this->getFileInfo( $path, $root );

			if ( null === $info || isset( $info['names'][ $name ] ) || isset( $info['globals'][ $name ] ) ) {
				$mentioned = true;
				break;
			}
		}

		// No other file in the plugin can see the name.
		if ( ! $mentioned ) {
			return false;
		}

		return $this->isReadByIncluders( $self, $root, $name, 0, array( $self => true ) );
	}

	/**
	 * Check every include site that may load $path, and the includers of those that are views themselves.
	 *
	 * @param string $path    Real path of the included file.
	 * @param string $root    Plugin root.
	 * @param string $name    Variable name without the dollar sign.
	 * @param int    $depth   Current depth.
	 * @param array  $visited Files already checked.
	 *
	 * @return bool
	 */
	private function isReadByIncluders( $path, $root, $name, $depth, array $visited ) {
		if ( $depth > $this->maxDepth ) {
			return true;
		}

		$stem  = basename( $path, '.php' );
		$found = false;

		foreach ( $this->getRootFiles( $root ) as $includer ) {
			if ( isset( $visited[ $includer ] ) ) {
				continue;
			}

			$info = $this->getFileInfo( $includer, $root );

			if ( null === $info ) {
				return true;
			}

			if ( strpos( $info['content'], $stem ) === false ) {
				continue;
			}

			foreach ( $info['scopes'] as $scopeId => $scope ) {
				foreach ( $scope['sites'] as $siteIndex => $site ) {
					// An unresolved include might be this file.
					if ( null !== $site && $site !== $path ) {
						continue;
					}

					$found = true;

					if ( $scope['abort'] || isset( $scope['vars'][ $name ] ) ) {
						return true;
					}

					if ( $this->isReadBySibling( $scope['sites'], $siteIndex, $root, $name ) ) {
						return true;
					}

					// A view including a view shares its scope with its own includer.
					if ( 0 === $scopeId && $this->isReadByIncluders( $includer, $root, $name, $depth + 1, $visited + array( $includer => true ) ) ) {
						return true;
					}
				}
			}
		}

		// The file name appears nowhere as an include, so it is loaded in a way this sniff cannot see.
		return ! $found;
	}

	/**
	 * Check the other includes in the same scope as an include site.
	 *
	 * @param array  $sites     Resolved paths, null when unresolved.
	 * @param int    $siteIndex The include site being checked.
	 * @param string $root      Plugin root.
	 * @param string $name      Variable name without the dollar sign.
	 *
	 * @return bool
	 */
	private function isReadBySibling( array $sites, $siteIndex, $root, $name ) {
		foreach ( $sites as $index => $sibling ) {
			if ( $index === $siteIndex ) {
				continue;
			}

			if ( null === $sibling ) {
				return true;
			}

			$names = $this->getIncludedNames( $sibling, $root, 1, array( $sibling => true ) );

			if ( true === $names || isset( $names[ $name ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Parse a file outside PHPCS: its variable names, and per scope its includes and abort conditions.
	 *
	 * Scope 0 is the file scope, other keys are function bodies. Class bodies are left out.
	 *
	 * @param string      $path Real path.
	 * @param string|null $root Plugin root.
	 *
	 * @return array|null
	 */
	private function getFileInfo( $path, $root ) {
		if ( array_key_exists( $path, self::$fileInfoCache ) ) {
			return self::$fileInfoCache[ $path ];
		}

		self::$fileInfoCache[ $path ] = null;
		$content                      = @file_get_contents( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( false === $content ) {
			return null;
		}

		preg_match_all( '/\$([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)/', $content, $nameMatches );
		preg_match_all( '/GLOBALS\[\s*[\'"]([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)[\'"]/', $content, $globalMatches );

		$info = array(
			'content' => $content,
			'names'   => array_fill_keys( $nameMatches[1], true ),
			'globals' => array_fill_keys( $globalMatches[1], true ),
			'scopes'  => $this->parseScopes( $content, dirname( $path ), $root ),
		);

		self::$fileInfoCache[ $path ] = $info;

		return $info;
	}

	/**
	 * Split a file into scopes with token_get_all() and collect what each one can see.
	 *
	 * @param string      $content File content.
	 * @param string      $fileDir Directory of the file.
	 * @param string|null $root    Plugin root.
	 *
	 * @return array
	 */
	private function parseScopes( $content, $fileDir, $root ) {
		$raw    = token_get_all( $content );
		$tokens = array();

		foreach ( $raw as $token ) {
			$tokens[] = is_array( $token ) ? array( $token[0], $token[1] ) : array( $token, $token );
		}

		$owners     = $this->mapTokenOwners( $tokens );
		$scopes     = array();
		$includeIds = array( T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE );

		foreach ( array_unique( $owners ) as $owner ) {
			if ( -2 !== $owner ) {
				$scopes[ $owner ] = array(
					'vars'  => array(),
					'abort' => false,
					'sites' => array(),
				);
			}
		}

		if ( ! isset( $scopes[0] ) ) {
			$scopes[0] = array(
				'vars'  => array(),
				'abort' => false,
				'sites' => array(),
			);
		}

		$count = count( $tokens );

		foreach ( $tokens as $index => $token ) {
			$owner = $owners[ $index ];

			if ( -2 === $owner ) {
				continue;
			}

			list( $id, $text ) = $token;

			if ( T_VARIABLE === $id ) {
				$scopes[ $owner ]['vars'][ substr( $text, 1 ) ] = true;
			} elseif ( T_STRING_VARNAME === $id ) {
				$scopes[ $owner ]['vars'][ $text ] = true;
			} elseif ( T_EVAL === $id || ( '$' === $id ) ) {
				$scopes[ $owner ]['abort'] = true;
			} elseif ( T_STRING === $id && isset( $this->scopeCalls[ strtolower( $text ) ] ) ) {
				$next = $this->nextMeaningful( $tokens, $index + 1, $count );
				$prev = $this->previousMeaningful( $tokens, $index - 1 );

				if ( null !== $next && '(' === $tokens[ $next ][0] && ( null === $prev || ! in_array( $tokens[ $prev ][0], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW ), true ) ) ) {
					$scopes[ $owner ]['abort'] = true;
				}
			} elseif ( in_array( $id, $includeIds, true ) ) {
				$scopes[ $owner ]['sites'][] = $this->resolveRawInclude( $tokens, $owners, $index, $fileDir, $root );
			}
		}

		return $scopes;
	}

	/**
	 * Map each raw token to its scope: 0 for file scope, a body start index for a function, -2 for class bodies.
	 *
	 * Function signatures belong to the function, so parameters count as its variables.
	 *
	 * @param array $tokens Raw tokens as id and text pairs.
	 *
	 * @return array
	 */
	private function mapTokenOwners( array $tokens ) {
		$owners       = array();
		$stack        = array();
		$pending      = null;
		$pendingStart = null;
		$pendingParen = 0;
		$parenDepth   = 0;
		$classIds     = array( T_CLASS, T_INTERFACE, T_TRAIT );

		if ( defined( 'T_ENUM' ) ) {
			$classIds[] = T_ENUM;
		}

		foreach ( $tokens as $index => $token ) {
			$id    = $token[0];
			$owner = 0;

			foreach ( array_reverse( $stack ) as $frame ) {
				if ( null !== $frame ) {
					$owner = $frame;
					break;
				}
			}

			if ( null !== $pending && 'function' === $pending ) {
				$owner = $pendingStart;
			}

			if ( T_FUNCTION === $id ) {
				$pending      = 'function';
				$pendingStart = $index;
				$pendingParen = $parenDepth;
				$owner        = $index;
			} elseif ( in_array( $id, $classIds, true ) && ! $this->isClassConstantFetch( $tokens, $index ) ) {
				$pending      = 'class';
				$pendingParen = $parenDepth;
			} elseif ( '(' === $id ) {
				++$parenDepth;
			} elseif ( ')' === $id ) {
				--$parenDepth;
			} elseif ( ';' === $id && null !== $pending && $parenDepth === $pendingParen ) {
				// Abstract or interface method without a body.
				$pending = null;
			} elseif ( '{' === $id || T_CURLY_OPEN === $id || T_DOLLAR_OPEN_CURLY_BRACES === $id ) {
				if ( '{' === $id && null !== $pending && $parenDepth === $pendingParen ) {
					$stack[] = 'function' === $pending ? $pendingStart : -2;
					$pending = null;
				} else {
					$stack[] = null;
				}
			} elseif ( '}' === $id ) {
				array_pop( $stack );
			}

			$owners[ $index ] = $owner;
		}

		return $owners;
	}

	/**
	 * Check whether a class keyword is "Foo::class" rather than a declaration.
	 *
	 * @param array $tokens Raw tokens.
	 * @param int   $index  Keyword index.
	 *
	 * @return bool
	 */
	private function isClassConstantFetch( array $tokens, $index ) {
		$prev = $this->previousMeaningful( $tokens, $index - 1 );

		return null !== $prev && T_DOUBLE_COLON === $tokens[ $prev ][0];
	}

	/**
	 * Index of the next raw token that is not whitespace or a comment.
	 *
	 * @param array $tokens Raw tokens.
	 * @param int   $start  First index to check.
	 * @param int   $count  Token count.
	 *
	 * @return int|null
	 */
	private function nextMeaningful( array $tokens, $start, $count ) {
		for ( $i = $start; $i < $count; $i++ ) {
			if ( ! in_array( $tokens[ $i ][0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) {
				return $i;
			}
		}

		return null;
	}

	/**
	 * Index of the previous raw token that is not whitespace or a comment.
	 *
	 * @param array $tokens Raw tokens.
	 * @param int   $start  First index to check.
	 *
	 * @return int|null
	 */
	private function previousMeaningful( array $tokens, $start ) {
		for ( $i = $start; $i >= 0; $i-- ) {
			if ( ! in_array( $tokens[ $i ][0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) {
				return $i;
			}
		}

		return null;
	}

	/**
	 * Resolve an include found by token_get_all().
	 *
	 * @param array       $tokens  Raw tokens.
	 * @param array       $owners  Scope of each token.
	 * @param int         $index   Include keyword index.
	 * @param string      $fileDir Directory of the file.
	 * @param string|null $root    Plugin root.
	 *
	 * @return string|null
	 */
	private function resolveRawInclude( array $tokens, array $owners, $index, $fileDir, $root ) {
		$parts = $this->collectRawExpression( $tokens, $index + 1 );
		$owner = $owners[ $index ];

		$resolveVariable = function ( $name ) use ( $tokens, $owners, $owner, $fileDir, $root ) {
			return $this->resolveRawVariable( $tokens, $owners, $owner, $name, $fileDir, $root );
		};

		return $this->resolvePath( $this->evaluatePath( $parts, $fileDir, $root, $resolveVariable ), $fileDir );
	}

	/**
	 * Resolve a path variable assigned exactly once in a scope of a file parsed by token_get_all().
	 *
	 * @param array       $tokens  Raw tokens.
	 * @param array       $owners  Scope of each token.
	 * @param int         $owner   Scope of the include.
	 * @param string      $name    Variable name with the dollar sign.
	 * @param string      $fileDir Directory of the file.
	 * @param string|null $root    Plugin root.
	 *
	 * @return string|null
	 */
	private function resolveRawVariable( array $tokens, array $owners, $owner, $name, $fileDir, $root ) {
		$count      = count( $tokens );
		$assignment = null;
		$bodyStart  = 0;

		// A function's signature shares its scope id, so anything before the body is a parameter or use variable.
		if ( 0 !== $owner ) {
			for ( $bodyStart = $owner; $bodyStart < $count && '{' !== $tokens[ $bodyStart ][0]; $bodyStart++ ) {
				continue;
			}
		}

		foreach ( $tokens as $i => $token ) {
			if ( T_VARIABLE !== $token[0] || $token[1] !== $name || $owners[ $i ] !== $owner ) {
				continue;
			}

			if ( $i < $bodyStart || $this->isRawDestructuringTarget( $tokens, $i ) ) {
				return null;
			}

			$prev = $this->previousMeaningful( $tokens, $i - 1 );
			$next = $this->nextMeaningful( $tokens, $i + 1, $count );

			if ( null !== $prev && in_array( $tokens[ $prev ][0], array( T_AS, '&', T_INC, T_DEC, T_GLOBAL, T_STATIC, T_DOUBLE_ARROW ), true ) ) {
				return null;
			}

			if ( null === $next || ! in_array( $tokens[ $next ][0], $this->getRawWriteIds(), true ) ) {
				continue;
			}

			if ( '=' !== $tokens[ $next ][0] || null !== $assignment ) {
				return null;
			}

			$assignment = $next;
		}

		if ( null === $assignment ) {
			return null;
		}

		return $this->evaluatePath( $this->collectRawExpression( $tokens, $assignment + 1 ), $fileDir, $root, null );
	}

	/**
	 * Check whether a raw variable token sits inside list() or a short list that is assigned to.
	 *
	 * @param array $tokens Raw tokens.
	 * @param int   $index  Variable index.
	 *
	 * @return bool
	 */
	private function isRawDestructuringTarget( array $tokens, $index ) {
		$depth = 0;

		for ( $i = $index - 1; $i >= 0; $i-- ) {
			$id = $tokens[ $i ][0];

			if ( ';' === $id || '{' === $id || '}' === $id || T_OPEN_TAG === $id ) {
				return false;
			}

			if ( ')' === $id || ']' === $id ) {
				++$depth;
				continue;
			}

			if ( '(' !== $id && '[' !== $id ) {
				continue;
			}

			if ( $depth > 0 ) {
				--$depth;
				continue;
			}

			// An unmatched opener encloses the variable.
			if ( '(' === $id ) {
				$before = $this->previousMeaningful( $tokens, $i - 1 );

				if ( null !== $before && T_LIST === $tokens[ $before ][0] ) {
					return true;
				}
				continue;
			}

			$closer = $this->findRawCloser( $tokens, $i );
			$after  = null === $closer ? null : $this->nextMeaningful( $tokens, $closer + 1, count( $tokens ) );

			if ( null !== $after && '=' === $tokens[ $after ][0] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Index of the bracket that closes the one at $start.
	 *
	 * @param array $tokens Raw tokens.
	 * @param int   $start  Opening bracket index.
	 *
	 * @return int|null
	 */
	private function findRawCloser( array $tokens, $start ) {
		$depth = 0;
		$count = count( $tokens );

		for ( $i = $start; $i < $count; $i++ ) {
			if ( '[' === $tokens[ $i ][0] ) {
				++$depth;
			} elseif ( ']' === $tokens[ $i ][0] ) {
				--$depth;

				if ( 0 === $depth ) {
					return $i;
				}
			}
		}

		return null;
	}

	/**
	 * Raw token ids that write to a variable placed before them.
	 *
	 * @return array
	 */
	private function getRawWriteIds() {
		$ids = array( '=', T_CONCAT_EQUAL, T_PLUS_EQUAL, T_MINUS_EQUAL, T_MUL_EQUAL, T_DIV_EQUAL, T_MOD_EQUAL, T_AND_EQUAL, T_OR_EQUAL, T_XOR_EQUAL, T_SL_EQUAL, T_SR_EQUAL, T_POW_EQUAL, T_INC, T_DEC );

		if ( defined( 'T_COALESCE_EQUAL' ) ) {
			$ids[] = T_COALESCE_EQUAL;
		}

		return $ids;
	}

	/**
	 * Simplified tokens of an expression, up to the semicolon, close tag or comma that ends it.
	 *
	 * @param array $tokens Raw tokens.
	 * @param int   $start  First index.
	 *
	 * @return array
	 */
	private function collectRawExpression( array $tokens, $start ) {
		$parts = array();
		$depth = 0;
		$count = count( $tokens );

		for ( $i = $start; $i < $count; $i++ ) {
			$id = $tokens[ $i ][0];

			if ( '(' === $id || '[' === $id ) {
				++$depth;
			} elseif ( ')' === $id || ']' === $id ) {
				--$depth;
			}

			if ( $depth < 0 || ( 0 === $depth && in_array( $id, array( ';', ',', T_CLOSE_TAG ), true ) ) ) {
				break;
			}

			if ( ! in_array( $id, array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) {
				$parts[] = $this->simplifyToken( $id, $tokens[ $i ][1], 'php' );
			}
		}

		return $parts;
	}
}
