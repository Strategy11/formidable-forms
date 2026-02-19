<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteActionsController extends FrmTransLiteActionsController {

	private static $active_order_id = null;

	/**
	 * @since x.x
	 *
	 * @param string             $callback
	 * @param array|false|object $field
	 *
	 * @return string
	 */
	public static function maybe_show_card( $callback, $field = false ) {
		if ( false === $field ) {
			// Pro isn't up to date.
			return $callback;
		}

		$form_id = is_object( $field ) ? $field->form_id : $field['form_id'];
		$actions = self::get_actions_before_submit( $form_id );

		if ( ! $actions ) {
			return $callback;
		}

		$field_id = is_object( $field ) ? $field->id : $field['id'];

		foreach ( $actions as $action ) {
			if ( (int) $action->post_content['credit_card'] === (int) $field_id ) {
				return self::class . '::show_card';
			}
		}

		return $callback;
	}

	/**
	 * Override the credit card field HTML if there is a PayPal action.
	 *
	 * @since x.x
	 *
	 * @param array  $field
	 * @param string $field_name
	 * @param array  $atts
	 *
	 * @return void
	 */
	public static function show_card( $field, $field_name, $atts ) {
		$actions = self::get_actions_before_submit( $field['form_id'] );

		if ( $actions ) {
			self::load_scripts( (int) $field['form_id'] );

			$html_id = $atts['html_id'];
			include FrmStrpLiteAppHelper::plugin_path() . '/views/payments/card-field.php';
			return;
		}

		// Use the Pro function when there are no Stripe actions.
		// This is required for other gateways like Authorize.Net.
		if ( is_callable( 'FrmProCreditCardsController::show_in_form' ) ) {
			FrmProCreditCardsController::show_in_form( $field, $field_name, $atts );
		}
	}

	/**
	 * Get all published payment actions with the PayPal gateway that have an amount set.
	 *
	 * @since x.x
	 *
	 * @param int|string $form_id
	 *
	 * @return array
	 */
	public static function get_actions_before_submit( $form_id ) {
		$payment_actions = self::get_actions_for_form( $form_id );

		foreach ( $payment_actions as $k => $payment_action ) {
			$gateway   = $payment_action->post_content['gateway'];
			$is_paypal = $gateway === 'paypal' || ( is_array( $gateway ) && in_array( 'paypal', $gateway, true ) );

			if ( ! $is_paypal || empty( $payment_action->post_content['amount'] ) ) {
				unset( $payment_actions[ $k ] );
			}
		}

		return $payment_actions;
	}

	/**
	 * Trigger a PayPal payment after a form is submitted.
	 * This is called for both one time and recurring payments.
	 *
	 * @param WP_Post  $action
	 * @param stdClass $entry
	 * @param mixed    $form
	 *
	 * @return array
	 */
	public static function trigger_gateway( $action, $entry, $form ) {
		$response = array(
			'success'      => false,
			'run_triggers' => false,
			'show_errors'  => true,
		);
		$atts     = compact( 'action', 'entry', 'form' );
		$amount   = self::prepare_amount( $action->post_content['amount'], $atts );

		// phpcs:ignore Universal.Operators.StrictComparisons
		if ( ! $amount || $amount == 000 ) {
			$response['error'] = __( 'Please specify an amount for the payment', 'formidable' );
			return $response;
		}

		if ( ! self::paypal_is_configured() ) {
			$response['error'] = __( 'PayPal still needs to be configured.', 'formidable' );
			return $response;
		}

		$payment_args = compact( 'form', 'entry', 'action', 'amount' );

		// Attempt to charge the customer's card.
		if ( 'recurring' === $action->post_content['type'] ) {
			$charge = self::trigger_recurring_payment( $payment_args );
		} else {
			$charge = self::trigger_one_time_payment( $payment_args );
		}

		if ( $charge === true ) {
			$response['success'] = true;
		} else {
			$response['error'] = $charge;
		}

		$paypal_message = '';
		$email          = false;
		$address        = false;

		if ( ! empty( self::$active_order_id ) ) {
			$order = FrmPayPalLiteConnectHelper::get_order( self::$active_order_id );

			if ( is_object( $order ) && isset( $order->payer ) && is_object( $order->payer ) ) {
				$payer = $order->payer;

				if ( ! empty( $payer->email_address ) ) {
					$email = $payer->email_address;
				}

				if ( ! empty( $payer->address ) && is_object( $payer->address ) ) {
					$address = $payer->address;
				}
			}

			$paypal_message = '';

			if ( isset( $order->payment_source ) && is_object( $order->payment_source ) ) {
				$source_array = (array) $order->payment_source;
				$source_type  = array_key_first( $source_array );

				switch ( $source_type ) {
					case 'paypal':
						$display_type = __( 'PayPal', 'formidable' );
						break;
					case 'credit_card':
						$display_type = __( 'Credit Card', 'formidable' );
						break;
					default:
						$display_type = ucwords( $source_type );
						break;
				}

				$paypal_message .= '<strong>' . __( 'Payment source: ', 'formidable' ) . '</strong>' . $display_type . '<br>';
			}

			if ( $email ) {
				$paypal_message .= '<strong>' . __( 'Payment made by: ', 'formidable' ) . '</strong>' . $email . '<br>';
			}

			if ( $address && ! empty( $address->address_line_1 ) ) {
				$formatted = '<strong>' . __( 'Address: ', 'formidable' ) . '</strong>' . '<br>';

				$formatted .= $address->address_line_1 . '<br>';

				// City, State Zip
				$city_line = '';
				if ( ! empty( $address->admin_area_2 ) ) {
					$city_line .= $address->admin_area_2;
				}
				if ( ! empty( $address->admin_area_1 ) ) {
					$city_line .= $city_line ? ', ' . $address->admin_area_1 : $address->admin_area_1;
				}
				if ( ! empty( $address->postal_code ) ) {
					$city_line .= $city_line ? ' ' . $address->postal_code : $address->postal_code;
				}
				if ( $city_line ) {
					$formatted .= $city_line . '<br>';
				}

				if ( ! empty( $address->country_code ) ) {
					$formatted .= $address->country_code . '<br>';
				}

				$paypal_message .= $formatted;
			}

			/**
			 * Filters the message to show in the main feedback area.
			 *
			 * @since x.x
			 *
			 * @param string   $paypal_message The message to show.
			 * @param stdClass $order          The order object.
			 */
			$paypal_message = apply_filters( 'frm_paypal_message', $paypal_message, $order );

			add_filter(
				'frm_main_feedback',
				function ( $message ) use ( $paypal_message ) {
					if ( $paypal_message ) {
						$details = '<div class="frm_paypal_payment_details" style="margin-top: 10px;">' . $paypal_message . '</div>';
						$message = preg_replace( '/(<div\b[^>]*\bfrm_message\b[^>]*>)(.*?)(<\/div>)/s', '$1$2' . $details . '$3', $message );
					}

					return $message;
				}
			);
		}

		return $response;
	}

	/**
	 * Trigger a one time payment.
	 *
	 * @param array $atts The arguments for the payment.
	 *
	 * @return string|true string on error, true on success.
	 */
	private static function trigger_one_time_payment( $atts ) {
		$paypal_order_id = FrmAppHelper::get_post_param( 'paypal_order_id', '', 'sanitize_text_field' );

		if ( ! $paypal_order_id ) {
			return 'No PayPal order ID found.';
		}

		$response = FrmPayPalLiteConnectHelper::capture_order( $paypal_order_id );

		if ( false === $response ) {
			return 'Failed to confirm order.';
		}

		if ( ! isset( $response->status ) || $response->status !== 'COMPLETED' ) {
			return 'Failed to capture order.';
		}

		$capture_id = self::get_capture_id_from_response( $response );

		// Create a payment record.
		$atts['status']         = 'complete';
		$atts['charge']         = new stdClass();
		$atts['charge']->id     = $capture_id ? $capture_id : $paypal_order_id;
		$atts['charge']->amount = $atts['amount'];

		$payment_id  = self::create_new_payment( $atts );
		$frm_payment = new FrmTransLitePayment();
		$payment     = $frm_payment->get_one( $payment_id );
		$status      = $atts['status'];

		FrmTransLiteActionsController::trigger_payment_status_change( compact( 'status', 'payment' ) );

		/*
		echo '<pre>';
		var_dump( $response );
		echo '</pre>';
		die();
		*/

		self::$active_order_id = $paypal_order_id;

		return true;
	}

	/**
	 * @param object $response
	 *
	 * @return string
	 */
	private static function get_capture_id_from_response( $response ) {
		if ( ! isset( $response->id ) ) {
			return '';
		}

		foreach ( $response->purchase_units as $purchase_unit ) {
			if ( empty( $purchase_unit->payments ) || ! is_object( $purchase_unit->payments ) ) {
				continue;
			}

			$payments = $purchase_unit->payments;

			if ( empty( $payments->captures ) || ! is_array( $payments->captures ) ) {
				continue;
			}

			$captures = $payments->captures;

			foreach ( $captures as $capture ) {
				return $capture->id;
			}
		}

		return '';
	}

	/**
	 * Add a payment row for the payments table.
	 *
	 * @param array $atts The arguments for the payment.
	 *
	 * @return int
	 */
	private static function create_new_payment( $atts ) {
		$atts['charge'] = (object) $atts['charge'];

		$new_values = array(
			'amount'     => FrmTransLiteAppHelper::get_formatted_amount_for_currency( $atts['charge']->amount, $atts['action'] ),
			'status'     => $atts['status'],
			'paysys'     => 'paypal',
			'item_id'    => $atts['entry']->id,
			'action_id'  => $atts['action']->ID,
			'receipt_id' => $atts['charge']->id,
			'sub_id'     => $atts['charge']->sub_id ?? '',
			'test'       => 'test' === FrmPayPalLiteAppHelper::active_mode() ? 1 : 0,
		);

		$frm_payment = new FrmTransLitePayment();
		return $frm_payment->create( $new_values );
	}

	/**
	 * Create a new PayPal subscription and a subscription and payment for the payments tables.
	 *
	 * @param array $atts Includes 'customer', 'entry', 'action', 'amount'.
	 *
	 * @return bool|string True on success, error message on failure
	 */
	private static function trigger_recurring_payment( $atts ) {
		// TODO
		return 'Recurring payments are not yet implemented for PayPal Lite.';
	}

	/**
	 * Check if PayPal integration is enabled.
	 *
	 * @return bool true if PayPal is set up.
	 */
	private static function paypal_is_configured() {
		return (bool) FrmPayPalLiteConnectHelper::get_merchant_id();
	}

	/**
	 * Convert the amount from 10.00 to 1000.
	 *
	 * @param mixed $amount
	 * @param array $atts
	 *
	 * @return string
	 */
	public static function prepare_amount( $amount, $atts = array() ) {
		$amount   = parent::prepare_amount( $amount, $atts );
		$currency = self::get_currency_for_action( $atts );
		return number_format( $amount, $currency['decimals'], '', '' );
	}

	/**
	 * If this form submits with ajax, load the scripts on the first page.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public static function maybe_load_scripts( $params ) {
		// phpcs:ignore Universal.Operators.StrictComparisons
		if ( $params['form_id'] == $params['posted_form_id'] ) {
			// This form has already been posted, so we aren't on the first page.
			return;
		}

		$form = FrmForm::getOne( $params['form_id'] );

		if ( ! $form ) {
			return;
		}

		$credit_card_field = FrmField::getAll(
			array(
				'fi.form_id' => $form->id,
				'type'       => 'credit_card',
			)
		);

		if ( ! $credit_card_field ) {
			return;
		}

		$payment_actions = self::get_actions_before_submit( $form->id );

		if ( ! $payment_actions ) {
			return;
		}

		$found_gateway = false;

		foreach ( $payment_actions as $action ) {
			$gateways = $action->post_content['gateway'];

			if ( in_array( 'paypal', (array) $gateways, true ) ) {
				$found_gateway = true;
				break;
			}
		}

		if ( ! $found_gateway ) {
			return;
		}

		self::load_scripts( (int) $form->id );
	}

	/**
	 * Load front end JavaScript for a PayPal form.
	 *
	 * @param int $form_id
	 *
	 * @return void
	 */
	public static function load_scripts( $form_id ) {
		if ( FrmAppHelper::is_admin_page( 'formidable-entries' ) ) {
			return;
		}

		if ( wp_script_is( 'formidable-paypal', 'enqueued' ) ) {
			return;
		}

		if ( ! $form_id || ! is_int( $form_id ) ) {
			_doing_it_wrong( __METHOD__, '$form_id parameter must be a non-zero integer', 'x.x' );
			return;
		}

		$payment_action_by_id = array();

		add_filter(
			'frm_trans_settings_for_js',
			/**
			 * @param array   $settings_for_action
			 * @param WP_Post $payment_action
			 *
			 * @return array
			 */
			function ( $settings_for_action, $payment_action ) use ( &$payment_action_by_id ) {
				$payment_action_by_id[ $payment_action->ID ] = $payment_action;
				return $settings_for_action;
			},
			10,
			2
		);

		$action_settings      = self::prepare_settings_for_js( $form_id );
		$action_setting_match = false;

		foreach ( $action_settings as $action ) {
			$gateways = $action['gateways'];

			if ( ! $gateways || in_array( 'paypal', (array) $gateways, true ) ) {
				$action_setting_match = $action;
				break;
			}
		}

		if ( false === $action_setting_match || ! array_key_exists( $action_setting_match['id'], $payment_action_by_id ) ) {
			return;
		}

		$action = $payment_action_by_id[ $action_setting_match['id'] ];

		// Use capture for one-time payments and subscription for recurring payments.
		$intent = $action->post_content['type'] === 'single' ? 'capture' : 'subscription';

		/**
		 * Build the PayPal SDK URL with required parameters.
		 *
		 * - Subscriptions require intent=subscription.
		 * - Subscriptions maybe also require vault=true.
		 * - To enable paylater, include enable-funding=paylater.
		 * - To enable Pay Now, include commit=true. This is the default, and what we support in this plugin.
		 * - To use Continue instead, use commit=false
		 */
		$query_args = array(
			'client-id'   => self::get_client_id(),
			'intent'      => $intent,
			'currency'    => strtoupper( $action->post_content['currency'] ?? 'USD' ),
			'merchant-id' => FrmPayPalLiteConnectHelper::get_merchant_id(),
		);

		$components = array(
			'buttons',
			'card-fields',
			'messages',
			// 'payment-fields',
			// 'marks',
		);

		switch( $action->post_content['pay_later'] ?? 'auto' ) {
			case 'off':
				$query_args['disable-funding'] = 'paylater';
				break;
			case 'no-messaging':
				// PayPal throws a  TypeError: can't access property "PAGE_TYPE", trackingDetails is undefined error
				// a lot of the time if you include messages. If you see this error, try using this 'no-messaging' option.
				$components = array_diff( $components, array( 'messages' ) );
				break;
		}

		$query_args['components'] = implode( ',', $components );

		$locale = self::get_paypal_locale();
		if ( $locale ) {
			$query_args['locale'] = str_replace( '-', '_', $locale );
		}

		/**
		 * Allow customization of the PayPal SDK URL query arguments.
		 *
		 * @since x.x
		 *
		 * @param array   $query_args
		 * @param WP_Post $action
		 */
		$query_args = apply_filters( 'frm_paypal_sdk_url_query_args', $query_args, $action );

		$sdk_url = add_query_arg( $query_args, 'https://www.paypal.com/sdk/js' );

		wp_register_script( 'paypal-sdk', $sdk_url, array(), null, false );

		add_filter(
			'script_loader_tag',
			/**
			 * @param string $tag
			 * @param string $handle
			 *
			 * @return string
			 */
			function ( $tag, $handle ) {
				if ( 'paypal-sdk' === $handle ) {
					$tag = str_replace( ' src=', ' data-partner-attribution-id="' . esc_attr( FrmPayPalLiteConnectHelper::get_bn_code() ) . '" src=', $tag );
				}
				return $tag;
			},
			10,
			2
		);

		$dependencies = array( 'paypal-sdk', 'formidable' );
		$script_url   = FrmPayPalLiteAppHelper::plugin_url() . 'js/frontend.js';

		wp_enqueue_script(
			'formidable-paypal',
			$script_url,
			$dependencies,
			FrmAppHelper::plugin_version(),
			false
		);

		$paypal_vars = array(
			'formId'      => $form_id,
			'nonce'       => wp_create_nonce( 'frm_paypal_ajax' ),
			'ajax'        => esc_url_raw( FrmAppHelper::get_ajax_url() ),
			'settings'    => $action_settings,
			'style'       => self::get_style_for_js( $form_id ),
			'buttonStyle' => self::get_button_style_for_js( $action ),
		);

		wp_localize_script( 'formidable-paypal', 'frmPayPalVars', $paypal_vars );
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_paypal_locale() {
		$locale  = str_replace( '_', '-', get_locale() );
		$parts   = explode( '_', $locale );
		$lang    = strtolower( $parts[0] );
		$country = isset( $parts[1] ) ? strtoupper( $parts[1] ) : '';

		switch ( $lang ) {
			case 'ar': // Arabic
				$countries = array( 'DZ', 'BH', 'EG', 'JO', 'KW', 'MA', 'OM', 'QA', 'SA', 'TN', 'AE', 'YE' );
				break;

			case 'bg': // Bulgarian
				$countries = array( 'BG' );
				break;

			case 'cs': // Czech
				$countries = array( 'CZ' );
				break;

			case 'da': // Danish
				$countries = array( 'DK', 'FO', 'GL' );
				break;

			case 'de': // German
				$countries = array( 'AT', 'DE', 'LU', 'CH' );
				break;

			case 'el': // Greek
				$countries = array( 'GR' );
				break;

			case 'en': // English
				$countries = array(
					'AL', 'DZ', 'AD', 'AO', 'AI', 'AG', 'AR', 'AM', 'AW', 'AU', 'AT', 'AZ',
					'BS', 'BH', 'BB', 'BY', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BA', 'BW', 'BR', 'VG', 'BN', 'BG', 'BF', 'BI',
					'KH', 'CM', 'CA', 'CV', 'KY', 'TD', 'CL', 'C2', 'CN', 'CO', 'KM', 'CG', 'CD', 'CK', 'CR', 'CI', 'HR', 'CY', 'CZ',
					'DK', 'DJ', 'DM', 'DO',
					'EC', 'EG', 'SV', 'ER', 'EE', 'SZ', 'ET',
					'FK', 'FO', 'FJ', 'FI', 'FR', 'GF', 'PF',
					'GA', 'GM', 'GE', 'DE', 'GI', 'GR', 'GL', 'GD', 'GP', 'GT', 'GN', 'GW', 'GY',
					'HN', 'HK', 'HU',
					'IS', 'IN', 'ID', 'IE', 'IL', 'IT',
					'JM', 'JP', 'JO',
					'KZ', 'KE', 'KI', 'KW', 'KG',
					'LA', 'LV', 'LS', 'LI', 'LT', 'LU',
					'MG', 'MW', 'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU', 'MX', 'FM', 'MD', 'MC', 'MN', 'ME', 'MS', 'MA', 'MZ',
					'NA', 'NR', 'NP', 'NL', 'AN', 'NC', 'NZ', 'NI', 'NE', 'NG', 'NU', 'NF', 'MK', 'NO',
					'OM',
					'PW', 'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PL', 'PT',
					'QA',
					'RE', 'RO', 'RU', 'RW',
					'WS', 'SM', 'ST', 'SA', 'SN', 'RS', 'SC', 'SL', 'SG', 'SK', 'SI', 'SB', 'SO', 'ZA', 'KR', 'ES', 'LK', 'SH', 'KN', 'LC', 'PM', 'VC', 'SR', 'SJ', 'SE', 'CH',
					'TW', 'TJ', 'TZ', 'TH', 'TG', 'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV',
					'UG', 'UA', 'AE', 'GB', 'US', 'UY',
					'VU', 'VA', 'VE', 'VN',
					'WF',
					'YE',
					'ZM', 'ZW',
				);
				break;

			case 'es': // Spanish
				$countries = array(
					'DZ', 'AD', 'AO', 'AI', 'AG', 'AR', 'AM', 'AW', 'AZ',
					'BS', 'BH', 'BB', 'BZ', 'BJ', 'BM', 'BO', 'BW', 'VG', 'BF', 'BI',
					'CV', 'KY', 'TD', 'CL', 'CO', 'KM', 'CG', 'CD', 'CK', 'CR',
					'DJ', 'DM', 'DO',
					'EC', 'EG', 'SV', 'ER', 'SZ', 'ET',
					'FK', 'FO', 'FJ', 'PF',
					'GA', 'GM', 'GE', 'GI', 'GL', 'GD', 'GT', 'GN', 'GW', 'GY',
					'HN',
					'IE',
					'JM', 'JO',
					'KZ', 'KE', 'KI', 'KW', 'KG',
					'LS', 'LI', 'LU',
					'MG', 'MW', 'ML', 'MH', 'MR', 'MU', 'MX', 'MS', 'MA', 'MZ',
					'NA', 'NR', 'AN', 'NC', 'NZ', 'NI', 'NE', 'NU', 'NF',
					'OM',
					'PW', 'PA', 'PG', 'PY', 'PE', 'PN',
					'QA',
					'RW',
					'SM', 'ST', 'SA', 'SN', 'RS', 'SC', 'SL', 'SB', 'SO', 'ZA', 'ES', 'SH', 'KN', 'LC', 'PM', 'VC', 'SR', 'SJ',
					'TJ', 'TZ', 'TG', 'TT', 'TN', 'TM', 'TC', 'TV',
					'UG', 'UA', 'AE', 'US', 'UY',
					'VU', 'VA', 'VE',
					'WF',
					'YE',
					'ZM',
				);
				break;

			case 'et': // Estonian
				$countries = array( 'EE' );
				break;

			case 'fi': // Finnish
				$countries = array( 'FI' );
				break;

			case 'fr': // French
				$countries = array(
					'DZ', 'AD', 'AO', 'AI', 'AG', 'AM', 'AW', 'AZ',
					'BS', 'BH', 'BB', 'BE', 'BZ', 'BJ', 'BM', 'BO', 'BW', 'VG', 'BF', 'BI',
					'CM', 'CA', 'CV', 'KY', 'TD', 'CL', 'CO', 'KM', 'CG', 'CD', 'CK', 'CR', 'CI',
					'DJ', 'DM', 'DO',
					'EC', 'EG', 'SV', 'ER', 'SZ', 'ET',
					'FK', 'FO', 'FJ', 'FR', 'GF', 'PF',
					'GA', 'GM', 'GE', 'GI', 'GL', 'GD', 'GP', 'GT', 'GN', 'GW', 'GY',
					'HN',
					'IE',
					'JM', 'JO',
					'KZ', 'KE', 'KI', 'KW', 'KG',
					'LS', 'LI', 'LU',
					'MG', 'MW', 'ML', 'MH', 'MQ', 'MR', 'MU', 'YT', 'MC', 'MS', 'MA', 'MZ',
					'NA', 'NR', 'AN', 'NC', 'NZ', 'NI', 'NE', 'NU', 'NF',
					'OM',
					'PW', 'PA', 'PG', 'PE', 'PN',
					'QA',
					'RE', 'RW',
					'SC', 'SM', 'ST', 'SA', 'SN', 'RS', 'SL', 'SB', 'SO', 'ZA', 'SH', 'KN', 'LC', 'PM', 'VC', 'SR', 'SJ', 'CH',
					'TJ', 'TZ', 'TG', 'TT', 'TN', 'TM', 'TC', 'TV',
					'UG', 'UA', 'AE', 'US', 'UY',
					'VU', 'VA', 'VE',
					'WF',
					'YE',
					'ZM',
				);
				break;

			case 'he': // Hebrew
				$countries = array( 'IL' );
				break;

			case 'hu': // Hungarian
				$countries = array( 'HU' );
				break;

			case 'id': // Indonesian
				$countries = array( 'ID' );
				break;

			case 'it': // Italian
				$countries = array( 'IT' );
				break;

			case 'ja': // Japanese
				$countries = array( 'JP' );
				break;

			case 'ko': // Korean
				$countries = array( 'KR' );
				break;

			case 'lt': // Lithuanian
				$countries = array( 'LT' );
				break;

			case 'lv': // Latvian
				$countries = array( 'LV' );
				break;

			case 'ms': // Malay
				$countries = array( 'BN', 'MY' );
				break;

			case 'nl': // Dutch
				$countries = array( 'BE', 'NL' );
				break;

			case 'no': // Norwegian
				$countries = array( 'NO' );
				break;

			case 'pl': // Polish
				$countries = array( 'PL' );
				break;

			case 'pt': // Portuguese
				$countries = array( 'BR', 'PT' );
				break;

			case 'ro': // Romanian
				$countries = array( 'RO' );
				break;

			case 'ru': // Russian
				$countries = array( 'EE', 'LV', 'LT', 'RU', 'UA' );
				break;

			case 'si': // Sinhala
				$countries = array( 'LK' );
				break;

			case 'sk': // Slovak
				$countries = array( 'SK' );
				break;

			case 'sl': // Slovenian
				$countries = array( 'SI' );
				break;

			case 'sq': // Albanian
				$countries = array( 'AL' );
				break;

			case 'sv': // Swedish
				$countries = array( 'SE' );
				break;

			case 'th': // Thai
				$countries = array( 'TH' );
				break;

			case 'tl': // Tagalog
				$countries = array( 'PH' );
				break;

			case 'tr': // Turkish
				$countries = array( 'TR' );
				break;

			case 'vi': // Vietnamese
				$countries = array( 'VN' );
				break;

			case 'zh': // Chinese
				$countries = array(
					'C2', 'CN', 'HK', 'TW',
					'DZ', 'AD', 'AO', 'AI', 'AG', 'AM', 'AW', 'AZ',
					'BS', 'BH', 'BB', 'BZ', 'BJ', 'BM', 'BO', 'BW', 'VG', 'BF', 'BI',
					'CV', 'KY', 'TD', 'CL', 'CO', 'KM', 'CG', 'CD', 'CK', 'CR',
					'DJ', 'DM', 'DO',
					'EC', 'EG', 'SV', 'ER', 'SZ', 'ET',
					'FK', 'FO', 'FJ', 'PF',
					'GA', 'GM', 'GE', 'GI', 'GL', 'GD', 'GT', 'GN', 'GW', 'GY',
					'HN',
					'IE',
					'JM', 'JO',
					'KZ', 'KE', 'KI', 'KW', 'KG',
					'LS', 'LI', 'LT', 'LU',
					'MG', 'MW', 'ML', 'MH', 'MR', 'MU', 'MS', 'MA', 'MZ',
					'NA', 'NR', 'AN', 'NC', 'NZ', 'NI', 'NE', 'NU', 'NF',
					'OM',
					'PW', 'PA', 'PG', 'PE', 'PN',
					'QA',
					'RW',
					'SM', 'ST', 'SA', 'SN', 'RS', 'SC', 'SL', 'SB', 'SO', 'ZA', 'SH', 'KN', 'LC', 'PM', 'VC', 'SR', 'SJ',
					'TJ', 'TZ', 'TG', 'TT', 'TN', 'TM', 'TC', 'TV',
					'UG', 'UA', 'AE', 'US', 'UY',
					'VU', 'VA', 'VE',
					'WF',
					'YE',
					'ZM',
				);
				break;

			default:
				$countries = array();
				break;
		}

		if ( $country && in_array( $country, $countries, true ) ) {
			return $lang . '-' . $country;
		}

		return 'en-US';
	}

	/**
	 * Get the style for the PayPal form.
	 *
	 * @param int $form_id
	 *
	 * @return array
	 */
	public static function get_style_for_js( $form_id ) {
		$settings = self::get_style_settings_for_form( $form_id );

		$style = array(
			'body'               => array(
				'padding' => 0,
			),
			'input'              => array(
				'font-size'     => $settings['field_font_size'],
				'color'         => $settings['text_color'],
				'font-weight'   => $settings['field_weight'],
				'padding'       => $settings['field_pad'],
				'line-height'   => 1.3,
				'border'        => self::get_border_shorthand( $settings ),
				'border-radius' => self::get_border_radius( $settings ),
			),
			'input::placeholder' => array(
				'color' => $settings['text_color_disabled'],
			),
			'.invalid'           => array(
				'color' => $settings['border_color_error'],
			),
		);

		if ( ! empty( $settings['font'] ) ) {
			$style['input']['font-family'] = $settings['font'];
		}

		/**
		 * Filter the PayPal card field styles.
		 *
		 * @since x.x
		 *
		 * @param array $style
		 * @param array $settings
		 * @param int   $form_id
		 */
		return apply_filters( 'frm_paypal_style', $style, $settings, $form_id );
	}

	/**
	 * Get PayPal button style configuration from form action settings.
	 * Documentation at https://developer.paypal.com/sdk/js/reference/#style
	 *
	 * @since x.x
	 *
	 * @param WP_Post $form_action The form action containing button settings.
	 *
	 * @return array The style configuration array for PayPal button.
	 */
	private static function get_button_style_for_js( $form_action ) {
		$button_color         = $form_action->post_content['button_color'] ?? 'default';
		$button_label         = $form_action->post_content['button_label'] ?? 'paypal';
		$button_border_radius = $form_action->post_content['button_border_radius'] ?? 10;

		$style_for_js = array(
			'layout'       => 'vertical',
			'color'        => $button_color,
			'label'        => $button_label,
			'borderRadius' => (int) $button_border_radius,
		);

		// Unset the color so PayPal can use its defaults.
		// Many buttons have different colors
		if ( 'default' === $button_color ) {
			unset( $style_for_js['color'] );
		}

		return $style_for_js;
	}

	/**
	 * Get and format the style settings for JavaScript to use with the get_style function.
	 *
	 * @param int $form_id
	 *
	 * @return array
	 */
	private static function get_style_settings_for_form( $form_id ) {
		if ( ! $form_id ) {
			return array();
		}

		$style = FrmStylesController::get_form_style( $form_id );

		if ( ! $style ) {
			return array();
		}

		$settings   = FrmStylesHelper::get_settings_for_output( $style );
		$disallowed = array( ';', ':', '!important' );

		foreach ( $settings as $k => $s ) {
			if ( is_string( $s ) ) {
				$settings[ $k ] = str_replace( $disallowed, '', $s );
			}
		}

		return $settings;
	}

	/**
	 * Get the border width for PayPal card fields.
	 *
	 * @since x.x
	 *
	 * @param array $settings
	 *
	 * @return string
	 */
	private static function get_border_width( $settings ) {
		if ( ! empty( $settings['field_shape_type'] ) && 'underline' === $settings['field_shape_type'] ) {
			return '0 0 ' . $settings['field_border_width'] . ' 0';
		}
		return $settings['field_border_width'];
	}

	/**
	 * Get the border radius for PayPal card fields.
	 *
	 * @since x.x
	 *
	 * @param array $settings
	 *
	 * @return string
	 */
	private static function get_border_radius( $settings ) {
		if ( ! empty( $settings['field_shape_type'] ) ) {
			switch ( $settings['field_shape_type'] ) {
				case 'underline':
				case 'regular':
					return '0px';
				case 'circle':
					return '30px';
			}
		}
		return $settings['border_radius'];
	}

	/**
	 * Get the border shorthand for PayPal card fields.
	 *
	 * @since x.x
	 *
	 * @param array $settings
	 *
	 * @return string
	 */
	private static function get_border_shorthand( $settings ) {
		$width = self::get_border_width( $settings );
		$style = $settings['field_border_style'];
		$color = $settings['border_color'];

		return "{$width} {$style} {$color}";
	}

	/**
	 * If the names are being used on the CC fields,
	 * make sure it doesn't prevent the submission if PayPal has approved.
	 *
	 * @since x.x
	 *
	 * @param array    $errors
	 * @param stdClass $field
	 * @param array    $values
	 *
	 * @return array
	 */
	public static function remove_cc_validation( $errors, $field ) {
		$paypal_order_id = FrmAppHelper::get_post_param( 'paypal_order_id', '', 'sanitize_text_field' );

		if ( ! $paypal_order_id ) {
			return $errors;
		}

		return FrmTransLiteActionsController::remove_cc_errors( $errors, $field );
	}

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function actions_js() {
		wp_enqueue_script(
			'frm_paypal_admin',
			FrmPayPalLiteAppHelper::plugin_url() . 'js/action.js',
			array( 'wp-hooks', 'wp-i18n' ),
			FrmAppHelper::plugin_version()
		);
	}

	/**
	 * Modify the new action post data to use the payment action type when the PayPal plugin is not active.
	 * This works better than having it disabled even when PayPal is supported.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function maybe_modify_new_action_post_data() {
		$action_type = FrmAppHelper::get_param( 'type', '', 'post', 'sanitize_text_field' );

		if ( ! in_array( $action_type, array( 'paypal', 'stripe', 'square' ), true ) ) {
			return;
		}

		if ( 'paypal' === $action_type && class_exists( 'FrmPaymentsController' ) ) {
			// Do not override the action if the PayPal plugin is active.
			return;
		}

		$_POST['type'] = 'payment';

		add_filter(
			'frm_form_payment_action_settings',
			/**
			 * @param WP_Post $action_settings
			 *
			 * @return WP_Post
			 */
			function ( $action_settings ) use ( $action_type ) {
				return self::set_gateway_as_default( $action_settings, $action_type );
			}
		);
	}

	/**
	 * Set the gateway to PayPal as the default.
	 *
	 * @param WP_Post $action_settings
	 *
	 * @return WP_Post
	 */
	private static function set_gateway_as_default( $action_settings, $action_type ) {
		$action_settings->post_content['gateway'] = array( $action_type );
		return $action_settings;
	}

	/**
	 * Print additional options for Stripe action settings.
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public static function add_action_options( $atts ) {
		$form_action    = $atts['form_action'];
		$action_control = $atts['action_control'];

		include FrmPayPalLiteAppHelper::plugin_path() . '/views/settings/action-settings-options.php';
	}

	/**
	 * Print additional options for button settings.
	 *
	 * @param FrmFormAction $action_control
	 * @param WP_Post       $form_action
	 *
	 * @return void
	 */
	public static function add_button_settings_section( $action_control, $form_action ) {
		include FrmPayPalLiteAppHelper::plugin_path() . '/views/settings/button-settings.php';
	}

	/**
	 * @return string
	 */
	private static function get_client_id() {
		// TODO: This will need logic for a production client ID as well.
		// This is currently just for testing.
		return 'AYTiIIchQiekyGhJouWoLapPfjijirOtKHSN255SLhcP0TIaWBID-zxsYDaNmP4fXL6YcQxiSIMS0Lwu';
	}
}
