<?php
/**
 * Gated Content Controller
 *
 * @package Formidable
 *
 * @since x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmGatedContentController {

	/**
	 * Post ID unlocked for the current request by maybe_unlock_post().
	 *
	 * Stored here so filter_password_required() can reference it without a
	 * closure (closures are forbidden as action/filter callbacks).
	 *
	 * @var int
	 */
	private static $unlocked_post_id = 0;

	/**
	 * Allow private posts into the main query when a valid gated-content token is present.
	 *
	 * Hooked on 'pre_get_posts', which fires before WP_Query runs its DB query.
	 * Private posts are excluded at the query level by WordPress, so the 'wp'
	 * hook is too late — get_queried_object_id() returns 0 for a 404'd private post.
	 *
	 * Token validation is intentionally deferred to maybe_unlock_post() (the 'wp'
	 * hook): by that time get_queried_object_id() is reliable and we can validate
	 * the token against the exact post that was resolved. If no valid token exists
	 * for the private post, maybe_unlock_post() forces a 404.
	 *
	 * @param WP_Query $query Current query object.
	 *
	 * @return void
	 */
	public static function maybe_include_private_posts( $query ) {
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}

		// Only widen singular requests — archives/lists must never expose private posts.
		if ( ! $query->is_singular ) {
			return;
		}

		$statuses = $query->get( 'post_status' );
		if ( ! is_array( $statuses ) ) {
			$statuses = $statuses ? array( $statuses ) : array( 'publish' );
		}

		// Already includes private — nothing to widen.
		if ( in_array( 'private', $statuses, true ) ) {
			return;
		}

		if ( ! FrmGatedTokenHelper::get_valid_token( 0, 'post' ) ) {
			return;
		}

		$statuses[] = 'private';
		$query->set( 'post_status', $statuses );
	}

	/**
	 * Attempt to unlock a gated post (password-protected or private) using a token.
	 *
	 * Hooked on 'wp' so get_queried_object_id() is available. Private posts are
	 * already in the query by this point (via maybe_include_private_posts), so
	 * only password-protected posts need the post_password_required filter.
	 *
	 * Resolution order:
	 *  1. URL query parameter access_code (raw token → hashed via get_valid_token).
	 *  2. Any frm_gc_* cookie whose hash validates against the current post.
	 *
	 * @return void
	 */
	public static function maybe_unlock_post() {
		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$is_password_protected = '' !== $post->post_password;
		$is_restricted_private = 'private' === $post->post_status && ! current_user_can( 'read_private_posts', $post_id );

		// Nothing to unlock — post is publicly accessible.
		if ( ! $is_password_protected && ! $is_restricted_private ) {
			return;
		}

		// Detect whether the token arrived via URL param before falling back to cookies.
		$access_code    = FrmAppHelper::simple_get( 'access_code' );
		$from_url_param = is_string( $access_code ) && '' !== $access_code;

		$valid_token = FrmGatedTokenHelper::get_valid_token( $post_id, 'post' );

		if ( $valid_token ) {
			// Password-protected posts need an explicit filter; private posts are
			// already accessible because maybe_include_private_posts widened the query.
			if ( $is_password_protected ) {
				self::$unlocked_post_id = $post_id;
				add_filter( 'post_password_required', 'FrmGatedContentController::filter_password_required', 10, 2 );
			}

			// Strip the raw token from the URL to prevent leakage via browser history,
			// server logs, and Referer headers. The cookie set above grants access on
			// the redirected request without the query parameter.
			if ( $from_url_param && wp_safe_redirect( remove_query_arg( 'access_code' ) ) ) {
				exit;
			}

			return;
		}

		// No valid token — force a 404 to prevent private posts from being exposed.
		if ( $is_restricted_private ) {
			self::force_404();
		}
	}

	/**
	 * Force the current request to a 404 response.
	 *
	 * Used when a private post was widened into the main query by
	 * maybe_include_private_posts() but no valid token was found.
	 *
	 * @return void
	 */
	private static function force_404() {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	/**
	 * Filter callback: return false for the single post unlocked by maybe_unlock_post().
	 *
	 * Fires on the 'post_password_required' filter. Only overrides the result for
	 * the specific post ID stored in self::$unlocked_post_id — all other posts are
	 * passed through unchanged.
	 *
	 * @param bool    $required Whether the password is required.
	 * @param WP_Post $post     Post being checked.
	 *
	 * @return bool
	 */
	public static function filter_password_required( $required, $post ) {
		if ( $post->ID === self::$unlocked_post_id ) {
			return false;
		}
		return $required;
	}

	/**
	 * Delete all gated tokens linked to a gated content action when it is permanently deleted.
	 *
	 * Fires on 'before_delete_post'. Only acts on frm_form_actions posts whose
	 * post_excerpt identifies them as gated_content actions.
	 *
	 * @param int     $post_id Post ID being deleted.
	 * @param WP_Post $post    Post object being deleted.
	 *
	 * @return void
	 */
	/**
	 * Clear the action-item membership cache when a gated content action is updated.
	 *
	 * Fires on 'save_post_frm_form_actions'. Only acts on updates (not creates)
	 * because the item list cannot change during initial creation.
	 *
	 * @param int     $post_id Post ID of the saved action.
	 * @param WP_Post $post    Saved post object.
	 * @param bool    $update  True when updating an existing post, false on create.
	 *
	 * @return void
	 */
	public static function on_action_updated( $post_id, $post, $update ) {
		if ( ! $update || FrmGatedContentAction::$slug !== $post->post_excerpt ) {
			return;
		}
		FrmGatedTokenHelper::delete_action_item_cache( $post_id );
	}

	/**
	 * Clean up when a gated content action post is permanently deleted.
	 *
	 * Hooked to `before_delete_post`. Clears the action-item transient cache
	 * (while the post is still readable) then removes all associated tokens.
	 *
	 * @param int     $post_id Post ID of the action being deleted.
	 * @param WP_Post $post    The action post object.
	 *
	 * @return void
	 */
	public static function on_action_deleted( int $post_id, WP_Post $post ) {
		if ( 'frm_form_actions' !== $post->post_type || FrmGatedContentAction::$slug !== $post->post_excerpt ) {
			return;
		}
		// Clear action-item cache first — the action post still exists at this
		// point (before_delete_post) so its settings are still readable.
		FrmGatedTokenHelper::delete_action_item_cache( $post_id );
		FrmGatedTokenHelper::delete_by_action( $post_id );
	}

	/**
	 * Generate a gated content token when a form action fires.
	 *
	 * @param object $action Form action post object (post_excerpt = 'gated_content').
	 * @param object $entry  Submitted form entry object.
	 * @param object $form   Form object.
	 * @param string $event  Trigger event ('create', 'payment-success', 'user_registration', …).
	 *
	 * @return void
	 */
	public static function trigger( $action, $entry, $form, $event ) {
		$raw_user_id = get_current_user_id();
		$user_id     = $raw_user_id ? $raw_user_id : null;

		/**
		 * Filters the user ID stored in the generated gated content token.
		 *
		 * Use this to supply the correct user ID when the current user is not yet
		 * logged in at trigger time — for example, during a user_registration event
		 * where the new user is created but auto-login has not yet happened.
		 *
		 * @since x.x
		 *
		 * @param int|null $user_id WordPress user ID, or null for guests.
		 * @param array    $args {
		 *     @type object $entry Submitted form entry object.
		 *     @type string $event Trigger event slug.
		 * }
		 */
		$user_id = apply_filters( 'frm_gated_content_token_user_id', $user_id, compact( 'entry', 'event' ) );

		FrmGatedTokenHelper::generate( $action->ID, $entry->id, $user_id );
	}

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
		$atts = shortcode_atts( self::default_shortcode_atts(), $atts, 'frm_gated_content' );

		$action_id = (int) $atts['id'];
		if ( ! $action_id ) {
			return '';
		}

		/**
		 * Handle a Pro or add-on show= value for the [frm_gated_content] shortcode.
		 *
		 * Return a non-null string to short-circuit the default rendering. Return null
		 * to let the shortcode fall through to its built-in show= handlers.
		 *
		 * @since x.x
		 *
		 * @param string|null $output    Output string, or null to continue default handling.
		 * @param string      $show      Value of the show= attribute.
		 * @param int         $action_id Gated content action post ID.
		 */
		$custom = apply_filters( 'frm_gated_content_shortcode_show', null, $atts['show'], $action_id );
		if ( null !== $custom ) {
			return (string) $custom;
		}

		// All other show values require the raw token.
		$raw_token = self::resolve_raw_token( $action_id );
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
		$items    = ( is_array( $settings ) && ! empty( $settings['items'] ) ) ? $settings['items'] : array();
		$show_url = ( 'url' === $atts['show'] );

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
	 * @param array  $item      Item array from action settings (must have 'id' and 'type' keys).
	 * @param string $raw_token Raw access token.
	 * @param bool   $show_url  True to return a plain escaped URL; false to return an <a> tag.
	 *
	 * @return string Rendered output, or empty string when the item is invalid or has no URL.
	 */
	private static function render_shortcode_item( $item, $raw_token, $show_url ) {
		if ( empty( $item['id'] ) || empty( $item['type'] ) ) {
			return '';
		}

		$url = self::get_item_url( (int) $item['id'], $item['type'], $raw_token );
		if ( ! $url ) {
			return '';
		}

		if ( $show_url ) {
			return esc_url( $url );
		}

		$label = self::get_item_title( (int) $item['id'], $item['type'] );
		if ( ! $label ) {
			$label = $url;
		}

		return '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}

	/**
	 * Resolve the raw token for an action from the per-request static variable or the
	 * 5-minute transient set by FrmGatedTokenHelper::generate().
	 *
	 * Only these two sources can yield a raw (unhashed) token — cookies and DB rows
	 * store only the SHA-256 hash, so they cannot be used here. Use
	 * FrmGatedTokenHelper::get_valid_token() when a token object is sufficient.
	 *
	 * A filter is provided so add-ons can supply a raw token from other sources
	 * (e.g. re-generating a single-use token on demand for a registered user).
	 *
	 * @param int $action_id Action post ID.
	 *
	 * @return string|null Raw token string, or null if unavailable.
	 */
	private static function resolve_raw_token( $action_id ) {
		$raw = FrmGatedTokenHelper::get_raw_token_for_action( $action_id );

		/**
		 * Filter the raw gated content token resolved for a shortcode.
		 *
		 * Fires after the static variable and transient are checked. Return a raw
		 * token string to supply one from another source, or null to indicate none
		 * is available. Cookie and hash-only sources cannot be used here — raw
		 * tokens are required for shortcode output.
		 *
		 * @since x.x
		 *
		 * @param string|null $raw  Raw token from the static variable / transient, or null.
		 * @param array       $args {
		 *     @type int $action_id Gated content action post ID.
		 * }
		 */
		/** @var string|null */
		return apply_filters( 'frm_gated_content_raw_token', $raw, compact( 'action_id' ) );
	}

	/**
	 * Build the gated access URL for a single content item.
	 *
	 * Appends the raw token as the `access_code` query argument. Pro item types
	 * ('frm_file', 'frm_pdf', …) can provide a base URL via the
	 * `frm_gated_content_item_url` filter.
	 *
	 * @param int|string $item_id   Content item ID (e.g. page post ID).
	 * @param string $type      Item type slug ('post', 'frm_file', 'frm_pdf', …).
	 * @param string $raw_token Raw access token to append as access_code query arg.
	 *
	 * @return string Full URL with access_code parameter, or empty string on failure.
	 */
	public static function get_item_url( $item_id, $type, $raw_token ) {
		$base_url = 'post' === $type ? get_permalink( $item_id ) : '';

		/**
		 * Filter the base URL for a gated content item type.
		 *
		 * Fires for all types including 'post', allowing the default permalink to
		 * be overridden. Pro add-ons use this to support 'frm_file', 'frm_pdf', etc.
		 *
		 * @param string $base_url Permalink for 'post' items; empty string for others.
		 * @param array  $args {
		 *     @type int|string $item_id   Content item ID.
		 *     @type string $type      Item type slug.
		 *     @type string $raw_token Raw access token.
		 * }
		 */
		$base_url = (string) apply_filters( 'frm_gated_content_item_url', $base_url, compact( 'item_id', 'type', 'raw_token' ) );

		if ( ! $base_url ) {
			return '';
		}

		return add_query_arg( 'access_code', $raw_token, $base_url );
	}

	/**
	 * Get the display title for a single gated content item.
	 *
	 * For 'post' items this is the post title. Pro item types ('frm_file',
	 * 'frm_pdf', …) can provide a title via the `frm_gated_content_item_title`
	 * filter.
	 *
	 * @param int|string $item_id Content item ID (e.g. page post ID or attachment ID).
	 * @param string $type    Item type slug ('post', 'frm_file', 'frm_pdf', …).
	 *
	 * @return string Display title, or empty string when unavailable.
	 */
	public static function get_item_title( $item_id, $type ) {
		$title = 'post' === $type ? get_the_title( $item_id ) : '';

		/**
		 * Filter the display title for a gated content item type.
		 *
		 * Fires for all types including 'post', allowing the default post title to
		 * be overridden. Pro add-ons use this to support 'frm_file', 'frm_pdf', etc.
		 *
		 * @param string $title   Post title for 'post' items; empty string for others.
		 * @param array  $args {
		 *     @type int|string $item_id Content item ID.
		 *     @type string $type    Item type slug.
		 * }
		 */
		return (string) apply_filters( 'frm_gated_content_item_title', $title, compact( 'item_id', 'type' ) );
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
		if ( empty( $items ) ) {
			return '';
		}

		$list_items = '';

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['id'] ) || empty( $item['type'] ) ) {
				continue;
			}

			$url = self::get_item_url( (int) $item['id'], $item['type'], $raw_token );
			if ( ! $url ) {
				continue;
			}

			$label = self::get_item_title( (int) $item['id'], $item['type'] );
			if ( ! $label ) {
				$label = $url;
			}

			$list_items .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
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
		if ( empty( $items ) ) {
			return '';
		}

		$list_items = '';

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['id'] ) || empty( $item['type'] ) ) {
				continue;
			}

			$url = self::get_item_url( (int) $item['id'], $item['type'], $raw_token );
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
