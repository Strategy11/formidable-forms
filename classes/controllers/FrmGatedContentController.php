<?php
/**
 * Gated Content Controller
 *
 * @package Formidable
 *
 * @since x.x
 *
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
	 * @since x.x
	 *
	 * @var int
	 */
	private static $unlocked_post_id = 0;

	/**
	 * Allow private pages into the main query when a valid token is present.
	 *
	 * Hooked on 'pre_get_posts', which fires before WP_Query runs its DB query.
	 * Private pages are excluded at the query level by WordPress, so the 'wp'
	 * hook is too late — get_queried_object_id() returns 0 for a 404'd private
	 * page. This hook resolves the target post ID from the query vars, validates
	 * the token, and conditionally appends 'private' to the post_status list.
	 *
	 * @since x.x
	 *
	 * @param WP_Query $query Current query object.
	 * @return void
	 */
	public static function maybe_include_private_pages( $query ) {
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}

		$post_id = self::get_requested_post_id( $query );
		if ( ! $post_id ) {
			return;
		}

		if ( self::has_valid_token_for_post( $post_id ) ) {
			$statuses = $query->get( 'post_status' );
			if ( ! is_array( $statuses ) ) {
				$statuses = $statuses ? array( $statuses ) : array( 'publish' );
			}
			if ( ! in_array( 'private', $statuses, true ) ) {
				$statuses[] = 'private';
				$query->set( 'post_status', $statuses );
			}
		}
	}

	/**
	 * Attempt to unlock a gated post (password-protected or private) using a token.
	 *
	 * Hooked on 'wp' so get_queried_object_id() is available. Private pages are
	 * already in the query by this point (via maybe_include_private_pages), so
	 * only password-protected pages need the post_password_required filter.
	 *
	 * Resolution order:
	 *  1. URL query parameter access_code (raw token → hashed via obtain_token).
	 *  2. Any frm_gc_* cookie whose hash validates against the current post.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function maybe_unlock_post() {
		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		// Detect whether the token arrived via URL param before falling back to cookies.
		$access_code    = FrmAppHelper::simple_get( 'access_code' );
		$from_url_param = is_string( $access_code ) && '' !== $access_code;

		// Try URL query parameter first (obtain_token with no action_id uses URL param only).
		$hash = FrmGatedTokenHelper::obtain_token();

		// No URL param — scan frm_gc_* cookies to find one that grants access to this page.
		if ( ! $hash ) {
			$hash = self::find_valid_cookie_hash_for_post( $post_id );
		}

		if ( ! $hash ) {
			return;
		}

		// Fetch the row once and reuse for both validation and cookie/redirect logic.
		$row = FrmGatedTokenHelper::get_row_by_hash( $hash );

		if ( FrmGatedTokenHelper::validate_hash( $hash, $post_id, 'page', $row ) ) {
			$post = get_post( $post_id );

			// Password-protected pages need an explicit filter; private pages are
			// already accessible because maybe_include_private_pages widened the query.
			if ( $post && '' !== $post->post_password ) {
				self::$unlocked_post_id = $post_id;
				add_filter( 'post_password_required', 'FrmGatedContentController::filter_password_required', 10, 2 );
			}

			// Refresh the frm_gc_ cookie so subsequent visits skip the URL param.
			if ( $row ) {
				FrmGatedTokenHelper::set_cookie( (int) $row->action_id, $hash, $row->expired_at );
			}

			// Strip the raw token from the URL to prevent leakage via browser history,
			// server logs, and Referer headers. The cookie set above grants access on
			// the redirected request without the query parameter.
			if ( $from_url_param ) {
				wp_safe_redirect( remove_query_arg( 'access_code' ) );
				exit;
			}

			return;
		}

		// Token present but invalid (expired or revoked) — redirect if action configured it.
		if ( ! $row ) {
			return;
		}

		$action = get_post( (int) $row->action_id );
		if ( ! $action ) {
			return;
		}

		$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
		if ( ! empty( $settings['show_form_page'] ) ) {
			$redirect_url = get_permalink( (int) $settings['show_form_page'] );
			if ( $redirect_url ) {
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}

	/**
	 * Filter callback: return false for the single post unlocked by maybe_unlock_post().
	 *
	 * Fires on the 'post_password_required' filter. Only overrides the result for
	 * the specific post ID stored in self::$unlocked_post_id — all other posts are
	 * passed through unchanged.
	 *
	 * @since x.x
	 *
	 * @param bool    $required Whether the password is required.
	 * @param WP_Post $post     Post being checked.
	 * @return bool
	 */
	public static function filter_password_required( $required, $post ) {
		if ( $post->ID === self::$unlocked_post_id ) {
			return false;
		}
		return $required;
	}

	/**
	 * Resolve the post ID being requested from query vars, including private posts.
	 *
	 * Called from maybe_include_private_pages() during pre_get_posts, before the
	 * DB query runs. Uses post_status => 'any' so private pages are returned
	 * regardless of whether the visitor is logged in.
	 *
	 * @since x.x
	 *
	 * @param WP_Query $query Current query object.
	 * @return int Post ID, or 0 if it cannot be determined.
	 */
	private static function get_requested_post_id( $query ) {
		if ( ! empty( $query->query_vars['page_id'] ) ) {
			return (int) $query->query_vars['page_id'];
		}

		if ( ! empty( $query->query_vars['pagename'] ) ) {
			$pages = get_posts(
				array(
					'pagename'       => $query->query_vars['pagename'],
					'post_type'      => 'page',
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'no_found_rows'  => true,
				)
			);
			return ! empty( $pages ) ? $pages[0]->ID : 0;
		}

		return 0;
	}

	/**
	 * Check whether the current request carries any valid token for a given post.
	 *
	 * Checks the URL access_code parameter first, then frm_gc_* cookies. Used by
	 * maybe_include_private_pages() to decide whether to widen the query.
	 *
	 * @since x.x
	 *
	 * @param int $post_id Post ID to validate against.
	 * @return bool True if a valid token is found.
	 */
	private static function has_valid_token_for_post( $post_id ) {
		$access_code = FrmAppHelper::simple_get( 'access_code' );
		if ( '' !== $access_code ) {
			$hash = hash( 'sha256', $access_code );
			if ( FrmGatedTokenHelper::validate_hash( $hash, $post_id, 'page' ) ) {
				return true;
			}
		}

		return null !== self::find_valid_cookie_hash_for_post( $post_id );
	}

	/**
	 * Scan frm_gc_* cookies and return the first hash that validates against a post.
	 *
	 * Used as a fallback when no access_code URL parameter is present, allowing
	 * return visits to stay unlocked without re-clicking the emailed link.
	 *
	 * @since x.x
	 *
	 * @param int $post_id Post ID to validate against.
	 * @return string|null Validated token hash, or null if none found.
	 */
	private static function find_valid_cookie_hash_for_post( $post_id ) {
		foreach ( $_COOKIE as $name => $value ) {
			if ( 0 !== strpos( $name, 'frm_gc_' ) ) {
				continue;
			}
			$hash = sanitize_text_field( $value );
			if ( FrmGatedTokenHelper::validate_hash( $hash, $post_id, 'page' ) ) {
				return $hash;
			}
		}
		return null;
	}

	/**
	 * Delete all gated tokens linked to a gated content action when it is permanently deleted.
	 *
	 * Fires on 'before_delete_post'. Only acts on frm_form_actions posts whose
	 * post_excerpt identifies them as gated_content actions.
	 *
	 * @since x.x
	 *
	 * @param int     $post_id Post ID being deleted.
	 * @param WP_Post $post    Post object being deleted.
	 * @return void
	 */
	public static function on_action_deleted( $post_id, $post ) {
		if ( 'frm_form_actions' !== $post->post_type || FrmGatedContentAction::$slug !== $post->post_excerpt ) {
			return;
		}
		FrmGatedTokenHelper::delete_by_action( $post_id );
	}

	/**
	 * Generate a gated content token when a form action fires.
	 *
	 * Runs at form action priority 8 — before On Submit (9) and Send Email (10) —
	 * so the raw token is already stored when those actions process [frm_gated_content]
	 * shortcodes on the same request or after a payment redirect.
	 *
	 * @since x.x
	 *
	 * @param object $action Form action post object (post_excerpt = 'gated_content').
	 * @param object $entry  Submitted form entry object.
	 * @param object $form   Form object.
	 * @param string $event  Trigger event ('create', 'update', or 'import').
	 * @return void
	 */
	public static function trigger( $action, $entry, $form, $event ) {
		$raw_user_id = get_current_user_id();
		$user_id     = $raw_user_id ? $raw_user_id : null;

		// On update, revoke any existing tokens for this action+entry pair before
		// issuing a fresh one — prevents unbounded row accumulation and ensures the
		// old token cannot be used once the entry owner receives a new one.
		if ( 'update' === $event ) {
			FrmGatedTokenHelper::delete_by_action_and_entry( $action->ID, $entry->id );
		}

		FrmGatedTokenHelper::generate( $action->ID, $entry->id, $user_id );
	}

	/**
	 * Handle the [frm_gated_content] shortcode.
	 *
	 * Attributes:
	 *  - id   (required) Action post ID.
	 *  - item (optional) 0-indexed item position. Omit to render the full item list.
	 *  - show (optional) 'link' (default) | 'url' | 'access_token' | 'expired_time'.
	 *          'link'         — <a> link tag(s). Without item: <ul> of links. With item: single <a>.
	 *          'url'          — Plain URL string. Without item: <ul> of plain URLs. With item: single URL.
	 *          'access_token' — Raw access token string.
	 *          'expired_time' — Formatted expiry date/time (empty if token never expires).
	 *
	 * @since x.x
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode output, or empty string when no token is available.
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'   => 0,
				'item' => false,
				'show' => 'link',
			),
			$atts,
			'frm_gated_content'
		);

		$action_id = (int) $atts['id'];
		if ( ! $action_id ) {
			return '';
		}

		// expired_time only needs a hash — try hash-only sources too.
		if ( 'expired_time' === $atts['show'] ) {
			$raw_token = self::resolve_raw_token( $action_id );
			if ( null !== $raw_token ) {
				return self::get_formatted_expiry( $raw_token );
			}
			$hash = FrmGatedTokenHelper::obtain_token( $action_id );
			return $hash ? self::get_formatted_expiry_by_hash( $hash ) : '';
		}

		// All other show values require the raw token.
		$raw_token = self::resolve_raw_token( $action_id );
		if ( null === $raw_token ) {
			return '';
		}

		if ( 'access_token' === $atts['show'] ) {
			return esc_html( $raw_token );
		}

		// show="link" (default) or show="url": load action items.
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

		$item = $items[ $idx ];
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

		$label = get_the_title( (int) $item['id'] );
		if ( ! $label ) {
			$label = $url;
		}

		return '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}

	/**
	 * Resolve the raw token for an action from the session transient or URL parameter.
	 *
	 * Cookie-only sources cannot yield a raw token, so they are excluded here.
	 * Use FrmGatedTokenHelper::obtain_token() when only a hash is needed.
	 *
	 * @since x.x
	 *
	 * @param int $action_id Action post ID.
	 * @return string|null Raw 48-char token, or null if unavailable.
	 */
	private static function resolve_raw_token( $action_id ) {
		// Transient set by FrmGatedTokenHelper::generate() — survives payment redirects.
		$raw = FrmGatedTokenHelper::get_raw_token_for_action( $action_id );
		if ( null !== $raw ) {
			return $raw;
		}

		// URL query parameter hit (e.g. visitor clicking a gated link from email).
		$candidate = FrmAppHelper::simple_get( 'access_code' );
		if ( is_string( $candidate ) && '' !== $candidate ) {
			$row = FrmGatedTokenHelper::get_row_by_token( $candidate );
			if ( $row
				&& (int) $row->action_id === $action_id
				&& ( null === $row->expired_at || time() < (int) $row->expired_at )
			) {
				return $candidate;
			}
		}

		return null;
	}

	/**
	 * Build the gated access URL for a single content item.
	 *
	 * Appends the raw token as the `access_code` query argument. Pro item types
	 * ('frm_file', 'frm_pdf', …) can provide a base URL via the
	 * `frm_gated_content_item_url` filter.
	 *
	 * @since x.x
	 *
	 * @param int    $item_id   Content item ID (e.g. page post ID).
	 * @param string $type      Item type slug ('page', 'frm_file', 'frm_pdf', …).
	 * @param string $raw_token Raw access token to append as access_code query arg.
	 * @return string Full URL with access_code parameter, or empty string on failure.
	 */
	public static function get_item_url( $item_id, $type, $raw_token ) {
		$base_url = '';

		switch ( $type ) {
			case 'page':
				$base_url = (string) get_permalink( $item_id );
				break;
			default:
				/**
				 * Filter the base URL for a gated content item type.
				 *
				 * Pro add-ons use this to support 'frm_file', 'frm_pdf', etc.
				 *
				 * @since x.x
				 *
				 * @param string $base_url Empty string by default.
				 * @param array  $args {
				 *     @type int    $item_id   Content item ID.
				 *     @type string $type      Item type slug.
				 *     @type string $raw_token Raw access token.
				 * }
				 */
				$base_url = (string) apply_filters( 'frm_gated_content_item_url', '', compact( 'item_id', 'type', 'raw_token' ) );
		}

		if ( ! $base_url ) {
			return '';
		}

		return add_query_arg( 'access_code', $raw_token, $base_url );
	}

	/**
	 * Render an unordered list of gated access links for all items in an action.
	 *
	 * @since x.x
	 *
	 * @param array  $items     Array of item arrays from action settings (each with 'id' and 'type' keys).
	 * @param string $raw_token Raw access token.
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

			$label = get_the_title( (int) $item['id'] );
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
	 * @since x.x
	 *
	 * @param array  $items     Array of item arrays from action settings (each with 'id' and 'type' keys).
	 * @param string $raw_token Raw access token.
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

	/**
	 * Return the formatted expiry time for a raw token.
	 *
	 * @since x.x
	 *
	 * @param string $raw_token Raw access token.
	 * @return string Localised date/time string, or empty string if the token never expires or is not found.
	 */
	public static function get_formatted_expiry( $raw_token ) {
		return self::get_formatted_expiry_by_hash( hash( 'sha256', $raw_token ) );
	}

	/**
	 * Return the formatted expiry time for a token hash.
	 *
	 * @since x.x
	 *
	 * @param string $hash SHA-256 hex hash of the access token.
	 * @return string Localised date/time string, or empty string if the token never expires or is not found.
	 */
	private static function get_formatted_expiry_by_hash( $hash ) {
		$row = FrmGatedTokenHelper::get_row_by_hash( $hash );

		if ( ! $row || null === $row->expired_at ) {
			return '';
		}

		return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $row->expired_at );
	}
}
