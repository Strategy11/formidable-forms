<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCssScopeHelper {

	/**
	 * Nest the CSS.
	 * This function nests the CSS by adding the class name prefix to the selectors.
	 *
	 * @param string $css
	 * @param string $class_name
	 *
	 * @return string
	 */
	public function nest( $css, $class_name ) {
		// Remove CSS comments but preserve newlines
		$css = preg_replace( '/\/\*.*?\*\//s', '', $css );

		$output = array();
		$css    = trim( $css );
		$length = strlen( $css );
		$i      = 0;
		$buffer = '';

		while ( $i < $length ) {
			$char = $css[ $i ];

			if ( '@' === $char ) {
				$brace_pos = strpos( $css, '{', $i );

				if ( false === $brace_pos ) {
					$buffer .= $char;
					++$i;

					continue;
				}

				$rule          = substr( $css, $i, $brace_pos - $i );
				$closing_brace = $this->find_matching_brace( $css, $brace_pos );
				$inner_content = substr( $css, $brace_pos + 1, $closing_brace - $brace_pos - 1 );

				// Don't nest keyframes content
				if ( str_contains( $rule, '@keyframes' ) ) {
					$output[] = "\n" . $rule . ' {' . $inner_content . '}' . "\n";
				} else {
					$output[] = "\n" . $rule . ' {';
					$output[] = $this->nest( $inner_content, $class_name );
					$output[] = '}' . "\n";
				}

				$i      = $closing_brace + 1;
				$buffer = '';

				continue;
			}//end if

			if ( '{' === $char ) {
				$selector      = trim( $buffer );
				$closing_brace = $this->find_matching_brace( $css, $i );
				$declarations  = substr( $css, $i + 1, $closing_brace - $i - 1 );

				// Preserve indentation and formatting of declarations
				$declarations = $this->preserve_declaration_formatting( $declarations );

				if ( '' !== $selector && '' !== trim( $declarations ) ) {
					$prefixed_selectors = $this->prefix_selectors( $selector, $class_name );

					if ( $prefixed_selectors ) {
						$output[] = "\n" . implode( ',' . "\n", $prefixed_selectors ) . ' {' . $declarations . '}' . "\n";
					}
				}

				$i      = $closing_brace + 1;
				$buffer = '';

				continue;
			}//end if

			$buffer .= $char;
			++$i;
		}//end while

		return implode( '', $output );
	}

	/**
	 * Unnest the CSS.
	 * This function unnests the CSS by removing the class name prefix from the selectors.
	 *
	 * @param string $css
	 * @param string $class_name
	 *
	 * @return string
	 */
	public function unnest( $css, $class_name ) {
		// Remove CSS comments but preserve newlines
		$css = preg_replace( '/\/\*.*?\*\//s', '', $css );

		$output        = array();
		$css           = trim( $css );
		$length        = strlen( $css );
		$i             = 0;
		$buffer        = '';
		$prefix        = '.' . $class_name . ' ';
		$prefix_length = strlen( $prefix );

		while ( $i < $length ) {
			$char = $css[ $i ];

			if ( '@' === $char ) {
				$brace_pos = strpos( $css, '{', $i );

				if ( false === $brace_pos ) {
					$buffer .= $char;
					++$i;

					continue;
				}

				$rule          = substr( $css, $i, $brace_pos - $i );
				$closing_brace = $this->find_matching_brace( $css, $brace_pos );
				$inner_content = substr( $css, $brace_pos + 1, $closing_brace - $brace_pos - 1 );

				$output[] = "\n" . $rule . ' {';
				$output[] = $this->unnest( $inner_content, $class_name );
				$output[] = '}' . "\n";

				$i      = $closing_brace + 1;
				$buffer = '';

				continue;
			}//end if

			if ( '{' === $char ) {
				$selector      = trim( $buffer );
				$closing_brace = $this->find_matching_brace( $css, $i );
				$declarations  = substr( $css, $i + 1, $closing_brace - $i - 1 );

				// Preserve indentation and formatting of declarations
				$declarations = $this->preserve_declaration_formatting( $declarations );

				if ( '' !== $selector && '' !== trim( $declarations ) ) {
					$unprefixed_selectors = $this->unprefix_selectors( $selector, $class_name, $prefix, $prefix_length );

					if ( $unprefixed_selectors ) {
						$output[] = "\n" . implode( ',' . "\n", $unprefixed_selectors ) . ' {' . $declarations . '}' . "\n";
					}
				}

				$i      = $closing_brace + 1;
				$buffer = '';

				continue;
			}//end if

			$buffer .= $char;
			++$i;
		}//end while

		return implode( '', $output );
	}

	/**
	 * Preserve declaration formatting with proper indentation.
	 *
	 * @param string $declarations
	 *
	 * @return string
	 */
	private function preserve_declaration_formatting( $declarations ) {
		// Trim the entire block but keep internal structure
		$declarations = trim( $declarations );

		if ( '' === $declarations ) {
			return '';
		}

		// Check if declarations are already on multiple lines
		if ( str_contains( $declarations, "\n" ) ) {
			// Already formatted - preserve it
			$lines           = explode( "\n", $declarations );
			$formatted_lines = array();

			foreach ( $lines as $line ) {
				$trimmed = trim( $line );

				if ( '' !== $trimmed ) {
					$formatted_lines[] = "\n\t" . $trimmed;
				}
			}

			return implode( '', $formatted_lines ) . "\n";
		}

		// Single line - add minimal formatting
		return ' ' . $declarations . ' ';
	}

	/**
	 * Build the list of prefixed selectors for a given selector string.
	 * Generates both descendant (.scope selector) and direct (selector.scope) forms.
	 *
	 * @param string $selector   The comma-separated selector string.
	 * @param string $class_name The scope class name.
	 *
	 * @return array The list of prefixed selectors.
	 */
	private function prefix_selectors( $selector, $class_name ) {
		$selectors          = array_map( 'trim', explode( ',', $selector ) );
		$prefixed_selectors = array();

		foreach ( $selectors as $single_selector ) {
			if ( '' === $single_selector ) {
				continue;
			}

			$prefixed_selectors[] = '.' . $class_name . ' ' . $single_selector;
			$direct               = $this->add_direct_scope( $single_selector, $class_name );

			if ( null !== $direct ) {
				$prefixed_selectors[] = $direct;
			}
		}

		return $prefixed_selectors;
	}

	/**
	 * Build the list of unprefixed selectors for a given selector string.
	 * Handles both descendant (.scope selector) and direct (selector.scope) forms.
	 *
	 * @param string $selector      The comma-separated selector string.
	 * @param string $class_name    The scope class name.
	 * @param string $prefix        The descendant prefix string (.class_name followed by space).
	 * @param int    $prefix_length The length of the descendant prefix.
	 *
	 * @return array The list of unprefixed selectors (deduplicated).
	 */
	private function unprefix_selectors( $selector, $class_name, $prefix, $prefix_length ) {
		$selectors            = array_filter(
			array_map( 'trim', explode( ',', $selector ) ),
			function ( $s ) {
				return '' !== $s;
			}
		);
		$unprefixed_selectors = array();

		foreach ( $selectors as $single_selector ) {
			if ( str_starts_with( $single_selector, $prefix ) ) {
				$unprefixed_selectors[] = trim( substr( $single_selector, $prefix_length ) );
			} else {
				$unprefixed_selectors[] = $this->remove_direct_scope( $single_selector, $class_name ) ?? $single_selector;
			}
		}

		return array_values( array_unique( $unprefixed_selectors ) );
	}

	/**
	 * Add the scope class directly to the first segment of a selector.
	 * For example, "h2" becomes "h2.scope" and ".foo .bar" becomes ".foo.scope .bar".
	 *
	 * @param string $selector   The CSS selector.
	 * @param string $class_name The scope class name.
	 *
	 * @return string|null The direct-scoped selector, or null if not applicable.
	 */
	private function add_direct_scope( $selector, $class_name ) {
		$parts = preg_split( '/(\s+)/', $selector, 2, PREG_SPLIT_DELIM_CAPTURE );
		$first = $parts[0];

		// Don't add direct scope to selectors starting with combinators or universal selector.
		if ( preg_match( '/^[>+~*]/', $first ) ) {
			return null;
		}

		$rest = '';

		if ( count( $parts ) > 1 ) {
			$rest = $parts[1] . $parts[2];
		}

		return $first . '.' . $class_name . $rest;
	}

	/**
	 * Remove the scope class from the first segment of a direct-scoped selector.
	 * For example, "h2.scope" becomes "h2" and ".foo.scope .bar" becomes ".foo .bar".
	 *
	 * @param string $selector   The CSS selector.
	 * @param string $class_name The scope class name.
	 *
	 * @return string|null The unscoped selector, or null if the scope was not found.
	 */
	private function remove_direct_scope( $selector, $class_name ) {
		$scope_pattern = '/\.' . preg_quote( $class_name, '/' ) . '(?![a-zA-Z0-9_-])/';
		$parts         = preg_split( '/(\s+)/', $selector, 2, PREG_SPLIT_DELIM_CAPTURE );

		if ( false === $parts || ! preg_match( $scope_pattern, $parts[0] ) ) {
			return null;
		}

		$parts[0] = preg_replace( $scope_pattern, '', $parts[0], 1 );
		$result   = trim( implode( '', $parts ) );

		return '' !== $result ? $result : null;
	}

	/**
	 * Find the matching brace in the CSS.
	 *
	 * @param string $css
	 * @param int    $open_pos
	 *
	 * @return int
	 */
	private function find_matching_brace( $css, $open_pos ) {
		$level       = 1;
		$length      = strlen( $css );
		$in_string   = false;
		$string_char = '';

		for ( $i = $open_pos + 1; $i < $length; $i++ ) {
			$char = $css[ $i ];

			// Handle string literals to avoid matching braces inside strings
			if ( ( '"' === $char || "'" === $char ) && ( 0 === $i || '\\' !== $css[ $i - 1 ] ) ) {
				if ( ! $in_string ) {
					$in_string   = true;
					$string_char = $char;
				} elseif ( $char === $string_char ) {
					$in_string = false;
				}

				continue;
			}

			// Skip braces inside strings
			if ( $in_string ) {
				continue;
			}

			if ( '{' === $char ) {
				++$level;
			} elseif ( '}' === $char ) {
				--$level;

				if ( 0 === $level ) {
					return $i;
				}
			}
		}//end for

		return $length - 1;
	}
}//end class
