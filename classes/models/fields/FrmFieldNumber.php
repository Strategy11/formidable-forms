<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldNumber extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'number';

	/**
	 * @var bool
	 */
	protected $array_allowed = false;

	/**
	 * @return bool[]
	 */
	protected function field_settings_for_type() {
		$settings = array(
			'size'           => true,
			'clear_on_focus' => true,
			'invalid'        => true,
			'range'          => true,
		);

		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->use_html ) {
			$settings['max'] = false;
		}

		return $settings;
	}

	/**
	 * @return array
	 */
	protected function extra_field_opts() {
		return array(
			'minnum' => 0,
			'maxnum' => 9999999,
			'step'   => 'any',
		);
	}

	/**
	 * @since 3.01.03
	 *
	 * @return void
	 */
	protected function add_extra_html_atts( $args, &$input_html ) {
		$this->add_min_max( $args, $input_html );
	}

	public function validate( $args ) {
		$errors = array();

		$this->remove_commas_from_number( $args );

		// Validate the number format.
		if ( ! is_numeric( $args['value'] ) && '' !== $args['value'] ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		}

		// validate number settings
		if ( $args['value'] != '' ) {
			$frm_settings = FrmAppHelper::get_settings();
			// only check if options are available in settings
			$minnum = FrmField::get_option( $this->field, 'minnum' );
			$maxnum = FrmField::get_option( $this->field, 'maxnum' );
			if ( $frm_settings->use_html && $maxnum !== '' && $minnum !== '' ) {
				$value = (float) $args['value'];
				if ( $value < $minnum ) {
					$errors[ 'field' . $args['id'] ] = __( 'Please select a higher number', 'formidable' );
				} elseif ( $value > $maxnum ) {
					$errors[ 'field' . $args['id'] ] = __( 'Please select a lower number', 'formidable' );
				}
			}

			$this->validate_step( $errors, $args );
		}

		return $errors;
	}

	/**
	 * Validates the step setting.
	 *
	 * @since 5.2.06
	 *
	 * @param array $errors Errors array.
	 * @param array $args   Validation args.
	 *
	 * @return void
	 */
	private function validate_step( &$errors, $args ) {
		if ( isset( $errors[ 'field' . $args['id'] ] ) ) {
			// Don't need to check if value is invalid before.
			return;
		}

		$step = FrmField::get_option( $this->field, 'step' );
		if ( ! $step || ! is_numeric( $step ) ) {
			return;
		}

		$result = $this->check_value_is_valid_with_step( $args['value'], $step );
		if ( ! $result ) {
			return;
		}

		$errors[ 'field' . $args['id'] ] = sprintf(
			// Translators: %1$s: the first nearest value; %2$s: the second nearest value.
			__( 'Please enter a valid value. Two nearest valid values are %1$s and %2$s', 'formidable' ),
			floatval( $result[0] ),
			floatval( $result[1] )
		);
	}

	/**
	 * Checks if value is valid with the given step.
	 *
	 * @since 5.2.07
	 *
	 * @param numeric $value The value.
	 * @param numeric $step  The step.
	 * @return array|int     Return `0` if valid. Otherwise, return an array contains two nearest values.
	 */
	private function check_value_is_valid_with_step( $value, $step ) {
		// Count the number of decimals.
		$decimals = max( FrmAppHelper::count_decimals( $value ), FrmAppHelper::count_decimals( $step ) );

		// Convert value and step to int to prevent precision problem.
		$pow   = pow( 10, $decimals );
		$value = intval( $pow * $value );
		$step  = intval( $pow * $step );
		$div   = $value / $step;
		if ( is_int( $div ) ) {
			return 0;
		}

		$div = floor( $div );
		return array( $div * $step / $pow, ( $div + 1 ) * $step / $pow );
	}

	/**
	 * IE fallback for number fields
	 * Remove the comma when HTML5 isn't supported
	 *
	 * @since 3.0
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	private function remove_commas_from_number( &$args ) {
		if ( strpos( $args['value'], ',' ) ) {
			$args['value'] = str_replace( ',', '', $args['value'] );
			FrmEntriesHelper::set_posted_value( $this->field, $args['value'], $args );
		}
	}

	/**
	 * Force the value to be numeric before it's saved in the DB
	 */
	public function set_value_before_save( $value ) {
		if ( ! is_numeric( $value ) ) {
			$value = (float) $value;
		}

		return $value;
	}

	/**
	 * @since 4.0.04
	 *
	 * @return void
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}
}
