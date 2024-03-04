<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmRecaptchaSettings extends FrmFieldCaptchaSettings {

	public function __construct( $frm_settings ) {
		$this->name        = 'reCAPTCHA';
		$this->secret      = $frm_settings->privkey;
		$this->token_field = 'g-recaptcha-response';
		$this->endpoint    = 'https://www.google.com/recaptcha/api/siteverify';
	}
}
