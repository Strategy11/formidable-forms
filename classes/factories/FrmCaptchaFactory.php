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
	 * @return FrmFieldCaptchaSettings
	 */
	public static function get_settings_object() {
		$frm_settings = FrmAppHelper::get_settings();
		$class        = self::get_settings_class( $frm_settings->active_captcha );
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
