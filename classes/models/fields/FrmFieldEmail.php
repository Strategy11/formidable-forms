<?php

/**
 * @since 3.0
 */
class FrmFieldEmail extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'email';

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
		);
	}
}
