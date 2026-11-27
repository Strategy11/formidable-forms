<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Combines the per style CSS blocks in the generated stylesheet.
 *
 * Every style is printed from the same template, so most of its rules only differ from
 * another style's rules by the style class. Rules with identical declarations are printed
 * once with :is() listing the style classes, which keeps the specificity of a single class.
 * Each style keeps the original order of its own rules, so the cascade inside a style does
 * not change. Rules that are not scoped to a style keep their original order, and only the
 * last copy of a rule that is printed more than once is kept. When a style's CSS has a rule
 * for another style's class, none of that other style's rules are combined, so they keep
 * their order with that rule.
 *
 * @since x.x
 */
class FrmStyleCssMerger {

	/**
	 * Stands in for the style class while rules from different styles are compared.
	 *
	 * @var string
	 */
	const PLACEHOLDER = "\x00";

	/**
	 * Separates an at-rule prelude from the marker that keeps the block apart from other blocks.
	 *
	 * @var string
	 */
	const BLOCK_MARKER = "\x06";

	/**
	 * The CSS printed for each style, in output order.
	 *
	 * @var array<array{class: string, css: string}>
	 */
	private $blocks = array();

	/**
	 * @since x.x
	 *
	 * @param string $style_class The style class without the leading dot, like frm_style_formidable-style.
	 * @param string $css         The CSS printed for this style.
	 *
	 * @return void
	 */
	public function add_style( $style_class, $css ) {
		$this->blocks[] = array(
			'class' => $style_class,
			'css'   => $css,
		);
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_css() {
		$original = implode( "\n", wp_list_pluck( $this->blocks, 'css' ) );

		/**
		 * Allows the per style rules in the generated stylesheet to be printed unmerged.
		 *
		 * @since x.x
		 *
		 * @param bool $merge True to combine rules with identical declarations.
		 */
		if ( count( $this->blocks ) < 2 || ! apply_filters( 'frm_merge_style_css', true ) ) {
			return $original;
		}

		$parsed   = array();
		$isolated = array();
		$classes  = wp_list_pluck( $this->blocks, 'class' );
		$pattern  = '/\.(' . implode( '|', array_map( array( self::class, 'quote_class' ), $classes ) ) . ')(?![\w-])/';

		foreach ( $this->blocks as $index => $block ) {
			$rules = self::parse_rules( $block['css'], $index );

			if ( false === $rules ) {
				return $original;
			}

			foreach ( $rules as $rule_index => $rule ) {
				preg_match_all( $pattern, $rule['selector'], $matches );
				$foreign = array_diff( $matches[1], array( $block['class'] ) );

				if ( ! $foreign ) {
					continue;
				}

				// A rule for another style has to keep its order with that style's rules.
				$rules[ $rule_index ]['mergeable'] = false;
				$isolated                         += array_fill_keys( $foreign, true );
			}

			$parsed[ $index ] = $rules;
		}//end foreach

		$sequences = array();

		foreach ( $this->blocks as $index => $block ) {
			$sequences[] = self::key_rules( $parsed[ $index ], $block['class'], $index, ! isset( $isolated[ $block['class'] ] ) );
		}

		$order = self::merge_order( $sequences );

		return self::build_css( $order, $sequences );
	}

	/**
	 * @since x.x
	 *
	 * @param string $style_class
	 *
	 * @return string
	 */
	private static function quote_class( $style_class ) {
		return preg_quote( $style_class, '/' );
	}

	/**
	 * Splits CSS into rules, each with the at-rule blocks it is nested in.
	 *
	 * @since x.x
	 *
	 * @param string $css
	 * @param int    $block_index The position of the style in the output.
	 *
	 * @return array|false False when the CSS uses syntax this parser does not handle.
	 */
	private static function parse_rules( $css, $block_index ) {
		$css = preg_replace( '/\/\*.*?\*\//s', '', $css );

		// A brace inside a quoted string would be read as a block boundary.
		preg_match_all( '/"[^"]*"|\'[^\']*\'/', $css, $strings );

		foreach ( $strings[0] as $string ) {
			if ( strpbrk( $string, '{}' ) ) {
				return false;
			}
		}

		$rules     = array();
		$context   = array();
		$offset    = 0;
		$at_blocks = 0;

		while ( true ) {
			$close = strpos( $css, '}', $offset );

			if ( false === $close ) {
				break;
			}

			$open = strpos( $css, '{', $offset );

			if ( false === $open || $open > $close ) {
				// The end of an at-rule block.
				if ( ! $context || '' !== trim( substr( $css, $offset, $close - $offset ) ) ) {
					return false;
				}

				array_pop( $context );
				$offset = $close + 1;
				continue;
			}

			$prelude = self::clean_whitespace( substr( $css, $offset, $open - $offset ) );

			if ( '' === $prelude || str_contains( $prelude, ';' ) ) {
				return false;
			}

			$next_open = strpos( $css, '{', $open + 1 );

			if ( '@' === $prelude[0] && false !== $next_open && $next_open < $close ) {
				// An at-rule like @media that holds rules.
				if ( preg_match( '/^@(-[a-z]+-)?keyframes\b/i', $prelude ) ) {
					// A later @keyframes block with the same name replaces an earlier one instead of
					// adding to it, so each block is kept apart from other blocks with the same prelude.
					$prelude .= self::BLOCK_MARKER . $block_index . '-' . $at_blocks;
				}

				$context[] = $prelude;
				$offset    = $open + 1;
				++$at_blocks;
				continue;
			}

			$rules[] = array(
				'context'   => $context,
				'selector'  => $prelude,
				'body'      => self::clean_whitespace( substr( $css, $open + 1, $close - $open - 1 ) ),
				'mergeable' => true,
			);
			$offset  = $close + 1;
		}//end while

		if ( $context || '' !== trim( substr( $css, $offset ) ) ) {
			return false;
		}

		return $rules;
	}

	/**
	 * Removes the same whitespace FrmStyle::get_css_content() removes from the saved file.
	 *
	 * @since x.x
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private static function clean_whitespace( $text ) {
		return trim( str_replace( array( "\r\n", "\r", "\n", "\t", '    ' ), '', $text ) );
	}

	/**
	 * Gives each rule a key that matches the same template rule in other styles.
	 *
	 * @since x.x
	 *
	 * @param array  $rules
	 * @param string $style_class
	 * @param int    $block_index The position of the style in the output.
	 * @param bool   $mergeable   False when another style's CSS has rules for this style, so its rules keep their order with those.
	 *
	 * @return array<string, mixed> Rules keyed in their original order.
	 */
	private static function key_rules( $rules, $style_class, $block_index, $mergeable ) {
		$pattern     = '/\.' . preg_quote( $style_class, '/' ) . '(?![\w-])/';
		$keyed       = array();
		$occurrences = array();

		foreach ( $rules as $rule ) {
			$selector = preg_replace( $pattern, self::PLACEHOLDER, $rule['selector'] );
			$scoped   = $mergeable && $rule['mergeable'] && str_contains( $selector, self::PLACEHOLDER );
			$key      = implode( "\x01", $rule['context'] ) . "\x02" . $selector;

			if ( ! $scoped ) {
				// Only rules scoped to the style are combined with other styles.
				$key .= "\x04" . $block_index;
			}

			$occurrences[ $key ] = isset( $occurrences[ $key ] ) ? $occurrences[ $key ] + 1 : 0;
			$key                .= "\x03" . $occurrences[ $key ];

			$rule['selector'] = $selector;
			$rule['class']    = $style_class;
			$rule['scoped']   = $scoped;
			$keyed[ $key ]    = $rule;
		}

		return $keyed;
	}

	/**
	 * Builds one rule order that keeps the original order of every style's rules.
	 *
	 * Each style is lined up with the order built from the styles before it. A rule that
	 * would have to move to share a position is renamed so it stays with its own style.
	 * Rules that are not scoped to a style also keep their order across styles.
	 *
	 * @since x.x
	 *
	 * @param array<int, array<string, array>> $sequences Rules keyed by rule key, in order. Renamed keys are updated.
	 *
	 * @return array<string>
	 */
	private static function merge_order( &$sequences ) {
		/**
		 * @var array<int, string> $order
		 */
		$order         = array();
		$last_unscoped = null;

		foreach ( $sequences as $index => $rules ) {
			$positions = array_flip( $order );
			$shared    = array();

			foreach ( $rules as $key => $rule ) {
				if ( $rule['scoped'] && isset( $positions[ $key ] ) ) {
					$shared[ $key ] = $positions[ $key ];
				}
			}

			$keep    = self::longest_increasing( $shared );
			$cursor  = -1;
			$renamed = array();

			foreach ( $rules as $key => $rule ) {
				if ( isset( $keep[ $key ] ) && $positions[ $key ] > $cursor ) {
					$cursor          = $positions[ $key ];
					$renamed[ $key ] = $rule;
					continue;
				}

				if ( isset( $positions[ $key ] ) ) {
					$key .= "\x05" . $index;
				}

				if ( ! $rule['scoped'] && null !== $last_unscoped ) {
					$cursor = max( $cursor, $positions[ $last_unscoped ] );
				}

				array_splice( $order, $cursor + 1, 0, array( $key ) );
				$positions = array_flip( $order );
				++$cursor;
				$renamed[ $key ] = $rule;

				if ( ! $rule['scoped'] ) {
					$last_unscoped = $key;
				}
			}//end foreach

			$sequences[ $index ] = $renamed;
		}//end foreach

		return $order;
	}

	/**
	 * Finds the longest run of keys whose positions go up, in their given order.
	 *
	 * @since x.x
	 *
	 * @param array<string, int> $positions Positions keyed by rule key.
	 *
	 * @return array<string, true>
	 */
	private static function longest_increasing( $positions ) {
		$keys     = array_keys( $positions );
		$values   = array_values( $positions );
		$tails    = array();
		$previous = array();

		foreach ( $values as $i => $value ) {
			$low  = 0;
			$high = count( $tails );

			while ( $low < $high ) {
				$middle = intdiv( $low + $high, 2 );

				if ( $values[ $tails[ $middle ] ] < $value ) {
					$low = $middle + 1;
				} else {
					$high = $middle;
				}
			}

			$previous[ $i ] = $low > 0 ? $tails[ $low - 1 ] : -1;
			$tails[ $low ]  = $i;
		}

		$keep = array();
		$i    = $tails ? end( $tails ) : -1;

		while ( $i >= 0 ) {
			$keep[ $keys[ $i ] ] = true;
			$i                   = $previous[ $i ];
		}

		return $keep;
	}

	/**
	 * @since x.x
	 *
	 * @param array<string>                    $order
	 * @param array<int, array<string, array>> $sequences
	 *
	 * @return string
	 */
	private static function build_css( $order, $sequences ) {
		$entries = array();

		foreach ( $order as $key ) {
			$groups = array();

			foreach ( $sequences as $rules ) {
				if ( ! isset( $rules[ $key ] ) ) {
					continue;
				}

				$rule = $rules[ $key ];

				if ( ! isset( $groups[ $rule['body'] ] ) ) {
					$groups[ $rule['body'] ]            = $rule;
					$groups[ $rule['body'] ]['classes'] = array();
				}

				$groups[ $rule['body'] ]['classes'][] = '.' . $rule['class'];
			}

			foreach ( $groups as $body => $group ) {
				$selector  = str_replace( self::PLACEHOLDER, self::class_selector( $group['classes'] ), $group['selector'] );
				$entries[] = array(
					'context' => $group['context'],
					'rule'    => $selector . '{' . $body . '}',
				);
			}
		}//end foreach

		return self::entries_to_css( $entries );
	}

	/**
	 * Prints the rules, keeping only the last copy of a rule that is printed more than once.
	 * The last copy sets the same values after anything in between, so the earlier ones have no effect.
	 *
	 * @since x.x
	 *
	 * @param array<array{context: array<string>, rule: string}> $entries
	 *
	 * @return string
	 */
	private static function entries_to_css( $entries ) {
		$last = array();

		foreach ( $entries as $index => $entry ) {
			$last[ implode( "\x01", $entry['context'] ) . "\x02" . $entry['rule'] ] = $index;
		}

		$css     = '';
		$context = array();

		foreach ( $entries as $index => $entry ) {
			if ( $last[ implode( "\x01", $entry['context'] ) . "\x02" . $entry['rule'] ] !== $index ) {
				continue;
			}

			$css    .= self::switch_context( $context, $entry['context'] );
			$context = $entry['context'];
			$css    .= $entry['rule'] . "\n";
		}

		return $css . self::switch_context( $context, array() );
	}

	/**
	 * Matches any of the style classes. :is() takes the specificity of a single class,
	 * so the merged rule keeps the specificity of each original rule.
	 *
	 * @since x.x
	 *
	 * @param array<string> $classes Style classes with the leading dot.
	 *
	 * @return string
	 */
	private static function class_selector( $classes ) {
		if ( 1 === count( $classes ) ) {
			return reset( $classes );
		}

		return ':is(' . implode( ',', $classes ) . ')';
	}

	/**
	 * Closes and opens at-rule blocks to move from one rule's nesting to the next one's.
	 *
	 * @since x.x
	 *
	 * @param array<string> $from
	 * @param array<string> $to
	 *
	 * @return string
	 */
	private static function switch_context( $from, $to ) {
		$shared = 0;

		while ( isset( $from[ $shared ], $to[ $shared ] ) && $from[ $shared ] === $to[ $shared ] ) {
			++$shared;
		}

		$css = str_repeat( "}\n", count( $from ) - $shared );

		foreach ( array_slice( $to, $shared ) as $prelude ) {
			$css .= explode( self::BLOCK_MARKER, $prelude )[0] . '{';
		}

		return $css;
	}
}
