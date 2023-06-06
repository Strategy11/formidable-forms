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
		$link  = '<a href="https://dashboard.stripe.com/payments/' . esc_attr( $receipt ) . '" target="_blank">';
		$link .= esc_html( $receipt );
		$link .= '</a>';
		return $link;
	}
}
