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

			if ( is_object( $this->event ) ) {
				$this->handle_event();
				$this->track_handled_event( $event_id );
				FrmPayPalLiteConnectHelper::process_event( $event_id );
			} else {
				$this->count_failed_event( $event_id );
			}
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

		if ( ! is_array( $option ) ) {
			return false;
		}

		return in_array( $event_id, $option, true );
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
		$transient_name = 'frm_paypal_failed_event_' . $event_id;
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
	 * @return void
	 */
	private function handle_event() {

	}

}
