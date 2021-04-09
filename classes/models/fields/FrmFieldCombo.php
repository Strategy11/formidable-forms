<?php
/**
 * Combo field - Field contains sub fields
 *
 * @package Formidable
 * @since 4.10.01
 */

abstract class FrmFieldCombo extends FrmFieldType {

	/**
	 * Does the html for this field label need to include "for"?
	 *
	 * @var bool
	 * @since 3.0
	 */
	protected $has_for_label = false;

	/**
	 * Gets sub fields.
	 *
	 * @return array
	 */
	abstract public function get_sub_fields();

	/**
	 * Registers extra options for saving.
	 *
	 * @return array
	 */
	protected function extra_field_opts() {
		$extra_options = parent::extra_field_opts();

		$sub_fields = $this->get_sub_fields();
		foreach ( $sub_fields as $key => $sub_field ) {
			if ( empty( $sub_field['options'] ) || ! is_array( $sub_field['options'] ) ) {
				continue;
			}

			foreach ( $sub_field['options'] as $option ) {
				if ( is_string( $option ) ) {
					$extra_options[ $key . '_' . $option ] = '';
				} elseif ( ! empty( $option['name'] ) ) {
					$extra_options[ $key . '_' . $option['name'] ] = '';
				}
			}
		}

		return $extra_options;
	}

	/**
	 * Include the settings for placeholder, default value, and sub labels for each
	 * of the individual field labels.
	 *
	 * @since 4.0
	 * @param array $args Includes 'field', 'display'.
	 */
	public function show_after_default( $args ) {
		$field         = $args['field'];
		$sub_fields    = $this->get_sub_fields();
		$default_value = $this->get_default_value();

		foreach ( $sub_fields as $name => $sub_field ) {
			$sub_field['name'] = $name;

			include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/combo-field/sub-field-options.php';
		}
	}

	/**
	 * Gets default value.
	 *
	 * @return array
	 */
	protected function get_default_value() {
		$default_value = $this->get_field_column( 'default_value' );

		if ( is_array( $default_value ) ) {
			return $default_value;
		}

		if ( is_object( $default_value ) ) {
			return (array) $default_value;
		}

		if ( ! $default_value ) {
			return array();
		}

		return json_decode( $default_value, true );
	}

	/**
	 * Gets built-in option labels.
	 *
	 * @return array
	 */
	protected function get_built_in_option_labels() {
		return array(
			'default_value' => __( 'Default Value', 'formidable' ),
			'placeholder'   => __( 'Placeholder Text', 'formidable' ),
			'desc'          => __( 'Description', 'formidable' ),
		);
	}

	/**
	 * Which built-in settings this field supports?
	 *
	 * @return array
	 */
	protected function field_settings_for_type() {
		$settings = array(
			'description'    => false,
			'default'        => false,
			'clear_on_focus' => false, // Don't use the regular placeholder option.
		);

		return $settings;
	}

	/**
	 * Shows field on the form builder.
	 *
	 * @param string $name Field name.
	 */
	public function show_on_form_builder( $name = '' ) {
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );

//		$defaults = $this->empty_value_array();
//		$this->fill_values( $field['default_value'], $defaults );

		$field['default_value'] = $this->get_default_value();
		$field['value']         = $field['default_value'];

		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/combo-field/show-on-form-builder.php';
	}

	/**
	 * Gets processed sub fields.
	 * You need to change this if field contains an option to sort sub fields.
	 *
	 * @return array
	 */
	protected function get_processed_sub_fields() {
		return $this->get_sub_fields();
	}

	protected function add_atts_to_input( $atts ) {
		return;
		$placeholder   = isset( $atts['field']['placeholder'] ) ? $atts['field']['placeholder'] : '';
		$default_value = $atts['field']['default_value'];

//		self::include_placeholder( $placeholder, $atts['key'], $atts['field'] );
		$atts['field']['placeholder'] = '';

		if ( isset( $default_value[ $atts['key'] ] ) ) {
			$atts['field']['default_value'] = $default_value[ $atts['key'] ];
		} else {
			$atts['field']['default_value'] = '';
		}

		if ( isset( $atts['sub_field']['optional'] ) && $atts['sub_field']['optional'] ) {
			add_filter( 'frm_field_classes', 'FrmProAddressesController::add_optional_class', 20, 2 );
			do_action( 'frm_field_input_html', $atts['field'] );
			remove_filter( 'frm_field_classes', 'FrmProAddressesController::add_optional_class', 20 );
		} else {
			do_action( 'frm_field_input_html', $atts['field'] );
		}

		if ( isset( $atts['sub_field']['atts'] ) ) {
			foreach ( $atts['sub_field']['atts'] as $att_name => $att_value ) {
				echo ' ' . esc_attr( $att_name ) . '="' . esc_attr( $att_value ) . '"';
			}
		}
	}
}
