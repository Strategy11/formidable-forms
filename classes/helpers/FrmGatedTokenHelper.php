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
	 * @param int $user_id   WordPress user ID.
	 * @param int $action_id Optional. When non-zero, restricts results to this action.
	 * @return array Array of token row objects, each with an `action_title` property from wp_posts.
	 */
	public static function get_tokens_for_user( $user_id, $action_id = 0 ) {
		global $wpdb;

		$where  = 'WHERE t.user_id = %d AND ( t.expired_at IS NULL OR t.expired_at > %d )';
		$params = array( $user_id, time() );

		if ( $action_id ) {
			$where   .= ' AND t.action_id = %d';
			$params[] = $action_id;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT t.*, p.post_title AS action_title'
				. ' FROM ' . $wpdb->prefix . 'frm_gated_tokens t'
				. ' INNER JOIN ' . $wpdb->posts . ' p ON p.ID = t.action_id'
				. ' ' . $where
				. ' ORDER BY t.created_at DESC',
				$params
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

	// ── Validation transient cache ────────────────────────────────────────── //

	/**
	 * Build the transient key for a token + item validation result.
	 *
	 * Format: frm_gc_v_{hash}_{item_type}_{item_id}
	 * Max length ≈ 9 + 64 + 1 + 20 + 1 + 10 = 105 chars (well under WP's 172-char limit).
	 *
	 * @param string $hash      SHA-256 hex hash of the token.
	 * @param string $item_type Content item type slug.
	 * @param int    $item_id   Content item ID.
	 *
	 * @return string
	 */
	private static function get_validation_transient_key( $hash, $item_type, $item_id ) {
		return 'frm_gc_v_' . $hash . '_' . $item_type . '_' . (int) $item_id;
	}

	/**
	 * Return the cached validation result for a token + item pair.
	 *
	 * Only successful validations are cached — a cache hit always means valid.
	 * Returns null when no cached result exists (full validation required).
	 *
	 * @param string $hash      SHA-256 hex hash of the token.
	 * @param string $item_type Content item type slug.
	 * @param int    $item_id   Content item ID.
	 *
	 * @return array|null Cached value array on hit, null on miss.
	 */
	public static function get_cached_validation( $hash, $item_type, $item_id ) {
		$cached = get_transient( self::get_validation_transient_key( $hash, $item_type, $item_id ) );
		return false !== $cached ? $cached : null;
	}

	/**
	 * Cache a successful token + item validation result.
	 *
	 * TTL equals the remaining lifetime of the token (or DAY_IN_SECONDS for
	 * tokens that never expire). Action-update hooks delete the cache early when
	 * the action's item list changes.
	 *
	 * @param string   $hash      SHA-256 hex hash of the token.
	 * @param string   $item_type Content item type slug.
	 * @param int      $item_id   Content item ID.
	 * @param int|null $expired_at Token expiry timestamp, or null if it never expires.
	 * @param int      $action_id  Gated content action post ID.
	 *
	 * @return void
	 */
	public static function set_validation_cache( $hash, $item_type, $item_id, $expired_at, $action_id ) {
		$ttl = null !== $expired_at ? max( 1, (int) $expired_at - time() ) : DAY_IN_SECONDS;
		set_transient(
			self::get_validation_transient_key( $hash, $item_type, $item_id ),
			array(
				'item_type'  => $item_type,
				'item_id'    => $item_id,
				'expired_at' => $expired_at,
				'action_id'  => $action_id,
			),
			$ttl
		);
	}

	/**
	 * Delete validation cache entries for every token belonging to an action.
	 *
	 * Call this when a gated content action's settings are updated so stale
	 * item-membership results are not served from cache.
	 *
	 * @param int $action_id Gated content action post ID.
	 *
	 * @return void
	 */
	public static function delete_validation_cache_for_action( $action_id ) {
		$action = get_post( $action_id );
		if ( ! $action ) {
			return;
		}

		$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
		if ( ! is_array( $settings ) || empty( $settings['items'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = self::get_tokens_for_action( $action_id, 9999 );
		foreach ( $rows as $row ) {
			foreach ( $settings['items'] as $item ) {
				if ( ! empty( $item['type'] ) && ! empty( $item['id'] ) ) {
					delete_transient( self::get_validation_transient_key( $row->token_hash, $item['type'], (int) $item['id'] ) );
				}
			}
		}
	}

	/**
	 * Delete all validation cache entries for a single token.
	 *
	 * Looks up the token's action to know which item_type + item_id combinations
	 * may have been cached, then deletes each transient.
	 *
	 * Call this when a token is mutated (expiry extended, hash renewed) or when
	 * the token is found to be expired during real-time validation.
	 *
	 * @param string $hash      SHA-256 hex hash of the token.
	 * @param int    $action_id Optional. When already known, avoids an extra row lookup.
	 *
	 * @return void
	 */
	public static function delete_validation_cache_for_token( $hash, $action_id = 0 ) {
		if ( ! $action_id ) {
			$row = self::get_row_by_hash( $hash );
			if ( ! $row ) {
				return;
			}
			$action_id = (int) $row->action_id;
		}

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
				delete_transient( self::get_validation_transient_key( $hash, $item['type'], (int) $item['id'] ) );
			}
		}
	}

	/**
	 * Validate a pre-computed token hash against a specific gated content item.
	 *
	 * Convenience wrapper around FrmGatedToken::validate() for callers that
	 * already hold a hash rather than a token object.
	 *
	 * @param string      $hash      SHA-256 hex hash of the raw access token.
	 * @param int         $item_id   ID of the content item to check access for.
	 * @param string      $item_type Type slug of the content item (e.g. 'post', 'frm_file').
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

		return ( new FrmGatedToken( $row ) )->validate( $item_id, $item_type );
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
	public static function action_contains_item( $action, $item_id, $item_type ) {
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
	 * Cookie name  : frm_gc_{action_id}
	 * Cookie value : {hash} — legacy / URL-param flow (no item context).
	 *              : {hash}:{item_type}:{item_id} — when item context is known.
	 *
	 * Embedding item context lets find_valid_cookie_hash_for_* skip cookies that
	 * clearly do not cover the requested item without a DB round-trip.
	 *
	 * @param int      $action_id  Action post ID.
	 * @param string   $hash       SHA-256 hex hash to store.
	 * @param int|null $expired_at Unix timestamp for cookie expiry, or null for session+1-year.
	 * @param string   $item_type  Optional item type slug (e.g. 'post', 'frm_file').
	 * @param int      $item_id    Optional item post/attachment ID.
	 * @return void
	 */
	public static function set_cookie( $action_id, $hash, $expired_at = null, $item_type = '', $item_id = 0 ) {
		if ( $item_type && $item_id ) {
			$value = $hash . ':' . $item_type . ':' . $item_id;
		} elseif ( $item_type ) {
			$value = $hash . ':' . $item_type; // Type-only (e.g. frm_pdf — no fixed item ID).
		} else {
			$value = $hash;
		}
		$expiry = null !== $expired_at ? $expired_at : ( time() + YEAR_IN_SECONDS );

		setcookie(
			'frm_gc_' . $action_id,
			$value,
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
	 * Extract the SHA-256 hash from a frm_gc_* cookie value.
	 *
	 * Handles both the legacy plain-hash format and the extended
	 * {hash}:{item_type}:{item_id} format. The hash is always the segment
	 * before the first colon (or the whole string when no colon is present).
	 *
	 * @param string $value Raw cookie value.
	 * @return string SHA-256 hex hash.
	 */
	public static function parse_hash_from_cookie_value( $value ) {
		$colon = strpos( $value, ':' );
		return false !== $colon ? substr( $value, 0, $colon ) : $value;
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
	 * Find the first valid access token for a gated content item.
	 *
	 * Checks sources in priority order, returning immediately on the first match:
	 *  1. `access_code` URL query parameter (raw token → hashed → validated).
	 *  2. All HttpOnly `frm_gc_*` cookies — one cookie per gated content action.
	 *  3. All active DB tokens for the current logged-in user. Supports registration-
	 *     gated content where the Registration add-on stores the new user ID on the
	 *     token so returning visitors are recognised without a URL param or cookie.
	 *  4. `frm_obtain_gated_token` filter — add-ons can supply a token from other sources.
	 *
	 * Note: the 5-minute static-cache / transient path is intentionally excluded —
	 * it is action-scoped and only meaningful for shortcode rendering immediately
	 * after token generation. Use get_raw_token_for_action() for that purpose.
	 *
	 * @param int    $item_id   Content item ID (post ID, attachment ID, …).
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
		 *     @type int    $item_id   Content item ID being accessed (0 if unknown).
		 *     @type string $item_type Content item type slug (empty if unknown).
		 * }
		 */
		return apply_filters( 'frm_obtain_gated_token', null, compact( 'item_id', 'item_type' ) );
	}

	/**
	 * Find a valid token from the `access_code` URL query parameter.
	 *
	 * @param int    $item_id   Content item ID.
	 * @param string $item_type Content item type slug.
	 *
	 * @return FrmGatedToken|null
	 */
	private static function get_valid_token_from_url_param( $item_id, $item_type ) {
		$hash = self::get_token_hash_from_url_param();
		if ( null === $hash ) {
			return null;
		}
		$row = self::get_row_by_hash( $hash );
		if ( ! $row ) {
			return null;
		}
		$token = new FrmGatedToken( $row );
		return $token->validate( $item_id, $item_type ) ? $token : null;
	}

	/**
	 * Find a valid token from all frm_gc_* cookies.
	 *
	 * Populates $seen_hashes with every hash examined so the caller can pass it to
	 * subsequent sources to avoid processing the same row twice.
	 *
	 * @param int    $item_id     Content item ID.
	 * @param string $item_type   Content item type slug.
	 * @param array  $seen_hashes Dedup map passed by reference.
	 *
	 * @return FrmGatedToken|null
	 */
	private static function get_valid_token_from_cookies( $item_id, $item_type, &$seen_hashes ) {
		foreach ( $_COOKIE as $name => $value ) {
			if ( 0 !== strpos( $name, 'frm_gc_' ) ) {
				continue;
			}
			$hash = self::parse_hash_from_cookie_value( sanitize_text_field( $value ) );
			if ( isset( $seen_hashes[ $hash ] ) ) {
				continue;
			}
			$seen_hashes[ $hash ] = true;
			$row                  = self::get_row_by_hash( $hash );
			if ( ! $row ) {
				continue;
			}
			$token = new FrmGatedToken( $row );
			if ( $token->validate( $item_id, $item_type ) ) {
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
	 * @param int    $item_id     Content item ID.
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

	/**
	 * Resolve a token hash from the `access_code` URL query parameter.
	 *
	 * When a valid raw token is found the corresponding cookie is set immediately
	 * (unless headers are already sent) so subsequent requests skip this path.
	 *
	 * @return string|null SHA-256 hex hash, or null when not found or invalid.
	 */
	private static function get_token_hash_from_url_param() {
		$url_token = FrmAppHelper::simple_get( 'access_code' );
		if ( '' === $url_token ) {
			return null;
		}

		$hash = hash( 'sha256', $url_token );
		$row  = self::get_row_by_hash( $hash );

		if ( ! $row ) {
			return null;
		}

		if ( null !== $row->expired_at && time() >= (int) $row->expired_at ) {
			return null;
		}

		// Only set the cookie when headers have not yet been sent. Headers are
		// already sent when get_valid_token() is called during shortcode rendering.
		// maybe_unlock_post() handles cookie setting on the 'wp' hook where
		// headers are always available.
		if ( ! headers_sent() ) {
			self::set_cookie( (int) $row->action_id, $hash, $row->expired_at );
		}

		return $hash;
	}

}
