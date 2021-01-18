<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldText extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'text';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	protected function field_settings_for_type() {
		return array(
			'size'           => true,
			'clear_on_focus' => true,
			'format'         => true,
			'invalid'        => true,
		);
	}
}
