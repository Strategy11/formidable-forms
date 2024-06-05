<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStylesHelper {

	public static function get_upload_base() {
		$uploads = wp_upload_dir();
		if ( is_ssl() && ! preg_match( '/^https:\/\/.*\..*$/', $uploads['baseurl'] ) ) {
			$uploads['baseurl'] = str_replace( 'http://', 'https://', $uploads['baseurl'] );
		}

		return $uploads;
	}

	/**
	 * Called from the admin header.
	 *
	 * @since 4.0
	 */
	public static function save_button() {
		?>
		<input type="submit" name="submit" class="button button-primary frm-button-primary" value="<?php esc_attr_e( 'Update', 'formidable' ); ?>" />
		<?php
	}

	/**
	 * @since 2.05
	 */
	public static function get_css_label_positions() {
		return array(
			'none'     => __( 'top', 'formidable' ),
			'left'     => __( 'left', 'formidable' ),
			'right'    => __( 'right', 'formidable' ),
			'no_label' => __( 'none', 'formidable' ),
			'inside'   => __( 'inside', 'formidable' ),
		);
	}

	public static function get_single_label_positions() {
		return array(
			'top'    => __( 'Top', 'formidable' ),
			'left'   => __( 'Left', 'formidable' ),
			'right'  => __( 'Right', 'formidable' ),
			'inline' => __( 'Inline (left without a set width)', 'formidable' ),
			'none'   => __( 'None', 'formidable' ),
			'hidden' => __( 'Hidden (but leave the space)', 'formidable' ),
			'inside' => __( 'Placeholder inside the field', 'formidable' ),
		);
	}

	public static function minus_icons() {
		return array(
			0 => array(
				'-' => '62e',
				'+' => '62f',
			),
			1 => array(
				'-' => '600',
				'+' => '602',
			),
			2 => array(
				'-' => '604',
				'+' => '603',
			),
			3 => array(
				'-' => '633',
				'+' => '632',
			),
			4 => array(
				'-' => '613',
				'+' => '60f',
			),
		);
	}

	public static function arrow_icons() {
		$minus_icons = self::minus_icons();

		return array(
			6    => array(
				'-' => '62d',
				'+' => '62a',
			),
			0    => array(
				'-' => '60d',
				'+' => '609',
			),
			1    => array(
				'-' => '60e',
				'+' => '60c',
			),
			2    => array(
				'-' => '630',
				'+' => '631',
			),
			3    => array(
				'-' => '62b',
				'+' => '628',
			),
			4    => array(
				'-' => '62c',
				'+' => '629',
			),
			5    => array(
				'-' => '635',
				'+' => '634',
			),
			'p0' => $minus_icons[0],
			'p1' => $minus_icons[1],
			'p2' => $minus_icons[2],
			'p3' => $minus_icons[3],
			'p4' => $minus_icons[4],
		);
	}

	/**
	 * @since 2.0
	 * @return string The class for this icon.
	 */
	public static function icon_key_to_class( $key, $icon = '+', $type = 'arrow' ) {
		if ( 'arrow' === $type && is_numeric( $key ) ) {
			// frm_arrowup6_icon.
			$arrow = array(
				'-' => 'down',
				'+' => 'up',
			);
			$class = 'frm_arrow' . $arrow[ $icon ];
		} else {
			// frm_minus1_icon.
			$key   = str_replace( 'p', '', $key );
			$plus  = array(
				'-' => 'minus',
				'+' => 'plus',
			);
			$class = 'frm_' . $plus[ $icon ];
		}

		if ( $key ) {
			$class .= $key;
		}
		$class .= '_icon';

		return $class;
	}

	/**
	 * @param WP_Post  $style
	 * @param FrmStyle $frm_style
	 * @param string   $type
	 * @return void
	 */
	public static function bs_icon_select( $style, $frm_style, $type = 'arrow' ) {
		$function_name = $type . '_icons';
		$icons         = self::$function_name();
		unset( $function_name );

		$name = 'arrow' === $type ? 'collapse_icon' : 'repeat_icon';
		?>
		<div class="btn-group" id="frm_<?php echo esc_attr( $name ); ?>_select">
			<button class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" type="button">
				<?php FrmAppHelper::icon_by_class( 'frmfont ' . self::icon_key_to_class( $style->post_content[ $name ], '+', $type ) ); ?>
				<?php FrmAppHelper::icon_by_class( 'frmfont ' . self::icon_key_to_class( $style->post_content[ $name ], '-', $type ) ); ?>
				<b class="caret"></b>
			</button>
			<ul class="multiselect-container frm-dropdown-menu">
				<?php foreach ( $icons as $key => $icon ) { ?>
					<li <?php echo $style->post_content['collapse_icon'] == $key ? 'class="active"' : ''; ?>>
						<a href="javascript:void(0);">
							<label>
								<input type="radio" value="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $frm_style->get_field_name( $name ) ); ?>" <?php checked( $style->post_content[ $name ], $key ); ?> />
								<span>
									<?php
									FrmAppHelper::icon_by_class( 'frmfont ' . self::icon_key_to_class( $key, '+', $type ) );
									FrmAppHelper::icon_by_class( 'frmfont ' . self::icon_key_to_class( $key, '-', $type ) );
									?>
								</span>
							</label>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Convert a color setting to a RGB CSV (without the rgb()/rgba() wrapper).
	 *
	 * @since 2.0
	 *
	 * @param string $color Color setting value. This could be hex or rgb.
	 * @return string RGB value without the rgb() wrapper.
	 */
	public static function hex2rgb( $color ) {
		if ( 0 === strpos( $color, 'rgb' ) ) {
			$rgb = self::get_rgb_array_from_rgb( $color );
		} else {
			$rgb = self::get_rgb_array_from_hex( $color );
		}
		return implode( ',', $rgb );
	}

	/**
	 * Remove the rgb()/rgba() wrapper from a RGB color and return its R, G and B values as an array.
	 *
	 * @since 6.8.3
	 *
	 * @param string $rgb    RGB value including the rgb() or rgba() wrapper.
	 * @return array<string> including three numeric values for R, G, and B.
	 */
	private static function get_rgb_array_from_rgb( $rgb ) {
		$rgb = str_replace( array( 'rgb(', 'rgba(', ')' ), '', $rgb );
		$rgb = explode( ',', $rgb );
		if ( 4 === count( $rgb ) ) {
			// Drop the alpha. The function is expected to only return r,g,b with no alpha.
			array_pop( $rgb );
		}
		return $rgb;
	}

	/**
	 * Get the R, G, and B array values from a Hex color code.
	 *
	 * @since 6.8.3
	 *
	 * @param string $hex    A hex color string.
	 * @return array<string> Including three numeric values for R, G, and B.
	 */
	private static function get_rgb_array_from_hex( $hex ) {
		$hex               = str_replace( '#', '', $hex );
		list( $r, $g, $b ) = sscanf( $hex, '%02x%02x%02x' );
		return array( $r, $g, $b );
	}

	/**
	 * @since 4.0
	 */
	public static function hex2rgba( $hex, $a ) {
		$rgb = self::hex2rgb( $hex );

		return 'rgba(' . $rgb . ',' . $a . ')';
	}

	/**
	 * @since 6.0
	 *
	 * @param string $rgba Color setting value. This could be hex or rgb.
	 * @return string Hex color value.
	 */
	private static function rgb_to_hex( $rgba ) {
		if ( strpos( $rgba, '#' ) === 0 ) {
			// Color is already hex.
			return $rgba;
		}
		preg_match( '/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i', $rgba, $by_color );
		return sprintf( '%02x%02x%02x', $by_color[1], $by_color[2], $by_color[3] );
	}

	/**
	 * @since 6.8
	 *
	 * @param string $hsl
	 * @return string|null Null if it fails to parse the HSL string.
	 */
	private static function hsl_to_hex( $hsl ) {
		// Convert hsla to hsl.
		$hsl = preg_replace( '/hsla\((\d+),\s*([\d.]+)%,\s*([\d.]+)%,\s*([\d.]+)\)/', 'hsl($1, $2%, $3%)', $hsl );

		// Extract HSL components from the color string.
		preg_match( '/hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)/', $hsl, $matches );

		if ( count( $matches ) !== 4 ) {
			// Invalid HSL string format.
			return null;
		}

		// Extract HSL values.
		$h = (int) $matches[1];
		$s = (int) $matches[2] / 100;
		$l = (int) $matches[3] / 100;

		// Calculate RGB values.
		$c = ( 1 - abs( 2 * $l - 1 ) ) * $s;
		$x = $c * ( 1 - abs( ( (int) ( $h / 60 ) % 2 ) - 1 ) );
		$m = $l - $c / 2;
		$r = 0;
		$g = 0;
		$b = 0;

		if ( $h >= 0 && $h < 60 ) {
			$r = $c;
			$g = $x;
		} elseif ( $h >= 60 && $h < 120 ) {
			$r = $x;
			$g = $c;
		} elseif ( $h >= 120 && $h < 180 ) {
			$g = $c;
			$b = $x;
		} elseif ( $h >= 180 && $h < 240 ) {
			$g = $x;
			$b = $c;
		} elseif ( $h >= 240 && $h < 300 ) {
			$r = $x;
			$b = $c;
		} elseif ( $h >= 300 && $h < 360 ) {
			$r = $c;
			$b = $x;
		}//end if

		// Convert RGB to 8-bit values
		$r = round( ( $r + $m ) * 255 );
		$g = round( ( $g + $m ) * 255 );
		$b = round( ( $b + $m ) * 255 );

		// Convert RGB to hex
		$hex = sprintf( '%02x%02x%02x', $r, $g, $b );

		return $hex;
	}

	/**
	 * @since 2.3
	 * @param string $hex   string  The original color in hex format #ffffff.
	 * @param int    $steps integer Should be between -255 and 255. Negative = darker, positive = lighter.
	 */
	public static function adjust_brightness( $hex, $steps ) {
		$steps = max( - 255, min( 255, $steps ) );

		if ( 0 === strpos( $hex, 'rgba(' ) ) {
			$rgba                   = str_replace( ')', '', str_replace( 'rgba(', '', $hex ) );
			list ( $r, $g, $b, $a ) = array_map( 'trim', explode( ',', $rgba ) );
			$r                      = max( 0, min( 255, $r + $steps ) );
			$g                      = max( 0, min( 255, $g + $steps ) );
			$b                      = max( 0, min( 255, $b + $steps ) );
			return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $a . ')';
		}

		// Normalize into a six character long hex string
		$hex = str_replace( '#', '', $hex );
		self::fill_hex( $hex );

		// Split into three parts: R, G and B
		$color_parts = str_split( $hex, 2 );
		$return      = '#';

		foreach ( $color_parts as $color ) {
			// Convert to decimal.
			$color = hexdec( $color );
			// Adjust color.
			$color = max( 0, min( 255, $color + $steps ) );

			// Make two char hex code.
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT );
		}

		return $return;
	}

	/**
	 * @since 6.0
	 *
	 * @param string $color
	 * @return int
	 */
	public static function get_color_brightness( $color ) {
		if ( 0 === strpos( $color, 'rgb' ) ) {
			$color = self::rgb_to_hex( $color );
		}

		if ( 0 === strpos( $color, 'hsl' ) ) {
			$hsl_to_hex = self::hsl_to_hex( $color );
			if ( is_null( $hsl_to_hex ) ) {
				// Fallback if we cannot convert the HSL value.
				return 0;
			}
			$color = $hsl_to_hex;
		}

		self::fill_hex( $color );

		$c_r        = hexdec( substr( $color, 0, 2 ) );
		$c_g        = hexdec( substr( $color, 2, 2 ) );
		$c_b        = hexdec( substr( $color, 4, 2 ) );
		$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

		return $brightness;
	}

	/**
	 * Change a 3 character hex color to a 6 character one.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private static function fill_hex( &$color ) {
		if ( 3 === strlen( $color ) ) {
			$color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
		}
	}

	/**
	 * @since 4.05.02
	 */
	public static function get_css_vars( $vars = array() ) {
		$vars = apply_filters( 'frm_css_vars', $vars );
		return array_unique( $vars );
	}

	/**
	 * @since 4.05.02
	 */
	public static function output_vars( $settings, $defaults = array(), $vars = array() ) {
		if ( empty( $vars ) ) {
			$vars = self::get_css_vars( array_keys( $settings ) );
		}
		$remove = array( 'remove_box_shadow', 'remove_box_shadow_active', 'theme_css', 'theme_name', 'theme_selector', 'important_style', 'submit_style', 'collapse_icon', 'center_form', 'custom_css', 'style_class', 'submit_bg_img', 'change_margin', 'repeat_icon' );
		$vars   = array_diff( $vars, $remove );

		foreach ( $vars as $var ) {
			if ( ! isset( $settings[ $var ] ) ) {
				continue;
			}
			if ( ! isset( $defaults[ $var ] ) ) {
				$defaults[ $var ] = '';
			}
			$show = empty( $defaults ) || ( $settings[ $var ] !== '' && $settings[ $var ] !== $defaults[ $var ] );
			if ( $show ) {
				echo '--' . esc_html( str_replace( '_', '-', $var ) ) . ':' . ( $var === 'font' ? FrmAppHelper::kses( $settings[ $var ] ) : esc_html( $settings[ $var ] ) ) . ';'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * @since 2.3
	 *
	 * @param WP_Post $style
	 * @return array
	 */
	public static function get_settings_for_output( $style ) {
		if ( self::previewing_style() ) {
			$frm_style = new FrmStyle();
			if ( isset( $_POST['frm_style_setting'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				// Sanitizing is done later.
				$posted = wp_unslash( $_POST['frm_style_setting'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
				if ( ! is_array( $posted ) ) {
					$posted = json_decode( $posted, true );
					FrmAppHelper::format_form_data( $posted );
					$settings   = $frm_style->sanitize_post_content( $posted['frm_style_setting']['post_content'] );
					$style_name = sanitize_title( $posted['style_name'] );
				} else {
					$settings   = $frm_style->sanitize_post_content( $posted['post_content'] );
					$style_name = FrmAppHelper::get_post_param( 'style_name', '', 'sanitize_title' );
				}
			} else {
				$settings   = $frm_style->sanitize_post_content( wp_unslash( $_GET ) );
				$style_name = FrmAppHelper::get_param( 'style_name', '', 'get', 'sanitize_title' );
			}

			FrmAppHelper::sanitize_value( 'sanitize_text_field', $settings );

			$settings['style_class'] = '';
			if ( ! empty( $style_name ) ) {
				$settings['style_class'] = $style_name . '.';
			}
		} else {
			$settings                = $style->post_content;
			$settings['style_class'] = 'frm_style_' . $style->post_name . '.';
		}//end if

		$settings['style_class']  .= 'with_frm_style';
		$settings['font']          = stripslashes( $settings['font'] );
		$settings['change_margin'] = self::description_margin_for_screensize( $settings['width'] );

		$checkbox_opts = array( 'important_style', 'auto_width', 'submit_style', 'collapse_icon', 'center_form' );
		foreach ( $checkbox_opts as $opt ) {
			if ( ! isset( $settings[ $opt ] ) ) {
				$settings[ $opt ] = 0;
			}
		}

		self::prepare_color_output( $settings );

		$settings['field_height'] = $settings['field_height'] === '' ? 'auto' : $settings['field_height'];
		$settings['field_width']  = $settings['field_width'] === '' ? 'auto' : $settings['field_width'];
		$settings['auto_width']   = $settings['auto_width'] ? 'auto' : $settings['field_width'];
		$settings['box_shadow']   = ! empty( $settings['remove_box_shadow'] ) ? 'none' : '0 1px 1px rgba(0, 0, 0, 0.075) inset';

		if ( ! isset( $settings['repeat_icon'] ) ) {
			$settings['repeat_icon'] = 1;
		}

		return $settings;
	}

	/**
	 * @since 2.3
	 */
	public static function prepare_color_output( &$settings, $allow_transparent = true ) {
		$colors = self::allow_color_override();
		foreach ( $colors as $css => $opts ) {
			if ( $css === 'transparent' && ! $allow_transparent ) {
				$css = '';
			}
			foreach ( $opts as $opt ) {
				self::get_color_output( $css, $settings[ $opt ] );
			}
		}
	}

	/**
	 * @since 2.3
	 *
	 * @return array
	 */
	private static function allow_color_override() {
		$frm_style = new FrmStyle();
		$colors    = $frm_style->get_color_settings();

		$transparent = array(
			'fieldset_color',
			'fieldset_bg_color',
			'bg_color',
			'bg_color_active',
			'bg_color_disabled',
			'section_bg_color',
			'error_bg',
			'success_bg_color',
			'progress_bg_color',
			'progress_active_bg_color',
			'submit_border_color',
			'submit_hover_border_color',
			'submit_active_border_color',
			'submit_hover_bg_color',
			'submit_active_bg_color',
		);

		return array(
			'transparent' => $transparent,
			''            => array_diff( $colors, $transparent ),
		);
	}

	/**
	 * @since 2.3
	 */
	private static function get_color_output( $default, &$color ) {
		$color = trim( $color );
		if ( empty( $color ) ) {
			$color = $default;
		} elseif ( false !== strpos( $color, 'rgb(' ) ) {
			$color = str_replace( 'rgb(', 'rgba(', $color );
			$color = str_replace( ')', ',1)', $color );
		} elseif ( strpos( $color, '#' ) === false && self::is_hex( $color ) ) {
			$color = '#' . $color;
		}
	}

	/**
	 * If a color looks like a hex code without the #, prepend the #.
	 * A color looks like a hex code if it does not contain the substrings "rgb", "rgba", "hsl", "hsla", or "hwb".
	 *
	 * @since 6.8
	 *
	 * @param string $color
	 * @return bool
	 */
	private static function is_hex( $color ) {
		$non_hex_substrings = array(
			'rgba(',
			'hsl(',
			'hsla(',
			'hwb(',
		);

		foreach ( $non_hex_substrings as $substring ) {
			if ( false !== strpos( $color, $substring ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * If left/right label is over a certain size,
	 * adjust the field description margin at a different screen size
	 *
	 * @since 2.3
	 */
	private static function description_margin_for_screensize( $width ) {
		$temp_label_width = str_replace( 'px', '', $width );
		$change_margin    = false;
		if ( $temp_label_width >= 230 ) {
			$change_margin = '800px';
		} elseif ( $width >= 215 ) {
			$change_margin = '700px';
		} elseif ( $width >= 180 ) {
			$change_margin = '650px';
		}

		return $change_margin;
	}

	/**
	 * @since 2.3
	 *
	 * @return bool
	 */
	public static function previewing_style() {
		$ajax_change = isset( $_POST['action'] ) && $_POST['action'] === 'frm_change_styling' && isset( $_POST['frm_style_setting'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		return $ajax_change || isset( $_GET['flat'] );
	}

	/**
	 * Get the URL for the Styler page list view (where you can assign styles to a form and view style templates) for a target form.
	 *
	 * @since 6.0
	 *
	 * @param int|string $form_id
	 * @return string
	 */
	public static function get_list_url( $form_id ) {
		return admin_url( 'admin.php?page=formidable-styles&form=' . absint( $form_id ) );
	}

	/**
	 * Get a link to edit a target style post object in the visual styler.
	 *
	 * @param stdClass|WP_Post $style
	 * @param int|string       $form_id Used for the back button and preview form target.
	 * @return string
	 */
	public static function get_edit_url( $style, $form_id = 0 ) {
		$query_args = array(
			'page'       => 'formidable-styles',
			'frm_action' => 'edit',
			'id'         => $style->ID,
		);

		if ( $form_id ) {
			// We include &form_id for the back button to know where to point to.
			$query_args['form'] = $form_id;
		}

		return add_query_arg( $query_args, admin_url( 'admin.php' ) );
	}

	/**
	 * Get a count of the number of forms assigned to a target style ID.
	 * All unassigned forms are included in the count for the default style.
	 *
	 * @since 6.0
	 *
	 * @param int|string $style_id
	 * @param bool       $is_default
	 * @return int
	 */
	public static function get_form_count_for_style( $style_id, $is_default ) {
		$serialized = serialize( array( 'custom_style' => (string) $style_id ) );
		// Chop off the "a:1:{" from the front and the "}" from the back.
		$substring       = substr( $serialized, 5, -1 );
		$number_of_forms = FrmDb::get_count(
			'frm_forms',
			array(
				'status'       => 'published',
				'options LIKE' => $substring,
			)
		);

		if ( ! $is_default ) {
			// Exit early as the rest of the code is about including the default count.
			return $number_of_forms;
		}

		$conversational_style_id = FrmDb::get_var( 'posts', array( 'post_name' => 'lines-no-boxes' ), 'ID' );
		$number_of_forms        += self::get_default_style_count( $style_id, $conversational_style_id );

		return $number_of_forms;
	}

	/**
	 * Get the number of forms that use the default style.
	 *
	 * @since 6.0
	 *
	 * @param int|string $style_id
	 * @param mixed      $conversational_style_id
	 * @return int
	 */
	private static function get_default_style_count( $style_id, $conversational_style_id ) {
		$substrings = array_map(
			function ( $value ) {
				$substring = serialize( array( 'custom_style' => $value ) );
				return substr( $substring, 5, -1 );
			},
			array( '1', 1 )
		);
		$where      = array(
			'status' => 'published',
			0        => array(
				'options NOT LIKE' => 'custom_style',
				'or'               => 1,
				'options LIKE'     => $substrings,
			),
		);

		if ( $conversational_style_id ) {
			// When a conversational style is set, check for it in the query by wrapping the where and adding a conversational option check.
			$is_conversational_style = (int) $style_id === (int) $conversational_style_id;
			$where[0]                = array(
				// The chat option doesn't exist if it isn't on.
				( $is_conversational_style ? 'options LIKE' : 'options NOT LIKE' ) => ';s:4:"chat";',
				$where[0],
			);
		}

		return FrmDb::get_count( 'frm_forms', $where );
	}

	/**
	 * @since 5.5.1
	 * @deprecated 6.10
	 * @return void
	 */
	public static function maybe_include_font_icon_css() {
		_deprecated_function( __METHOD__, '6.10' );
	}
}
