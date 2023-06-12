<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteHooksController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		if ( class_exists( 'FrmTransHooksController', false ) ) {
			// Exit early, let the Payments submodule handle everything.
			return;
		}

		// Actions.
		add_action( 'frm_payment_cron', 'FrmTransLiteAppController::run_payment_cron' );
		add_action( 'frm_registered_form_actions', 'FrmTransLiteActionsController::register_actions' );
		add_action( 'frm_add_form_option_section', 'FrmTransLiteActionsController::actions_js' );
		add_action( 'frm_trigger_payment_action', 'FrmTransLiteActionsController::trigger_action', 10, 3 );

		// Filters.
		add_filter( 'frm_action_triggers', 'FrmTransLiteActionsController::add_payment_trigger' );
		add_filter( 'frm_email_action_options', 'FrmTransLiteActionsController::add_trigger_to_action' );
		add_filter( 'frm_twilio_action_options', 'FrmTransLiteActionsController::add_trigger_to_action' );
		add_filter( 'frm_mailchimp_action_options', 'FrmTransLiteActionsController::add_trigger_to_action' );
		add_filter( 'frm_api_action_options', 'FrmTransLiteActionsController::add_trigger_to_action' );
	}

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		if ( class_exists( 'FrmTransHooksController', false ) ) {
			// Exit early, let the Payments submodule handle everything.
			return;
		}

		// Actions.
		add_action( 'admin_menu', 'FrmTransLitePaymentsController::menu', 25 );
		add_action( 'admin_head', 'FrmTransLiteListsController::add_list_hooks' );
		add_action( 'frm_show_entry_sidebar', 'FrmTransLiteEntriesController::sidebar_list', 9 );
		add_action( 'admin_init', self::class . '::maybe_redirect_to_stripe_settings' );

		// Filters
		add_filter( 'set-screen-option', 'FrmTransLiteListsController::save_per_page', 10, 3 );

		if ( defined( 'DOING_AJAX' ) ) {
			self::load_ajax_hooks();
		}
	}

	/**
	 * Redirect to Stripe settings when payments are not yet installed
	 * and the payments page is accessed by its URL.
	 *
	 * @return void
	 */
	public static function maybe_redirect_to_stripe_settings() {
		if ( ! FrmAppHelper::is_admin_page( 'formidable-payments' ) || self::payments_are_installed() ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=formidable-settings&t=stripe_settings' ) );
		die();
	}

	/**
	 * @return bool
	 */
	private static function payments_are_installed() {
		$db     = new FrmTransLiteDb();
		$option = get_option( $db->db_opt_name );
		return false !== $option;
	}

	private static function load_ajax_hooks() {
		add_action( 'wp_ajax_frm_trans_refund', 'FrmTransLitePaymentsController::refund_payment' );
		add_action( 'wp_ajax_frm_trans_cancel', 'FrmTransLiteSubscriptionsController::cancel_subscription' );
	}
}
