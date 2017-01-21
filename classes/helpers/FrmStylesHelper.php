<?php
class FrmStylesHelper {

    public static function jquery_themes() {
        $themes = array(
            'ui-lightness'  => 'UI Lightness',
            'ui-darkness'   => 'UI Darkness',
            'smoothness'    => 'Smoothness',
            'start'         => 'Start',
            'redmond'       => 'Redmond',
            'sunny'         => 'Sunny',
            'overcast'      => 'Overcast',
            'le-frog'       => 'Le Frog',
            'flick'         => 'Flick',
			'pepper-grinder' => 'Pepper Grinder',
            'eggplant'      => 'Eggplant',
            'dark-hive'     => 'Dark Hive',
            'cupertino'     => 'Cupertino',
            'south-street'  => 'South Street',
            'blitzer'       => 'Blitzer',
            'humanity'      => 'Humanity',
            'hot-sneaks'    => 'Hot Sneaks',
            'excite-bike'   => 'Excite Bike',
            'vader'         => 'Vader',
            'dot-luv'       => 'Dot Luv',
            'mint-choc'     => 'Mint Choc',
            'black-tie'     => 'Black Tie',
            'trontastic'    => 'Trontastic',
            'swanky-purse'  => 'Swanky Purse',
        );

        $themes = apply_filters('frm_jquery_themes', $themes);
        return $themes;
    }

	public static function jquery_css_url( $theme_css ) {
        if ( $theme_css == -1 ) {
            return;
        }

        if ( ! $theme_css || $theme_css == '' || $theme_css == 'ui-lightness' ) {
            $css_file = FrmAppHelper::plugin_url() . '/css/ui-lightness/jquery-ui.css';
        } else if ( preg_match('/^http.?:\/\/.*\..*$/', $theme_css) ) {
            $css_file = $theme_css;
        } else {
            $uploads = self::get_upload_base();
			$file_path = '/formidable/css/' . $theme_css . '/jquery-ui.css';
            if ( file_exists($uploads['basedir'] . $file_path) ) {
                $css_file = $uploads['baseurl'] . $file_path;
            } else {
				$css_file = FrmAppHelper::jquery_ui_base_url() . '/themes/' . $theme_css . '/jquery-ui.min.css';
            }
        }

        return $css_file;
    }

    public static function enqueue_jquery_css() {
		$form = self::get_form_for_page();
		$theme_css = FrmStylesController::get_style_val( 'theme_css', $form );
        if ( $theme_css != -1 ) {
            wp_enqueue_style('jquery-theme', self::jquery_css_url($theme_css), array(), FrmAppHelper::plugin_version());
        }
    }

	public static function get_form_for_page() {
		global $frm_vars;
		$form_id = 'default';
		if ( ! empty( $frm_vars['forms_loaded'] ) ) {
			foreach ( $frm_vars['forms_loaded'] as $form ) {
				if ( is_object( $form ) ) {
					$form_id = $form->id;
					break;
				}
			}
		}
		return $form_id;
	}

    public static function get_upload_base() {
        $uploads = wp_upload_dir();
        if ( is_ssl() && ! preg_match('/^https:\/\/.*\..*$/', $uploads['baseurl']) ) {
            $uploads['baseurl'] = str_replace('http://', 'https://', $uploads['baseurl']);
        }

        return $uploads;
    }

	public static function style_menu( $active = '' ) {
?>
        <h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles' ) ) ?>" class="nav-tab <?php echo ( '' == $active ) ? 'nav-tab-active' : '' ?>"><?php _e( 'Edit Styles', 'formidable' ) ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles&frm_action=manage' ) ) ?>" class="nav-tab <?php echo ( 'manage' == $active ) ? 'nav-tab-active' : '' ?>"><?php _e( 'Manage Form Styles', 'formidable' ) ?></a>
			<a href="<?php echo esc_url( admin_url('admin.php?page=formidable-styles&frm_action=custom_css' ) ) ?>" class="nav-tab <?php echo ( 'custom_css' == $active ) ? 'nav-tab-active' : '' ?>"><?php _e( 'Custom CSS', 'formidable' ) ?></a>
        </h2>
