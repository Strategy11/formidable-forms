<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLitePaymentsController {

	/**
	 * Get the receipt link for a Stripe payment.
	 *
	 * @param string $receipt
	 *
	 * @return string
	 */
	public static function get_receipt_link( $receipt ) {
		$url = 'https://dashboard.stripe.com/';

		if ( str_starts_with( $receipt, 'sub_' ) ) {
			$url .= 'subscriptions/';
		} elseif ( str_starts_with( $receipt, 'seti_' ) ) {
			$url .= 'setup_intents/';
		} else {
			$url .= 'payments/';
		}

		$url  .= $receipt;
		$link  = '<a href="' . esc_url( $url ) . '" target="_blank">';
		$link .= esc_html( $receipt );
		return $link . '</a>';
	}
}
