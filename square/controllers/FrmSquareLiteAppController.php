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
		if ( empty( $actions ) ) {
			wp_send_json_error( __( 'No Square actions found for this form', 'formidable' ) );
		}

		$action               = reset( $actions );
		$verification_details = array(
			'amount'         => self::get_amount_value_for_verification( $action ),
			'billingContact' => self::get_billing_contact( $action ),
			'currencyCode'   => strtoupper( $action->post_content['currency'] ),
			'intent'         => 'CHARGE',
		);

		wp_send_json_success(
			array(
				'verificationDetails' => $verification_details,
			)
		);
	}

	/**
	 * Get the amount value for verification.
	 *
	 * @param WP_Post $action
	 * @return string
	 */
	private static function get_amount_value_for_verification( $action ) {
		$amount = $action->post_content['amount'];
		if ( strpos( $amount, '[' ) === false ) {
			return $amount;
		}

		$form = FrmForm::getOne( $action->menu_order );
		if ( ! $form ) {
			return $amount;
		}

		// Update amount based on field shortcodes.
		$entry  = self::generate_false_entry();
		$amount = FrmSquareLiteActionsController::prepare_amount( $amount, compact( 'form', 'entry', 'action' ) );

		return $amount;
	}

	/**
	 * @param WP_Post $action
	 * @return array
	 */
	public static function get_billing_contact( $action ) {
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

		if ( is_array( $address ) && isset( $address['line1'] ) && isset( $address['line2'] ) && is_callable( 'FrmProAddressesController::get_country_code' ) ) {
			$details['addressLines'] = array( $address['line1'], $address['line2'] );
			$details['city']         = $address['city'];
			$details['state']        = $address['state'];
			$details['postalCode']   = $address['zip'];
			$details['countryCode']  = FrmProAddressesController::get_country_code( $address['country'] );
		}

		return $details;
	}

	/**
	 * Create an entry object with posted values.
	 *
	 * @since 6.22
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
				if ( is_array( $v ) ) {
					foreach ( $v as $f => $value ) {
						FrmAppHelper::sanitize_value( 'wp_kses_post', $value );
						$entry->metas[ absint( $f ) ] = $value;
					}
				}
			} else {
				FrmAppHelper::sanitize_value( 'wp_kses_post', $v );
				$entry->{$k} = $v;
			}
		}

		return $entry;
	}
}
