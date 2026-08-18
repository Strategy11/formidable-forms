<?php
/**
 * Sniff to detect inline is_object/is_array ternaries for field type access.
 *
 * Detects patterns like:
 *   is_object( $field ) ? $field->type : $field['type']
 *   is_array( $field ) ? $field['type'] : $field->type
 *
 * These should use FrmField::get_field_type( $field ) instead.
 *
 * @package Formidable\Sniffs\CodeAnalysis
 */

namespace Formidable\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects and fixes inline is_object/is_array ternaries that check field type.
 *
 * Bad:
 *   is_object( $field ) ? $field->type : $field['type']
 *   is_array( $this->field ) ? $this->field['type'] : $this->field->type
 *
 * Good:
 *   FrmField::get_field_type( $field )
 *   FrmField::get_field_type( $this->field )
 */
class PreferFrmFieldGetFieldTypeSniff implements Sniff {

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
		$content = strtolower( $tokens[ $stackPtr ]['content'] );

		if ( 'is_object' !== $content && 'is_array' !== $content ) {
			return;
		}

		$check_function = $tokens[ $stackPtr ]['content'];
		$is_object_check = 'is_object' === $content;

		// Skip if this is the get_field_type method definition itself.
		if ( $this->is_inside_get_field_type( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// Find the opening parenthesis of is_object/is_array.
		$open_paren = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );

		if ( false === $open_paren || T_OPEN_PARENTHESIS !== $tokens[ $open_paren ]['code'] ) {
			return;
		}

		if ( ! isset( $tokens[ $open_paren ]['parenthesis_closer'] ) ) {
			return;
		}

		$close_paren = $tokens[ $open_paren ]['parenthesis_closer'];

		// Extract the variable being checked.
		$variable = $this->extract_variable( $phpcsFile, $tokens, $open_paren + 1, $close_paren );

		if ( false === $variable ) {
			return;
		}

		// Check if the variable name contains "field" (case-insensitive).
		if ( stripos( $variable, 'field' ) === false ) {
			return;
		}

		// Find the ternary operator after the closing parenthesis.
		$ternary = $phpcsFile->findNext( T_WHITESPACE, $close_paren + 1, null, true );

		if ( false === $ternary || T_INLINE_THEN !== $tokens[ $ternary ]['code'] ) {
			return;
		}

		// Find the colon (inline else).
		$colon = $this->find_ternary_colon( $phpcsFile, $tokens, $ternary );

		if ( false === $colon ) {
			return;
		}

		// Extract the "then" expression (between ? and :).
		$then_content = $this->get_trimmed_content( $phpcsFile, $tokens, $ternary + 1, $colon );

		// Find the end of the "else" expression.
		$else_end = $this->find_else_end( $phpcsFile, $tokens, $colon );

		if ( false === $else_end ) {
			return;
		}

		// Extract the "else" expression (between : and end).
		$else_content = $this->get_trimmed_content( $phpcsFile, $tokens, $colon + 1, $else_end );

		// Determine which branch should have object access and which array access.
		if ( $is_object_check ) {
			$object_branch = $then_content;
			$array_branch  = $else_content;
		} else {
			// is_array: then = array access, else = object access.
			$object_branch = $else_content;
			$array_branch  = $then_content;
		}

		// Verify the object branch accesses ->type on the same variable.
		if ( ! $this->is_object_type_access( $object_branch, $variable ) ) {
			return;
		}

		// Verify the array branch accesses ['type'] on the same variable.
		if ( ! $this->is_array_type_access( $array_branch, $variable ) ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use FrmField::get_field_type( %s ) instead of %s( %s ) ? ... : ... for field type access.',
			$stackPtr,
			'PreferGetFieldType',
			array( $variable, $check_function, $variable )
		);

		if ( $fix ) {
			$this->apply_fix( $phpcsFile, $stackPtr, $else_end, $variable );
		}
	}

	/**
	 * Check if the current position is inside FrmField::get_field_type.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return bool
	 */
	private function is_inside_get_field_type( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		$function = $phpcsFile->findPrevious( T_FUNCTION, $stackPtr );

		if ( false === $function ) {
			return false;
		}

		$name_token = $phpcsFile->findNext( T_STRING, $function + 1, $stackPtr );

		if ( false === $name_token ) {
			return false;
		}

		return 'get_field_type' === $tokens[ $name_token ]['content'];
	}

