<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmCaptchaFactory {

	/**
	 * @since x.x
	 *
	 * @param string $captcha_type Either 'active', 'recaptcha', 'hcaptcha' or 'turnstile'. If active, the global setting will be used.
	 * @return FrmFieldCaptchaSettings
	 */
	public static function get_settings_object( $captcha_type = 'active' ) {
		if ( 'active' === $captcha_type ) {
			$frm_settings = FrmAppHelper::get_settings();
			$class        = self::get_settings_class( $frm_settings->active_captcha );
		} else {
			$class = self::get_settings_class( $captcha_type );
		}

		$settings     = new $class( $frm_settings );
		return $settings;
	}

	/**
	 * @since x.x
	 *
	 * @param string $active_captcha
	 * @return string
	 */
	private static function get_settings_class( $active_captcha ) {
		$settings_classes = array(
			'recaptcha' => 'FrmRecaptchaSettings',
			'hcaptcha'  => 'FrmHcaptchaSettings',
			'turnstile' => 'FrmTurnstileSettings',
		);

		if ( ! isset( $settings_classes[ $active_captcha ] ) ) {
			$active_captcha = 'recaptcha';
		}

		return $settings_classes[ $active_captcha ];
	}
}
