<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.8.4
 */
class FrmRecaptchaSettings extends FrmFieldCaptchaSettings {

	/**
	 * @since 6.8.4
	 *
	 * @return void
	 */
	protected function set_endpoint() {
		$this->endpoint = 'https://www.google.com/recaptcha/api/siteverify';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function getName() {
		return 'reCAPTCHA';
	}


	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return 'g-recaptcha';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://www.google.com/recaptcha/';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_site_key_tooltip() {
		return __( 'reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog. reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot.', 'formidable' );
	}

	/**
	 * Add additional element attributes for reCAPTCHA.
	 *
	 * @since 6.8.4
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
	 * @since 6.8.4
	 *
	 * @param array $field
	 * @return string
	 */
	protected function get_captcha_size( $field ) {
		if ( $this->captcha_is_invisible() ) {
			return 'invisible';
		}
		return parent::get_captcha_size( $field );
	}

	/**
	 * Show the Captcha field size setting if the captcha is visible.
	 *
	 * @since 6.8.4
	 *
	 * @return bool
	 */
	public function should_show_captcha_size() {
		return ! $this->captcha_is_invisible();
	}

	/**
	 * @since 6.8.4
	 *
	 * @return bool
	 */
	public function should_show_captcha_theme() {
		return ! $this->captcha_is_invisible();
	}

	/**
	 * @since 6.8.4
	 *
	 * @return bool
	 */
	private function captcha_is_invisible() {
		return in_array( $this->frm_settings->re_type, array( 'invisible', 'v3' ), true );
	}
}
