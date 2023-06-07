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

		// TODO Move this somewhere less temporary.
		$callback = function( $values, $field ) {
			if ( $field->type !== 'credit_card' ) {
				return $values;
			}

			if ( ! FrmAppHelper::is_admin_page( 'formidable' ) ) {
				do_action( 'frm_enqueue_stripe_scripts', array( 'form_id' => $field->form_id ) );
			}

			return $values;
		};

		add_filter( 'frm_setup_new_fields_vars', $callback, 10, 2 );
		add_filter( 'frm_setup_edit_fields_vars', $callback, 10, 2 );

		// register_activation_hook( dirname( dirname( __FILE__ ) ) . '/formidable-stripe.php', 'FrmStrpLiteAppController::install' );

		add_filter( 'frm_payment_gateways', 'FrmStrpLiteAppController::add_gateway' );
		add_filter( 'frm_filter_final_form', 'FrmStrpLiteAuth::maybe_show_message' );
		add_action( 'frm_entry_form', 'FrmStrpLiteAuth::add_hidden_token_field' );
		add_filter( 'frm_pro_show_card_callback', 'FrmStrpLiteActionsController::show_card_callback' );
		add_filter( 'frm_validate_credit_card_field_entry', 'FrmStrpLiteActionsController::remove_cc_validation', 20, 3 );
		add_action( 'frm_enqueue_form_scripts', 'FrmStrpLiteActionsController::maybe_load_scripts' );
		add_action( 'frm_enqueue_stripe_scripts', 'FrmStrpLiteActionsController::load_scripts' );
		add_filter( 'frm_setup_edit_fields_vars', 'FrmStrpLiteSettingsController::prepare_field_desc', 30, 2 );
		add_filter( 'frm_setup_new_fields_vars', 'FrmStrpLiteSettingsController::prepare_field_desc', 30, 2 );
		add_action( 'init', 'FrmStrpLiteConnectHelper::check_for_stripe_connect_webhooks' );

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
		add_action( 'admin_init', 'FrmStrpLiteAppController::include_updater', 1 );
		add_action( 'frm_after_uninstall', 'FrmStrpLiteAppController::uninstall' );
		add_action( 'frm_pay_show_stripe_options', 'FrmStrpLiteActionsController::add_action_options' );
		add_action( 'frm_add_settings_section', 'FrmStrpLiteSettingsController::add_settings_section' );

		// Filters.
		add_filter( 'frm_pay_action_defaults', 'FrmStrpLiteActionsController::add_action_defaults' );
		add_filter( 'frm_before_save_payment_action', 'FrmStrpLiteActionsController::before_save_settings' );
		add_filter( 'frm_pay_stripe_receipt', 'FrmStrpLitePaymentsController::get_receipt_link' );

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
	}
}
