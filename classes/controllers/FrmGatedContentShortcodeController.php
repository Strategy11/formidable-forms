<?php
/**
 * Gated Content Shortcode Controller
 *
 * Handles the [frm_gated_content] shortcode and all item URL/title rendering.
 *
 * @package Formidable
 *
 * @since 6.33
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmGatedContentShortcodeController {

	/**
	 * Default attributes for the [frm_gated_content] shortcode.
	 *
	 *  - id   (required) Action post ID.
	 *  - item (optional) 0-indexed item position. Omit to render the full item list.
	 *  - show (optional) 'link' (default) | 'url' | 'access_token'.
	 *          'link'         — <a> link tag(s). Without item: <ul> of links. With item: single <a>.
	 *          'url'          — Plain URL string. Without item: <ul> of plain URLs. With item: single URL.
	 *          'access_token' — Raw access token string.
	 *
	 * @return array<string, mixed>
	 */
	private static function default_shortcode_atts() {
		return array(
			'id'   => 0,
			'item' => false,
			'show' => 'link',
		);
	}

	/**
	 * Handle the [frm_gated_content] shortcode.
	 *
	 * @param array $atts Shortcode attributes. See default_shortcode_atts() for supported keys.
	 *
	 * @return string Shortcode output, or empty string when no token is available.
	 */
	public static function shortcode( $atts ) {
		$atts      = shortcode_atts( self::default_shortcode_atts(), $atts, 'frm_gated_content' );
		$action_id = (int) $atts['id'];

		if ( ! $action_id ) {
			return '';
		}

		/**
		 * Override or extend [frm_gated_content] shortcode output.
		 *
		 * Return a non-null string to short-circuit the default rendering. Return null
		 * to let the shortcode fall through to its built-in show= handlers.
		 *
		 * @since 6.33
		 *
		 * @param string|null $output Output string, or null to continue default handling.
		 * @param array       $atts   Full shortcode attributes array (id, show, item, …).
		 *                            $atts['id'] is the gated content action post ID.
		 */
		$custom = apply_filters( 'frm_gated_content_shortcode_custom_output', null, $atts );

		if ( null !== $custom ) {
			return (string) $custom;
		}

		// All other show values require the raw token.
		$raw_token = FrmGatedTokenHelper::get_raw_token_for_action( $action_id );

		if ( null === $raw_token ) {
			return '';
		}

		if ( 'access_token' === $atts['show'] ) {
			return esc_html( $raw_token );
		}

		return self::render_items_shortcode_output( $action_id, $raw_token, $atts );
	}

	/**
	 * Render the item links or URLs portion of the [frm_gated_content] shortcode.
	 *
	 * Called after the raw token is confirmed. Loads the action's items and
	 * dispatches to the appropriate renderer based on the show= and item= atts.
	 *
	 * @param int    $action_id Gated content action post ID.
	 * @param string $raw_token Raw access token.
	 * @param array  $atts      Shortcode attributes (show, item).
	 *
	 * @return string
	 */
	private static function render_items_shortcode_output( $action_id, $raw_token, $atts ) {
		$action = get_post( $action_id );

		if ( ! $action ) {
			return '';
		}

		$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
		$items    = is_array( $settings ) && ! empty( $settings['items'] ) ? $settings['items'] : array();
		$show_url = 'url' === $atts['show'];

		if ( false === $atts['item'] ) {
			// No item specified — render all items.
			return $show_url
			? self::render_item_url_list( $items, $raw_token )
			: self::render_item_list( $items, $raw_token );
		}

		// Single item by 0-based index.
		$idx = (int) $atts['item'];

		if ( ! isset( $items[ $idx ] ) || ! is_array( $items[ $idx ] ) ) {
			return '';
		}

		return self::render_shortcode_item( $items[ $idx ], $raw_token, $show_url );
	}

	/**
	 * Render a single gated content item as a URL string or anchor link.
	 *
	 * @param array  $raw_item_data Item array from action settings (must have 'id' and 'type' keys).
	 * @param string $raw_token     Raw access token.
	 * @param bool   $show_url      True to return a plain escaped URL; false to return an <a> tag.
	 *
	 * @return string Rendered output, or empty string when the item is invalid or has no URL.
	 */
	private static function render_shortcode_item( $raw_item_data, $raw_token, $show_url ) {
		if ( empty( $raw_item_data['id'] ) || empty( $raw_item_data['type'] ) ) {
			return '';
		}

		$item = FrmGatedItem::make( $raw_item_data );
		$url  = $item->get_url( $raw_token );

		if ( ! $url ) {
			return '';
		}

		if ( $show_url ) {
			return esc_url( $url );
		}

		$label = $item->get_title();

		if ( ! $label ) {
			$label = $url;
		}

		return '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</a>';
	}

	/**
	 * Render an unordered list of gated access links for all items in an action.
	 *
	 * @param array  $items     Array of item arrays from action settings (each with 'id' and 'type' keys).
	 * @param string $raw_token Raw access token.
	 *
	 * @return string HTML <ul> element, or empty string when no items produce a URL.
	 */
	public static function render_item_list( $items, $raw_token ) {
		if ( ! $items ) {
			return '';
		}

		$list_items = '';

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['id'] ) || empty( $item['type'] ) ) {
				continue;
			}

			$gated_item = FrmGatedItem::make( $item );
			$url        = $gated_item->get_url( $raw_token );

			if ( ! $url ) {
				continue;
			}

			$label = $gated_item->get_title();

			if ( ! $label ) {
				$label = $url;
			}

			$list_items .= '<li><a href="' . esc_url( $url ) . '" title="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</a></li>';
		}

		if ( ! $list_items ) {
			return '';
		}

		return '<ul class="frm-gated-content-list">' . $list_items . '</ul>';
	}

	/**
	 * Render an unordered list of plain URLs (no link tags) for all items in an action.
	 *
	 * Used when show="url" is set without an item index.
	 *
	 * @param array  $items     Array of item arrays from action settings (each with 'id' and 'type' keys).
	 * @param string $raw_token Raw access token.
	 *
	 * @return string HTML <ul> element of plain-text URLs, or empty string when no items produce a URL.
	 */
	public static function render_item_url_list( $items, $raw_token ) {
		if ( ! $items ) {
			return '';
		}

		$list_items = '';

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['id'] ) || empty( $item['type'] ) ) {
				continue;
			}

			$gated_item = FrmGatedItem::make( $item );
			$url        = $gated_item->get_url( $raw_token );

			if ( ! $url ) {
				continue;
			}

			$list_items .= '<li>' . esc_url( $url ) . '</li>';
		}

		if ( ! $list_items ) {
			return '';
		}

		return '<ul class="frm-gated-content-list">' . $list_items . '</ul>';
	}
}
