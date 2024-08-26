<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.8.4
 */
class FrmCaptchaFactory {

	/**
	 * Get the proper FrmFieldCaptchaSettings child class based on a type setting.
	 *
	 * @since 6.8.4
	 *
	 * @param string $captcha_type Either 'active', 'recaptcha', 'hcaptcha' or 'turnstile'. If active, the global setting will be used.
	 * @return FrmFieldCaptchaSettings
	 */
	public static function get_settings_object( $captcha_type = 'active' ) {
		$frm_settings = FrmAppHelper::get_settings();

		if ( 'active' === $captcha_type ) {
			$class = self::get_settings_class( $frm_settings->active_captcha );
		} else {
			$class = self::get_settings_class( $captcha_type );
		}

		return new $class( $frm_settings );
	}

	/**
	 * @since 6.8.4
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
