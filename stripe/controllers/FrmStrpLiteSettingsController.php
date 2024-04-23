<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteSettingsController {

	/**
	 * Add Stripe section to Global Settings.
	 *
	 * @param array $sections
	 * @return array
	 */
	public static function add_settings_section( $sections ) {
		$sections['stripe'] = array(
			'class'    => __CLASS__,
			'function' => 'route',
			'icon'     => 'frm_icon_font frm_stripe_icon',
		);

		add_action(
			'frm_messages_settings_form',
			/**
			 * @param object $frm_settings
			 * @return void
			 */
			function ( $frm_settings ) {
				$stripe_settings = FrmStrpLiteAppHelper::get_settings()->settings;
				require FrmStrpLiteAppHelper::plugin_path() . '/views/settings/messages.php';
			}
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
		$atts                          = array_merge( $atts, self::get_default_settings_atts() );
		$errors                        = $atts['errors'];
		$message                       = $atts['message'];
		$settings                      = FrmStrpLiteAppHelper::get_settings();
		$stripe_connect_is_live        = FrmStrpLiteConnectHelper::stripe_connect_is_setup( 'live' );
		$stripe_connect_is_on_for_test = FrmStrpLiteConnectHelper::stripe_connect_is_setup( 'test' );

		include FrmStrpLiteAppHelper::plugin_path() . '/views/settings/form.php';
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
		$settings = FrmStrpLiteAppHelper::get_settings();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$settings->update( $_POST );
		$settings->store();
	}
}
