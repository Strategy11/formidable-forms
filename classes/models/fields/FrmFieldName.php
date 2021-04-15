<?php
/**
 * Name field
 *
 * @package Formidable
 * @since 4.10.01
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
	 * Gets ALL sub fields.
	 *
	 * @return array
	 */
	protected function get_sub_fields() {
		return array(
			'first'  => array(
				'type'     => 'text', // See supported types in classes/views/frm-fields/back-end/combo-field/show-on-form-builder.php.
				'label'    => __( 'First', 'formidable' ),
				'classes'  => '',
				'options'  => array(
					'default_value',
					'placeholder',
					'desc',
					// Maybe support array of field data in the future. See classes/views/frm-fields/back-end/combo-field/sub-field-options.php
				),
				'optional' => false,
				'atts'     => array(),
			),
			'middle' => array(
				'type'     => 'text',
				'label'    => __( 'Middle', 'formidable' ),
				'classes'  => '',
				'options'  => array(
					'default_value',
					'placeholder',
					'desc',
				),
				'optional' => false,
				'atts'     => array(),
			),
			'last'   => array(
				'type'     => 'text',
				'label'    => __( 'Last', 'formidable' ),
				'classes'  => '',
				'options'  => array(
					'default_value',
					'placeholder',
					'desc',
				),
				'optional' => false,
				'atts'     => array(),
			),
		);
	}

	/**
	 * Gets processed sub fields.
	 * This should return the list of sub fields after sorting or show/hide based of some options.
	 *
	 * @return array
	 */
	protected function get_processed_sub_fields() {
		$sub_fields  = $this->get_sub_fields();
		$name_layout = FrmField::get_option( $this->field, 'name_layout' );
		$names       = explode( '_', $name_layout );
		$col_class   = 'frm' . intval( 12 / count( $names ) );

		$result = array();

		foreach ( $names as $name ) {
			if ( empty( $sub_fields[ $name ] ) ) {
				continue;
			}

			if ( ! isset( $sub_fields[ $name ]['classes'] ) ) {
				$sub_fields[ $name ]['classes'] = $col_class;
			} elseif ( is_array( $sub_fields[ $name ]['classes'] ) ) {
				$sub_fields[ $name ]['classes'] = implode( ' ', $sub_fields[ $name ]['classes'] ) . ' ' . $col_class;
			} else {
				$sub_fields[ $name ]['classes'] .= ' ' . $col_class;
			}

			$result[ $name ] = $sub_fields[ $name ];
		}

		return $result;
	}

	/**
	 * Gets extra field options.
	 *
	 * @return string[]
	 */
	protected function extra_field_opts() {
		$extra_options = parent::extra_field_opts();

		$extra_options['name_layout'] = 'first_last';

		return $extra_options;
	}

	/**
	 * Shows primary options.
	 *
	 * @since 4.0
	 *
	 * @param array $args Includes 'field', 'display', and 'values'.
	 */
	public function show_primary_options( $args ) {
		$field = $args['field'];
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
		$name_layout = FrmField::get_option( $this->field, 'name_layout' );

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
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}
}
