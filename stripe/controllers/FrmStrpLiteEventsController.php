<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteEventsController {

	/**
	 * @var string
	 */
	public static $events_to_skip_option_name = 'frm_strp_events_to_skip';

	private $event;
	private $invoice;
	private $charge;
	private $status;

	/**
	 * @return void
	 */
	private function set_payment_status() {
		if ( $this->status === 'refunded' ) {
			$this->charge = $this->invoice->id;
		}

		$frm_payment = new FrmTransLitePayment();
		$payment     = false;

		if ( $this->charge ) {
			$payment = $frm_payment->get_one_by( $this->charge, 'receipt_id' );
		}

		if ( ! $payment && $this->status === 'refunded' ) {
			// If the refunded payment doesn't exist, stop here.
			FrmTransLiteLog::log_message( 'Stripe Webhook Message', 'No action taken. The refunded payment does not exist' );
			echo json_encode(
				array(
					'response' => 'no payment exists',
					'success'  => false,
				)
			);
			return;
		}

		$run_triggers = false;

		if ( ! $payment ) {
			$payment      = $this->prepare_from_invoice();
			$run_triggers = true;
		} elseif ( $payment->status !== $this->status ) {
			if ( $this->should_skip_status_update_for_first_recurring_payment( $payment ) ) {
				return;
			}

			$payment_values    = (array) $payment;
			$is_partial_refund = $this->is_partial_refund();

			if ( $is_partial_refund ) {
				$this->set_partial_refund( $payment_values );
				$amount_refunded = number_format( $this->invoice->amount_refunded / 100, 2 );
				// translators: %s: The amount of money that was refunded.
				$note = sprintf( __( 'Payment partially refunded %s', 'formidable' ), $amount_refunded );
			} else {
				$payment_values['status'] = $this->status;
				$payment->status          = $this->status;
				// translators: %s: The status of the payment.
				$note = sprintf( __( 'Payment %s', 'formidable' ), $payment_values['status'] );
			}

			FrmTransLiteAppHelper::add_note_to_payment( $payment_values, $note );

			$u = $frm_payment->update( $payment->id, $payment_values );

			echo json_encode(
				array(
					'response' => 'Payment ' . $payment->id . ' was updated',
					'success'  => true,
				)
			);
			if ( ! $is_partial_refund ) {
				$run_triggers = true;
			}
		}//end if

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
	 * Skip updating the payment object for the first recurring payment.
	 * This is to prevent double notifications because the first recurring payment creates an invoice and that invoice triggers the payment events.
	 *
	 * @since 6.5, introduced in v2.07 of the Stripe add on.
	 *
	 * @param stdClass $payment
	 * @return bool
	 */
	private function should_skip_status_update_for_first_recurring_payment( $payment ) {
		if ( ! in_array( $this->event->type, array( 'payment_intent.succeeded', 'payment_intent.payment_failed' ), true ) ) {
			return false;
		}

		if ( empty( $payment->sub_id ) ) {
			// Only skip for subscriptions. This is because subscription events create an invoice, and the status change events trigger for the invoice as well.
			return false;
		}

		return $this->is_first_payment( $payment );
	}

	/**
	 * Tell Stripe Connect API that the request came through by flushing early before processing.
	 * Flushing early allows the API to end the request earlier.
	 *
	 * @since 6.5, introduced in v2.07 of the Stripe add on.
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
	 * When a customer is deleted in Stripe, remove the link to a user.
	 *
	 * @since 6.5, introduced in v2.01 of the Stripe add on.
	 * @return void
	 */
	private function reset_customer() {
		global $wpdb;
		$customer_id = $this->invoice->id;
		if ( empty( $customer_id ) ) {
			return;
		}
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->usermeta WHERE meta_value = %s AND meta_key LIKE %s",
				$customer_id,
				'_frmstrp_customer_id%'
			)
		);
	}

	/**
	 * @return void
	 */
	private function maybe_subscription_canceled() {
		if ( $this->invoice->cancel_at_period_end == true ) {
			$this->subscription_canceled( 'future_cancel' );
		}
	}

	/**
	 * @param string $status
	 * @return bool
	 */
	private function subscription_canceled( $status = 'canceled' ) {
		$sub = $this->get_subscription( $this->invoice->id );
		if ( ! $sub ) {
			return false;
		}

		if ( $sub->status === $status ) {
			FrmTransLiteLog::log_message( 'Stripe Webhook Message', 'No action taken since the subscription is already canceled.' );
			echo json_encode(
				array(
					'response' => 'Already canceled',
					'success'  => true,
				)
			);
			return false;
		}

		FrmTransLiteSubscriptionsController::change_subscription_status(
			array(
				'status' => $status,
				'sub'    => $sub,
			)
		);
		return true;
	}

	private function prepare_from_invoice() {
		if ( empty( $this->invoice->subscription ) ) {
			// This isn't a subscription.
			FrmTransLiteLog::log_message( 'Stripe Webhook Message', 'No action taken since this is not a subscription.' );
			echo json_encode(
				array(
					'response' => 'Invoice missing',
					'success'  => false,
				)
			);
			return false;
		}

		$sub = $this->get_subscription( $this->invoice->subscription );
		if ( ! $sub ) {
			return false;
		}

		$payment        = $this->get_payment_for_sub( $sub->id );
		$payment_values = (array) $payment;
		$this->set_payment_values( $payment_values );

		$frm_payment = new FrmTransLitePayment();

		if ( $this->is_first_payment( $payment ) ) {
			// The first payment for the subscription needs to be updated with the receipt id.
			$frm_payment->update( $payment->id, $payment_values );
			$payment_id = $payment->id;
		} else {
			$payment_values['test'] = $this->event->livemode ? 0 : 1;

			// If this isn't the first, create a new payment.
			$payment_id = $frm_payment->create( $payment_values );
		}

		$this->update_next_bill_date( $sub, $payment_values );

		$payment = $frm_payment->get_one( $payment_id );
		return $payment;
	}

	/**
	 * @since 6.5, introduced in v2.07 of the Stripe add on.
	 *
	 * @param stdClass $payment
	 * @return bool
	 */
	private function is_first_payment( $payment ) {
		return ! $payment->receipt_id || 0 === strpos( $payment->receipt_id, 'pi_' );
	}

	private function get_subscription( $sub_id ) {
		$frm_sub = new FrmTransLiteSubscription();
		$sub     = $frm_sub->get_one_by( $sub_id, 'sub_id' );
		if ( ! $sub ) {
			// If this isn't an existing subscription, it must be a charge for another site/plugin.
			FrmTransLiteLog::log_message( 'Stripe Webhook Message', 'No action taken since there is not a matching subscription for ' . $sub_id );
			echo json_encode(
				array(
					'response' => 'Invoice missing',
					'success'  => false,
				)
			);
		}

		return $sub;
	}

	private function get_payment_for_sub( $sub_id ) {
		$frm_payment = new FrmTransLitePayment();
		return $frm_payment->get_one_by( $sub_id, 'sub_id' );
	}

	/**
	 * @param array $payment_values
	 * @return void
	 */
	private function set_payment_values( &$payment_values ) {
		$payment_values['begin_date']  = gmdate( 'Y-m-d' );
		$payment_values['expire_date'] = '0000-00-00';

		foreach ( $this->invoice->lines->data as $line ) {
			$payment_values['amount']      = number_format( $line->amount / 100, 2, '.', '' );
			$payment_values['begin_date']  = gmdate( 'Y-m-d', $line->period->start );
			$payment_values['expire_date'] = gmdate( 'Y-m-d', $line->period->end );
		}

		$payment_values['receipt_id'] = $this->charge ? $this->charge : __( 'None', 'formidable' );
		$payment_values['status']     = $this->status;
		$payment_values['meta_value'] = array();
		$payment_values['created_at'] = current_time( 'mysql', 1 );

		FrmTransLiteAppHelper::add_note_to_payment( $payment_values );
	}

	/**
	 * @param object $sub
	 * @param array  $payment
	 * @return void
	 */
	private function update_next_bill_date( $sub, $payment ) {
		$frm_sub = new FrmTransLiteSubscription();
		if ( $payment['status'] === 'complete' ) {
			$frm_sub->update( $sub->id, array( 'next_bill_date' => $payment['expire_date'] ) );
		} elseif ( $payment['status'] === 'refunded' ) {
			$frm_sub->update( $sub->id, array( 'next_bill_date' => $payment['begin_date'] ) );
		}
	}

	/**
	 * @return bool
	 */
	private function is_partial_refund() {
		$partial = false;
		if ( $this->status === 'refunded' ) {
			$amount          = $this->invoice->amount;
			$amount_refunded = $this->invoice->amount_refunded;
			$partial         = $amount != $amount_refunded;
		}
		return $partial;
	}

	/**
	 * @param array $payment_values
	 * @return void
	 */
	private function set_partial_refund( &$payment_values ) {
		$payment_values['amount'] = $this->invoice->amount - $this->invoice->amount_refunded;
		$payment_values['amount'] = number_format( $payment_values['amount'] / 100, 2 );
	}

	/**
	 * @return void
	 */
	public function process_connect_events() {
		$this->flush_response();

		$unprocessed_event_ids = FrmStrpLiteConnectHelper::get_unprocessed_event_ids();
		if ( $unprocessed_event_ids ) {
			$this->process_event_ids( $unprocessed_event_ids );
		}
		wp_send_json_success();
	}

	/**
	 * @since 6.5, introduced in v2.07 of the Stripe add on.
	 *
	 * @param array<string> $event_ids
	 * @return void
	 */
	private function process_event_ids( $event_ids ) {
		foreach ( $event_ids as $event_id ) {
			if ( $this->should_skip_event( $event_id ) ) {
				continue;
			}

			set_transient( 'frm_last_process_' . $event_id, time(), 60 );

			$this->event = FrmStrpLiteConnectHelper::get_event( $event_id );
			if ( is_object( $this->event ) ) {
				$this->handle_event();
				$this->track_handled_event( $event_id );
				FrmStrpLiteConnectHelper::process_event( $event_id );
			} else {
				$this->count_failed_event( $event_id );
			}
		}
	}

	/**
	 * @since 6.5, introduced in v2.07 of the Stripe add on.
	 *
	 * @param string $event_id
	 * @return bool True if the event should be skipped.
	 */
	private function should_skip_event( $event_id ) {
		if ( $this->last_attempt_to_process_event_is_too_recent( $event_id ) ) {
			return true;
		}

		$option = get_option( self::$events_to_skip_option_name );
		if ( ! is_array( $option ) ) {
			return false;
		}

		return in_array( $event_id, $option, true );
	}

	/**
	 * @param string $event_id
	 * @return bool
	 */
	private function last_attempt_to_process_event_is_too_recent( $event_id ) {
		$last_process_attempt = get_transient( 'frm_last_process_' . $event_id );
		return is_numeric( $last_process_attempt ) && $last_process_attempt > time() - 60;
	}

	/**
	 * @since 6.5, introduced in v2.07 of the Stripe add on.
	 *
	 * @param string $event_id
	 * @return void
	 */
	private function count_failed_event( $event_id ) {
		$transient_name = 'frm_failed_event_' . $event_id;
		$transient      = get_transient( $transient_name );
		if ( is_int( $transient ) ) {
			$failed_count = $transient + 1;
		} else {
			$failed_count = 1;
		}

		$maximum_retries = 3;
		if ( $failed_count >= $maximum_retries ) {
			$this->track_handled_event( $event_id );
		} else {
			set_transient( $transient_name, $failed_count );
		}
	}

	/**
	 * Track an event to no longer process.
	 * This is called for successful events, and also for failed events after a number of retries.
	 *
	 * @since 6.5, introduced in v2.07 of the Stripe add on.
	 *
	 * @param string $event_id
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
	 * @return void
	 */
	private function handle_event() {
		$this->invoice = $this->event->data->object;
		$this->charge  = isset( $this->invoice->charge ) ? $this->invoice->charge : false;
		if ( ! $this->charge && $this->invoice->object === 'payment_intent' ) {
			$this->charge = $this->invoice->id;
		}

		$events = array(
			'payment_intent.succeeded'      => 'complete',
			'payment_intent.payment_failed' => 'failed',
			'invoice.payment_succeeded'     => 'complete',
			'invoice.payment_failed'        => 'failed',
			'charge.refunded'               => 'refunded',
		);

		if ( isset( $events[ $this->event->type ] ) ) {
			$this->status = $events[ $this->event->type ];
			$this->set_payment_status();
		} elseif ( $this->event->type === 'customer.deleted' ) {
			$this->reset_customer();
		} elseif ( $this->event->type === 'customer.subscription.deleted' ) {
			$this->subscription_canceled();
		} elseif ( $this->event->type === 'customer.subscription.updated' ) {
			$this->maybe_subscription_canceled();
		}
	}
}
