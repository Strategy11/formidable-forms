<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Address field type.
 *
 * @since x.x
 */
class FrmFieldAddress extends FrmFieldCombo {

	/**
	 * @var string
	 *
	 * @since x.x
	 */
	protected $type = 'address';

	/**
	 * This is used to check if field is combo field.
	 *
	 * @var bool
	 *
	 * @since x.x
	 */
	public $is_combo_field = true;

	/**
	 * Constructor - register sub-fields.
	 *
	 * @since x.x
	 *
	 * @param array|int|object $field
	 * @param string           $type
	 */
	public function __construct( $field = 0, $type = '' ) {
		parent::__construct( $field, $type );

		$this->register_sub_fields(
			array(
				'line1'   => __( 'Line 1', 'formidable' ),
				'line2'   => __( 'Line 2', 'formidable' ),
				'city'    => __( 'City', 'formidable' ),
				'state'   => __( 'State/Province', 'formidable' ),
				'zip'     => __( 'Zip/Postal', 'formidable' ),
				'country' => __( 'Country', 'formidable' ),
			)
		);
	}

	protected function field_settings_for_type() {
		return array(
			'clear_on_focus' => false,
			'description'    => false,
			'default'        => false,
		);
	}

	protected function extra_field_opts() {
		$options                 = parent::extra_field_opts();
		$options['address_type'] = 'international';

		return $options;
	}

	/**
	 * Get processed sub-fields based on address type.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	protected function get_processed_sub_fields() {
		$address_type = FrmField::get_option( $this->field, 'address_type' );
		$sub_fields   = $this->sub_fields;

		// Set default classes and attributes
		$default_atts = array(
			'line1'   => array( 'autocomplete' => 'address-line1' ),
			'line2'   => array(
				'autocomplete' => 'address-line2',
				'optional'     => true,
			),
			'city'    => array(
				'autocomplete'    => 'address-level2',
				'wrapper_classes' => 'frm_third frm_first',
			),
			'state'   => array(
				'autocomplete'    => 'address-level1',
				'wrapper_classes' => 'frm_third',
			),
			'zip'     => array(
				'autocomplete'    => 'postal-code',
				'wrapper_classes' => 'frm_third',
			),
			'country' => array( 'autocomplete' => 'country-name' ),
		);

		foreach ( $sub_fields as $name => &$sub_field ) {
			if ( isset( $default_atts[ $name ] ) ) {
				$sub_field = array_merge( $sub_field, $default_atts[ $name ] );
			}
		}

		// Handle Europe address type (no state field)
		if ( 'europe' === $address_type ) {
			unset( $sub_fields['state'] );
			$sub_fields['city']['wrapper_classes'] = 'frm_third';
			$sub_fields['zip']['wrapper_classes']  = 'frm_third frm_first';
		}

		// Handle US address type (state dropdown)
		if ( 'us' === $address_type ) {
			$sub_fields['state']['type']    = 'select';
			$sub_fields['state']['options'] = FrmFieldsHelper::get_us_states();
		}

		// Handle international address type (country dropdown)
		if ( 'international' === $address_type ) {
			$sub_fields['country']['type']    = 'select';
			$sub_fields['country']['options'] = FrmFieldsHelper::get_countries();
		}

		// Generic type has no country dropdown
		if ( 'generic' === $address_type ) {
			unset( $sub_fields['country'] );
		}

		return $sub_fields;
	}

	/**
	 * @since x.x
	 *
	 * @param array $args - Includes 'field', 'display', and 'values'.
	 */
	public function show_primary_options( $args ) {
		$field = $args['field'];

		include FrmAppHelper::plugin_path() . '/classes/views/combo-fields/addresses/back-end-field-opts.php';

		parent::show_primary_options( $args );
	}

	/**
	 * @param array $atts
	 *
	 * @return void
	 */
	protected function fill_default_atts( &$atts ) {
		parent::fill_default_atts( $atts );
		$defaults = array(
			'line_sep'    => ' <br/>',
			'force_array' => false,
		);
		$atts     = wp_parse_args( $atts, $defaults );
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$keys_to_check                = array( 'line1', 'city', 'state', 'zip', 'country' );
		$at_least_one_field_populated = false;

		foreach ( $keys_to_check as $key ) {
			if ( ! empty( $value[ $key ] ) ) {
				$at_least_one_field_populated = true;
				break;
			}
		}

		if ( ! $at_least_one_field_populated ) {
			return '';
		}

		if ( ! isset( $value['line1'] ) ) {
			$value['line1'] = '';
		}

		return $this->format_address_for_display( $value, $atts );
	}

	/**
	 * @since x.x
	 *
	 * @param array $value
	 * @param array $atts
	 *
	 * @return array|string
	 */
	public function format_address_for_display( $value, $atts = array() ) {
		$defaults = $this->empty_value_array();
		$this->fill_values( $value, $defaults );

		$this->fill_default_atts( $atts );

		if ( $atts['force_array'] ) {
			return $value;
		}

		$format = $this->address_format_for_display( $atts, $value );

		foreach ( $defaults as $k => $part ) {
			$format = str_replace( '[' . $k . ']', $value[ $k ], $format );
		}

		return str_replace( $atts['line_sep'] . $atts['line_sep'], $atts['line_sep'], $format );
	}