	/**
	 * Extract a variable (including property chains like $this->field) from between tokens.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $start     Start position (exclusive of parenthesis).
	 * @param int   $end       End position (exclusive of parenthesis).
	 *
	 * @return false|string The variable string or false if not a simple variable.
	 */
	private function extract_variable( File $phpcsFile, array $tokens, $start, $end ) {
		$variable = '';

		for ( $i = $start; $i < $end; $i++ ) {
			if ( T_WHITESPACE === $tokens[ $i ]['code'] ) {
				continue;
			}

			$code = $tokens[ $i ]['code'];

			if ( T_VARIABLE !== $code && T_OBJECT_OPERATOR !== $code && T_STRING !== $code ) {
				return false;
			}

			$variable .= $tokens[ $i ]['content'];
		}

		if ( '' === $variable ) {
			return false;
		}

		return $variable;
	}

	/**
	 * Find the colon (inline else) that matches the ternary operator.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $ternary   The position of the T_INLINE_THEN token.
	 *
	 * @return false|int
	 */
	private function find_ternary_colon( File $phpcsFile, array $tokens, $ternary ) {
		$depth = 1;

		for ( $i = $ternary + 1; $i < $phpcsFile->numTokens; $i++ ) {
			if ( T_INLINE_THEN === $tokens[ $i ]['code'] ) {
				++$depth;
			} elseif ( T_INLINE_ELSE === $tokens[ $i ]['code'] ) {
				--$depth;

				if ( 0 === $depth ) {
					return $i;
				}
			}
		}

		return false;
	}

	/**
	 * Find the end of the else expression in the ternary.
	 *
	 * Stops at a semicolon, comma, closing parenthesis, or closing bracket
	 * that isn't part of the else expression itself.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $colon     The position of the T_INLINE_ELSE token.
	 *
	 * @return false|int The position of the token that ends the else expression.
	 */
	private function find_else_end( File $phpcsFile, array $tokens, $colon ) {
		$depth = 0;

		for ( $i = $colon + 1; $i < $phpcsFile->numTokens; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( T_OPEN_PARENTHESIS === $code || T_OPEN_SQUARE_BRACKET === $code ) {
				++$depth;
			} elseif ( T_CLOSE_PARENTHESIS === $code || T_CLOSE_SQUARE_BRACKET === $code ) {
				if ( 0 === $depth ) {
					return $i;
				}
				--$depth;
			} elseif ( T_SEMICOLON === $code || T_COMMA === $code ) {
				if ( 0 === $depth ) {
					return $i;
				}
			}
		}

		return false;
	}

	/**
	 * Get trimmed content between two token positions.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $tokens    The token stack.
	 * @param int   $start     Start position.
	 * @param int   $end       End position (exclusive).
	 *
	 * @return string
	 */
	private function get_trimmed_content( File $phpcsFile, array $tokens, $start, $end ) {
		$content = '';

		for ( $i = $start; $i < $end; $i++ ) {
			$content .= $tokens[ $i ]['content'];
		}

		return trim( $content );
	}

	/**
	 * Check if an expression is a $variable->type access.
	 *
	 * @param string $expression The expression to check.
	 * @param string $variable   The variable name.
	 *
	 * @return bool
	 */
	private function is_object_type_access( $expression, $variable ) {
		$normalized = preg_replace( '/\s+/', '', $expression );
		$expected   = preg_replace( '/\s+/', '', $variable ) . '->type';

		return $normalized === $expected;
	}

	/**
	 * Check if an expression is a $variable['type'] access.
	 *
	 * @param string $expression The expression to check.
	 * @param string $variable   The variable name.
	 *
	 * @return bool
	 */
	private function is_array_type_access( $expression, $variable ) {
		$normalized = preg_replace( '/\s+/', '', $expression );
		$expected   = preg_replace( '/\s+/', '', $variable ) . '[\'type\']';

		if ( $normalized === $expected ) {
			return true;
		}

		// Also check double quotes.
		$expected_double = preg_replace( '/\s+/', '', $variable ) . '["type"]';

		return $normalized === $expected_double;
	}

	/**
	 * Apply the fix by replacing the ternary with FrmField::get_field_type().
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $start     Start of the is_object/is_array token.
	 * @param int    $end       End of the else expression (the semicolon/comma/paren position).
	 * @param string $variable  The variable being checked.
	 *
	 * @return void
	 */
	private function apply_fix( File $phpcsFile, $start, $end, $variable ) {
		$phpcsFile->fixer->beginChangeset();

		$replacement = 'FrmField::get_field_type( ' . $variable . ' )';

		for ( $i = $start; $i < $end; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		$phpcsFile->fixer->replaceToken( $start, $replacement );

		$phpcsFile->fixer->endChangeset();
	}
}
