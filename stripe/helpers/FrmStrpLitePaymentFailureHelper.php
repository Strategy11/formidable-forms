<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Clean up local records after a verified Stripe payment failure.
 *
 * @since x.x
 */
class FrmStrpLitePaymentFailureHelper {

	/**
	 * Returns intent object by calling 'get_intent' endpoint, or false if the request failed, the intent
	 * wasn't found, or the posted value's client secret doesn't match the one Stripe has on the intent.
	 *
	 * @since x.x
	 *
	 * @param string $intent The full posted intent value, including the client secret.
	 *
	 * @return false|object
	 */
	private static function get_intent_object( $intent ) {
		$intent_id     = FrmStrpLiteFormIntentHelper::get_intent_id( $intent );
		$prefix        = explode( '_', $intent_id )[0];
		$method        = 'seti' === $prefix ? 'get_setup_intent' : 'get_intent';
		$intent_object = FrmStrpLiteAppHelper::call_stripe_helper_class( $method, $intent_id );

		if ( ! is_object( $intent_object ) || $intent_object->client_secret !== $intent ) {
			return false;
		}

		return $intent_object;
	}

	/**
	 * Marks failed payment rows as failed and delete entries linked with a payment.
	 *
	 * @since x.x
	 *
	 * @param string     $intent_id      The failed intent id.
	 * @param array|null $failed_payments Payment rows already loaded for this intent.
	 *
	 * @return void
	 */
	private static function process_failed_payments( $intent_id, $failed_payments = null ) {
		$frm_payment = new FrmTransLitePayment();

		if ( null === $failed_payments ) {
			$failed_payments = $frm_payment->get_all_by( $intent_id, 'receipt_id' );
		}

		$status = 'failed';

		foreach ( $failed_payments as $payment ) {
			if ( $status !== $payment->status ) {
				$frm_payment->update( $payment->id, array( 'status' => $status ) );
				FrmTransLiteActionsController::trigger_payment_status_change( compact( 'status', 'payment' ) );
			}

			FrmEntry::destroy( $payment->item_id );
		}
	}

	/**
	 * Verify a reported failure only when local payment records need cleanup.
	 *
	 * @since x.x
	 *
	 * @param string $intent The reported intent's client secret.
	 *
	 * @return void
	 */
	public static function cleanup_failed_intent( $intent ) {
		if ( ! is_string( $intent ) || ! str_contains( $intent, '_secret_' ) ) {
			return;
		}

		$intent_id       = FrmStrpLiteFormIntentHelper::get_intent_id( $intent );
		$frm_payment     = new FrmTransLitePayment();
		$failed_payments = $frm_payment->get_all_by( $intent_id, 'receipt_id' );

		if ( ! $failed_payments ) {
			// No local records need cleanup, so there is no reason to ask Stripe.
			return;
		}

		$intent_object = self::get_intent_object( $intent );

		if ( ! self::payment_or_setup_has_error( $intent_object ) ) {
			return;
		}

		self::process_failed_payments( $intent_object->id, $failed_payments );
	}

	/**
	 * Returns if the payment or subscription with the provided intent object has error.
	 *
	 * @since x.x
	 *
	 * @param false|object $intent_object
	 *
	 * @return bool
	 */
	private static function payment_or_setup_has_error( $intent_object ) {
		if ( ! $intent_object || ! in_array( $intent_object->status, array( 'requires_payment_method', 'canceled' ), true ) ) {
			return false;
		}

		return ! empty( $intent_object->last_payment_error ) || ! empty( $intent_object->last_setup_error ) || 'canceled' === $intent_object->status;
	}
}
