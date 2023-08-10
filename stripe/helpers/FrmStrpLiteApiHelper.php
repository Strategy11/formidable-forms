<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

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
			require dirname( __FILE__ ) . '/FrmStrpLiteConnectApiAdapter.php';
		}
		return FrmStrpLiteConnectApiAdapter::refund_payment( $payment_id );
	}

	/**
	 * @param string $sub_id
	 * @return bool
	 */
	public static function cancel_subscription( $sub_id ) {
		if ( ! class_exists( 'FrmStrpLiteConnectApiAdapter' ) ) {
			require dirname( __FILE__ ) . '/FrmStrpLiteConnectApiAdapter.php';
		}
		return FrmStrpLiteConnectApiAdapter::cancel_subscription( $sub_id );
	}
}
