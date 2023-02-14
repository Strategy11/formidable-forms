<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.0
 */
class FrmFieldCaptchaSettings {

	public $secret;

	public $token_field;

	public $end_point;

	public function __construct( $frm_settings ) {
		if ( $frm_settings->active_captcha === 'recaptcha' ) {
			$this->secret      = $frm_settings->privkey;
			$this->token_field = 'g-recaptcha-response';
			$this->endpoint    = 'https://www.google.com/recaptcha/api/siteverify';
		} else {
			$this->secret      = $frm_settings->hcaptcha_privkey;
			$this->token_field = 'h-captcha-response';
			$this->endpoint    = 'https://hcaptcha.com/siteverify';
		}
	}
}
