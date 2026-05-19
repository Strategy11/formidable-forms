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
	 * Per-request cache of token rows indexed by SHA-256 hash.
	 * Populated by get_row_by_hash() so multiple shortcodes for the same
	 * action on one page do not each issue a separate DB query.
	 *
	 * @var array<string, object|null>
	 */
	private static $row_cache = array();

	/**
	 * Generate a new access token for a gated content action and persist it.
	 *
	 * @param int      $action_id ID of the frm_form_actions post.
	 * @param int      $entry_id  ID of the submitted form entry.
	 * @param int|null $user_id   Logged-in user ID, or null for guests.
	 *
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

		$hash = hash( 'sha256', $token );

		$wpdb->delete(
			$wpdb->prefix . 'frm_gated_tokens',
			array( 'token_hash' => $hash ),
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
	 * Get all token rows for a given user.
	 *
	 * @param int $user_id WordPress user ID.
	 *
	 * @return object[]
	 */
	public static function get_tokens_for_user( $user_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'frm_gated_tokens WHERE user_id = %d',
				$user_id
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
		if ( array_key_exists( $hash, self::$row_cache ) ) {
			return self::$row_cache[ $hash ];
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'frm_gated_tokens WHERE token_hash = %s LIMIT 1',
				$hash
			)
		);

		self::$row_cache[ $hash ] = is_object( $row ) ? $row : null;

		return self::$row_cache[ $hash ];
	}

	/**
	 * Remove a token row from the per-request cache.
	 *
	 * Call this after mutating a token row (e.g. extending expiry or renewing
	 * the hash) so subsequent get_row_by_hash() calls re-fetch from the DB.
	 *
	 * @param string $hash SHA-256 hex hash of the token row to evict.
	 *
	 * @return void
	 */
	public static function forget_cached_row( $hash ) {
		unset( self::$row_cache[ $hash ] );
	}

	/**
	 * Validate a raw access code against a specific gated content item.
	 *
	 * Hashes the code, fetches the matching DB row, then delegates to
	 * FrmGatedToken::validate() — which enforces expiry, item-membership, and the
	 * frm_gated_content_validate filter.
	 *
	 * @param string $access_code Raw access token (same value as the access_code URL parameter).
	 * @param string $item_type   Content item type slug (e.g. 'post', 'frm_file'). Pass empty string to skip item check.
	 * @param int|string $item_id     Content item ID (post ID, attachment ID, …). Pass 0 to skip item check.
	 *
	 * @return FrmGatedToken|null Validated token object, or null if the code is invalid or does not grant access.
	 */
	public static function validate_access_code( $access_code, $item_type = '', $item_id = 0 ) {
		$row = self::get_row_by_hash( hash( 'sha256', $access_code ) );
		if ( null === $row ) {
			return null;
		}
		$token = new FrmGatedToken( $row );
		return $token->validate( $item_id, $item_type ) ? $token : null;
	}

	/**
	 * Build the transient key for an action + item membership result.
	 *
	 * Format: frm_gc_ac_{action_id}_{item_type}_{item_id}
	 *
	 * @param int        $action_id ID of the frm_form_actions post.
	 * @param string     $item_type Content item type slug.
	 * @param int|string $item_id   Content item ID.
	 *
	 * @return string
	 */
	private static function get_action_item_transient_key( $action_id, $item_type, $item_id ) {
		return 'frm_gc_ac_' . $action_id . '_' . $item_type . '_' . $item_id;
	}

	/**
	 * Delete the cached membership result for every item listed in an action's settings.
	 *
	 * Call this when an action is updated or deleted so stale results are not served.
	 *
	 * @param int $action_id ID of the frm_form_actions post.
	 *
	 * @return void
	 */
	public static function delete_action_item_cache( $action_id ) {
		$action = get_post( $action_id );
		if ( ! $action ) {
			return;
		}

		$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
		if ( ! is_array( $settings ) || empty( $settings['items'] ) ) {
			return;
		}

		foreach ( $settings['items'] as $item ) {
			if ( ! empty( $item['type'] ) && ! empty( $item['id'] ) ) {
				delete_transient( self::get_action_item_transient_key( $action_id, $item['type'], $item['id'] ) );
			}
		}
	}

	/**
	 * Check whether an action's settings include a specific content item.
	 *
	 * The result is cached in a transient keyed to the action + item pair so
	 * subsequent requests skip the DB lookup. TTL matches the token's remaining
	 * lifetime, or DAY_IN_SECONDS when no expiry is provided.
	 *
	 * @param int        $action_id  ID of the frm_form_actions post.
	 * @param string     $item_type  Content item type slug to match.
	 * @param int|string $item_id    Content item ID to look for.
	 * @param int|null   $expired_at Token expiry timestamp used to set TTL, or null for no expiry.
	 *
	 * @return bool True if the item is listed in the action's items setting.
	 */
	public static function action_contains_item( $action_id, $item_type, $item_id, $expired_at = null ) {
		$key    = self::get_action_item_transient_key( $action_id, $item_type, $item_id );
		$cached = get_transient( $key );

		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$action = get_post( $action_id );
		if ( ! $action ) {
			return false;
		}

		$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
		$result   = false;
		if ( is_array( $settings ) && ! empty( $settings['items'] ) ) {
			foreach ( $settings['items'] as $item ) {
				if ( is_array( $item ) && (string) $item['id'] === (string) $item_id && $item['type'] === $item_type ) {
					$result = true;
					break;
				}
			}
		}

		$ttl = null !== $expired_at ? max( 1, $expired_at - time() ) : DAY_IN_SECONDS;
		set_transient( $key, $result, $ttl );

		return $result;
	}

	/**
	 * Set an HttpOnly cookie that stores a raw access token for a gated content item.
	 *
	 * Cookie name  : frm_gc_{item_type}_{item_id}  — when item ID is known.
	 *              : frm_gc_{item_type}             — when only type is known (e.g. frm_pdf).
	 * Cookie value : raw access token (same value passed in the access_code URL parameter).
	 *
	 * Storing the raw token lets users verify that the cookie matches the link they
	 * received, and lets get_valid_token_from_cookies() look up the DB by
	 * hash('sha256', $raw_token) — the same query as the URL-param path.
	 * The token is always re-validated against the DB on every request; the cookie
	 * is used only as a transport, never trusted on its own.
	 *
	 * @param string   $raw_token  Raw access token to store.
	 * @param int|null $expired_at Unix timestamp for cookie expiry, or null for 1-year TTL.
	 * @param string   $item_type  Content item type slug (e.g. 'post', 'frm_file').
	 * @param int|string $item_id    Content item ID, or 0 when not applicable.
	 * @return void
	 */
	public static function set_cookie( $raw_token, $expired_at = null, $item_type = '', $item_id = 0 ) {
		if ( ! $item_type ) {
			return; // Cannot build a meaningful cookie name without at least a type.
		}

		$cookie_name = $item_id
			? 'frm_gc_' . $item_type . '_' . $item_id
			: 'frm_gc_' . $item_type;

		$expiry = null !== $expired_at ? $expired_at : ( time() + YEAR_IN_SECONDS );

		setcookie(
			$cookie_name,
			$raw_token,
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
	 * @return string Transient key (~30 chars for logged-in users, ~90 chars for guests).
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
	 * Find the first valid access token for a gated content item.
	 *
	 * Checks sources in priority order, returning immediately on the first match:
	 *  1. `access_code` URL query parameter (raw token → hashed → validated).
	 *  2. HttpOnly `frm_gc_*` cookies — one cookie per gated content item, keyed by
	 *     item type and ID. When both are known, a single named lookup is used;
	 *     otherwise all matching cookies are scanned.
	 *  3. All active DB tokens for the current logged-in user. Supports registration-
	 *     gated content where the Registration add-on stores the new user ID on the
	 *     token so returning visitors are recognised without a URL param or cookie.
	 *  4. `frm_obtain_gated_token` filter — add-ons can supply a token from other sources.
	 *
	 * Note: the 5-minute static-cache / transient path is intentionally excluded —
	 * it is action-scoped and only meaningful for shortcode rendering immediately
	 * after token generation. Use get_raw_token_for_action() for that purpose.
	 *
	 * @param int|string $item_id   Content item ID (post ID, attachment ID, …).
	 * @param string $item_type Content item type slug (e.g. 'post', 'frm_file').
	 *
	 * @return FrmGatedToken|null First valid token, or null if none found.
	 */
	public static function get_valid_token( $item_id = 0, $item_type = '' ) {
		// 1. URL query parameter — definitive.
		$token = self::get_valid_token_from_url_param( $item_id, $item_type );
		if ( null !== $token ) {
			return $token;
		}

		// 2 & 3. Cookies then user DB (shared dedup).
		$seen_hashes = array();

		$token = self::get_valid_token_from_cookies( $item_id, $item_type, $seen_hashes );
		if ( null !== $token ) {
			return $token;
		}

		$token = self::get_valid_token_from_user( $item_id, $item_type, $seen_hashes );
		if ( null !== $token ) {
			return $token;
		}

		/**
		 * Filter the resolved valid token for a gated content item.
		 *
		 * Fires after URL param, cookies, and user DB have all been checked without finding
		 * a valid token. Add-ons can return a validated FrmGatedToken to grant access from
		 * alternative sources, or null to indicate no token is available.
		 *
		 * @since x.x
		 *
		 * @param FrmGatedToken|null $token     Null — no valid token found by core.
		 * @param array              $args {
		 *     @type int|string $item_id   Content item ID being accessed (0 if unknown).
		 *     @type string $item_type Content item type slug (empty if unknown).
		 * }
		 */
		return apply_filters( 'frm_obtain_gated_token', null, compact( 'item_id', 'item_type' ) );
	}

	/**
	 * Find a valid token from the `access_code` URL query parameter.
	 *
	 * On success, sets an frm_gc_* cookie keyed to the validated item so that
	 * subsequent requests can skip this path entirely. The raw token is stored
	 * as the cookie value so users can verify it matches their access link.
	 *
	 * @param int|string $item_id   Content item ID.
	 * @param string $item_type Content item type slug.
	 *
	 * @return FrmGatedToken|null
	 */
	private static function get_valid_token_from_url_param( $item_id, $item_type ) {
		$url_token = FrmAppHelper::simple_get( 'access_code' );
		if ( '' === $url_token ) {
			return null;
		}

		$token = self::validate_access_code( $url_token, $item_type, $item_id );
		if ( null === $token ) {
			return null;
		}

		// Cookie is set after validation so the name is scoped to the exact item
		// that was just granted access. Store the raw token so it matches the
		// access_code URL parameter — easier to verify and simpler to look up.
		if ( ! headers_sent() ) {
			self::set_cookie( $url_token, $token->get_expired_at(), $item_type, $item_id );
		}

		return $token;
	}

	/**
	 * Find a valid token from frm_gc_* cookies.
	 *
	 * When both item_type and item_id are known, performs a direct O(1) cookie name
	 * lookup (frm_gc_{item_type}_{item_id}) — no iteration required. Otherwise scans
	 * all cookies whose names start with the appropriate prefix.
	 *
	 * Populates $seen_hashes with every hash examined so the caller can pass it to
	 * subsequent sources to avoid processing the same row twice.
	 *
	 * @param int|string $item_id     Content item ID.
	 * @param string $item_type   Content item type slug.
	 * @param array  $seen_hashes Dedup map passed by reference.
	 *
	 * @return FrmGatedToken|null
	 */
	private static function get_valid_token_from_cookies( $item_id, $item_type, &$seen_hashes ) {
		if ( $item_type && $item_id ) {
			// Direct lookup — skip every other cookie without touching the DB.
			$cookie_name = 'frm_gc_' . $item_type . '_' . $item_id;
			if ( ! isset( $_COOKIE[ $cookie_name ] ) ) {
				return null;
			}
			$raw_token            = sanitize_text_field( $_COOKIE[ $cookie_name ] );
			$seen_hashes[ hash( 'sha256', $raw_token ) ] = true;
			return self::validate_access_code( $raw_token, $item_type, $item_id );
		}

		// Partial or no context — scan cookies whose names share the type prefix.
		// e.g. item_type='post' matches frm_gc_post and frm_gc_post_*.
		$prefix = $item_type ? 'frm_gc_' . $item_type : 'frm_gc_';
		foreach ( $_COOKIE as $name => $value ) {
			if ( 0 !== strpos( $name, $prefix ) ) {
				continue;
			}
			$raw_token = sanitize_text_field( $value );
			$hash      = hash( 'sha256', $raw_token );
			if ( isset( $seen_hashes[ $hash ] ) ) {
				continue;
			}
			$seen_hashes[ $hash ] = true;
			$token                = self::validate_access_code( $raw_token, $item_type, $item_id );
			if ( null !== $token ) {
				return $token;
			}
		}
		return null;
	}

	/**
	 * Find a valid token from DB rows belonging to the current logged-in user.
	 *
	 * Skips hashes already seen in earlier sources via $seen_hashes.
	 *
	 * @param int|string $item_id     Content item ID.
	 * @param string $item_type   Content item type slug.
	 * @param array  $seen_hashes Dedup map passed by reference.
	 *
	 * @return FrmGatedToken|null
	 */
	private static function get_valid_token_from_user( $item_id, $item_type, &$seen_hashes ) {
		if ( ! is_user_logged_in() ) {
			return null;
		}

		foreach ( self::get_tokens_for_user( get_current_user_id() ) as $row ) {
			if ( isset( $seen_hashes[ $row->token_hash ] ) ) {
				continue;
			}
			$seen_hashes[ $row->token_hash ] = true;
			$token                           = new FrmGatedToken( $row );
			if ( $token->validate( $item_id, $item_type ) ) {
				return $token;
			}
		}
		return null;
	}

}
