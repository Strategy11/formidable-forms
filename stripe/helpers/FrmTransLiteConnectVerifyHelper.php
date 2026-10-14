<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Handles the site verification step shared by the Stripe, Square, and PayPal Connect helpers.
 *
 * Lite sites authenticate with the Connect server using the site url and a uuid instead of a
 * license key. Before the Connect server trusts that pair, it makes an anonymous request back to
 * the site so the site can confirm the uuid is really its own. That request cannot be
 * authenticated, so it is gated three ways: the site must have an onboarding request in progress,
 * the request must arrive within a short window of that request, and it must include the one time
 * token the site generated for it.
 *
 * @since x.x
 */
class FrmTransLiteConnectVerifyHelper {

	/**
	 * Gateways that use this verification step.
	 *
	 * @var array
	 */
	private static $gateways = array( 'stripe', 'square', 'paypal' );

	/**
	 * How long a pending onboarding request stays verifiable, in seconds.
	 *
	 * The Connect server verifies while it handles the onboarding request, so this only needs to
	 * cover a single round trip. Keeping it short limits how long the endpoint answers at all.
	 *
	 * @return int
	 */
	private static function get_request_lifetime() {
		return 15 * MINUTE_IN_SECONDS;
	}

	/**
	 * Get the option name that holds the pending onboarding request for a gateway.
	 *
	 * @param string $gateway 'stripe', 'square', or 'paypal'.
	 *
	 * @return string
	 */
	private static function get_option_name( $gateway ) {
		return 'frm_' . $gateway . '_lite_connect_verify';
	}

	/**
	 * Record that an onboarding request is starting and return the token to send along with it.
	 *
	 * Call this when building the request body for the Connect server. The server is expected to
	 * echo the token back in its verify request.
	 *
	 * @since x.x
	 *
	 * @param string $gateway 'stripe', 'square', or 'paypal'.
	 *
	 * @return string The token to send to the Connect server. Empty string for an unknown gateway.
	 */
	public static function start_request( $gateway ) {
		if ( ! in_array( $gateway, self::$gateways, true ) ) {
			return '';
		}

		$token   = wp_generate_password( 32, false );
		$request = array(
			'token' => $token,
			'time'  => time(),
		);

		update_option( self::get_option_name( $gateway ), $request, false );

		return $token;
	}

	/**
	 * Forget the pending onboarding request for a gateway.
	 *
	 * @since x.x
	 *
	 * @param string $gateway 'stripe', 'square', or 'paypal'.
	 *
	 * @return void
	 */
	public static function clear_request( $gateway ) {
		if ( ! in_array( $gateway, self::$gateways, true ) ) {
			return;
		}

		delete_option( self::get_option_name( $gateway ) );
	}

	/**
	 * Get the pending onboarding request for a gateway, if there is a current one.
	 *
	 * @param string $gateway 'stripe', 'square', or 'paypal'.
	 *
	 * @return array|false
	 */
	private static function get_pending_request( $gateway ) {
		if ( ! in_array( $gateway, self::$gateways, true ) ) {
			return false;
		}

		$request = get_option( self::get_option_name( $gateway ) );

		if ( ! is_array( $request ) || empty( $request['token'] ) || empty( $request['time'] ) ) {
			return false;
		}

		if ( (int) $request['time'] + self::get_request_lifetime() < time() ) {
			// The window has passed. Drop the row so the endpoint stops answering.
			self::clear_request( $gateway );
			return false;
		}

		return $request;
	}

	/**
	 * Check if this site authenticates with a Pro license instead of a uuid.
	 *
	 * @return bool
	 */
	private static function site_has_pro_license() {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			return false;
		}

		return ! empty( FrmAddonsController::get_pro_license() );
	}

	/**
	 * Answer the anonymous verify request from the Connect server.
	 *
	 * Sends a JSON response and exits.
	 *
	 * @since x.x
	 *
	 * @param string $gateway 'stripe', 'square', or 'paypal'.
	 *
	 * @return void
	 */
	public static function handle_request( $gateway ) {
		if ( self::site_has_pro_license() ) {
			// A licensed site never authenticates with a uuid, so it never needs verifying.
			wp_send_json_error();
		}

		$request = self::get_pending_request( $gateway );

		if ( ! $request ) {
			// No onboarding request is in progress, so there is nothing to verify.
			wp_send_json_error();
		}

		$token = FrmAppHelper::get_post_param( 'verify_token', '', 'sanitize_text_field' );

		if ( ! $token || ! hash_equals( $request['token'], $token ) ) {
			wp_send_json_error();
		}

		$site_identifier = FrmAppHelper::get_post_param( 'site_identifier', '', 'sanitize_text_field' );

		if ( ! $site_identifier ) {
			wp_send_json_error();
		}

		$usage = new FrmUsage();

		if ( hash_equals( $usage->uuid(), $site_identifier ) ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}
}