	/**
	 * @since x.x
	 *
	 * @param array $atts
	 * @param array $value
	 *
	 * @return string
	 */
	private function address_format_for_display( $atts, $value ) {
		if ( ! empty( $atts['show'] ) ) {
			return '[' . $atts['show'] . ']';
		}

		$line_sep       = $atts['line_sep'];
		$address_type   = FrmField::get_option( $this->field, 'address_type' );
		$address_format = '';

		if ( ! empty( $value['line1'] ) ) {
			$address_format = '[line1]' . $line_sep . '[line2]' . $line_sep;
		}

		if ( 'europe' === $address_type ) {
			$address_format .= '[zip] [city]';
		} else {
			$address_format .= '[city], [state] [zip]';
		}

		$address_format .= $line_sep . '[country]';

		/**
		 * Change the format of a displayed address
		 *
		 * @since x.x
		 *
		 * @param string $address_format
		 * @param array  $args
		 */
		/** @var string */
		$result = apply_filters( 'frm_address_format', $address_format, array( 'field' => $this->field ) );
		return is_string( $result ) ? $result : $address_format;
	}

	/**
	 * @since x.x
	 *
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function address_string_to_array( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( ! is_string( $value ) ) {
			$empty_array = array_keys( $this->empty_value_array() );
			return array_combine( $empty_array, array_fill( 0, count( $empty_array ), '' ) );
		}

		$value             = array_map( 'trim', explode( ',', $value ) );
		$empty_array       = array_keys( $this->empty_value_array() );
		$array_length_diff = count( $empty_array ) - count( $value );

		if ( $array_length_diff === 1 ) {
			$value[] = '';
		} elseif ( $array_length_diff > 1 ) {
			$filler_array = array_fill( 0, $array_length_diff, '' );
			$value        = array_merge( $value, $filler_array );
		} elseif ( $array_length_diff < 0 ) {
			$value = array_slice( $value, 0, count( $empty_array ) );
		}

		return array_combine( $empty_array, $value );
	}

	/**
	 * Get empty value array for address.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private function empty_value_array() {
		/**
		 * @since x.x
		 *
		 * @param array $empty_value_array array of empty address data.
		 */
		/** @var array */
		$result = apply_filters(
			'frm_address_empty_value_array',
			array(
				'line1'   => '',
				'line2'   => '',
				'city'    => '',
				'state'   => '',
				'zip'     => '',
				'country' => '',
			)
		);

		return is_array( $result ) ? $result : array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'zip'     => '',
			'country' => '',
		);
	}

	/**
	 * Convert comma-separated address values to an associative array
	 *
	 * @since x.x
	 *
	 * @param array|string $value
	 * @param array        $atts
	 *
	 * @return array
	 */
	protected function prepare_import_value( $value, $atts ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		/** @var string */
		$sep   = apply_filters( 'frm_csv_sep', ', ' );
		$sep   = is_string( $sep ) && $sep !== '' ? $sep : ', ';
		$value = explode( $sep, $value );
		$count = count( $value );

		if ( $count < 4 || $count > 6 ) {
			return $value;
		}

		$new_value = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'zip'     => '',
			'country' => '',
		);

		$new_value['line1'] = $value[0];
		$last_item          = end( $value );

		if ( $count === 6 || ( $count === 5 && is_numeric( $last_item ) ) ) {
			$new_value['line2'] = $value[1];
			$new_value['city']  = $value[2];
			$new_value['state'] = $value[3];
			$new_value['zip']   = $value[4];

			if ( $count === 6 ) {
				$new_value['country'] = $value[5];
			}
		} else {
			$new_value['city']  = $value[1];
			$new_value['state'] = $value[2];
			$new_value['zip']   = $value[3];

			if ( $count === 5 ) {
				$new_value['country'] = $value[4];
			}
		}

		return $new_value;
	}

	/**
	 * Validate field.
	 *
	 * @since x.x
	 *
	 * @param array $args Arguments. Includes `errors`, `value`.
	 *
	 * @return array Errors array.
	 */
	public function validate( $args ) {
		$errors = parent::validate( $args );

		// Add zip validation for US addresses
		$values = $args['value'];

		if ( empty( $values['zip'] ) ) {
			return $errors;
		}

		$address_type = FrmField::get_option( $this->field, 'address_type' );
		$format       = '';

		if ( 'us' === $address_type ) {
			$format = '/^[0-9]{5}(?:-[0-9]{4})?$/';
		}

		$format = apply_filters( 'frm_zip_format', $format, array( 'field' => $this->field ) );

		if ( $format && ! preg_match( $format, $values['zip'] ) ) {
			$errors[ 'field' . $args['id'] . '-zip' ] = __( 'This value is invalid', 'formidable' );
		}

		return $errors;
	}

	/**
	 * @since x.x
	 *
	 * @param mixed $value
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}
}
