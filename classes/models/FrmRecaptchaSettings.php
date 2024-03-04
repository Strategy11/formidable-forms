<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmRecaptchaSettings extends FrmFieldCaptchaSettings {

	/**
	 * @param FrmSettings $frm_settings
	 */
	public function __construct( $frm_settings ) {
		$this->frm_settings = $frm_settings;
		$this->secret       = $frm_settings->privkey;
		$this->token_field  = 'g-recaptcha-response';
		$this->endpoint     = 'https://www.google.com/recaptcha/api/siteverify';
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

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://www.google.com/recaptcha/';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_site_key_tooltip() {
		return __( 'reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog. reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot.', 'formidable' );
	}
}
