<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFieldsHelper {

	public static function setup_new_vars( $type = '', $form_id = '' ) {

		if ( strpos( $type, '|' ) ) {
			list( $type, $setting ) = explode( '|', $type );
		}

		$values = self::get_default_field( $type );

		global $wpdb;
		$field_count = FrmDb::get_var( 'frm_fields', array( 'form_id' => $form_id ), 'field_order', array( 'order_by' => 'field_order DESC' ) );

		$values['field_key']   = FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_fields', 'field_key' );
		$values['form_id']     = $form_id;
		$values['field_order'] = $field_count + 1;

		$values['field_options']['custom_html'] = self::get_default_html( $type );

		if ( isset( $setting ) && ! empty( $setting ) ) {
			if ( in_array( $type, array( 'data', 'lookup' ) ) ) {
				$values['field_options']['data_type'] = $setting;
			} else {
				$values['field_options'][ $setting ] = 1;
			}
		}

		return $values;
	}

	public static function get_html_id( $field, $plus = '' ) {
		return apply_filters( 'frm_field_html_id', 'field_' . $field['field_key'] . $plus, $field );
	}

	public static function setup_edit_vars( $field, $doing_ajax = false ) {
		$values = self::field_object_to_array( $field );

		return apply_filters( 'frm_setup_edit_field_vars', $values, array( 'doing_ajax' => $doing_ajax ) );
	}

	public static function field_object_to_array( $field ) {
		$values = (array) $field;

		self::fill_field_array( $field, $values );

		$values['custom_html'] = ( isset( $field->field_options['custom_html'] ) ) ? $field->field_options['custom_html'] : self::get_default_html( $field->type );

		return $values;
	}

	private static function fill_field_array( $field, array &$field_array ) {
		$field_array['options'] = $field->options;
		$field_array['value']   = $field->default_value;

		self::prepare_edit_front_field( $field_array, $field );

		$field_array = array_merge( (array) $field->field_options, $field_array );
	}

	/**
	 * Prepare field while creating a new entry
	 *
	 * @since 3.0
	 */
	public static function prepare_new_front_field( &$field_array, $field, $args = array() ) {
		$args['action'] = 'new';
		self::prepare_front_field( $field_array, $field, $args );
	}

	/**
	 * Prepare field while editing an entry
	 *
	 * @since 3.0
	 */
	public static function prepare_edit_front_field( &$field_array, $field, $entry_id = 0, $args = array() ) {
		$args['entry_id'] = $entry_id;
		$args['action']   = 'edit';
		self::prepare_front_field( $field_array, $field, $args );
	}

	/**
	 * Prepare field while creating a new entry
	 *
	 * @since 3.0
	 */
	private static function prepare_front_field( &$field_array, $field, $args ) {
		self::fill_default_field_opts( $field, $field_array );
		self::fill_cleared_strings( $field, $field_array );

		// Track the original field's type
		$field_array['original_type'] = isset( $field->field_options['original_type'] ) ? $field->field_options['original_type'] : $field->type;

		self::prepare_field_options_for_display( $field_array, $field, $args );

		if ( $args['action'] == 'edit' ) {
			$field_array = apply_filters( 'frm_setup_edit_fields_vars', $field_array, $field, $args['entry_id'], $args );
		} else {
			$field_array = apply_filters( 'frm_setup_new_fields_vars', $field_array, $field, $args );
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function get_default_field_options( $type ) {
		$field_type = FrmFieldFactory::get_field_type( $type );

		return $field_type->get_default_field_options();
	}

	/**
	 * @since 3.0
	 *
	 * @param object $field
	 * @param array $values
	 */
	private static function fill_default_field_opts( $field, array &$values ) {
		$check_post = FrmAppHelper::is_admin_page() && $_POST && isset( $_POST['field_options'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$defaults = self::get_default_field_options_from_field( $field, $values );
		if ( ! $check_post ) {
			$defaults['required_indicator'] = '';
			$defaults['original_type']      = $field->type;
		}

		foreach ( $defaults as $opt => $default ) {
			$values[ $opt ] = isset( $field->field_options[ $opt ] ) ? $field->field_options[ $opt ] : $default;

			if ( $check_post ) {
				self::get_posted_field_setting( $opt . '_' . $field->id, $values[ $opt ] );
			}

			unset( $opt, $default );
		}
	}

	/**
	 * Fill the required message, invalid message,
	 * and refill the HTML when cleared
	 *
	 * @since 3.0
	 *
	 * @param object $field
	 * @param array $field_array
	 */
	private static function fill_cleared_strings( $field, array &$field_array ) {
		$frm_settings = FrmAppHelper::get_settings();

		if ( '' == $field_array['blank'] && '1' === $field_array['required'] ) {
			$field_array['blank'] = $frm_settings->blank_msg;
		}

		if ( '' === $field_array['invalid'] ) {
			if ( 'captcha' === $field->type ) {
				$field_array['invalid'] = $frm_settings->re_msg;
			} else {
				/* translators: %s: Field name */
				$field_array['invalid'] = sprintf( __( '%s is invalid', 'formidable' ), $field_array['name'] );
			}
		}

		if ( '' == $field_array['custom_html'] ) {
			$field_array['custom_html'] = self::get_default_html( $field->type );
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param string $setting
	 * @param mixed $value
	 */
	private static function get_posted_field_setting( $setting, &$value ) {
		if ( ! isset( $_POST['field_options'][ $setting ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		if ( strpos( $setting, 'html' ) !== false ) {
			// Strip slashes from HTML but not regex or script tags.
			$value = wp_unslash( $_POST['field_options'][ $setting ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		} elseif ( strpos( $setting, 'format_' ) === 0 ) {
			// TODO: Remove stripslashes on output, and use on input only.
			$value = sanitize_text_field( $_POST['field_options'][ $setting ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Missing
		} else {
			$value = wp_unslash( $_POST['field_options'][ $setting ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			FrmAppHelper::sanitize_value( 'wp_kses_post', $value );
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param object $field
	 * @param array $values The field array is needed for hooks
	 *
	 * @return array
	 */
	public static function get_default_field_options_from_field( $field, $values = array() ) {
		$field_type = self::get_original_field( $field );
		$opts       = $field_type->get_default_field_options();

		$opts = apply_filters( 'frm_default_field_opts', $opts, $values, $field );
		$opts = apply_filters( 'frm_default_' . $field->type . '_field_opts', $opts, $values, $field );

		return $opts;
	}

	/**
	 * @since 3.0
	 *
	 * @param object $field
	 *
	 * @return array
	 */
	private static function get_original_field( $field ) {
		$original_type = FrmField::get_option( $field, 'original_type' );
		if ( ! empty( $original_type ) && $field->type != $original_type ) {
			$field->type = $original_type;
		}

		return FrmFieldFactory::get_field_object( $field );
	}

	/**
	 * @since 3.0
	 *
	 * @param array $field_array
	 * @param object $field
	 * @param array $atts
	 */
	private static function prepare_field_options_for_display( &$field_array, $field, $atts ) {
		$field_obj   = FrmFieldFactory::get_field_object( $field );
		$field_array = $field_obj->prepare_front_field( $field_array, $atts );
	}

	/**
	 * @since 3.0
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function get_default_field( $type ) {
		$field_type = FrmFieldFactory::get_field_type( $type );

		return $field_type->get_new_field_defaults();
	}

	public static function fill_field( &$values, $field, $form_id, $new_key = '' ) {
		global $wpdb;

		$values['field_key']     = FrmAppHelper::get_unique_key( $new_key, $wpdb->prefix . 'frm_fields', 'field_key' );
		$values['form_id']       = $form_id;
		$values['options']       = maybe_serialize( $field->options );
		$values['default_value'] = FrmAppHelper::maybe_json_encode( $field->default_value );

		foreach ( array( 'name', 'description', 'type', 'field_order', 'field_options', 'required' ) as $col ) {
			$values[ $col ] = $field->{$col};
		}
	}

	/**
	 * @since 2.0
	 *
	 * @param $field
	 * @param $error
	 *
	 * @return string
	 */
	public static function get_error_msg( $field, $error ) {
		$frm_settings     = FrmAppHelper::get_settings();
		$default_settings = $frm_settings->default_options();
		$field_name       = is_array( $field ) ? $field['name'] : $field->name;

		$conf_msg = __( 'The entered values do not match', 'formidable' );
		$defaults = array(
			'unique_msg' => array(
				'full' => $default_settings['unique_msg'],
				/* translators: %s: Field name */
				'part' => sprintf( __( '%s must be unique', 'formidable' ), $field_name ),
			),
			'invalid'    => array(
				'full' => __( 'This field is invalid', 'formidable' ),
				/* translators: %s: Field name */
				'part' => sprintf( __( '%s is invalid', 'formidable' ), $field_name ),
			),
			'blank'      => array(
				'full' => $frm_settings->blank_msg,
				'part' => $frm_settings->blank_msg,
			),
			'conf_msg'   => array(
				'full' => $conf_msg,
				'part' => $conf_msg,
			),
		);

		$msg = FrmField::get_option( $field, $error );
		$msg = empty( $msg ) ? $defaults[ $error ]['part'] : $msg;
		$msg = do_shortcode( $msg );

		return $msg;
	}

	public static function get_form_fields( $form_id, $error = array() ) {
		$fields = FrmField::get_all_for_form( $form_id );

		return apply_filters( 'frm_get_paged_fields', $fields, $form_id, $error );
	}

	public static function get_default_html( $type = 'text' ) {
		$field        = FrmFieldFactory::get_field_type( $type );
		$default_html = $field->default_html();

		// these hooks are here for reverse compatibility since 3.0
		if ( ! apply_filters( 'frm_normal_field_type_html', true, $type ) ) {
			$default_html = apply_filters( 'frm_other_custom_html', '', $type );
		}

		return apply_filters( 'frm_custom_html', $default_html, $type );
	}

	/**
	 * @param array $fields
	 * @param array $errors
	 * @param object $form
	 * @param $form_action
	 */
	public static function show_fields( $fields, $errors, $form, $form_action ) {
		foreach ( $fields as $field ) {
			$field_obj = FrmFieldFactory::get_field_type( $field['type'], $field );
			$field_obj->show_field( compact( 'errors', 'form', 'form_action' ) );
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 * @param string|array $value
	 */
	public static function run_wpautop( $atts, &$value ) {
		$autop = isset( $atts['wpautop'] ) ? $atts['wpautop'] : true;
		if ( apply_filters( 'frm_use_wpautop', $autop ) ) {
			if ( is_array( $value ) ) {
				$value = implode( "\n", $value );
			}
			$value = wpautop( $value );
		}
	}

	/**
	 * Get the class to use for the label position
	 *
	 * @since 2.05
	 */
	public static function &label_position( $position, $field, $form ) {
		if ( $position && $position != '' ) {
			if ( $position == 'inside' && ! self::is_placeholder_field_type( $field['type'] ) ) {
				$position = 'top';
			}

			return $position;
		}

		$position = FrmStylesController::get_style_val( 'position', $form );
		if ( $position == 'none' ) {
			$position = 'top';
		} elseif ( $position == 'no_label' ) {
			$position = 'none';
		} elseif ( $position == 'inside' && ! self::is_placeholder_field_type( $field['type'] ) ) {
			$position = 'top';
		}

		$position = apply_filters( 'frm_html_label_position', $position, $field, $form );
		$position = ( ! empty( $position ) ) ? $position : 'top';

		return $position;
	}

	/**
	 * Check if this field type allows placeholders
	 *
	 * @since 2.05
	 * @param string $type
	 * @return bool
	 */
	public static function is_placeholder_field_type( $type ) {
		return ! in_array( $type, array( 'radio', 'checkbox', 'hidden', 'file' ), true );
	}

	public static function get_checkbox_id( $field, $opt_key, $type = 'checkbox' ) {
		$id = $field['id'];
		if ( isset( $field['in_section'] ) && $field['in_section'] && ! FrmAppHelper::is_admin_page( 'formidable' ) ) {
			$id .= '-' . $field['in_section'];
		}

		return 'frm_' . $type . '_' . $id . '-' . $opt_key;
	}

	public static function show_single_option( $field ) {
		self::hidden_field_option( $field );

		if ( ! is_array( $field['options'] ) ) {
			return;
		}

		$base_name = 'default_value_' . $field['id'];
		$html_id    = isset( $field['html_id'] ) ? $field['html_id'] : self::get_html_id( $field );

		$default_type = self::get_default_value_type( $field );

		foreach ( $field['options'] as $opt_key => $opt ) {
			$field_val = self::get_value_from_array( $opt, $opt_key, $field );
			$opt       = self::get_label_from_array( $opt, $opt_key, $field );

			$field_name = $base_name . ( $default_type === 'checkbox' ? '[' . $opt_key . ']' : '' );

			$checked = ( isset( $field['default_value'] ) && ( ( ! is_array( $field['default_value'] ) && $field['default_value'] == $field_val ) || ( is_array( $field['default_value'] ) && in_array( $field_val, $field['default_value'] ) ) ) );

			// If this is an "Other" option, get the HTML for it.
			if ( self::is_other_opt( $opt_key ) ) {
				if ( FrmAppHelper::pro_is_installed() ) {
					require( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/other-option.php' );
				}
			} else {
				require( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/single-option.php' );
			}

			unset( $checked );
		}
	}

	/**
	 * Include hidden row for javascript to duplicate.
	 *
	 * @since 4.0
	 * @param array $field
	 */
	private static function hidden_field_option( $field ) {
		// Don't duplicate during an ajax add option.
		$ajax_action = FrmAppHelper::get_param( 'action', '', 'post', 'sanitize_text_field' );
		if ( $ajax_action === 'frm_add_field_option' ) {
			return;
		}

		$opt_key    = '000';
		$field_val  = __( 'New Option', 'formidable' );
		$opt        = __( 'New Option', 'formidable' );
		$checked    = false;
		$field_name = 'default_value_' . $field['id'];
		$html_id    = isset( $field['html_id'] ) ? $field['html_id'] : self::get_html_id( $field );

		$default_type = self::get_default_value_type( $field );
		$field_name  .= ( $default_type === 'checkbox' ? '[' . $opt_key . ']' : '' );

		require( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/single-option.php' );
	}

	/**
	 * @since 4.0
	 *
	 * @param array $field
	 * @return string radio or checkbox
	 */
	private static function get_default_value_type( $field ) {
		$default_type = $field['type'];
		if ( $field['type'] === 'select' ) {
			$default_type = FrmField::is_multiple_select( $field ) ? 'checkbox' : 'radio';
		}
		return $default_type;
	}

	public static function get_value_from_array( $opt, $opt_key, $field ) {
		$opt = apply_filters( 'frm_field_value_saved', $opt, $opt_key, $field );

		return FrmFieldsController::check_value( $opt, $opt_key, $field );
	}

	public static function get_label_from_array( $opt, $opt_key, $field ) {
		$opt = apply_filters( 'frm_field_label_seen', $opt, $opt_key, $field );

		return FrmFieldsController::check_label( $opt );
	}

	/**
	 * @since 4.0
	 */
	public static function inline_modal( $args ) {
		$defaults = array(
			'id'       => '',
			'class'    => '',
			'show'     => 0,
			'callback' => array(),
			'args'     => array(),
			'title'    => '',
		);
		$args = array_merge( $defaults, $args );

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/inline-modal.php' );
	}

	/**
	 * @since 4.0
	 */
	public static function smart_values() {
		$continue = apply_filters( 'frm_smart_values_box', true );
		if ( $continue === true ) {
			$upgrade_link = array(
				'medium'  => 'builder',
				'content' => 'smart-tags',
			);
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/smart-values.php' );
		}
	}

	/**
	 * @since 4.0
	 */
	public static function input_mask() {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/input-mask-info.php' );
	}

	/**
	 * @since 4.0
	 */
	public static function layout_classes() {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/layout-classes.php' );
	}

	/**
	 * @param int $tax_id
	 *
	 * @return string
	 */
	public static function get_term_link( $tax_id ) {
		$tax = get_taxonomy( $tax_id );
		if ( ! $tax ) {
			return '';
		}

		$link = sprintf(
			/* translators: %1$s: Start HTML link, %2$s: Content type label, %3$s: Content type, %4$s: End HTML link */
			esc_html__( 'Options are dynamically created from your %1$s%2$s: %3$s%4$s', 'formidable' ),
			'<a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=' . $tax->name ) ) . '" target="_blank">',
			esc_html__( 'taxonomy', 'formidable' ),
			empty( $tax->labels->name ) ? esc_html__( 'Categories', 'formidable' ) : $tax->labels->name,
			'</a>'
		);
		unset( $tax );

		return $link;
	}

	public static function value_meets_condition( $observed_value, $cond, $hide_opt ) {
		$hide_opt       = self::get_value_for_comparision( $hide_opt );
		$observed_value = self::get_value_for_comparision( $observed_value );

		if ( is_array( $observed_value ) ) {
			return self::array_value_condition( $observed_value, $cond, $hide_opt );
		}

		$m = false;
		if ( $cond === '==' ) {
			$m = $observed_value == $hide_opt;
		} elseif ( $cond === '!=' ) {
			$m = $observed_value != $hide_opt;
		} elseif ( $cond === '>' ) {
			$m = $observed_value > $hide_opt;
		} elseif ( $cond === '>=' ) {
			$m = $observed_value >= $hide_opt;
		} elseif ( $cond === '<' ) {
			$m = $observed_value < $hide_opt;
		} elseif ( $cond === '<=' ) {
			$m = $observed_value <= $hide_opt;
		} elseif ( $cond === 'LIKE' || $cond === 'not LIKE' ) {
			$m = stripos( $observed_value, $hide_opt );
			if ( $cond === 'not LIKE' ) {
				$m = ( $m === false ) ? true : false;
			} else {
				$m = ( $m === false ) ? false : true;
			}
		} elseif ( $cond === '%LIKE' ) {
			// ends with
			$length = strlen( $hide_opt );
			$substr = substr( $observed_value, strlen( $observed_value ) - $length );
			$m      = 0 === strcasecmp( $substr, $hide_opt );
		} elseif ( 'LIKE%' === $cond ) {
			// starts with
			$length = strlen( $hide_opt );
			$substr = substr( $observed_value, 0, $length );
			$m      = 0 === strcasecmp( $substr, $hide_opt );
		}

		return $m;
	}

	/**
	 * Trim and sanitize the values
	 *
	 * @since 2.05
	 */
	private static function get_value_for_comparision( $value ) {
		// Remove white space from hide_opt
		if ( ! is_array( $value ) ) {
			$value = trim( $value );
		}

		FrmAppHelper::sanitize_value( 'wp_kses_post', $value );

		return $value;
	}

	public static function array_value_condition( $observed_value, $cond, $hide_opt ) {
		$m = false;
		if ( $cond === '==' ) {
			if ( is_array( $hide_opt ) ) {
				$m = array_intersect( $hide_opt, $observed_value );
				$m = empty( $m ) ? false : true;
			} else {
				$m = in_array( $hide_opt, $observed_value );
			}
		} elseif ( $cond === '!=' ) {
			$m = ! in_array( $hide_opt, $observed_value );
		} elseif ( $cond === '>' ) {
			$min = min( $observed_value );
			$m   = $min > $hide_opt;
		} elseif ( $cond === '<' ) {
			$max = max( $observed_value );
			$m   = $max < $hide_opt;
		} elseif ( $cond === 'LIKE' || $cond === 'not LIKE' ) {
			foreach ( $observed_value as $ob ) {
				$m = strpos( $ob, $hide_opt );
				if ( $m !== false ) {
					$m = true;
					break;
				}
			}

			if ( $cond === 'not LIKE' ) {
				$m = ( $m === false ) ? true : false;
			}
		} elseif ( $cond === '%LIKE' ) {
			// ends with
			foreach ( $observed_value as $ob ) {
				if ( $hide_opt === substr( $ob, strlen( $ob ) - strlen( $hide_opt ) ) ) {
					$m = true;
					break;
				}
			}
		} elseif ( $cond === 'LIKE%' ) {
			// starts with
			foreach ( $observed_value as $ob ) {
				if ( $hide_opt === substr( $ob, 0, strlen( $hide_opt ) ) ) {
					$m = true;
					break;
				}
			}
		}

		return $m;
	}

	/**
	 * Replace a few basic shortcodes and field ids
	 *
	 * @since 2.0
	 * @return string
	 */
	public static function basic_replace_shortcodes( $value, $form, $entry ) {
		if ( strpos( $value, '[sitename]' ) !== false ) {
			$new_value = wp_specialchars_decode( FrmAppHelper::site_name(), ENT_QUOTES );
			$value     = str_replace( '[sitename]', $new_value, $value );
		}

		$value = apply_filters( 'frm_content', $value, $form, $entry );
		$value = do_shortcode( $value );

		return $value;
	}

	public static function get_shortcodes( $content, $form_id ) {
		if ( FrmAppHelper::pro_is_installed() ) {
			return FrmProDisplaysHelper::get_shortcodes( $content, $form_id );
		}

		$fields = FrmField::getAll(
			array(
				'fi.form_id'  => (int) $form_id,
				'fi.type not' => FrmField::no_save_fields(),
			)
		);

		$tagregexp = self::allowed_shortcodes( $fields );

		preg_match_all( "/\[(if )?($tagregexp)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $content, $matches, PREG_PATTERN_ORDER );

		return $matches;
	}

	public static function allowed_shortcodes( $fields = array() ) {
		$tagregexp = array(
			'editlink',
			'id',
			'key',
			'ip',
			'siteurl',
			'sitename',
			'admin_email',
			'post[-|_]id',
			'created[-|_]at',
			'updated[-|_]at',
			'updated[-|_]by',
			'parent[-|_]id',
		);

		foreach ( $fields as $field ) {
			$tagregexp[] = $field->id;
			$tagregexp[] = $field->field_key;
		}

		$tagregexp = implode( '|', $tagregexp );

		return $tagregexp;
	}

	public static function replace_content_shortcodes( $content, $entry, $shortcodes ) {
		foreach ( $shortcodes[0] as $short_key => $tag ) {
			if ( empty( $tag ) ) {
				continue;
			}

			$atts = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcodes[3][ $short_key ] );
			$tag  = FrmShortcodeHelper::get_shortcode_tag( $shortcodes, $short_key );

			$atts['entry'] = $entry;
			$atts['tag']   = $tag;
			$replace_with  = self::get_value_for_shortcode( $atts );

			if ( $replace_with !== null ) {
				$replace_with = self::trigger_shortcode_atts( $replace_with, $atts );
				self::sanitize_embedded_shortcodes( compact( 'entry' ), $replace_with );
				$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
			}

			unset( $atts, $replace_with );
		}

		return $content;
	}

	/**
	 * @param string $replace_with
	 * @param array  $atts
	 */
	private static function trigger_shortcode_atts( $replace_with, $atts ) {
		$supported_atts = array( 'sanitize', 'sanitize_url' );
		$included_atts  = array_intersect( $supported_atts, array_keys( $atts ) );
		foreach ( $included_atts as $included_att ) {
			$function     = 'atts_' . $included_att;
			$replace_with = self::$function( $replace_with, $atts );
		}
		return $replace_with;
	}

	/**
	 * @param string $replace_with
	 * @return string
	 */
	private static function atts_sanitize( $replace_with ) {
		return sanitize_title_with_dashes( $replace_with );
	}

	/**
	 * @param string $replace_with
	 * @return string
	 */
	private static function atts_sanitize_url( $replace_with ) {
		return urlencode( $replace_with );
	}

	/**
	 * Prevent shortcodes in fields from being processed
	 *
	 * @since 3.01.02
	 *
	 * @param array $atts - includes entry object
	 * @param string $value
	 */
	public static function sanitize_embedded_shortcodes( $atts, &$value ) {
		$atts['value']   = $value;
		$should_sanitize = apply_filters( 'frm_sanitize_shortcodes', true, $atts );
		if ( $should_sanitize ) {
			$value = str_replace( '[', '&#91;', $value );
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	private static function get_value_for_shortcode( $atts ) {
		$clean_tag = str_replace( '-', '_', $atts['tag'] );

		$shortcode_values = array(
			'id'  => $atts['entry']->id,
			'key' => $atts['entry']->item_key,
			'ip'  => $atts['entry']->ip,
		);

		$dynamic_default = array( 'admin_email', 'siteurl', 'frmurl', 'sitename', 'get' );

		if ( isset( $shortcode_values[ $atts['tag'] ] ) ) {
			$replace_with = $shortcode_values[ $atts['tag'] ];
		} elseif ( in_array( $atts['tag'], $dynamic_default ) ) {
			$replace_with = self::dynamic_default_values( $atts['tag'], $atts );
		} elseif ( $clean_tag == 'user_agent' ) {
			$description  = $atts['entry']->description;
			$replace_with = FrmEntriesHelper::get_browser( $description['browser'] );
		} elseif ( $clean_tag == 'created_at' || $clean_tag == 'updated_at' ) {
			$atts['tag']  = $clean_tag;
			$replace_with = self::get_entry_timestamp( $atts );
		} elseif ( $clean_tag == 'created_by' || $clean_tag == 'updated_by' ) {
			$replace_with = self::get_display_value( $atts['entry']->{$clean_tag}, (object) array( 'type' => 'user_id' ), $atts );
		} else {
			$replace_with = self::get_field_shortcode_value( $atts );
		}

		return $replace_with;
	}

	/**
	 * @since 3.0
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	private static function get_entry_timestamp( $atts ) {
		if ( isset( $atts['format'] ) ) {
			$time_format = ' ';
		} else {
			$atts['format'] = get_option( 'date_format' );
			$time_format    = '';
		}

		return FrmAppHelper::get_formatted_time( $atts['entry']->{$atts['tag']}, $atts['format'], $time_format );
	}

	/**
	 * @since 3.0
	 *
	 * @param $atts
	 *
	 * @return null|string
	 */
	private static function get_field_shortcode_value( $atts ) {
		$field = FrmField::getOne( $atts['tag'] );
		if ( empty( $field ) ) {
			return null;
		}

		if ( isset( $atts['show'] ) && $atts['show'] === 'field_label' ) {
			$replace_with = $field->name;
		} elseif ( isset( $atts['show'] ) && $atts['show'] === 'description' ) {
			$replace_with = $field->description;
		} else {
			$replace_with = FrmEntryMeta::get_meta_value( $atts['entry'], $field->id );
			$string_value = $replace_with;
			if ( is_array( $replace_with ) ) {
				$sep          = isset( $atts['sep'] ) ? $atts['sep'] : ', ';
				$string_value = implode( $sep, $replace_with );
			}

			if ( empty( $string_value ) && $string_value != '0' ) {
				$replace_with = '';
			} else {
				$atts['entry_id']  = $atts['entry']->id;
				$atts['entry_key'] = $atts['entry']->item_key;
				$replace_with      = self::get_display_value( $replace_with, $field, $atts );
			}
		}

		return $replace_with;
	}

	/**
	 * Get the value to replace a few standard shortcodes
	 *
	 * @since 2.0
	 * @return string
	 */
	public static function dynamic_default_values( $tag, $atts = array(), $return_array = false ) {
		$new_value = '';
		switch ( $tag ) {
			case 'admin_email':
				$new_value = get_option( 'admin_email' );
				break;
			case 'siteurl':
				$new_value = FrmAppHelper::site_url();
				break;
			case 'frmurl':
				$new_value = FrmAppHelper::plugin_url();
				break;
			case 'sitename':
				$new_value = FrmAppHelper::site_name();
				break;
			case 'get':
				$new_value = self::process_get_shortcode( $atts, $return_array );
		}

		return $new_value;
	}

	/**
	 * Process the [get] shortcode
	 *
	 * @since 2.0
	 * @return string|array
	 */
	public static function process_get_shortcode( $atts, $return_array = false ) {
		if ( ! isset( $atts['param'] ) ) {
			return '';
		}

		if ( strpos( $atts['param'], '&#91;' ) ) {
			$atts['param'] = str_replace( '&#91;', '[', $atts['param'] );
			$atts['param'] = str_replace( '&#93;', ']', $atts['param'] );
		}

		$new_value = FrmAppHelper::get_param( $atts['param'], '', 'get', 'sanitize_text_field' );
		$new_value = FrmAppHelper::get_query_var( $new_value, $atts['param'] );

		if ( $new_value == '' ) {
			if ( ! isset( $atts['prev_val'] ) ) {
				$atts['prev_val'] = '';
			}

			$new_value = isset( $atts['default'] ) ? $atts['default'] : $atts['prev_val'];
		}

		if ( is_array( $new_value ) && ! $return_array ) {
			$new_value = implode( ', ', $new_value );
		}

		return $new_value;
	}

	public static function get_display_value( $value, $field, $atts = array() ) {

		$value = apply_filters( 'frm_get_' . $field->type . '_display_value', $value, $field, $atts );
		$value = apply_filters( 'frm_get_display_value', $value, $field, $atts );

		$value = self::get_unfiltered_display_value( compact( 'value', 'field', 'atts' ) );

		return apply_filters( 'frm_display_value', $value, $field, $atts );
	}

	/**
	 * @param $atts array Includes value, field, and atts
	 */
	public static function get_unfiltered_display_value( $atts ) {
		$value = $atts['value'];
		$field = $atts['field'];
		$atts  = isset( $atts['atts'] ) ? $atts['atts'] : $atts;

		if ( is_array( $field ) ) {
			$field = $field['id'];
		}

		$field_obj = FrmFieldFactory::get_field_object( $field );

		return $field_obj->get_display_value( $value, $atts );
	}

	/**
	 * Get a value from the user profile from the user ID
	 *
	 * @since 3.0
	 */
	public static function get_user_display_name( $user_id, $user_info = 'display_name', $args = array() ) {
		$defaults = array(
			'blank' => false,
			'link'  => false,
			'size'  => 96,
		);

		$args = wp_parse_args( $args, $defaults );

		$user = get_userdata( $user_id );
		$info = '';

		if ( $user ) {
			if ( $user_info == 'avatar' ) {
				$info = get_avatar( $user_id, $args['size'] );
			} elseif ( $user_info == 'author_link' ) {
				$info = get_author_posts_url( $user_id );
			} else {
				$info = isset( $user->$user_info ) ? $user->$user_info : '';
			}

			if ( 'display_name' === $user_info && empty( $info ) && ! $args['blank'] ) {
				$info = $user->user_login;
			}
		}

		if ( $args['link'] ) {
			$info = '<a href="' . esc_url( admin_url( 'user-edit.php?user_id=' . $user_id ) ) . '">' . $info . '</a>';
		}

		return $info;
	}

	public static function get_field_types( $type ) {
		$single_input   = self::single_input_fields();
		$multiple_input = array( 'radio', 'checkbox', 'select', 'scale', 'star', 'lookup' );

		$field_selection = FrmField::all_field_selection();

		$field_types = array();
		if ( in_array( $type, $single_input ) ) {
			self::field_types_for_input( $single_input, $field_selection, $field_types );
		} elseif ( in_array( $type, $multiple_input ) ) {
			self::field_types_for_input( $multiple_input, $field_selection, $field_types );
		} elseif ( isset( $field_selection[ $type ] ) ) {
			$field_types[ $type ] = $field_selection[ $type ];
		}

		$field_types = apply_filters( 'frm_switch_field_types', $field_types, compact( 'type', 'field_selection' ) );

		return $field_types;
	}

	/**
	 * Get a list of all fields that use a single value input.
	 *
	 * @since 4.0
	 */
	public static function single_input_fields() {
		$fields = array(
			'text',
			'textarea',
			'rte',
			'number',
			'email',
			'url',
			'date',
			'phone',
			'hidden',
			'time',
			'tag',
			'password',
		);
		return apply_filters( 'frm_single_input_fields', $fields );
	}

	private static function field_types_for_input( $inputs, $fields, &$field_types ) {
		foreach ( $inputs as $input ) {
			$field_types[ $input ] = $fields[ $input ];
			unset( $input );
		}
	}

	/**
	 * Check if current field option is an "other" option
	 *
	 * @since 2.0.6
	 *
	 * @param string $opt_key
	 *
	 * @return boolean Returns true if current field option is an "Other" option
	 */
	public static function is_other_opt( $opt_key ) {
		return $opt_key && strpos( $opt_key, 'other_' ) === 0;
	}

	/**
	 * Get value that belongs in "Other" text box
	 *
	 * @since 2.0.6
	 *
	 * @param array $args
	 */
	public static function get_other_val( $args ) {
		$defaults = array(
			'opt_key' => 0,
			'field'   => array(),
			'parent'  => false,
			'pointer' => false,
		);
		$args     = wp_parse_args( $args, $defaults );

		$opt_key   = $args['opt_key'];
		$field     = $args['field'];
		$parent    = $args['parent'];
		$pointer   = $args['pointer'];
		$other_val = '';

		// If option is an "other" option and there is a value set for this field,
		// check if the value belongs in the current "Other" option text field
		if ( ! self::is_other_opt( $opt_key ) || ! FrmField::is_option_true( $field, 'value' ) ) {
			return $other_val;
		}

		// Check posted vals before checking saved values
		// For fields inside repeating sections - note, don't check if $pointer is true because it will often be zero
		if ( $parent && isset( $_POST['item_meta'][ $parent ][ $pointer ]['other'][ $field['id'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( FrmField::is_field_with_multiple_values( $field ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$other_val = isset( $_POST['item_meta'][ $parent ][ $pointer ]['other'][ $field['id'] ][ $opt_key ] ) ? sanitize_text_field( wp_unslash( $_POST['item_meta'][ $parent ][ $pointer ]['other'][ $field['id'] ][ $opt_key ] ) ) : '';
			} else {
				$other_val = sanitize_text_field( wp_unslash( $_POST['item_meta'][ $parent ][ $pointer ]['other'][ $field['id'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			}

			return $other_val;

		} elseif ( isset( $field['id'] ) && isset( $_POST['item_meta']['other'][ $field['id'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// For normal fields

			if ( FrmField::is_field_with_multiple_values( $field ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$other_val = isset( $_POST['item_meta']['other'][ $field['id'] ][ $opt_key ] ) ? sanitize_text_field( wp_unslash( $_POST['item_meta']['other'][ $field['id'] ][ $opt_key ] ) ) : '';
			} else {
				$other_val = sanitize_text_field( wp_unslash( $_POST['item_meta']['other'][ $field['id'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			}

			return $other_val;
		}

		// For checkboxes
		if ( $field['type'] === 'checkbox' && is_array( $field['value'] ) ) {
			// Check if there is an "other" val in saved value and make sure the
			// "other" val is not equal to the Other checkbox option
			if ( isset( $field['value'][ $opt_key ] ) && $field['options'][ $opt_key ] != $field['value'][ $opt_key ] ) {
				$other_val = $field['value'][ $opt_key ];
			}
		} else {
			/**
			 * For radio buttons and dropdowns
			 * Check if saved value equals any of the options. If not, set it as the other value.
			 */
			foreach ( $field['options'] as $opt_key => $opt_val ) {
				$temp_val = is_array( $opt_val ) ? $opt_val['value'] : $opt_val;
				// Multi-select dropdowns - key is not preserved
				if ( is_array( $field['value'] ) ) {
					$o_key = array_search( $temp_val, $field['value'] );
					if ( isset( $field['value'][ $o_key ] ) ) {
						unset( $field['value'][ $o_key ], $o_key );
					}
				} elseif ( $temp_val == $field['value'] ) {
					// For radio and regular dropdowns
					return '';
				} else {
					$other_val = $field['value'];
				}
				unset( $opt_key, $opt_val, $temp_val );
			}
			// For multi-select dropdowns only
			if ( is_array( $field['value'] ) && ! empty( $field['value'] ) ) {
				$other_val = reset( $field['value'] );
			}
		}

		return $other_val;
	}

	/**
	 * Check if there is a saved value for the "Other" text field. If so, set it as the $other_val.
	 * Intended for front-end use
	 *
	 * @since 2.0.6
	 *
	 * @param array $args should include field, opt_key and field name
	 * @param boolean $other_opt
	 * @param string $checked
	 *
	 * @return array $other_args
	 */
	public static function prepare_other_input( $args, &$other_opt, &$checked ) {
		$other_args = array(
			'name'  => '',
			'value' => '',
		);

		//Check if this is an "Other" option
		if ( ! self::is_other_opt( $args['opt_key'] ) ) {
			return $other_args;
		}

		$other_opt  = true;

		self::set_other_name( $args, $other_args );
		self::set_other_value( $args, $other_args );

		if ( '' !== $other_args['value'] ) {
			$checked = 'checked="checked" ';
		}

		return $other_args;
	}

	/**
	 * @param array $args
	 * @param array $other_args
	 *
	 * @since 2.0.6
	 */
	private static function set_other_name( $args, &$other_args ) {
		//Set up name for other field
		$other_args['name'] = str_replace( '[]', '', $args['field_name'] );
		$other_args['name'] = preg_replace( '/\[' . $args['field']['id'] . '\]$/', '', $other_args['name'] );
		$other_args['name'] = $other_args['name'] . '[other][' . $args['field']['id'] . ']';

		//Converts item_meta[field_id] => item_meta[other][field_id] and
		//item_meta[parent][0][field_id] => item_meta[parent][0][other][field_id]
		if ( FrmField::is_field_with_multiple_values( $args['field'] ) ) {
			$other_args['name'] .= '[' . $args['opt_key'] . ']';
		}
	}

	/**
	 * Find the parent and pointer, and get text for "other" text field
	 *
	 * @param array $args
	 * @param array $other_args
	 *
	 * @since 2.0.6
	 */
	private static function set_other_value( $args, &$other_args ) {
		$parent  = '';
		$pointer = '';

		// Check for parent ID and pointer
		$temp_array = explode( '[', $args['field_name'] );

		// Count should only be greater than 3 if inside of a repeating section
		if ( count( $temp_array ) > 3 ) {
			$parent  = str_replace( ']', '', $temp_array[1] );
			$pointer = str_replace( ']', '', $temp_array[2] );
		}

		// Get text for "other" text field
		$other_args['value'] = self::get_other_val(
			array(
				'opt_key' => $args['opt_key'],
				'field'   => $args['field'],
				'parent'  => $parent,
				'pointer' => $pointer,
			)
		);
	}

	/**
	 * If this field includes an other option, show it
	 *
	 * @param $args array
	 *
	 * @since 2.0.6
	 */
	public static function include_other_input( $args ) {
		if ( ! $args['other_opt'] ) {
			return;
		}

		$classes = array( 'frm_other_input' );
		if ( ! $args['checked'] || trim( $args['checked'] ) == '' ) {
			// hide the field if the other option is not selected
			$classes[] = 'frm_pos_none';
		}
		if ( $args['field']['type'] == 'select' && $args['field']['multiple'] ) {
			$classes[] = 'frm_other_full';
		}

		// Set up HTML ID for Other field
		$other_id = self::get_other_field_html_id( $args['field']['type'], $args['html_id'], $args['opt_key'] );

		$label = isset( $args['opt_label'] ) ? $args['opt_label'] : $args['field']['name'];

		echo '<label for="' . esc_attr( $other_id ) . '" class="frm_screen_reader frm_hidden">' .
			esc_html( $label ) .
			'</label>' .
			'<input type="text" id="' . esc_attr( $other_id ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" ' .
			( $args['read_only'] ? ' readonly="readonly" disabled="disabled"' : '' ) .
			' name="' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $args['value'] ) . '" />';
	}

	/**
	 * Get the HTML id for an "Other" text field
	 * Note: This does not affect fields in repeating sections
	 *
	 * @since 2.0.08
	 *
	 * @param string $type - field type
	 * @param string $html_id
	 * @param string|boolean $opt_key
	 *
	 * @return string $other_id
	 */
	public static function get_other_field_html_id( $type, $html_id, $opt_key = false ) {
		$other_id = $html_id;

		// If hidden radio field, add an opt key of 0
		if ( $type == 'radio' && $opt_key === false ) {
			$opt_key = 0;
		}

		if ( $opt_key !== false ) {
			$other_id .= '-' . $opt_key;
		}

		$other_id .= '-otext';

		return $other_id;
	}

	public static function switch_field_ids( $val ) {
		global $frm_duplicate_ids;
		$replace      = array();
		$replace_with = array();
		foreach ( (array) $frm_duplicate_ids as $old => $new ) {
			$replace[]      = '[if ' . $old . ']';
			$replace_with[] = '[if ' . $new . ']';
			$replace[]      = '[if ' . $old . ' ';
			$replace_with[] = '[if ' . $new . ' ';
			$replace[]      = '[/if ' . $old . ']';
			$replace_with[] = '[/if ' . $new . ']';
			$replace[]      = '[\/if ' . $old . ']';
			$replace_with[] = '[\/if ' . $new . ']';
			$replace[]      = '[foreach ' . $old . ']';
			$replace_with[] = '[foreach ' . $new . ']';
			$replace[]      = '[/foreach ' . $old . ']';
			$replace_with[] = '[/foreach ' . $new . ']';
			$replace[]      = '[\/foreach ' . $old . ']';
			$replace_with[] = '[\/foreach ' . $new . ']';
			$replace[]      = '[' . $old . ']';
			$replace_with[] = '[' . $new . ']';
			$replace[]      = '[' . $old . ' ';
			$replace_with[] = '[' . $new . ' ';
			$replace[]      = 'field_id="' . $old . '"';
			$replace_with[] = 'field_id="' . $new . '"';
			$replace[]      = 'field_id=\"' . $old . '\"';
			$replace_with[] = 'field_id=\"' . $new . '\"';
			unset( $old, $new );
		}
		if ( is_array( $val ) ) {
			foreach ( $val as $k => $v ) {
				if ( is_string( $v ) ) {
					$val[ $k ] = str_replace( $replace, $replace_with, $v );
					unset( $k, $v );
				}
			}
		} else {
			$val = str_replace( $replace, $replace_with, $val );
		}

		return $val;
	}

	/**
	 * @since 4.0
	 */
	public static function bulk_options_overlay() {
		$prepop = array();
		self::get_bulk_prefilled_opts( $prepop, true );

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/bulk-options-overlay.php' );
	}

	public static function get_us_states() {
		$states = array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AR' => 'Arkansas',
			'AZ' => 'Arizona',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		);

		return apply_filters( 'frm_us_states', $states );
	}

	public static function get_countries() {
		$countries = array(
			__( 'Afghanistan', 'formidable' ),
			__( 'Aland Islands', 'formidable' ),
			__( 'Albania', 'formidable' ),
			__( 'Algeria', 'formidable' ),
			__( 'American Samoa', 'formidable' ),
			__( 'Andorra', 'formidable' ),
			__( 'Angola', 'formidable' ),
			__( 'Anguilla', 'formidable' ),
			__( 'Antarctica', 'formidable' ),
			__( 'Antigua and Barbuda', 'formidable' ),
			__( 'Argentina', 'formidable' ),
			__( 'Armenia', 'formidable' ),
			__( 'Aruba', 'formidable' ),
			__( 'Australia', 'formidable' ),
			__( 'Austria', 'formidable' ),
			__( 'Azerbaijan', 'formidable' ),
			__( 'Bahamas', 'formidable' ),
			__( 'Bahrain', 'formidable' ),
			__( 'Bangladesh', 'formidable' ),
			__( 'Barbados', 'formidable' ),
			__( 'Belarus', 'formidable' ),
			__( 'Belgium', 'formidable' ),
			__( 'Belize', 'formidable' ),
			__( 'Benin', 'formidable' ),
			__( 'Bermuda', 'formidable' ),
			__( 'Bhutan', 'formidable' ),
			__( 'Bolivia', 'formidable' ),
			__( 'Bonaire, Sint Eustatius and Saba', 'formidable' ),
			__( 'Bosnia and Herzegovina', 'formidable' ),
			__( 'Botswana', 'formidable' ),
			__( 'Bouvet Island', 'formidable' ),
			__( 'Brazil', 'formidable' ),
			__( 'British Indian Ocean Territory', 'formidable' ),
			__( 'Brunei', 'formidable' ),
			__( 'Bulgaria', 'formidable' ),
			__( 'Burkina Faso', 'formidable' ),
			__( 'Burundi', 'formidable' ),
			__( 'Cambodia', 'formidable' ),
			__( 'Cameroon', 'formidable' ),
			__( 'Canada', 'formidable' ),
			__( 'Cape Verde', 'formidable' ),
			__( 'Cayman Islands', 'formidable' ),
			__( 'Central African Republic', 'formidable' ),
			__( 'Chad', 'formidable' ),
			__( 'Chile', 'formidable' ),
			__( 'China', 'formidable' ),
			__( 'Christmas Island', 'formidable' ),
			__( 'Cocos (Keeling) Islands', 'formidable' ),
			__( 'Colombia', 'formidable' ),
			__( 'Comoros', 'formidable' ),
			__( 'Congo', 'formidable' ),
			__( 'Cook Islands', 'formidable' ),
			__( 'Costa Rica', 'formidable' ),
			__( 'C&ocirc;te d\'Ivoire', 'formidable' ),
			__( 'Croatia', 'formidable' ),
			__( 'Cuba', 'formidable' ),
			__( 'Curacao', 'formidable' ),
			__( 'Cyprus', 'formidable' ),
			__( 'Czech Republic', 'formidable' ),
			__( 'Denmark', 'formidable' ),
			__( 'Djibouti', 'formidable' ),
			__( 'Dominica', 'formidable' ),
			__( 'Dominican Republic', 'formidable' ),
			__( 'East Timor', 'formidable' ),
			__( 'Ecuador', 'formidable' ),
			__( 'Egypt', 'formidable' ),
			__( 'El Salvador', 'formidable' ),
			__( 'Equatorial Guinea', 'formidable' ),
			__( 'Eritrea', 'formidable' ),
			__( 'Estonia', 'formidable' ),
			__( 'Ethiopia', 'formidable' ),
			__( 'Falkland Islands (Malvinas)', 'formidable' ),
			__( 'Faroe Islands', 'formidable' ),
			__( 'Fiji', 'formidable' ),
			__( 'Finland', 'formidable' ),
			__( 'France', 'formidable' ),
			__( 'French Guiana', 'formidable' ),
			__( 'French Polynesia', 'formidable' ),
			__( 'French Southern Territories', 'formidable' ),
			__( 'Gabon', 'formidable' ),
			__( 'Gambia', 'formidable' ),
			__( 'Georgia', 'formidable' ),
			__( 'Germany', 'formidable' ),
			__( 'Ghana', 'formidable' ),
			__( 'Gibraltar', 'formidable' ),
			__( 'Greece', 'formidable' ),
			__( 'Greenland', 'formidable' ),
			__( 'Grenada', 'formidable' ),
			__( 'Guadeloupe', 'formidable' ),
			__( 'Guam', 'formidable' ),
			__( 'Guatemala', 'formidable' ),
			__( 'Guernsey', 'formidable' ),
			__( 'Guinea', 'formidable' ),
			__( 'Guinea-Bissau', 'formidable' ),
			__( 'Guyana', 'formidable' ),
			__( 'Haiti', 'formidable' ),
			__( 'Heard Island and McDonald Islands', 'formidable' ),
			__( 'Holy See', 'formidable' ),
			__( 'Honduras', 'formidable' ),
			__( 'Hong Kong', 'formidable' ),
			__( 'Hungary', 'formidable' ),
			__( 'Iceland', 'formidable' ),
			__( 'India', 'formidable' ),
			__( 'Indonesia', 'formidable' ),
			__( 'Iran', 'formidable' ),
			__( 'Iraq', 'formidable' ),
			__( 'Ireland', 'formidable' ),
			__( 'Israel', 'formidable' ),
			__( 'Isle of Man', 'formidable' ),
			__( 'Italy', 'formidable' ),
			__( 'Jamaica', 'formidable' ),
			__( 'Japan', 'formidable' ),
			__( 'Jersey', 'formidable' ),
			__( 'Jordan', 'formidable' ),
			__( 'Kazakhstan', 'formidable' ),
			__( 'Kenya', 'formidable' ),
			__( 'Kiribati', 'formidable' ),
			__( 'North Korea', 'formidable' ),
			__( 'South Korea', 'formidable' ),
			__( 'Kosovo', 'formidable' ),
			__( 'Kuwait', 'formidable' ),
			__( 'Kyrgyzstan', 'formidable' ),
			__( 'Laos', 'formidable' ),
			__( 'Latvia', 'formidable' ),
			__( 'Lebanon', 'formidable' ),
			__( 'Lesotho', 'formidable' ),
			__( 'Liberia', 'formidable' ),
			__( 'Libya', 'formidable' ),
			__( 'Liechtenstein', 'formidable' ),
			__( 'Lithuania', 'formidable' ),
			__( 'Luxembourg', 'formidable' ),
			__( 'Macao', 'formidable' ),
			__( 'Macedonia', 'formidable' ),
			__( 'Madagascar', 'formidable' ),
			__( 'Malawi', 'formidable' ),
			__( 'Malaysia', 'formidable' ),
			__( 'Maldives', 'formidable' ),
			__( 'Mali', 'formidable' ),
			__( 'Malta', 'formidable' ),
			__( 'Marshall Islands', 'formidable' ),
			__( 'Martinique', 'formidable' ),
			__( 'Mauritania', 'formidable' ),
			__( 'Mauritius', 'formidable' ),
			__( 'Mayotte', 'formidable' ),
			__( 'Mexico', 'formidable' ),
			__( 'Micronesia', 'formidable' ),
			__( 'Moldova', 'formidable' ),
			__( 'Monaco', 'formidable' ),
			__( 'Mongolia', 'formidable' ),
			__( 'Montenegro', 'formidable' ),
			__( 'Montserrat', 'formidable' ),
			__( 'Morocco', 'formidable' ),
			__( 'Mozambique', 'formidable' ),
			__( 'Myanmar', 'formidable' ),
			__( 'Namibia', 'formidable' ),
			__( 'Nauru', 'formidable' ),
			__( 'Nepal', 'formidable' ),
			__( 'Netherlands', 'formidable' ),
			__( 'New Caledonia', 'formidable' ),
			__( 'New Zealand', 'formidable' ),
			__( 'Nicaragua', 'formidable' ),
			__( 'Niger', 'formidable' ),
			__( 'Nigeria', 'formidable' ),
			__( 'Niue', 'formidable' ),
			__( 'Norfolk Island', 'formidable' ),
			__( 'Northern Mariana Islands', 'formidable' ),
			__( 'Norway', 'formidable' ),
			__( 'Oman', 'formidable' ),
			__( 'Pakistan', 'formidable' ),
			__( 'Palau', 'formidable' ),
			__( 'Palestine', 'formidable' ),
			__( 'Panama', 'formidable' ),
			__( 'Papua New Guinea', 'formidable' ),
			__( 'Paraguay', 'formidable' ),
			__( 'Peru', 'formidable' ),
			__( 'Philippines', 'formidable' ),
			__( 'Pitcairn', 'formidable' ),
			__( 'Poland', 'formidable' ),
			__( 'Portugal', 'formidable' ),
			__( 'Puerto Rico', 'formidable' ),
			__( 'Qatar', 'formidable' ),
			__( 'Reunion', 'formidable' ),
			__( 'Romania', 'formidable' ),
			__( 'Russia', 'formidable' ),
			__( 'Rwanda', 'formidable' ),
			__( 'Saint Barthelemy', 'formidable' ),
			__( 'Saint Helena, Ascension and Tristan da Cunha', 'formidable' ),
			__( 'Saint Kitts and Nevis', 'formidable' ),
			__( 'Saint Lucia', 'formidable' ),
			__( 'Saint Martin (French part)', 'formidable' ),
			__( 'Saint Pierre and Miquelon', 'formidable' ),
			__( 'Saint Vincent and the Grenadines', 'formidable' ),
			__( 'Samoa', 'formidable' ),
			__( 'San Marino', 'formidable' ),
			__( 'Sao Tome and Principe', 'formidable' ),
			__( 'Saudi Arabia', 'formidable' ),
			__( 'Senegal', 'formidable' ),
			__( 'Serbia', 'formidable' ),
			__( 'Seychelles', 'formidable' ),
			__( 'Sierra Leone', 'formidable' ),
			__( 'Singapore', 'formidable' ),
			__( 'Sint Maarten (Dutch part)', 'formidable' ),
			__( 'Slovakia', 'formidable' ),
			__( 'Slovenia', 'formidable' ),
			__( 'Solomon Islands', 'formidable' ),
			__( 'Somalia', 'formidable' ),
			__( 'South Africa', 'formidable' ),
			__( 'South Georgia and the South Sandwich Islands', 'formidable' ),
			__( 'South Sudan', 'formidable' ),
			__( 'Spain', 'formidable' ),
			__( 'Sri Lanka', 'formidable' ),
			__( 'Sudan', 'formidable' ),
			__( 'Suriname', 'formidable' ),
			__( 'Svalbard and Jan Mayen', 'formidable' ),
			__( 'Swaziland', 'formidable' ),
			__( 'Sweden', 'formidable' ),
			__( 'Switzerland', 'formidable' ),
			__( 'Syria', 'formidable' ),
			__( 'Taiwan', 'formidable' ),
			__( 'Tajikistan', 'formidable' ),
			__( 'Tanzania', 'formidable' ),
			__( 'Thailand', 'formidable' ),
			__( 'Timor-Leste', 'formidable' ),
			__( 'Togo', 'formidable' ),
			__( 'Tokelau', 'formidable' ),
			__( 'Tonga', 'formidable' ),
			__( 'Trinidad and Tobago', 'formidable' ),
			__( 'Tunisia', 'formidable' ),
			__( 'Turkey', 'formidable' ),
			__( 'Turkmenistan', 'formidable' ),
			__( 'Turks and Caicos Islands', 'formidable' ),
			__( 'Tuvalu', 'formidable' ),
			__( 'Uganda', 'formidable' ),
			__( 'Ukraine', 'formidable' ),
			__( 'United Arab Emirates', 'formidable' ),
			__( 'United Kingdom', 'formidable' ),
			__( 'United States', 'formidable' ),
			__( 'United States Minor Outlying Islands', 'formidable' ),
			__( 'Uruguay', 'formidable' ),
			__( 'Uzbekistan', 'formidable' ),
			__( 'Vanuatu', 'formidable' ),
			__( 'Vatican City', 'formidable' ),
			__( 'Venezuela', 'formidable' ),
			__( 'Vietnam', 'formidable' ),
			__( 'Virgin Islands, British', 'formidable' ),
			__( 'Virgin Islands, U.S.', 'formidable' ),
			__( 'Wallis and Futuna', 'formidable' ),
			__( 'Western Sahara', 'formidable' ),
			__( 'Yemen', 'formidable' ),
			__( 'Zambia', 'formidable' ),
			__( 'Zimbabwe', 'formidable' ),
		);

		sort( $countries, SORT_LOCALE_STRING );
		return apply_filters( 'frm_countries', $countries );
	}

	/**
	 * Gets bulk prefilled options.
	 *
	 * @since 5.0.04 Add `$include_class` param.
	 *
	 * @param array       $prepop        Bulk options.
	 * @param array|false $include_class Include the class in the bulk options.
	 */
	public static function get_bulk_prefilled_opts( array &$prepop, $include_class = false ) {
		// Countries.
		$countries = self::get_countries();
		if ( $include_class ) {
			$countries['class'] = 'frm-countries-opts';
		}

		$prepop[ __( 'Countries', 'formidable' ) ] = $countries;

		// State abv.
		$states    = self::get_us_states();
		$state_abv = array_keys( $states );
		sort( $state_abv );
		if ( $include_class ) {
			$state_abv['class'] = 'frm-state-abv-opts';
		}

		$prepop[ __( 'U.S. State Abbreviations', 'formidable' ) ] = $state_abv;

		// States.
		$states = array_values( $states );
		sort( $states );
		if ( $include_class ) {
			$states['class'] = 'frm-states-opts';
		}

		$prepop[ __( 'U.S. States', 'formidable' ) ] = $states;
		unset( $state_abv, $states );

		// Age.
		$ages = array(
			__( 'Under 18', 'formidable' ),
			__( '18-24', 'formidable' ),
			__( '25-34', 'formidable' ),
			__( '35-44', 'formidable' ),
			__( '45-54', 'formidable' ),
			__( '55-64', 'formidable' ),
			__( '65 or Above', 'formidable' ),
			__( 'Prefer Not to Answer', 'formidable' ),
		);
		if ( $include_class ) {
			$ages['class'] = 'frm-age-opts';
		}

		$prepop[ __( 'Age', 'formidable' ) ] = $ages;

		// Satisfaction.
		$satisfaction = array(
			__( 'Very Unsatisfied', 'formidable' ),
			__( 'Unsatisfied', 'formidable' ),
			__( 'Neutral', 'formidable' ),
			__( 'Satisfied', 'formidable' ),
			__( 'Very Satisfied', 'formidable' ),
			__( 'N/A', 'formidable' ),
		);
		if ( $include_class ) {
			$satisfaction['class'] = 'frm-satisfaction-opts';
		}

		$prepop[ __( 'Satisfaction', 'formidable' ) ] = $satisfaction;

		// Importance.
		$importance = array(
			__( 'Not at all Important', 'formidable' ),
			__( 'Somewhat Important', 'formidable' ),
			__( 'Neutral', 'formidable' ),
			__( 'Important', 'formidable' ),
			__( 'Very Important', 'formidable' ),
			__( 'N/A', 'formidable' ),
		);
		if ( $include_class ) {
			$importance['class'] = 'frm-importance-opts';
		}

		$prepop[ __( 'Importance', 'formidable' ) ] = $importance;

		// Agreement.
		$agreement = array(
			__( 'Strongly Disagree', 'formidable' ),
			__( 'Disagree', 'formidable' ),
			__( 'Neutral', 'formidable' ),
			__( 'Agree', 'formidable' ),
			__( 'Strongly Agree', 'formidable' ),
			__( 'N/A', 'formidable' ),
		);
		if ( $include_class ) {
			$agreement['class'] = 'frm-agreement-opts';
		}

		$prepop[ __( 'Agreement', 'formidable' ) ] = $agreement;

		// Likely.
		$likely = array(
			__( 'Extremely Unlikely', 'formidable' ),
			__( 'Unlikely', 'formidable' ),
			__( 'Neutral', 'formidable' ),
			__( 'Likely', 'formidable' ),
			__( 'Extremely Likely', 'formidable' ),
			__( 'N/A', 'formidable' ),
		);
		if ( $include_class ) {
			$likely['class'] = 'frm-likely-opts';
		}

		$prepop[ __( 'Likely', 'formidable' ) ] = $likely;

		$prepop = apply_filters( 'frm_bulk_field_choices', $prepop );
	}

	/**
	 * Display a field value selector
	 *
	 * @since 2.03.05
	 *
	 * @param int $selector_field_id
	 * @param array $selector_args
	 */
	public static function display_field_value_selector( $selector_field_id, $selector_args ) {
		$field_value_selector = FrmFieldFactory::create_field_value_selector( $selector_field_id, $selector_args );
		$field_value_selector->display();
	}

	/**
	 * Convert a field object to a flat array
	 *
	 * @since 2.03.05
	 *
	 * @param object $field
	 *
	 * @return array
	 */
	public static function convert_field_object_to_flat_array( $field ) {
		$field_options = $field->field_options;
		$field_array   = get_object_vars( $field );
		unset( $field_array['field_options'] );

		return $field_array + $field_options;
	}

	/**
	 * @since 4.04
	 *
	 * @param array $args
	 * @return void
	 */
	public static function show_add_field_buttons( $args ) {
		$field_key    = $args['field_key'];
		$field_type   = $args['field_type'];
		$field_label  = FrmAppHelper::icon_by_class( FrmFormsHelper::get_field_link_icon( $field_type ), array( 'echo' => false ) );
		$field_name   = FrmFormsHelper::get_field_link_name( $field_type );
		$field_label .= ' <span>' . $field_name . '</span>';

		// If the individual field isn't allowed, disable it.
		$run_filter             = true;
		$single_no_allow        = ' ';
		$install_data           = '';
		$requires               = '';
		$link                   = isset( $field_type['link'] ) ? esc_url_raw( $field_type['link'] ) : '';
		$has_show_upgrade_class = strpos( $field_type['icon'], ' frm_show_upgrade' );
		$show_upgrade           = $has_show_upgrade_class || false !== strpos( $args['no_allow_class'], 'frm_show_upgrade' );

		if ( $has_show_upgrade_class ) {
			$single_no_allow   .= 'frm_show_upgrade';
			$field_type['icon'] = str_replace( ' frm_show_upgrade', '', $field_type['icon'] );
			$run_filter         = false;
			if ( isset( $field_type['addon'] ) ) {
				$upgrading = FrmAddonsController::install_link( $field_type['addon'] );
				if ( isset( $upgrading['url'] ) ) {
					$install_data = json_encode( $upgrading );
				}
				$requires = FrmFormsHelper::get_plan_required( $upgrading );
			} elseif ( isset( $field_type['require'] ) ) {
				$requires = $field_type['require'];
			}
		}

		$upgrade_label   = '';
		$upgrade_message = '';
		if ( $show_upgrade ) {
			/* translators: %s: Field name */
			$upgrade_label = sprintf( esc_html__( '%s fields', 'formidable' ), $field_name );

			if ( isset( $field_type['message'] ) ) {
				$upgrade_message = FrmAppHelper::kses( $field_type['message'], array( 'a', 'img' ) );
			}
		}

		$li_params = array(
			'class'         => 'frmbutton frm6 ' . $args['no_allow_class'] . $single_no_allow . ' frm_t' . str_replace( '|', '-', $field_key ),
			'id'            => $field_key,
			'data-upgrade'  => $upgrade_label,
			'data-link'     => $link,
			'data-medium'   => 'builder',
			'data-oneclick' => $install_data,
			'data-content'  => $field_key,
			'data-requires' => $requires,
		);

		if ( $upgrade_message ) {
			$li_params['data-message'] = $upgrade_message;
		}
		?>
		<li <?php FrmAppHelper::array_to_html_params( $li_params, true ); ?>>
		<?php
		if ( $run_filter ) {
			$field_label = apply_filters( 'frmpro_field_links', $field_label, $args['id'], $field_key );
		}
		echo FrmAppHelper::kses( $field_label, array( 'a', 'i', 'span', 'use', 'svg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		</li>
		<?php
	}

	/**
	 * Shows Display format option.
	 *
	 * @since 5.0.04
	 *
	 * @param array $field Field data.
	 */
	public static function show_radio_display_format( $field ) {
		$options = array(
			'0'       => array(
				'text'   => __( 'Simple', 'formidable' ),
				'svg'    => 'frm_simple_radio',
			),
			'1'       => array(
				'text'    => __( 'Images', 'formidable' ),
				'svg'     => 'frm_image_as_option',
				'addon'   => 'pro',
				'upgrade' => __( 'Image Options', 'formidable' ),
				'message' => __( 'Show images instead of radio buttons or check boxes. This is ideal for polls, surveys, segmenting questionnaires and more.', 'formidable' ) . '<img src="' . esc_url( FrmAppHelper::plugin_url() ) . '/images/image-options.png" />',
				'content' => 'image-options',
			),
			'buttons' => array(
				'text'    => __( 'Buttons', 'formidable' ),
				'svg'     => 'frm_button_as_option',
				'addon'   => 'surveys',
				'upgrade' => __( 'Button Options', 'formidable' ),
				'message' => __( 'Show buttons for radio buttons or check boxes. This is ideal for polls, surveys, segmenting questionnaires and more.', 'formidable' ),
				'content' => 'button-options',
			),
		);

		/**
		 * Allows modifying the options of Display format setting of Radio field.
		 *
		 * @since 5.0.04
		 *
		 * @param array $options Options.
		 */
		$options = apply_filters( 'frm_radio_display_format_options', $options );

		$args = self::get_display_format_args( $field, $options );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/radio-display-format.php';
	}

	/**
	 * Gets display format arguments to pass to the images_dropdown() method.
	 *
	 * @since 5.0.04
	 *
	 * @param array $field   Field data.
	 * @param array $options Options array.
	 * @return array
	 */
	private static function get_display_format_args( $field, $options ) {
		$args = array(
			'selected'    => '0',
			'options'     => array(),
			'name'        => 'field_options[image_options_' . $field['id'] . ']',
			'input_attrs' => array(
				'class' => 'frm_toggle_image_options',
			),
		);

		self::fill_image_setting_options( $options, $args );

		/**
		 * Allows modifying the arguments of Display format setting of Radio field.
		 *
		 * @since 5.0.04
		 *
		 * @param array $args        Arguments.
		 * @param array $method_args The arguments from the method. Contains `field`, `options`.
		 */
		return apply_filters( 'frm_radio_display_format_args', $args, compact( 'field', 'options' ) );
	}

	/**
	 * @since 5.0.04
	 */
	private static function fill_image_setting_options( $options, &$args ) {
		foreach ( $options as $key => $option ) {
			$args['options'][ $key ] = $option;

			if ( ! empty( $option['addon'] ) ) {
				$args['options'][ $key ]['custom_attrs'] = self::fill_image_setting_addon_link( $option );
			}

			unset( $args['options'][ $key ]['addon'] );
			$fill = array( 'upgrade', 'message', 'content' );
			foreach ( $fill as $f ) {
				unset( $args['options'][ $key ][ $f ], $f );
			}
		}
	}

	/**
	 * @since 5.0.04
	 *
	 * @return array
	 */
	private static function fill_image_setting_addon_link( $option ) {
		$custom_attrs = array(
			'class'       => 'frm_noallow frm_show_upgrade',
			'data-medium' => 'builder',
		);

		// translators: Add-on name.
		$custom_attrs['data-upgrade'] = sprintf( __( 'Formidable %s', 'formidable' ), ucwords( $option['addon'] ) );

		$fill = array( 'upgrade', 'message', 'content' );
		foreach ( $fill as $f ) {
			if ( isset( $option[ $f ] ) ) {
				$custom_attrs[ 'data-' . $f ] = $option[ $f ];
			}
		}

		if ( 'pro' === $option['addon'] ) {
			return $custom_attrs;
		}

		$upgrading = FrmAddonsController::install_link( $option['addon'] );
		if ( isset( $upgrading['url'] ) ) {
			$install_data = wp_json_encode( $upgrading );
		} else {
			$install_data = '';
		}

		$custom_attrs['data-oneclick'] = $install_data;
		$custom_attrs['data-requires'] = FrmFormsHelper::get_plan_required( $upgrading );

		return $custom_attrs;
	}

	/**
	 * @deprecated 4.0
	 */
	public static function show_icon_link_js( $atts ) {
		_deprecated_function( __METHOD__, '4.0' );
		$atts['icon'] .= $atts['is_selected'] ? ' ' : ' frm_inactive_icon ';
		if ( isset( $atts['has_default'] ) && ! $atts['has_default'] ) {
			$atts['icon'] .= 'frm_hidden ';
		}
		echo '<a href="javascript:void(0)" class="frm_bstooltip ' . esc_attr( $atts['icon'] ) . 'frm_default_val_icons frm_action_icon frm_icon_font" title="' . esc_attr( $atts['message'] ) . '"></a>';
	}

	/**
	 * @deprecated 4.0
	 */
	public static function show_default_blank_js( $is_selected, $has_default_value = true ) {
		_deprecated_function( __METHOD__, '4.0' );
	}

	/**
	 * @deprecated 4.0
	 */
	public static function clear_on_focus_html( $field, $display, $id = '' ) {
		_deprecated_function( __METHOD__, '4.0' );
	}

	/**
	 * @deprecated 4.0
	 */
	public static function show_onfocus_js( $is_selected, $has_default_value = true ) {
		_deprecated_function( __METHOD__, '4.0' );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function display_recaptcha() {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldCaptcha::field_input' );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function remove_inline_conditions( $no_vars, $code, $replace_with, &$html ) {
		FrmDeprecated::remove_inline_conditions( $no_vars, $code, $replace_with, $html );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function get_shortcode_tag( $shortcodes, $short_key, $args ) {
		return FrmDeprecated::get_shortcode_tag( $shortcodes, $short_key, $args );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 *
	 * @param string       $html
	 * @param array        $field
	 * @param array        $errors
	 * @param object|false $form
	 * @param array        $args
	 *
	 * @return string
	 */
	public static function replace_shortcodes( $html, $field, $errors = array(), $form = false, $args = array() ) {
		return FrmDeprecated::replace_shortcodes( $html, $field, $errors, $form, $args );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function get_default_field_opts( $type, $field = null, $limit = false ) {
		return FrmDeprecated::get_default_field_opts( $type, $field, $limit );
	}

	/**
	 * @deprecated 2.02.07
	 * @codeCoverageIgnore
	 */
	public static function dropdown_categories( $args ) {
		return FrmDeprecated::dropdown_categories( $args );
	}
}
