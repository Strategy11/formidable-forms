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
	 * @since 4.0
	 */
	public static function get_style_menu( $active = '' ) {
		ob_start();
		self::style_menu( $active );
		$menu = ob_get_contents();
		ob_end_clean();

		return $menu;
	}

	public static function style_menu( $active = '' ) {
		?>
		<ul class="frm_form_nav">
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles' ) ); ?>"
					class="<?php echo ( '' == $active ) ? 'current_page' : ''; ?>">
					<?php esc_html_e( 'Edit Styles', 'formidable' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles&frm_action=manage' ) ); ?>"
					class="<?php echo ( 'manage' == $active ) ? 'current_page' : ''; ?>">
					<?php esc_html_e( 'Manage Styles', 'formidable' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles&frm_action=custom_css' ) ); ?>"
					class="<?php echo ( 'custom_css' == $active ) ? 'current_page' : ''; ?>">
					<?php esc_html_e( 'Custom CSS', 'formidable' ); ?>
				</a>
			</li>
		</ul>
		<?php
	}

	/**
	 * @since 4.0
	 */
	public static function styler_save_button( $atts ) {
		$style = $atts['style'];
		if ( ! empty( $style->ID ) && empty( $style->menu_order ) ) {
			$delete_link = admin_url( 'admin.php?page=formidable-styles&frm_action=destroy&id=' . $style->ID );
		}

		include( FrmAppHelper::plugin_path() . '/classes/views/styles/header-buttons.php' );
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
	 * Either get the heading or the style switcher.
	 *
	 * @since 4.0
	 */
	public static function styler_switcher( $atts ) {
		if ( has_action( 'frm_style_switcher_heading' ) ) {
			do_action( 'frm_style_switcher_heading', $atts );
		} else {
			?>
			<div class="frm_top_left">
				<h1>
					<?php echo esc_html( $atts['style']->post_title ); ?>
				</h1>
			</div>
			<?php
		}
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
			//frm_arrowup6_icon
			$arrow = array(
				'-' => 'down',
				'+' => 'up',
			);
			$class = 'frm_arrow' . $arrow[ $icon ];
		} else {
			//frm_minus1_icon
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

	public static function bs_icon_select( $style, $frm_style, $type = 'arrow' ) {
		$function_name = $type . '_icons';
		$icons         = self::$function_name();
		unset( $function_name );

		$name = ( 'arrow' == $type ) ? 'collapse_icon' : 'repeat_icon';
		?>
		<div class="btn-group" id="frm_<?php echo esc_attr( $name ); ?>_select">
			<button class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" type="button">
				<?php FrmAppHelper::icon_by_class( 'frmfont ' . self::icon_key_to_class( $style->post_content[ $name ], '+', $type ) ); ?>
				<?php FrmAppHelper::icon_by_class( 'frmfont ' . self::icon_key_to_class( $style->post_content[ $name ], '-', $type ) ); ?>
				<b class="caret"></b>
			</button>
			<ul class="multiselect-container frm-dropdown-menu">
				<?php foreach ( $icons as $key => $icon ) { ?>
					<li <?php echo ( $style->post_content['collapse_icon'] == $key ) ? 'class="active"' : ''; ?>>
						<a href="javascript:void(0);">
							<label>
								<input type="radio" value="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $frm_style->get_field_name( $name ) ); ?>" <?php checked( $style->post_content[ $name ], $key ); ?>/>
								<span>
									<?php FrmAppHelper::icon_by_class( 'frmfont ' . self::icon_key_to_class( $key, '+', $type ) ); ?>
									<?php FrmAppHelper::icon_by_class( 'frmfont ' . self::icon_key_to_class( $key, '-', $type ) ); ?>
								</span>
							</label>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
		<?php
	}

	public static function hex2rgb( $hex ) {
		$hex = str_replace( '#', '', $hex );

		list( $r, $g, $b ) = sscanf( $hex, '%02x%02x%02x' );

		$rgb = array( $r, $g, $b );

		return implode( ',', $rgb );
	}

	/**
	 * @since 4.0
	 */
	public static function hex2rgba( $hex, $a ) {
		$rgb = self::hex2rgb( $hex );

		return 'rgba(' . $rgb . ',' . $a . ')';
	}

	/**
	 * @param $hex string - The original color in hex format #ffffff
	 * @param $steps integer - should be between -255 and 255. Negative = darker, positive = lighter
	 *
	 * @since 2.3
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
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 );
			$hex .= str_repeat( substr( $hex, 1, 1 ), 2 );
			$hex .= str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		// Split into three parts: R, G and B
		$color_parts = str_split( $hex, 2 );
		$return      = '#';

		foreach ( $color_parts as $color ) {
			$color  = hexdec( $color ); // Convert to decimal
			$color  = max( 0, min( 255, $color + $steps ) ); // Adjust color
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT ); // Make two char hex code
		}

		return $return;
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
		}

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
		$settings['box_shadow']   = ( isset( $settings['remove_box_shadow'] ) && $settings['remove_box_shadow'] ) ? 'none' : '0 1px 1px rgba(0, 0, 0, 0.075) inset';

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
			'success_bg_color',
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
		} elseif ( strpos( $color, '#' ) === false && false === strpos( $color, 'rgba(' ) ) {
			$color = '#' . $color;
		}
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
	 */
	public static function previewing_style() {
		$ajax_change = isset( $_POST['action'] ) && $_POST['action'] === 'frm_change_styling' && isset( $_POST['frm_style_setting'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		return $ajax_change || isset( $_GET['flat'] );
	}

	/**
	 * @deprecated 3.01
	 * @codeCoverageIgnore
	 */
	public static function get_sigle_label_postitions() {
		return FrmDeprecated::get_sigle_label_postitions();
	}

	/**
	 * @deprecated 3.02.03
	 * @codeCoverageIgnore
	 */
	public static function jquery_themes() {
		return FrmDeprecated::jquery_themes();
	}

	/**
	 * @deprecated 3.02.03
	 * @codeCoverageIgnore
	 */
	public static function jquery_css_url( $theme_css ) {
		return FrmDeprecated::jquery_css_url( $theme_css );
	}

	/**
	 * @deprecated 3.02.03
	 * @codeCoverageIgnore
	 */
	public static function enqueue_jquery_css() {
		FrmDeprecated::enqueue_jquery_css();
	}

	/**
	 * @deprecated 3.02.03
	 * @codeCoverageIgnore
	 */
	public static function get_form_for_page() {
		return FrmDeprecated::get_form_for_page();
	}
}
