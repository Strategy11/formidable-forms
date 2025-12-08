<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteAppController {

	/**
	 * Add the gateway for compatibility with the Payments submodule.
	 * This adds the PayPal checkbox option to the list of gateways.
	 *
	 * @param array $gateways
	 *
	 * @return array
	 */
	public static function add_gateway( $gateways ) {
		$gateways['paypal'] = array(
			'label'      => 'PayPal',
			'user_label' => __( 'Payment', 'formidable' ),
			'class'      => 'PayPalLite',
			'recurring'  => true,
			'include'    => array(
				'billing_first_name',
				'billing_last_name',
				'credit_card',
				'billing_address',
			),
		);
		return $gateways;
	}

	/**
	 * Handle the request to initialize with PayPal Api
	 *
	 * @return void
	 */
	public static function handle_oauth() {
		FrmAppHelper::permission_check( 'frm_change_settings' );

		if ( ! check_admin_referer( 'frm_ajax', 'nonce' ) ) {
			wp_send_json_error();
		}

		$redirect_url = FrmPayPalLiteConnectHelper::get_oauth_redirect_url();

		if ( false === $redirect_url ) {
			wp_send_json_error( 'Unable to connect to PayPal successfully' );
		}

		$response_data = array(
			'redirect_url' => $redirect_url,
		);
		wp_send_json_success( $response_data );
	}

	public static function handle_disconnect() {
		FrmAppHelper::permission_check( 'frm_change_settings' );

		if ( ! check_admin_referer( 'frm_ajax', 'nonce' ) ) {
			wp_send_json_error();
		}

		FrmPayPalLiteConnectHelper::handle_disconnect();
		wp_send_json_success();
	}

	/**
	 * Create a PayPal order via AJAX.
	 *
	 * @return void
	 */
	public static function create_order() {
		$order_id = FrmPayPalLiteConnectHelper::create_order();

		if ( false === $order_id ) {
			wp_send_json_error( 'Failed to create PayPal order' );
		}

		wp_send_json_success( array( 'orderID' => $order_id ) );
	}
}
