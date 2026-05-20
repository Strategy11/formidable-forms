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

		$any_post_item = FrmGatedItem::make(
			array(
				'type' => 'post',
				'id'   => 0,
			)
		);

		if ( ! FrmGatedTokenHelper::get_valid_token( $any_post_item ) ) {
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
		$from_url_param = FrmAppHelper::simple_get( 'access_code' );

		$post_item   = FrmGatedItem::make(
			array(
				'type' => 'post',
				'id'   => $post_id,
			)
		);
		$valid_token = FrmGatedTokenHelper::get_valid_token( $post_item );

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
		return $post->ID === self::$unlocked_post_id ? false : $required;
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
		FrmGatedTokenHelper::generate( $action, $entry, $event );
	}
}
