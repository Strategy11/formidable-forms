<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteHooksController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'frm_add_form_option_section', 'FrmSquareLiteActionsController::actions_js' );

		// Exit early, let the Payments submodule handle everything.
		if ( class_exists( 'FrmTransHooksController', false ) ) {
			return;
		}

		// Actions.
		add_action( 'frm_payment_cron', 'FrmTransLiteAppController::run_payment_cron' );
		add_filter( 'frm_registered_form_actions', 'FrmTransLiteActionsController::register_actions' );
		add_action( 'frm_add_form_option_section', 'FrmTransLiteActionsController::actions_js' );
		add_action( 'frm_trigger_payment_action', 'FrmTransLiteActionsController::trigger_action', 10, 3 );

		// Filters.
		add_filter( 'frm_action_triggers', 'FrmTransLiteActionsController::add_payment_trigger' );
		add_filter( 'frm_email_action_options', 'FrmTransLiteActionsController::add_trigger_to_action' );
		add_filter( 'frm_twilio_action_options', 'FrmTransLiteActionsController::add_trigger_to_action' );
		add_filter( 'frm_mailchimp_action_options', 'FrmTransLiteActionsController::add_trigger_to_action' );
		add_filter( 'frm_api_action_options', 'FrmTransLiteActionsController::add_trigger_to_action' );
		add_filter( 'frm_setup_new_fields_vars', 'FrmTransLiteActionsController::hide_gateway_field_on_front_end', 20, 2 );
		add_filter( 'frm_setup_edit_fields_vars', 'FrmTransLiteActionsController::hide_gateway_field_on_front_end', 20, 2 );
	}

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_action(
			'admin_init',
			function () {
				self::fix_addon_hooks();
			}
		);

		if ( class_exists( 'FrmTransHooksController', false ) ) {
			add_action( 'frm_pay_show_square_options', 'FrmTransLiteAppController::add_repeat_cadence_value' );

			// Exit early, let the Payments submodule handle everything.
			return;
		}

		// Actions.
		add_action( 'admin_menu', 'FrmTransLitePaymentsController::menu', 25 );
		add_action( 'admin_head', 'FrmTransLiteListsController::add_list_hooks' );
		add_action( 'frm_show_entry_sidebar', 'FrmTransLiteEntriesController::sidebar_list', 9 );
		add_action( 'frm_after_install', 'FrmTransLiteAppController::on_after_install' );

		// Filters.
		add_filter( 'set-screen-option', 'FrmTransLiteListsController::save_per_page', 10, 3 );

		// Use 9 to run before the Stripe Lite and Square Lite code.
		add_filter( 'frm_before_save_payment_action', 'FrmTransLiteActionsController::before_save_settings', 9, 2 );

		if ( defined( 'DOING_AJAX' ) ) {
			self::load_ajax_hooks();
		}
	}

	private static function load_ajax_hooks() {
		add_action( 'wp_ajax_frm_trans_refund', 'FrmTransLitePaymentsController::refund_payment' );
		add_action( 'wp_ajax_frm_trans_cancel', 'FrmTransLiteSubscriptionsController::cancel_subscription' );
	}

	/**
	 * Make sure that Payments appear when there are inbox items for all Payments plugins.
	 *
	 * @since 6.17.1
	 *
	 * @return void
	 */
	private static function fix_addon_hooks() {
		$unread_count = FrmEntriesHelper::get_visible_unread_inbox_count();
		if ( ! $unread_count ) {
			// Nothing to fix.
			return;
		}

		$menu_name = FrmAppHelper::get_menu_name();
		$hook_name = 'manage_' . sanitize_title( $menu_name ) . '-' . $unread_count . '_page_formidable-payments_columns';

		if ( FrmTransLiteAppHelper::should_fallback_to_paypal() && is_callable( 'FrmPaymentsController::payment_columns' ) ) {
			// Fallback to PayPal add-on.
			add_filter( $hook_name, 'FrmPaymentsController::payment_columns' );
		} elseif ( is_callable( 'FrmTransListsController::payment_columns' ) ) {
			// Fallback to the Payments submodule.
			add_filter( $hook_name, 'FrmTransListsController::payment_columns' );
		} else {
			return;
		}

		add_filter( 'screen_options_show_screen', 'FrmTransLiteListsController::remove_screen_options', 10, 2 );
	}
}
