<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteSettingsController {

	/**
	 * Add PayPal section to Global Settings.
	 *
	 * @since 6.31
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

	/**
	 * Sync test_mode after PayPal add-on saves its settings.
	 * This runs with high priority to ensure it runs after the add-on's process_form.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public static function sync_test_mode_after_addon( $params ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['frm_paypal_test_mode'] ) ) {
			return;
		}

		$test_mode = absint( $_POST['frm_paypal_test_mode'] );
		$options = get_option( 'frm_paypal_options' );

		if ( ! is_object( $options ) ) {
			$options = new stdClass();
		}

		// Preserve test_mode which the add-on's update() method doesn't handle
		$options->test_mode = $test_mode;
		update_option( 'frm_paypal_options', $options );
	}

	/**
	 * Preserve test_mode when the PayPal add-on updates frm_paypal_options.
	 * This hooks into update_option to ensure test_mode isn't lost.
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $new_value The new option value.
	 *
	 * @return void
	 */
	public static function preserve_test_mode_on_update( $old_value, $new_value ) {
		if ( ! is_object( $old_value ) || ! is_object( $new_value ) ) {
			return;
		}

		// If old value had test_mode but new value doesn't, preserve it
		if ( isset( $old_value->test_mode ) && ! isset( $new_value->test_mode ) ) {
			$new_value->test_mode = $old_value->test_mode;
			// Update the option with the preserved test_mode
			update_option( 'frm_paypal_options', $new_value );
		}
	}
}
