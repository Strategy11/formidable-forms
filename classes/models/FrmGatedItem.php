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
 * @since x.x
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
	 * @param string     $type Item type slug.
	 * @param int|string $id   Content item ID.
	 */
	public function __construct( $type, $id ) {
		$this->type = $type;
		$this->id   = $id;
	}

	/**
	 * Create a FrmGatedItem (or subclass) for the given type and ID.
	 *
	 * Fires `frm_gated_item_make` so Pro and add-on plugins can return a
	 * subclass instance for their own item types without Lite needing to know
	 * about them.
	 *
	 * @param string     $type Item type slug.
	 * @param int|string $id   Content item ID.
	 *
	 * @return FrmGatedItem
	 */
	public static function make( $type, $id ) {
		/**
		 * Create a subclass instance for a non-core item type.
		 *
		 * Return a FrmGatedItem subclass instance to handle the given type.
		 * Return null to fall back to the base FrmGatedItem.
		 *
		 * @since x.x
		 *
		 * @param FrmGatedItem|null $item Instance, or null to use base class.
		 * @param string            $type Item type slug.
		 * @param int|string        $id   Content item ID.
		 */
		$item = apply_filters( 'frm_gated_item_make', null, $type, $id );

		return $item instanceof self ? $item : new self( $type, $id );
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
		$url = (string) get_permalink( $this->id );
		return $url ? add_query_arg( 'access_code', $raw_token, $url ) : '';
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
}
