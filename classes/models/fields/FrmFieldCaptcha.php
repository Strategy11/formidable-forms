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

	protected function new_field_settings() {
		$frm_settings = FrmAppHelper::get_settings();
		return array(
			'invalid' => $frm_settings->re_msg,
		);
	}

	protected function extra_field_opts() {
		return array(
			'label'         => 'none',
			'captcha_size'  => 'normal',
			'captcha_theme' => 'light',
		);
	}
}
