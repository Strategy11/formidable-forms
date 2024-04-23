<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteActionsController {

	/**
	 * Track the entry IDs we're destroying so we don't attempt to delete an entry more than once.
	 * Set in self::destroy_entry_later.
	 *
	 * @var array
	 */
	private static $entry_ids_to_destroy_later = array();

	/**
	 * Register payment action type.
	 *
	 * @param array $actions
	 * @return array
	 */
	public static function register_actions( $actions ) {
		$actions['payment'] = 'FrmTransLiteAction';
		return $actions;
	}

	/**
	 * Include scripts for handling payments at an administrative level.
	 * This includes handling the after payment settings for Stripe actions.
	 * It also handles refunds and canceling subscriptions.
	 *
	 * @return void
	 */
	public static function actions_js() {
		wp_enqueue_script(
			'frmtrans_admin',
			FrmTransLiteAppHelper::plugin_url() . '/js/frmtrans_admin.js',
			array( 'jquery' ),
			FrmAppHelper::plugin_version()
		);
		wp_localize_script(
			'frmtrans_admin',
			'frm_trans_vars',
			array(
				'nonce' => wp_create_nonce( 'frm_trans_ajax' ),
			)
		);
	}

	/**
	 * Add event types for actions so an email can trigger on a successful payment.
	 *
	 * @param array $triggers
	 * @return array
	 */
	public static function add_payment_trigger( $triggers ) {
		$triggers['payment-success']       = __( 'Successful Payment', 'formidable' );
		$triggers['payment-failed']        = __( 'Failed Payment', 'formidable' );
		$triggers['payment-refunded']      = __( 'Refunded Payment', 'formidable' );
		$triggers['payment-processing']    = __( 'Processing Payment', 'formidable' );
		$triggers['payment-future-cancel'] = __( 'Canceled Subscription', 'formidable' );
		$triggers['payment-canceled']      = __( 'Subscription Canceled and Expired', 'formidable' );
		return $triggers;
	}

	/**
	 * @param array $options
	 * @return array
	 */
	public static function add_trigger_to_action( $options ) {
		$options['event'][] = 'payment-success';
		$options['event'][] = 'payment-failed';
		$options['event'][] = 'payment-processing';
		$options['event'][] = 'payment-future-cancel';
		$options['event'][] = 'payment-canceled';
		$options['event'][] = 'payment-refunded';
		return $options;
	}

	/**
	 * @param WP_Post  $action
	 * @param stdClass $entry
	 * @param mixed    $form
	 * @return void
	 */
	public static function trigger_action( $action, $entry, $form ) {
		self::prepare_description( $action, compact( 'entry', 'form' ) );
		FrmStrpLiteActionsController::trigger_gateway( $action, $entry, $form );
	}

	/**
	 * @param WP_Post  $action
	 * @param stdClass $entry
	 * @param mixed    $form
	 * @return array
	 */
	public static function trigger_gateway( $action, $entry, $form ) {
		// This function must be overridden in a subclass.
		return array(
			'success'      => false,
			'run_triggers' => false,
			'show_errors'  => true,
		);
	}

	/**
	 * @return string
	 */
	public static function force_message_after_create() {
		return 'message';
	}

	/**
	 * @since 6.5, introduced in v1.12 of the Payments submodule.
	 *
	 * @param object $sub
	 * @return void
	 */
	public static function trigger_subscription_status_change( $sub ) {
		$frm_payment = new FrmTransLitePayment();
		$payment     = $frm_payment->get_one_by( $sub->id, 'sub_id' );

		if ( $payment && $payment->action_id ) {
			self::trigger_payment_status_change(
				array(
					'status'  => $sub->status,
					'payment' => $payment,
				)
			);
		}
	}

	/**
	 * @param array $atts
	 * @return void
	 */
	public static function trigger_payment_status_change( $atts ) {
		$entry_id = isset( $atts['entry'] ) ? $atts['entry']->id : $atts['payment']->item_id;
		$atts     = array(
			'trigger'  => $atts['status'],
			'entry_id' => $entry_id,
		);

		if ( ! isset( $atts['payment'] ) ) {
			$frm_payment     = new FrmTransLitePayment();
			$atts['payment'] = $frm_payment->get_one_by( $entry_id, 'item_id' );
		}

		if ( ! isset( $atts['trigger'] ) ) {
			$atts['trigger'] = $atts['status'];
		}

		// Set future-cancel as trigger when applicable.
		$atts['trigger'] = str_replace( '_', '-', $atts['trigger'] );

		if ( $atts['payment'] ) {
			self::trigger_actions_after_payment( $atts['payment'], $atts );
		}
	}

	/**
	 * Maybe trigger payment-success or payment-failed event after payment so actions (like emails) can run.
	 *
	 * @param object $payment
	 * @param array  $atts
	 * @return void
	 */
	public static function trigger_actions_after_payment( $payment, $atts = array() ) {
		if ( 'pending' === $payment->status ) {
			// 3D Secure has a delayed payment status, so avoid sending a payment failed email for a pending payment.
			return;
		}

		$entry = FrmEntry::getOne( $payment->item_id );

		if ( isset( $atts['trigger'] ) ) {
			$trigger_event = 'payment-' . $atts['trigger'];
		} else {
			$trigger_event = 'payment-' . $payment->status;
		}

		$allowed_triggers = array_keys( self::add_payment_trigger( array() ) );
		if ( ! in_array( $trigger_event, $allowed_triggers, true ) ) {
			$trigger_event = $payment->status === 'complete' ? 'payment-success' : 'payment-failed';
		}
		FrmFormActionsController::trigger_actions( $trigger_event, $entry->form_id, $entry->id );
	}

	/**
	 * Filter fields in description.
	 *
	 * @param WP_Post $action
	 * @param array   $atts
	 * @return void
	 */
	public static function prepare_description( &$action, $atts ) {
		$description = $action->post_content['description'];
		if ( ! empty( $description ) ) {
			$atts['value']                       = $description;
			$description                         = FrmTransLiteAppHelper::process_shortcodes( $atts );
			$action->post_content['description'] = $description;
		}
	}

	/**
	 * Convert the amount into 10.00.
	 *
	 * @param mixed $amount
	 * @param array $atts
	 * @return string
	 */
	public static function prepare_amount( $amount, $atts = array() ) {
		if ( isset( $atts['form'] ) ) {
			$atts['value'] = $amount;
			$amount        = FrmTransLiteAppHelper::process_shortcodes( $atts );
		}

		if ( is_string( $amount ) && strlen( $amount ) >= 2 && $amount[0] == '[' && substr( $amount, -1 ) == ']' ) {
			// Make sure we don't use a field id as the amount.
			$amount = 0;
		}

		$currency = self::get_currency_for_action( $atts );

		$total = 0;
		foreach ( (array) $amount as $a ) {
			$this_amount = self::get_amount_from_string( $a );
			self::maybe_use_decimal( $this_amount, $currency );
			self::normalize_number( $this_amount, $currency );

			$total += $this_amount;
			unset( $a, $this_amount );
		}

		return number_format( $total, $currency['decimals'], '.', '' );
	}

	/**
	 * Get currency to use when preparing amount.
	 *
	 * @param array $atts
	 * @return array
	 */
	public static function get_currency_for_action( $atts ) {
		$currency = 'usd';
		if ( isset( $atts['form'] ) ) {
			$currency = $atts['action']->post_content['currency'];
		} elseif ( isset( $atts['currency'] ) ) {
			$currency = $atts['currency'];
		}

		return FrmCurrencyHelper::get_currency( $currency );
	}

	/**
	 * @param string $amount
	 *
	 * @return string
	 */
	private static function get_amount_from_string( $amount ) {
		$amount = html_entity_decode( $amount );
		$amount = trim( $amount );
		preg_match_all( '/[0-9,.]*\.?\,?[0-9]+/', $amount, $matches );
		$amount = $matches ? end( $matches[0] ) : 0;
		return $amount;
	}

	/**
	 * @param string $amount
	 * @param array  $currency
	 * @return void
	 */
	private static function maybe_use_decimal( &$amount, $currency ) {
		if ( $currency['thousand_separator'] !== '.' ) {
			return;
		}

		$amount_parts     = explode( '.', $amount );
		$used_for_decimal = count( $amount_parts ) === 2 && strlen( $amount_parts[1] ) === 2;
		if ( $used_for_decimal ) {
			$amount = str_replace( '.', $currency['decimal_separator'], $amount );
		}
	}

	/**
	 * @param string $amount
	 * @param array  $currency
	 * @return void
	 */
	private static function normalize_number( &$amount, $currency ) {
		$amount = str_replace( $currency['thousand_separator'], '', $amount );
		$amount = str_replace( $currency['decimal_separator'], '.', $amount );
		$amount = number_format( (float) $amount, $currency['decimals'], '.', '' );
	}

	/**
	 * These settings are included in frm_stripe_vars.settings global JavaScript object on Stripe forms.
	 *
	 * @param int $form_id
	 * @return array
	 */
	public static function prepare_settings_for_js( $form_id ) {
		$payment_actions = self::get_actions_for_form( $form_id );
		$action_settings = array();
		foreach ( $payment_actions as $payment_action ) {
			$settings_for_action = array(
				'id'         => $payment_action->ID,
				'first_name' => $payment_action->post_content['billing_first_name'],
				'last_name'  => $payment_action->post_content['billing_last_name'],
				'gateways'   => $payment_action->post_content['gateway'],
				'fields'     => self::get_fields_for_price( $payment_action ),
				'one'        => $payment_action->post_content['type'],
				'email'      => $payment_action->post_content['email'],
			);

			/**
			 * @param array   $settings_for_action
			 * @param WP_Post $payment_action
			 */
			$settings_for_action = apply_filters( 'frm_trans_settings_for_js', $settings_for_action, $payment_action );
			$action_settings[]   = $settings_for_action;
		}

		return $action_settings;
	}

	/**
	 * Include the price field ids to pass to the javascript.
	 *
	 * @since 6.5, introduced in v2.0 of the Payments submodule.
	 *
	 * @param WP_Post $action
	 * @return array|int
	 */
	private static function get_fields_for_price( $action ) {
		$amount     = $action->post_content['amount'];
		$shortcodes = FrmFieldsHelper::get_shortcodes( $amount, $action->menu_order );
		return isset( $shortcodes[2] ) ? $shortcodes[2] : -1;
	}

	/**
	 * Get all published payment actions.
	 *
	 * @param int|string $form_id
	 * @return array
	 */
	public static function get_actions_for_form( $form_id ) {
		$action_status   = array(
			'post_status' => 'publish',
		);
		$payment_actions = FrmFormAction::get_action_for_form( $form_id, 'payment', $action_status );
		if ( ! $payment_actions ) {
			$payment_actions = array();
		}
		return $payment_actions;
	}

	/**
	 * Make sure a gateway field is hidden on the front end.
	 *
	 * @param array    $values
	 * @param stdClass $field
	 * @return array
	 */
	public static function hide_gateway_field_on_front_end( $values, $field ) {
		if ( $field->type !== 'gateway' ) {
			return $values;
		}

		// This is also called from the frm_enqueue_form_scripts hook.
		// With this here, the value of frm_stripe_vars.settings[0].fields is -1
		// This is because the amount value is processed and a shortcode is not found in '000'.
		FrmStrpLiteActionsController::load_scripts( (int) $field->form_id );

		$values['type'] = 'hidden';
		return $values;
	}

	/**
	 * Entries are deleted on payment failure so set the form values after an error from the entry data that gets deleted.
	 *
	 * @since 6.5.1
	 *
	 * @param array    $values
	 * @param stdClass $field
	 * @return array
	 */
	public static function fill_entry_from_previous( $values, $field ) {
		global $frm_vars;
		$previous_entry = isset( $frm_vars['frm_trans']['pay_entry'] ) ? $frm_vars['frm_trans']['pay_entry'] : false;
		if ( empty( $previous_entry ) || $previous_entry->form_id != $field->form_id ) {
			return $values;
		}

		if ( is_array( $previous_entry->metas ) && isset( $previous_entry->metas[ $field->id ] ) ) {
			$values['value'] = $previous_entry->metas[ $field->id ];
		}

		$frm_vars['trans_filled'] = true;

		$previous_entry_id = $previous_entry->id;
		self::destroy_entry_later( $previous_entry_id );

		return $values;
	}

	/**
	 * Destroy an entry, but delay it to happen when the form is displayed.
	 * It needs to happen late enough that FrmProNestedFormsController::display_single_iteration_of_nested_form is able to fill in data for repeater fields.
	 * See Formidable Stripe issue #136 for more information.
	 *
	 * @since 6.5.1
	 *
	 * @param int|string $entry_id
	 * @return void
	 */
	private static function destroy_entry_later( $entry_id ) {
		if ( in_array( (int) $entry_id, self::$entry_ids_to_destroy_later, true ) ) {
			// Avoid trying to delete this multiple times as fill_entry_from_previous is called more than once.
			return;
		}

		$destroy_callback =
			/**
			 * Destroy an entry and remove this action so it only tries to destroy the entry once.
			 *
			 * @param int|string $entry_id
			 * @param Closure    $destroy_callback
			 * @return void
			 */
			function () use ( $entry_id, &$destroy_callback ) {
				FrmEntry::destroy( $entry_id );
				// Only call this once.
				remove_action( 'frm_entry_form', $destroy_callback );
			};
		add_action( 'frm_entry_form', $destroy_callback );

		self::$entry_ids_to_destroy_later[] = (int) $entry_id;
	}
}
