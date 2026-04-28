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
	 * Generate a new access token for a gated content action and persist it.
	 *
	 * @since x.x
	 *
	 * @param int      $action_id ID of the frm_form_actions post.
	 * @param int      $entry_id  ID of the submitted form entry.
	 * @param int|null $user_id   Logged-in user ID, or null for guests.
	 * @return string Raw 48-character token. Only ever stored in URLs or emails — never in the DB.
	 */
	public static function generate( $action_id, $entry_id, $user_id = null ) {
		global $wpdb;

		$raw_token  = wp_generate_password( 48, false );
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

		// Persist for shortcode rendering in the same or a subsequent redirect request (5-min TTL).
		set_transient( self::get_token_transient_key( $action_id ), $raw_token, 5 * MINUTE_IN_SECONDS );

		return $raw_token;
	}

	/**
	 * Validate a token against a specific gated content item.
	 *
	 * @since x.x
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

		// Confirm the item is listed in the action's settings.
		$is_valid = false;
		$action   = get_post( (int) $row->action_id );

		if ( $action ) {
			$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
			if ( is_array( $settings ) && ! empty( $settings['items'] ) ) {
				foreach ( $settings['items'] as $item ) {
					if ( ! is_array( $item ) ) {
						continue;
					}
					if ( (int) $item['id'] === $item_id && $item['type'] === $item_type ) {
						$is_valid = true;
						break;
					}
				}
			}
		}

		/**
		 * Filter whether a gated content token is valid for an item.
		 *
		 * @since x.x
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
	 * @since x.x
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
	 * @since x.x
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
	 * @since x.x
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
	 * Extend a token's expiry by a given number of hours from now.
	 *
	 * @since x.x
	 *
	 * @param string $token Raw access token.
	 * @param int    $hours Number of hours to add from the current time.
	 * @return bool True if a matching token was found and updated, false otherwise.
	 */
	public static function extend( $token, $hours ) {
		global $wpdb;

		$updated = $wpdb->update(
			$wpdb->prefix . 'frm_gated_tokens',
			array( 'expired_at' => time() + ( $hours * 3600 ) ),
			array( 'token_hash' => hash( 'sha256', $token ) ),
			array( '%d' ),
			array( '%s' )
		);

		return 0 < $updated;
	}

	/**
	 * Renew a token by replacing its hash atomically, optionally updating expiry.
	 *
	 * @since x.x
	 *
	 * @param string   $old_token  Raw token to replace.
	 * @param int|null $new_expiry Unix timestamp for the new expiry, or null to keep the existing value.
	 * @return string New raw token.
	 */
	public static function renew( $old_token, $new_expiry = null ) {
		global $wpdb;

		$new_raw_token  = wp_generate_password( 48, false );
		$new_token_hash = hash( 'sha256', $new_raw_token );

		$data   = array( 'token_hash' => $new_token_hash );
		$format = array( '%s' );

		if ( null !== $new_expiry ) {
			$data['expired_at'] = $new_expiry;
			$format[]           = '%d';
		}

		$wpdb->update(
			$wpdb->prefix . 'frm_gated_tokens',
			$data,
			array( 'token_hash' => hash( 'sha256', $old_token ) ),
			$format,
			array( '%s' )
		);

		return $new_raw_token;
	}

	/**
	 * Delete all expired tokens from the database.
	 *
	 * Intended to be called by WP Cron on a scheduled interval.
	 *
	 * @since x.x
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
	 * @since x.x
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
	 * @since x.x
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
	 * @since x.x
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
	 * @since x.x
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
	 * @since x.x
	 *
	 * @param string $hash      SHA-256 hex hash of the raw access token.
	 * @param int    $item_id   ID of the content item to check access for.
	 * @param string $item_type Type slug of the content item (e.g. 'page', 'frm_file').
	 * @return bool True if the hash grants access to the item.
	 */
	public static function validate_hash( $hash, $item_id, $item_type ) {
		$row = self::get_row_by_hash( $hash );

		if ( null === $row ) {
			return false;
		}

		if ( null !== $row->expired_at && time() >= (int) $row->expired_at ) {
			return false;
		}

		$is_valid = false;
		$action   = get_post( (int) $row->action_id );

		if ( $action ) {
			$settings = FrmAppHelper::maybe_json_decode( $action->post_content );
			if ( is_array( $settings ) && ! empty( $settings['items'] ) ) {
				foreach ( $settings['items'] as $item ) {
					if ( ! is_array( $item ) ) {
						continue;
					}
					if ( (int) $item['id'] === $item_id && $item['type'] === $item_type ) {
						$is_valid = true;
						break;
					}
				}
			}
		}

		/** This filter is documented in classes/helpers/FrmGatedTokenHelper.php */
		return (bool) apply_filters( 'frm_gated_content_validate', $is_valid, compact( 'row', 'item_id', 'item_type' ) );
	}

	/**
	 * Find the newest active token row for a given action and IP address.
	 *
	 * Used as an IP-based fallback when a visitor's cookie hash no longer matches
	 * any row (e.g. after a token was renewed) but the same IP has a valid row.
	 *
	 * @since x.x
	 *
	 * @param int    $action_id Action post ID.
	 * @param string $ip        Visitor IP address.
	 * @return object|null Token row object, or null if no active row found.
	 */
	public static function get_active_row_by_action_and_ip( $action_id, $ip ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'frm_gated_tokens'
				. ' WHERE action_id = %d AND ip_address = %s'
				. ' AND ( expired_at IS NULL OR expired_at > %d )'
				. ' ORDER BY created_at DESC LIMIT 1',
				$action_id,
				$ip,
				time()
			)
		);
		return is_object( $row ) ? $row : null;
	}

	/**
	 * Set an HttpOnly cookie that stores a token hash for a gated content action.
	 *
	 * Cookie name : frm_gc_{action_id}
	 * Cookie value: 64-char hex SHA-256 hash of the raw token
	 *
	 * @since x.x
	 *
	 * @param int      $action_id  Action post ID.
	 * @param string   $hash       SHA-256 hex hash to store.
	 * @param int|null $expired_at Unix timestamp for cookie expiry, or null for session+1-year.
	 * @return void
	 */
	public static function set_cookie( $action_id, $hash, $expired_at = null ) {
		$expiry = null !== $expired_at ? $expired_at : ( time() + YEAR_IN_SECONDS );

		$parts = array(
			'frm_gc_' . $action_id . '=' . rawurlencode( $hash ),
			'Expires=' . gmdate( 'D, d M Y H:i:s T', $expiry ),
			'Path=/',
			'SameSite=Lax',
			'HttpOnly',
		);

		if ( is_ssl() ) {
			$parts[] = 'Secure';
		}

		header( 'Set-Cookie: ' . implode( '; ', $parts ), false );
	}

	/**
	 * Build the transient key for a generated token, scoped to the current user or IP.
	 *
	 * Logged-in users are keyed by user ID; guests are keyed by an MD5 of their IP so
	 * that two users submitting the same form simultaneously cannot read each other's
	 * pending token.
	 *
	 * @since x.x
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
	 * @since x.x
	 *
	 * @param int $action_id Action post ID.
	 * @return string|null Raw 48-char token, or null if unavailable.
	 */
	public static function get_raw_token_for_action( $action_id ) {
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
	 *  4. IP-address fallback when cookie hash is stale (e.g. after token renewal).
	 *
	 * Returns a SHA-256 hex hash string on success, or null when no valid token is
	 * found. Callers should pass the hash to self::validate_hash() to confirm it
	 * grants access to a specific content item.
	 *
	 * @since x.x
	 *
	 * @param int $action_id Optional. Restrict resolution to a specific action post ID.
	 *                       When 0 the URL-param path still works (action_id is read
	 *                       from the token row); cookie and IP paths are skipped.
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
				self::set_cookie( $row_action_id, $hash, $row->expired_at );
				return $hash;
			}
		}

		// Cookie and IP paths require a known action_id.
		if ( ! $action_id ) {
			return null;
		}

		// 3. HttpOnly cookie set on a previous URL-param hit.
		$cookie_name = 'frm_gc_' . $action_id;
		$cookie_hash = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_text_field( $_COOKIE[ $cookie_name ] ) : '';

		if ( '' !== $cookie_hash ) {
			$row = self::get_row_by_hash( $cookie_hash );

			if ( $row && ( null === $row->expired_at || time() < (int) $row->expired_at ) ) {
				return $cookie_hash;
			}

			// 4. Cookie hash is stale (token was renewed or deleted) — try IP fallback.
			$ip     = FrmAppHelper::get_ip_address();
			$ip_row = self::get_active_row_by_action_and_ip( $action_id, $ip );

			if ( $ip_row ) {
				// Refresh the cookie so the next request hits path 3 again.
				self::set_cookie( $action_id, $ip_row->token_hash, $ip_row->expired_at );
				return (string) $ip_row->token_hash;
			}
		}

		return null;
	}
}
