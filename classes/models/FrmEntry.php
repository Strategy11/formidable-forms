<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEntry {

	/**
	 * Create a new entry
	 *
	 * @param array $values
	 *
	 * @return int | boolean $entry_id
	 */
	public static function create( $values ) {
		$entry_id = self::create_entry( $values, 'standard' );

		return $entry_id;
	}

	/**
	 * Create a new entry with some differences depending on type
	 *
	 * @param array $values
	 * @param string $type
	 *
	 * @return int | boolean $entry_id
	 */
	private static function create_entry( $values, $type ) {
		$new_values = self::before_insert_entry_in_database( $values, $type );

		// Don't check XML entries for duplicates
		if ( $type != 'xml' && self::is_duplicate( $new_values, $values ) ) {
			return false;
		}

		$entry_id = self::continue_to_create_entry( $values, $new_values );

		return $entry_id;
	}

	/**
	 * Check for duplicate entries created in the last minute
	 *
	 * @return boolean
	 */
	public static function is_duplicate( $new_values, $values ) {
		$duplicate_entry_time = apply_filters( 'frm_time_to_check_duplicates', 60, $new_values );

		if ( false === self::is_duplicate_check_needed( $values, $duplicate_entry_time ) ) {
			return false;
		}

		$check_val                 = $new_values;
		$check_val['created_at >'] = date( 'Y-m-d H:i:s', ( strtotime( $new_values['created_at'] ) - absint( $duplicate_entry_time ) ) );

		unset( $check_val['created_at'], $check_val['updated_at'] );
		unset( $check_val['is_draft'], $check_val['id'], $check_val['item_key'] );

		if ( $new_values['item_key'] == $new_values['name'] ) {
			unset( $check_val['name'] );
		}

		global $wpdb;
		$entry_exists = FrmDb::get_col( $wpdb->prefix . 'frm_items', $check_val, 'id', array( 'order_by' => 'created_at DESC' ) );

		if ( ! $entry_exists || empty( $entry_exists ) || ! isset( $values['item_meta'] ) ) {
			return false;
		}

		$is_duplicate = false;
		foreach ( $entry_exists as $entry_exist ) {
			$is_duplicate = true;

			// make sure it's a duplicate
			$metas       = FrmEntryMeta::get_entry_meta_info( $entry_exist );
			$field_metas = array();
			foreach ( $metas as $meta ) {
				$field_metas[ $meta->field_id ] = $meta->meta_value;
			}

			// If prev entry is empty and current entry is not, they are not duplicates
			$filtered_vals = array_filter( $values['item_meta'] );
			$field_metas   = array_filter( $field_metas );
			if ( empty( $field_metas ) && ! empty( $filtered_vals ) ) {
				return false;
			}

			// compare serialized values and not arrays
			$new_meta = array_map( 'maybe_serialize', $filtered_vals );

			if ( $field_metas === $new_meta ) {
				$is_duplicate = true;
				break;
			}

			if ( count( $field_metas ) !== count( $new_meta ) ) {
				// TODO: compare values saved in the post also
				$is_duplicate = false;
				continue;
			}

			$diff = array_diff_assoc( $field_metas, $new_meta );
			foreach ( $diff as $field_id => $meta_value ) {
				if ( ! empty( $meta_value ) ) {
					$is_duplicate = false;
					continue;
				}
			}

			if ( $is_duplicate ) {
				break;
			}
		}

		return $is_duplicate;
	}

	/**
	 * Determine if an entry needs to be checked as a possible duplicate
	 *
	 * @since 2.0.23
	 *
	 * @param array $values
	 * @param int $duplicate_entry_time
	 *
	 * @return bool
	 */
	private static function is_duplicate_check_needed( $values, $duplicate_entry_time ) {
		// If time for checking duplicates is set to an empty value, don't check for duplicates
		if ( empty( $duplicate_entry_time ) ) {
			return false;
		}

		// If CSV is importing, don't check for duplicates
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			return false;
		}

		// If repeating field entries are getting created, don't check for duplicates
		if ( isset( $values['parent_form_id'] ) && $values['parent_form_id'] ) {
			return false;
		}

		return true;
	}

	public static function duplicate( $id ) {
		global $wpdb;

		$values = self::getOne( $id );

		$new_values               = array();
		$new_values['item_key']   = FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_items', 'item_key' );
		$new_values['name']       = $values->name;
		$new_values['is_draft']   = $values->is_draft;
		$new_values['user_id']    = (int) $values->user_id;
		$new_values['updated_by'] = (int) $values->user_id;
		$new_values['form_id']    = $values->form_id ? (int) $values->form_id : null;
		$new_values['created_at'] = current_time( 'mysql', 1 );
		$new_values['updated_at'] = $new_values['created_at'];

		$query_results = $wpdb->insert( $wpdb->prefix . 'frm_items', $new_values );
		if ( ! $query_results ) {
			return false;
		}

		$entry_id = $wpdb->insert_id;

		global $frm_vars;
		if ( ! isset( $frm_vars['saved_entries'] ) ) {
			$frm_vars['saved_entries'] = array();
		}
		$frm_vars['saved_entries'][] = (int) $entry_id;

		FrmEntryMeta::duplicate_entry_metas( $id, $entry_id );
		self::clear_cache();

		do_action( 'frm_after_duplicate_entry', $entry_id, $new_values['form_id'], array( 'old_id' => $id ) );

		return $entry_id;
	}

	/**
	 * Update an entry (not via XML)
	 *
	 * @param int $id
	 * @param array $values
	 *
	 * @return boolean|int $update_results
	 */
	public static function update( $id, $values ) {
		$update_results = self::update_entry( $id, $values, 'standard' );

		return $update_results;
	}

	/**
	 * Update an entry with some differences depending on the update type
	 *
	 * @since 2.0.16
	 *
	 * @param int $id
	 * @param array $values
	 *
	 * @return boolean|int $query_results
	 */
	private static function update_entry( $id, $values, $update_type ) {
		global $wpdb;

		$update = self::before_update_entry( $id, $values, $update_type );
		if ( ! $update ) {
			return false;
		}

		$new_values = self::package_entry_to_update( $id, $values );

		$query_results = $wpdb->update( $wpdb->prefix . 'frm_items', $new_values, compact( 'id' ) );

		self::after_update_entry( $query_results, $id, $values, $new_values );

		return $query_results;
	}

	public static function destroy( $id ) {
		global $wpdb;
		$id = (int) $id;

		$entry = self::getOne( $id );
		if ( ! $entry ) {
			$result = false;

			return $result;
		}

		do_action( 'frm_before_destroy_entry', $id, $entry );

		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_item_metas WHERE item_id=%d', $id ) );
		$result = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_items WHERE id=%d', $id ) );

		self::clear_cache();

		return $result;
	}

	public static function update_form( $id, $value, $form_id ) {
		global $wpdb;
		$form_id = isset( $value ) ? $form_id : null;
		$result  = $wpdb->update( $wpdb->prefix . 'frm_items', array( 'form_id' => $form_id ), array( 'id' => $id ) );
		if ( $result ) {
			self::clear_cache();
		}

		return $result;
	}

	/**
	 * Clear entry caching
	 * Called when an entry is changed
	 *
	 * @since 2.0.5
	 */
	public static function clear_cache() {
		FrmDb::cache_delete_group( 'frm_entry' );
		FrmDb::cache_delete_group( 'frm_item' );
		FrmDb::cache_delete_group( 'frm_entry_meta' );
		FrmDb::cache_delete_group( 'frm_item_meta' );
	}

	/**
	 * After switching to the wp_loaded hook for processing entries,
	 * we can no longer use 'name', but check it as a fallback
	 *
	 * @since 2.0.11
	 */
	public static function get_new_entry_name( $values, $default = '' ) {
		$name = isset( $values['item_name'] ) ? $values['item_name'] : ( isset( $values['name'] ) ? $values['name'] : $default );
		if ( is_array( $name ) ) {
			$name = reset( $name );
		}

		return $name;
	}

	/**
	 * If $entry is numeric, get the entry object
	 *
	 * @param int|object $entry by reference
	 *
	 * @since 2.0.9
	 */
	public static function maybe_get_entry( &$entry ) {
		if ( $entry && is_numeric( $entry ) ) {
			$entry = self::getOne( $entry );
		} elseif ( empty( $entry ) ) {
			$entry = false;
		}
	}

	public static function getOne( $id, $meta = false ) {
		global $wpdb;

		$query = "SELECT it.*, fr.name as form_name, fr.form_key as form_key FROM {$wpdb->prefix}frm_items it
                  LEFT OUTER JOIN {$wpdb->prefix}frm_forms fr ON it.form_id=fr.id WHERE ";

		$query      .= is_numeric( $id ) ? 'it.id=%d' : 'it.item_key=%s';
		$query_args = array( $id );
		$query      = $wpdb->prepare( $query, $query_args ); // WPCS: unprepared SQL ok.

		if ( ! $meta ) {
			$entry = FrmDb::check_cache( $id . '_nometa', 'frm_entry', $query, 'get_row' );
			self::prepare_entry( $entry );
			return $entry;
		}

		$entry = FrmDb::check_cache( $id, 'frm_entry' );
		if ( $entry !== false ) {
			self::prepare_entry( $entry );
			return $entry;
		}

		$entry = $wpdb->get_row( $query ); // WPCS: unprepared SQL ok.
		$entry = self::get_meta( $entry );
		self::prepare_entry( $entry );

		return $entry;
	}

	/**
	 * @since 4.02.03
	 *
	 * @param object $entry
	 */
	private static function prepare_entry( &$entry ) {
		if ( empty( $entry ) ) {
			return;
		}

		FrmAppHelper::unserialize_or_decode( $entry->description );
		$entry = wp_unslash( $entry ); // TODO: Remove slashes on input only, not output.
	}

	/**
	 * @since 4.02.03
	 *
	 * @param array $entries
	 */
	private static function prepare_entries( &$entries ) {
		foreach ( $entries as $k => $entry ) {
			self::prepare_entry( $entry );
			$entries[ $k ] = $entry;
		}
	}

	public static function get_meta( $entry ) {
		if ( ! $entry ) {
			return $entry;
		}

		global $wpdb;
		$metas = FrmDb::get_results(
			$wpdb->prefix . 'frm_item_metas m LEFT JOIN ' . $wpdb->prefix . 'frm_fields f ON m.field_id=f.id',
			array(
				'item_id'    => $entry->id,
				'field_id !' => 0,
			),
			'field_id, meta_value, field_key, item_id'
		);

		$entry->metas = array();

		$include_key = apply_filters( 'frm_include_meta_keys', false, array( 'form_id' => $entry->form_id ) );
		foreach ( $metas as $meta_val ) {
			if ( $meta_val->item_id == $entry->id ) {
				FrmAppHelper::unserialize_or_decode( $meta_val->meta_value );
				$entry->metas[ $meta_val->field_id ] = $meta_val->meta_value;
				if ( $include_key ) {
					$entry->metas[ $meta_val->field_key ] = $entry->metas[ $meta_val->field_id ];
				}
				continue;
			}

			// include sub entries in an array
			if ( ! isset( $entry_metas[ $meta_val->field_id ] ) ) {
				$entry->metas[ $meta_val->field_id ] = array();
			}

			FrmAppHelper::unserialize_or_decode( $meta_val->meta_value );
			$entry->metas[ $meta_val->field_id ][] = $meta_val->meta_value;

			unset( $meta_val );
		}
		unset( $metas );

		FrmDb::set_cache( $entry->id, $entry, 'frm_entry' );

		return $entry;
	}

	/**
	 * @param string $id
	 */
	public static function exists( $id ) {
		global $wpdb;

		if ( FrmDb::check_cache( $id, 'frm_entry' ) ) {
			$exists = true;

			return $exists;
		}

		if ( is_numeric( $id ) ) {
			$where = array( 'id' => $id );
		} else {
			$where = array( 'item_key' => $id );
		}
		$id = FrmDb::get_var( $wpdb->prefix . 'frm_items', $where );

		return ( $id && $id > 0 );
	}

	public static function getAll( $where, $order_by = '', $limit = '', $meta = false, $inc_form = true ) {
		global $wpdb;

		$limit = FrmDb::esc_limit( $limit );

		$cache_key = FrmAppHelper::maybe_json_encode( $where ) . $order_by . $limit . $inc_form;
		$entries   = wp_cache_get( $cache_key, 'frm_entry' );

		if ( false === $entries ) {
			$fields = 'it.id, it.item_key, it.name, it.ip, it.form_id, it.post_id, it.user_id, it.parent_item_id, it.updated_by, it.created_at, it.updated_at, it.is_draft';
			$table  = $wpdb->prefix . 'frm_items it ';

			if ( $inc_form ) {
				$fields = 'it.*, fr.name as form_name,fr.form_key as form_key';
				$table  .= 'LEFT OUTER JOIN ' . $wpdb->prefix . 'frm_forms fr ON it.form_id=fr.id ';
			}

			if ( preg_match( '/ meta_([0-9]+)/', $order_by, $order_matches ) ) {
				// sort by a requested field
				$field_id = (int) $order_matches[1];
				$fields   .= ', (SELECT meta_value FROM ' . $wpdb->prefix . 'frm_item_metas WHERE field_id = ' . $field_id . ' AND item_id = it.id) as meta_' . $field_id;
				unset( $order_matches, $field_id );
			}

			// prepare the query
			$query = 'SELECT ' . $fields . ' FROM ' . $table . FrmDb::prepend_and_or_where( ' WHERE ', $where ) . $order_by . $limit;

			$entries = $wpdb->get_results( $query, OBJECT_K ); // WPCS: unprepared SQL ok.
			unset( $query );

			FrmDb::set_cache( $cache_key, $entries, 'frm_entry' );
		}

		if ( ! $meta || ! $entries ) {
			self::prepare_entries( $entries );
			return $entries;
		}
		unset( $meta );

		if ( ! is_array( $where ) && preg_match( '/^it\.form_id=\d+$/', $where ) ) {
			$where = array( 'it.form_id' => substr( $where, 11 ) );
		}

		$meta_where = array( 'field_id !' => 0 );
		if ( $limit == '' && is_array( $where ) && count( $where ) == 1 && isset( $where['it.form_id'] ) ) {
			$meta_where['fi.form_id'] = $where['it.form_id'];
		} else {
			$meta_where['item_id'] = array_keys( $entries );
		}

		$metas = FrmDb::get_results( $wpdb->prefix . 'frm_item_metas it LEFT OUTER JOIN ' . $wpdb->prefix . 'frm_fields fi ON (it.field_id = fi.id)', $meta_where, 'item_id, meta_value, field_id, field_key, form_id' );

		unset( $meta_where );

		if ( ! $metas ) {
			self::prepare_entries( $entries );
			return $entries;
		}

		foreach ( $metas as $m_key => $meta_val ) {
			if ( ! isset( $entries[ $meta_val->item_id ] ) ) {
				continue;
			}

			if ( ! isset( $entries[ $meta_val->item_id ]->metas ) ) {
				$entries[ $meta_val->item_id ]->metas = array();
			}

			FrmAppHelper::unserialize_or_decode( $meta_val->meta_value );
			$entries[ $meta_val->item_id ]->metas[ $meta_val->field_id ] = $meta_val->meta_value;
			unset( $m_key, $meta_val );
		}

		if ( ! FrmAppHelper::prevent_caching() ) {
			foreach ( $entries as $entry ) {
				FrmDb::set_cache( $entry->id, $entry, 'frm_entry' );
				unset( $entry );
			}
		}

		self::prepare_entries( $entries );
		return $entries;
	}

	// Pagination Methods
	/**
	 * @param int|array|string If int, use the form id.
	 */
	public static function getRecordCount( $where = '' ) {
		global $wpdb;
		$table_join = $wpdb->prefix . 'frm_items it LEFT OUTER JOIN ' . $wpdb->prefix . 'frm_forms fr ON it.form_id=fr.id';

		if ( is_numeric( $where ) ) {
			$table_join = 'frm_items';
			$where      = array( 'form_id' => $where );
		}

		if ( is_array( $where ) ) {
			$count = FrmDb::get_count( $table_join, $where );
		} else {
			$cache_key = 'count_' . FrmAppHelper::maybe_json_encode( $where );
			$query     = 'SELECT COUNT(*) FROM ' . $table_join . FrmDb::prepend_and_or_where( ' WHERE ', $where );
			$count     = FrmDb::check_cache( $cache_key, 'frm_entry', $query, 'get_var' );
		}

		return $count;
	}

	public static function getPageCount( $p_size, $where = '' ) {
		$p_size = (int) $p_size;
		$count  = 1;
		if ( $p_size ) {
			if ( ! is_numeric( $where ) ) {
				$where = self::getRecordCount( $where );
			}
			$count = ceil( (int) $where / $p_size );
		}

		return $count;
	}

	/**
	 * Prepare the data before inserting it into the database
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 * @param string $type
	 *
	 * @return array $new_values
	 */
	private static function before_insert_entry_in_database( &$values, $type ) {

		self::sanitize_entry_post( $values );

		if ( $type != 'xml' ) {
			$values = apply_filters( 'frm_pre_create_entry', $values );
		}

		$new_values = self::package_entry_data( $values );

		return $new_values;
	}

	/**
	 * Create an entry and perform after create actions
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 * @param array $new_values
	 *
	 * @return boolean|int $entry_id
	 */
	private static function continue_to_create_entry( $values, $new_values ) {
		$entry_id = self::insert_entry_into_database( $new_values );
		if ( ! $entry_id ) {
			return false;
		}

		self::after_insert_entry_in_database( $values, $new_values, $entry_id );

		return $entry_id;
	}

	/**
	 * Sanitize the POST values before we use them
	 *
	 * @since 2.0
	 *
	 * @param array $values The POST values by reference
	 */
	public static function sanitize_entry_post( &$values ) {
		$sanitize_method = array(
			'form_id'          => 'absint',
			'frm_action'       => 'sanitize_title',
			'form_key'         => 'sanitize_title',
			'item_key'         => 'sanitize_title',
			'item_name'        => 'sanitize_text_field',
			'frm_saving_draft' => 'absint',
			'is_draft'         => 'absint',
			'post_id'          => 'absint',
			'parent_item_id'   => 'absint',
			'created_at'       => 'sanitize_text_field',
			'updated_at'       => 'sanitize_text_field',
		);

		FrmAppHelper::sanitize_request( $sanitize_method, $values );
	}

	/**
	 * Prepare the new values for inserting into the database
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 *
	 * @return array $new_values
	 */
	private static function package_entry_data( &$values ) {
		global $wpdb;

		if ( ! isset( $values['item_key'] ) ) {
			$values['item_key'] = '';
		}

		$item_name  = self::get_new_entry_name( $values, $values['item_key'] );
		$new_values = array(
			'item_key'       => FrmAppHelper::get_unique_key( $values['item_key'], $wpdb->prefix . 'frm_items', 'item_key' ),
			'name'           => FrmAppHelper::truncate( $item_name, 255, 1, '' ),
			'ip'             => self::get_ip( $values ),
			'is_draft'       => self::get_is_draft_value( $values ),
			'form_id'        => (int) self::get_entry_value( $values, 'form_id', null ),
			'post_id'        => (int) self::get_entry_value( $values, 'post_id', 0 ),
			'parent_item_id' => (int) self::get_entry_value( $values, 'parent_item_id', 0 ),
			'created_at'     => self::get_created_at( $values ),
			'updated_at'     => self::get_updated_at( $values ),
			'description'    => self::get_entry_description( $values ),
			'user_id'        => self::get_entry_user_id( $values ),
		);

		$new_values['updated_by'] = isset( $values['updated_by'] ) ? $values['updated_by'] : $new_values['user_id'];

		return $new_values;
	}

	private static function get_entry_value( $values, $name, $default ) {
		return isset( $values[ $name ] ) ? $values[ $name ] : $default;
	}

	/**
	 * Get the ip for a new entry.
	 * Allow the import to override the value.
	 *
	 * @since 2.03.10
	 *
	 * @param array $values
	 *
	 * @return string
	 */
	private static function get_ip( $values ) {
		if ( ! FrmAppHelper::ips_saved() ) {
			return '';
		}

		$ip = FrmAppHelper::get_ip_address();
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			$ip = self::get_entry_value( $values, 'ip', $ip );
		}

		return $ip;
	}

	/**
	 * Get the is_draft value for a new entry
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 *
	 * @return int
	 */
	private static function get_is_draft_value( $values ) {
		return ( ( isset( $values['frm_saving_draft'] ) && $values['frm_saving_draft'] == 1 ) || ( isset( $values['is_draft'] ) && $values['is_draft'] == 1 ) ) ? 1 : 0;
	}

	/**
	 * Get the created_at value for a new entry
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 *
	 * @return string
	 */
	private static function get_created_at( $values ) {
		return self::get_entry_value( $values, 'created_at', current_time( 'mysql', 1 ) );
	}

	/**
	 * Get the updated_at value for a new entry
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 *
	 * @return string
	 */
	private static function get_updated_at( $values ) {
		if ( isset( $values['updated_at'] ) ) {
			$updated_at = $values['updated_at'];
		} else {
			$updated_at = self::get_created_at( $values );
		}

		return $updated_at;
	}

	/**
	 * Get the description value for a new entry
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 *
	 * @return string
	 */
	private static function get_entry_description( $values ) {
		if ( isset( $values['description'] ) && ! empty( $values['description'] ) ) {
			$description = FrmAppHelper::maybe_json_encode( $values['description'] );
		} else {
			$description = json_encode(
				array(
					'browser'  => FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' ),
					'referrer' => FrmAppHelper::get_server_value( 'HTTP_REFERER' ),
				)
			);
		}

		return $description;
	}

	/**
	 * Get the user_id value for a new entry
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 *
	 * @return int
	 */
	private static function get_entry_user_id( $values ) {
		if ( isset( $values['frm_user_id'] ) && ( is_numeric( $values['frm_user_id'] ) || FrmAppHelper::is_admin() ) ) {
			$user_id = $values['frm_user_id'];
		} else {
			$current_user_id = get_current_user_id();
			$user_id         = $current_user_id ? $current_user_id : 0;
		}

		return $user_id;
	}

	/**
	 * Insert new entry into the database
	 *
	 * @since 2.0.16
	 *
	 * @param array $new_values
	 *
	 * @return int | boolean $entry_id
	 */
	private static function insert_entry_into_database( $new_values ) {
		global $wpdb;

		$query_results = $wpdb->insert( $wpdb->prefix . 'frm_items', $new_values );

		if ( ! $query_results ) {
			$entry_id = false;
		} else {
			$entry_id = $wpdb->insert_id;
		}

		return $entry_id;
	}

	/**
	 * Add the new entry to global $frm_vars
	 *
	 * @since 2.0.16
	 *
	 * @param int $entry_id
	 */
	private static function add_new_entry_to_frm_vars( $entry_id ) {
		global $frm_vars;

		if ( ! isset( $frm_vars['saved_entries'] ) ) {
			$frm_vars['saved_entries'] = array();
		}

		$frm_vars['saved_entries'][] = (int) $entry_id;
	}

	/**
	 * Add entry metas, if there are any
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 * @param int $entry_id
	 */
	private static function maybe_add_entry_metas( $values, $entry_id ) {
		if ( isset( $values['item_meta'] ) ) {
			FrmEntryMeta::update_entry_metas( $entry_id, $values['item_meta'] );
		}
	}

	/**
	 * Trigger frm_after_create_entry hooks
	 *
	 * @since 2.0.16
	 *
	 * @param int $entry_id
	 * @param array $new_values
	 */
	private static function after_entry_created_actions( $entry_id, $values, $new_values ) {
		// this is a child entry
		$is_child = isset( $values['parent_form_id'] ) && isset( $values['parent_nonce'] ) && ! empty( $values['parent_form_id'] ) && wp_verify_nonce( $values['parent_nonce'], 'parent' );

		do_action( 'frm_after_create_entry', $entry_id, $new_values['form_id'], compact( 'is_child' ) );
		do_action( 'frm_after_create_entry_' . $new_values['form_id'], $entry_id, compact( 'is_child' ) );
	}

	/**
	 * Actions to perform immediately after an entry is inserted in the frm_items database
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 * @param array $new_values
	 * @param int $entry_id
	 */
	private static function after_insert_entry_in_database( $values, $new_values, $entry_id ) {

		self::add_new_entry_to_frm_vars( $entry_id );

		self::maybe_add_entry_metas( $values, $entry_id );

		self::clear_cache();

		self::after_entry_created_actions( $entry_id, $values, $new_values );
	}

	/**
	 * Perform some actions right before updating an entry
	 *
	 * @since 2.0.16
	 *
	 * @param int $id
	 * @param array $values
	 * @param string $update_type
	 *
	 * @return boolean $update
	 */
	private static function before_update_entry( $id, &$values, $update_type ) {
		$update = true;

		global $frm_vars;

		if ( isset( $frm_vars['saved_entries'] ) && is_array( $frm_vars['saved_entries'] ) && in_array( (int) $id, (array) $frm_vars['saved_entries'] ) ) {
			$update = false;
		}

		if ( $update && $update_type != 'xml' ) {
			$values = apply_filters( 'frm_pre_update_entry', $values, $id );
		}

		return $update;
	}

	/**
	 * Package the entry data for updating
	 *
	 * @since 2.0.16
	 *
	 * @param int $id
	 * @param array $values
	 *
	 * @return array $new_values
	 */
	private static function package_entry_to_update( $id, $values ) {
		global $wpdb;

		$new_values = array(
			'name'       => self::get_new_entry_name( $values ),
			'form_id'    => (int) self::get_entry_value( $values, 'form_id', null ),
			'is_draft'   => self::get_is_draft_value( $values ),
			'updated_at' => current_time( 'mysql', 1 ),
			'updated_by' => isset( $values['updated_by'] ) ? $values['updated_by'] : get_current_user_id(),
		);

		if ( isset( $values['post_id'] ) ) {
			$new_values['post_id'] = (int) $values['post_id'];
		}

		if ( isset( $values['item_key'] ) ) {
			$new_values['item_key'] = FrmAppHelper::get_unique_key( $values['item_key'], $wpdb->prefix . 'frm_items', 'item_key', $id );
		}

		if ( isset( $values['parent_item_id'] ) ) {
			$new_values['parent_item_id'] = (int) $values['parent_item_id'];
		}

		if ( isset( $values['frm_user_id'] ) && is_numeric( $values['frm_user_id'] ) ) {
			$new_values['user_id'] = $values['frm_user_id'];
		}

		$new_values = apply_filters( 'frm_update_entry', $new_values, $id );

		return $new_values;
	}

	/**
	 * Perform some actions right after updating an entry
	 *
	 * @since 2.0.16
	 *
	 * @param boolean|int $query_results
	 * @param int $id
	 * @param array $values
	 * @param array $new_values
	 */
	private static function after_update_entry( $query_results, $id, $values, $new_values ) {
		if ( $query_results ) {
			self::clear_cache();
		}

		global $frm_vars;
		if ( ! isset( $frm_vars['saved_entries'] ) ) {
			$frm_vars['saved_entries'] = array();
		}

		$frm_vars['saved_entries'][] = (int) $id;

		if ( isset( $values['item_meta'] ) ) {
			FrmEntryMeta::update_entry_metas( $id, $values['item_meta'] );
		}

		do_action( 'frm_after_update_entry', $id, $new_values['form_id'] );
		do_action( 'frm_after_update_entry_' . $new_values['form_id'], $id );
	}

	/**
	 * Create entry from an XML import
	 * Certain actions aren't necessary when importing (like saving sub entries, checking for duplicates, etc.)
	 *
	 * @since 2.0.16
	 *
	 * @param array $values
	 *
	 * @return int | boolean $entry_id
	 */
	public static function create_entry_from_xml( $values ) {
		$entry_id = self::create_entry( $values, 'xml' );

		return $entry_id;
	}

	/**
	 * Update entry from an XML import
	 * Certain actions aren't necessary when importing (like saving sub entries and modifying other vals)
	 *
	 * @since 2.0.16
	 *
	 * @param int $id
	 * @param array $values
	 *
	 * @return int | boolean $updated
	 */
	public static function update_entry_from_xml( $id, $values ) {
		$updated = self::update_entry( $id, $values, 'xml' );

		return $updated;
	}

	/**
	 * @param string $key
	 *
	 * @return int entry_id
	 */
	public static function get_id_by_key( $key ) {
		$entry_id = FrmDb::get_var( 'frm_items', array( 'item_key' => sanitize_title( $key ) ) );

		return (int) $entry_id;
	}
}
