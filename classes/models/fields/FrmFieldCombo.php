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
	 * Gets ALL sub fields.
	 *
	 * @return array
	 */
	abstract protected function get_sub_fields();

	/**
	 * Registers extra options for saving.
	 *
	 * @return array
	 */
	protected function extra_field_opts() {
		$extra_options = parent::extra_field_opts();

		// Register for sub field options.
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
	 * Gets default value of field.
	 * This should return an array of default value of sub fields.
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
			$default_value = array();
			$sub_fields    = $this->get_sub_fields();

			foreach ( $sub_fields as $name => $sub_field ) {
				$default_value[ $name ] = '';
			}

			return $default_value;
		}

		return json_decode( $default_value, true ); // We store default value as JSON string in db.
	}

	/**
	 * Gets labels for built-in options of fields or sub fields.
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

		$field['default_value'] = $this->get_default_value();
		$field['value']         = $field['default_value'];

		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/combo-field/show-on-form-builder.php';
	}

	/**
	 * Gets processed sub fields.
	 * This should return the list of sub fields after sorting or show/hide based of some options.
	 *
	 * @return array
	 */
	protected function get_processed_sub_fields() {
		return $this->get_sub_fields();
	}

	/**
	 * Shows field in the frontend.
	 *
	 * @param array $args           Arguments.
	 * @param array $shortcode_atts Shortcode attributes.
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$field      = $this->field;
		$sub_fields = $this->get_processed_sub_fields();

		$field['default_value'] = $this->get_default_value();
		if ( empty( $field['value'] ) ) {
			$field['value'] = $field['default_value'];
		}

		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/front-end/combo-field/combo-field.php';
		$input_html = ob_get_clean();

		return $input_html;
	}


	/**
	 * Prints sub field input atts.
	 *
	 * @param array $args Arguments. Includes `field`, `sub_field`.
	 */
	protected function print_input_atts( $args ) {
		$field     = $args['field'];
		$sub_field = $args['sub_field'];

		// Placeholder.
		if ( ! empty( $field['field_options'][ $sub_field['name'] . '_placeholder' ] ) ) {
			echo 'placeholder="' . esc_attr( $field['field_options'][ $sub_field['name'] . '_placeholder' ] ) . '" ';
		}

		// Add optional class.
		if ( isset( $sub_field['optional'] ) && $sub_field['optional'] ) {
			add_filter( 'frm_field_classes', array( $this, 'add_optional_class' ), 20, 2 );
			do_action( 'frm_field_input_html', $field );
			remove_filter( 'frm_field_classes', array( $this, 'add_optional_class' ), 20 );
		} else {
			do_action( 'frm_field_input_html', $field );
		}

		// Print custom attributes declared in get_sub_fields() method.
		if ( ! empty( $sub_field['atts'] ) && is_array( $sub_field['atts'] ) ) {
			foreach ( $sub_field['atts'] as $att_name => $att_value ) {
				echo ' ' . esc_attr( $att_name ) . '="' . esc_attr( $att_value ) . '"';
			}
		}
	}

	/**
	 * Adds optional class.
	 *
	 * @param string $classes CSS classes.
	 * @param string $field   Field data.
	 * @return string
	 */
	public function add_optional_class( $classes, $field ) {
		$classes .= ' frm_optional';
		return $classes;
	}

	/**
	 * Validate field.
	 *
	 * @param array $args Arguments. Includes `errors`, `value`.
	 * @return array Errors array.
	 */
	public function validate( $args ) {
		$errors    = isset( $args['errors'] ) ? $args['errors'] : array();
		$blank_msg = FrmFieldsHelper::get_error_msg( $this->field, 'blank' );

		$sub_fields = $this->get_processed_sub_fields();

		// Validate not empty.
		foreach ( $sub_fields as $name => $sub_field ) {
			if ( empty( $sub_field['optional'] ) && empty( $args['value'][ $name ] ) ) {
				$errors[ 'field' . $args['id'] . '-' . $name ] = '';
				$errors[ 'field' . $args['id'] ] = $blank_msg;
			}
		}

		return $errors;
	}
}
