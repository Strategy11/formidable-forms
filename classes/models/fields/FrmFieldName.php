<?php
/**
 * Name field
 *
 * @package Formidable
 * @since 4.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFieldName extends FrmFieldCombo {

	/**
	 * Field name.
	 *
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'name';

	/**
	 * Could this field hold email values?
	 *
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	public function __construct( $field = '', $type = '' ) {
		parent::__construct( $field, $type );

		$this->register_sub_fields(
			array(
				'first'  => __( 'First', 'formidable' ),
				'middle' => __( 'Middle', 'formidable' ),
				'last'   => __( 'Last', 'formidable' ),
			)
		);
	}

	/**
	 * Gets processed sub fields.
	 * This should return the list of sub fields after sorting or show/hide based of some options.
	 *
	 * @return array
	 */
	protected function get_processed_sub_fields() {
		$name_layout = $this->get_name_layout();
		$names       = explode( '_', $name_layout );
		$col_class   = 'frm' . intval( 12 / count( $names ) );

		$result = array();

		foreach ( $names as $name ) {
			if ( empty( $this->sub_fields[ $name ] ) ) {
				continue;
			}

			if ( ! isset( $this->sub_fields[ $name ]['wrapper_classes'] ) ) {
				$this->sub_fields[ $name ]['wrapper_classes'] = $col_class;
			} elseif ( is_array( $this->sub_fields[ $name ]['wrapper_classes'] ) ) {
				$this->sub_fields[ $name ]['wrapper_classes'] = implode( ' ', $this->sub_fields[ $name ]['wrapper_classes'] ) . ' ' . $col_class;
			} else {
				$this->sub_fields[ $name ]['wrapper_classes'] .= ' ' . $col_class;
			}

			$result[ $name ] = $this->sub_fields[ $name ];
		}

		return $result;
	}

	/**
	 * Gets name layout option value.
	 *
	 * @return string
	 */
	protected function get_name_layout() {
		$name_layout = FrmField::get_option( $this->field, 'name_layout' );
		if ( ! $name_layout ) {
			$name_layout = 'first_last';
		}
		return $name_layout;
	}

	/**
	 * Gets extra field options.
	 *
	 * @return string[]
	 */
	protected function extra_field_opts() {
		$extra_options = parent::extra_field_opts();

		$extra_options['name_layout'] = 'first_last';

		// Default desc.
		foreach ( $this->sub_fields as $name => $sub_field ) {
			$extra_options[ $name . '_desc' ] = $sub_field['label'];
		}

		return $extra_options;
	}

	/**
	 * Shows primary options.
	 *
	 * @since 4.0
	 *
	 * @param array $args Includes 'field', 'display', and 'values'.
	 *
	 * @return void
	 */
	public function show_primary_options( $args ) {
		$field = (array) $args['field'];
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/name/primary-options.php';

		parent::show_primary_options( $args );
	}

	/**
	 * Prepares the display value.
	 * This also handles the shortcode output. Support [id], [id show=first], [id show=last], [id show=middle].
	 *
	 * @param mixed $value Field value before processing.
	 * @param array $atts  Shortcode attributes.
	 * @return string      Most of cases, this will return string.
	 */
	protected function prepare_display_value( $value, $atts ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$name_layout = $this->get_name_layout();

		if ( ! empty( $atts['show'] ) ) {
			return isset( $value[ $atts['show'] ] ) ? $value[ $atts['show'] ] : '';
		}

		$value = wp_parse_args(
			$value,
			array(
				'first'  => '',
				'middle' => '',
				'last'   => '',
			)
		);

		switch ( $name_layout ) {
			case 'last_first':
				$value = $value['last'] . ' ' . $value['first'];
				break;

			case 'first_middle_last':
				$value = $value['first'] . ' ' . $value['middle'] . ' ' . $value['last'];
				break;

			default:
				$value = $value['first'] . ' ' . $value['last'];
		}

		return trim( $value );
	}

	/**
	 * @since 4.0.04
	 *
	 * @return void
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}

	/**
	 * Validate field.
	 *
	 * @param array $args Arguments. Includes `errors`, `value`.
	 * @return array Errors array.
	 */
	public function validate( $args ) {
		/**
		 * If users fill just HTML tag, it passes the validation but value is empty in the database because of the
		 * sanitization. So we need to sanitize the value before validating.
		 */
		$this->sanitize_value( $args['value'] );
		return parent::validate( $args );
	}

	/**
	 * Loads processed args for field output.
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
	 *
	 * @return void
	 */
	protected function process_args_for_field_output( &$args ) {
		parent::process_args_for_field_output( $args );

		// Show all subfields in form builder then use JS to show or hide them.
		if ( $this->should_print_hidden_sub_fields() && count( $args['sub_fields'] ) !== count( $this->sub_fields ) ) {
			$hidden_fields      = array_diff_key( $this->sub_fields, $args['sub_fields'] );
			$args['sub_fields'] = $this->sub_fields;

			foreach ( $hidden_fields as $name => $hidden_field ) {
				$args['sub_fields'][ $name ]['wrapper_classes'] .= ' frm_hidden';
			}
		}
	}

	/**
	 * Checks if should print hidden subfields and hide them. This is useful to use js to show or hide sub fields.
	 *
	 * @return bool
	 */
	protected function should_print_hidden_sub_fields() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		return FrmAppHelper::is_form_builder_page() || FrmAppHelper::doing_ajax() && isset( $_POST['action'] ) && 'frm_insert_field' === $_POST['action'];
	}

	/**
	 * Gets inputs container attributes.
	 *
	 * @return array
	 */
	protected function get_inputs_container_attrs() {
		$attrs = parent::get_inputs_container_attrs();

		$attrs['data-name-layout'] = $this->get_name_layout();
		return $attrs;
	}
}
