<?php

/**
 * @since 3.0
 */
class FrmFieldPhone extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'phone';

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
}
