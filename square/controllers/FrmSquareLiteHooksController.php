<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteHooksController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'frm_enqueue_form_scripts', 'FrmSquareLiteActionsController::maybe_load_scripts' );
		add_filter( 'frm_validate_credit_card_field_entry', 'FrmSquareLiteActionsController::remove_cc_validation', 20, 3 );

		add_filter( 'frm_payment_gateways', 'FrmSquareLiteAppController::add_gateway' );

		add_action( 'init', 'FrmSquareLiteConnectHelper::check_for_redirects' );

		// Use 20 so this happens after the Stripe add-on.
		add_filter( 'frm_pro_show_card_callback', 'FrmSquareLiteActionsController::maybe_show_card', 20, 2 );
	}

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_filter( 'frm_add_settings_section', 'FrmSquareLiteSettingsController::add_settings_section' );
		add_action( 'frm_update_settings', 'FrmSquareLiteSettingsController::process_form' );

		if ( defined( 'DOING_AJAX' ) ) {
			self::load_ajax_hooks();
		}
	}

	/**
	 * @return void
	 */
	private static function load_ajax_hooks() {
		add_action( 'wp_ajax_frm_square_oauth', 'FrmSquareLiteAppController::handle_oauth' );
		add_action( 'wp_ajax_frm_square_disconnect', 'FrmSquareLiteAppController::handle_disconnect' );

		add_action( 'wp_ajax_frm_verify_buyer', 'FrmSquareLiteAppController::verify_buyer' );
		add_action( 'wp_ajax_nopriv_frm_verify_buyer', 'FrmSquareLiteAppController::verify_buyer' );

		$frm_square_events_controller = new FrmSquareLiteEventsController();
		add_action( 'wp_ajax_nopriv_frm_square_process_events', array( &$frm_square_events_controller, 'process_events' ) );
		add_action( 'wp_ajax_frm_square_process_events', array( &$frm_square_events_controller, 'process_events' ) );

		// Verify Square Lite sites.
		add_action( 'wp_ajax_nopriv_frm_square_lite_verify', 'FrmSquareLiteConnectHelper::verify' );
	}
}
