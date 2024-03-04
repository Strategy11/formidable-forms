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
	public function get_name() {
		return 'hCaptcha';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return 'h-captcha';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://www.hcaptcha.com/signup-interstitial';
	}

	/**
	 * Get the prefix for the global setting.
	 * reCAPTCHA fields just use pubkey/privkey.
	 * But other captcha integrations use a prefix like hcaptcha_public/turnstile_privkey.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_settings_prefix() {
		return 'hcaptcha_';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_site_key_tooltip() {
		return __( 'hCaptcha is an anti-bot solution that protects user privacy and rewards websites. It is a privacy-focused drop-in replacement for reCAPTCHA.', 'formidable' );
	}
}
