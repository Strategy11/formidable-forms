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
	 */
	public static function create_order() {
		check_ajax_referer( 'frm_paypal_ajax', 'nonce' );

		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

		if ( ! $form_id ) {
			wp_send_json_error( __( 'Invalid form ID', 'formidable' ) );
		}

		$actions = FrmPayPalLiteActionsController::get_actions_before_submit( $form_id );

		if ( ! $actions ) {
			wp_send_json_error( __( 'No PayPal actions found for this form', 'formidable' ) );
		}

		$action = reset( $actions );
		$amount = self::get_amount_value_for_verification( $action );

		// PayPal expects the amount in a format like 10.00, so format it.
		$amount         = number_format( floatval( $amount ), 2, '.', '' );
		$currency       = strtoupper( $action->post_content['currency'] );
		$order_response = FrmPayPalLiteConnectHelper::create_order( $amount, $currency );

		if ( false === $order_response ) {
			wp_send_json_error( 'Failed to create PayPal order' );
		}

		if ( ! isset( $order_response->order_id ) ) {
			wp_send_json_error( 'Failed to create PayPal order' );
		}

		wp_send_json_success( array( 'orderID' => $order_response->order_id ) );
	}

	/**
	 * Get the amount value for verification.
	 *
	 * @param WP_Post $action
	 *
	 * @return string
	 */
	private static function get_amount_value_for_verification( $action ) {
		$amount = $action->post_content['amount'];

		if ( ! str_contains( $amount, '[' ) ) {
			return $amount;
		}

		$form = FrmForm::getOne( $action->menu_order );

		if ( ! $form ) {
			return $amount;
		}

		// Update amount based on field shortcodes.
		$entry = self::generate_false_entry();

		return FrmPayPalLiteActionsController::prepare_amount( $amount, compact( 'form', 'entry', 'action' ) );
	}

	/**
	 * Create an entry object with posted values.
	 *
	 * @since x.x
	 *
	 * @return stdClass
	 */
	private static function generate_false_entry() {
		$entry           = new stdClass();
		$entry->post_id  = 0;
		$entry->id       = 0;
		$entry->item_key = '';
		$entry->metas    = array();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		foreach ( $_POST as $k => $v ) {
			$k = sanitize_text_field( stripslashes( $k ) );
			$v = wp_unslash( $v );

			if ( $k === 'item_meta' ) {
				if ( is_array( $v ) ) {
					foreach ( $v as $f => $value ) {
						FrmAppHelper::sanitize_value( 'wp_kses_post', $value );
						$entry->metas[ absint( $f ) ] = $value;
					}
				}
			} else {
				FrmAppHelper::sanitize_value( 'wp_kses_post', $v );
				$entry->{$k} = $v;
			}
		}

		return $entry;
	}

	/**
	 * Create a PayPal subscription object via AJAX.
	 *
	 * @return void
	 */
	public static function create_subscription() {
		check_ajax_referer( 'frm_paypal_ajax', 'nonce' );

		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

		if ( ! $form_id ) {
			wp_send_json_error( __( 'Invalid form ID', 'formidable' ) );
		}

		$actions = FrmPayPalLiteActionsController::get_actions_before_submit( $form_id );

		if ( ! $actions ) {
			wp_send_json_error( __( 'No PayPal actions found for this form', 'formidable' ) );
		}

		$action = reset( $actions );
		$amount = self::get_amount_value_for_verification( $action );

		// PayPal expects the amount in a format like 10.00, so format it.
		$amount   = number_format( floatval( $amount ), 2, '.', '' );
		$currency = strtoupper( $action->post_content['currency'] );

		// Pass $product_name, $interval and $interval_count to the helper
		// As well as trial period and the maximum number of payments.
		// Also send subscriber email.
		// TODO Process the description.
		// TODO Do we want a new Product Name setting?
		$product_name   = $action->post_content['description'] ?? '';
		$interval       = $action->post_content['interval'] ?? '';
		$interval_count = $action->post_content['interval_count'] ?? 1;
		$trial_period   = $action->post_content['trial_period'] ?? '';
		$payment_limit  = $action->post_content['payment_limit'] ?? '';

		// TODO Process email properly.
		$email = $action->post_content['email'] ?? '';

		$data = array(
			'amount'         => $amount,
			'currency'       => $currency,
			'product_name'   => $product_name,
			'interval'       => $interval,
			'interval_count' => $interval_count,
			'trial_period'   => $trial_period,
			'payment_limit'  => $payment_limit,
			'email'          => $email,
		);

		$response = FrmPayPalLiteConnectHelper::create_subscription( $data );

		if ( false === $response ) {
			wp_send_json_error( 'Failed to create PayPal subscription' );
		}

		if ( ! isset( $response->subscription_id ) ) {
			wp_send_json_error( 'Failed to create PayPal subscription' );
		}

		wp_send_json_success( array( 'subscriptionID' => $response->subscription_id ) );
	}
}
