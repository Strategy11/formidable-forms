<?php
/**
 * Gated Token Helper
 *
 * @package Formidable
 *
 * @since x.x
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmGatedTokenHelper {

	/**
	 * Per-request cache of raw tokens indexed by action_id.
	 * Populated by generate() so the same request can resolve the token
	 * without a transient round-trip.
	 *
	 * @var array<int, string>
	 */
	private static $generated_tokens = array();

	/**
	 * Generate a new access token for a gated content action and persist it.
	 *
	 * @param int      $action_id ID of the frm_form_actions post.
	 * @param int      $entry_id  ID of the submitted form entry.
	 * @param int|null $user_id   Logged-in user ID, or null for guests.
	 * @return string Raw 32-character token. Only ever stored in URLs or emails — never in the DB.
	 */
	public static function generate( $action_id, $entry_id, $user_id = null ) {
		global $wpdb;

		$raw_token  = wp_generate_password( 32, false );
		$token_hash = hash( 'sha256', $raw_token );
		$now        = time();

		// Read expired_hours from action settings to compute expiry timestamp.
		$expired_at = null;
		$action     = get_post( $action_id );
		if ( $action ) {
			$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
			if ( is_array( $settings ) && ! empty( $settings['expired_hours'] ) ) {
				$expired_at = $now + ( (int) $settings['expired_hours'] * 3600 );
			}
		}

		$data   = array(
			'token_hash' => $token_hash,
			'action_id'  => $action_id,
			'entry_id'   => $entry_id,
			'ip_address' => FrmAppHelper::get_ip_address(),
			'created_at' => $now,
		);
		$format = array( '%s', '%d', '%d', '%s', '%d' );

		// Only include nullable columns when they carry a value — passing null
		// with a %d format would insert 0 instead of NULL.
		if ( null !== $user_id ) {
			$data['user_id'] = $user_id;
			$format[]        = '%d';
		}

		if ( null !== $expired_at ) {
			$data['expired_at'] = $expired_at;
			$format[]           = '%d';
		}

		$wpdb->insert( $wpdb->prefix . 'frm_gated_tokens', $data, $format );

		// Cache in static variable for same-request shortcode rendering (no DB/cache round-trip).
		self::$generated_tokens[ $action_id ] = $raw_token;

		// Persist for shortcode rendering in a subsequent redirect request (5-min TTL).
		set_transient( self::get_token_transient_key( $action_id ), $raw_token, 5 * MINUTE_IN_SECONDS );

		return $raw_token;
	}

	/**
	 * Validate a token against a specific gated content item.
	 *
	 * @param string $token     Raw access token.
	 * @param int    $item_id   ID of the content item to check access for.
	 * @param string $item_type Type slug of the content item (e.g. 'page', 'frm_file').
	 * @return bool True if the token grants access to the item.
	 */
	public static function validate( $token, $item_id, $item_type ) {
		$row = self::get_row_by_token( $token );

		if ( null === $row ) {
			return false;
		}

		// Token is expired when expired_at is set and in the past.
		if ( null !== $row->expired_at && time() >= (int) $row->expired_at ) {
			return false;
		}

		$action   = get_post( (int) $row->action_id );
		$is_valid = self::action_contains_item( $action, $item_id, $item_type );

		/**
		 * Filter whether a gated content token is valid for an item.
		 *
		 * @param bool  $is_valid Whether the token grants access.
		 * @param array $args {
		 *     @type object $row       Token row from wp_frm_gated_tokens.
		 *     @type int    $item_id   Content item ID being checked.
		 *     @type string $item_type Content item type being checked.
		 * }
		 */
		return (bool) apply_filters( 'frm_gated_content_validate', $is_valid, compact( 'row', 'item_id', 'item_type' ) );
	}

	/**
	 * Delete all tokens associated with a gated content action.
	 *
	 * Called when the action post is permanently deleted so orphaned token rows
	 * do not accumulate in wp_frm_gated_tokens.
	 *
	 * @param int $action_id ID of the frm_form_actions post being deleted.
	 * @return void
	 */
	public static function delete_by_action( $action_id ) {
		global $wpdb;

		$wpdb->delete(
			$wpdb->prefix . 'frm_gated_tokens',
			array( 'action_id' => $action_id ),
			array( '%d' )
		);
	}

	/**
	 * Revoke all tokens for a specific action + entry pair.
	 *
	 * Called before re-generating a token on entry update so the previous
	 * token cannot be used after the entry owner receives a fresh one.
	 *
	 * @param int $action_id ID of the frm_form_actions post.
	 * @param int $entry_id  ID of the form entry.
	 * @return void
	 */
	public static function delete_by_action_and_entry( $action_id, $entry_id ) {
		global $wpdb;

		$wpdb->delete(
			$wpdb->prefix . 'frm_gated_tokens',
			array(
				'action_id' => $action_id,
				'entry_id'  => $entry_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * Revoke an access token by deleting it from the database.
	 *
	 * @param string $token Raw access token to revoke.
	 * @return void
	 */
	public static function revoke( $token ) {
		global $wpdb;

		$wpdb->delete(
			$wpdb->prefix . 'frm_gated_tokens',
			array( 'token_hash' => hash( 'sha256', $token ) ),
			array( '%s' )
		);
	}

	/**
	 * Delete all expired tokens from the database.
	 *
	 * Intended to be called by WP Cron on a scheduled interval.
	 *
	 * @return void
	 */
	public static function cleanup_expired() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM ' . $wpdb->prefix . 'frm_gated_tokens WHERE expired_at IS NOT NULL AND expired_at < %d',
				time()
			)
		);
	}

	/**
	 * Get a paginated list of tokens for a given action, ordered newest first.
	 *
	 * @param int $action_id Action post ID.
	 * @param int $limit     Maximum number of rows to return.
	 * @param int $offset    Number of rows to skip (for pagination).
	 * @return array Array of token row objects.
	 */
	public static function get_tokens_for_action( $action_id, $limit = 50, $offset = 0 ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'frm_gated_tokens WHERE action_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d',
				$action_id,
				$limit,
				$offset
			)
		);
		return is_array( $results ) ? $results : array();
	}

	/**
	 * Get all active (non-expired) tokens for a user, joined with action post data.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return array Array of token row objects, each with an `action_title` property from wp_posts.
	 */
	public static function get_tokens_for_user( $user_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT t.*, p.post_title AS action_title'
				. ' FROM ' . $wpdb->prefix . 'frm_gated_tokens t'
				. ' INNER JOIN ' . $wpdb->posts . ' p ON p.ID = t.action_id'
				. ' WHERE t.user_id = %d'
				. ' AND ( t.expired_at IS NULL OR t.expired_at > %d )'
				. ' ORDER BY t.created_at DESC',
				$user_id,
				time()
			)
		);
		return is_array( $results ) ? $results : array();
	}

	/**
	 * Retrieve a single token row by raw token string.
	 *
	 * @param string $token Raw access token.
	 * @return object|null Token row object, or null if not found or token has no match.
	 */
	public static function get_row_by_token( $token ) {
		return self::get_row_by_hash( hash( 'sha256', $token ) );
	}

	/**
	 * Retrieve a single token row by pre-computed SHA-256 hash.
	 *
	 * Use this when you already hold a hash (e.g. from a cookie) to avoid
	 * double-hashing.
	 *
	 * @param string $hash Hex-encoded SHA-256 hash of the raw token.
	 * @return object|null Token row object, or null if not found.
	 */
	public static function get_row_by_hash( $hash ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'frm_gated_tokens WHERE token_hash = %s LIMIT 1',
				$hash
			)
		);
		return is_object( $row ) ? $row : null;
	}

	/**
	 * Validate a pre-computed token hash against a specific gated content item.
	 *
	 * Identical logic to validate() but accepts an already-hashed value,
	 * avoiding double-hashing when the caller resolved the token from a cookie.
	 *
	 * @param string      $hash      SHA-256 hex hash of the raw access token.
	 * @param int         $item_id   ID of the content item to check access for.
	 * @param string      $item_type Type slug of the content item (e.g. 'page', 'frm_file').
	 * @param object|null $row       Optional pre-fetched token row. Skips the DB lookup when provided.
	 * @return bool True if the hash grants access to the item.
	 */
	public static function validate_hash( $hash, $item_id, $item_type, $row = null ) {
		if ( null === $row ) {
			$row = self::get_row_by_hash( $hash );
		}

		if ( null === $row ) {
			return false;
		}

		if ( null !== $row->expired_at && time() >= (int) $row->expired_at ) {
			return false;
		}

		$action   = get_post( (int) $row->action_id );
		$is_valid = self::action_contains_item( $action, $item_id, $item_type );

		/** This filter is documented in classes/helpers/FrmGatedTokenHelper.php */
		return (bool) apply_filters( 'frm_gated_content_validate', $is_valid, compact( 'row', 'item_id', 'item_type' ) );
	}

	/**
	 * Check whether an action's settings include a specific content item.
	 *
	 * @param WP_Post|null $action    Action post object, or null if not found.
	 * @param int          $item_id   Content item ID to look for.
	 * @param string       $item_type Content item type slug to match.
	 *
	 * @return bool True if the item is listed in the action's items setting.
	 */
	private static function action_contains_item( $action, $item_id, $item_type ) {
		if ( ! $action ) {
			return false;
		}

		$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
		if ( ! is_array( $settings ) || empty( $settings['items'] ) ) {
			return false;
		}

		foreach ( $settings['items'] as $item ) {
			if ( is_array( $item ) && (int) $item['id'] === $item_id && $item['type'] === $item_type ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Set an HttpOnly cookie that stores a token hash for a gated content action.
	 *
	 * Cookie name : frm_gc_{action_id}
	 * Cookie value: 64-char hex SHA-256 hash of the raw token
	 *
	 * @param int      $action_id  Action post ID.
	 * @param string   $hash       SHA-256 hex hash to store.
	 * @param int|null $expired_at Unix timestamp for cookie expiry, or null for session+1-year.
	 * @return void
	 */
	public static function set_cookie( $action_id, $hash, $expired_at = null ) {
		$expiry = null !== $expired_at ? $expired_at : ( time() + YEAR_IN_SECONDS );

		setcookie(
			'frm_gc_' . $action_id,
			$hash,
			array(
				'expires'  => $expiry,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * Build the transient key for a generated token, scoped to the current user or IP.
	 *
	 * Logged-in users are keyed by user ID; guests are keyed by an MD5 of their IP so
	 * that two users submitting the same form simultaneously cannot read each other's
	 * pending token.
	 *
	 * @param int $action_id Action post ID.
	 * @return string Transient key, at most ~52 characters.
	 */
	private static function get_token_transient_key( $action_id ) {
		$user_id = get_current_user_id();
		$scope   = $user_id ? (string) $user_id : hash( 'sha256', FrmAppHelper::get_ip_address() );
		return 'frm_gc_token_' . $action_id . '_' . $scope;
	}

	/**
	 * Retrieve the raw token most recently generated for a given action in this session.
	 *
	 * Reads the 5-minute transient set by generate(). Returns null when no pending
	 * token exists — e.g. a different user, a different browser/IP, or the TTL has
	 * expired.
	 *
	 * @param int $action_id Action post ID.
	 * @return string|null Raw 48-char token, or null if unavailable.
	 */
	public static function get_raw_token_for_action( $action_id ) {
		if ( isset( self::$generated_tokens[ $action_id ] ) ) {
			return self::$generated_tokens[ $action_id ];
		}

		$token = get_transient( self::get_token_transient_key( $action_id ) );
		return false !== $token ? (string) $token : null;
	}

	/**
	 * Resolve the active token hash for a gated content action.
	 *
	 * Resolution order:
	 *  1. 5-minute transient set by generate() — survives payment redirects.
	 *  2. `access_code` URL query parameter (raw token → hashed → validated).
	 *  3. HttpOnly cookie `frm_gc_{action_id}` (stores hash directly).
	 *
	 * Returns a SHA-256 hex hash string on success, or null when no valid token is
	 * found. Callers should pass the hash to self::validate_hash() to confirm it
	 * grants access to a specific content item.
	 *
	 * @param int $action_id Optional. Restrict resolution to a specific action post ID.
	 *                       When 0 the URL-param path still works (action_id is read
	 *                       from the token row); cookie path is skipped.
	 * @return string|null Token hash, or null if no valid token could be resolved.
	 */
	public static function obtain_token( $action_id = 0 ) {
		// 1. Transient set by self::generate() — persists across payment redirects (5-min TTL).
		if ( $action_id ) {
			$raw = self::get_raw_token_for_action( $action_id );
			if ( null !== $raw ) {
				return hash( 'sha256', $raw );
			}
		}

		// 2. URL query parameter: ?access_code=<raw_token>
		$url_token = FrmAppHelper::simple_get( 'access_code' );
		if ( '' !== $url_token ) {
			$hash = hash( 'sha256', $url_token );
			$row  = self::get_row_by_hash( $hash );

			$row_action_id = $row ? (int) $row->action_id : 0;
			$row_active    = $row && ( null === $row->expired_at || time() < (int) $row->expired_at );

			if ( $row_active && ( ! $action_id || $row_action_id === $action_id ) ) {
				// Only set the cookie when headers have not yet been sent. Headers are
				// already sent when obtain_token() is called during shortcode rendering
				// (e.g. show="expired_time" on a page that also has the access_code
				// param but for a different action). maybe_unlock_post() handles cookie
				// setting on the 'wp' hook where headers are always available.
				if ( ! headers_sent() ) {
					self::set_cookie( $row_action_id, $hash, $row->expired_at );
				}
				return $hash;
			}
		}

		// Cookie path requires a known action_id.
		if ( ! $action_id ) {
			return null;
		}

		// 3. HttpOnly cookie set on a previous URL-param or transient hit.
		$cookie_name = 'frm_gc_' . $action_id;
		$cookie_hash = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_text_field( $_COOKIE[ $cookie_name ] ) : '';

		if ( '' !== $cookie_hash ) {
			$row = self::get_row_by_hash( $cookie_hash );

			if ( $row && ( null === $row->expired_at || time() < (int) $row->expired_at ) ) {
				return $cookie_hash;
			}
		}

		return null;
	}
}
