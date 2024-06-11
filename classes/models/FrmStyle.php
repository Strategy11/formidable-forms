<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStyle {
	/**
	 * Unique ID number of the current instance.
	 *
	 * @var int
	 */
	public $number = false;

	/**
	 * The id of the post.
	 *
	 * @var int|string
	 */
	public $id = 0;

	/**
	 * @param int|string $id The id of the stylsheet or 'default'.
	 */
	public function __construct( $id = 0 ) {
		$this->id = $id;
	}

	/**
	 * @return stdClass
	 */
	public function get_new() {
		$this->id = 0;

		$max_slug_value = 2147483647;
		// We want to have at least 2 characters in the slug.
		$min_slug_value = 37;
		$key            = base_convert( rand( $min_slug_value, $max_slug_value ), 10, 36 );

		$style = array(
			'post_type'    => FrmStylesController::$post_type,
			'ID'           => '',
			'post_title'   => __( 'New Style', 'formidable' ),
			'post_name'    => $key,
			'post_content' => $this->get_defaults(),
			'menu_order'   => '',
			'post_status'  => 'publish',
		);

		return (object) $style;
	}

	/**
	 * @param array $settings
	 * @return int|WP_Error
	 */
	public function save( $settings ) {
		return FrmDb::save_settings( $settings, 'frm_styles' );
	}

	/**
	 * @return void
	 */
	public function duplicate( $id ) {
		// Duplicating is a pro feature. This is handled in FrmProStyle::duplicate instead.
	}

	/**
	 * Handle save actions in the visual styler edit page.
	 *
	 * @param mixed $id
	 * @return array<int|WP_Error>
	 */
	public function update( $id = 'default' ) {
		$all_instances = $this->get_all();

		if ( ! $id ) {
			$new_style       = (array) $this->get_new();
			$all_instances[] = $new_style;
		}

		$action_ids = array();

		foreach ( $all_instances as $new_instance ) {
			$new_instance = (array) $new_instance;
			$this->id     = $new_instance['ID'];

			if ( $id != $this->id || ! $_POST || ! isset( $_POST['frm_style_setting'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				// Don't continue if not saving this style.
				continue;
			}

			// Custom CSS is no longer used from the default style, but it is still checked if the Global Setting is missing.
			// Preserve the previous value in case Custom CSS has not been saved as a Global Setting yet.
			$custom_css = isset( $new_instance['post_content']['custom_css'] ) ? $new_instance['post_content']['custom_css'] : '';

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! empty( $_POST['frm_style_setting']['post_title'] ) ) {
				// The nonce check happens in FrmStylesController::save_style before this is called.
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$new_instance['post_title'] = sanitize_text_field( wp_unslash( $_POST['frm_style_setting']['post_title'] ) );
			}

			$new_instance['post_content']               = isset( $_POST['frm_style_setting']['post_content'] ) ? $this->sanitize_post_content( wp_unslash( $_POST['frm_style_setting']['post_content'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			$new_instance['post_content']['custom_css'] = $custom_css;
			unset( $custom_css );

			$new_instance['post_type']   = FrmStylesController::$post_type;
			$new_instance['post_status'] = 'publish';

			if ( ! $id ) {
				$new_instance['post_name'] = $new_instance['post_title'];
			}

			$default_settings = $this->get_defaults();

			foreach ( $default_settings as $setting => $default ) {
				if ( ! isset( $new_instance['post_content'][ $setting ] ) ) {
					$new_instance['post_content'][ $setting ] = $default;
				}

				if ( $this->is_color( $setting ) ) {
					$color_val = $new_instance['post_content'][ $setting ];
					if ( $color_val !== '' && false !== strpos( $color_val, 'rgb' ) ) {
						// Maybe sanitize if invalid rgba value is entered.
						$this->maybe_sanitize_rgba_value( $color_val );
					}
					$new_instance['post_content'][ $setting ] = str_replace( '#', '', $color_val );
				} elseif ( in_array( $setting, array( 'submit_style', 'important_style', 'auto_width' ), true ) && ! isset( $new_instance['post_content'][ $setting ] ) ) {
					$new_instance['post_content'][ $setting ] = 0;
				} elseif ( $setting === 'font' ) {
					$new_instance['post_content'][ $setting ] = $this->force_balanced_quotation( $new_instance['post_content'][ $setting ] );
				}
			}

			$action_ids[] = $this->save( $new_instance );
		}//end foreach

		$this->save_settings();

		return $action_ids;
	}

	/**
	 * Sanitize custom color values and convert it to valid one filling missing values.
	 *
	 * @since 5.3.2
	 *
	 * @param string $color_val The color value, by reference.
	 * @return void
	 */
	private function maybe_sanitize_rgba_value( &$color_val ) {
		if ( preg_match( '/(rgb|rgba)\(/', $color_val ) !== 1 ) {
			return;
		}

		$color_val = trim( $color_val );
		// Remove leading braces so (rgba(1,1,1,1) doesn't cause inconsistent braces.
		$color_val = ltrim( $color_val, '(' );
		$patterns  = array( '/rgba\((\s*\d+\s*,){3}[[0-1]\.]+\)/', '/rgb\((\s*\d+\s*,){2}\s*[\d]+\)/' );
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $color_val ) === 1 ) {
				return;
			}
		}

		// Remove all leading ')' braces, then add one back. This way there's always a single brace.
		$color_val  = rtrim( $color_val, ')' );
		$color_val .= ')';

		$color_rgba = substr( $color_val, strpos( $color_val, '(' ) + 1, strlen( $color_val ) - strpos( $color_val, '(' ) - 2 );
		// Remove any excessive braces from the rgba like rgba((.
		$color_rgba            = trim( $color_rgba, '()' );
		$length_of_color_codes = strpos( $color_val, '(' );
		$new_color_values      = array();

		// replace empty values by 0 or 1 (if alpha position).
		foreach ( explode( ',', $color_rgba ) as $index => $value ) {
			$new_value             = null;
			$value_is_empty_string = '' === trim( $value ) || '' === $value;

			if ( 3 === $length_of_color_codes || ( $index !== $length_of_color_codes - 1 ) ) {
				// Insert a value for r, g, or b.
				if ( $value < 0 ) {
					$new_value = 0;
				} elseif ( $value > 255 ) {
					$new_value = 255;
				} elseif ( $value_is_empty_string ) {
					$new_value = 0;
				} else {
					$new_value = absint( $value );
				}
			} else {
				// Insert a value for alpha.
				if ( $value_is_empty_string ) {
					$new_value = 4 === $length_of_color_codes ? 1 : 0;
				} elseif ( $value > 1 || $value < 0 ) {
					$new_value = 1;
				} else {
					$new_value = floatval( $value );
				}
			}//end if

			$new_color_values[] = null === $new_value ? $value : $new_value;
		}//end foreach

		// add more 0s and 1 (if alpha position) if needed.
		$missing_values = $length_of_color_codes - count( $new_color_values );
		if ( $missing_values > 1 ) {
			$insert_values = array_fill( 0, $missing_values - 1, 0 );
			$last_value    = 4 === $length_of_color_codes ? 1 : 0;
			array_push( $insert_values, $last_value );
		} elseif ( $missing_values === 1 ) {
			$insert_values = 4 === $length_of_color_codes ? array( 1 ) : array( 0 );
		}
		if ( ! empty( $insert_values ) ) {
			$new_color_values = array_merge( $new_color_values, $insert_values );
		}

		$new_color = implode( ',', $new_color_values );
		$prefix    = substr( $color_val, 0, strpos( $color_val, '(' ) + 1 );
		// Limit the number of opening braces after rgb/rgba. There should only be one.
		$prefix    = rtrim( $prefix, '(' ) . '(';
		$new_color = $prefix . $new_color . ')';

		$color_val = $new_color;
	}

	/**
	 * @since 5.0.13
	 *
	 * @param array $settings
	 * @return array
	 */
	public function sanitize_post_content( $settings ) {
		$defaults           = $this->get_defaults();
		$valid_keys         = array_keys( $defaults );
		$sanitized_settings = array();
		foreach ( $valid_keys as $key ) {
			if ( isset( $settings[ $key ] ) ) {
				$sanitized_settings[ $key ] = sanitize_textarea_field( $settings[ $key ] );
			} else {
				$sanitized_settings[ $key ] = $defaults[ $key ];
			}

			if ( 'custom_css' !== $key ) {
				$sanitized_settings[ $key ] = $this->strip_invalid_characters( $sanitized_settings[ $key ] );
			}
		}
		return $sanitized_settings;
	}

	/**
	 * Remove any characters that should not be used in CSS.
	 *
	 * @since 6.2.3
	 *
	 * @param string $setting
	 * @return string
	 */
	private function strip_invalid_characters( $setting ) {
		$characters_to_remove = array( '{', '}', ';', '[', ']' );

		// RGB is handled instead in self::maybe_sanitize_rgba_value.
		if ( 0 !== strpos( $setting, 'rgb' ) ) {
			$setting = $this->maybe_fix_braces( $setting, $characters_to_remove );
		}

		return str_replace( $characters_to_remove, '', $setting );
	}

	/**
	 * @since 6.2.3
	 *
	 * @param string $setting
	 * @param array  $characters_to_remove
	 * @return string
	 */
	private function maybe_fix_braces( $setting, &$characters_to_remove ) {
		$number_of_opening_braces = substr_count( $setting, '(' );
		$number_of_closing_braces = substr_count( $setting, ')' );

		if ( $number_of_opening_braces === $number_of_closing_braces ) {
			return $this->trim_braces( $setting );
		}

		if ( $this->should_remove_every_brace( $setting ) ) {
			// Add to $characters_to_remove to remove when str_replace is called.
			array_push( $characters_to_remove, '(', ')' );
			return $setting;
		}

		return $this->trim_braces( $setting );
	}

	/**
	 * @since 6.2.3
	 *
	 * @param string $input
	 * @return string
	 */
	private function trim_braces( $input ) {
		$output = $input;
		// Remove any ( from the start of the string as no CSS values expect at the first character.
		if ( $output && in_array( $output[0], array( '(', ')' ), true ) ) {
			$output = ltrim( $output, '()' );
		}
		// Remove extra braces from the end.
		if ( in_array( substr( $output, -1 ), array( '(', ')' ), true ) ) {
			$output = rtrim( $output, '()' );
			if ( false !== strpos( $output, '(' ) ) {
				$output .= ')';
			}
		}
		return $output;
	}

	/**
	 * @since 6.2.3
	 *
	 * @param string $setting
	 * @return bool
	 */
	private function should_remove_every_brace( $setting ) {
		if ( 0 === strpos( trim( $setting, '()' ), 'calc' ) ) {
			// Support calc() sizes. We do not want to remove all braces when calc is used.
			return false;
		}

		// Matches hex values but also checks for unexpected ( and ).
		$looks_like_a_hex_value = preg_match( '/^(?:\()?(?!#?[a-fA-F0-9]*[^\(#\)\da-fA-F])[a-fA-F0-9\(\)]*(?:\))?$/', $setting );
		if ( $looks_like_a_hex_value ) {
			return true;
		}

		// Matches size values but also checks for unexpected ( and ).
		// This is case insensitive so it will catch PX, PT, etc, as well.
		$looks_like_a_size = preg_match( '/\(?[+-]?\d*\.?\d+(?:px|%|em|rem|ex|pt|pc|mm|cm|in)\)?/i', $setting );
		if ( $looks_like_a_size ) {
			return true;
		}

		return false;
	}

	/**
	 * @since 3.01.01
	 *
	 * @param string $setting
	 * @return bool
	 */
	private function is_color( $setting ) {
		$extra_colors = array( 'error_bg', 'error_border', 'error_text' );
		return strpos( $setting, 'color' ) !== false || in_array( $setting, $extra_colors, true );
	}

	/**
	 * @since 3.01.01
	 *
	 * @return array
	 */
	public function get_color_settings() {
		$defaults = $this->get_defaults();
		$settings = array_keys( $defaults );

		return array_filter( $settings, array( $this, 'is_color' ) );
	}

	/**
	 * Create static CSS file and update the CSS transient alternative.
	 *
	 * @return void
	 */
	public function save_settings() {
		$filename = FrmAppHelper::plugin_path() . '/css/custom_theme.css.php';
		update_option( 'frm_last_style_update', gmdate( 'njGi' ) );

		if ( ! is_file( $filename ) ) {
			return;
		}

		$this->clear_cache();

		$css         = $this->get_css_content( $filename );
		$create_file = new FrmCreateFile(
			array(
				'file_name'     => FrmStylesController::get_file_name(),
				'new_file_path' => FrmAppHelper::plugin_path() . '/css',
			)
		);
		$create_file->create_file( $css );

		update_option( 'frmpro_css', $css, 'no' );
		set_transient( 'frmpro_css', $css, MONTH_IN_SECONDS );
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	private function get_css_content( $filename ) {
		$css = '/* ' . __( 'WARNING: Any changes made to this file will be lost when your Formidable settings are updated', 'formidable' ) . ' */' . "\n";

		$saving    = true;
		$frm_style = $this;

		ob_start();
		include $filename;
		$css .= preg_replace( '/\/\*(.|\s)*?\*\//', '', str_replace( array( "\r\n", "\r", "\n", "\t", '    ' ), '', ob_get_contents() ) );
		ob_end_clean();

		return FrmStylesController::replace_relative_url( $css );
	}

	/**
	 * @return void
	 */
	private function clear_cache() {
		$default_post_atts = array(
			'post_type'   => FrmStylesController::$post_type,
			'post_status' => 'publish',
			'numberposts' => 99,
			'orderby'     => 'title',
			'order'       => 'ASC',
		);

		FrmDb::delete_cache_and_transient( json_encode( $default_post_atts ), 'frm_styles' );
		FrmDb::cache_delete_group( 'frm_styles' );
		FrmDb::delete_cache_and_transient( 'frmpro_css' );
	}

	/**
	 * Delete a style by its post ID.
	 *
	 * @param int $id
	 * @return false|WP_Post|null
	 */
	public function destroy( $id ) {
		if ( $id === $this->get_default_style()->ID ) {
			return false;
		}
		return wp_delete_post( $id );
	}

	/**
	 * @return stdClass|WP_Post
	 */
	public function get_one() {
		if ( 'default' === $this->id ) {
			$style = $this->get_default_style();
			if ( $style ) {
				$this->id = $style->ID;
			} else {
				$this->id = 0;
			}

			return $style;
		}

		$style = get_post( $this->id );

		if ( ! $style ) {
			return $style;
		}

		$style->post_content = FrmAppHelper::maybe_json_decode( $style->post_content );

		$default_values = $this->get_defaults();

		// fill default values
		$style->post_content = $this->override_defaults( $style->post_content );
		$style->post_content = wp_parse_args( $style->post_content, $default_values );

		return $style;
	}

	/**
	 * @param string $orderby
	 * @param string $order
	 * @param int    $limit
	 * @return array
	 */
	public function get_all( $orderby = 'title', $order = 'ASC', $limit = 99 ) {
		$post_atts = array(
			'post_type'   => FrmStylesController::$post_type,
			'post_status' => 'publish',
			'numberposts' => $limit,
			'orderby'     => $orderby,
			'order'       => $order,
		);

		$temp_styles = FrmDb::check_cache( json_encode( $post_atts ), 'frm_styles', $post_atts, 'get_posts' );

		if ( empty( $temp_styles ) ) {
			global $wpdb;
			// make sure there wasn't a conflict with the query
			$query       = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->posts . ' WHERE post_type=%s AND post_status=%s ORDER BY post_title ASC LIMIT 99', FrmStylesController::$post_type, 'publish' );
			$temp_styles = FrmDb::check_cache( 'frm_backup_style_check', 'frm_styles', $query, 'get_results' );

			if ( empty( $temp_styles ) ) {
				// create a new style if there are none
				$new             = $this->get_new();
				$new->post_title = __( 'Formidable Style', 'formidable' );
				$new->post_name  = $new->post_title;
				$new->menu_order = 1;
				$new             = $this->save( (array) $new );
				$this->update( 'default' );

				$post_atts['include'] = $new;

				$temp_styles = get_posts( $post_atts );
			}
		}

		$default_values = $this->get_defaults();
		$default_style  = false;

		$styles = array();
		foreach ( $temp_styles as $style ) {
			$this->id = $style->ID;
			if ( $style->menu_order ) {
				if ( $default_style ) {
					// only return one default
					$style->menu_order = 0;
				} else {
					// check for a default style
					$default_style = $style->ID;
				}
			}

			$style->post_content = FrmAppHelper::maybe_json_decode( $style->post_content );

			// fill default values
			$style->post_content = $this->override_defaults( $style->post_content );
			$style->post_content = wp_parse_args( $style->post_content, $default_values );

			$styles[ $style->ID ] = $style;
		}

		if ( ! $default_style ) {
			$default_style = reset( $styles );

			$styles[ $default_style->ID ]->menu_order = 1;
		}

		return $styles;
	}

	/**
	 * @param array|null $styles
	 */
	public function get_default_style( $styles = null ) {
		if ( ! isset( $styles ) ) {
			$styles = $this->get_all( 'menu_order', 'DESC', 1 );
		}

		foreach ( $styles as $style ) {
			if ( $style->menu_order ) {
				return $style;
			}
		}
	}

	/**
	 * @param mixed $settings
	 * @return mixed
	 */
	public function override_defaults( $settings ) {
		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		$settings['line_height'] = ! isset( $settings['field_height'] ) || $settings['field_height'] == '' || $settings['field_height'] === 'auto' ? 'normal' : $settings['field_height'];

		if ( ! isset( $settings['form_desc_size'] ) && isset( $settings['description_font_size'] ) ) {
			$settings['form_desc_size']  = $settings['description_font_size'];
			$settings['form_desc_color'] = $settings['description_color'];
			$settings['title_color']     = $settings['label_color'];
		}

		if ( ! isset( $settings['section_color'] ) && isset( $settings['label_color'] ) ) {
			$settings['section_color']        = $settings['label_color'];
			$settings['section_border_color'] = $settings['border_color'];
		}

		if ( ! isset( $settings['submit_hover_bg_color'] ) && isset( $settings['submit_bg_color'] ) ) {
			$settings['submit_hover_bg_color']     = $settings['submit_bg_color'];
			$settings['submit_hover_color']        = $settings['submit_text_color'];
			$settings['submit_hover_border_color'] = $settings['submit_border_color'];

			$settings['submit_active_bg_color']     = $settings['submit_bg_color'];
			$settings['submit_active_color']        = $settings['submit_text_color'];
			$settings['submit_active_border_color'] = $settings['submit_border_color'];
		}

		return apply_filters( 'frm_override_default_styles', $settings );
	}

	/**
	 * @return array
	 */
	public function get_defaults() {
		$defaults = array(
			'theme_css'                  => 'ui-lightness',
			'theme_name'                 => 'UI Lightness',

			'center_form'                => '',
			'form_width'                 => '100%',
			'form_align'                 => 'left',
			'direction'                  => is_rtl() ? 'rtl' : 'ltr',
			'fieldset'                   => '0px',
			'fieldset_color'             => '000000',
			'fieldset_padding'           => '0 0 15px 0',
			'fieldset_bg_color'          => '',

			'title_size'                 => '40px',
			'title_color'                => '444444',
			'title_margin_top'           => '10px',
			'title_margin_bottom'        => '60px',
			'form_desc_size'             => '14px',
			'form_desc_color'            => '666666',
			'form_desc_margin_top'       => '10px',
			'form_desc_margin_bottom'    => '25px',
			'form_desc_padding'          => '0',

			'font'                       => '',
			'font_size'                  => '15px',
			'label_color'                => '3f4b5b',
			'weight'                     => 'normal',
			'position'                   => 'none',
			'align'                      => 'left',
			'width'                      => '150px',
			'required_color'             => 'B94A48',
			'required_weight'            => 'bold',
			'label_padding'              => '0 0 3px 0',

			'description_font_size'      => '12px',
			'description_color'          => '666666',
			'description_weight'         => 'normal',
			'description_style'          => 'normal',
			'description_align'          => 'left',
			'description_margin'         => '0',

			'field_font_size'            => '14px',
			'field_height'               => '32px',
			'line_height'                => 'normal',
			'field_width'                => '100%',
			'auto_width'                 => false,
			'field_pad'                  => '6px 10px',
			'field_margin'               => '20px',
			'field_weight'               => 'normal',
			'text_color'                 => '555555',
			// 'border_color_hv'   => 'cccccc',
			'border_color'               => 'BFC3C8',
			'field_border_width'         => '1px',
			'field_border_style'         => 'solid',

			'bg_color'                   => 'ffffff',
			// 'bg_color_hv'       => 'ffffff',
			'remove_box_shadow'          => '',
			'bg_color_active'            => 'ffffff',
			'border_color_active'        => '66afe9',
			'remove_box_shadow_active'   => '',
			'text_color_error'           => '444444',
			'bg_color_error'             => 'ffffff',
			'border_color_error'         => 'B94A48',
			'border_width_error'         => '1px',
			'border_style_error'         => 'solid',
			'bg_color_disabled'          => 'ffffff',
			'border_color_disabled'      => 'E5E5E5',
			'text_color_disabled'        => 'A1A1A1',

			'radio_align'                => 'block',
			'check_align'                => 'block',
			'check_font_size'            => '13px',
			'check_label_color'          => '444444',
			'check_weight'               => 'normal',

			'section_font_size'          => '18px',
			'section_color'              => '444444',
			'section_weight'             => 'bold',
			'section_pad'                => '15px 0 3px 0',
			'section_mar_top'            => '15px',
			'section_mar_bottom'         => '30px',
			'section_bg_color'           => '',
			'section_border_color'       => 'e8e8e8',
			'section_border_width'       => '2px',
			'section_border_style'       => 'solid',
			'section_border_loc'         => '-top',
			'collapse_icon'              => '6',
			'collapse_pos'               => 'after',
			'repeat_icon'                => '1',
			'repeat_icon_color'          => 'ffffff',

			'submit_style'               => false,
			'submit_font_size'           => '15px',
			'submit_width'               => 'auto',
			'submit_height'              => 'auto',
			'submit_bg_color'            => '579AF6',
			'submit_border_color'        => '579AF6',
			'submit_border_width'        => '1px',
			'submit_text_color'          => 'ffffff',
			'submit_weight'              => 'normal',
			'submit_border_radius'       => '4px',
			'submit_bg_img'              => '',
			'submit_margin'              => '10px',
			'submit_padding'             => '10px 20px',
			'submit_shadow_color'        => 'eeeeee',
			'submit_hover_bg_color'      => 'efefef',
			'submit_hover_color'         => '444444',
			'submit_hover_border_color'  => 'cccccc',
			'submit_active_bg_color'     => 'efefef',
			'submit_active_color'        => '444444',
			'submit_active_border_color' => 'cccccc',

			'border_radius'              => '4px',
			'error_bg'                   => 'F2DEDE',
			'error_border'               => 'EBCCD1',
			'error_text'                 => 'B94A48',
			'error_font_size'            => '14px',

			'success_bg_color'           => 'DFF0D8',
			'success_border_color'       => 'D6E9C6',
			'success_text_color'         => '468847',
			'success_font_size'          => '14px',

			'important_style'            => false,

			'progress_bg_color'          => 'eaeaea',
			'progress_active_color'      => 'ffffff',
			'progress_active_bg_color'   => '579AF6',
			'progress_color'             => '3f4b5b',
			'progress_border_color'      => 'E5E5E5',
			'progress_border_size'       => '2px',
			'progress_size'              => '24px',
			'custom_css'                 => '',
		);

		return apply_filters( 'frm_default_style_settings', $defaults );
	}

	/**
	 * Get a name attribute value for a style setting input.
	 *
	 * @param string $field_name
	 * @param string $post_field
	 * @return string
	 */
	public function get_field_name( $field_name, $post_field = 'post_content' ) {
		return 'frm_style_setting' . ( empty( $post_field ) ? '' : '[' . $post_field . ']' ) . '[' . $field_name . ']';
	}

	/**
	 * @return array
	 */
	public static function get_bold_options() {
		return array(
			100      => 100,
			200      => 200,
			300      => 300,
			'normal' => __( 'normal', 'formidable' ),
			500      => 500,
			600      => 600,
			'bold'   => __( 'bold', 'formidable' ),
			800      => 800,
			900      => 900,
		);
	}

	/**
	 * Don't let imbalanced font families ruin the whole stylesheet.
	 *
	 * @param string $value
	 * @return string
	 */
	public function force_balanced_quotation( $value ) {
		$balanced_characters = array( '"', "'" );
		foreach ( $balanced_characters as $char ) {
			$char_count  = substr_count( $value, $char );
			$is_balanced = $char_count % 2 == 0;

			if ( $is_balanced ) {
				continue;
			}

			if ( $value && $char === $value[ strlen( $value ) - 1 ] ) {
				$value = $char . $value;
			} else {
				$value .= $char;
			}
		}
		return $value;
	}
}
