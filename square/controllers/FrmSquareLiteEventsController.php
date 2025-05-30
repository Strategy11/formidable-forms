<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteEventsController {

	/**
	 * @var string
	 */
	public static $events_to_skip_option_name = 'frm_square_events_to_skip';

	private $event;

	/**
	 * Tell Square Connect API that the request came through by flushing early before processing.
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

		$unprocessed_event_ids = FrmSquareLiteConnectHelper::get_unprocessed_event_ids();

		if ( $unprocessed_event_ids ) {
			$this->process_event_ids( $unprocessed_event_ids );
		}
		wp_send_json_success();
	}

	/**
	 * @since x.x
	 *
	 * @param array<string> $event_ids
	 * @return void
	 */
	private function process_event_ids( $event_ids ) {
		foreach ( $event_ids as $event_id ) {
			if ( $this->should_skip_event( $event_id ) ) {
				continue;
			}

			set_transient( 'frm_square_last_process_' . $event_id, time(), 60 );

			$this->event = FrmSquareLiteConnectHelper::get_event( $event_id );

			if ( is_object( $this->event ) ) {
				$this->handle_event();
				$this->track_handled_event( $event_id );
				FrmSquareLiteConnectHelper::process_event( $event_id );
			} else {
				$this->count_failed_event( $event_id );
			}
		}
	}

	/**
	 * @since x.x
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
		$last_process_attempt = get_transient( 'frm_square_last_process_' . $event_id );
		return is_numeric( $last_process_attempt ) && $last_process_attempt > time() - 60;
	}

	/**
	 * @since x.x
	 *
	 * @param string $event_id
	 * @return void
	 */
	private function count_failed_event( $event_id ) {
		$transient_name = 'frm_square_failed_event_' . $event_id;
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
		switch ( $this->event->type ) {
			case 'payment.created':
				$payment_id   = $this->event->data->id;
				$subscription = FrmSquareLiteConnectHelper::get_subscription_id_for_payment( $payment_id );

				if ( is_object( $subscription ) && isset( $subscription->id ) ) {
					$subscription_id = $subscription->id;
					$this->add_subscription_payment( $subscription_id );
				}
				break;
			case 'payment.updated':
				$payment_id  = $this->event->data->id;
				$frm_payment = new FrmTransLitePayment();
				$payment     = $frm_payment->get_one_by( $payment_id, 'receipt_id' );

				if ( $payment ) {
					$status = $this->event->data->object->payment->status;

					if ( 'COMPLETED' === $status ) {
						// TODO Trigger "Payment complete" actions.
						$frm_payment->update( $payment->id, array( 'status' => 'complete' ) );
					}
					return;
				}
				break;
			case 'subscription.updated':
				$subscription_id = $this->event->data->id;
				$frm_sub         = new FrmTransLiteSubscription();
				$sub             = $frm_sub->get_one_by( $subscription_id, 'sub_id' );

				if ( $sub ) {
					$status = $this->event->data->object->subscription->status;

					// if ( 'DEACTIVATED' === $status ) {
						// TODO: Mark subscription as cancelled.
					// }
				}
				break;
		}//end switch
	}

	/**
	 * Add a payment row for the payments table.
	 *
	 * @param string $subscription_id The Square ID for the current subscription.
	 * @return void
	 */
	private function add_subscription_payment( $subscription_id ) {
		$payment_id = $this->event->data->id;

		$frm_payment = new FrmTransLitePayment();
		$payment     = $frm_payment->get_one_by( $payment_id, 'receipt_id' );

		if ( $payment ) {
			// Avoid adding the same payment twice.
			return;
		}

		$frm_sub = new FrmTransLiteSubscription();
		$sub     = $frm_sub->get_one_by( $subscription_id, 'sub_id' );
		if ( ! $sub ) {
			return;
		}

		$payment_object = $this->event->data->object->payment;

		if ( 'JPY' === $payment_object->amount_money->currency ) {
			// Japanese does not include the additional 2 digits.
			$amount = $payment_object->amount_money->amount;
		} else {
			$amount = floatval( $payment_object->amount_money->amount ) / 100;
		}

		$frm_payment = new FrmTransLitePayment();
		$frm_payment->create(
			array(
				'paysys'     => 'square',
				'amount'     => $amount,
				'status'     => 'authorized',
				'item_id'    => $sub->item_id,
				'action_id'  => $sub->action_id,
				'receipt_id' => $payment_id,
				'sub_id'     => $sub->id,
				'test'       => 'test' === FrmSquareLiteAppHelper::active_mode() ? 1 : 0,
			)
		);
	}
}
