<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * This file exists for backward compatibility with the Payments Submodule.
 * Without these functions the Authorize.Net add on will trigger fatal errors.
 */
class FrmStrpLiteApiHelper {

	/**
	 * The payments submodule calls this function.
	 * This function exists so payments can be refunded when Authorize.Net is active.
	 *
	 * @param int $payment_id
	 * @return bool
	 */
	public static function refund_payment( $payment_id ) {
		if ( ! class_exists( 'FrmStrpLiteConnectApiAdapter' ) ) {
			require __DIR__ . '/FrmStrpLiteConnectApiAdapter.php';
		}
		return FrmStrpLiteConnectApiAdapter::refund_payment( $payment_id );
	}

	/**
	 * The payments submodule calls this function.
	 * This function exists so subscriptions can be cancelled when Authorize.Net is active.
	 *
	 * @param string $sub_id
	 * @return bool
	 */
	public static function cancel_subscription( $sub_id ) {
		if ( ! class_exists( 'FrmStrpLiteConnectApiAdapter' ) ) {
			require __DIR__ . '/FrmStrpLiteConnectApiAdapter.php';
		}
		return FrmStrpLiteConnectApiAdapter::cancel_subscription( $sub_id );
	}
}
