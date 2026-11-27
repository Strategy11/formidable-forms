<?php
/**
 * Gated Token
 *
 * Value object wrapping a single wp_frm_gated_tokens DB row.
 * Encapsulates expiry checks and item-level validation.
 *
 * @package Formidable
 *
 * @since 6.33
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmGatedToken {

	/**
	 * Full DB row from wp_frm_gated_tokens.
	 *
	 * @var object
	 */
	private $row;

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
		$this->row        = $row;
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
	 * Full DB row from wp_frm_gated_tokens.
	 *
	 * Use this to access columns not exposed by dedicated getters (e.g. entry_id,
	 * ip_address, created_at).
	 *
	 * @return object
	 */
	public function get_row() {
		return $this->row;
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
	 * When $item->id is empty the item check is skipped — used by types (e.g.
	 * frm_pdf) that are identified by shortcode rather than a fixed item ID.
	 * Successful item lookups are cached in a transient to avoid re-fetching
	 * action settings on subsequent requests. Only the structural item check is
	 * cached — the `frm_gated_content_is_valid` filter still fires every time.
	 *
	 * @param FrmGatedItem $item Content item (type slug + ID).
	 *
	 * @return bool
	 */
	public function validate( FrmGatedItem $item ) {
		if ( $this->is_expired() ) {
			return false;
		}

		$is_valid = FrmGatedTokenHelper::action_contains_item( $this->action_id, $item, $this->expired_at );

		/**
		 * Filters whether a resolved token grants access to a content item.
		 *
		 * @since 6.33
		 *
		 * @param bool          $is_valid  Whether the token passes structural validation.
		 * @param array         $args {
		 *
		 *     @type FrmGatedToken $token The token being validated.
		 *     @type FrmGatedItem  $item  Content item being checked.
		 * }
		 */
		return (bool) apply_filters(
			'frm_gated_content_is_valid',
			$is_valid,
			array(
				'token' => $this,
				'item'  => $item,
			)
		);
	}
}
