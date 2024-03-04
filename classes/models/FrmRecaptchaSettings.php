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

	/**
	 * Add additional element attributes for reCAPTCHA.
	 *
	 * @since x.x
	 *
	 * 
	 * @param array $attributes
	 * @param array $field
	 * @return array
	 */
	public function add_front_end_element_attributes( $attributes, $field ) {
		$attributes   = parent::add_front_end_element_attributes( $attributes, $field );
		$captcha_size = $attributes['data-size'];

		if ( $captcha_size === 'invisible' && ! $this->frm_settings->re_multi ) {
			$attributes['data-callback'] = 'frmAfterRecaptcha';
		}

		return $attributes;
	}

	/**
	 * @since x.x
	 *
	 * @param array $field
	 * @return string
	 */
	protected function get_captcha_size( $field ) {
		if ( in_array( $this->frm_settings->re_type, array( 'invisible', 'v3' ), true ) ) {
			return 'invisible';
		}
		return parent::get_captcha_size( $field );
	}

	/**
	 * Show the Captcha field size setting if the captcha is visible.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public function should_show_captcha_size() {
		return ! $this->captcha_is_invisible();
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	public function should_show_captcha_theme() {
		return ! $this->captcha_is_invisible();
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	private function captcha_is_invisible() {
		return in_array( $this->frm_settings->re_type, array( 'invisible', 'v3' ), true );
	}
}
