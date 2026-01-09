<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteActionsController extends FrmTransLiteActionsController {

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

		$amount = self::prepare_amount( $action->post_content['amount'], $atts );

		if ( ! $amount || $amount === 000 ) {
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

		$action_settings = self::prepare_settings_for_js( $form_id );
		$found_gateway   = false;

		foreach ( $action_settings as $action ) {
			$gateways = $action['gateways'];

			if ( ! $gateways || in_array( 'paypal', (array) $gateways, true ) ) {
				$found_gateway = true;
				break;
			}
		}

		if ( ! $found_gateway ) {
			return;
		}

		$client_id = self::get_client_id();

		// Build the PayPal SDK URL with required parameters.
		$sdk_url = add_query_arg(
			array(
				'client-id'  => $client_id,
				'components' => 'buttons,card-fields',
				// Use capture for one time payments.
				// 'intent'     => 'capture',
				// Subscriptions require vault=true.
				'intent'     => 'subscription',
				'vault'      => 'true',
			),
			'https://www.paypal.com/sdk/js'
		);

		wp_register_script(
			'paypal-sdk',
			$sdk_url,
			array(),
			null,
			false
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
			'clientId' => $client_id,
			'formId'   => $form_id,
			'nonce'    => wp_create_nonce( 'frm_paypal_ajax' ),
			'ajax'     => esc_url_raw( FrmAppHelper::get_ajax_url() ),
			'settings' => $action_settings,
			'style'    => self::get_style_for_js( $form_id ),
		);

		wp_localize_script( 'formidable-paypal', 'frmPayPalVars', $paypal_vars );
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
	public static function remove_cc_validation( $errors, $field, $values ) {
		// TODO
		$has_processed = false;

		if ( ! $has_processed ) {
			return $errors;
		}

		$field_id = $field->temp_id ?? $field->id;

		if ( isset( $errors[ 'field' . $field_id . '-cc' ] ) ) {
			unset( $errors[ 'field' . $field_id . '-cc' ] );
		}

		if ( isset( $errors[ 'field' . $field_id ] ) ) {
			unset( $errors[ 'field' . $field_id ] );
		}

		return $errors;
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
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_client_id() {
		// TODO: This will need logic for a production client ID as well.
		// This is currently just for testing.
		return 'AV8DLwHFtnUai9Yuy8B5ocRSgtlCBiRAh6Vkl4vhgeuiKRLzilt-vzjd6O1tjIVI_5AiPG0H-HtBssrE';
	}
}
