<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteAppController {

	/**
	 * Add the gateway for compatibility with the Payments submodule.
	 * This adds the Stripe checkbox option to the list of gateways.
	 *
	 * @param array $gateways
	 * @return array
	 */
	public static function add_gateway( $gateways ) {
		$gateways['square'] = array(
			'label'      => 'Square',
			'user_label' => __( 'Payment', 'formidable' ),
			'class'      => 'SquareLite',
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
	 * Handle the request to initialize with Square Api
	 *
	 * @return void
	 */
	public static function handle_oauth() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		if ( ! check_admin_referer( 'frm_ajax', 'nonce' ) ) {
			wp_send_json_error();
		}

		$response_data = array(
			'redirect_url' => FrmSquareLiteConnectHelper::get_oauth_redirect_url(),
		);
		wp_send_json_success( $response_data );
	}
}
