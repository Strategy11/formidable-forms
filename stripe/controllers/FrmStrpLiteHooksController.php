<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteHooksController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		if ( class_exists( 'FrmStrpHooksController', false ) ) {
			// Exit early, let the Stripe add on handle everything.
			return;
		}

		// Actions.
		add_action( 'frm_entry_form', 'FrmStrpLiteAuth::add_hidden_token_field' );
		add_action( 'frm_enqueue_form_scripts', 'FrmStrpLiteActionsController::maybe_load_scripts' );
		add_action( 'init', 'FrmStrpLiteConnectHelper::check_for_stripe_connect_webhooks' );

		// Filters.
		add_filter( 'frm_saved_errors', 'FrmStrpLiteAppController::maybe_add_payment_error', 10, 2 );
		add_filter( 'frm_filter_final_form', 'FrmStrpLiteAuth::maybe_show_message' );
		add_filter( 'frm_setup_edit_entry_vars', 'FrmStrpLiteAppController::maybe_delete_pay_entry', 20, 2 );

		// This filter flags the Pro credit card field that Stripe is enabled.
		add_filter(
			'frm_pro_show_card_callback',
			function () {
				return 'FrmStrpLiteActionsController::show_card';
			}
		);

		// This filter hides gateway fields from the entries list.
		add_filter(
			'frm_fields_in_entries_list_table',
			function ( $form_cols ) {
				return array_filter(
					$form_cols,
					function ( $form_col ) {
						return 'gateway' !== $form_col->type;
					}
				);
			}
		);

		add_filter( 'frm_payment_gateways', 'FrmStrpLiteAppController::add_gateway' );
		add_filter( 'frm_validate_credit_card_field_entry', 'FrmStrpLiteActionsController::remove_cc_validation', 20, 3 );

		// Stripe link.
		add_filter( 'frm_form_object', 'FrmStrpLiteLinkController::force_ajax_submit_for_stripe_link' );
		add_filter( 'frm_form_classes', 'FrmStrpLiteLinkController::add_form_classes' );
	}

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		if ( class_exists( 'FrmStrpHooksController', false ) ) {
			// Exit early, let the Stripe add on handle everything.
			return;
		}

		// Actions.
		add_action( 'frm_after_uninstall', 'FrmStrpLiteAppController::uninstall' );
		add_action( 'frm_add_settings_section', 'FrmStrpLiteSettingsController::add_settings_section' );
		add_action( 'admin_init', 'FrmStrpLiteAppController::maybe_redirect_to_stripe_settings' );
		add_action( 'frm_update_settings', 'FrmStrpLiteSettingsController::process_form' );

		// Filters.
		add_filter( 'frm_pay_action_defaults', 'FrmStrpLiteActionsController::add_action_defaults' );
		add_filter( 'frm_before_save_payment_action', 'FrmStrpLiteActionsController::before_save_settings', 10, 2 );
		add_filter( 'frm_pay_stripe_receipt', 'FrmStrpLitePaymentsController::get_receipt_link' );
		add_filter( 'frm_sub_stripe_receipt', 'FrmStrpLitePaymentsController::get_receipt_link' );

		if ( defined( 'DOING_AJAX' ) ) {
			self::load_ajax_hooks();
		}
	}

	/**
	 * @return void
	 */
	private static function load_ajax_hooks() {
		$frm_strp_events_controller = new FrmStrpLiteEventsController();
		add_action( 'wp_ajax_nopriv_frm_strp_process_events', array( &$frm_strp_events_controller, 'process_connect_events' ) );
		add_action( 'wp_ajax_frm_strp_process_events', array( &$frm_strp_events_controller, 'process_connect_events' ) );
		add_action( 'wp_ajax_nopriv_frm_strp_amount', 'FrmStrpLiteAuth::update_intent_ajax' );
		add_action( 'wp_ajax_frm_strp_amount', 'FrmStrpLiteAuth::update_intent_ajax' );

		// Stripe link.
		add_action( 'wp_ajax_nopriv_frmstrplinkreturn', 'FrmStrpLiteLinkController::handle_return_url' );
		add_action( 'wp_ajax_frmstrplinkreturn', 'FrmStrpLiteLinkController::handle_return_url' );

		// Stripe Lite.
		add_action( 'wp_ajax_nopriv_frm_strp_lite_verify', 'FrmStrpLiteConnectHelper::verify' );
	}
}
