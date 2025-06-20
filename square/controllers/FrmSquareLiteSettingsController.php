<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteSettingsController {

	/**
	 * Add Square section to Global Settings.
	 *
	 * @since 6.22
	 *
	 * @param array $sections
	 * @return array
	 */
	public static function add_settings_section( $sections ) {
		$sections['square'] = array(
			'class'    => __CLASS__,
			'function' => 'route',
			'icon'     => 'frm_icon_font frm_square_icon',
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
	 * Print the Stripe section for Global settings.
	 *
	 * @param array $atts
	 * @return void
	 */
	public static function global_settings_form( $atts = array() ) {
		include FrmSquareLiteAppHelper::plugin_path() . '/views/settings/form.php';
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
	 * Handle processing changes to global Stripe Settings.
	 *
	 * @return void
	 */
	public static function process_form() {
		$settings = FrmSquareLiteAppHelper::get_settings();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$settings->update( $_POST );
		$settings->store();
	}
}
