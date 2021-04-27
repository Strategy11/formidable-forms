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
		$name_layout = FrmField::get_option( $this->field, 'name_layout' );
		$names       = explode( '_', $name_layout );
		$col_class   = 'frm' . intval( 12 / count( $names ) );

		$result = array();

		foreach ( $names as $name ) {
			if ( empty( $this->sub_fields[ $name ] ) ) {
				continue;
			}

			if ( ! isset( $this->sub_fields[ $name ]['classes'] ) ) {
				$this->sub_fields[ $name ]['classes'] = $col_class;
			} elseif ( is_array( $this->sub_fields[ $name ]['classes'] ) ) {
				$this->sub_fields[ $name ]['classes'] = implode( ' ', $this->sub_fields[ $name ]['classes'] ) . ' ' . $col_class;
			} else {
				$this->sub_fields[ $name ]['classes'] .= ' ' . $col_class;
			}

			$result[ $name ] = $this->sub_fields[ $name ];
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

	/**
	 * @return array
	 */
	public function translatable_strings() {
		$strings   = parent::translatable_strings();
		$strings[] = 'first_desc';
		$strings[] = 'middle_desc';
		$strings[] = 'last_desc';
		return $strings;
	}
}
