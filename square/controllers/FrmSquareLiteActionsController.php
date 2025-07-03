<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteActionsController extends FrmTransLiteActionsController {

	/**
	 * @since 6.22
	 *
	 * @param string             $callback
	 * @param array|false|object $field
	 * @return string
	 */
	public static function maybe_show_card( $callback, $field = false ) {
		if ( false === $field ) {
			// Pro isn't up to date.
			return $callback;
		}

		$form_id = is_object( $field ) ? $field->form_id : $field['form_id'];
		$actions = self::get_actions_before_submit( $form_id );
		if ( empty( $actions ) ) {
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
	 * Override the credit card field HTML if there is a Square action.
	 *
	 * @since 6.22
	 *
	 * @param array  $field
	 * @param string $field_name
	 * @param array  $atts
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
	 * Get all published payment actions with the Square gateway that have an amount set.
	 *
	 * @since 6.22
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
	 * @param mixed    $form
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
	 * @param array $atts The arguments for the payment.
	 * @return string|true string on error, true on success.
	 */
	private static function trigger_one_time_payment( $atts ) {
		if ( empty( $_POST['square-token'] ) || empty( $_POST['square-verification-token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return __( 'Please enter a valid credit card', 'formidable' );
		}

		$currency           = strtoupper( $atts['action']->post_content['currency'] );
		$square_token       = sanitize_text_field( $_POST['square-token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$verification_token = sanitize_text_field( $_POST['square-verification-token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$description        = FrmTransLiteAppHelper::process_shortcodes(
			array(
				'entry' => $atts['entry'],
				'form'  => $atts['entry']->form_id,
				'value' => $atts['action']->post_content['description'],
			)
		);
		$result             = FrmSquareLiteConnectHelper::create_payment( $atts['amount'], $currency, $square_token, $verification_token, $description );

		if ( false === $result ) {
			return FrmSquareLiteConnectHelper::get_latest_error_from_square_api();
		}

		$atts['status']         = $result->status === 'COMPLETED' ? 'complete' : 'failed';
		$atts['charge']         = new stdClass();
		$atts['charge']->id     = $result->id;
		$atts['charge']->amount = $atts['amount'];

		$payment_id  = self::create_new_payment( $atts );
		$frm_payment = new FrmTransLitePayment();
		$payment     = $frm_payment->get_one( $payment_id );
		$status      = $atts['status'];

		FrmTransLiteActionsController::trigger_payment_status_change( compact( 'status', 'payment' ) );

		return true;
	}

	/**
	 * Add a payment row for the payments table.
	 *
	 * @param array $atts The arguments for the payment.
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
		if ( empty( $_POST['square-token'] ) || empty( $_POST['square-verification-token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return __( 'Please enter a valid credit card', 'formidable' );
		}

		$currency           = strtoupper( $atts['action']->post_content['currency'] );
		$square_token       = sanitize_text_field( $_POST['square-token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$verification_token = sanitize_text_field( $_POST['square-verification-token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// We can put this all behind our API.
		// It will require that we pass the customer info and the catalog info.
		// 1. Call the API with the customer and catalog info.
		// 2. Add the database rows.

		$action          = $atts['action'];
		$billing_contact = FrmSquareLiteAppController::get_billing_contact( $action );

		$info = array(
			'payment'  => array(
				'token'             => $square_token,
				'verificationToken' => $verification_token,
			),
			'customer' => array(
				'givenName'    => $billing_contact['givenName'],
				'familyName'   => $billing_contact['familyName'],
				'emailAddress' => $billing_contact['email'],
			),
			'catalog'  => array(
				'name'      => self::prepare_subscription_description( $action->post_content['description'], $atts ),
				'trialDays' => $action->post_content['trial_interval_count'],
				'limit'     => $action->post_content['payment_limit'],
				'amount'    => $atts['amount'],
				'currency'  => $currency,
				'cadence'   => $action->post_content['repeat_cadence'] ?? 'DAILY',
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

		$response = FrmSquareLiteConnectHelper::create_subscription( $info );
		if ( false === $response ) {
			return FrmSquareLiteConnectHelper::get_latest_error_from_square_api();
		}

		if ( is_string( $response ) ) {
			return $response;
		}

		if ( ! is_object( $response ) || ! isset( $response->id ) ) {
			return __( 'There was a problem creating the subscription', 'formidable' );
		}

		// Add subscription database row.
		// We do not add a payment row at this time. This is handled with our webhook handling.
		$subscription_id = self::create_new_subscription( $response->id, $atts );

		return true;
	}

	/**
	 * Prepare the description for a subscription.
	 *
	 * @param string $description
	 * @param array  $atts
	 * @return string
	 */
	private static function prepare_subscription_description( $description, $atts ) {
		$shortcode_atts = array(
			'entry' => $atts['entry'],
			'form'  => $atts['entry']->form_id,
			'value' => $description,
		);
		return FrmTransLiteAppHelper::process_shortcodes( $shortcode_atts );
	}

	/**
	 * Create a new subscription and payment for the payments tables.
	 *
	 * @param string $subscription_id
	 * @param array  $atts
	 * @return int
	 */
	private static function create_new_subscription( $subscription_id, $atts ) {
		$repeat_cadence = $atts['action']->post_content['repeat_cadence'] ?? 'DAILY';
		$interval_count = self::get_interval_count_from_repeat_cadence( $repeat_cadence );
		$interval       = self::get_interval_from_repeat_cadence( $repeat_cadence );

		$new_values = array(
			'amount'         => FrmTransLiteAppHelper::get_formatted_amount_for_currency( $atts['amount'], $atts['action'] ),
			'paysys'         => 'square',
			'item_id'        => $atts['entry']->id,
			'action_id'      => $atts['action']->ID,
			'sub_id'         => $subscription_id,
			'interval_count' => $interval_count,
			'time_interval'  => $interval,
			'status'         => 'active',
			'next_bill_date' => gmdate( 'Y-m-d' ),
			'test'           => 'test' === FrmSquareLiteAppHelper::active_mode() ? 1 : 0,
		);

		$frm_payment = new FrmTransLiteSubscription();
		$payment_id  = $frm_payment->create( $new_values );

		return $payment_id;
	}

	/**
	 * @param string $repeat_cadence
	 * @return int
	 */
	private static function get_interval_count_from_repeat_cadence( $repeat_cadence ) {
		switch ( $repeat_cadence ) {
			case 'NINETY_DAYS':
				return 90;
			case 'SIXTY_DAYS':
				return 60;
			case 'THIRTY_DAYS':
				return 30;
			case 'EVERY_SIX_MONTHS':
				return 6;
			case 'EVERY_FOUR_MONTHS':
				return 4;
			case 'QUARTERLY':
				return 3;
			case 'EVERY_TWO_WEEKS':
			case 'EVERY_TWO_MONTHS':
			case 'EVERY_TWO_YEARS':
				return 2;
			case 'DAILY':
			case 'ANNUAL':
			case 'MONTHLY':
			case 'WEEKLY':
			default:
				return 1;
		}//end switch
	}

	/**
	 * @param string $repeat_cadence
	 * @return string
	 */
	private static function get_interval_from_repeat_cadence( $repeat_cadence ) {
		switch ( $repeat_cadence ) {
			case 'ANNUAL':
			case 'EVERY_TWO_YEARS':
				return 'year';

			case 'MONTHLY':
			case 'EVERY_TWO_MONTHS':
			case 'QUARTERLY':
			case 'EVERY_FOUR_MONTHS':
			case 'EVERY_SIX_MONTHS':
				return 'month';

			case 'WEEKLY':
			case 'EVERY_TWO_WEEKS':
				return 'week';

			case 'DAILY':
			case 'THIRTY_DAYS':
			case 'SIXTY_DAYS':
			case 'NINETY_DAYS':
			default:
				return 'day';
		}//end switch
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

		$payment_actions = self::get_actions_before_submit( $form->id );
		if ( ! $payment_actions ) {
			return;
		}

		$found_gateway = false;
		foreach ( $payment_actions as $action ) {
			$gateways = $action->post_content['gateway'];
			if ( in_array( 'square', (array) $gateways, true ) ) {
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
			_doing_it_wrong( __METHOD__, '$form_id parameter must be a non-zero integer', '6.22' );
			return;
		}

		$action_settings = self::prepare_settings_for_js( $form_id );
		$found_gateway   = false;
		foreach ( $action_settings as $action ) {
			$gateways = $action['gateways'];
			if ( ! $gateways || in_array( 'square', (array) $gateways, true ) ) {
				$found_gateway = true;
				break;
			}
		}

		if ( ! $found_gateway ) {
			return;
		}

		wp_register_script(
			'square',
			FrmSquareLiteAppHelper::active_mode() === 'live' ? 'https://web.squarecdn.com/v1/square.js' : 'https://sandbox.web.squarecdn.com/v1/square.js',
			array(),
			'1.0',
			false
		);

		$dependencies = array( 'square', 'formidable' );
		$script_url   = FrmSquareLiteAppHelper::plugin_url() . 'js/frontend.js';

		wp_enqueue_script(
			'formidable-square',
			$script_url,
			$dependencies,
			FrmAppHelper::plugin_version(),
			false
		);

		$square_vars     = array(
			'formId'     => $form_id,
			'nonce'      => wp_create_nonce( 'frm_square_ajax' ),
			'ajax'       => esc_url_raw( FrmAppHelper::get_ajax_url() ),
			'settings'   => $action_settings,
			'appId'      => self::get_app_id(),
			'locationId' => self::get_location_id(),
			'style'      => self::get_style( $form_id ),
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
		return FrmSquareLiteConnectHelper::get_location_id();
	}

	/**
	 * @param int $form_id
	 * @return array
	 */
	private static function get_style( $form_id ) {
		$settings = self::get_style_settings_for_form( $form_id );
		$style    = array(
			'input'                     => array(
				'fontSize'        => $settings['field_font_size'],
				'color'           => $settings['text_color'],
				'backgroundColor' => $settings['bg_color'],
				'fontWeight'      => $settings['field_weight'],
			),
			// How does input placeholder work??
			'input::placeholder'        => array(
				'color' => $settings['text_color_disabled'],
			),
			'.input-container'          => array(
				'borderRadius' => self::get_border_radius( $settings ),
			),
			'.input-container.is-focus' => array(
				'borderColor' => $settings['border_color_active'],
			),
		);

		if ( ! empty( $settings['font'] ) ) {
			$style['input']['fontFamily'] = $settings['font'];
		}

		/**
		 * @since 6.22
		 *
		 * @param array $style
		 * @param array $settings
		 * @param int   $form_id
		 */
		return apply_filters( 'frm_square_style', $style, $settings, $form_id );
	}

	/**
	 * Get the border radius for Stripe elements.
	 *
	 * @since 6.22
	 *
	 * @param array $settings
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
	 * Get and format the style settings for JavaScript to use with the get_style function.
	 *
	 * @since 6.22
	 *
	 * @param int $form_id
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
	 * If the names are being used on the CC fields,
	 * make sure it doesn't prevent the submission if Stripe has approved.
	 *
	 * @since 6.22
	 *
	 * @param array    $errors
	 * @param stdClass $field
	 * @param array    $values
	 * @return array
	 */
	public static function remove_cc_validation( $errors, $field, $values ) {
		$has_processed = ! empty( $_POST['square-token'] ) && ! empty( $_POST['square-verification-token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
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

	/**
	 * @return void
	 */
	public static function actions_js() {
		wp_enqueue_script(
			'frm_square_admin',
			FrmSquareLiteAppHelper::plugin_url() . 'js/action.js',
			array( 'wp-hooks', 'wp-i18n' ),
			FrmAppHelper::plugin_version()
		);
	}
}
