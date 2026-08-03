<?php
/**
 * Gated Content Item
 *
 * Value object representing a single item in a gated content action's items list.
 * Subclasses add type-specific properties and can override matches(), get_url(),
 * and get_title() for type-specific behaviour.
 *
 * @package Formidable
 *
 * @since 6.33
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmGatedItem {

	/**
	 * Item type slug (e.g. 'post', 'frm_file', 'frm_pdf').
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Content item ID (page post ID, attachment ID, view ID, …).
	 *
	 * @var int|string
	 */
	public $id;

	/**
	 * @param array{type: string, id: int|string} $item Item data array with 'type' and 'id' keys.
	 */
	public function __construct( array $item ) {
		$this->type = $item['type'];
		$this->id   = $item['id'];
	}

	/**
	 * Create a FrmGatedItem (or subclass) for the given type and ID.
	 *
	 * Fires `frm_gated_item_make` so Pro and add-on plugins can return a
	 * subclass instance for their own item types without Lite needing to know
	 * about them.
	 *
	 * @param array{type: string, id: int|string} $item Item data array with 'type' and 'id' keys.
	 *
	 * @return FrmGatedItem
	 */
	public static function make( array $item ) {
		/**
		 * Create a subclass instance for a non-core item type.
		 *
		 * Return a FrmGatedItem subclass instance to handle the given type.
		 * Return null to fall back to the base FrmGatedItem.
		 *
		 * @since 6.33
		 *
		 * @param FrmGatedItem|null                   $instance Instance, or null to use base class.
		 * @param array{type: string, id: int|string} $item     Item data array with 'type' and 'id' keys.
		 */
		$instance = apply_filters( 'frm_gated_item_make', null, $item );

		return $instance instanceof self ? $instance : new self( $item );
	}

	/**
	 * Check whether this item matches a raw item settings array.
	 *
	 * Subclasses may override to implement type-specific matching logic.
	 *
	 * @param array $item_data Raw item array from action settings (must have 'type' and 'id' keys).
	 *
	 * @return bool
	 */
	public function matches( $item_data ) {
		return is_array( $item_data )
		&& isset( $item_data['type'], $item_data['id'] )
		&& $item_data['type'] === $this->type
		&& (string) $item_data['id'] === (string) $this->id;
	}

	/**
	 * Return the gated access URL for this item.
	 *
	 * Base implementation handles 'post' items (permalink + access_code). Subclasses
	 * override this for type-specific URL schemes.
	 *
	 * @param string $raw_token Raw access token to append as the access_code query arg.
	 *
	 * @return string Full URL with access_code parameter, or empty string on failure.
	 */
	public function get_url( $raw_token ) {
		$url = self::get_permalink_for_gated_item( $this->id );
		return $url ? add_query_arg( 'access_code', $raw_token, $url ) : '';
	}

	/**
	 * Return the permalink for a gated item, using the pretty URL even for private posts/pages.
	 *
	 * WordPress's _get_page_link() falls back to ?page_id=ID for private pages when
	 * the current user cannot read private pages. Passing a cloned post object with
	 * post_status set to 'publish' bypasses that capability check.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	protected static function get_permalink_for_gated_item( $post_id ) {
		$post = get_post( $post_id );

		if ( $post && 'private' === $post->post_status ) {
			$post              = clone $post;
			$post->post_status = 'publish';
		}

		return (string) get_permalink( $post );
	}

	/**
	 * Return the display title for this item.
	 *
	 * Base implementation handles 'post' items (post title). Subclasses override
	 * this for type-specific titles.
	 *
	 * @return string Display title, or empty string when unavailable.
	 */
	public function get_title() {
		return get_the_title( $this->id );
	}

	/**
	 * Return the cookie name used to persist the access token for this item.
	 *
	 * Subclasses may override to produce a more specific name — e.g. when the
	 * same post type can have multiple distinct access scopes (view + entry).
	 *
	 * @since 6.33
	 *
	 * @return string
	 */
	public function get_cookie_name() {
		return 'frm_gc_' . $this->get_transient_key();
	}

	/**
	 * Return the item-specific segment used to build cache/transient keys.
	 *
	 * Callers prepend their own prefix and any additional scope identifiers
	 * (e.g. action ID) to form the full key. Subclasses may override to
	 * include extra scope — e.g. entry ID for view items — so that cache
	 * entries for different access scopes never collide.
	 *
	 * @since 6.33
	 *
	 * @return string
	 */
	public function get_transient_key() {
		return $this->type . '_' . $this->id;
	}
}
