<?php
/**
 * ALTCHA on the Captcha tab of Global Settings.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Adds ALTCHA to the captcha types in Global Settings > Captcha/Spam, with its settings panel.
 *
 * @since x.x
 */
class FrmAltchaSettingsHelper {

	/**
	 * Print the ALTCHA tab next to reCAPTCHA, hCaptcha and Turnstile.
	 *
	 * @since x.x
	 *
	 * @param FrmSettings $frm_settings Global settings.
	 *
	 * @return void
	 */
	public static function type_option( $frm_settings ) {
		$is_active = FrmAltchaSettings::TYPE === $frm_settings->active_captcha;
		include FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/altcha-option.php';
	}

	/**
	 * Print the ALTCHA settings panel, shown while ALTCHA is the selected captcha type.
	 *
	 * @since x.x
	 *
	 * @param FrmSettings $frm_settings Global settings.
	 *
	 * @return void
	 */
	public static function settings_panel( $frm_settings ) {
		$is_active = FrmAltchaSettings::TYPE === $frm_settings->active_captcha;
		$settings  = FrmAltchaHelper::get_settings();
		include FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/altcha.php';
	}

	/**
	 * Save the settings with the rest of Global Settings.
	 *
	 * Lite checks the settings nonce and the frm_change_settings capability before this runs.
	 *
	 * @since x.x
	 *
	 * @param array $params Posted Global Settings.
	 *
	 * @return void
	 */
	public static function update( $params ) {
		if ( ! is_array( $params ) || ! isset( $params['frm_altcha'] ) || ! is_array( $params['frm_altcha'] ) || ! current_user_can( 'frm_change_settings' ) ) {
			return;
		}

		// Lite passes the posted settings already unslashed.
		$raw = map_deep( $params['frm_altcha'], 'sanitize_text_field' );
		update_option( FrmAltchaHelper::SETTINGS_OPTION, FrmAltchaHelper::sanitize_settings( $raw ), false );
	}
}
