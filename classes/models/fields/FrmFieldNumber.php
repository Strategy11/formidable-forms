<?php

/**
 * @since 3.0
 */
class FrmFieldNumber extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'number';
	protected $display_type = 'text';

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

	protected function extra_field_opts() {
		return array(
			'minnum' => 0,
			'maxnum' => 9999999,
			'step'   => 'any',
		);
	}

	/**
	 * @since 3.01.03
	 */
	protected function add_extra_html_atts( $args, &$input_html ) {
		$this->add_min_max( $args, $input_html );
	}

	public function validate( $args ) {
		$errors = array();

		$this->remove_commas_from_number( $args );

		//validate the number format
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
		}

		return $errors;
	}

	/**
	 * IE fallback for number fields
	 * Remove the comma when HTML5 isn't supported
	 *
	 * @since 3.0
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
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}
}
