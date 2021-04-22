<?php
/**
 * Combo field - Field contains sub fields
 *
 * @package Formidable
 * @since 4.10.01
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFieldCombo extends FrmFieldType {

	/**
	 * Does the html for this field label need to include "for"?
	 *
	 * @var bool
	 * @since 3.0
	 */
	protected $has_for_label = false;

	/**
	 * Sub fields.
	 *
	 * @var array
	 */
	protected $sub_fields = array();

	/**
	 * This is used to check if field is combo field.
	 *
	 * @var bool
	 */
	public $is_combo_field = true;

	/**
	 * Gets ALL subfields.
	 *
	 * @return array
	 */
	public function get_sub_fields() {
		return $this->sub_fields;
	}

	/**
	 * Registers sub fields.
	 *
	 * @param array $sub_fields Sub fields. Accepts array or array or array of string.
	 */
	protected function register_sub_fields( array $sub_fields ) {
		$defaults = $this->get_default_sub_field();

		foreach ( $sub_fields as $name => $sub_field ) {
			if ( empty( $sub_field ) ) {
				continue;
			}

			if ( is_array( $sub_field ) ) {
				$sub_field                 = wp_parse_args( $sub_field, $defaults );
				$sub_field['name']         = $name;
				$this->sub_fields[ $name ] = $sub_field;
				continue;
			}

			if ( is_string( $sub_field ) ) {
				$this->sub_fields[ $name ] = wp_parse_args(
					array(
						'name'  => $name,
						'label' => $sub_field,
					),
					$defaults
				);
			}
		}
	}

	/**
	 * Gets default sub field.
	 *
	 * @return array
	 */
	protected function get_default_sub_field() {
		return array(
			'type'     => 'text',
			'label'    => '',
			'classes'  => '',
			'options'  => array(
				'default_value',
				'placeholder',
				'desc',
			),
			'optional' => false,
			'atts'     => array(),
		);
	}

	/**
	 * Registers extra options for saving.
	 *
	 * @return array
	 */
	protected function extra_field_opts() {
		$extra_options = parent::extra_field_opts();

		// Register for sub field options.
		foreach ( $this->sub_fields as $key => $sub_field ) {
			if ( empty( $sub_field['options'] ) || ! is_array( $sub_field['options'] ) ) {
				continue;
			}

			foreach ( $sub_field['options'] as $option ) {
				if ( 'default_value' === $option ) { // We parse default value from field column.
					continue;
				}

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
		$default_value = $this->get_default_value();

		foreach ( $this->sub_fields as $name => $sub_field ) {
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

			foreach ( $this->sub_fields as $name => $sub_field ) {
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

		$field_name = $this->html_name( $name );

		$this->load_field_output( compact( 'field', 'field_name' ) );
	}

	/**
	 * Gets processed sub fields.
	 * This should return the list of sub fields after sorting or show/hide based of some options.
	 *
	 * @return array
	 */
	protected function get_processed_sub_fields() {
		return $this->sub_fields;
	}

	/**
	 * Shows field in the frontend.
	 *
	 * @param array $args           Arguments.
	 * @param array $shortcode_atts Shortcode attributes.
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$field = (array) $this->field;

		$field['default_value'] = $this->get_default_value();
		if ( empty( $field['value'] ) ) {
			$field['value'] = $field['default_value'];
		}

		$args['field']          = $field;
		$args['shortcode_atts'] = $shortcode_atts;

		ob_start();
		$this->load_field_output( $args );
		$input_html = ob_get_clean();

		return $input_html;
	}

	/**
	 * Loads field output.
	 *
	 * @param array $args {
	 *     Arguments.
	 *
	 *     @type array  $field          Field array.
	 *     @type string $html_id        HTML ID.
	 *     @type string $field_name     Field name attribute.
	 *     @type array  $shortcode_atts Shortcode attributes.
	 *     @type array  $errors         Field errors.
	 *     @type bool   $remove_names   Remove name attribute or not.
	 * }
	 */
	protected function load_field_output( $args ) {
		if ( empty( $args['field'] ) ) {
			return;
		}

		$args['field'] = (array) $args['field'];

		if ( ! isset( $args['html_id'] ) ) {
			$args['html_id'] = $this->html_id();
		}

		if ( ! isset( $args['field_name'] ) ) {
			$args['field_name'] = $this->html_name( $args['field']['name'] );
		}

		$args['sub_fields'] = $this->get_processed_sub_fields();

		if ( ! isset( $args['shortcode_atts'] ) ) {
			$args['shortcode_atts'] = array();
		}

		if ( ! isset( $args['errors'] ) ) {
			$args['errors'] = array();
		}

		if ( file_exists( FrmAppHelper::plugin_path() . "/classes/views/frm-fields/front-end/{$args['field']['type']}-field/{$args['field']['type']}-field.php" ) ) {
			include FrmAppHelper::plugin_path() . "/classes/views/frm-fields/front-end/{$args['field']['type']}-field/{$args['field']['type']}-field.php";
		} else {
			include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/front-end/combo-field/combo-field.php';
		}
	}

	/**
	 * Prints sub field input atts.
	 *
	 * @param array $args Arguments. Includes `field`, `sub_field`.
	 */
	protected function print_input_atts( $args ) {
		$field     = $args['field'];
		$sub_field = $args['sub_field'];
		$atts      = array();

		// Placeholder.
		if ( in_array( 'placeholder', $sub_field['options'] ) ) {
			$placeholder = FrmField::get_option( $field, $sub_field['name'] . '_placeholder' );
			if ( $placeholder ) {
				$atts[] = 'placeholder="' . esc_attr( $placeholder ) . '"';
			}
		}

		// Add optional class.
		$classes = isset( $sub_field['classes'] ) ? $sub_field['classes'] : '';
		if ( is_array( $classes ) ) {
			$classes = implode( ' ', $classes );
		}

		if ( ! empty( $sub_field['optional'] ) ) {
			$classes .= ' frm_optional';
		}

		if ( $classes ) {
			$atts[] = 'class="' . esc_attr( $classes ) . '"';
		}

		// Print custom attributes
		if ( ! empty( $sub_field['atts'] ) && is_array( $sub_field['atts'] ) ) {
			foreach ( $sub_field['atts'] as $att_name => $att_value ) {
				$atts[] = esc_attr( trim( $att_name ) ) . '="' . esc_attr( trim( $att_value ) ) . '"';
			}
		}

		echo implode( ' ', $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Validate field.
	 *
	 * @param array $args Arguments. Includes `errors`, `value`.
	 * @return array Errors array.
	 */
	public function validate( $args ) {
		$errors    = isset( $args['errors'] ) ? $args['errors'] : array();

		if ( ! $this->field->required ) {
			return $errors;
		}

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

	/**
	 * Gets export headings.
	 *
	 * @return array
	 */
	public function get_export_headings() {
		$headings   = array();
		$field_id   = FrmField::get_option( $this->field, 'id' );
		$field_name = FrmField::get_option( $this->field, 'name' );
		foreach ( $this->sub_fields as $name => $sub_field ) {
			$headings[ $field_id . '_' . $name ] = $field_name . ' - ' . $sub_field['label'];
		}

		return $headings;
	}
}
