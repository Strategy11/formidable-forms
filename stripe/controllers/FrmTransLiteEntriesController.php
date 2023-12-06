<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteEntriesController {

	/**
	 * Include payment details in the entry sidebar.
	 *
	 * @param stdClass $entry
	 * @return void
	 */
	public static function sidebar_list( $entry ) {
		if ( FrmTransLiteAppHelper::should_fallback_to_paypal() ) {
			return;
		}

		// This line removes PayPal actions from the entries sidebar.
		remove_action( 'frm_show_entry_sidebar', 'FrmPaymentsController::sidebar_list' );
		add_action( 'frm_entry_shared_sidebar_middle', __CLASS__ . '::show_sidebar_list' );
	}

	/**
	 * Show the payment details in the entry sidebar.
	 *
	 * @param stdClass $entry
	 * @return void
	 */
	public static function show_sidebar_list( $entry ) {
		$frm_payment = new FrmTransLitePayment();
		$payments    = $frm_payment->get_all_for_entry( $entry->id );
		if ( ! $payments ) {
			return;
		}

		$frm_sub       = new FrmTransLiteSubscription();
		$subscriptions = $frm_sub->get_all_for_entry( $entry->id );
		$entry_total   = 0;
		$date_format   = get_option( 'date_format' );

		FrmTransLiteActionsController::actions_js();

		include FrmTransLiteAppHelper::plugin_path() . '/views/payments/sidebar_list.php';
	}
}
