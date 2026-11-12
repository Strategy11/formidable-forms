<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Collects the active theme's front end CSS and rewrites it so an admin preview area can be
 * styled like the front end, without the theme leaking into the rest of wp-admin.
 *
 * The rewritten CSS is wrapped in an @scope block. Browsers without @scope support ignore
 * the whole block, so the preview simply looks the way it did before this was added.
 *
 * @since x.x
 */
class FrmStylesThemeCssHelper {

	/**
	 * At rules that either mean nothing inside @scope or need to stay global, so they get
	 * moved above the @scope block instead of being wrapped by it.
	 *
	 * @var array
	 */
	private static $hoisted_at_rules = array( 'font-face', 'keyframes', '-webkit-keyframes', '-moz-keyframes', '-o-keyframes', 'property', 'counter-style', 'font-feature-values' );

	/**
	 * At rules that group style rules, so their contents get rewritten recursively.
	 *
	 * @var array
	 */
	private static $group_at_rules = array( 'media', 'supports', 'layer', 'container', 'document' );

	/**
	 * The custom property that stands in for the theme's root font size.
	 */
	const REM_PROPERTY = '--frm-preview-rem';

	/**
	 * The font size a browser starts from before any CSS is applied.
	 */
	const DEFAULT_ROOT_FONT_SIZE = 16;

	/**
	 * Build the scoped stylesheet for the current theme.
	 *
	 * @since x.x
	 *
	 * @param string $scope_root  Selector list for the elements the theme CSS applies inside.
	 * @param string $scope_limit Optional selector list for admin UI inside the scope root that must stay unstyled by the theme.
	 *
	 * @return string Empty when the theme exposes no readable CSS.
	 */
	public static function get_scoped_theme_css( $scope_root, $scope_limit = '' ) {
		$css = '';

		foreach ( self::get_theme_stylesheets() as $stylesheet ) {
			$contents = self::read_file( $stylesheet['path'] );

			if ( '' === $contents ) {
				continue;
			}

			$contents = self::absolutize_urls( $contents, $stylesheet['base_url'] );
			$media    = isset( $stylesheet['media'] ) ? trim( $stylesheet['media'] ) : 'all';

			// A stylesheet registered for print must not start applying on screen.
			if ( '' !== $media && 'all' !== $media ) {
				$contents = '@media ' . $media . ' {' . "\n" . $contents . "\n" . '}';
			}

			$css .= $contents . "\n";
		}

		$css .= self::get_global_stylesheet();

		return '' === trim( $css ) ? '' : self::scope_css( $css, $scope_root, $scope_limit );
	}

	/**
	 * Identify the current theme CSS, so a theme update or a Customizer or Global Styles edit
	 * changes both a cached copy and a stylesheet URL's version.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function get_version() {
		$theme         = wp_get_theme();
		$global_styles = self::get_global_stylesheet();

		return FrmAppHelper::plugin_version() . '-' . $theme->get_stylesheet() . '-' . $theme->get( 'Version' ) . '-' . substr( md5( $global_styles ), 0, 8 );
	}

	/**
	 * Theme.json derived CSS, which is where a block theme keeps most of its presets.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_global_stylesheet() {
		if ( ! function_exists( 'wp_get_global_stylesheet' ) ) {
			return '';
		}

		return (string) wp_get_global_stylesheet();
	}

	/**
	 * List the theme's front end stylesheets, in dependency order where possible.
	 *
	 * The theme is asked to enqueue itself the way it would on the front end, and only the
	 * handles that resolve to a file inside the theme directory are kept. Plugin CSS is left
	 * out on purpose, since Formidable's own front end CSS is already on the admin page.
	 *
	 * @since x.x
	 *
	 * @return array List of arrays with 'path' and 'base_url' keys.
	 */
	private static function get_theme_stylesheets() {
		$stylesheets = self::get_enqueued_theme_stylesheets();

		if ( ! $stylesheets ) {
			$stylesheets = self::get_default_theme_stylesheets();
		}

		/**
		 * Filter the theme stylesheets pulled into an admin preview, such as the Visual Styler.
		 *
		 * @since x.x
		 *
		 * @param array $stylesheets List of arrays with 'path' and 'base_url' keys.
		 */
		return apply_filters( 'frm_preview_theme_stylesheets', $stylesheets );
	}

