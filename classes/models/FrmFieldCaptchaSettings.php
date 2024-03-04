<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.0
 */
class FrmFieldCaptchaSettings {

	/**
	 * @param FrmSettings $settings
	 */
	public $frm_settings;

	/**
	 * @since 6.0
	 *
	 * @var string
	 */
	public $secret;

	/**
	 * @since 6.0
	 *
	 * @var string
	 */
	public $token_field;

	/**
	 * @since 6.0
	 *
	 * @var string
	 */
	public $endpoint;

	/**
	 * @param FrmSettings $frm_settings
	 */
	public function __construct( $frm_settings ) {
		$this->frm_settings = $frm_settings;
		$this->secret       = '';
		$this->token_field  = '';
		$this->endpoint     = '';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_name() {
		return '';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return '';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_documentation_url() {
		return '';
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
		return '';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_site_key_tooltip() {
		return '';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_pubkey() {
		$key = $this->get_settings_prefix() . 'pubkey';
		return isset( $this->frm_settings->$key ) ? $this->frm_settings->$key : '';
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	public function has_pubkey() {
		return ! empty( $this->get_pubkey() );
	}

	/**
	 * This can be used to add additional attributes to a front end Captcha element.
	 *
	 * @since x.x
	 *
	 * @param array  $attributes
	 * @param string $captcha_size
	 * @return array
	 */
	public function add_front_end_element_attributes( $attributes, $captcha_size ) {
		return $attributes;
	}
}
