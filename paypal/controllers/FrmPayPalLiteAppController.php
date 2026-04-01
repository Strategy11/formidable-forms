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
	 * Extract pricing data from posted form values.
	 *
	 * @since x.x
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return array Array of products with prices and quantities.
	 */
	private static function get_pricing_data_from_posted_values( $form_id ) {
		$products = array();
		$fields   = FrmField::get_all_for_form( $form_id );

		if ( ! $fields ) {
			return $products;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$posted_data = $_POST['item_meta'] ?? array();

		foreach ( $fields as $field ) {
			if ( ! in_array( $field->type, array( 'product', 'quantity', 'total' ), true ) ) {
				continue;
			}

			$field_id = $field->id;
			$value    = $posted_data[ $field_id ] ?? '';

			if ( empty( $value ) ) {
				continue;
			}

			if ( 'product' === $field->type ) {
				$product_field = FrmFieldFactory::get_field_object( $field );

				if ( method_exists( $product_field, 'get_posted_price' ) ) {
					$price = $product_field->get_posted_price( $value );

					if ( $price ) {
						$products[] = array(
							'name'     => $field->name,
							'price'    => is_array( $price ) ? array_sum( $price ) : $price,
							'quantity' => 1,
							'type'     => 'product',
							'field_id' => $field_id,
						);
					}
				}
			} elseif ( 'quantity' === $field->type ) {
				$quantity = is_numeric( $value ) ? (int) $value : 1;
				// Quantity fields are linked to product fields via product_field setting
				$product_field_ids = FrmField::get_option( $field, 'product_field' );

				if ( $product_field_ids ) {
					// This quantity will be associated with its product field
					// We'll handle the association in the product processing
					$products[] = array(
						'name'     => $field->name,
						'price'    => 0, // Quantity fields don't have price
						'quantity' => $quantity,
						'type'     => 'quantity',
						'product_field_ids' => (array) $product_field_ids,
					);
				}
			}
		}

		// Associate quantity fields with their products
		$final_products = array();
		$product_quantities = array();

		foreach ( $products as $item ) {
			if ( 'quantity' === $item['type'] ) {
				foreach ( $item['product_field_ids'] as $product_field_id ) {
					$product_quantities[ $product_field_id ] = $item['quantity'];
				}
			}
		}

		foreach ( $products as $item ) {
			if ( 'product' === $item['type'] ) {
				$quantity = $product_quantities[ $item['field_id'] ] ?? 1;
				$final_products[] = array(
					'name'     => $item['name'],
					'price'    => $item['price'],
					'quantity' => $quantity,
				);
			}
		}

		return $final_products;
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

		$action              = reset( $actions );
		$amount              = self::get_amount_value_for_verification( $action );
		$payer               = self::get_payer_data_from_posted_values( $action );
		$shipping_preference = self::get_shipping_preference( $action );
		$pricing_data        = self::get_pricing_data_from_posted_values( $form_id );

		// PayPal expects the amount in a format like 10.00, so format it.
		$amount   = number_format( floatval( $amount ), 2, '.', '' );
		$currency = strtoupper( $action->post_content['currency'] );

		$order_response = FrmPayPalLiteConnectHelper::create_order( $amount, $currency, $payment_source, $payer, $shipping_preference, $pricing_data );

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
	 * @param WP_Post $action
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
	 * @since x.x
	 *
	 * @param WP_Post $action
	 *
	 * @return string
	 */
	private static function get_shipping_preference( $action ) {
		$setting = ! empty( $action->post_content['shipping_preference'] ) ? $action->post_content['shipping_preference'] : 'use_paypal_account_data';

		switch ( $setting ) {
			case 'use_address_field_data':
				return 'SET_PROVIDED_ADDRESS';

			case 'no_shipping':
				return 'NO_SHIPPING';

			case 'use_paypal_account_data':
			default:
				return 'GET_FROM_FILE';
		}
	}

	/**
	 * @since x.x
	 *
	 * @param array $payer
	 * @param array $address
	 * @param int   $address_field_id
	 *
	 * @return void
	 */
	private static function maybe_add_address_data( &$payer, $address, $address_field_id ) {
		if ( ! is_array( $address ) || ! isset( $address['line1'] ) || ! is_callable( 'FrmProAddressesController::get_country_code' ) ) {
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

		if ( ! $address['line1'] || ! $address['city'] || ! $address['state'] || ! $address['zip'] || ! $country_code ) {
			return;
		}

		$payer['address'] = array(
			'address_line_1' => $address['line1'],
			'address_line_2' => $address['line2'] ?? '',
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
			'apple_pay',
			'bancontact',
			'blik',
			'eps',
			'giropay',
			'ideal',
			'mybank',
			'p24',
			'sepa',
			'sofort',
			'trustly',
			'venmo',
			'paylater',
			'google_pay',
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
		$product_name   = $action->post_content['product_name'] ?? '';
		$interval       = $action->post_content['interval'] ?? '';
		$interval_count = $action->post_content['interval_count'] ?? 1;
		$trial_period   = $action->post_content['trial_period'] ?? '';
		$payment_limit  = $action->post_content['payment_limit'] ?? '';

		// TODO Process email properly.
		$email = $action->post_content['email'] ?? '';

		$data = array(
			'amount'              => $amount,
			'currency'            => $currency,
			'product_name'        => $product_name,
			'interval'            => $interval,
			'interval_count'      => $interval_count,
			'trial_period'        => $trial_period,
			'payment_limit'       => $payment_limit,
			'email'               => $email,
			'payer'               => self::get_payer_data_from_posted_values( $action ),
			'shipping_preference' => self::get_shipping_preference( $action ),
		);

		$vault_setup_token = FrmAppHelper::get_post_param( 'vault_setup_token', '', 'sanitize_text_field' );

		if ( $vault_setup_token ) {
			$data['vault_setup_token'] = $vault_setup_token;
		}

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

		$payment_source = FrmAppHelper::get_post_param( 'payment_source', 'card', 'sanitize_text_field' );

		$data = array(
			'payment_source' => $payment_source,
		);

		$response = FrmPayPalLiteConnectHelper::create_vault_setup_token( $data );

		if ( false === $response ) {
			wp_send_json_error( 'Failed to create PayPal vault setup token' );
		}

		if ( ! isset( $response->token ) ) {
			wp_send_json_error( 'Failed to create PayPal vault setup token' );
		}

		wp_send_json_success( array( 'token' => $response->token ) );
	}
}
