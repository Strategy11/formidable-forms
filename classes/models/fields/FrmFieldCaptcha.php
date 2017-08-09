<?php

/**
 * @since 3.0
 */
class FrmFieldCaptcha extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'captcha';

	protected function field_settings_for_type() {
		return array(
			'required'      => false,
			'invalid'       => true,
			'default_blank' => false,
			'captcha_size'  => true,
		);
	}
}
