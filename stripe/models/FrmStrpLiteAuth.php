<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteAuth {

	/**
	 * If returning from Stripe to authorize a payment, show the message.
	 * This is used for 3D secure and for Stripe link.
	 *
	 * @since 2.0
	 *
	 * @param string $html Form HTML that gets filtered through frm_filter_final_form.
	 * @return string
	 */
	public static function maybe_show_message( $html ) {
		$intent_id       = FrmAppHelper::simple_get( 'payment_intent' );
		$is_setup_intent = false;

		if ( ! $intent_id ) {
			$intent_id = FrmAppHelper::simple_get( 'setup_intent' );
			if ( ! $intent_id ) {
				return $html;
			}

			$is_setup_intent = true;
		}

		$link_error = FrmAppHelper::simple_get( 'frm_link_error' );
		if ( $link_error ) {
			$message = '<div class="frm_error_style">' . self::get_message_for_stripe_link_code( $link_error ) . '</div>';
			self::insert_error_message( $message, $html );
			return $html;
		}

		$entry_id = FrmAppHelper::simple_get( 'frmstrp', 'absint', 0 );
		if ( ! $entry_id ) {
			return $html;
		}

		$charge_id  = FrmAppHelper::simple_get( 'charge' );
		$has_charge = (bool) $charge_id;

		$atts = array( 'entry' => FrmEntry::getOne( $entry_id ) );
		self::prepare_success_atts( $atts );
		$is_stripe_link = false !== FrmStrpLiteActionsController::get_stripe_link_action( $atts['form']->id );

		$frm_payment = new FrmTransLitePayment();

		if ( $has_charge ) {
			// Stripe link payments use charge id.
			$payment = $frm_payment->get_one_by( $charge_id, 'receipt_id' );
		} else {
			// 3D secure payments use intent id.
			$payment = $frm_payment->get_one_by( $intent_id, 'receipt_id' );
		}

		if ( ! $payment ) {
			return $html;
		}

		if ( $entry_id !== (int) $payment->item_id || ! FrmStrpLiteAppHelper::stripe_is_configured() ) {
			return $html;
		}

		$intent_function_name = $is_setup_intent ? 'get_setup_intent' : 'get_intent';
		$intent               = FrmStrpLiteAppHelper::call_stripe_helper_class( $intent_function_name, $intent_id );

		if ( ! $intent || ! self::verify_client_secret( $intent, $is_setup_intent ) ) {
			return $html;
		}

		if ( in_array( $intent->status, array( 'requires_source', 'requires_payment_method', 'canceled' ), true ) ) {
			$message = '<div class="frm_error_style">' . $intent->last_payment_error->message . '</div>';
			self::insert_error_message( $message, $html );
			return $html;
		}

		$atts['fields'] = FrmFieldsHelper::get_form_fields( $atts['form']->id );

		ob_start();
		FrmFormsController::run_success_action( $atts );
		$message = ob_get_contents();
		ob_end_clean();

		return $message;
	}

	/**
	 * Check the client secret in the URL, verify it matches the Stripe object and isn't being manipulated.
	 *
	 * @since 3.0
	 *
	 * @param object $intent
	 * @param bool   $is_setup_intent
	 * @return bool True if the client secret is set and valid.
	 */
	private static function verify_client_secret( $intent, $is_setup_intent ) {
		$client_secret_param = $is_setup_intent ? 'setup_intent_client_secret' : 'payment_intent_client_secret';
		$client_secret       = FrmAppHelper::simple_get( $client_secret_param );
		return $client_secret && $client_secret === $intent->client_secret;
	}

	/**
	 * Translate an error code into a readable message for the front end.
	 * FrmStrpLiteLinkRedirectHelper uses these codes to redirect errors that are then handled in self::maybe_show_message.
	 *
	 * @since 3.0
	 *
	 * @param string $code
	 * @return string
	 */
	private static function get_message_for_stripe_link_code( $code ) {
		switch ( $code ) {
			case 'intent_does_not_exist':
				return __( 'Payment intent does not exist.', 'formidable' );
			case 'unable_to_verify':
				return __( 'Unable to verify payment intent.', 'formidable' );
			case 'did_not_complete':
				return __( 'Payment did not complete.', 'formidable' );
			case 'no_payment_record':
				return __( 'Unable to find record of payment.', 'formidable' );
			case 'no_entry_found':
				return __( 'This form submission does not exist.', 'formidable' );
			case 'no_stripe_link_action':
				return __( 'This form is not configured for Stripe link payments.', 'formidable' );
			case 'create_subscription_failed':
				return __( 'Something went wrong when trying to create a subscription.', 'formidable' );
			case 'payment_failed':
				return __( 'Payment was not successfully processed.', 'formidable' );
		}
		return '';
	}

	/**
	 * Add the parameters the receiving functions are expecting.
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 * @return void
	 */
	private static function prepare_success_atts( &$atts ) {
		$atts['form']     = FrmForm::getOne( $atts['entry']->form_id );
		$atts['entry_id'] = $atts['entry']->id;

		$opt = 'success_action';
		$atts['conf_method'] = ( isset( $atts['form']->options[ $opt ] ) && ! empty( $atts['form']->options[ $opt ] ) ) ? $atts['form']->options[ $opt ] : 'message';
	}

	/**
	 * Insert a message/error where the form styling will be applied.
	 *
	 * @since 2.0
	 */
	private static function insert_error_message( $message, &$form ) {
		$add_after = '<fieldset>';
		$pos = strpos( $form, $add_after );
		if ( $pos !== false ) {
			$form = substr_replace( $form, $add_after . $message, $pos, strlen( $add_after ) );
		}
	}

	/**
	 * Include the token if going between pages.
	 *
	 * @param object $form The form being submitted.
	 * @return void
	 */
	public static function add_hidden_token_field( $form ) {
		$posted_form = FrmAppHelper::get_param( 'form_id', 0, 'post', 'absint' );
		if ( $posted_form != $form->id || FrmFormsController::just_created_entry( $form->id ) ) {
			// Check to make sure the correct form was submitted.
			// Was an entry already created and the form should be loaded fresh?

			$intents = self::maybe_create_intents( $form->id );
			self::include_intents_in_form( $intents, $form );

			return;
		}

		$intents = self::get_payment_intents( 'frmintent' . $form->id );
		if ( ! empty( $intents ) ) {
			self::update_intent_pricing( $form->id, $intents );
		} else {
			$intents = self::maybe_create_intents( $form->id );
		}

		self::include_intents_in_form( $intents, $form );
	}

	/**
	 * Include hidden fields with payment intent IDs in the form.
	 *
	 * @since 2.02
	 *
	 * @param array    $intents
	 * @param stdClass $form
	 * @return void
	 */
	private static function include_intents_in_form( $intents, $form ) {
		foreach ( $intents as $intent ) {
			if ( is_array( $intent ) ) {
				$id     = $intent['id'];
				$action = $intent['action'];
			} else {
				$id     = $intent;
				$action = '';
			}

			echo '<input type="hidden" name="frmintent' . esc_attr( $form->id ) . '[]" value="' . esc_attr( $id ) . '" data-action="' . esc_attr( $action ) . '" />';
		}
	}

	/**
	 * Check POST data for payment intents.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function get_payment_intents( $name ) {
		if ( ! isset( $_POST[ $name ] ) ) {
			return array();
		}
		$intents = $_POST[ $name ];
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $intents );
		return $intents;
	}

	/**
	 * Update pricing before authorizing.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function update_intent_ajax() {
		check_ajax_referer( 'frm_strp_ajax', 'nonce' );
		$form = json_decode( stripslashes( $_POST['form'] ), true );
		self::format_form_data( $form );

		$form_id = absint( $form['form_id'] );
		$intents = isset( $form[ 'frmintent' . $form_id ] ) ? $form[ 'frmintent' . $form_id ] : array();

		if ( empty( $intents ) ) {
			wp_die();
		}

		if ( ! is_array( $intents ) ) {
			$intents = array( $intents );
		} else {
			foreach ( $intents as $k => $intent ) {
				if ( is_array( $intent ) && isset( $intent[ $k ] ) ) {
					$intents[ $k ] = $intent[ $k ];
				}
			}
		}

		$_POST = $form;
		self::update_intent_pricing( $form_id, $intents );

		wp_die();
	}

	/**
	 * Update pricing on page turn and non-ajax validation.
	 *
	 * @since 2.0
	 * @param int   $form_id
	 * @param array $intents
	 * @return void
	 */
	private static function update_intent_pricing( $form_id, &$intents ) {
		if ( ! isset( $_POST['form_id'] ) || absint( $_POST['form_id'] ) != $form_id ) {
			return;
		}

		$actions = FrmStrpLiteActionsController::get_actions_before_submit( $form_id );
		if ( empty( $actions ) || empty( $intents ) ) {
			return;
		}

		$form = FrmForm::getOne( $form_id );

		try {
			if ( ! FrmStrpLiteAppHelper::call_stripe_helper_class( 'initialize_api' ) ) {
				return;
			}
		} catch ( Exception $e ) {
			// Intent was not created.
			return;
		}

		foreach ( $intents as $k => $intent ) {
			$intent_id = explode( '_secret_', $intent )[0];
			$saved     = FrmStrpLiteAppHelper::call_stripe_helper_class( 'get_intent', $intent_id );
			foreach ( $actions as $action ) {
				if ( $saved->metadata->action != $action->ID ) {
					continue;
				}
				$intents[ $k ] = array(
					'id'     => $intent,
					'action' => $action->ID,
				);

				$amount = $action->post_content['amount'];
				if ( strpos( $amount, '[' ) === false ) {
					// The amount is static, so it doesn't need an update.
					continue;
				}

				// Update amount based on field shortcodes.
				$entry  = self::generate_false_entry();
				$amount = FrmStrpLiteActionsController::prepare_amount( $amount, compact( 'form', 'entry', 'action' ) );
				if ( $saved->amount == $amount || $amount == '000' ) {
					continue;
				}

				FrmStrpLiteAppHelper::call_stripe_helper_class( 'update_intent', $intent_id, array( 'amount' => $amount ) );
			}
		}
	}

	/**
	 * Create an entry object with posted values.
	 *
	 * @since 2.0
	 * @return stdClass
	 */
	private static function generate_false_entry() {
		$entry          = new stdClass();
		$entry->post_id = 0;
		$entry->id      = 0;
		$entry->metas   = array();
		foreach ( $_POST as $k => $v ) {
			$k = sanitize_text_field( stripslashes( $k ) );
			$v = wp_unslash( $v );

			if ( $k === 'item_meta' ) {
				foreach ( $v as $f => $value ) {
					FrmAppHelper::sanitize_value( 'wp_kses_post', $value );
					$entry->metas[ absint( $f ) ] = $value;
				}
			} else {
				FrmAppHelper::sanitize_value( 'wp_kses_post', $v );
				$entry->{$k} = $v;
			}
		}
		return $entry;
	}

	/**
	 * Reformat the form data in name => value array.
	 *
	 * @since 2.0
	 *
	 * @param array $form
	 * @return void
	 */
	private static function format_form_data( &$form ) {
		$formatted = array();

		foreach ( $form as $input ) {
			$key = $input['name'];
			if ( isset( $formatted[ $key ] ) ) {
				if ( is_array( $formatted[ $key ] ) ) {
					$formatted[ $key ][] = $input['value'];
				} else {
					$formatted[ $key ] = array( $formatted[ $key ], $input['value'] );
				}
			} else {
				$formatted[ $key ] = $input['value'];
			}
		}

		parse_str( http_build_query( $formatted ), $form );
	}

	/**
	 * Create intents on form load when required.
	 * This only happens in two cases: For stripe link, and when processing a one-time payment before the entry is created.
	 *
	 * @since 2.0
	 *
	 * @param string|int $form_id
	 * @return array
	 */
	private static function maybe_create_intents( $form_id ) {
		$intents = array();

		if ( ! FrmStrpLiteAppHelper::call_stripe_helper_class( 'initialize_api' ) ) {
			// Stripe is not configured, so don't create intents.
			return $intents;
		}

		$actions = FrmStrpLiteActionsController::get_actions_before_submit( $form_id );
		self::add_amount_to_actions( $form_id, $actions );

		foreach ( $actions as $action ) {
			if ( ! self::requires_payment_intent_on_load( $action ) ) {
				continue;
			}

			$intent = self::create_intent( $action );
			if ( is_object( $intent ) ) {
				$intents[] = array(
					'id'     => $intent->client_secret,
					'action' => $action->ID,
				);
			}
		}

		return $intents;
	}

	/**
	 * Create a payment intent for Stripe link or when processing a payment before the entry is created.
	 *
	 * @since 3.0 This code was moved out of self::maybe_create_intents into a new function.
	 *
	 * @param WP_Post $action
	 * @return mixed
	 */
	private static function create_intent( $action ) {
		$amount = $action->post_content['amount'];
		if ( $amount == '000' ) {
			$amount = 100; // Create the intent when the form loads.
		}

		$new_charge = array(
			'amount'               => $amount,
			'currency'             => $action->post_content['currency'],
			'metadata'             => array( 'action' => $action->ID ),
			'setup_future_usage'   => 'off_session',
			'payment_method_types' => array( 'card' ),
		);

		$use_stripe_link = self::uses_stripe_link( $action );
		if ( $use_stripe_link ) {
			if ( 'recurring' === $action->post_content['type'] ) {
				return self::create_setup_intent();
			}
			$new_charge['payment_method_types'][] = 'link';
		}

		$use_manual_capture = 'authorize' === $action->post_content['capture'] || ! $use_stripe_link;
		if ( $use_manual_capture ) {
			$new_charge['capture_method'] = 'manual'; // Authorize only and capture after submit.
		}

		return FrmStrpLiteAppHelper::call_stripe_helper_class( 'create_intent', $new_charge );
	}

	/**
	 * Create a customer and an associated setup intent for a recurring Stripe link payment.
	 *
	 * @since 3.0
	 *
	 * @return object|false
	 */
	private static function create_setup_intent() {
		$payment_info = array(
			'user_id' => FrmTransLiteAppHelper::get_user_id_for_current_payment(),
		);

		// We need to add a customer to support subscriptions with link.
		$customer = FrmStrpLiteAppHelper::call_stripe_helper_class( 'get_customer', $payment_info );
		if ( ! is_object( $customer ) ) {
			return false;
		}

		return FrmStrpLiteAppHelper::call_stripe_helper_class( 'create_setup_intent', $customer->id );
	}

	/**
	 * @since 2.0
	 *
	 * @param string|int $form_id
	 * @param array      $actions
	 * @return void
	 */
	private static function add_amount_to_actions( $form_id, &$actions ) {
		if ( empty( $actions ) ) {
			return;
		}
		$form = FrmForm::getOne( $form_id );

		foreach ( $actions as $k => $action ) {
			$amount = self::get_amount_before_submit( compact( 'action', 'form' ) );
			$actions[ $k ]->post_content['amount'] = $amount;
		}
	}

	/**
	 * @since 2.0
	 *
	 * @param array $atts
	 * @return string
	 */
	private static function get_amount_before_submit( $atts ) {
		$amount = $atts['action']->post_content['amount'];
		return FrmStrpLiteActionsController::prepare_amount( $atts['action']->post_content['amount'], $atts );
	}

	/**
	 * Returns whether or not a specific action needs to create a payment intent when the form loads.
	 * This is only true when using stripe link or when processing a one-time payment before the entry is created.
	 *
	 * @since 3.0
	 * @todo Continue to handle the other case (process payment before and not recurring) in the Stripe add on.
	 *
	 * @param WP_Post $action
	 * @return bool
	 */
	private static function requires_payment_intent_on_load( $action ) {
		return self::uses_stripe_link( $action );
	}

	/**
	 * Check if an action is set to use a Stripe link. This is based on the "Use previously saved card" toggle in Stripe payment actions.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $action
	 * @return bool
	 */
	private static function uses_stripe_link( $action ) {
		return ! empty( $action->post_content['stripe_link'] );
	}

	/**
	 * Triggered by the frm_redirect_url hook.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function set_redirect_url( $url ) {
		global $frm_strp_redirect_url;
		if ( $frm_strp_redirect_url ) {
			$url = $frm_strp_redirect_url;
		}
		return $url;
	}

	/**
	 * Triggered by the frm_success_filter hook.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function trigger_redirect() {
		return 'redirect';
	}

	/**
	 * Get the URL to return to after a payment is complete.
	 * This may either use the success URL on redirect, or the message on success.
	 * It shouldn't be confused for the Stripe link return URL. It isn't used for that. That uses the frmstrplinkreturn AJAX action instead.
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function return_url( $atts ) {
		$atts = array(
			'entry' => $atts['entry'],
		);
		self::prepare_success_atts( $atts );

		if ( $atts['conf_method'] === 'redirect' ) {
			$redirect = self::get_redirect_url( $atts );
		} else {
			$redirect = self::get_message_url( $atts );
		}

		return $redirect;
	}

	/**
	 * If the form should redirect, get the url to redirect to.
	 *
	 * @since 2.0
	 *
	 * @param array $atts {
	 *     @type stdClass $form
	 *     @type stdClass $entry
	 * }
	 * @return string
	 */
	private static function get_redirect_url( $atts ) {
		$success_url = trim( $atts['form']->options['success_url'] );
		$success_url = apply_filters( 'frm_content', $success_url, $atts['form'], $atts['entry'] );
		$success_url = do_shortcode( $success_url );
		$atts['id']  = $atts['entry']->id;

		add_filter( 'frm_redirect_url', 'FrmEntriesController::prepare_redirect_url' );
		return apply_filters( 'frm_redirect_url', $success_url, $atts['form'], $atts );
	}

	/**
	 * If the form should should a message, apend it to the success url.
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private static function get_message_url( $atts ) {
		$url  = FrmAppHelper::get_server_value( 'HTTP_REFERER' );

		return add_query_arg( array( 'frmstrp' => $atts['entry_id'] ), $url );
	}
}
