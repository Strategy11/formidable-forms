<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteAuth {

	/**
	 * All of the form IDs with payment details in the URL params will be included in this array.
	 *
	 * @var array
	 */
	private static $form_ids = array();

	/**
	 * If returning from Stripe to authorize a payment, show the message.
	 * This is used for 3D secure and for Stripe link.
	 *
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @param string $html Form HTML that gets filtered through frm_filter_final_form.
	 * @return string
	 */
	public static function maybe_show_message( $html ) {
		$link_error = FrmAppHelper::simple_get( 'frm_link_error' );
		if ( $link_error ) {
			$message = '<div class="frm_error_style">' . self::get_message_for_stripe_link_code( $link_error ) . '</div>';
			self::insert_error_message( $message, $html );
			return $html;
		}

		$form_id = self::check_html_for_form_id_match( $html );
		if ( false === $form_id ) {
			return $html;
		}

		$details = FrmStrpLiteUrlParamHelper::get_details_for_form( $form_id );
		if ( ! is_array( $details ) ) {
			return $html;
		}

		$atts = array(
			'fields' => FrmFieldsHelper::get_form_fields( $form_id ),
			'entry'  => $details['entry'],
		);
		self::prepare_success_atts( $atts );

		$intent  = $details['intent'];
		$payment = $details['payment'];

		if ( self::intent_has_failed_status( $intent ) ) {
			$message = '<div class="frm_error_style">' . $intent->last_payment_error->message . '</div>';
			self::insert_error_message( $message, $html );
			return $html;
		}

		$intent_is_processing = 'processing' === $intent->status;
		if ( $intent_is_processing ) {
			// Append an additional processing message to the end of the success message.
			$filter = function ( $message ) {
				$stripe_settings = FrmStrpLiteAppHelper::get_settings();
				$message        .= '<p>' . esc_html( $stripe_settings->settings->processing_message ) . '</p>';
				return $message;
			};
			add_filter( 'frm_content', $filter );
		}

		ob_start();
		FrmFormsController::run_success_action( $atts );
		$message = ob_get_contents();
		ob_end_clean();

		// Clean up the filter we added above so no other success messages get altered if there are multiple forms.
		if ( $intent_is_processing ) {
			remove_filter( 'frm_content', $filter );
		}

		return $message;
	}

	/**
	 * @param int|string $form_id
	 * @return array|false
	 */
	private static function check_request_params( $form_id ) {
		if ( ! FrmStrpLiteAppHelper::stripe_is_configured() ) {
			return false;
		}

		$details = FrmStrpLiteUrlParamHelper::get_details_for_form( $form_id );
		if ( ! is_array( $details ) ) {
			return false;
		}

		self::$form_ids[] = $form_id;

		return $details;
	}

	/**
	 * The frm_filter_final_form filter only passes form HTML as a string.
	 * To determine which form is being filtered, this function checks for the
	 * hidden form_id input. If there is a match, it returns the matching form id.
	 *
	 * @since 6.5
	 *
	 * @param string $html
	 * @return false|int Matching form id or false if there is no match.
	 */
	private static function check_html_for_form_id_match( $html ) {
		foreach ( self::$form_ids as $form_id ) {
			$substring = '<input type="hidden" name="form_id" value="' . $form_id . '"';
			if ( strpos( $html, $substring ) ) {
				return $form_id;
			}
		}

		return false;
	}

	/**
	 * Translate an error code into a readable message for the front end.
	 * FrmStrpLiteLinkRedirectHelper uses these codes to redirect errors that are then handled in self::maybe_show_message.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
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
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @param array $atts
	 * @return void
	 */
	private static function prepare_success_atts( &$atts ) {
		$atts['form']        = FrmForm::getOne( $atts['entry']->form_id );
		$atts['entry_id']    = $atts['entry']->id;
		$opt                 = 'success_action';
		$atts['conf_method'] = ! empty( $atts['form']->options[ $opt ] ) ? $atts['form']->options[ $opt ] : 'message';
	}

	/**
	 * Insert a message/error where the form styling will be applied.
	 *
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 */
	private static function insert_error_message( $message, &$form ) {
		$add_after = '<fieldset>';
		$pos       = strpos( $form, $add_after );
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
	 * @since 6.5, introduced in v2.02 of the Stripe add on.
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
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function get_payment_intents( $name ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST[ $name ] ) ) {
			return array();
		}
		$intents = $_POST[ $name ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $intents );
		return $intents;
	}

	/**
	 * Update pricing before authorizing.
	 *
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @return void
	 */
	public static function update_intent_ajax() {
		check_ajax_referer( 'frm_strp_ajax', 'nonce' );

		if ( empty( $_POST['form'] ) ) {
			wp_die();
		}

		$form = json_decode( stripslashes( $_POST['form'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_array( $form ) ) {
			wp_die();
		}

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
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 * @param int   $form_id
	 * @param array $intents
	 * @return void
	 */
	private static function update_intent_pricing( $form_id, &$intents ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
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
			$intent_id       = explode( '_secret_', $intent )[0];
			$is_setup_intent = 0 === strpos( $intent_id, 'seti_' );
			if ( $is_setup_intent ) {
				continue;
			}

			$saved = FrmStrpLiteAppHelper::call_stripe_helper_class( 'get_intent', $intent_id );
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
			}//end foreach
		}//end foreach
	}

	/**
	 * Create an entry object with posted values.
	 *
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 * @return stdClass
	 */
	private static function generate_false_entry() {
		$entry          = new stdClass();
		$entry->post_id = 0;
		$entry->id      = 0;
		$entry->metas   = array();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
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
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
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
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @param int|string $form_id
	 * @return array
	 */
	private static function maybe_create_intents( $form_id ) {
		$intents = array();

		$details = self::check_request_params( $form_id );
		if ( is_array( $details ) ) {
			$payment        = $details['payment'];
			$intent         = $details['intent'];
			$payment_failed = self::payment_failed( $payment, $intent );

			// Exit early if the request params are set.
			// This way an extra payment intent isn't created for Stripe Link.
			if ( ! $payment_failed ) {
				return $intents;
			}
		}

		if ( ! FrmStrpLiteAppHelper::call_stripe_helper_class( 'initialize_api' ) ) {
			// Stripe is not configured, so don't create intents.
			return $intents;
		}

		$actions = FrmStrpLiteActionsController::get_actions_before_submit( $form_id );
		self::add_amount_to_actions( $form_id, $actions );

		foreach ( $actions as $action ) {
			if ( is_array( $details ) && self::intent_has_failed_status( $details['intent'] ) ) {
				$intents[] = array(
					'id'     => $details['intent']->client_secret,
					'action' => $action->ID,
				);
				continue;
			}

			$intent = self::create_intent( $action );
			if ( ! is_object( $intent ) ) {
				// A non-object is a string error message.
				// The error gets logged to results.log so we can just skip it.
				// Reasons it could fail is because a payment method type was specified that will not work.
				// A payment method type may not work because of a currency conflict, or because it isn't enabled.
				// Or the payment method type could be an incorrect value.
				// When using Stripe Connect, the error will just say "Unable to create intent".
				// In this case, you can find the full error message in the Stripe dashboard.
				continue;
			}

			$intents[] = array(
				'id'     => $intent->client_secret,
				'action' => $action->ID,
			);
		}//end foreach

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
			// Create the intent when the form loads.
			$amount = 100;
		}

		if ( 'recurring' === $action->post_content['type'] ) {
			$payment_method_types = FrmStrpLitePaymentTypeHandler::get_payment_method_types( $action );
			return self::create_setup_intent( $payment_method_types );
		}

		$new_charge = array(
			'amount'   => $amount,
			'currency' => $action->post_content['currency'],
			'metadata' => array( 'action' => $action->ID ),
		);

		if ( FrmStrpLitePaymentTypeHandler::should_use_automatic_payment_methods( $action ) ) {
			$new_charge['automatic_payment_methods'] = array( 'enabled' => true );
		} else {
			$payment_method_types               = FrmStrpLitePaymentTypeHandler::get_payment_method_types( $action );
			$new_charge['payment_method_types'] = $payment_method_types;
		}

		return FrmStrpLiteAppHelper::call_stripe_helper_class( 'create_intent', $new_charge );
	}

	/**
	 * Create a customer and an associated setup intent for a recurring Stripe link payment.
	 *
	 * @since 6.5, introduced in v3.0 of the Stripe add on.
	 *
	 * @param array $payment_method_types
	 * @return false|object
	 */
	private static function create_setup_intent( $payment_method_types ) {
		$payment_info = array(
			'user_id' => FrmTransLiteAppHelper::get_user_id_for_current_payment(),
		);

		// We need to add a customer to support subscriptions with link.
		$customer = FrmStrpLiteAppHelper::call_stripe_helper_class( 'get_customer', $payment_info );
		if ( ! is_object( $customer ) ) {
			return false;
		}

		return FrmStrpLiteAppHelper::call_stripe_helper_class( 'create_setup_intent', $customer->id, $payment_method_types );
	}

	/**
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @param int|string $form_id
	 * @param array      $actions
	 * @return void
	 */
	private static function add_amount_to_actions( $form_id, &$actions ) {
		if ( empty( $actions ) ) {
			return;
		}
		$form = FrmForm::getOne( $form_id );

		foreach ( $actions as $k => $action ) {
			$amount                                = self::get_amount_before_submit( compact( 'action', 'form' ) );
			$actions[ $k ]->post_content['amount'] = $amount;
		}
	}

	/**
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @param array $atts
	 * @return string
	 */
	private static function get_amount_before_submit( $atts ) {
		$amount = $atts['action']->post_content['amount'];
		return FrmStrpLiteActionsController::prepare_amount( $atts['action']->post_content['amount'], $atts );
	}

	/**
	 * Get the URL to return to after a payment is complete.
	 * This may either use the success URL on redirect, or the message on success.
	 * It shouldn't be confused for the Stripe link return URL. It isn't used for that. That uses the frmstrplinkreturn AJAX action instead.
	 *
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
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
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @param array $atts {
	 *     The form and entry details.
	 *
	 *     @type stdClass $form
	 *     @type stdClass $entry
	 * }
	 * @return string
	 */
	private static function get_redirect_url( $atts ) {
		$actions = FrmFormsController::get_met_on_submit_actions( $atts );
		if ( $actions ) {
			$success_url = reset( $actions )->post_content['success_url'];
		}

		if ( empty( $success_url ) ) {
			$success_url = $atts['form']->options['success_url'];
		}

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
	 * @since 6.5, introduced in v2.0 of the Stripe add on.
	 *
	 * @param array $atts
	 */
	private static function get_message_url( $atts ) {
		$url = self::get_referer_url( $atts['entry_id'], false );
		if ( false === $url ) {
			$url = FrmAppHelper::get_server_value( 'HTTP_REFERER' );
		}
		return add_query_arg( array( 'frmstrp' => $atts['entry_id'] ), $url );
	}

	/**
	 * @since 6.5
	 *
	 * @param int|string $entry_id
	 * @param bool       $delete_meta
	 * @return false|string
	 */
	public static function get_referer_url( $entry_id, $delete_meta = true ) {
		$row = FrmDb::get_row(
			'frm_item_metas',
			array(
				'field_id'        => 0,
				'item_id'         => $entry_id,
				'meta_value LIKE' => '{"referer":',
			),
			'id, meta_value'
		);
		if ( ! $row ) {
			return false;
		}

		$meta = $row->meta_value;
		$meta = json_decode( $meta, true );

		if ( ! is_array( $meta ) || empty( $meta['referer'] ) ) {
			return false;
		}

		self::delete_temporary_referer_meta( (int) $row->id );
		return $meta['referer'];
	}

	/**
	 * Delete the referer meta as we'll no longer need it.
	 *
	 * @param int $row_id
	 * @return void
	 */
	private static function delete_temporary_referer_meta( $row_id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'frm_item_metas', array( 'id' => $row_id ) );
	}

	/**
	 * Check if a payment or setup intent has failed.
	 *
	 * @since 6.5.1
	 *
	 * @param object $intent
	 * @return bool
	 */
	private static function intent_has_failed_status( $intent ) {
		return in_array( $intent->status, array( 'requires_source', 'requires_payment_method', 'canceled' ), true );
	}

	/**
	 * Check if a payment failed.
	 *
	 * @since 6.8
	 *
	 * @param object $payment
	 * @param object $intent
	 * @return bool
	 */
	public static function payment_failed( $payment, $intent ) {
		if ( self::intent_has_failed_status( $intent ) ) {
			return true;
		}

		// The $intent will be "succeeded" with a failed payment when testing with the 4000000000000341 credit card.
		if ( 'payment_failed' === FrmAppHelper::simple_get( 'frm_link_error' ) && 'failed' === $payment->status ) {
			return true;
		}

		return false;
	}
}
