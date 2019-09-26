<?php

class FrmFieldsController {

	public static function load_field() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		// Javascript may be included in some field settings.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$fields = isset( $_POST['field'] ) ? wp_unslash( $_POST['field'] ) : array();
		if ( empty( $fields ) ) {
			wp_die();
		}

		$_GET['page'] = 'formidable';

		$values     = array(
			'id'         => FrmAppHelper::get_post_param( 'form_id', '', 'absint' ),
			'doing_ajax' => true,
		);
		$field_html = array();

		foreach ( $fields as $field ) {
			$field = htmlspecialchars_decode( nl2br( $field ) );
			$field = json_decode( $field );
			if ( ! isset( $field->id ) || ! is_numeric( $field->id ) ) {
				// this field may have already been loaded
				continue;
			}

			if ( ! isset( $field->value ) ) {
				$field->value = '';
			}
			$field->field_options = json_decode( json_encode( $field->field_options ), true );
			$field->options       = json_decode( json_encode( $field->options ), true );
			$field->default_value = json_decode( json_encode( $field->default_value ), true );

			ob_start();
			self::load_single_field( $field, $values );
			$field_html[ absint( $field->id ) ] = ob_get_contents();
			ob_end_clean();
		}

		echo json_encode( $field_html );

		wp_die();
	}

	/**
	 * Create a new field with ajax
	 */
	public static function create() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_type = FrmAppHelper::get_post_param( 'field_type', '', 'sanitize_text_field' );
		$form_id    = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

		do_action( 'frm_before_create_field', $field_type, $form_id );

		$field = self::include_new_field( $field_type, $form_id );

		// this hook will allow for multiple fields to be added at once
		do_action( 'frm_after_field_created', $field, $form_id );

		wp_die();
	}

	/**
	 * Set up and create a new field
	 *
	 * @param string $field_type
	 * @param integer $form_id
	 *
	 * @return array|bool
	 */
	public static function include_new_field( $field_type, $form_id ) {
		$field_values = FrmFieldsHelper::setup_new_vars( $field_type, $form_id );
		$field_values = apply_filters( 'frm_before_field_created', $field_values );

		$field_id = FrmField::create( $field_values );

		if ( ! $field_id ) {
			return false;
		}

		$field = self::get_field_array_from_id( $field_id );

		$values = array();
		if ( FrmAppHelper::pro_is_installed() ) {
			$values['post_type'] = FrmProFormsHelper::post_type( $form_id );

			$parent_form_id = FrmDb::get_var( 'frm_forms', array( 'id' => $form_id ), 'parent_form_id' );
			if ( $parent_form_id ) {
				$field['parent_form_id'] = $parent_form_id;
			}
		}

		self::load_single_field( $field, $values, $form_id );

		return $field;
	}

	public static function duplicate() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );
		$form_id  = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

		$copy_field = FrmField::getOne( $field_id );
		if ( ! $copy_field ) {
			wp_die();
		}

		do_action( 'frm_duplicate_field', $copy_field, $form_id );
		do_action( 'frm_duplicate_field_' . $copy_field->type, $copy_field, $form_id );

		$values = array(
			'id' => $copy_field->id,
		);
		FrmFieldsHelper::fill_field( $values, $copy_field, $copy_field->form_id );
		$values = apply_filters( 'frm_prepare_single_field_for_duplication', $values );

		$field_id = FrmField::create( $values );
		if ( $field_id ) {
			self::load_single_field( $field_id, $values );
		}

		wp_die();
	}

	/**
	 * @since 3.0
	 *
	 * @param int $field_id
	 *
	 * @return array
	 */
	public static function get_field_array_from_id( $field_id ) {
		$field = FrmField::getOne( $field_id );

		return FrmFieldsHelper::setup_edit_vars( $field );
	}

	/**
	 * @since 3.0
	 *
	 * @param int|array|object $field_object
	 * @param array $values
	 * @param int $form_id
	 */
	public static function load_single_field( $field_object, $values, $form_id = 0 ) {
		global $frm_vars;
		$frm_vars['is_admin'] = true;

		if ( is_numeric( $field_object ) ) {
			$field_object = FrmField::getOne( $field_object );
		} elseif ( is_array( $field_object ) ) {
			$field        = $field_object;
			$field_object = FrmField::getOne( $field['id'] );
		}

		$field_obj = FrmFieldFactory::get_field_factory( $field_object );
		$display   = self::display_field_options( array(), $field_obj );

		$ajax_loading    = isset( $values['ajax_load'] ) && $values['ajax_load'];
		$ajax_this_field = isset( $values['count'] ) && $values['count'] > 10 && ! in_array( $field_object->type, array( 'divider', 'end_divider' ) );

		if ( $ajax_loading && $ajax_this_field ) {
			$li_classes = self::get_classes_for_builder_field( array(), $display, $field_obj );
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/ajax-field-placeholder.php' );
		} else {
			if ( ! isset( $field ) && is_object( $field_object ) ) {
				$field_object->parent_form_id = isset( $values['id'] ) ? $values['id'] : $field_object->form_id;

				$field = FrmFieldsHelper::setup_edit_vars( $field_object );
			}

			$li_classes = self::get_classes_for_builder_field( $field, $display, $field_obj );
			$li_classes .= ' ui-state-default widgets-holder-wrap';

			require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/add_field.php' );
		}
	}

	/**
	 * @since 3.0
	 */
	private static function get_classes_for_builder_field( $field, $display, $field_info ) {
		$li_classes = $field_info->form_builder_classes( $display['type'] );
		$classes    = isset( $field['classes'] ) ? $field['classes'] : '';

		// Exclude alignright for now since we aren't using widths.
		$classes    = str_replace( ' frm_alignright ', ' ', $classes );
		if ( trim( $classes ) === 'frm_alignright' ) {
			$classes = '';
		}

		$li_classes .= ' frm_form_field frmstart ' . $classes . ' frmend';
		if ( ! empty( $field ) ) {
			$li_classes = apply_filters( 'frm_build_field_class', $li_classes, $field );
		}

		return $li_classes;
	}

	public static function destroy() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );
		FrmField::destroy( $field_id );
		wp_die();
	}

	/* Field Options */

	public static function import_options() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( ! is_admin() || ! current_user_can( 'frm_edit_forms' ) ) {
			return;
		}

		$field_id = FrmAppHelper::get_param( 'field_id', '', 'post', 'absint' );
		$field    = FrmField::getOne( $field_id );

		if ( ! in_array( $field->type, array( 'radio', 'checkbox', 'select' ) ) ) {
			return;
		}

		$field = FrmFieldsHelper::setup_edit_vars( $field );
		$opts  = FrmAppHelper::get_param( 'opts', '', 'post', 'wp_kses_post' );
		$opts  = explode( "\n", rtrim( $opts, "\n" ) );
		$opts  = array_map( 'trim', $opts );

		$separate = FrmAppHelper::get_param( 'separate', '', 'post', 'sanitize_text_field' );
		$field['separate_value'] = ( $separate === 'true' );

		if ( $field['separate_value'] ) {
			foreach ( $opts as $opt_key => $opt ) {
				if ( strpos( $opt, '|' ) !== false ) {
					$vals = explode( '|', $opt );
					$opts[ $opt_key ] = array(
						'label' => trim( $vals[0] ),
						'value' => trim( $vals[1] ),
					);
					unset( $vals );
				}
				unset( $opt_key, $opt );
			}
		}

		// Keep other options after bulk update.
		if ( isset( $field['field_options']['other'] ) && $field['field_options']['other'] == true ) {
			$other_array = array();
			foreach ( $field['options'] as $opt_key => $opt ) {
				if ( FrmFieldsHelper::is_other_opt( $opt_key ) ) {
					$other_array[ $opt_key ] = $opt;
				}
				unset( $opt_key, $opt );
			}
			if ( ! empty( $other_array ) ) {
				$opts = array_merge( $opts, $other_array );
			}
		}

		$field['options'] = $opts;

		FrmFieldsHelper::show_single_option( $field );

		wp_die();
	}

	/**
	 * @since 4.0
	 *
	 * @param array $atts - Includes field array, field_obj, display array, values array.
	 */
	public static function load_single_field_settings( $atts ) {
		$field     = $atts['field'];
		$field_obj = $atts['field_obj'];
		$values    = $atts['values'];
		$display   = $atts['display'];
		unset( $atts );

		if ( ! isset( $field['unique'] ) ) {
			$field['unique'] = false;
		}

		if ( ! isset( $field['read_only'] ) ) {
			$field['read_only'] = false;
		}

		$field_types         = FrmFieldsHelper::get_field_types( $field['type'] );
		$pro_field_selection = FrmField::pro_field_selection();
		$all_field_types     = array_merge( $pro_field_selection, FrmField::field_selection() );
		$disabled_fields     = FrmAppHelper::pro_is_installed() ? array() : $pro_field_selection;
		$frm_settings        = FrmAppHelper::get_settings();

		if ( ! isset( $all_field_types[ $field['type'] ] ) ) {
			// Add fallback for an add-on field type that has been deactivated.
			$all_field_types[ $field['type'] ] = array(
				'name' => ucfirst( $field['type'] ),
				'icon' => 'frm_icon_font frm_pencil_icon',
			);
		} elseif ( ! is_array( $all_field_types[ $field['type'] ] ) ) {
			// Fallback for fields added in a more basic way.
			FrmFormsHelper::prepare_field_type( $all_field_types[ $field['type'] ] );
		}

		$type_name = $all_field_types[ $field['type'] ]['name'];
		if ( $field['type'] === 'divider' && FrmField::is_option_true( $field, 'repeat' ) ) {
			$type_name = $all_field_types['divider|repeat']['name'];
		}

		if ( $display['default'] ) {
			$default_value_types = self::default_value_types( $field, compact( 'display' ) );
		}

		if ( $display['clear_on_focus'] && is_array( $field['placeholder'] ) ) {
			$field['placeholder'] = implode( $field['placeholder'], ', ' );
		}
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/settings.php' );
	}

	/**
	 * Get the list of default value types that can be toggled in the builder.
	 *
	 * @since 4.0
	 * @return array
	 */
	private static function default_value_types( $field, $atts ) {
		$types = array(
			'default_value' => array(
				'class' => '',
				'icon'  => 'frm_icon_font frm_text2_icon',
				'title' => __( 'Default Value (Text)', 'formidable' ),
				'data'  => array(
					'frmshow' => '#default-value-for-',
				),
			),
			'calc' => array(
				'class' => 'frm_show_upgrade frm_noallow',
				'title' => __( 'Default Value (Calculation)', 'formidable' ),
				'icon'  => 'frm_icon_font frm_calculator_icon',
				'data'  => array(
					'medium'  => 'calculations',
					'upgrade' => __( 'Calculator forms', 'formidable' ),
				),
			),
			'get_values_field' => array(
				'class' => 'frm_show_upgrade frm_noallow',
				'title' => __( 'Default Value (Lookup)', 'formidable' ),
				'icon'  => 'frm_icon_font frm_search_icon',
				'data'  => array(
					'medium'  => 'lookup',
					'upgrade' => __( 'Lookup fields', 'formidable' ),
				),
			),
		);

		$types = apply_filters( 'frm_default_value_types', $types, $atts );

		if ( FrmAppHelper::pro_is_installed() && ! FrmAppHelper::meets_min_pro_version( '4.0' ) ) {
			// Prevent settings from showing in 2 spots.
			unset( $types['calc'], $types['get_values_field'] );
		}

		// Set active class.
		$settings = array_keys( $types );
		$active   = 'default_value';

		foreach ( $settings as $type ) {
			if ( ! empty( $field[ $type ] ) ) {
				$active = $type;
			}
		}

		$types[ $active ]['class']  .= ' current';
		$types[ $active ]['current'] = true;

		return $types;
	}

	public static function change_type( $type ) {
		$type_switch = array(
			'scale'   => 'radio',
			'star'    => 'radio',
			'10radio' => 'radio',
			'rte'     => 'textarea',
			'website' => 'url',
			'image'   => 'url',
		);
		if ( isset( $type_switch[ $type ] ) ) {
			$type = $type_switch[ $type ];
		}

		$pro_fields = FrmField::pro_field_selection();
		$types      = array_keys( $pro_fields );
		if ( in_array( $type, $types ) ) {
			$type = 'text';
		}

		return $type;
	}

	/**
	 * @param array $settings
	 * @param object $field_info
	 *
	 * @return array
	 */
	public static function display_field_options( $settings, $field_info = null ) {
		if ( $field_info ) {
			$settings               = $field_info->display_field_settings();
			$settings['field_data'] = $field_info->field;
		}

		return apply_filters( 'frm_display_field_options', $settings );
	}

	/**
	 * Display the format option
	 *
	 * @since 3.0
	 *
	 * @param array $field
	 */
	public static function show_format_option( $field ) {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/value-format.php' );
	}

	public static function input_html( $field, $echo = true ) {
		$class = array();
		self::add_input_classes( $field, $class );

		$add_html = array();
		self::add_html_size( $field, $add_html );
		self::add_html_length( $field, $add_html );
		self::add_html_placeholder( $field, $add_html, $class );
		self::add_validation_messages( $field, $add_html );

		$class = apply_filters( 'frm_field_classes', implode( ' ', $class ), $field );

		FrmFormsHelper::add_html_attr( $class, 'class', $add_html );

		self::add_shortcodes_to_html( $field, $add_html );
		self::add_pattern_attribute( $field, $add_html );

		$add_html = apply_filters( 'frm_field_extra_html', $add_html, $field );
		$add_html = ' ' . implode( ' ', $add_html ) . '  ';

		if ( $echo ) {
			echo $add_html; // WPCS: XSS ok.
		}

		return $add_html;
	}

	private static function add_input_classes( $field, array &$class ) {
		if ( isset( $field['input_class'] ) && ! empty( $field['input_class'] ) ) {
			$class[] = $field['input_class'];
		}

		if ( $field['type'] == 'hidden' || $field['type'] == 'user_id' ) {
			return;
		}

		if ( isset( $field['size'] ) && $field['size'] > 0 ) {
			$class[] = 'auto_width';
		}
	}

	private static function add_html_size( $field, array &$add_html ) {
		$size_fields = array(
			'select',
			'data',
			'time',
			'hidden',
			'file',
			'lookup',
		);

		if ( ! isset( $field['size'] ) || $field['size'] <= 0 || in_array( $field['type'], $size_fields ) ) {
			return;
		}

		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
			return;
		}

		if ( is_numeric( $field['size'] ) ) {
			$field['size'] .= 'px';
		}

		$important = apply_filters( 'frm_use_important_width', 1, $field );
		// Note: This inline styling must stay since we cannot realistically set a class for every possible field size.
		$add_html['style'] = 'style="width:' . esc_attr( $field['size'] ) . ( $important ? ' !important' : '' ) . '"';

		self::add_html_cols( $field, $add_html );
	}

	private static function add_html_cols( $field, array &$add_html ) {
		if ( ! in_array( $field['type'], array( 'textarea', 'rte' ) ) ) {
			return;
		}

		// Convert to cols for textareas.
		$calc = array(
			''    => 9,
			'px'  => 9,
			'rem' => 0.444,
			'em'  => 0.544,
		);

		// include "col" for valid html
		$unit = trim( preg_replace( '/[0-9]+/', '', $field['size'] ) );

		if ( ! isset( $calc[ $unit ] ) ) {
			return;
		}

		$size = (float) str_replace( $unit, '', $field['size'] ) / $calc[ $unit ];

		$add_html['cols'] = 'cols="' . absint( $size ) . '"';
	}

	private static function add_html_length( $field, array &$add_html ) {
		// Check for max setting and if this field accepts maxlength.
		$fields = array(
			'textarea',
			'rte',
			'hidden',
			'file',
		);

		if ( FrmField::is_option_empty( $field, 'max' ) || in_array( $field['type'], $fields ) ) {
			return;
		}

		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
			// Don't load on form builder page.
			return;
		}

		$add_html['maxlength'] = 'maxlength="' . esc_attr( $field['max'] ) . '"';
	}

	private static function add_html_placeholder( $field, array &$add_html, array &$class ) {
		if ( $field['default_value'] != '' ) {
			if ( is_array( $field['default_value'] ) ) {
				$add_html['data-frmval'] = 'data-frmval="' . esc_attr( json_encode( $field['default_value'] ) ) . '"';
			} else {
				self::add_frmval_to_input( $field, $add_html );
			}
		}

		$field['placeholder'] = self::prepare_placeholder( $field );
		if ( $field['placeholder'] == '' || is_array( $field['placeholder'] ) ) {
			// don't include a json placeholder
			return;
		}

		$frm_settings = FrmAppHelper::get_settings();

		if ( $frm_settings->use_html ) {
			self::add_placeholder_to_input( $field, $add_html );
		} else {
			self::add_frmval_to_input( $field, $add_html );

			$class[] = 'frm_toggle_default';

			if ( $field['value'] == $field['placeholder'] ) {
				$class[] = 'frm_default';
			}
		}
	}

	private static function prepare_placeholder( $field ) {
		$is_placeholder_field = FrmFieldsHelper::is_placeholder_field_type( $field['type'] );
		$is_combo_field       = in_array( $field['type'], array( 'address', 'credit_card' ) );

		$placeholder = $field['placeholder'];
		if ( empty( $placeholder ) && $is_placeholder_field && ! $is_combo_field ) {
			$placeholder = self::get_default_value_from_name( $field );
		}

		return $placeholder;
	}

	/**
	 * If the label position is "inside",
	 * get the label to use as the placeholder
	 *
	 * @since 2.05
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	public static function get_default_value_from_name( $field ) {
		$position = FrmField::get_option( $field, 'label' );
		if ( $position == 'inside' ) {
			$default_value = $field['name'];
		} else {
			$default_value = '';
		}

		return $default_value;
	}

	/**
	 * Use HMTL5 placeholder with js fallback
	 *
	 * @param array $field
	 * @param array $add_html
	 */
	private static function add_placeholder_to_input( $field, &$add_html ) {
		if ( FrmFieldsHelper::is_placeholder_field_type( $field['type'] ) ) {
			$add_html['placeholder'] = 'placeholder="' . esc_attr( $field['placeholder'] ) . '"';
		}
	}

	private static function add_frmval_to_input( $field, &$add_html ) {
		if ( $field['placeholder'] != '' ) {
			$add_html['data-frmval'] = 'data-frmval="' . esc_attr( $field['placeholder'] ) . '"';

			if ( 'select' === $field['type'] ) {
				$add_html['data-frmplaceholder'] = 'data-frmplaceholder="' . esc_attr( $field['placeholder'] ) . '"';
			}
		}

		if ( $field['default_value'] != '' ) {
			$add_html['data-frmval'] = 'data-frmval="' . esc_attr( $field['default_value'] ) . '"';
		}
	}

	private static function add_validation_messages( $field, array &$add_html ) {
		if ( FrmField::is_required( $field ) ) {
			$required_message        = FrmFieldsHelper::get_error_msg( $field, 'blank' );
			$add_html['data-reqmsg'] = 'data-reqmsg="' . esc_attr( $required_message ) . '"';
			self::maybe_add_html_required( $field, $add_html );
		}

		if ( ! FrmField::is_option_empty( $field, 'invalid' ) ) {
			$invalid_message         = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
			$add_html['data-invmsg'] = 'data-invmsg="' . esc_attr( $invalid_message ) . '"';
		}
	}

	/**
	 * If 'required' is added to a conditionall hidden field, the form won't
	 * submit in many browsers. Check to make sure the javascript to conditionally
	 * remove it is present if needed.
	 *
	 * @since 3.06.01
	 * @param array $field
	 * @param array $add_html
	 */
	private static function maybe_add_html_required( $field, array &$add_html ) {
		if ( in_array( $field['type'], array( 'radio', 'checkbox', 'file', 'data', 'lookup' ) ) ) {
			return;
		}

		$include_html = FrmAppHelper::meets_min_pro_version( '3.06.01' );
		if ( $include_html ) {
			$add_html['aria-required'] = 'aria-required="true"';
		}
	}

	private static function add_shortcodes_to_html( $field, array &$add_html ) {
		if ( FrmField::is_option_empty( $field, 'shortcodes' ) ) {
			return;
		}

		foreach ( $field['shortcodes'] as $k => $v ) {
			if ( 'opt' === $k ) {
				continue;
			}

			if ( is_numeric( $k ) && strpos( $v, '=' ) ) {
				$add_html[] = $v;
			} elseif ( ! empty( $k ) && isset( $add_html[ $k ] ) ) {
				$add_html[ $k ] = str_replace( $k . '="', $k . '="' . $v, $add_html[ $k ] );
			} else {
				$add_html[ $k ] = $k . '="' . esc_attr( $v ) . '"';
			}

			unset( $k, $v );
		}
	}

	/**
	 * Add pattern attribute
	 *
	 * @since 3.0
	 *
	 * @param array $field
	 * @param array $add_html
	 */
	private static function add_pattern_attribute( $field, array &$add_html ) {
		$has_format   = FrmField::is_option_true_in_array( $field, 'format' );
		$format_field = FrmField::is_field_type( $field, 'text' );

		if ( $field['type'] == 'phone' || ( $has_format && $format_field ) ) {
			$frm_settings = FrmAppHelper::get_settings();

			if ( $frm_settings->use_html ) {
				$format = FrmEntryValidate::phone_format( $field );
				$format = substr( $format, 2, - 1 );

				$add_html['pattern'] = 'pattern="' . esc_attr( $format ) . '"';
			}
		}
	}

	public static function check_value( $opt, $opt_key, $field ) {
		if ( is_array( $opt ) ) {
			if ( FrmField::is_option_true( $field, 'separate_value' ) ) {
				$opt = isset( $opt['value'] ) ? $opt['value'] : ( isset( $opt['label'] ) ? $opt['label'] : reset( $opt ) );
			} else {
				$opt = isset( $opt['label'] ) ? $opt['label'] : reset( $opt );
			}
		}

		return $opt;
	}

	public static function check_label( $opt ) {
		if ( is_array( $opt ) ) {
			$opt = ( isset( $opt['label'] ) ? $opt['label'] : reset( $opt ) );
		}

		return $opt;
	}

	/**
	 * @deprecated 4.0
	 */
	public static function update_ajax_option() {
		_deprecated_function( __METHOD__, '4.0' );
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field', 0, 'absint' );
		if ( ! $field_id ) {
			wp_die();
		}

		$field = FrmField::getOne( $field_id );

		if ( isset( $_POST['separate_value'] ) ) {
			$new_val = FrmField::is_option_true( $field, 'separate_value' ) ? 0 : 1;

			$field->field_options['separate_value'] = $new_val;
			unset( $new_val );
		}

		FrmField::update(
			$field_id,
			array(
				'field_options' => $field->field_options,
				'form_id'       => $field->form_id,
			)
		);
		wp_die();
	}

	/**
	 * @deprecated 4.0
	 */
	public static function import_choices() {
		_deprecated_function( __METHOD__, '4.0' );
		wp_die();
	}

	/**
	 * Add Single Option or Other Option.
	 *
	 * @deprecated 4.0 Moved to Pro for Other option only.
	 */
	public static function add_option() {
		_deprecated_function( __METHOD__, '4.0', 'FrmProFormsController::add_other_option' );
		if ( is_callable( 'FrmProFormsController::add_other_option' ) ) {
			FrmProFormsController::add_other_option();
		}
	}

	/**
	 * @deprecated 4.0
	 */
	public static function update_order() {
		FrmDeprecated::update_order();
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function edit_name( $field = 'name', $id = '' ) {
		FrmDeprecated::edit_name( $field, $id );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 *
	 * @param int $field_id
	 * @param array $values
	 * @param int $form_id
	 *
	 * @return array
	 */
	public static function include_single_field( $field_id, $values, $form_id = 0 ) {
		return FrmDeprecated::include_single_field( $field_id, $values, $form_id );
	}

	/**
	 * @deprecated 2.3
	 * @codeCoverageIgnore
	 */
	public static function edit_option() {
		FrmDeprecated::deprecated( __METHOD__, '2.3' );
	}

	/**
	 * @deprecated 2.3
	 * @codeCoverageIgnore
	 */
	public static function delete_option() {
		FrmDeprecated::deprecated( __METHOD__, '2.3' );
	}
}
