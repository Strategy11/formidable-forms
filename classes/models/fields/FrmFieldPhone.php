<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldPhone extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'phone';
	protected $display_type = 'text';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	protected function field_settings_for_type() {
		return array(
			'size'           => true,
			'clear_on_focus' => true,
			'invalid'        => true,
			'format'         => true,
		);
	}

	protected function html5_input_type() {
		$frm_settings = FrmAppHelper::get_settings();

		return $frm_settings->use_html ? 'tel' : 'text';
	}

	/**
	 * @since 4.0.04
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}
}