	/**
	 * Run the theme's front end enqueue in a sandbox and collect what it registered.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_enqueued_theme_stylesheets() {
		global $wp_styles, $wp_scripts;

		$previous_styles  = $wp_styles;
		$previous_scripts = $wp_scripts;

		// Swapped so the theme registers into a throwaway queue instead of the real one, and put
		// back below. Without this the front end handles would follow the request into wp-admin.
		$wp_styles  = new WP_Styles(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_scripts = new WP_Scripts(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Buffered so a callback that echoes cannot land ahead of the CSS response headers.
		ob_start();

		try {
			do_action( 'wp_enqueue_scripts' );
			$handles = self::resolve_style_dependencies( $wp_styles );
			$found   = self::filter_theme_styles( $wp_styles, $handles );
		} catch ( Exception $e ) {
			// A theme or plugin can fail when its front end enqueue runs outside a front end
			// request. Fall back to the theme stylesheet rather than breaking the preview.
			$found = array();
		} catch ( Error $e ) {
			$found = array();
		}

		ob_end_clean();

		$wp_styles  = $previous_styles; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_scripts = $previous_scripts; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return $found;
	}

	/**
	 * Expand the queue into a flat, dependency ordered list of handles.
	 *
	 * @since x.x
	 *
	 * @param WP_Styles $styles The sandboxed styles instance.
	 *
	 * @return array
	 */
	private static function resolve_style_dependencies( $styles ) {
		if ( ! $styles->queue ) {
			return array();
		}

		$styles->all_deps( $styles->queue );

		return $styles->to_do;
	}

	/**
	 * Keep only the handles whose source file lives inside the active theme.
	 *
	 * @since x.x
	 *
	 * @param WP_Styles $styles  The sandboxed styles instance.
	 * @param array     $handles Dependency ordered style handles.
	 *
	 * @return array
	 */
	private static function filter_theme_styles( $styles, $handles ) {
		$roots = self::get_theme_roots();
		$found = array();

		foreach ( $handles as $handle ) {
			if ( ! is_string( $handle ) || ! isset( $styles->registered[ $handle ] ) ) {
				continue;
			}

			$style = $styles->registered[ $handle ];

			if ( ! self::is_non_empty_string( $style->src ) ) {
				continue;
			}

			$src = self::normalize_src( $style->src );

			// WP_Styles keeps the media attribute in the dependency's args property.
			$media = self::is_non_empty_string( $style->args ) ? $style->args : 'all';

			foreach ( $roots as $root ) {
				if ( ! str_starts_with( $src, $root['url'] ) ) {
					continue;
				}

				$relative = substr( $src, strlen( $root['url'] ) );

				$found[] = array(
					'path'     => $root['path'] . $relative,
					'base_url' => dirname( $root['url'] . $relative ) . '/',
					'media'    => $media,
				);
				break;
			}
		}//end foreach

		return $found;
	}

	/**
	 * Whether a value is a string with something in it.
	 *
	 * @since x.x
	 *
	 * @param mixed $value Any value read off a registered dependency.
	 *
	 * @return bool
	 */
	private static function is_non_empty_string( $value ) {
		return is_string( $value ) && '' !== $value;
	}

	/**
	 * The parent and child theme directories, longest URL first so a child theme inside the
	 * parent directory is matched before the parent.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_theme_roots() {
		$roots = array(
			array(
				'url'  => self::normalize_src( get_stylesheet_directory_uri() ),
				'path' => get_stylesheet_directory(),
			),
			array(
				'url'  => self::normalize_src( get_template_directory_uri() ),
				'path' => get_template_directory(),
			),
		);

		usort( $roots, 'FrmStylesThemeCssHelper::compare_root_url_length' );

		return $roots;
	}

	/**
	 * Sort theme roots so the longest URL is tested first.
	 *
	 * @since x.x
	 *
	 * @param array $a First theme root.
	 * @param array $b Second theme root.
	 *
	 * @return int
	 */
	public static function compare_root_url_length( $a, $b ) {
		return strlen( $b['url'] ) - strlen( $a['url'] );
	}

	/**
	 * Drop the query string and the protocol so sources and theme roots compare cleanly.
	 *
	 * @since x.x
	 *
	 * @param string $src A stylesheet URL.
	 *
	 * @return string
	 */
	private static function normalize_src( $src ) {
		$src = strtok( $src, '?' );
		return preg_replace( '#^https?://#', '//', $src );
	}

