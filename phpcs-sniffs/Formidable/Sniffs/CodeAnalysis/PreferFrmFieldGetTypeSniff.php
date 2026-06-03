<?php
/**
 * Sniff to detect FrmField::getOne() calls where the result is only used for the field type.
 *
 * When the result of FrmField::getOne() is only accessed via ->type, the lighter
 * FrmField::get_type() should be used instead.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects FrmField::getOne() where the result is only used to access ->type.
 *
 * Bad:
 *   $field = FrmField::getOne( $id );
 *   if ( $field && $field->type === 'file' ) {
 *
 * Good:
 *   if ( 'file' === FrmField::get_type( $id ) ) {
 */
class PreferFrmFieldGetTypeSniff implements Sniff {

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

		if ( 'getOne' !== $tokens[ $stackPtr ]['content'] ) {
			return;
		}

		// Verify this is FrmField::getOne().
		if ( ! $this->is_frm_field_get_one( $phpcsFile, $tokens, $stackPtr ) ) {
			return;
		}

		// Find the variable this is assigned to: $var = FrmField::getOne(...).
		$variable_name = $this->get_assigned_variable( $phpcsFile, $tokens, $stackPtr );

		if ( false === $variable_name ) {
			return;
		}

		// Skip conditional reassignment (e.g. if ( ! is_object( $field ) ) { $field = FrmField::getOne( $field ); }).
		if ( $this->is_conditional_reassignment( $phpcsFile, $tokens, $stackPtr, $variable_name ) ) {
			return;
		}

		// Find the scope to analyze (function/method body or file).
		$scope_boundaries = $this->get_scope_boundaries( $tokens, $stackPtr );