<?php
    }

    public static function minus_icons() {
        return array(
			0 => array( '-' => '62e', '+' => '62f' ),
			1 => array( '-' => '600', '+' => '602' ),
			2 => array( '-' => '604', '+' => '603' ),
			3 => array( '-' => '633', '+' => '632' ),
			4 => array( '-' => '613', '+' => '60f' ),
        );
    }

    public static function arrow_icons() {
        $minus_icons = self::minus_icons();

        return array(
			6 => array( '-' => '62d', '+' => '62a' ),
			0 => array( '-' => '60d', '+' => '609' ),
			1 => array( '-' => '60e', '+' => '60c' ),
			2 => array( '-' => '630', '+' => '631' ),
			3 => array( '-' => '62b', '+' => '628' ),
			4 => array( '-' => '62c', '+' => '629' ),
			5 => array( '-' => '635', '+' => '634' ),
            'p0' => $minus_icons[0],
            'p1' => $minus_icons[1],
            'p2' => $minus_icons[2],
            'p3' => $minus_icons[3],
            'p4' => $minus_icons[4],
        );
    }

    /**
     * @since 2.0
     * @return The class for this icon
     */
	public static function icon_key_to_class( $key, $icon = '+', $type = 'arrow' ) {
        if ( 'arrow' == $type && is_numeric($key) ) {
            //frm_arrowup6_icon
			$arrow = array( '-' => 'down', '+' => 'up' );
			$class = 'frm_arrow' . $arrow[ $icon ];
        } else {
            //frm_minus1_icon
            $key = str_replace('p', '', $key);
			$plus = array( '-' => 'minus', '+' => 'plus' );
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
		$icons = self::$function_name();
		unset( $function_name );

        $name = ( 'arrow' == $type ) ? 'collapse_icon' : 'repeat_icon';
?>
    	<select name="<?php echo esc_attr( $frm_style->get_field_name($name) ) ?>" id="frm_<?php echo esc_attr( $name ) ?>" class="frm_icon_font frm_multiselect hide-if-js">
            <?php foreach ( $icons as $key => $icon ) { ?>
			<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $style->post_content[ $name ], $key ) ?>>
				<?php echo '&#xe' . $icon['+'] . '; &#xe' . $icon['-'] . ';'; ?>
            </option>
            <?php } ?>
    	</select>

        <div class="btn-group hide-if-no-js" id="frm_<?php echo esc_attr( $name ) ?>_select">
            <button class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" type="button">
				<i class="frm_icon_font <?php echo esc_attr( self::icon_key_to_class( $style->post_content[ $name ], '+', $type ) ) ?>"></i>
				<i class="frm_icon_font <?php echo esc_attr( self::icon_key_to_class( $style->post_content[ $name ], '-', $type ) ) ?>"></i>
                <b class="caret"></b>
            </button>
            <ul class="multiselect-container frm-dropdown-menu">
                <?php foreach ( $icons as $key => $icon ) { ?>
                <li <?php echo ( $style->post_content['collapse_icon'] == $key ) ? 'class="active"' : '' ?>>
                    <a href="javascript:void(0);">
                        <label>
                            <input type="radio" value="<?php echo esc_attr( $key ) ?>"/>
                            <span>
                                <i class="frm_icon_font <?php echo esc_attr( self::icon_key_to_class( $key, '+', $type ) ) ?>"></i>
                                <i class="frm_icon_font <?php echo esc_attr( self::icon_key_to_class( $key, '-', $type ) ) ?>"></i>
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
        $hex = str_replace('#', '', $hex);

        if ( strlen($hex) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
        } else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
        }
		$rgb = array( $r, $g, $b );
        return implode(',', $rgb); // returns the rgb values separated by commas
        //return $rgb; // returns an array with the rgb values
    }

	/**
	 * @param $hex string - The original color in hex format #ffffff
	 * @param $steps integer - should be between -255 and 255. Negative = darker, positive = lighter
	 * @since 2.3
	 */
	public static function adjust_brightness( $hex, $steps ) {
		$steps = max( -255, min( 255, $steps ) );

		// Normalize into a six character long hex string
		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 );
			$hex .= str_repeat( substr( $hex, 1, 1 ), 2 );
			$hex .= str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		// Split into three parts: R, G and B
		$color_parts = str_split( $hex, 2 );
		$return = '#';

		foreach ( $color_parts as $color ) {
			$color   = hexdec( $color ); // Convert to decimal
			$color   = max( 0,min( 255,$color + $steps ) ); // Adjust color
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT ); // Make two char hex code
		}

		return $return;
	}

	/**
	 * @since 2.3
	 */
	public static function get_settings_for_output( $style ) {
		if ( self::previewing_style() ) {
			if ( isset( $_GET['frm_style_setting'] ) ) {
				$settings = $_GET['frm_style_setting']['post_content'];
			} else {
				$settings = $_GET;
			}
			FrmAppHelper::sanitize_value( 'sanitize_text_field', $settings );

			$style_name = FrmAppHelper::simple_get( 'style_name', 'sanitize_title' );
			$settings['style_class'] = '';
			if ( ! empty( $style_name ) ) {
				$settings['style_class'] = $style_name . '.';
			}
		} else {
			$settings = $style->post_content;
			$settings['style_class'] = 'frm_style_' . $style->post_name . '.';
		}

		$settings['style_class'] .= 'with_frm_style';
		$settings['font'] = stripslashes( $settings['font'] );
		$settings['change_margin'] = self::description_margin_for_screensize( $settings['width'] );

		$checkbox_opts = array( 'important_style', 'auto_width', 'submit_style', 'collapse_icon', 'center_form' );
		foreach ( $checkbox_opts as $opt ) {
			if ( ! isset( $settings[ $opt ] ) ) {
				$settings[ $opt ] = 0;
			}
		}

		self::prepare_color_output( $settings );

		return $settings;
	}

	/**
	 * @since 2.3
	 */
	private static function prepare_color_output( &$settings ) {
		$colors = self::allow_color_override();
		foreach ( $colors as $css => $opts ) {
			foreach ( $opts as $opt ) {
				self::get_color_output( $css, $settings[ $opt ] );
			}
		}
	}

	/**
	 * @since 2.3
	 */
	private static function allow_color_override() {
		return array(
			'transparent' => array(
				'fieldset_color', 'fieldset_bg_color', 'bg_color',
				'bg_color_disabled', 'bg_color_active', 'bg_color_error',
				'section_bg_color', 'error_bg', 'success_bg_color',
				'progress_bg_color', 'progress_active_bg_color',
			),
			'' => array(
				'title_color', 'section_color', 'submit_text_color',
				'label_color', 'check_label_color', 'form_desc_color',
				'description_color', 'text_color', 'text_color_disabled',
				'border_color', 'submit_bg_color', 'submit_border_color',
				'error_text', 'progress_border_color', 'progress_color',
				'progress_active_color',
				'submit_hover_bg_color', 'submit_hover_border_color', 'submit_hover_color',
				'submit_active_color', 'submit_active_border_color', 'submit_active_bg_color',
			),
		);
	}

	/**
	 * @since 2.3
	 */
	private static function get_color_output( $default, &$color ) {
		$color = ( trim( $color ) == '' ) ? $default : '#' . $color;
	}

	/**
	 * If left/right label is over a certain size,
	 * adjust the field description margin at a different screen size
	 * @since 2.3
	 */
	private static function description_margin_for_screensize( $width ) {
		$temp_label_width = str_replace( 'px', '', $width );
		$change_margin = false;
		if ( $temp_label_width >= 230 ) {
			$change_margin = '800px';
		} else if ( $width >= 215 ) {
			$change_margin = '700px';
		} else if ( $width >= 180 ) {
			$change_margin = '650px';
		}
		return $change_margin;
	}

	/**
	 * @since 2.3
	 */
	public static function previewing_style() {
		return isset( $_GET['frm_style_setting'] ) || isset( $_GET['flat'] );
	}
}
