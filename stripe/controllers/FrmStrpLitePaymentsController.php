<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLitePaymentsController {

	/**
	 * Get the receipt link for a Stripe payment.
	 *
	 * @param string $receipt
	 * @return string
	 */
	public static function get_receipt_link( $receipt ) {
		$url = 'https://dashboard.stripe.com/';

		if ( 0 === strpos( $receipt, 'sub_' ) ) {
			$url .= 'subscriptions/';
		} elseif ( 0 === strpos( $receipt, 'seti_' ) ) {
			$url .= 'setup_intents/';
		} else {
			$url .= 'payments/';
		}

		$url .= $receipt;

		$link  = '<a href="' . esc_url( $url ) . '" target="_blank">';
		$link .= esc_html( $receipt );
		$link .= '</a>';
		return $link;
	}
}
