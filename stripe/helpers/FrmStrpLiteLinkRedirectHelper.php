<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmStrpLiteLinkRedirectHelper {

	/**
	 * @var string Either a Payment Intent ID (prefixed with pi_) or a Setup Intent ID (prefixed with seti_).
	 */
	private $stripe_id;

	/**
	 * @var string
	 */
	private $client_secret;

	/**
	 * @param string $stripe_id either a Payment Intent ID (prefixed with pi_) or a Setup Intent ID (prefixed with seti_).
	 * @param string $client_secret
	 * @return void
	 */
	public function __construct( $stripe_id, $client_secret ) {
		$this->stripe_id     = $stripe_id;
		$this->client_secret = $client_secret;
	}

	/**
	 * @param string $error_code
	 * @return void
	 */
	public function handle_error( $error_code ) {
		$this->add_intent_info_and_redirect(
			add_query_arg( array( 'frm_link_error' => $error_code ), FrmAppHelper::get_server_value( 'HTTP_REFERER' ) )
		);
	}

	/**
	 * Redirect to handle the form's on success condition similar to how 3D secure is handled after being redirected.
	 *
	 * @param stdClass $entry
	 * @param string   $charge_id
	 * @return void
	 */
	public function handle_success( $entry, $charge_id ) {
		$form = FrmForm::getOne( $entry->form_id );

		// Let a stripe link success message get handled the same as a 3D secure redirect.
		// When it shows a message, it adds a &frmstrp= param to the URL.
		$redirect = FrmStrpLiteAuth::return_url( compact( 'form', 'entry' ) );

		if ( $charge_id ) {
			$redirect .= '&charge=' . $charge_id;
		}

		$this->add_intent_info_and_redirect( $redirect );
	}

	/**
	 * Redirect, have FrmStrpLiteAuth::maybe_show_message handle it similar to 3D secure.
	 *
	 * @param string $url
	 */
	private function add_intent_info_and_redirect( $url ) {
		if ( 0 === strpos( $this->stripe_id, 'pi_' ) ) {
			$url .= '&payment_intent=' . $this->stripe_id;
			$url .= '&payment_intent_client_secret=' . $this->client_secret;
		} else {
			$url .= '&setup_intent=' . $this->stripe_id;
			$url .= '&setup_intent_client_secret=' . $this->client_secret;
		}
		wp_safe_redirect( $url );
		die();
	}
}
