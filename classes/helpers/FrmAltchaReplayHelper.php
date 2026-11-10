<?php
/**
 * Single-use tracking for ALTCHA solutions.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Remembers spent challenges until they expire, so one solution cannot create many entries.
 *
 * The replay table stores only a binary challenge hash and its expiration. Its primary key
 * makes claims atomic, so two requests with the same payload cannot both succeed.
 *
 * @since x.x
 */
class FrmAltchaReplayHelper {

	/**
	 * Cron hook that deletes expired rows.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const PURGE_HOOK = 'frm_altcha_purge_used';

	/**
	 * Maximum number of expired rows deleted in one query.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const PURGE_BATCH_SIZE = 1000;

	/**
	 * Tables confirmed to exist during this request, keyed by the full site table name.
	 *
	 * @since x.x
	 *
	 * @var array<string, bool>
	 */
	private static $ready_tables = array();

	/**
	 * Get the replay table for the current site.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'frm_altcha_replays';
	}

	/**
	 * Check storage without creating a table on sites that have not used ALTCHA.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function table_exists() {
		$table = self::table_name();

		if ( isset( self::$ready_tables[ $table ] ) ) {
			return true;
		}

		global $wpdb;
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );

		if ( null !== $exists ) {
			self::$ready_tables[ $table ] = true;
		}

		return null !== $exists;
	}

	/**
	 * Create replay storage only while claiming the first solved challenge.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function create_table() {
		global $wpdb;
		$table   = self::table_name();
		$created = $wpdb->query(
			$wpdb->prepare(
				'CREATE TABLE IF NOT EXISTS %i (
					challenge_hash binary(32) NOT NULL,
					expires_at BIGINT UNSIGNED NOT NULL,
					PRIMARY KEY (challenge_hash),
					KEY expires_at (expires_at)
				)',
				$table
			)
		);

		if ( false === $created ) {
			return false;
		}

		self::$ready_tables[ $table ] = true;
		return true;
	}

	/**
	 * Whether a challenge was already spent.
	 *
	 * @since x.x
	 *
	 * @param string $id Challenge ID from FrmAltchaChallengeHelper::verify().
	 *
	 * @return bool
	 */
	public static function is_used( $id ) {
		if ( ! self::is_valid_id( $id ) ) {
			return false;
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! self::table_exists() ) {
			return false;
		}

		// Not FrmDb::get_var(), which caches the result. A challenge claimed earlier in the same
		// request, or by a parallel request, must never read as unused.

		$found = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( 'SELECT 1 FROM %i WHERE challenge_hash = UNHEX(%s)', $wpdb->prefix . 'frm_altcha_replays', $id )
		);

		return null !== $found;
	}

	/**
	 * Mark a challenge as spent.
	 *
	 * @since x.x
	 *
	 * @param string $id      Challenge ID from FrmAltchaChallengeHelper::verify().
	 * @param int    $expires Unix time the challenge expires. The row can be deleted after that.
	 *
	 * @return bool False when the challenge was already spent.
	 */
	public static function claim( $id, $expires ) {
		if ( ! self::is_valid_id( $id ) ) {
			return false;
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! self::table_exists() && ! self::create_table() ) {
			return false;
		}

		$inserted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'INSERT IGNORE INTO %i (challenge_hash, expires_at) VALUES (UNHEX(%s), %d)',
				$wpdb->prefix . 'frm_altcha_replays',
				$id,
				$expires
			)
		);

		if ( 1 === $inserted && false === wp_next_scheduled( self::PURGE_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', self::PURGE_HOOK );
		}

		return 1 === $inserted;
	}

	/**
	 * Delete expired challenges in indexed batches to keep each delete small.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function purge() {
		if ( ! self::table_exists() ) {
			wp_clear_scheduled_hook( self::PURGE_HOOK );
			return;
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		$now = time();

		do {
			$deleted = $wpdb->query(
				$wpdb->prepare(
					'DELETE FROM %i WHERE expires_at < %d ORDER BY expires_at LIMIT %d',
					$wpdb->prefix . 'frm_altcha_replays',
					$now,
					self::PURGE_BATCH_SIZE
				)
			);
		} while ( self::PURGE_BATCH_SIZE === $deleted );
	}

	/**
	 * Remove optional replay storage when Formidable is uninstalled.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;
		$table = self::table_name();
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table ) );
		unset( self::$ready_tables[ $table ] );
		wp_clear_scheduled_hook( self::PURGE_HOOK );
	}

	/**
	 * Require a SHA-256 hex digest before converting it to a binary database key.
	 *
	 * @since x.x
	 *
	 * @param string $id Challenge ID from the verified payload.
	 *
	 * @return bool
	 */
	private static function is_valid_id( $id ) {
		return is_string( $id ) && 1 === preg_match( '/^[0-9a-f]{64}$/D', $id );
	}
}
