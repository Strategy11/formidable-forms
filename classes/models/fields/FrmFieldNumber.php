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

	public function validate( $args ) {
		$errors = array();

		//validate the number format
		if ( ! is_numeric( $args['value'] ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		}

		// validate number settings
		if ( $args['value'] != '' ) {
			$frm_settings = FrmAppHelper::get_settings();
			// only check if options are available in settings
			$minnum = FrmField::get_option( $this->field, 'minnum' );
			$maxnum = FrmField::get_option( $this->field, 'maxnum' );
			if ( $frm_settings->use_html && $maxnum != '' && $minnum != '' ) {
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

	public function set_value_before_save( $value ) {
		if ( ! is_numeric( $value ) ) {
			$value = (float) $value;
		}
		return $value;
	}
}
