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
	 * Attempt to unlock a password-protected post using a gated content token.
	 *
	 * Hooked on 'wp' so get_queried_object_id() is available (query is parsed
	 * by the time 'wp' fires, unlike 'init').
	 *
	 * Resolution order:
	 *  1. URL query parameter access_code (raw token → hashed via obtain_token).
	 *  2. Any frm_gc_* cookie whose hash validates against the current post.
	 *
	 * Pages must be WordPress password-protected to use this mechanism. The
	 * gated token replaces the password — visitors never need to know the actual
	 * WordPress post password.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function maybe_unlock_post() {
		$post_id = (int) get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		// Try URL query parameter first (obtain_token with no action_id uses URL param only).
		$hash = FrmGatedTokenHelper::obtain_token();

		// No URL param — scan frm_gc_* cookies to find one that grants access to this page.
		if ( ! $hash ) {
			$hash = self::find_valid_cookie_hash_for_post( $post_id );
		}

		if ( ! $hash ) {
			return;
		}

		if ( FrmGatedTokenHelper::validate_hash( $hash, $post_id, 'page' ) ) {
			// Unlock this post for the current request only.
			self::$unlocked_post_id = $post_id;
			add_filter( 'post_password_required', 'FrmGatedContentController::filter_password_required', 10, 2 );

			// Refresh the frm_gc_ cookie so subsequent visits skip the URL param.
			$row = FrmGatedTokenHelper::get_row_by_hash( $hash );
			if ( $row ) {
				FrmGatedTokenHelper::set_cookie( (int) $row->action_id, $hash, $row->expired_at );
			}
			return;
		}

		// Token present but invalid (expired or revoked) — redirect if action configured it.
		$row = FrmGatedTokenHelper::get_row_by_hash( $hash );
		if ( ! $row ) {
			return;
		}

		$action = get_post( (int) $row->action_id );
		if ( ! $action ) {
			return;
		}

		$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
		if ( ! empty( $settings['show_form_page'] ) ) {
			wp_safe_redirect( (string) get_permalink( (int) $settings['show_form_page'] ) );
			exit;
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
		if ( (int) $post->ID === self::$unlocked_post_id ) {
			return false;
		}
		return $required;
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
	 * so the raw token is already in FrmGatedTokenHelper::$tokens when those
	 * actions process [frm_gated_content] shortcodes on the same request.
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
		$user_id = get_current_user_id() ?: null;
		FrmGatedTokenHelper::generate( $action->ID, $entry->id, $user_id );
	}

	/**
	 * Handle the [frm_gated_content] shortcode.
	 *
	 * Attributes:
	 *  - id   (required) Action post ID.
	 *  - item (optional) 0-indexed item position. Omit to render the full item list.
	 *  - show (optional) 'url' (default) | 'access_token' | 'expired_time'.
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
				'show' => 'url',
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

		// All other outputs require the raw token.
		$raw_token = self::resolve_raw_token( $action_id );
		if ( null === $raw_token ) {
			return '';
		}

		if ( 'access_token' === $atts['show'] ) {
			return esc_html( $raw_token );
		}

		// show="url" (default).
		$action = get_post( $action_id );
		if ( ! $action ) {
			return '';
		}

		$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
		$items    = ( is_array( $settings ) && ! empty( $settings['items'] ) ) ? $settings['items'] : array();

		if ( false === $atts['item'] ) {
			return self::render_item_list( $items, $raw_token );
		}

		$idx = (int) $atts['item'];
		if ( ! isset( $items[ $idx ] ) || ! is_array( $items[ $idx ] ) ) {
			return '';
		}

		$item = $items[ $idx ];
		if ( empty( $item['id'] ) || empty( $item['type'] ) ) {
			return '';
		}

		return esc_url( self::get_item_url( (int) $item['id'], $item['type'], $raw_token ) );
	}

	/**
	 * Resolve the raw token for an action from same-request cache or URL parameter.
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
		// Same-request static cache populated by FrmGatedTokenHelper::generate().
		if ( isset( FrmGatedTokenHelper::$tokens[ $action_id ] ) ) {
			return FrmGatedTokenHelper::$tokens[ $action_id ];
		}

		// URL query parameter hit (e.g. visitor clicking a gated link from email).
		$candidate = FrmAppHelper::simple_get( 'access_code' );
		if ( '' !== $candidate ) {
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
				$base_url = (string) get_permalink( (int) $item_id );
				break;
			default:
				/**
				 * Filter the base URL for a gated content item type.
				 *
				 * Pro add-ons use this to support 'frm_file', 'frm_pdf', etc.
				 *
				 * @since x.x
				 *
				 * @param string $base_url  Empty string by default.
				 * @param int    $item_id   Content item ID.
				 * @param string $type      Item type slug.
				 * @param string $raw_token Raw access token.
				 */
				$base_url = (string) apply_filters( 'frm_gated_content_item_url', '', (int) $item_id, $type, $raw_token );
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
