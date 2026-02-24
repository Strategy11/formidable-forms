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
	 * Get the current amount for a PayPal action via AJAX.
	 * Used to update the Pay Later messaging when price fields change.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function get_amount() {
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

		wp_send_json_success( array( 'amount' => $amount ) );
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

		$payment_source = FrmAppHelper::get_post_param( 'payment_source', '', 'sanitize_text_field' );

		if ( ! $payment_source ) {
			wp_send_json_error( __( 'No payment source provided', 'formidable' ) );
		}

		if ( ! in_array( $payment_source, self::get_valid_payment_sources(), true ) ) {
			wp_send_json_error( __( 'Invalid payment source', 'formidable' ) );
		}

		$actions = FrmPayPalLiteActionsController::get_actions_before_submit( $form_id );

		if ( ! $actions ) {
			wp_send_json_error( __( 'No PayPal actions found for this form', 'formidable' ) );
		}

		$action = reset( $actions );
		$amount = self::get_amount_value_for_verification( $action );
		$payer  = self::get_payer_data_from_posted_values( $action );

		// PayPal expects the amount in a format like 10.00, so format it.
		$amount         = number_format( floatval( $amount ), 2, '.', '' );
		$currency       = strtoupper( $action->post_content['currency'] );
		$order_response = FrmPayPalLiteConnectHelper::create_order( $amount, $currency, $payment_source, $payer );

		if ( class_exists( 'FrmLog' ) ) {
			$log = new FrmLog();
			$log->add(
				array(
					'title'   => 'PayPal Order Response',
					'content' => print_r( $order_response, true ),
				)
			);
		}

		if ( false === $order_response ) {
			wp_send_json_error( 'Failed to create PayPal order' );
		}

		if ( ! isset( $order_response->order_id ) ) {
			wp_send_json_error( 'Failed to create PayPal order' );
		}

		wp_send_json_success( array( 'orderID' => $order_response->order_id ) );
	}

	/**
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_payer_data_from_posted_values( $action ) {
		$email_setting      = $action->post_content['email'];
		$first_name_setting = $action->post_content['billing_first_name'];
		$last_name_setting  = $action->post_content['billing_last_name'];
		$address_setting    = $action->post_content['billing_address'];

		$entry      = self::generate_false_entry();
		$first_name = $first_name_setting && isset( $entry->metas[ $first_name_setting ] ) ? $entry->metas[ $first_name_setting ] : '';
		$last_name  = $last_name_setting && isset( $entry->metas[ $last_name_setting ] ) ? $entry->metas[ $last_name_setting ] : '';
		$address    = $address_setting && isset( $entry->metas[ $address_setting ] ) ? $entry->metas[ $address_setting ] : '';

		if ( is_array( $first_name ) && isset( $first_name['first'] ) ) {
			$first_name = $first_name['first'];
		}

		if ( is_array( $last_name ) && isset( $last_name['last'] ) ) {
			$last_name = $last_name['last'];
		}

		$payer = array();

		if ( $first_name && $last_name ) {
			$payer['name'] = array(
				'given_name' => $first_name,
				'surname'    => $last_name,
			);
		}

		if ( $email_setting ) {
			$shortcode_atts         = array(
				'entry' => $entry,
				'form'  => $action->menu_order,
				'value' => $email_setting,
			);
			$payer['email_address'] = FrmTransLiteAppHelper::process_shortcodes( $shortcode_atts );
		}

		self::maybe_add_address_data( $payer, $address, (int) $address_setting );

		return $payer;
	}

	/**
	 * @since 6.25
	 *
	 * @param array $payer
	 * @param array $address
	 * @param int   $address_field_id
	 *
	 * @return void
	 */
	private static function maybe_add_address_data( &$payer, $address, $address_field_id ) {
		if ( ! is_array( $address ) || ! isset( $address['line1'] ) || ! isset( $address['line2'] ) || ! is_callable( 'FrmProAddressesController::get_country_code' ) ) {
			return;
		}

		$address_field = FrmField::getOne( $address_field_id );

		if ( ! $address_field ) {
			return;
		}

		if ( 'us' === $address_field->field_options['address_type'] ) {
			$country_code = 'US';
		} else {
			$country_code = FrmProAddressesController::get_country_code( $address['country'] );
		}

		if ( ! $address['line1'] && ! $address['line2'] && ! $address['city'] && ! $address['state'] && ! $address['zip'] && ! $country_code ) {
			return;
		}

		$payer['address'] = array(
			'address_line_1' => $address['line1'],
			'address_line_2' => $address['line2'],
			'admin_area_2'   => $address['city'],
			'admin_area_1'   => $address['state'],
			'postal_code'    => $address['zip'],
			'country_code'   => $country_code,
		);
	}

	/**
	 * @since x.x
	 *
	 * @return array<string>
	 */
	private static function get_valid_payment_sources() {
		$sources = array(
			'card',
			'paypal',
			'mybank',
			'bancontact',
			'blik',
			'eps',
			'p24',
			'trustly',
			'satispay',
			'sepa',
			'ideal',
		);

		/**
		 * @since x.x
		 *
		 * @param array<string> $sources
		 */
		return apply_filters( 'frm_paypal_valid_payment_sources', $sources );
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

		return number_format( floatval( FrmPayPalLiteActionsController::prepare_amount( $amount, compact( 'form', 'entry', 'action' ) ) ) / 100, 2 );
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

			if ( $k !== 'item_meta' ) {
				FrmAppHelper::sanitize_value( 'wp_kses_post', $v );
				$entry->{$k} = $v;
				continue;
			}

			if ( ! is_array( $v ) ) {
				continue;
			}

			foreach ( $v as $f => $value ) {
				FrmAppHelper::sanitize_value( 'wp_kses_post', $value );
				$entry->metas[ absint( $f ) ] = $value;
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

	public static function create_vault_setup_token() {
		check_ajax_referer( 'frm_paypal_ajax', 'nonce' );

		$response = FrmPayPalLiteConnectHelper::create_vault_setup_token();

		if ( false === $response ) {
			wp_send_json_error( 'Failed to create PayPal vault setup token' );
		}

		if ( ! isset( $response->token ) ) {
			wp_send_json_error( 'Failed to create PayPal vault setup token' );
		}

		wp_send_json_success( array( 'token' => $response->token ) );
	}
}
