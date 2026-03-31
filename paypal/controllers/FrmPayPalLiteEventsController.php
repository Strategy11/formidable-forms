<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteEventsController {

	/**
	 * @var string
	 */
	public static $events_to_skip_option_name = 'frm_paypal_events_to_skip';

	/**
	 * @var object|null
	 */
	private $event;

	/**
	 * The data object from the event.
	 *
	 * @since x.x
	 *
	 * @var object|null
	 */
	private $resource;

	/**
	 * The mapped payment status for the current event.
	 *
	 * @since x.x
	 *
	 * @var string|null
	 */
	private $status;

	/**
	 * Tell PayPal Connect API that the request came through by flushing early before processing.
	 * Flushing early allows the API to end the request earlier.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function flush_response() {
		ob_start();

		// Get the size of the output.
		$size = ob_get_length();

		// Disable compression (in case content length is compressed).
		header( 'Content-Encoding: none' );

		// Set the content length of the response.
		header( 'Content-Length: ' . $size );

		// Close the connection.
		header( 'Connection: close' );

		// Flush all output.
		ob_end_flush();
		@ob_flush();
		flush();
	}

	/**
	 * @return void
	 */
	public function process_events() {
		$this->flush_response();

		$unprocessed_event_ids = FrmPayPalLiteConnectHelper::get_unprocessed_event_ids();

		if ( $unprocessed_event_ids ) {
			$this->process_event_ids( $unprocessed_event_ids );
		}

		wp_send_json_success();
	}

	/**
	 * @since x.x
	 *
	 * @param array<string> $event_ids
	 *
	 * @return void
	 */
	private function process_event_ids( $event_ids ) {
		foreach ( $event_ids as $event_id ) {
			if ( $this->should_skip_event( $event_id ) ) {
				continue;
			}

			set_transient( 'frm_paypal_last_process_' . $event_id, time(), 60 );

			$this->event = FrmPayPalLiteConnectHelper::get_event( $event_id );

			if ( ! is_object( $this->event ) ) {
				$this->count_failed_event( $event_id );
				continue;
			}

			$this->handle_event();
			$this->track_handled_event( $event_id );
			FrmPayPalLiteConnectHelper::process_event( $event_id );
		}
	}

	/**
	 * @since x.x
	 *
	 * @param string $event_id
	 *
	 * @return bool True if the event should be skipped.
	 */
	private function should_skip_event( $event_id ) {
		if ( $this->last_attempt_to_process_event_is_too_recent( $event_id ) ) {
			return true;
		}

		$option = get_option( self::$events_to_skip_option_name );

		return is_array( $option ) && in_array( $event_id, $option, true );
	}

	/**
	 * @param string $event_id
	 *
	 * @return bool
	 */
	private function last_attempt_to_process_event_is_too_recent( $event_id ) {
		$last_process_attempt = get_transient( 'frm_paypal_last_process_' . $event_id );
		return is_numeric( $last_process_attempt ) && $last_process_attempt > time() - 60;
	}

	/**
	 * @since x.x
	 *
	 * @param string $event_id
	 *
	 * @return void
	 */
	private function count_failed_event( $event_id ) {
		$transient_name  = 'frm_paypal_failed_event_' . $event_id;
		$transient       = get_transient( $transient_name );
		$failed_count    = is_int( $transient ) ? $transient + 1 : 1;
		$maximum_retries = 3;

		if ( $failed_count >= $maximum_retries ) {
			$this->track_handled_event( $event_id );
		} else {
			set_transient( $transient_name, $failed_count, 4 * DAY_IN_SECONDS );
		}
	}

	/**
	 * Track an event to no longer process.
	 * This is called for successful events, and also for failed events after a number of retries.
	 *
	 * @since x.x
	 *
	 * @param string $event_id
	 *
	 * @return void
	 */
	private function track_handled_event( $event_id ) {
		$option = get_option( self::$events_to_skip_option_name );

		if ( is_array( $option ) ) {
			if ( count( $option ) > 1000 ) {
				// Prevent the option from getting too big by removing the front item before adding the next.
				array_shift( $option );
			}
		} else {
			$option = array();
		}

		$option[] = $event_id;
		update_option( self::$events_to_skip_option_name, $option, false );
	}

	/**
	 * Log a PayPal webhook message using FrmLog when available.
	 *
	 * @since x.x
	 *
	 * @param string $title   The log entry title.
	 * @param mixed  $content The log entry content.
	 *
	 * @return void
	 */
	private function log( $title, $content = '' ) {
		if ( ! class_exists( 'FrmLog' ) ) {
			return;
		}

		$log = new FrmLog();
		$log->add(
			array(
				'title'   => $title,
				'content' => is_string( $content ) ? $content : print_r( $content, true ),
			)
		);
	}

	/**
	 * @return void
	 */
	private function handle_event() {
		$this->resource = $this->event->resource ?? null;

		if ( ! is_object( $this->resource ) ) {
			FrmTransLiteLog::log_message( 'PayPal Webhook Message', 'No resource object found in event' );
			return;
		}

		$this->log( 'PayPal Webhook: Received Event', array(
			'event_type'  => $this->event->event_type ?? 'unknown',
			'resource_id' => $this->resource->id ?? 'unknown',
		) );

		$payment_events = array(
			'PAYMENT.CAPTURE.COMPLETED' => 'complete',
			'PAYMENT.CAPTURE.DECLINED'  => 'failed',
			'PAYMENT.CAPTURE.DENIED'    => 'failed',
			'PAYMENT.CAPTURE.REFUNDED'  => 'refunded',
			'PAYMENT.CAPTURE.REVERSED'  => 'refunded',
			'PAYMENT.SALE.COMPLETED'    => 'complete',
			'PAYMENT.SALE.DENIED'       => 'failed',
			'PAYMENT.SALE.REFUNDED'     => 'refunded',
			'PAYMENT.SALE.REVERSED'     => 'refunded',
		);

		if ( isset( $payment_events[ $this->event->event_type ] ) ) {
			$this->status = $payment_events[ $this->event->event_type ];
			$this->log( 'PayPal Webhook: Payment Event', array(
				'event_type'    => $this->event->event_type,
				'mapped_status' => $this->status,
				'resource_id'   => $this->resource->id ?? '',
				'amount'        => $this->get_amount_from_resource(),
			) );
			$this->handle_payment_event();
			return;
		}

		switch ( $this->event->event_type ) {
			case 'BILLING.SUBSCRIPTION.ACTIVATED':
			case 'BILLING.SUBSCRIPTION.RE-ACTIVATED':
				$this->handle_subscription_activated();
				break;

			case 'BILLING.SUBSCRIPTION.CANCELLED':
			case 'BILLING.SUBSCRIPTION.EXPIRED':
			case 'BILLING.SUBSCRIPTION.SUSPENDED':
				$this->handle_subscription_canceled();
				break;

			case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
				$this->handle_subscription_payment_failed();
				break;

			case 'BILLING.SUBSCRIPTION.UPDATED':
				$this->handle_subscription_updated();
				break;
		}
	}

	/**
	 * Handle a payment capture or sale event by syncing the payment record.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function handle_payment_event() {
		$receipt_id = $this->resource->id ?? '';

		if ( ! $receipt_id ) {
			FrmTransLiteLog::log_message( 'PayPal Webhook Message', 'No resource ID found in payment event' );
			return;
		}

		$frm_payment = new FrmTransLitePayment();
		$payment     = $frm_payment->get_one_by( $receipt_id, 'receipt_id' );

		if ( ! $payment && $this->status === 'refunded' ) {
			$this->log( 'PayPal Webhook: Refund Skipped', 'No matching payment found for receipt ' . $receipt_id );
			FrmTransLiteLog::log_message( 'PayPal Webhook Message', 'No action taken. The refunded payment does not exist for ' . $receipt_id );
			return;
		}

		$run_triggers = false;

		if ( ! $payment ) {
			$this->log( 'PayPal Webhook: No Existing Payment', 'No payment found for receipt ' . $receipt_id . '. Checking for subscription payment.' );
			$payment = $this->maybe_create_subscription_payment();

			if ( $payment ) {
				$run_triggers = true;
			}
		} elseif ( $payment->status !== $this->status ) {
			$payment_values = (array) $payment;

			$is_partial_refund = $this->is_partial_refund();

			if ( $is_partial_refund ) {
				$this->set_partial_refund( $payment_values );
				$amount_refunded = $this->get_refunded_amount();
				// translators: %s: The amount of money that was refunded.
				$note = sprintf( __( 'Payment partially refunded %s', 'formidable' ), $amount_refunded );
			} else {
				$payment_values['status'] = $this->status;
				$payment->status          = $this->status;
				// translators: %s: The status of the payment.
				$note = sprintf( __( 'Payment %s', 'formidable' ), $payment_values['status'] );
			}

			FrmTransLiteAppHelper::add_note_to_payment( $payment_values, $note );

			$frm_payment->update( $payment->id, $payment_values );

			$this->log( 'PayPal Webhook: Payment Updated', array(
				'payment_id'       => $payment->id,
				'old_status'       => $payment->status,
				'new_status'       => $this->status,
				'is_partial_refund' => $is_partial_refund,
				'note'             => $note,
			) );

			if ( ! $is_partial_refund ) {
				$run_triggers = true;
			}
		}

		if ( $run_triggers && $payment && $payment->action_id ) {
			FrmTransLiteActionsController::trigger_payment_status_change(
				array(
					'status'  => $this->status,
					'payment' => $payment,
				)
			);
		}
	}

	/**
	 * Try to create a new payment record from a subscription payment event.
	 * This handles recurring payments after the first one.
	 *
	 * @since x.x
	 *
	 * @return false|object The new payment object or false if not a subscription payment.
	 */
	private function maybe_create_subscription_payment() {
		$subscription_id = $this->get_subscription_id_from_resource();

		if ( ! $subscription_id ) {
			return false;
		}

		$sub = $this->get_subscription( $subscription_id );

		if ( ! $sub ) {
			return false;
		}

		$receipt_id = $this->resource->id ?? '';
		$amount     = $this->get_amount_from_resource();

		$payment_values = array(
			'paysys'      => 'paypal',
			'amount'      => $amount,
			'status'      => $this->status,
			'item_id'     => $sub->item_id,
			'action_id'   => $sub->action_id,
			'receipt_id'  => $receipt_id,
			'sub_id'      => $sub->id,
			'begin_date'  => gmdate( 'Y-m-d' ),
			'expire_date' => '0000-00-00',
			'meta_value'  => array(),
			'created_at'  => current_time( 'mysql', 1 ),
			'test'        => 'test' === FrmPayPalLiteAppHelper::active_mode() ? 1 : 0,
		);

		FrmTransLiteAppHelper::add_note_to_payment( $payment_values );

		$frm_payment = new FrmTransLitePayment();

		$existing_payment = $frm_payment->get_one_by( $receipt_id, 'receipt_id' );

		if ( $existing_payment ) {
			$this->log( 'PayPal Webhook: Duplicate Payment Skipped', 'Payment already exists for receipt ' . $receipt_id );
			return false;
		}

		$payment_id = $frm_payment->create( $payment_values );

		$this->log( 'PayPal Webhook: Subscription Payment Created', array(
			'payment_id'      => $payment_id,
			'subscription_id' => $subscription_id,
			'receipt_id'      => $receipt_id,
			'amount'          => $amount,
			'status'          => $this->status,
		) );

		$this->update_next_bill_date( $sub );
		$this->maybe_cancel_subscription_at_limit( $sub );

		return $frm_payment->get_one( $payment_id );
	}

	/**
	 * Get the PayPal subscription ID from the resource object.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private function get_subscription_id_from_resource() {
		if ( ! empty( $this->resource->billing_agreement_id ) ) {
			return $this->resource->billing_agreement_id;
		}

		if ( ! empty( $this->resource->supplementary_data->related_ids->subscription_id ) ) {
			return $this->resource->supplementary_data->related_ids->subscription_id;
		}

		return '';
	}

	/**
	 * Get the payment amount from the resource object.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private function get_amount_from_resource() {
		if ( ! empty( $this->resource->amount->total ) ) {
			return $this->resource->amount->total;
		}

		if ( ! empty( $this->resource->amount->value ) ) {
			return $this->resource->amount->value;
		}

		return '0.00';
	}

	/**
	 * Get the refunded amount from the resource object.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private function get_refunded_amount() {
		if ( ! empty( $this->resource->amount->total ) ) {
			return $this->resource->amount->total;
		}

		if ( ! empty( $this->resource->amount->value ) ) {
			return $this->resource->amount->value;
		}

		return '0.00';
	}

	/**
	 * Check if a refund is partial by comparing the refunded amount to the original payment amount.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private function is_partial_refund() {
		if ( $this->status !== 'refunded' ) {
			return false;
		}

		$receipt_id = $this->resource->id ?? '';

		if ( ! $receipt_id ) {
			return false;
		}

		// For refund events, look up the original payment to compare amounts.
		$sale_id = $this->resource->sale_id ?? ( $this->resource->links[0]->href ?? '' );

		if ( ! $sale_id ) {
			return false;
		}

		$frm_payment      = new FrmTransLitePayment();
		$original_payment  = $frm_payment->get_one_by( $receipt_id, 'receipt_id' );
		$refunded_amount   = (float) $this->get_refunded_amount();

		if ( $original_payment ) {
			return $refunded_amount < (float) $original_payment->amount;
		}

		return false;
	}

	/**
	 * Set the partial refund amount on a payment.
	 *
	 * @since x.x
	 *
	 * @param array $payment_values The payment values to update.
	 *
	 * @return void
	 */
	private function set_partial_refund( &$payment_values ) {
		$refunded = (float) $this->get_refunded_amount();
		$original = (float) $payment_values['amount'];

		$payment_values['amount'] = number_format( $original - $refunded, 2, '.', '' );
	}

	/**
	 * Get a subscription record by its PayPal subscription ID.
	 *
	 * @since x.x
	 *
	 * @param string $sub_id The PayPal subscription ID.
	 *
	 * @return object|null
	 */
	private function get_subscription( $sub_id ) {
		$frm_sub = new FrmTransLiteSubscription();
		$sub     = $frm_sub->get_one_by( $sub_id, 'sub_id' );

		if ( ! $sub ) {
			FrmTransLiteLog::log_message( 'PayPal Webhook Message', 'No action taken since there is not a matching subscription for ' . $sub_id );
		}

		return $sub;
	}

	/**
	 * Update the next bill date for a subscription using the PayPal API.
	 *
	 * @since x.x
	 *
	 * @param object $sub The local subscription record.
	 *
	 * @return void
	 */
	private function update_next_bill_date( $sub ) {
		$subscription = FrmPayPalLiteConnectHelper::get_subscription( $sub->sub_id );

		if ( ! is_object( $subscription ) ) {
			$this->log( 'PayPal Webhook: Next Bill Date Update Failed', 'Could not retrieve subscription ' . $sub->sub_id . ' from PayPal API' );
			return;
		}

		$next_bill_date = '';

		if ( ! empty( $subscription->billing_info->next_billing_time ) ) {
			$next_bill_date = gmdate( 'Y-m-d', strtotime( $subscription->billing_info->next_billing_time ) );
		}

		if ( $next_bill_date ) {
			$frm_sub = new FrmTransLiteSubscription();
			$frm_sub->update( $sub->id, array( 'next_bill_date' => $next_bill_date ) );
			$this->log( 'PayPal Webhook: Next Bill Date Updated', array(
				'subscription_id' => $sub->sub_id,
				'next_bill_date'  => $next_bill_date,
			) );
		}
	}

	/**
	 * Check if a subscription has reached its payment limit.
	 * If it has, cancel the subscription.
	 *
	 * @since x.x
	 *
	 * @param object $sub The local subscription record.
	 *
	 * @return void
	 */
	private function maybe_cancel_subscription_at_limit( $sub ) {
		$action = FrmFormAction::get_single_action_type( $sub->action_id, 'payment' );

		if ( ! is_object( $action ) || empty( $action->post_content['payment_limit'] ) ) {
			return;
		}

		$frm_payment  = new FrmTransLitePayment();
		$all_payments = $frm_payment->get_all_by( $sub->id, 'sub_id' );
		$count        = FrmTransLiteAppHelper::count_completed_payments( $all_payments );

		if ( $count < (int) $action->post_content['payment_limit'] ) {
			return;
		}

		$cancelled = FrmPayPalLiteConnectHelper::cancel_subscription( $sub->sub_id );

		if ( $cancelled ) {
			$this->log( 'PayPal Webhook: Subscription Cancelled at Limit', array(
				'subscription_id' => $sub->sub_id,
				'payment_count'   => $count,
				'payment_limit'   => $action->post_content['payment_limit'],
			) );

			FrmTransLiteSubscriptionsController::change_subscription_status(
				array(
					'status' => 'future_cancel',
					'sub'    => $sub,
				)
			);
		}
	}

	/**
	 * Handle a subscription activated or re-activated event.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function handle_subscription_activated() {
		$subscription_id = $this->resource->id ?? '';

		if ( ! $subscription_id ) {
			return;
		}

		$sub = $this->get_subscription( $subscription_id );

		if ( ! $sub ) {
			return;
		}

		if ( $sub->status === 'active' ) {
			$this->log( 'PayPal Webhook: Subscription Already Active', 'Subscription ' . $subscription_id . ' is already active. No action taken.' );
			FrmTransLiteLog::log_message( 'PayPal Webhook Message', 'No action taken since the subscription is already active.' );
			return;
		}

		$this->log( 'PayPal Webhook: Subscription Activated', array(
			'subscription_id' => $subscription_id,
			'event_type'      => $this->event->event_type,
			'old_status'      => $sub->status,
		) );

		FrmTransLiteSubscriptionsController::change_subscription_status(
			array(
				'status' => 'active',
				'sub'    => $sub,
			)
		);

		$this->update_next_bill_date( $sub );
	}

	/**
	 * Handle a subscription cancelled, expired, or suspended event.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function handle_subscription_canceled() {
		$subscription_id = $this->resource->id ?? '';

		if ( ! $subscription_id ) {
			return;
		}

		$sub = $this->get_subscription( $subscription_id );

		if ( ! $sub ) {
			return;
		}

		if ( $sub->status === 'canceled' ) {
			$this->log( 'PayPal Webhook: Subscription Already Canceled', 'Subscription ' . $subscription_id . ' is already canceled. No action taken.' );
			FrmTransLiteLog::log_message( 'PayPal Webhook Message', 'No action taken since the subscription is already canceled.' );
			return;
		}

		$this->log( 'PayPal Webhook: Subscription Canceled', array(
			'subscription_id' => $subscription_id,
			'event_type'      => $this->event->event_type,
			'old_status'      => $sub->status,
		) );

		FrmTransLiteSubscriptionsController::change_subscription_status(
			array(
				'status' => 'canceled',
				'sub'    => $sub,
			)
		);
	}

	/**
	 * Handle a subscription payment failed event.
	 * Increments the fail count and cancels after too many failures.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function handle_subscription_payment_failed() {
		$subscription_id = $this->resource->id ?? '';

		if ( ! $subscription_id ) {
			return;
		}

		$sub = $this->get_subscription( $subscription_id );

		if ( ! $sub ) {
			return;
		}

		$frm_sub    = new FrmTransLiteSubscription();
		$fail_count = (int) $sub->fail_count + 1;

		$frm_sub->update(
			$sub->id,
			array( 'fail_count' => $fail_count )
		);

		$this->log( 'PayPal Webhook: Subscription Payment Failed', array(
			'subscription_id' => $subscription_id,
			'fail_count'      => $fail_count,
			'will_cancel'     => $fail_count > 3,
		) );

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
	 * Handle a subscription updated event.
	 * Syncs subscription data like amount and next bill date.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function handle_subscription_updated() {
		$subscription_id = $this->resource->id ?? '';

		if ( ! $subscription_id ) {
			return;
		}

		$sub = $this->get_subscription( $subscription_id );

		if ( ! $sub ) {
			return;
		}

		$new_values = array();

		// Sync the subscription status.
		$paypal_status = $this->resource->status ?? '';
		$status_map    = array(
			'ACTIVE'    => 'active',
			'SUSPENDED' => 'canceled',
			'CANCELLED' => 'canceled',
			'EXPIRED'   => 'canceled',
		);

		if ( $paypal_status && isset( $status_map[ $paypal_status ] ) && $sub->status !== $status_map[ $paypal_status ] ) {
			$this->log( 'PayPal Webhook: Subscription Status Synced', array(
				'subscription_id' => $subscription_id,
				'old_status'      => $sub->status,
				'new_status'      => $status_map[ $paypal_status ],
				'paypal_status'   => $paypal_status,
			) );

			FrmTransLiteSubscriptionsController::change_subscription_status(
				array(
					'status' => $status_map[ $paypal_status ],
					'sub'    => $sub,
				)
			);
		}

		// Sync next billing date and amount from the PayPal API.
		$subscription = FrmPayPalLiteConnectHelper::get_subscription( $subscription_id );

		if ( ! is_object( $subscription ) ) {
			return;
		}

		if ( ! empty( $subscription->billing_info->next_billing_time ) ) {
			$new_values['next_bill_date'] = gmdate( 'Y-m-d', strtotime( $subscription->billing_info->next_billing_time ) );
		}

		if ( ! empty( $subscription->plan->billing_cycles[0]->pricing_scheme->fixed_price->value ) ) {
			$new_values['amount'] = $subscription->plan->billing_cycles[0]->pricing_scheme->fixed_price->value;
		}

		if ( $new_values ) {
			$frm_sub = new FrmTransLiteSubscription();
			$frm_sub->update( $sub->id, $new_values );

			$this->log( 'PayPal Webhook: Subscription Data Synced', array_merge(
				array( 'subscription_id' => $subscription_id ),
				$new_values
			) );
		}
	}
}
