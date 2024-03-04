<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmRecaptchaSettings extends FrmFieldCaptchaSettings {

	public function __construct( $frm_settings ) {
		$this->secret      = $frm_settings->privkey;
		$this->token_field = 'g-recaptcha-response';
		$this->endpoint    = 'https://www.google.com/recaptcha/api/siteverify';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function getName() {
		return 'reCAPTCHA';
	}


	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return 'g-recaptcha';
	}
}
