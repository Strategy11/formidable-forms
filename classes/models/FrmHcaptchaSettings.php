<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmHcaptchaSettings extends FrmFieldCaptchaSettings {

	public function __construct( $frm_settings ) {
		$this->secret      = $frm_settings->hcaptcha_privkey;
		$this->token_field = 'h-captcha-response';
		$this->endpoint    = 'https://hcaptcha.com/siteverify';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function getName() {
		return 'hCaptcha';
	}
}
