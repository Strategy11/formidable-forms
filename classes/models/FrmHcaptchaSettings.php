<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.8.4
 */
class FrmHcaptchaSettings extends FrmFieldCaptchaSettings {

	/**
	 * @since 6.8.4
	 *
	 * @return void
	 */
	protected function set_endpoint() {
		$this->endpoint = 'https://hcaptcha.com/siteverify';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_name() {
		return 'hCaptcha';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return 'h-captcha';
	}

	/**
	 * @since 6.8.4
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
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_settings_prefix() {
		return 'hcaptcha_';
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	public function get_site_key_tooltip() {
		return __( 'hCaptcha is an anti-bot solution that protects user privacy and rewards websites. It is a privacy-focused drop-in replacement for reCAPTCHA.', 'formidable' );
	}

	/**
	 * Add the error codes that are specific to hCaptcha.
	 *
	 * @since x.x
	 *
	 * @return array<string,string>
	 */
	public function get_error_code_messages() {
		return array_merge(
			parent::get_error_code_messages(),
			array(
				'invalid-or-already-seen-response' => __( 'The CAPTCHA response expired or was already used.', 'formidable' ),
				'not-using-dummy-passcode'         => __( 'A test site key is paired with a live secret key. Use a matching pair in the global CAPTCHA settings.', 'formidable' ),
				'sitekey-secret-mismatch'          => __( 'The site key and secret key in the global CAPTCHA settings belong to different hCaptcha accounts.', 'formidable' ),
				'invalid-sitekey'                  => __( 'The site key in the global CAPTCHA settings is not valid.', 'formidable' ),
				'invalid-remoteip'                 => __( 'The visitor IP address sent for verification was not valid.', 'formidable' ),
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
				'not-using-dummy-passcode',
				'sitekey-secret-mismatch',
				'invalid-sitekey',
			)
		);
	}
}
