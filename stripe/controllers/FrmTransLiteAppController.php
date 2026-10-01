<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteAppController {

	/**
	 * Install or upgrade database structures.
	 *
	 * @param mixed $old_db_version Previous database version, or false on fresh install.
	 *
	 * @return void
	 */
	public static function install( $old_db_version = false ) {
		self::maybe_schedule_cron();

		$db = new FrmTransLiteDb();
		$db->upgrade( $old_db_version );
	}

	/**
	 * This is called on the frm_after_install hook that is called when Lite migrations have run.
	 *
	 * @return void
	 */
	public static function on_after_install() {
		if ( ! FrmTransLiteAppHelper::payments_table_exists() ) {
			return;
		}

		$db = new FrmTransLiteDb();
		$db->upgrade();
	}

	/**
	 * Schedule the payment cron if it is not already scheduled.
	 *
	 * @return void
	 */
	public static function maybe_schedule_cron() {
		if ( ! wp_next_scheduled( 'frm_payment_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'frm_payment_cron' );
		}
	}

	/**
	 * Remove the payment cron when all gateways are disconnected.
	 *
	 * @since 6.32.1
	 *
	 * @param string $gateway 'stripe', 'square', or 'paypal'.
	 * @param string $mode 'test' or 'live'.
	 *
	 * @return void
	 */
	public static function maybe_remove_payment_cron( $gateway, $mode ) {
		$stripe_connected = FrmStrpLiteConnectHelper::at_least_one_mode_is_setup();
		$square_connected = FrmSquareLiteConnectHelper::at_least_one_mode_is_setup();
		$paypal_connected = FrmPayPalLiteConnectHelper::at_least_one_mode_is_setup();

		if ( ! $stripe_connected && ! $square_connected && ! $paypal_connected ) {
			wp_clear_scheduled_hook( 'frm_payment_cron' );
		}
	}

	/**
	 * Process overdue subscriptions.
	 *
	 * @return void
	 */
	public static function run_payment_cron() {
		$frm_sub               = new FrmTransLiteSubscription();
		$frm_payment           = new FrmTransLitePayment();
		$overdue_subscriptions = $frm_sub->get_overdue_subscriptions();

		if ( ! $overdue_subscriptions && ! $frm_sub->get_active_subscriptions() ) {
			return;
		}

		FrmTransLiteLog::log_message( 'Overdue Subscription Cron Message', count( $overdue_subscriptions ) . ' subscriptions found to be processed.', false );

		foreach ( $overdue_subscriptions as $sub ) {
			if ( $sub->status !== 'future_cancel' ) {
				continue;
			}

			$last_payment = $frm_payment->get_one_by( $sub->id, 'sub_id' );

			if ( ! $last_payment ) {
				continue;
			}

			FrmTransLiteSubscriptionsController::change_subscription_status(
				array(
					'status' => 'canceled',
					'sub'    => $sub,
				)
			);

			unset( $sub );
		}//end foreach
	}

	/**
	 * @param array $atts
	 *
	 * @return void
	 */
	private static function maybe_trigger_changes( $atts ) {
		if ( $atts['payment'] ) {
			FrmTransLiteActionsController::trigger_payment_status_change( $atts );
		}
	}

	/**
	 * This is called when the Payments submodule is active.
	 * It ensures that the hidden repeater cadence input exists even when another add-on is handling the settings.
	 *
	 * @since 6.22
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public static function add_repeat_cadence_value( $args ) {
		$action = $args['form_action'];

		if ( empty( $action->post_content['repeat_cadence'] ) ) {
			return;
		}

		$params = array(
			'type'  => 'hidden',
			'class' => 'frm-repeat-cadence-value',
			'value' => $action->post_content['repeat_cadence'],
		);
		echo '<input ';
		FrmAppHelper::array_to_html_params( $params, true );
		echo ' />';
	}

	/**
	 * Gateway fields are included for add-on compatibility but we do not want it to be visible.
	 * They do however need to be visible when the payments submodule is active.
	 *
	 * @since 6.30
	 *
	 * @return void
	 */
	public static function hide_gateway_fields_in_builder() {
		wp_add_inline_style(
			'formidable-admin',
			'
			#frm_builder_page li[data-ftype="gateway"] { display: none; }
			.frm_field_box:has(li[data-ftype="gateway"]:only-child) { display: none; }
			'
		);
	}

	/**
	 * Remove the cron when the plugin is deactivated.
	 *
	 * @deprecated 6.32.1
	 *
	 * @return void
	 */
	public static function remove_cron() {
		_deprecated_function( __METHOD__, '6.32.1' );
		wp_clear_scheduled_hook( 'frm_payment_cron' );
	}
}
