<?php
/**
 * Shared operational metadata, relationships and rebuildable numeric facts.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmObjectStorage {
	/**
	 * Normalize namespaced storage keys while retaining dot separators.
	 *
	 * @since x.x
	 *
	 * @param string $value Consumer namespace or key.
	 *
	 * @return string
	 */
	private static function key( $value ) {
		return (string) preg_replace( '/[^a-z0-9_.-]/', '', strtolower( $value ) );
	}
	/**
	 * Install lazily for consuming add-ons, without changing entry storage.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function install() {
		if ( '1' === get_option( 'frm_object_storage_version' ) ) {
			return;
		}
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$meta      = $wpdb->prefix . 'frm_object_meta';
		$relations = $wpdb->prefix . 'frm_object_relations';
		$stats     = $wpdb->prefix . 'frm_object_stats';
		dbDelta(
			$wpdb->prepare(
				'CREATE TABLE %i (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			object_type varchar(40) NOT NULL,
			object_id bigint(20) unsigned NOT NULL,
			meta_key varchar(100) NOT NULL,
			meta_value longtext NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY object_key (object_type,object_id,meta_key)
		)',
				$meta
			) . ' ' . $wpdb->get_charset_collate() . ';'
		);
		dbDelta(
			$wpdb->prepare(
				"CREATE TABLE %i (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			kind varchar(40) NOT NULL,
			source_type varchar(40) NOT NULL,
			source_id bigint(20) unsigned NOT NULL,
			target_type varchar(40) NOT NULL,
			target_id bigint(20) unsigned NOT NULL,
			owner_id bigint(20) unsigned NOT NULL DEFAULT 0,
			entry_id bigint(20) unsigned NOT NULL DEFAULT 0,
			status varchar(20) NOT NULL DEFAULT 'active',
			amount decimal(20,6) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY relationship (kind,source_type,source_id,target_type,target_id,owner_id),
			KEY target_kind (target_type,target_id,kind,status,created_at),
			KEY owner_kind (owner_id,kind,status),
			KEY entry_id (entry_id)
		)",
				$relations
			) . ' ' . $wpdb->get_charset_collate() . ';'
		);
		dbDelta(
			$wpdb->prepare(
				'CREATE TABLE %i (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			object_type varchar(40) NOT NULL,
			object_id bigint(20) unsigned NOT NULL,
			metric varchar(100) NOT NULL,
			amount decimal(20,6) NOT NULL DEFAULT 0,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY object_metric (object_type,object_id,metric),
			KEY metric_amount (metric,amount)
		)',
				$stats
			) . ' ' . $wpdb->get_charset_collate() . ';'
		);

		foreach ( array( $meta, $relations, $stats ) as $table ) {
			if ( $table !== $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) ) {
				return;
			}
		}

		update_option( 'frm_object_storage_version', '1', false );
	}
	/**
	 * Store namespaced internal metadata as JSON, never as PHP objects.
	 *
	 * @since x.x
	 *
	 * @param string $type Object namespace.
	 * @param int    $id Object ID.
	 * @param string $key Metadata key.
	 * @param mixed  $value JSON value.
	 *
	 * @return bool
	 */
	public static function set_meta( $type, $id, $key, $value ) {
		global $wpdb;
		return false !== $wpdb->query(
			$wpdb->prepare(
				'INSERT INTO %i (object_type,object_id,meta_key,meta_value) VALUES (%s,%d,%s,%s) ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)',
				$wpdb->prefix . 'frm_object_meta',
				self::key( $type ),
				absint( $id ),
				self::key( $key ),
				wp_json_encode( $value )
			)
		);
	}
	/**
	 * Retrieve internal metadata without executing serialized content.
	 *
	 * @since x.x
	 *
	 * @param string $type Object namespace.
	 * @param int    $id Object ID.
	 * @param string $key Metadata key.
	 *
	 * @return mixed
	 */
	public static function get_meta( $type, $id, $key ) {
		global $wpdb;
		$json = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT meta_value FROM %i WHERE object_type = %s AND object_id = %d AND meta_key = %s',
				$wpdb->prefix . 'frm_object_meta',
				self::key( $type ),
				absint( $id ),
				self::key( $key )
			)
		);
		return is_string( $json ) ? json_decode( $json, true ) : null;
	}
	/**
	 * Upsert a unique relationship. Consumers must authorize their own actors.
	 *
	 * @since x.x
	 *
	 * @param array $row Typed source, target, owner, state and amount.
	 *
	 * @return bool
	 */
	public static function set_relation( $row ) {
		global $wpdb;
		$row = wp_parse_args(
			$row,
			array(
				'owner_id'   => 0,
				'entry_id'   => 0,
				'status'     => 'active',
				'amount'     => 0,
				'created_at' => gmdate( 'Y-m-d H:i:s' ),
			)
		);

		foreach ( array( 'kind', 'source_type', 'target_type' ) as $key ) {
			if ( empty( $row[ $key ] ) || strlen( $row[ $key ] ) > 40 || self::key( $row[ $key ] ) !== $row[ $key ] ) {
				return false;
			}
		}

		if ( empty( $row['source_id'] ) || empty( $row['target_id'] ) ) {
			return false;
		}

		FrmDb::cache_delete_group( 'frm_object_relation' );
		$result = false !== $wpdb->query(
			$wpdb->prepare(
				'INSERT INTO %i (kind,source_type,source_id,target_type,target_id,owner_id,entry_id,status,amount,created_at) VALUES '
					. '(%s,%s,%d,%s,%d,%d,%d,%s,%f,%s) ON DUPLICATE KEY UPDATE entry_id = VALUES(entry_id), status = VALUES(status), amount = VALUES(amount)',
				$wpdb->prefix . 'frm_object_relations',
				$row['kind'],
				$row['source_type'],
				absint( $row['source_id'] ),
				$row['target_type'],
				absint( $row['target_id'] ),
				absint( $row['owner_id'] ),
				absint( $row['entry_id'] ),
				self::key( $row['status'] ),
				(float) $row['amount'],
				$row['created_at']
			)
		);
		FrmDb::cache_delete_group( 'frm_object_relation' );
		return $result;
	}
	/**
	 * Read bounded relationships using indexed, allowlisted columns.
	 *
	 * @since x.x
	 *
	 * @param array $where Equality constraints.
	 * @param int   $limit Page size.
	 * @param int   $offset Page offset.
	 *
	 * @return array
	 */
	public static function relations( $where, $limit = 20, $offset = 0 ) {
		global $wpdb;
		$clauses = self::where( $where );

		if ( ! $clauses ) {
			return array();
		}

		return FrmDb::get_results(
			'frm_object_relations',
			$clauses,
			'*',
			array(
				'order_by' => 'id DESC',
				'limit'    => absint( $offset ) . ',' . max( 1, min( 100, absint( $limit ) ) ),
			)
		);
	}
	/**
	 * Remove relationships within a specific consumer's scope.
	 *
	 * @since x.x
	 *
	 * @param array $where Equality constraints including kind.
	 *
	 * @return bool
	 */
	public static function delete_relations( $where ) {
		global $wpdb;

		$where = self::where( $where );

		if ( empty( $where['kind'] ) || count( $where ) < 2 ) {
			return false;
		}

		FrmDb::cache_delete_group( 'frm_object_relation' );
		$result = false !== $wpdb->delete( $wpdb->prefix . 'frm_object_relations', self::where( $where ) );
		FrmDb::cache_delete_group( 'frm_object_relation' );
		return $result;
	}
	/**
	 * Allow equality constraints without accepting SQL from consumers.
	 *
	 * @since x.x
	 *
	 * @param array $where Equality constraints.
	 *
	 * @return array
	 */
	private static function where( $where ) {
		return array_intersect_key( $where, array_flip( array( 'id', 'kind', 'source_type', 'source_id', 'target_type', 'target_id', 'owner_id', 'entry_id', 'status' ) ) );
	}
	/**
	 * Aggregate relationships without materializing every matching record.
	 *
	 * @since x.x
	 *
	 * @param array $where Equality constraints.
	 * @param int   $since Earliest UTC timestamp, or zero.
	 *
	 * @return array Count and amount.
	 */
	public static function aggregate( $where, $since = 0 ) {
		global $wpdb;
		$clauses = self::where( $where );
		$empty   = array(
			'count'  => 0,
			'amount' => 0,
		);

		if ( ! $clauses ) {
			return $empty;
		}

		$args = array( $wpdb->prefix . 'frm_object_relations' );

		foreach ( array( 'id', 'kind', 'source_type', 'source_id', 'target_type', 'target_id', 'owner_id', 'entry_id', 'status' ) as $key ) {
			if ( isset( $clauses[ $key ] ) && ! is_scalar( $clauses[ $key ] ) ) {
				return $empty;
			}

			$args[] = isset( $clauses[ $key ] ) ? 1 : 0;
			$args[] = $clauses[ $key ] ?? '';
		}

		$args[] = gmdate( 'Y-m-d H:i:s', absint( $since ) );
		// Numeric rebuilds read current rows rather than a prior cached aggregate.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT COUNT(*) AS count, COALESCE(SUM(amount),0) AS amount FROM %i'
				. ' WHERE (%d = 0 OR id = %s) AND (%d = 0 OR kind = %s)'
				. ' AND (%d = 0 OR source_type = %s) AND (%d = 0 OR source_id = %s)'
				. ' AND (%d = 0 OR target_type = %s) AND (%d = 0 OR target_id = %s)'
				. ' AND (%d = 0 OR owner_id = %s) AND (%d = 0 OR entry_id = %s)'
				. ' AND (%d = 0 OR status = %s) AND created_at >= %s',
				$args
			),
			ARRAY_A
		);
		return is_array( $row ) ? $row : $empty;
	}
	/**
	 * Persist a rebuildable numeric fact for indexed ordering.
	 *
	 * @since x.x
	 *
	 * @param string $type Object namespace.
	 * @param int    $id Object ID.
	 * @param string $metric Metric namespace.
	 * @param float  $amount Numeric value.
	 *
	 * @return bool
	 */
	public static function set_stat( $type, $id, $metric, $amount ) {
		global $wpdb;
		return false !== $wpdb->query(
			$wpdb->prepare(
				'INSERT INTO %i (object_type,object_id,metric,amount,updated_at) VALUES (%s,%d,%s,%f,%s) ON DUPLICATE KEY UPDATE amount = '
				. 'VALUES(amount), updated_at = VALUES(updated_at)',
				$wpdb->prefix . 'frm_object_stats',
				self::key( $type ),
				absint( $id ),
				self::key( $metric ),
				$amount,
				gmdate( 'Y-m-d H:i:s' )
			)
		);
	}
	/**
	 * Read a cached numeric fact, returning zero when no records exist.
	 *
	 * @since x.x
	 *
	 * @param string $type Object namespace.
	 * @param int    $id Object ID.
	 * @param string $metric Metric namespace.
	 *
	 * @return float
	 */
	public static function get_stat( $type, $id, $metric ) {
		global $wpdb;
		return (float) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT amount FROM %i WHERE object_type = %s AND object_id = %d AND metric = %s',
				$wpdb->prefix . 'frm_object_stats',
				self::key( $type ),
				absint( $id ),
				self::key( $metric )
			)
		);
	}
	/**
	 * Remove entry-specific operational data after native content deletion.
	 *
	 * @since x.x
	 *
	 * @param int $id Deleted entry ID.
	 *
	 * @return void
	 */
	public static function delete_entry( $id ) {
		if ( '1' !== get_option( 'frm_object_storage_version' ) ) {
			return;
		}
		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix . 'frm_object_meta',
			array(
				'object_type' => 'entry',
				'object_id'   => $id,
			)
		);
		$wpdb->delete(
			$wpdb->prefix . 'frm_object_stats',
			array(
				'object_type' => 'entry',
				'object_id'   => $id,
			)
		);
		$wpdb->delete( $wpdb->prefix . 'frm_object_relations', array( 'entry_id' => $id ) );
		$wpdb->delete(
			$wpdb->prefix . 'frm_object_relations',
			array(
				'source_type' => 'cart-line',
				'source_id'   => $id,
			)
		);
		$wpdb->delete(
			$wpdb->prefix . 'frm_object_relations',
			array(
				'source_type' => 'entry',
				'source_id'   => $id,
			)
		);
		$wpdb->delete(
			$wpdb->prefix . 'frm_object_relations',
			array(
				'target_type' => 'entry',
				'target_id'   => $id,
			)
		);
		FrmDb::cache_delete_group( 'frm_object_relation' );
	}
}
