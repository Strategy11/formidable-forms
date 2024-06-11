<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteAppController {

	/**
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
	 * Remove the cron when the plugin is deactivated.
	 *
	 * @return void
	 */
	public static function remove_cron() {
		wp_clear_scheduled_hook( 'frm_payment_cron' );
	}

	/**
	 * Process overdue subscriptions.
	 *
	 * @return void
	 */
	public static function run_payment_cron() {
		$frm_sub     = new FrmTransLiteSubscription();
		$frm_payment = new FrmTransLitePayment();

		$overdue_subscriptions = $frm_sub->get_overdue_subscriptions();
		FrmTransLiteLog::log_message( 'Stripe Cron Message', count( $overdue_subscriptions ) . ' subscriptions found to be processed.' );

		foreach ( $overdue_subscriptions as $sub ) {
			$last_payment = $frm_payment->get_one_by( $sub->id, 'sub_id' );

			$log_message = 'Subscription #' . $sub->id . ': ';
			if ( $sub->status === 'future_cancel' ) {
				FrmTransLiteSubscriptionsController::change_subscription_status(
					array(
						'status' => 'canceled',
						'sub'    => $sub,
					)
				);

				$status       = 'failed';
				$log_message .= 'Failed triggers run on canceled subscription. ';
			} else {
				// Get the most recent payment after the gateway has a chance to create one.
				$check_payment = $frm_payment->get_one_by( $sub->id, 'sub_id' );
				$new_payment   = $check_payment->id != $last_payment->id;
				$last_payment  = $check_payment;
				$status        = 'no';

				if ( ! $last_payment ) {
					$log_message .= 'No payments found for subscription #' . $sub->id . '. ';
					self::add_one_fail( $sub );
				} elseif ( $new_payment ) {
					$status = $last_payment->status;
					self::update_sub_for_new_payment( $sub, $last_payment );
				} elseif ( $last_payment->expire_date < gmdate( 'Y-m-d' ) ) {
					// The payment has expired, and no new payment was made.
					$status = 'failed';
					self::add_one_fail( $sub );
				} else {
					// Don't run any triggers.
					$last_payment = false;
				}

				$log_message .= $status . ' triggers run ';
				if ( $last_payment ) {
					$log_message .= 'on payment #' . $last_payment->id;
				}
			}//end if

			FrmTransLiteLog::log_message( 'Stripe Cron Message', $log_message );

			self::maybe_trigger_changes(
				array(
					'status'  => $status,
					'payment' => $last_payment,
				)
			);

			unset( $sub );
		}//end foreach
	}

	/**
	 * @param object $sub
	 * @param object $last_payment
	 *
	 * @return void
	 */
	private static function update_sub_for_new_payment( $sub, $last_payment ) {
		$frm_sub = new FrmTransLiteSubscription();
		if ( $last_payment->status === 'complete' ) {
			$frm_sub->update(
				$sub->id,
				array(
					'fail_count'     => 0,
					'next_bill_date' => $last_payment->expire_date,
				)
			);
		} elseif ( $last_payment->status === 'failed' ) {
			self::add_one_fail( $sub );
		}
	}

	/**
	 * Add to the fail count.
	 * If the subscription has failed > 3 times, set it to canceled.
	 *
	 * @param object $sub
	 * @return void
	 */
	private static function add_one_fail( $sub ) {
		$frm_sub    = new FrmTransLiteSubscription();
		$fail_count = $sub->fail_count + 1;
		$new_values = compact( 'fail_count' );
		$frm_sub->update( $sub->id, $new_values );

		if ( $fail_count > 3 ) {
			FrmTransLiteSubscriptionsController::change_subscription_status(
				array(
					'status' => 'canceled',
					'sub'    => $sub,
				)
			);
		}
	}

	/**
	 * @param array $atts
	 * @return void
	 */
	private static function maybe_trigger_changes( $atts ) {
		if ( $atts['payment'] ) {
			FrmTransLiteActionsController::trigger_payment_status_change( $atts );
		}
	}
}
