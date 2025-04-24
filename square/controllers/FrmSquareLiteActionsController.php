<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteActionsController extends FrmTransLiteActionsController {

	/**
	 * Override the credit card field HTML if there is a Square action.
	 *
	 * @since x.x
	 *
	 * @param array  $field
	 * @param string $field_name
	 * @param array  $atts
	 * @return void
	 */
	public static function show_card( $field, $field_name, $atts ) {
		$actions = self::get_actions_before_submit( $field['form_id'] );

		if ( $actions ) {
			// TODO This likely overwrites Stripe.
			// We'll need to check $actions for a credit card field match.

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
	 * Get all published payment actions with the Stripe gateway that have an amount set.
	 *
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @param int|string $form_id
	 * @return array
	 */
	public static function get_actions_before_submit( $form_id ) {
		$payment_actions = self::get_actions_for_form( $form_id );
		foreach ( $payment_actions as $k => $payment_action ) {
			$gateway   = $payment_action->post_content['gateway'];
			$is_square = $gateway === 'square' || ( is_array( $gateway ) && in_array( 'square', $gateway, true ) );
			if ( ! $is_square || empty( $payment_action->post_content['amount'] ) ) {
				unset( $payment_actions[ $k ] );
			}
		}
		return $payment_actions;
	}

	/**
	 * Trigger a Square payment after a form is submitted.
	 * This is called for both one time and recurring payments.
	 *
	 * @param WP_Post  $action
	 * @param stdClass $entry
	 * @param stdClass $form
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
		if ( empty( $amount ) || $amount == 000 ) {
			$response['error'] = __( 'Please specify an amount for the payment', 'formidable' );
			return $response;
		}

		if ( ! self::square_is_configured() ) {
			$response['error'] = __( 'There was a problem communicating with Square. Please try again.', 'formidable' );
			return $response;
		}

		$payment_args = compact( 'form', 'entry', 'action', 'amount' );

		// Attempt to charge the customer's card.
		if ( 'recurring' === $action->post_content['type'] ) {
			$charge = self::trigger_recurring_payment( $payment_args );
		} else {
			$charge                   = self::trigger_one_time_payment( $payment_args );
			$response['run_triggers'] = true;
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
	 * @param array $atts {
	 *     @type stdClass $form
	 *     @type stdClass $entry
	 *     @type WP_Post  $action
	 *     @type string   $amount
	 * }
	 * @return string|true string on error, true on success.
	 */
	private static function trigger_one_time_payment( $atts ) {
		if ( empty( $_POST['square-token'] ) || empty( $_POST['square-verification-token'] ) ) {
			return __( 'Please enter a valid credit card', 'formidable' );
		}

		$currency           = strtoupper( $atts['action']->post_content['currency'] );
		$square_token       = sanitize_text_field( $_POST['square-token'] );
		$verification_token = sanitize_text_field( $_POST['square-verification-token'] );

		// TODO We'll need to send the square tokens to our API.
		$result = FrmSquareLiteConnectHelper::create_payment( $atts['amount'], $currency, $square_token, $verification_token );

		if ( false === $result ) {
			return FrmSquareLiteConnectHelper::get_latest_error_from_square_api();
		}

		$atts['status'] = $result->status === 'COMPLETED' ? 'complete' : 'failed';

		$atts['charge']         = new stdClass();
		$atts['charge']->id     = $result->id;
		$atts['charge']->amount = $atts['amount'];

		self::create_new_payment( $atts );

		return true;
	}

	/**
	 * Add a payment row for the payments table.
	 *
	 * @param array $atts {
	 *     @type object  $charge
	 *     @type object  $entry
	 *     @type WP_Post $action
	 * }
	 * @return int
	 */
	private static function create_new_payment( $atts ) {
		$atts['charge'] = (object) $atts['charge'];

		$new_values = array(
			'amount'     => FrmTransLiteAppHelper::get_formatted_amount_for_currency( $atts['charge']->amount, $atts['action'] ),
			'status'     => $atts['status'],
			'paysys'     => 'square',
			'item_id'    => $atts['entry']->id,
			'action_id'  => $atts['action']->ID,
			'receipt_id' => $atts['charge']->id,
			'sub_id'     => isset( $atts['charge']->sub_id ) ? $atts['charge']->sub_id : '',
			'test'       => 'test' === FrmSquareLiteAppHelper::active_mode() ? 1 : 0,
		);

		$frm_payment = new FrmTransLitePayment();
		$payment_id  = $frm_payment->create( $new_values );
		return $payment_id;
	}

	/**
	 * Create a new Square subscription and a subscription and payment for the payments tables.
	 *
	 * @param array $atts Includes 'customer', 'entry', 'action', 'amount'.
	 * @return bool|string True on success, error message on failure
	 */
	private static function trigger_recurring_payment( $atts ) {
		// We can put this all behind our API.
		// It will require that we pass the customer info and the catalog info.
		// 1. Call the API with the customer and catalog info.
		// 2. Add the database rows.

		$action          = $atts['action'];;
		$billing_contact = FrmSquareLiteAppController::get_billing_contact( $action );

		$info = array(
			'customer' => array(
				'givenName'    => $billing_contact['givenName'],
				'familyName'   => $billing_contact['familyName'],
				'emailAddress' => $billing_contact['email'],
			),
			'catalog'  => array(
				'name'           => $action->post_content['description'],
				'trial_days'     => $action->post_content['trial_interval_count'],
				'limit'          => $action->post_content['payment_limit'],
				'amount'         => $atts['amount'],
				'interval'       => $action->post_content['interval'],
				'interval_count' => $action->post_content['interval_count'],
			),
		);

		if ( isset( $billing_contact['addressLines'] ) ) {
			$info['customer']['address'] = array(
				'addressLine1'                 => $billing_contact['addressLines'][0],
				'addressLine2'                 => $billing_contact['addressLines'][1],
				'locality'                     => $billing_contact['city'],
				'administrativeDistrictLevel1' => $billing_contact['state'],
				'postalCode'                   => $billing_contact['postalCode'],
				'country'                      => $billing_contact['countryCode'],
			);
		}

		FrmSquareLiteConnectHelper::create_subscription( $info );
	}

	/**
	 * Check if Square integration is enabled.
	 *
	 * @return bool true if Square is set up.
	 */
	private static function square_is_configured() {
		return (bool) FrmSquareLiteConnectHelper::get_merchant_id();
	}

	/**
	 * Replace an [email] shortcode with the current user email.
	 *
	 * @param string $email
	 * @return string
	 */
	private static function replace_email_shortcode( $email ) {
		if ( false === strpos( $email, '[email]' ) ) {
			return $email;
		}

		global $current_user;
		return str_replace(
			'[email]',
			! empty( $current_user->user_email ) ? $current_user->user_email : '',
			$email
		);
	}

	/**
	 * Set the customer name based on the mapped first and last name fields in the Stripe action.
	 *
	 * @since 6.5, introduced in v2.02 of the Stripe add on.
	 *
	 * @param array $atts
	 * @param array $payment_info
	 * @return void
	 */
	private static function add_customer_name( $atts, &$payment_info ) {
		if ( empty( $atts['action']->post_content['billing_first_name'] ) ) {
			return;
		}

		$name = '[' . $atts['action']->post_content['billing_first_name'] . ' show="first"]';
		if ( ! empty( $atts['action']->post_content['billing_last_name'] ) ) {
			$name .= ' [' . $atts['action']->post_content['billing_last_name'] . ' show="last"]';
		}

		$payment_info['name'] = apply_filters( 'frm_content', $name, $atts['form'], $atts['entry'] );
	}

	/**
	 * Convert the amount from 10.00 to 1000.
	 *
	 * @param mixed $amount
	 * @param array $atts
	 * @return string
	 */
	public static function prepare_amount( $amount, $atts = array() ) {
		$amount   = parent::prepare_amount( $amount, $atts );
		$currency = self::get_currency_for_action( $atts );
		return number_format( $amount, $currency['decimals'], '', '' );
	}

	/**
	 * Filter Square action on save.
	 *
	 * @param array $settings
	 * @param array $action
	 * @return array
	 */
	public static function before_save_settings( $settings, $action ) {
		$settings['currency'] = strtolower( $settings['currency'] );
		$form_id              = absint( $action['menu_order'] );

		if ( empty( $settings['credit_card'] ) ) {
			$credit_card_field_id = FrmDb::get_var(
				'frm_fields',
				array(
					'type'    => 'credit_card',
					'form_id' => $form_id,
				)
			);
			if ( ! $credit_card_field_id ) {
				$credit_card_field_id = self::add_a_credit_card_field( $form_id );
			}
			if ( $credit_card_field_id ) {
				$settings['credit_card'] = $credit_card_field_id;
			}
		}

		$gateway_field_id = FrmDb::get_var(
			'frm_fields',
			array(
				'type'    => 'gateway',
				'form_id' => $form_id,
			)
		);
		if ( ! $gateway_field_id ) {
			self::add_a_gateway_field( $form_id );
		}

		return $settings;
	}

	/**
	 * @param int    $form_id
	 * @param string $field_type
	 * @param string $field_name
	 * @return false|int
	 */
	private static function add_a_field( $form_id, $field_type, $field_name ) {
		$new_values         = FrmFieldsHelper::setup_new_vars( $field_type, $form_id );
		$new_values['name'] = $field_name;
		$field_id           = FrmField::create( $new_values );
		return $field_id;
	}

	/**
	 * A credit card field is added automatically if missing before a Stripe action is updated.
	 *
	 * @param int $form_id
	 * @return false|int
	 */
	private static function add_a_credit_card_field( $form_id ) {
		return self::add_a_field( $form_id, 'credit_card', __( 'Payment', 'formidable' ) );
	}

	/**
	 * A gateway field is added automatically for compatibility with the Stripe add on.
	 * The gateway field is not important for the Stripe Lite implementation.
	 *
	 * @param int $form_id
	 * @return false|int
	 */
	private static function add_a_gateway_field( $form_id ) {
		return self::add_a_field( $form_id, 'gateway', __( 'Payment Method', 'formidable' ) );
	}

	/**
	 * If this form submits with ajax, load the scripts on the first page.
	 *
	 * @param array $params
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

		self::load_scripts( (int) $form->id );
	}

	/**
	 * Load front end JavaScript for a Stripe form.
	 *
	 * @param int $form_id
	 * @return void
	 */
	public static function load_scripts( $form_id ) {
		if ( FrmAppHelper::is_admin_page( 'formidable-entries' ) ) {
			return;
		}

		if ( wp_script_is( 'formidable-square', 'enqueued' ) ) {
			return;
		}

		if ( ! $form_id || ! is_int( $form_id ) ) {
			_doing_it_wrong( __METHOD__, '$form_id parameter must be a non-zero integer', 'x.x' );
			return;
		}

		wp_register_script(
			'square',
			// TODO This will need to change for live payments.
			'https://sandbox.web.squarecdn.com/v1/square.js',
			array(),
			'1.0',
			false
		);

		$dependencies = array( 'square', 'formidable' );
		$script_url = FrmSquareLiteAppHelper::plugin_url() . 'js/frontend.js';

		wp_enqueue_script(
			'formidable-square',
			$script_url,
			$dependencies,
			uniqid(),
			false
		);

		$action_settings = self::prepare_settings_for_js( $form_id );
		$square_vars     = array(
			'formId'         => $form_id,
			'nonce'           => wp_create_nonce( 'frm_square_ajax' ),
			'ajax'            => esc_url_raw( FrmAppHelper::get_ajax_url() ),
			'settings'        => $action_settings,
			'appId'           => self::get_app_id(),
			'locationId'      => self::get_location_id(),
		);

		wp_localize_script( 'formidable-square', 'frmSquareVars', $square_vars );
	}

	/**
	 * Get the app ID for the Square app.
	 *
	 * @return string
	 */
	private static function get_app_id() {
		$mode = FrmSquareLiteAppHelper::active_mode();
		if ( 'live' === $mode ) {
			return 'sq0idp-eR4XI1xgNduJAXcBvjemTg';
		}
		return 'sandbox-sq0idb-MXl8ilzmhAgsHWKV9c6ycQ';
	}

	/**
	 * Get the location ID for the Square app.
	 *
	 * @return string
	 */
	private static function get_location_id() {
		$mode = FrmSquareLiteAppHelper::active_mode();
		if ( 'live' === $mode ) {
			return 'L2GZQYSMGEKK0';
		}
		return 'L7Q1NBZ6SSJ79';
	}

	/**
	 * If the names are being used on the CC fields,
	 * make sure it doesn't prevent the submission if Stripe has approved.
	 *
	 * @since x.x
	 *
	 * @param array    $errors
	 * @param stdClass $field
	 * @param array    $values
	 * @return array
	 */
	public static function remove_cc_validation( $errors, $field, $values ) {
		$has_processed = isset( $_POST[ 'frmintent' . $field->form_id ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! $has_processed ) {
			return $errors;
		}

		$field_id = isset( $field->temp_id ) ? $field->temp_id : $field->id;

		if ( isset( $errors[ 'field' . $field_id . '-cc' ] ) ) {
			unset( $errors[ 'field' . $field_id . '-cc' ] );
		}
		if ( isset( $errors[ 'field' . $field_id ] ) ) {
			unset( $errors[ 'field' . $field_id ] );
		}

		return $errors;
	}
}
