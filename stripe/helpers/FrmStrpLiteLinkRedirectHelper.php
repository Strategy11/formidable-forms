<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.5, introduced in v3.0 of the Stripe add on.
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
	 * @var int|null The entry ID associated with the payment being handled.
	 */
	private $entry_id;

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
	 * Set the entry ID to pull referer data from.
	 * This is separate from the constructor as the entry ID isn't known for some error cases.
	 *
	 * @param int|string $entry_id
	 * @return void
	 */
	public function set_entry_id( $entry_id ) {
		$this->entry_id = absint( $entry_id );
	}

	/**
	 * @param string $error_code
	 * @param string $charge_id
	 * @return void
	 */
	public function handle_error( $error_code, $charge_id = '' ) {
		if ( ! empty( $this->entry_id ) ) {
			$referer = FrmStrpLiteAuth::get_referer_url( $this->entry_id );
		}

		if ( empty( $referer ) ) {
			$referer = FrmAppHelper::get_server_value( 'HTTP_REFERER' );
		}

		$args = array(
			'frm_link_error' => $error_code,
		);

		if ( $charge_id ) {
			$args['charge'] = $charge_id;
		}

		$this->add_intent_info_and_redirect(
			add_query_arg( $args, $referer )
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
		$redirect            = FrmStrpLiteAuth::return_url( compact( 'form', 'entry' ) );
		$is_message_redirect = false !== strpos( $redirect, 'frmstrp=' );

		if ( $this->url_is_external( $redirect ) || ! $is_message_redirect ) {
			wp_redirect( $redirect );
			die();
		}

		// $redirect may not include the whole link to the form, breaking the redirect as iDEAL/Sofort have an additional redirect.
		$referer_url = $this->get_referer_url( $entry->id );
		if ( is_string( $referer_url ) ) {
			$parts = explode( '?', $redirect, 2 );
			if ( 2 === count( $parts ) ) {
				$redirect = $parts[1];
			}
			$redirect = $referer_url . '?' . $redirect;
		}

		if ( $charge_id ) {
			$redirect .= '&charge=' . $charge_id;
		}

		$this->add_intent_info_and_redirect( $redirect );
	}

	/**
	 * Determine if a redirect URL is going to an external site or not.
	 *
	 * @param string $url
	 */
	private function url_is_external( $url ) {
		if ( false === strpos( $url, 'http' ) ) {
			return false;
		}

		$home_url = home_url();
		$parsed   = parse_url( $home_url );
		if ( is_array( $parsed ) ) {
			$home_url = $parsed['scheme'] . '://' . $parsed['host'];
		}
		return 0 !== strpos( $url, $home_url );
	}

	/**
	 * Try to get the referer URL from the entry meta.
	 * If it is found, it will also be deleted as it is only required once.
	 *
	 * @param int|string $entry_id
	 * @return false|string
	 */
	private function get_referer_url( $entry_id ) {
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
	 * Redirect, have FrmStrpLiteAuth::maybe_show_message handle it similar to 3D secure.
	 *
	 * @param string $url
	 */
	private function add_intent_info_and_redirect( $url ) {
		if ( 0 === strpos( $this->stripe_id, 'pi_' ) ) {
			$url = add_query_arg( 'payment_intent', $this->stripe_id, $url );
			$url = add_query_arg( 'payment_intent_client_secret', $this->client_secret, $url );
		} else {
			$url = add_query_arg( 'setup_intent', $this->stripe_id, $url );
			$url = add_query_arg( 'setup_intent_client_secret', $this->client_secret, $url );
		}

		// iDeal redirects URLs are incorrectly encoded.
		// This str_replace reverts that encoding issue.
		$url = str_replace( '%3Ffrmstrp%3D', '&frmstrp=', $url );

		wp_redirect( $url );
		die();
	}
}
