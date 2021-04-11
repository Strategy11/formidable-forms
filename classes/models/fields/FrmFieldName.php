<?php
/**
 * Name field
 *
 * @package Formidable
 * @since 4.10.01
 */

class FrmFieldName extends FrmFieldCombo {

	/**
	 * Field name.
	 *
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'name';


	public function get_sub_fields() {
		// TODO: Implement get_sub_fields() method.
		return array(
			'first'  => array(
				'type'     => 'text',
				'label'    => __( 'First', 'formidable' ),
				'classes'  => '',
				'options'  => array(
					'default_value',
					'placeholder',
					'desc',
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

	protected function get_processed_sub_fields() {
		$sub_fields  = $this->get_sub_fields();

		$name_layout = FrmField::get_option( $this->field, 'name_layout' );

		if ( 'last_first' === $name_layout ) {
			$sub_fields['first']['classes'] .= ' frm6';
			$sub_fields['last']['classes']  .= ' frm6';

			return array(
				'last'  => $sub_fields['last'],
				'first' => $sub_fields['first'],
			);
		}

		if ( 'first_middle_last' === $name_layout ) {
			$sub_fields['first']['classes']  .= ' frm4';
			$sub_fields['middle']['classes'] .= ' frm4';
			$sub_fields['last']['classes']   .= ' frm4';

			return array(
				'first'  => $sub_fields['first'],
				'middle' => $sub_fields['middle'],
				'last'   => $sub_fields['last'],
			);
		}

		$sub_fields['first']['classes'] .= ' frm6';
		$sub_fields['last']['classes']  .= ' frm6';

		return array(
			'first' => $sub_fields['first'],
			'last'  => $sub_fields['last'],
		);
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
	 * @since 3.0
	 *
	 * @param array|string $value
	 * @param array $atts
	 *
	 * @return array|string
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
		error_log( print_r( $value, true ) );
	}
}
