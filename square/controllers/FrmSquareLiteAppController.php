<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteAppController {

	/**
	 * Add the gateway for compatibility with the Payments submodule.
	 * This adds the Stripe checkbox option to the list of gateways.
	 *
	 * @param array $gateways
	 *
	 * @return array
	 */
	public static function add_gateway( $gateways ) {
		$gateways['square'] = array(
			'label'      => 'Square',
			'user_label' => __( 'Payment', 'formidable' ),
			'class'      => 'SquareLite',
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
	 * Handle the request to initialize with Square Api
	 *
	 * @return void
	 */
	public static function handle_oauth() {
		FrmAppHelper::permission_check( 'frm_change_settings' );

		if ( ! check_admin_referer( 'frm_ajax', 'nonce' ) ) {
			wp_send_json_error();
		}

		$redirect_url = FrmSquareLiteConnectHelper::get_oauth_redirect_url();

		if ( false === $redirect_url ) {
			wp_send_json_error( 'Unable to connect to Square successfully' );
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

		FrmSquareLiteConnectHelper::handle_disconnect();
		wp_send_json_success();
	}

	/**
	 * Handle the verify buyer action.
	 *
	 * @return void
	 */
	public static function verify_buyer() {
		check_ajax_referer( 'frm_square_ajax', 'nonce' );

		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

		if ( ! $form_id ) {
			wp_send_json_error( __( 'Invalid form ID', 'formidable' ) );
		}

		$actions = FrmSquareLiteActionsController::get_actions_before_submit( $form_id );

		if ( ! $actions ) {
			wp_send_json_error( __( 'No Square actions found for this form', 'formidable' ) );
		}

		$action               = self::get_action_for_verification( $actions );
		$verification_details = array(
			'amount'         => self::get_amount_value_for_verification( $action ),
			'billingContact' => self::get_billing_contact( $action ),
			'currencyCode'   => strtoupper( $action->post_content['currency'] ),
			'intent'         => 'CHARGE',
		);

		wp_send_json_success(
			array(
				'verificationDetails' => $verification_details,
				'hash'                => md5( serialize( $verification_details ) ),
			)
		);
	}

	/**
	 * Get the action that the submission will actually trigger.
	 *
	 * Square verifies a single amount, so when a form has more than one Square action,
	 * the conditional logic of each action is checked against the posted values. Without
	 * this, the first action always wins and the buyer gets verified for an amount that
	 * a different action is going to charge.
	 *
	 * @since 6.35
	 *
	 * @param array $actions Payment actions from FrmSquareLiteActionsController::get_actions_before_submit. Never empty.
	 *
	 * @return WP_Post
	 */
	private static function get_action_for_verification( $actions ) {
		if ( count( $actions ) > 1 ) {
			$entry = self::generate_false_entry();

			foreach ( $actions as $action ) {
				if ( ! FrmFormAction::action_conditions_met( $action, $entry ) ) {
					// Conditions were met, so this is the action that will charge the buyer.
					return $action;
				}
			}
		}

		// Either there is a single action, or no action passed its conditional logic.
		return reset( $actions );
	}

	/**
	 * Get the amount value for verification.
	 *
	 * Square's verifyBuyer expects the amount as a decimal string in the currency's
	 * major units ("20.00" for twenty pounds), not the smallest denomination that the
	 * Payments API uses. FrmSquareLiteActionsController::prepare_amount returns the
	 * smallest denomination, so the parent is called here instead.
	 *
	 * @param WP_Post $action
	 *
	 * @return string
	 */
	private static function get_amount_value_for_verification( $action ) {
		$amount = $action->post_content['amount'];

		if ( ! str_contains( $amount, '[' ) ) {
			$currency = $action->post_content['currency'];
			return FrmTransLiteActionsController::prepare_amount( $amount, compact( 'currency' ) );
		}

		$form = FrmForm::getOne( $action->menu_order );

		if ( ! $form ) {
			return $amount;
		}

		// Update amount based on field shortcodes.
		$entry = self::generate_false_entry();

		return FrmTransLiteActionsController::prepare_amount( $amount, compact( 'form', 'entry', 'action' ) );
	}

	/**
	 * Show a warning in the payment action settings when the selected address field
	 * uses an address type without a country, as Square requires a country code.
	 *
	 * @since 6.34
	 *
	 * @param object $action
	 *
	 * @return void
	 */
	public static function maybe_show_address_type_warning( $action ) {
		if ( is_callable( 'FrmProSquareLiteController::maybe_show_address_type_warning' ) ) {
			// Pro renders this warning with the same hook.
			return;
		}

		if ( empty( $action->post_content['gateway'] ) ) {
			return;
		}

		$gateways = (array) $action->post_content['gateway'];

		if ( ! in_array( 'square', $gateways, true ) ) {
			return;
		}

		if ( empty( $action->post_content['billing_address'] ) ) {
			return;
		}

		$address_field = FrmField::getOne( $action->post_content['billing_address'] );

		if ( ! $address_field || self::address_field_is_compatible_with_square( $address_field ) ) {
			return;
		}
		?>
		<div class="frm_warning_style">
		<?php
		esc_html_e( 'The address field selected is not compatible with Square, because it does not include the country code. Select another address type to prevent checkout errors.', 'formidable' ); // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		?>
		</div>
		<?php
	}

	/**
	 * Square requires a country code, which the generic address type does not collect.
	 *
	 * @since 6.34
	 *
	 * @param stdClass $field
	 *
	 * @return bool
	 */
	private static function address_field_is_compatible_with_square( $field ) {
		return ! isset( $field->field_options['address_type'] ) || 'generic' !== $field->field_options['address_type'];
	}

	/**
	 * @param WP_Post $action
	 *
	 * @return array
	 */
	public static function get_billing_contact( $action ) {
		$email_setting      = $action->post_content['email'];
		$first_name_setting = $action->post_content['billing_first_name'];
		$last_name_setting  = $action->post_content['billing_last_name'];

		// @phpstan-ignore-next-line
		$address_setting = $action->post_content['billing_address'] ?? '';

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

		$details = array(
			'givenName'  => $first_name,
			'familyName' => $last_name,
		);

		if ( $email_setting ) {
			$shortcode_atts   = array(
				'entry' => $entry,
				'form'  => $action->menu_order,
				'value' => $email_setting,
			);
			$details['email'] = FrmTransLiteAppHelper::process_shortcodes( $shortcode_atts );
		}

		self::maybe_add_address_data( $details, $address, (int) $address_setting );

		return $details;
	}

	/**
	 * @since 6.25
	 *
	 * @param array $details
	 * @param array $address
	 * @param int   $address_field_id
	 *
	 * @return void
	 */
	private static function maybe_add_address_data( &$details, $address, $address_field_id ) {
		if ( ! is_array( $address ) || ! isset( $address['line1'] ) || ! isset( $address['line2'] ) ) {
			return;
		}

		$address_field = FrmField::getOne( $address_field_id );

		if ( ! $address_field ) {
			return;
		}

		if ( 'us' === $address_field->field_options['address_type'] ) {
			$country_code = 'US';
		} else {
			$country_code = FrmAddressesController::get_country_code( $address['country'] );
		}

		if ( ! $address['line1'] && ! $address['line2'] && ! $address['city'] && ! $address['state'] && ! $address['zip'] && ! $country_code ) {
			return;
		}

		$details['addressLines'] = array( $address['line1'], $address['line2'] );
		$details['city']         = $address['city'];
		$details['state']        = $address['state'];
		$details['postalCode']   = $address['zip'];
		$details['countryCode']  = $country_code;
	}

	/**
	 * Create an entry object with posted values.
	 *
	 * @since 6.22
	 *
	 * @return stdClass
	 */
	private static function generate_false_entry() {
		$entry           = new stdClass();
		$entry->post_id  = 0;
		$entry->id       = 0;
		$entry->item_key = '';
		// Shortcode replacement reads ip off of the entry, so it cannot be left unset.
		$entry->ip    = '';
		$entry->metas = array();

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
}
