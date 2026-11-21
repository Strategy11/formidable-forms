<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmInfopeakSettings extends FrmFieldCaptchaSettings {

	/**
	 * InfoPeak Captcha has a reCAPTCHA compatible siteverify endpoint.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	protected function set_endpoint() {
		$this->endpoint = 'https://captcha.infopeak.io/siteverify';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_name() {
		return 'InfoPeak Captcha';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return 'infopeak-captcha';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://accounts.infopeak.io/signup?plan=captcha';
	}

	/**
	 * InfoPeak Captcha global settings are saved as infopeak_pubkey and infopeak_privkey.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_settings_prefix() {
		return 'infopeak_';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_site_key_tooltip() {
		return __(
			'InfoPeak Captcha is an EU-hosted captcha without cookies or image puzzles. The visitor\'s browser solves a small proof-of-work challenge while the form is filled in.',
			'formidable'
		);
	}

	/**
	 * The widget has a single size and theme, so the data-size and data-theme attributes are not used.
	 *
	 * @since x.x
	 *
	 * @param array $attributes
	 * @param array $field
	 *
	 * @return array
	 */
	public function add_front_end_element_attributes( $attributes, $field ) {
		return $attributes;
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	public function should_show_captcha_size() {
		return false;
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	public function should_show_captcha_theme() {
		return false;
	}

	/**
	 * Add the error codes that are specific to InfoPeak Captcha.
	 *
	 * @since x.x
	 *
	 * @return array<string,string>
	 */
	public function get_error_code_messages() {
		return array_merge(
			parent::get_error_code_messages(),
			array(
				'quota-exceeded' => __( 'The monthly verification quota for this InfoPeak Captcha site key is used up.', 'formidable' ),
			)
		);
	}

	/**
	 * A used up quota cannot be fixed by the person filling out the form.
	 *
	 * @since x.x
	 *
	 * @return array<int,string>
	 */
	public function get_configuration_error_codes() {
		return array_merge(
			parent::get_configuration_error_codes(),
			array(
				'quota-exceeded',
			)
		);
	}
}
