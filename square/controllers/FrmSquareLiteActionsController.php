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

		// Use the Pro function when there are no Stripe actions.
		// This is required for other gateways like Authorize.Net.
		if ( ! $actions && is_callable( 'FrmProCreditCardsController::show_in_form' ) ) {
			FrmProCreditCardsController::show_in_form( $field, $field_name, $atts );
		}

		$html_id = $atts['html_id'];
		include FrmStrpLiteAppHelper::plugin_path() . '/views/payments/card-field.php';
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
			$response['error'] = __( 'There was a problem communicating with Stripe. Please try again.', 'formidable' );
			return $response;
		}

		$customer = self::set_customer_with_token( $atts );
		if ( ! is_object( $customer ) ) {
			$response['error'] = $customer;
			return $response;
		}

		$one_time_payment_args = compact( 'customer', 'form', 'entry', 'action', 'amount' );

		// attempt to charge the customer's card
		if ( 'recurring' === $action->post_content['type'] ) {
			$charge = self::trigger_recurring_payment( compact( 'customer', 'entry', 'action', 'amount' ) );
		} else {
			$charge                   = self::trigger_one_time_payment( $one_time_payment_args );
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
	 * Check if Square integration is enabled.
	 *
	 * @return bool true if Square is set up.
	 */
	private static function square_is_configured() {
		// TODO
		return false;
	}

	/**
	 * Set a customer object to $_POST['customer'] to use later.
	 *
	 * @param array $atts
	 * @return object|string
	 */
	private static function set_customer_with_token( $atts ) {
		if ( isset( self::$customer ) ) {
			// It's an object if this isn't the first Stripe action running.
			return self::$customer;
		}

		$payment_info = array(
			'user_id' => FrmTransLiteAppHelper::get_user_id_for_current_payment(),
		);

		if ( ! empty( $atts['action']->post_content['email'] ) ) {
			$payment_info['email'] = apply_filters( 'frm_content', $atts['action']->post_content['email'], $atts['form'], $atts['entry'] );
			$payment_info['email'] = self::replace_email_shortcode( $payment_info['email'] );
		}

		self::add_customer_name( $atts, $payment_info );

		$customer = FrmStrpLiteAppHelper::call_stripe_helper_class( 'get_customer', $payment_info );
		// Set for later use.
		self::$customer = $customer;

		return $customer;
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

		$stripe_connect_is_setup = FrmStrpLiteConnectHelper::stripe_connect_is_setup();
		if ( ! $stripe_connect_is_setup ) {
			return;
		}

		if ( ! $form_id || ! is_int( $form_id ) ) {
			_doing_it_wrong( __METHOD__, '$form_id parameter must be a non-zero integer', '6.5' );
			return;
		}

		$settings    = FrmStrpLiteAppHelper::get_settings();
		$publishable = $settings->get_active_publishable_key();

		wp_register_script(
			'stripe',
			'https://js.stripe.com/v3/',
			array(),
			'3.0',
			false
		);

		$suffix       = FrmAppHelper::js_suffix();
		$dependencies = array( 'stripe', 'formidable' );

		if ( '.min' === $suffix && is_readable( FrmAppHelper::plugin_path() . '/js/frmstrp.min.js' ) ) {
			// Use the combined file if it is available.
			$script_url = FrmAppHelper::plugin_url() . '/js/frmstrp.min.js';
		} else {
			if ( ! $suffix && ! is_readable( FrmStrpLiteAppHelper::plugin_path() . 'js/frmstrp.js' ) ) {
				// The unminified file is not included in releases so force the minified script.
				$suffix = '.min';
			}
			$script_url = FrmStrpLiteAppHelper::plugin_url() . 'js/frmstrp' . $suffix . '.js';
		}

		if ( class_exists( 'FrmProStrpLiteController' ) && ( ! $suffix || ! FrmProAppController::has_combo_js_file() ) ) {
			$dependencies[] = 'formidablepro';
		}

		wp_enqueue_script(
			'formidable-square',
			$script_url,
			$dependencies,
			FrmAppHelper::plugin_version(),
			false
		);

		$action_settings = self::prepare_settings_for_js( $form_id );
		$square_vars     = array(
			'publishable_key' => $publishable,
			'form_id'         => $form_id,
			'nonce'           => wp_create_nonce( 'frm_square_ajax' ),
			'ajax'            => esc_url_raw( FrmAppHelper::get_ajax_url() ),
			'settings'        => $action_settings,
			'locale'          => self::get_locale(),
		);

		wp_localize_script( 'formidable-square', 'frm_square_vars', $square_vars );
	}

	/**
	 * Get the language to use for Stripe elements.
	 *
	 * @since x.x
	 * @return string
	 */
	private static function get_locale() {
		$allowed = array( 'ar', 'da', 'de', 'en', 'es', 'fi', 'fr', 'he', 'it', 'ja', 'nl', 'no', 'pl', 'ru', 'sv', 'zh' );
		$current = get_locale();
		$parts   = explode( '_', $current );
		$part    = strtolower( $parts[0] );
		return in_array( $part, $allowed, true ) ? $part : 'auto';
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