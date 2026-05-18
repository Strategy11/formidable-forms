<?php
/**
 * Gated Token
 *
 * Value object wrapping a single wp_frm_gated_tokens DB row.
 * Encapsulates expiry checks, item-level validation, and cookie management.
 *
 * @package Formidable
 *
 * @since x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmGatedToken {

	/**
	 * SHA-256 hash stored in the DB (token_hash column).
	 *
	 * @var string
	 */
	private $token_hash;

	/**
	 * ID of the frm_form_actions post this token belongs to.
	 *
	 * @var int
	 */
	private $action_id;

	/**
	 * Unix timestamp after which the token is expired, or null if it never expires.
	 *
	 * @var int|null
	 */
	private $expired_at;

	/**
	 * WordPress user ID stored with this token, or null when not user-scoped.
	 *
	 * @var int|null
	 */
	private $user_id;

	/**
	 * @param object $row DB row from wp_frm_gated_tokens.
	 */
	public function __construct( $row ) {
		$this->token_hash = (string) $row->token_hash;
		$this->action_id  = (int) $row->action_id;
		$this->expired_at = null !== $row->expired_at ? (int) $row->expired_at : null;
		$this->user_id    = ! empty( $row->user_id ) ? (int) $row->user_id : null;
	}

	/**
	 * SHA-256 hash stored in the DB (token_hash column).
	 *
	 * @return string
	 */
	public function get_hash() {
		return $this->token_hash;
	}

	/**
	 * ID of the frm_form_actions post this token belongs to.
	 *
	 * @return int
	 */
	public function get_action_id() {
		return $this->action_id;
	}

	/**
	 * Unix timestamp after which the token is expired, or null if it never expires.
	 *
	 * @return int|null
	 */
	public function get_expired_at() {
		return $this->expired_at;
	}

	/**
	 * WordPress user ID stored with this token, or null when not user-scoped.
	 *
	 * @return int|null
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Whether this token has passed its expiry timestamp.
	 *
	 * @return bool
	 */
	private function is_expired() {
		return null !== $this->expired_at && time() >= $this->expired_at;
	}

	/**
	 * The gated content action post, or null if the post no longer exists.
	 *
	 * @return WP_Post|null
	 */
	public function get_action() {
		$post = get_post( $this->action_id );
		return $post instanceof WP_Post ? $post : null;
	}

	/**
	 * Validate this token against a specific content item.
	 *
	 * Returns false immediately if the token is expired (and clears its cache).
	 * When $item_id is empty the item check is skipped — used by types (e.g.
	 * frm_pdf) that are identified by shortcode rather than a fixed item ID.
	 * Successful item lookups are cached in a transient to avoid re-fetching
	 * action settings on subsequent requests. Only the structural item check is
	 * cached — the `frm_gated_content_validate` filter still fires every time.
	 *
	 * @param int|string $item_id   Content item ID (post ID, view ID, …). Pass 0 to skip item check.
	 * @param string $item_type Content item type slug (e.g. 'post', 'frm_pdf').
	 *
	 * @return bool
	 */
	public function validate( $item_id = 0, $item_type = '' ) {
		if ( $this->is_expired() ) {
			return false;
		}

		if ( empty( $item_id ) ) {
			$is_valid = true;
		} else {
			$is_valid = FrmGatedTokenHelper::action_contains_item( $this->action_id, $item_type, $item_id, $this->expired_at );
		}

		/**
		 * Filters whether a resolved token grants access to a content item.
		 *
		 * @since x.x
		 *
		 * @param bool          $is_valid  Whether the token passes structural validation.
		 * @param array         $args {
		 *     @type FrmGatedToken $token     The token being validated.
		 *     @type int|string    $item_id   Content item ID being checked.
		 *     @type string        $item_type Content item type slug.
		 * }
		 */
		return (bool) apply_filters(
			'frm_gated_content_validate',
			$is_valid,
			array(
				'token'     => $this,
				'item_id'   => $item_id,
				'item_type' => $item_type,
			)
		);
	}

	/**
	 * Persist a frm_gc_{item_type}_{item_id} cookie so subsequent requests skip the URL param.
	 *
	 * The raw token must be supplied by the caller — the token object only stores
	 * the hash, so only code that already holds the raw value (e.g. immediately
	 * after reading the access_code URL parameter) should call this method.
	 *
	 * @param string $raw_token Raw access token (same value as the access_code URL param).
	 * @param string $item_type Content item type slug (e.g. 'post', 'frm_file').
	 * @param int|string $item_id   Content item ID, or 0 when not applicable.
	 */
	public function set_cookie( $raw_token, $item_type = '', $item_id = 0 ) {
		FrmGatedTokenHelper::set_cookie( $raw_token, $this->expired_at, $item_type, $item_id );
	}

}