	/**
	 * The stylesheets to read when the sandboxed enqueue produced nothing.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_default_theme_stylesheets() {
		$stylesheets = array();
		$directories = array(
			get_template_directory()   => get_template_directory_uri(),
			get_stylesheet_directory() => get_stylesheet_directory_uri(),
		);

		foreach ( $directories as $directory => $url ) {
			$path = $directory . '/style.css';

			if ( in_array( $path, wp_list_pluck( $stylesheets, 'path' ), true ) ) {
				continue;
			}

			$stylesheets[] = array(
				'path'     => $path,
				'base_url' => trailingslashit( $url ),
				'media'    => 'all',
			);
		}

		return $stylesheets;
	}

	/**
	 * Read a stylesheet from the filesystem.
	 *
	 * @since x.x
	 *
	 * @param string $path Absolute path to a CSS file.
	 *
	 * @return string Empty when the file is missing or unreadable.
	 */
	private static function read_file( $path ) {
		if ( ! $path || ! is_readable( $path ) ) {
			return '';
		}

		global $wp_filesystem;

		if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			if ( ! WP_Filesystem() ) {
				return '';
			}
		}

		$contents = $wp_filesystem->get_contents( $path );

		return is_string( $contents ) ? $contents : '';
	}

	/**
	 * Turn relative url() references into absolute ones, since the stylesheet is served from
	 * admin-ajax.php rather than from the theme directory.
	 *
	 * @since x.x
	 *
	 * @param string $css      The stylesheet contents.
	 * @param string $base_url The URL of the directory the stylesheet was read from.
	 *
	 * @return string
	 */
	private static function absolutize_urls( $css, $base_url ) {
		$callback = function ( $matches ) use ( $base_url ) {
			return 'url(' . FrmStylesThemeCssHelper::absolutize_url( $matches[1], $base_url ) . ')';
		};

		return preg_replace_callback( '/url\(\s*([^)]+?)\s*\)/i', $callback, $css );
	}

	/**
	 * Resolve a single url() value against the directory its stylesheet came from.
	 *
	 * @since x.x
	 *
	 * @param string $url      The raw url() value, quotes included.
	 * @param string $base_url The URL of the directory the stylesheet was read from.
	 *
	 * @return string
	 */
	public static function absolutize_url( $url, $base_url ) {
		$quote = '';
		$value = trim( $url );

		if ( '' !== $value && ( '"' === $value[0] || "'" === $value[0] ) ) {
			$quote = $value[0];
			$value = trim( $value, $quote );
		}

		if ( '' === $value || 'data:' === substr( $value, 0, 5 ) || '#' === $value[0] || '/' === $value[0] ) {
			return $quote . $value . $quote;
		}

		if ( preg_match( '#^https?://#i', $value ) ) {
			return $quote . $value . $quote;
		}

		return $quote . $base_url . $value . $quote;
	}

	/**
	 * Wrap a stylesheet in @scope, hoisting the at rules that cannot live inside it and
	 * retargeting the selectors that point at the document root.
	 *
	 * @since x.x
	 *
	 * @param string $css         The combined theme CSS.
	 * @param string $scope_root  Selector list for the elements the theme CSS applies inside.
	 * @param string $scope_limit Selector list for admin UI inside the scope that stays unstyled by the theme.
	 *
	 * @return string
	 */
	private static function scope_css( $css, $scope_root, $scope_limit = '' ) {
		$hoisted = '';
		$scoped  = self::rewrite_rules( $css, $hoisted );

		if ( '' === trim( $scoped ) ) {
			return $hoisted;
		}

		$scoped = self::get_root_reset() . "\n" . $scoped;
		$limit  = '' === $scope_limit ? '' : ' to (' . $scope_limit . ')';

		return $hoisted . '@scope (' . $scope_root . ')' . $limit . ' {' . "\n" . $scoped . "\n" . '}' . "\n";
	}

	/**
	 * Start the scope root from the values a front end page body starts from.
	 *
	 * The preview otherwise inherits wp-admin's 13px system font and grey text, so any theme
	 * that leaves a property to the browser default would look different in the preview. The
	 * theme's own html and body rules come after this, so they still win.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_root_reset() {
		$size = self::DEFAULT_ROOT_FONT_SIZE . 'px';

		return ':scope {' . self::REM_PROPERTY . ': ' . $size . ';font-family: initial;font-size: ' . $size . ';font-weight: 400;line-height: normal;'
		. 'color: CanvasText;letter-spacing: normal;-webkit-font-smoothing: auto;}';
	}

	/**
	 * Rewrite rem lengths in declarations so they resolve against the theme's root font size.
	 *
	 * A rem is always measured from the document root, and the document here is wp-admin, so a
	 * theme that shrinks html to 62.5% would otherwise render everything in the preview 1.6
	 * times too large. Swapping the unit for a multiple of a custom property pins each length
	 * to the size the theme intended.
	 *
	 * @since x.x
	 *
	 * @param string $declarations A declaration block.
	 *
	 * @return string
	 */
	private static function convert_rems( $declarations ) {
		if ( false === stripos( $declarations, 'rem' ) ) {
			return $declarations;
		}

		return preg_replace(
			'/(?<![\w.#-])(-?(?:\d*\.)?\d+)rem\b/i',
			'calc($1 * var(' . self::REM_PROPERTY . '))',
			$declarations
		);
	}

	/**
	 * Resolve root font sizes against the browser default and publish them in the same rule.
	 *
	 * Keeping the custom property beside font-size preserves conditional rules and importance.
	 *
	 * @since x.x
	 *
	 * @param string $declarations The declaration block of an html or :root rule.
	 *
	 * @return string
	 */
	private static function normalize_root_font_size( $declarations ) {
		return preg_replace_callback(
			'/(^|;)\s*font-size\s*:\s*((?:\d*\.)?\d+)(%|px|em|rem)\s*(!important\s*)?(?=;|$)/i',
			function ( $matches ) {
				$size = (float) $matches[2];
				$unit = strtolower( $matches[3] );

				if ( '%' === $unit ) {
					$size *= self::DEFAULT_ROOT_FONT_SIZE / 100;
				} elseif ( 'px' !== $unit ) {
					$size *= self::DEFAULT_ROOT_FONT_SIZE;
				}

				$value = $size . 'px' . ( isset( $matches[4] ) ? ' ' . trim( $matches[4] ) : '' );
				return $matches[1] . 'font-size:' . $value . ';' . self::REM_PROPERTY . ':' . $value;
			},
			$declarations
		);
	}

	/**
	 * Walk a block of CSS, hoisting what has to stay global and rewriting the rest.
	 *
	 * @since x.x
	 *
	 * @param string $css     The CSS to rewrite.
	 * @param string $hoisted Collects the at rules that must be emitted outside the @scope block.
	 *
	 * @return string
	 */
	private static function rewrite_rules( $css, &$hoisted ) {
		$output = '';

		foreach ( self::split_rules( $css ) as $chunk ) {
			if ( 'at-statement' === $chunk['type'] ) {
				// @charset and @import cannot be honoured from inside a generated stylesheet.
				continue;
			}

			if ( 'at-rule' === $chunk['type'] ) {
				$name = strtolower( $chunk['name'] );

				if ( in_array( $name, self::$hoisted_at_rules, true ) ) {
					$hoisted .= $chunk['prelude'] . '{' . $chunk['block'] . '}' . "\n";
					continue;
				}

				if ( in_array( $name, self::$group_at_rules, true ) ) {
					$output .= $chunk['prelude'] . '{' . "\n" . self::rewrite_rules( $chunk['block'], $hoisted ) . "\n" . '}' . "\n";
					continue;
				}

				$output .= $chunk['prelude'] . '{' . self::convert_rems( $chunk['block'] ) . '}' . "\n";
				continue;
			}

			if ( self::targets_document_root( $chunk['prelude'] ) ) {
				$chunk['block'] = self::normalize_root_font_size( $chunk['block'] );
			}

			$selectors    = self::rewrite_selector_list( $chunk['prelude'] );
			$declarations = self::convert_rems( $chunk['block'] );

			// The theme's page background belongs to the page, not to a preview panel sitting in
			// wp-admin. Backgrounds on anything inside the preview are left alone.
			if ( ':scope' === $selectors ) {
				$declarations = self::remove_background_declarations( $declarations );
			}

			$output .= $selectors . '{' . $declarations . '}' . "\n";
		}//end foreach

		return $output;
	}

	/**
	 * Drop every background property from a declaration block.
	 *
	 * Splitting is done by hand rather than with a regular expression because a background can
	 * hold semicolons inside a data URI or nested functions such as gradients.
	 *
	 * @since x.x
	 *
	 * @param string $declarations A declaration block.
	 *
	 * @return string
	 */
	private static function remove_background_declarations( $declarations ) {
		$kept      = array();
		$current   = '';
		$depth     = 0;
		$quote     = '';
		$in_string = false;
		$length    = strlen( $declarations );

		for ( $index = 0; $index < $length; $index++ ) {
			$character = $declarations[ $index ];

			if ( $in_string ) {
				$current .= $character;

				if ( '\\' === $character && $index + 1 < $length ) {
					$index++;
					$current .= $declarations[ $index ];
					continue;
				}

				if ( $character === $quote ) {
					$in_string = false;
				}
				continue;
			}

			if ( '"' === $character || "'" === $character ) {
				$in_string = true;
				$quote     = $character;
				$current  .= $character;
				continue;
			}

			if ( '(' === $character ) {
				$depth++;
			} elseif ( ')' === $character ) {
				$depth--;
			} elseif ( ';' === $character && 0 === $depth ) {
				$kept[]  = $current;
				$current = '';
				continue;
			}

			$current .= $character;
		}//end for

		$kept[] = $current;

		foreach ( $kept as $position => $declaration ) {
			if ( preg_match( '/^\s*background(?:-[a-z]+)?\s*:/i', $declaration ) ) {
				unset( $kept[ $position ] );
			}
		}

		return implode( ';', $kept );
	}

	/**
	 * Whether a selector list sets properties on the document root itself.
	 *
	 * @since x.x
	 *
	 * @param string $selector_list The raw selector list.
	 *
	 * @return bool
	 */
	private static function targets_document_root( $selector_list ) {
		foreach ( explode( ',', $selector_list ) as $selector ) {
			if ( preg_match( '/^\s*(?:html|:root)\s*$/i', $selector ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retarget every selector in a comma separated list.
	 *
	 * A list with nothing aimed at the document root is returned untouched, so commas inside
	 * attribute values are never rewritten.
	 *
	 * @since x.x
	 *
	 * @param string $selector_list The raw selector list.
	 *
	 * @return string
	 */
	private static function rewrite_selector_list( $selector_list ) {
		if ( ! preg_match( '/(?:^|,)\s*(?:html|:root|body)\b/i', $selector_list ) ) {
			return $selector_list;
		}

		$selectors = self::split_selector_list( $selector_list );

		foreach ( $selectors as $index => $selector ) {
			$selectors[ $index ] = self::rewrite_selector( $selector );
		}

		// Collapsing root prefixes can leave duplicates behind, for example from `html, body`.
		return implode( ', ', array_unique( $selectors ) );
	}

	/**
	 * Split a selector list on the commas that separate selectors, skipping the ones inside
	 * parentheses, brackets or quotes.
	 *
	 * @since x.x
	 *
	 * @param string $selector_list The raw selector list.
	 *
	 * @return string[]
	 */
	private static function split_selector_list( $selector_list ) {
		$selectors = array();
		$current   = '';
		$depth     = 0;
		$quote     = '';
		$length    = strlen( $selector_list );

		for ( $index = 0; $index < $length; $index++ ) {
			$character = $selector_list[ $index ];

			if ( '' !== $quote ) {
				$current .= $character;

				if ( '\\' === $character && $index + 1 < $length ) {
					$index++;
					$current .= $selector_list[ $index ];
				} elseif ( $character === $quote ) {
					$quote = '';
				}
				continue;
			}

			if ( '"' === $character || "'" === $character ) {
				$quote = $character;
			} elseif ( '(' === $character || '[' === $character ) {
				$depth++;
			} elseif ( ')' === $character || ']' === $character ) {
				$depth--;
			} elseif ( ',' === $character && 0 === $depth ) {
				$selectors[] = $current;
				$current     = '';
				continue;
			}

			$current .= $character;
		}//end for

		$selectors[] = $current;

		return $selectors;
	}

	/**
	 * Point a selector at the scope root when it was written against the document root.
	 *
	 * Inside @scope nothing matches html, body or :root, so `body .entry-content` would stop
	 * working. Stripping the root prefix keeps the rule, and a selector that was nothing but a
	 * root prefix becomes :scope so the theme's base typography still gets inherited.
	 *
	 * @since x.x
	 *
	 * @param string $selector A single selector.
	 *
	 * @return string
	 */
	private static function rewrite_selector( $selector ) {
		$selector = trim( $selector );

		if ( '' === $selector ) {
			return $selector;
		}

		$stripped = preg_replace( '/^(?:html|:root)\b(?::[a-z-]+(?:\([^)]*\))?|\.[^\s>+~.:\[]+|\[[^\]]*\]|#[^\s>+~.:\[]+)*\s*/i', '', $selector );
		$stripped = preg_replace( '/^>\s*/', '', $stripped );
		$stripped = preg_replace( '/^body\b(?::[a-z-]+(?:\([^)]*\))?|\.[^\s>+~.:\[]+|\[[^\]]*\]|#[^\s>+~.:\[]+)*\s*/i', '', $stripped );
		$stripped = preg_replace( '/^>\s*/', '', $stripped );
		$stripped = trim( $stripped );

		if ( $stripped === $selector ) {
			return $selector;
		}

		return ':scope' . ( '' === $stripped || str_starts_with( $stripped, '::' ) ? '' : ' ' ) . $stripped;
	}

	/**
	 * Split CSS into its top level chunks, keeping strings and comments intact.
	 *
	 * @since x.x
	 *
	 * @param string $css The CSS to split.
	 *
	 * @return array List of chunks with 'type', 'name', 'prelude' and 'block' keys.
	 */
	private static function split_rules( $css ) { // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
		$chunks    = array();
		$prelude   = '';
		$block     = '';
		$depth     = 0;
		$quote     = '';
		$in_string = false;
		$length    = strlen( $css );

		for ( $index = 0; $index < $length; $index++ ) {
			$character = $css[ $index ];

			if ( $in_string ) {
				if ( $depth > 0 ) {
					$block .= $character;
				} else {
					$prelude .= $character;
				}

				if ( '\\' === $character && $index + 1 < $length ) {
					$index++;

					if ( $depth > 0 ) {
						$block .= $css[ $index ];
					} else {
						$prelude .= $css[ $index ];
					}
					continue;
				}

				if ( $character === $quote ) {
					$in_string = false;
				}
				continue;
			}//end if

			if ( '"' === $character || "'" === $character ) {
				$in_string = true;
				$quote     = $character;

				if ( $depth > 0 ) {
					$block .= $character;
				} else {
					$prelude .= $character;
				}
				continue;
			}

			if ( '/' === $character && $index + 1 < $length && '*' === $css[ $index + 1 ] ) {
				$end = strpos( $css, '*/', $index + 2 );

				if ( false === $end ) {
					break;
				}

				$index = $end + 1;
				continue;
			}

			if ( '{' === $character ) {
				$depth++;

				if ( 1 === $depth ) {
					continue;
				}
			} elseif ( '}' === $character ) {
				$depth--;

				if ( 0 === $depth ) {
					$chunks[] = self::make_chunk( $prelude, $block );
					$prelude  = '';
					$block    = '';
					continue;
				}
			} elseif ( ';' === $character && 0 === $depth ) {
				if ( '' !== trim( $prelude ) ) {
					$chunks[] = array(
						'type'    => 'at-statement',
						'name'    => self::get_at_rule_name( $prelude ),
						'prelude' => trim( $prelude ),
						'block'   => '',
					);
				}

				$prelude = '';
				continue;
			}//end if

			if ( $depth > 0 ) {
				$block .= $character;
			} else {
				$prelude .= $character;
			}
		}//end for

		return $chunks;
	}

	/**
	 * Build a chunk from a prelude and the block that followed it.
	 *
	 * @since x.x
	 *
	 * @param string $prelude The selector list or at rule prelude.
	 * @param string $block   The contents of the braces.
	 *
	 * @return array
	 */
	private static function make_chunk( $prelude, $block ) {
		$trimmed = trim( $prelude );

		if ( '' !== $trimmed && '@' === $trimmed[0] ) {
			return array(
				'type'    => 'at-rule',
				'name'    => self::get_at_rule_name( $trimmed ),
				'prelude' => $trimmed . ' ',
				'block'   => $block,
			);
		}

		return array(
			'type'    => 'rule',
			'name'    => '',
			'prelude' => $trimmed,
			'block'   => $block,
		);
	}

	/**
	 * Read the at rule keyword out of a prelude.
	 *
	 * @since x.x
	 *
	 * @param string $prelude An at rule prelude such as '@media screen'.
	 *
	 * @return string The keyword without the leading @, lowercased.
	 */
	private static function get_at_rule_name( $prelude ) {
		$prelude = ltrim( $prelude );

		if ( '' === $prelude || '@' !== $prelude[0] ) {
			return '';
		}

		preg_match( '/^@([a-z-]+)/i', $prelude, $matches );

		return isset( $matches[1] ) ? strtolower( $matches[1] ) : '';
	}
}
