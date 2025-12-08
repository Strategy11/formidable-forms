<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteHooksController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'frm_enqueue_form_scripts', 'FrmPayPalLiteActionsController::maybe_load_scripts' );
		add_filter( 'frm_validate_credit_card_field_entry', 'FrmPayPalLiteActionsController::remove_cc_validation', 20, 3 );

		add_filter( 'frm_payment_gateways', 'FrmPayPalLiteAppController::add_gateway' );

		add_action( 'init', 'FrmPayPalLiteConnectHelper::check_for_redirects' );

		// Use 20 so this happens after the Stripe add-on.
		add_filter( 'frm_pro_show_card_callback', 'FrmPayPalLiteActionsController::maybe_show_card', 20, 2 );
	}

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_filter( 'frm_add_settings_section', 'FrmPayPalLiteSettingsController::add_settings_section' );
		add_action( 'frm_update_settings', 'FrmPayPalLiteSettingsController::process_form' );

		if ( defined( 'DOING_AJAX' ) ) {
			self::load_ajax_hooks();
		}
	}

	/**
	 * @return void
	 */
	private static function load_ajax_hooks() {
		add_action( 'wp_ajax_frm_paypal_oauth', 'FrmPayPalLiteAppController::handle_oauth' );
		add_action( 'wp_ajax_frm_paypal_disconnect', 'FrmPayPalLiteAppController::handle_disconnect' );

		$frm_paypal_events_controller = new FrmPayPalLiteEventsController();
		add_action( 'wp_ajax_nopriv_frm_paypal_process_events', array( &$frm_paypal_events_controller, 'process_events' ) );
		add_action( 'wp_ajax_frm_paypal_process_events', array( &$frm_paypal_events_controller, 'process_events' ) );

		// Verify PayPal Lite sites.
		add_action( 'wp_ajax_nopriv_frm_paypal_lite_verify', 'FrmPayPalLiteConnectHelper::verify' );

		add_action( 'wp_ajax_frm_paypal_create_order', 'FrmPayPalLiteAppController::create_order' );
		add_action( 'wp_ajax_nopriv_frm_paypal_create_order', 'FrmPayPalLiteAppController::create_order' );
	}
}
