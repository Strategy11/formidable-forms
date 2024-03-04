<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmTurnstileSettings extends FrmFieldCaptchaSettings {

	/**
	 * @param FrmSettings $frm_settings
	 */
	public function __construct( $frm_settings ) {
		$this->frm_settings = $frm_settings;
		$this->secret       = $frm_settings->turnstile_privkey;
		$this->token_field  = 'frm-turnstile-response';
		$this->endpoint     = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Turnstile';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return 'cf-turnstile';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://www.cloudflare.com/products/turnstile/';
	}

	/**
	 * Turnstile global settings are saved as turnstile_pubkey and turnstile_privkey.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_settings_prefix() {
		return 'turnstile_';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_site_key_tooltip() {
		return __( 'Turnstile is a free tool to replace CAPTCHAs. Turnstile delivers frustration-free, CAPTCHA-free web experiences to website visitors - with just a simple snippet of free code. Moreover, Turnstile stops abuse and confirms visitors are real without the data privacy concerns or awful user experience of CAPTCHAs.', 'formidable' );
	}

	/**
	 * Add additional element attributes for Turnstile.
	 *
	 * @since x.x
	 *
	 * 
	 * @param array $attributes
	 * @param array $field
	 * @return array
	 */
	public function add_front_end_element_attributes( $attributes, $field ) {
		$attributes['data-callback'] = 'frmAfterTurnstile';
		return $attributes;
	}
}
