<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteSettingsController {

	/**
	 * Add PayPal section to Global Settings.
	 *
	 * @since x.x
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public static function add_settings_section( $sections ) {
		$sections['paypal'] = array(
			'class'    => self::class,
			'function' => 'route',
			'icon'     => 'frm_icon_font frm_paypal_icon',
		);

		return $sections;
	}

	/**
	 * Handle global settings routing.
	 *
	 * @return void
	 */
	public static function route() {
		self::global_settings_form();
	}

	/**
	 * Print the PayPal section for Global settings.
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public static function global_settings_form( $atts = array() ) {
		include FrmPayPalLiteAppHelper::plugin_path() . '/views/settings/form.php';
	}

	/**
	 * @return array
	 */
	private static function get_default_settings_atts() {
		return array(
			'errors'  => array(),
			'message' => '',
		);
	}

	/**
	 * Handle processing changes to global PayPal Settings.
	 *
	 * @return void
	 */
	public static function process_form() {
		$settings = FrmPayPalLiteAppHelper::get_settings();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$settings->update( $_POST );
		$settings->store();
	}
}
