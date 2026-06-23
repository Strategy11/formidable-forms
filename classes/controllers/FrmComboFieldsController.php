<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Base controller for combo fields (fields with multiple sub-fields like Address).
 *
 * @since x.x
 */
class FrmComboFieldsController {

	/**
	 * Get sub fields for combo fields.
	 *
	 * @since x.x
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	public static function get_sub_fields( $field ) {
		return array();
	}

	/**
	 * Fill values with defaults.
	 *
	 * @since x.x
	 *
	 * @param mixed $value
	 * @param array $defaults
	 *
	 * @return void
	 */
	public static function fill_values( &$value, $defaults ) {
		$value = $value ? array_merge( $defaults, (array) $value ) : $defaults;
	}

	/**
	 * Include placeholder attribute for sub-field.
	 *
	 * @since x.x
	 *
	 * @param mixed  $default_value
	 * @param string $sub_field
	 * @param array  $field
	 *
	 * @return void
	 */
	public static function include_placeholder( $default_value, $sub_field, $field = array() ) {
		if ( ! is_array( $default_value ) ) {
			$default_value = array();
		}

		if ( $field ) {
			$use_placeholder = ! FrmField::is_option_empty( $field, 'placeholder' );

			if ( ( ! $use_placeholder || empty( $default_value[ $sub_field ] ) ) && $sub_field === 'line1' ) {
				// Allow for 'inside' label position.
				$default_value[ $sub_field ] = FrmFieldsController::get_default_value_from_name( $field );
				$use_placeholder             = $default_value[ $sub_field ] !== '';
			}

			if ( ! $use_placeholder ) {
				return;
			}
		}

		if ( ! $default_value ) {
			return;
		}

		$placeholder = $default_value[ $sub_field ] ?? '';
		echo ' placeholder="' . esc_attr( $placeholder ) . '" data-placeholder="' . esc_attr( $placeholder ) . '"';
	}

	/**
	 * Get dropdown label for select sub-fields.
	 *
	 * @since x.x
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public static function get_dropdown_label( $atts ) {
		$default = $atts['sub_field']['placeholder'] ?? ' ';
		return apply_filters( 'frm_combo_dropdown_label', $default, $atts );
	}

	/**
	 * Add attributes to input field.
	 *
	 * @since x.x
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public static function add_atts_to_input( $atts ) {
		$placeholder   = $atts['field']['placeholder'] ?? '';
		$default_value = $atts['field']['default_value'];
		$field_obj     = FrmFieldFactory::get_field_type( $atts['field']['type'], $atts['field'] );

		if ( $atts['field']['type'] === 'address' ) {
			/**
			 * @var FrmFieldAddress $field_obj
			 */
			$placeholder   = $field_obj->address_string_to_array( $placeholder );
			$default_value = $field_obj->address_string_to_array( $default_value );
		}

		self::include_placeholder( $placeholder, $atts['key'], $atts['field'] );
		$atts['field']['placeholder'] = '';

		$atts['field']['default_value'] = $default_value[ $atts['key'] ] ?? '';

		if ( 'select' === $atts['sub_field']['type'] && ! empty( $atts['field']['read_only'] ) ) {
			$atts['sub_field']['atts']['disabled'] = 'disabled';
		}

		if ( ! empty( $atts['sub_field']['optional'] ) ) {
			add_filter( 'frm_field_classes', 'FrmAddressesController::add_optional_class', 20, 2 );
			do_action( 'frm_field_input_html', $atts['field'] );
			remove_filter( 'frm_field_classes', 'FrmAddressesController::add_optional_class', 20 );
		} else {
			do_action( 'frm_field_input_html', $atts['field'] );
		}

		if ( ! isset( $atts['sub_field']['atts'] ) ) {
			return;
		}

		foreach ( $atts['sub_field']['atts'] as $att_name => $att_value ) {
			echo ' ' . esc_attr( $att_name ) . '="' . esc_attr( $att_value ) . '"';
		}
	}

	/**
	 * Include sub-label for field.
	 *
	 * @since x.x
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public static function include_sub_label( $atts ) {
		self::show_sub_label( $atts );
	}

	/**
	 * Show sub-label for field.
	 *
	 * @since x.x
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public static function show_sub_label( $atts ) {
		$field       = $atts['field'];
		$option_name = $atts['option_name'];

		if ( $field[ $option_name ] !== '' ) {
			echo '<div class="frm_description">' . wp_kses_post( $field[ $option_name ] ) . '</div>';
		}
	}

	/**
	 * Add error class if field has error.
	 *
	 * @since x.x
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public static function maybe_add_error_class( $atts ) {
		$temp_id   = ! empty( $atts['atts']['field_id'] ) ? $atts['atts']['field_id'] : $atts['field']['id'];
		$has_error = isset( $atts['errors'][ 'field' . $temp_id . '-' . $atts['key'] ] );

		if ( $has_error ) {
			echo ' frm_blank_field';
		}
	}
}
