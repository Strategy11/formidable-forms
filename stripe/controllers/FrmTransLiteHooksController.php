<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteHooksController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		register_activation_hook( dirname( dirname( __FILE__ ) ) . '/formidable-payments.php', 'FrmTransLiteAppController::install' );
		register_deactivation_hook( dirname( dirname( __FILE__ ) ) . '/formidable-payments.php', 'FrmTransLiteAppController::remove_cron' );

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
		// Actions.
		add_action( 'admin_menu', 'FrmTransLitePaymentsController::menu', 25 );
		add_action( 'admin_head', 'FrmTransLiteListsController::add_list_hooks' );
		add_action( 'frm_show_entry_sidebar', 'FrmTransLiteEntriesController::sidebar_list', 9 );

		// TODO Move to a load_ajax_hooks function.
		add_action( 'wp_ajax_frm_trans_refund', 'FrmTransLitePaymentsController::refund_payment' );
		add_action( 'wp_ajax_frm_trans_cancel', 'FrmTransLiteSubscriptionsController::cancel_subscription' );
	}
}
