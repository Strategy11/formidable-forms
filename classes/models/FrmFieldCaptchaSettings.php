<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.0
 */
class FrmFieldCaptchaSettings {

	/**
	 * @var string
	 */
	public $secret;

	/**
	 * @var string
	 */
	public $token_field;

	/**
	 * @var string
	 */
	public $endpoint;

	public function __construct( $frm_settings ) {
		if ( 'recaptcha' === $frm_settings->active_captcha ) {
			$this->secret      = $frm_settings->privkey;
			$this->token_field = 'g-recaptcha-response';
			$this->endpoint    = 'https://www.google.com/recaptcha/api/siteverify';
		} elseif ( 'hcaptha' === $frm_settings->active_captcha ) {
			$this->secret      = $frm_settings->hcaptcha_privkey;
			$this->token_field = 'h-captcha-response';
			$this->endpoint    = 'https://hcaptcha.com/siteverify';
		} else {
			$this->secret      = $frm_settings->turnstile_privkey;
			$this->token_field = 'frm-turnstile-response';
			$this->endpoint    = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
		}
	}
}
