<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.0
 */
class FrmFieldCaptchaSettings {

	/**
	 * The global settings object.
	 *
	 * @param FrmSettings $settings
	 */
	public $frm_settings;

	/**
	 * The private key used for validating the token.
	 *
	 * @since 6.0
	 *
	 * @var string
	 */
	public $secret;

	/**
	 * The key value to check in $_POST data with the token for validation.
	 *
	 * @since 6.0
	 *
	 * @var string
	 */
	public $token_field;

	/**
	 * The URL that is called when validating the token.
	 *
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
		$this->set_secret();
		$this->set_token_field();
		$this->set_endpoint();
	}

	/**
	 * Set the private key to $this->secret.
	 *
	 * @return void
	 */
	protected function set_secret() {
		$key = $this->get_key_for_privkey();
		$this->secret = ! empty( $this->frm_settings->$key ) ? $this->frm_settings->$key : '';
	}

	/**
	 * Set the string to use for $this->token_field.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	protected function set_token_field() {
		$this->token_field = '';
	}

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	protected function set_endpoint() {
		$this->endpoint = '';
	}

	/**
	 * Get the name of the CAPTCHA service.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_name() {
		return '';
	}

	/**
	 * Get the class name used for the CAPTCHA element on the front end.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return '';
	}

	/**
	 * Get the URL of the page to link to in global settings. This page should have
	 * instructions on how to get site and secret keys.
	 *
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
	 * Get the string to use for the tooltip on the Global settings page when hovering over the Site Key label.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_site_key_tooltip() {
		return '';
	}

	/**
	 * Get the key used in FrmSettings for the site secret used for validating a Captcha.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	protected function get_key_for_privkey() {
		return $this->get_settings_prefix() . 'privkey';
	}

	/**
	 * Get the key used in FrmSettings for the site key used to initialize a Captcha on the front end.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	protected function get_key_for_pubkey() {
		return $this->get_settings_prefix() . 'pubkey';
	}

	/**
	 * Get the site key to use for a Captcha on the front end.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_pubkey() {
		$key = $this->get_key_for_pubkey();
		return isset( $this->frm_settings->$key ) ? $this->frm_settings->$key : '';
	}

	/**
	 * Check if the public key is not empty.
	 *
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
	 * @param array $attributes
	 * @param array $field
	 * @return array
	 */
	public function add_front_end_element_attributes( $attributes, $field ) {
		$attributes['data-size'] = $this->get_captcha_size( $field );

		if ( ! empty( $field['captcha_theme'] ) ) {
			$attributes['data-theme'] = $field['captcha_theme'];
		}

		return $attributes;
	}

	/**
	 * @since x.x
	 *
	 * @param array $field
	 * @return string Either 'normal' or 'compact'.
	 */
	protected function get_captcha_size( $field ) {
		return $field['captcha_size'] === 'default' ? 'normal' : $field['captcha_size'];
	}

	/**
	 * Deternine if the Captcha Size setting should be shown in field settings.
	 * This has options for "Normal" and "Compact".
	 * This is supported by all CAPTCHA types except for invisible reCAPTCHAs.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public function should_show_captcha_size() {
		return true;
	}

	/**
	 * Determine if we should show a theme dropdown for our Captcha field.
	 * This is applicable for all Captcha types except for invisible reCAPTCHA.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public function should_show_captcha_theme() {
		return true;
	}

	/**
	 * Determine if the "auto" option should be shown for a specific captcha type.
	 * This is only applicable for Turnstile.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public function should_show_captcha_theme_auto_option() {
		return false;
	}
}
