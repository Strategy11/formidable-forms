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
		return __( 'Turnstile is a free tool to replace CAPTCHAs. Turnstile delivers frustration-free, CAPTCHA-free web experiences to website visitors - with just a simple snippet of free code. Moreover, Turnstile stops abuse and confirms visitors are real without the data privacy concerns or awful user experience of CAPTCHAs.', 'formidable' );
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
}
