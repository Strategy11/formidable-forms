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
				if ( strpos( $rule, '@keyframes' ) !== false ) {
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
					// Handle multiple selectors
					$selectors          = array_map( 'trim', explode( ',', $selector ) );
					$prefixed_selectors = array();

					foreach ( $selectors as $single_selector ) {
						if ( '' !== $single_selector ) {
							$prefixed_selectors[] = '.' . $class_name . ' ' . $single_selector;
						}
					}

					if ( ! empty( $prefixed_selectors ) ) {
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
			}

			if ( '{' === $char ) {
				$selector      = trim( $buffer );
				$closing_brace = $this->find_matching_brace( $css, $i );
				$declarations  = substr( $css, $i + 1, $closing_brace - $i - 1 );

				// Preserve indentation and formatting of declarations
				$declarations = $this->preserve_declaration_formatting( $declarations );

				if ( '' !== $selector && '' !== trim( $declarations ) ) {
					// Handle multiple selectors
					$selectors            = array_filter(
						array_map( 'trim', explode( ',', $selector ) ),
						function ( $s ) {
							return '' !== $s;
						}
					);
					$unprefixed_selectors = array();

					foreach ( $selectors as $single_selector ) {
						$unprefixed_selectors[] = 0 === strpos( $single_selector, $prefix )
							? trim( substr( $single_selector, $prefix_length ) )
							: $single_selector;
					}

					if ( ! empty( $unprefixed_selectors ) ) {
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
		if ( strpos( $declarations, "\n" ) !== false ) {
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