		// Find the semicolon that ends the assignment statement.
		$open_paren = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $open_paren || T_OPEN_PARENTHESIS !== $tokens[ $open_paren ]['code'] ) {
			return;
		}

		$close_paren    = $tokens[ $open_paren ]['parenthesis_closer'];
		$assignment_end = $phpcsFile->findNext( T_SEMICOLON, $close_paren + 1 );

		if ( false === $assignment_end ) {
			return;
		}

		// Collect all usage positions of the variable after the assignment.
		$usages = $this->get_variable_usages( $phpcsFile, $tokens, $variable_name, $assignment_end + 1, $scope_boundaries['end'] );

		if ( false === $usages ) {
			return;
		}

		// Extract the argument passed to getOne().
		$get_one_arg = $this->extract_get_one_arg( $phpcsFile, $tokens, $open_paren, $close_paren );

		if ( false === $get_one_arg ) {
			return;
		}

		// Find the start of the assignment line (for removing the whole line).
		$assignment_var = $phpcsFile->findPrevious( T_WHITESPACE, $phpcsFile->findPrevious( T_WHITESPACE, $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true ) - 1, null, true ) - 1, null, true );
		$line_start     = $this->find_line_start( $tokens, $assignment_var );

		$fix = $phpcsFile->addFixableError(
			'Use FrmField::get_type() instead of FrmField::getOne() when only the field type is needed. The variable %s is only used to access ->type.',
			$stackPtr,
			'UseGetType',
			array( $variable_name )
		);

		if ( $fix ) {
			$this->apply_fix( $phpcsFile, $tokens, $line_start, $assignment_end, $usages, $get_one_arg );
		}
	}

	/**
	 * Check if this is a conditional reassignment pattern.
	 *
	 * Detects patterns like:
	 *   if ( ! is_object( $field ) ) {
	 *       $field = FrmField::getOne( $field );
	 *   }
	 *
	 * Where the assigned variable is also the argument to getOne(), indicating
	 * a conditional type-coercion that should not be replaced with get_type().
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param array  $tokens        The token stack.
	 * @param int    $stackPtr      The position of 'getOne'.
	 * @param string $variable_name The variable being assigned.
	 *
	 * @return bool
	 */
	private function is_conditional_reassignment( File $phpcsFile, array $tokens, $stackPtr, $variable_name ) {
		if ( empty( $tokens[ $stackPtr ]['conditions'] ) ) {
			return false;
		}

		// Check if the immediate enclosing scope is a conditional block.
		$conditions = $tokens[ $stackPtr ]['conditions'];
		$last_type  = end( $conditions );

		if ( ! in_array( $last_type, array( T_IF, T_ELSEIF, T_ELSE ), true ) ) {
			return false;
		}

		// Check if the getOne argument is the same variable being assigned to.
		$open_paren = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $open_paren || T_OPEN_PARENTHESIS !== $tokens[ $open_paren ]['code'] ) {
			return false;
		}

		$close_paren = $tokens[ $open_paren ]['parenthesis_closer'];
		$first_arg   = $phpcsFile->findNext( T_WHITESPACE, $open_paren + 1, $close_paren, true );

		if ( false === $first_arg || T_VARIABLE !== $tokens[ $first_arg ]['code'] ) {
			return false;
		}

		return $tokens[ $first_arg ]['content'] === $variable_name;
	}

	/**
	 * Verify the token is part of FrmField::getOne().
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  The position of 'getOne'.
	 *
	 * @return bool
	 */
	private function is_frm_field_get_one( File $phpcsFile, array $tokens, $stackPtr ) {
		// Expect :: before getOne.
		$double_colon = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $double_colon || T_DOUBLE_COLON !== $tokens[ $double_colon ]['code'] ) {
			return false;
		}

		// Expect FrmField before ::.
		$class_name = $phpcsFile->findPrevious( T_WHITESPACE, $double_colon - 1, null, true );

		if ( false === $class_name || T_STRING !== $tokens[ $class_name ]['code'] ) {
			return false;
		}

		return 'FrmField' === $tokens[ $class_name ]['content'];
	}

	/**
	 * Get the variable name that FrmField::getOne() is assigned to.
	 *
	 * Looks for the pattern: $var = FrmField::getOne(...)
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $stackPtr  The position of 'getOne'.
	 *
	 * @return false|string The variable name (e.g. '$field') or false.
	 */
	private function get_assigned_variable( File $phpcsFile, array $tokens, $stackPtr ) {
		// Walk back past: getOne <- :: <- FrmField <- = <- $var.
		$double_colon = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $double_colon ) {
			return false;
		}

		$class_name = $phpcsFile->findPrevious( T_WHITESPACE, $double_colon - 1, null, true );

		if ( false === $class_name ) {
			return false;
		}

		$equals = $phpcsFile->findPrevious( T_WHITESPACE, $class_name - 1, null, true );

		if ( false === $equals || T_EQUAL !== $tokens[ $equals ]['code'] ) {
			return false;
		}

		$variable = $phpcsFile->findPrevious( T_WHITESPACE, $equals - 1, null, true );

		if ( false === $variable || T_VARIABLE !== $tokens[ $variable ]['code'] ) {
			return false;
		}

		return $tokens[ $variable ]['content'];
	}

	/**
	 * Get the scope boundaries (start and end token positions) for analysis.
	 *
	 * @param array $tokens   The token stack.
	 * @param int   $stackPtr The position of the current token.
	 *
	 * @return array Array with 'start' and 'end' keys.
	 */
	private function get_scope_boundaries( array $tokens, $stackPtr ) {
		if ( ! isset( $tokens[ $stackPtr ]['conditions'] ) || empty( $tokens[ $stackPtr ]['conditions'] ) ) {
			return array(
				'start' => 0,
				'end'   => count( $tokens ) - 1,
			);
		}

		// Find the innermost function/method scope.
		$scope_ptr = null;

		foreach ( $tokens[ $stackPtr ]['conditions'] as $ptr => $type ) {
			if ( in_array( $type, array( T_FUNCTION, T_CLOSURE ), true ) ) {
				$scope_ptr = $ptr;
			}
		}

		if ( null !== $scope_ptr && isset( $tokens[ $scope_ptr ]['scope_opener'], $tokens[ $scope_ptr ]['scope_closer'] ) ) {
			return array(
				'start' => $tokens[ $scope_ptr ]['scope_opener'],
				'end'   => $tokens[ $scope_ptr ]['scope_closer'],
			);
		}

		return array(
			'start' => 0,
			'end'   => count( $tokens ) - 1,
		);
	}

	/**
	 * Get all variable usages after the assignment, categorized by type.
	 *
	 * Returns an array of usage info, or false if any usage disqualifies.
	 *
	 * Each usage entry has:
	 * - 'type': 'type_access' or 'truthiness'
	 * - 'var_pos': position of the $var token
	 * - 'arrow_pos': position of -> (type_access only)
	 * - 'property_pos': position of 'type' (type_access only)
	 * - 'and_pos': position of && token (truthiness with && only)
	 * - 'whitespace_before_and': position of whitespace before && (if any)
	 *
	 * @param File   $phpcsFile     The file being scanned.
	 * @param array  $tokens        The token stack.
	 * @param string $variable_name The variable name to track.
	 * @param int    $start         Start position for scanning.
	 * @param int    $end           End position for scanning.
	 *
	 * @return array|false Array of usage info or false if variable has disqualifying usages.
	 */
	private function get_variable_usages( File $phpcsFile, array $tokens, $variable_name, $start, $end ) {
		$usages            = array();
		$found_type_access = false;

		for ( $i = $start; $i <= $end; $i++ ) {
			if ( T_VARIABLE !== $tokens[ $i ]['code'] || $tokens[ $i ]['content'] !== $variable_name ) {
				continue;
			}

			$next = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );

			if ( false === $next ) {
				return false;
			}

			// Case 1: $var->type.
			if ( T_OBJECT_OPERATOR === $tokens[ $next ]['code'] ) {
				$property = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );

				if ( false === $property || T_STRING !== $tokens[ $property ]['code'] || 'type' !== $tokens[ $property ]['content'] ) {
					return false;
				}

				$after_property = $phpcsFile->findNext( T_WHITESPACE, $property + 1, null, true );

				if ( false !== $after_property && T_OPEN_PARENTHESIS === $tokens[ $after_property ]['code'] ) {
					return false;
				}

				$found_type_access = true;
				$usages[]          = array(
					'type'         => 'type_access',
					'var_pos'      => $i,
					'arrow_pos'    => $next,
					'property_pos' => $property,
				);
				continue;
			}

			// Case 2: $var && or $var ) — truthiness check.
			if ( $this->is_truthiness_check( $tokens, $next ) ) {
				$usage = array(
					'type'    => 'truthiness',
					'var_pos' => $i,
				);

				if ( T_BOOLEAN_AND === $tokens[ $next ]['code'] || T_LOGICAL_AND === $tokens[ $next ]['code'] ) {
					$usage['and_pos'] = $next;

					// Track whitespace after && for removal.
					$after_and = $next + 1;

					if ( $after_and < $end && T_WHITESPACE === $tokens[ $after_and ]['code'] ) {
						$usage['whitespace_after_and'] = $after_and;
					}

					// Track whitespace before $var for removal.
					$before_var = $i - 1;

					if ( $before_var >= $start && T_WHITESPACE === $tokens[ $before_var ]['code'] ) {
						$usage['whitespace_before_var'] = $before_var;
					}
				}

				$usages[] = $usage;
				continue;
			}

			if ( T_EQUAL === $tokens[ $next ]['code'] ) {
				return false;
			}

			return false;
		}

		if ( ! $found_type_access ) {
			return false;
		}

		return $usages;
	}

	/**
	 * Check if the token after a variable indicates a truthiness check.
	 *
	 * @param array $tokens The token stack.
	 * @param int   $next   The position of the token after the variable.
	 *
	 * @return bool
	 */
	private function is_truthiness_check( array $tokens, $next ) {
		$truthiness_tokens = array(
			T_BOOLEAN_AND,         // &&
			T_LOGICAL_AND,         // and
			T_CLOSE_PARENTHESIS,   // )
			T_INLINE_THEN,         // ?
		);

		return in_array( $tokens[ $next ]['code'], $truthiness_tokens, true );
	}

	/**
	 * Extract the argument content from getOne( ... ).
	 *
	 * @param File  $phpcsFile  The file being scanned.
	 * @param array $tokens     The token stack.
	 * @param int   $open_paren Position of the opening parenthesis.
	 * @param int   $close_paren Position of the closing parenthesis.
	 *
	 * @return false|string The argument string or false.
	 */
	private function extract_get_one_arg( File $phpcsFile, array $tokens, $open_paren, $close_paren ) {
		$arg = '';

		for ( $i = $open_paren + 1; $i < $close_paren; $i++ ) {
			$arg .= $tokens[ $i ]['content'];
		}

		$arg = trim( $arg );

		if ( '' === $arg ) {
			return false;
		}

		return $arg;
	}

	/**
	 * Find the start of the line for a given token position.
	 *
	 * @param array $tokens   The token stack.
	 * @param int   $stackPtr The position to find the line start for.
	 *
	 * @return int
	 */
	private function find_line_start( array $tokens, $stackPtr ) {
		$line = $tokens[ $stackPtr ]['line'];

		while ( $stackPtr > 0 && $tokens[ $stackPtr - 1 ]['line'] === $line ) {
			--$stackPtr;
		}

		return $stackPtr;
	}

	/**
	 * Apply the fix.
	 *
	 * 1. Remove the entire assignment line ($var = FrmField::getOne( ... );)
	 * 2. Replace each $var->type with FrmField::get_type( <arg> )
	 * 3. Remove $var && truthiness checks
	 *
	 * @param File  $phpcsFile      The file being scanned.
	 * @param array $tokens         The token stack.
	 * @param int   $line_start     Start of the assignment line.
	 * @param int   $assignment_end Semicolon ending the assignment.
	 * @param array $usages         Array of variable usage info.
	 * @param string $get_one_arg   The argument originally passed to getOne().
	 *
	 * @return void
	 */
	private function apply_fix( File $phpcsFile, array $tokens, $line_start, $assignment_end, array $usages, $get_one_arg ) {
		$fixer       = $phpcsFile->fixer;
		$replacement = 'FrmField::get_type( ' . $get_one_arg . ' )';

		$fixer->beginChangeset();

		// 1. Remove the assignment line including trailing newline.
		$remove_end = $assignment_end;

		if ( isset( $tokens[ $assignment_end + 1 ] ) && T_WHITESPACE === $tokens[ $assignment_end + 1 ]['code'] && $tokens[ $assignment_end + 1 ]['content'] === $phpcsFile->eolChar ) {
			$remove_end = $assignment_end + 1;
		}

		for ( $i = $line_start; $i <= $remove_end; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}

		// 2. Process each usage.
		foreach ( $usages as $usage ) {
			if ( 'type_access' === $usage['type'] ) {
				// Replace $var->type with FrmField::get_type( <arg> ).
				$fixer->replaceToken( $usage['var_pos'], $replacement );

				// Remove tokens between $var and after 'type' (the -> and type tokens, plus whitespace).
				for ( $i = $usage['var_pos'] + 1; $i <= $usage['property_pos']; $i++ ) {
					$fixer->replaceToken( $i, '' );
				}
			} elseif ( 'truthiness' === $usage['type'] && isset( $usage['and_pos'] ) ) {
				// Remove $var && and whitespace after &&, but keep whitespace before $var.
				$remove_through = $usage['and_pos'];

				if ( isset( $usage['whitespace_after_and'] ) ) {
					$remove_through = $usage['whitespace_after_and'];
				}

				for ( $i = $usage['var_pos']; $i <= $remove_through; $i++ ) {
					$fixer->replaceToken( $i, '' );
				}
			}
		}

		$fixer->endChangeset();
	}
}
