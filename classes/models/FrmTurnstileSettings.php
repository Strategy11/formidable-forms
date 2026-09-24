<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.8.4
 */
class FrmTurnstileSettings extends FrmFieldCaptchaSettings {

	/**
	 * @since 6.8.4
	 *
	 * @return void
	 */
	protected function set_endpoint() {
		$this->endpoint = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Turnstile';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return 'cf-turnstile';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://www.cloudflare.com/products/turnstile/';
	}

	/**
	 * Turnstile global settings are saved as turnstile_pubkey and turnstile_privkey.
	 *
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_settings_prefix() {
		return 'turnstile_';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_site_key_tooltip() {
		return __( 'Turnstile is a free tool to replace CAPTCHAs. Turnstile delivers frustration-free, CAPTCHA-free web experiences to website visitors - with just a simple snippet of free code. Moreover, Turnstile stops abuse and confirms visitors are real without the data privacy concerns or awful user experience of CAPTCHAs.', 'formidable' ); // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
	}

	/**
	 * Turnstile supports an "Auto" theme option so show it.
	 *
	 * @since 6.8.4
	 *
	 * @return bool
	 */
	public function should_show_captcha_theme_auto_option() {
		return true;
	}

	/**
	 * Always add the "frm-" prefix for Turnstile fields.
	 *
	 * @since 6.25.1
	 *
	 * @param bool $allow_multiple
	 *
	 * @return string
	 */
	public function get_class_prefix( $allow_multiple ) {
		return 'frm-';
	}

	/**
	 * Add the error codes that are specific to Turnstile.
	 *
	 * @since x.x
	 *
	 * @return array<string,string>
	 */
	public function get_error_code_messages() {
		return array_merge(
			parent::get_error_code_messages(),
			array(
				'invalid-widget-id'     => __( 'The site key in the global CAPTCHA settings does not match a Turnstile widget.', 'formidable' ),
				'invalid-parsed-secret' => __( 'The secret key in the global CAPTCHA settings is not valid.', 'formidable' ),
				'missing-input'         => __( 'The verification request was missing the secret key or the CAPTCHA response.', 'formidable' ),
			)
		);
	}

	/**
	 * @since x.x
	 *
	 * @return array<int,string>
	 */
	public function get_configuration_error_codes() {
		return array_merge(
			parent::get_configuration_error_codes(),
			array(
				'invalid-widget-id',
				'invalid-parsed-secret',
			)
		);
	}
}
