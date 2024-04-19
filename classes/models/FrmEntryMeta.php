<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEntryMeta {

	/**
	 * @param int    $entry_id
	 * @param int    $field_id
	 * @param string $meta_key usually set to '' as this parameter is no longer used.
	 * @param mixed  $meta_value
	 * @return int
	 */
	public static function add_entry_meta( $entry_id, $field_id, $meta_key, $meta_value ) {
		global $wpdb;

		if ( FrmAppHelper::is_empty_value( $meta_value ) ) {
			// don't save blank fields
			return 0;
		}

		$new_values = array(
			'meta_value' => is_array( $meta_value ) ? serialize( array_filter( $meta_value, 'FrmAppHelper::is_not_empty_value' ) ) : trim( $meta_value ),
			'item_id'    => $entry_id,
			'field_id'   => $field_id,
			'created_at' => current_time( 'mysql', 1 ),
		);

		self::set_value_before_save( $new_values );
		$new_values = apply_filters( 'frm_add_entry_meta', $new_values );

		$query_results = $wpdb->insert( $wpdb->prefix . 'frm_item_metas', $new_values );

		if ( $query_results ) {
			self::clear_cache();
			wp_cache_delete( $entry_id, 'frm_entry' );
			$id = $wpdb->insert_id;
		} else {
			$id = 0;
		}

		return $id;
	}

	/**
	 * @param int          $entry_id
	 * @param int          $field_id
	 * @param string       $meta_key   Deprecated.
	 * @param array|string $meta_value
	 *
	 * @return bool|false|int
	 */
	public static function update_entry_meta( $entry_id, $field_id, $meta_key, $meta_value ) {
		if ( ! $field_id ) {
			return false;
		}

		global $wpdb;

		$values               = array(
			'item_id'  => $entry_id,
			'field_id' => $field_id,
		);
		$where_values         = $values;
		$values['meta_value'] = $meta_value;
		self::set_value_before_save( $values );
		$values = apply_filters( 'frm_update_entry_meta', $values );

		if ( is_array( $values['meta_value'] ) ) {
			$values['meta_value'] = array_filter( $values['meta_value'], 'FrmAppHelper::is_not_empty_value' );
		}
		$meta_value = maybe_serialize( $values['meta_value'] );

		wp_cache_delete( $entry_id, 'frm_entry' );
		self::clear_cache();

		return $wpdb->update( $wpdb->prefix . 'frm_item_metas', array( 'meta_value' => $meta_value ), $where_values );
	}

	/**
	 * @since 3.0
	 */
	private static function set_value_before_save( &$values ) {
		$field = FrmField::getOne( $values['field_id'] );
		if ( $field ) {
			$field_obj = FrmFieldFactory::get_field_object( $field );

			$values['meta_value'] = $field_obj->set_value_before_save( $values['meta_value'] );
		}
	}

	/**
	 * @since 3.0
	 */
	private static function get_value_to_save( $atts, &$value ) {
		if ( is_object( $atts['field'] ) ) {
			$field_obj = FrmFieldFactory::get_field_object( $atts['field'] );
			$value     = $field_obj->get_value_to_save(
				$value,
				array(
					'entry_id' => $atts['entry_id'],
					'field_id' => $atts['field_id'],
				)
			);
		}

		$value = apply_filters( 'frm_prepare_data_before_db', $value, $atts['field_id'], $atts['entry_id'], array( 'field' => $atts['field'] ) );
	}

	public static function update_entry_metas( $entry_id, $values ) {
		global $wpdb;

		$prev_values = FrmDb::get_col(
			$wpdb->prefix . 'frm_item_metas',
			array(
				'item_id'    => $entry_id,
				'field_id !' => 0,
			),
			'field_id'
		);

		foreach ( $values as $field_id => $meta_value ) {
			$field = false;
			if ( ! empty( $field_id ) ) {
				$field = FrmField::getOne( $field_id );
			}

			self::get_value_to_save( compact( 'field', 'field_id', 'entry_id' ), $meta_value );

			if ( $prev_values && in_array( $field_id, $prev_values ) ) {

				if ( ( is_array( $meta_value ) && empty( $meta_value ) ) || ( ! is_array( $meta_value ) && trim( $meta_value ) == '' ) ) {
					// remove blank fields
					unset( $values[ $field_id ] );
				} else {
					// if value exists, then update it
					self::update_entry_meta( $entry_id, $field_id, '', $meta_value );
				}
			} else {
				// if value does not exist, then create it
				self::add_entry_meta( $entry_id, $field_id, '', $meta_value );
			}
		}//end foreach

		if ( empty( $prev_values ) ) {
			return;
		}

		$prev_values = array_diff( $prev_values, array_keys( $values ) );

		if ( empty( $prev_values ) ) {
			return;
		}

		// prepare the query
		$where = array(
			'item_id'  => $entry_id,
			'field_id' => $prev_values,
		);
		FrmDb::get_where_clause_and_values( $where );

		// Delete any leftovers
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_item_metas ' . $where['where'], $where['values'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		self::clear_cache();
	}

	public static function duplicate_entry_metas( $old_id, $new_id ) {
		$metas = self::get_entry_meta_info( $old_id );

		/**
		 * Allows changing entry duplicate values before save.
		 *
		 * @since 5.4.4
		 *
		 * @param array $metas The list of entry meta values.
		 */
		$metas = apply_filters( 'frm_before_duplicate_entry_values', $metas );
		foreach ( $metas as $meta ) {
			self::add_entry_meta( $new_id, $meta->field_id, '', $meta->meta_value );
			unset( $meta );
		}
		self::clear_cache();
	}

	public static function delete_entry_meta( $entry_id, $field_id ) {
		global $wpdb;
		self::clear_cache();

		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}frm_item_metas WHERE field_id=%d AND item_id=%d", $field_id, $entry_id ) );
	}

	/**
	 * Clear entry meta caching
	 * Called when a meta is added or changed
	 *
	 * @since 2.0.5
	 */
	public static function clear_cache() {
		FrmDb::cache_delete_group( 'frm_entry_meta' );
		FrmDb::cache_delete_group( 'frm_item_meta' );
	}

	/**
	 * @since 2.0.9
	 *
	 * @param stdClass   $entry
	 * @param int|string $field_id
	 * @return mixed
	 */
	public static function get_meta_value( $entry, $field_id ) {
		if ( isset( $entry->metas ) ) {
			return isset( $entry->metas[ $field_id ] ) ? $entry->metas[ $field_id ] : false;
		}
		return self::get_entry_meta_by_field( $entry->id, $field_id );
	}

	public static function get_entry_meta_by_field( $entry_id, $field_id ) {
		global $wpdb;

		if ( is_object( $entry_id ) ) {
			$entry    = $entry_id;
			$entry_id = $entry->id;
			$cached   = $entry;
		} else {
			$entry_id = (int) $entry_id;
			$cached   = FrmDb::check_cache( $entry_id, 'frm_entry' );
		}

		if ( $cached && isset( $cached->metas ) && isset( $cached->metas[ $field_id ] ) ) {
			$result = $cached->metas[ $field_id ];

			return wp_unslash( $result );
		}

		$get_table = $wpdb->prefix . 'frm_item_metas';
		$query     = array( 'item_id' => $entry_id );
		if ( is_numeric( $field_id ) ) {
			$query['field_id'] = $field_id;
		} else {
			$get_table            .= ' it LEFT OUTER JOIN ' . $wpdb->prefix . 'frm_fields fi ON it.field_id=fi.id';
			$query['fi.field_key'] = $field_id;
		}

		$result = FrmDb::get_var( $get_table, $query, 'meta_value' );
		FrmAppHelper::unserialize_or_decode( $result );
		$result = wp_unslash( $result );

		return $result;
	}

	public static function get_entry_metas_for_field( $field_id, $order = '', $limit = '', $args = array() ) {
		$defaults = array(
			'value'        => false,
			'unique'       => false,
			'stripslashes' => true,
			'is_draft'     => false,
		);
		$args     = wp_parse_args( $args, $defaults );

		$query = array();
		self::meta_field_query( $field_id, $order, $limit, $args, $query );
		$query = implode( ' ', $query );

		$cache_key = 'entry_metas_for_field_' . $field_id . $order . $limit . FrmAppHelper::maybe_json_encode( $args );
		$values    = FrmDb::check_cache( $cache_key, 'frm_entry', $query, 'get_col' );

		if ( ! $args['stripslashes'] ) {
			return $values;
		}

		foreach ( $values as $k => $v ) {
			FrmAppHelper::unserialize_or_decode( $v );
			$values[ $k ] = $v;
			unset( $k, $v );
		}

		return wp_unslash( $values );
	}

	/**
	 * @param int|string $field_id
	 * @param string     $order
	 * @param string     $limit
	 * @param array      $args
	 * @param array      $query
	 */
	private static function meta_field_query( $field_id, $order, $limit, $args, array &$query ) {
		global $wpdb;
		$query[] = 'SELECT';
		$query[] = $args['unique'] ? 'DISTINCT(em.meta_value)' : 'em.meta_value';
		$query[] = 'FROM ' . $wpdb->prefix . 'frm_item_metas em ';

		if ( ! $args['is_draft'] ) {
			$query[] = 'INNER JOIN ' . $wpdb->prefix . 'frm_items e ON (e.id=em.item_id)';
		}

		if ( is_numeric( $field_id ) ) {
			$query[] = $wpdb->prepare( 'WHERE em.field_id=%d', $field_id );
		} else {
			$query[] = $wpdb->prepare( 'LEFT JOIN ' . $wpdb->prefix . 'frm_fields fi ON (em.field_id = fi.id) WHERE fi.field_key=%s', $field_id );
		}

		if ( ! $args['is_draft'] ) {
			$query[] = 'AND e.is_draft=0';
		}

		if ( $args['value'] ) {
			$query[] = $wpdb->prepare( ' AND meta_value=%s', $args['value'] );
		}
		$query[] = $order . $limit;
	}

	public static function get_entry_meta_info( $entry_id ) {
		return FrmDb::get_results( 'frm_item_metas', array( 'item_id' => $entry_id ) );
	}

	/**
	 * @param array  $where
	 * @param string $order_by
	 * @param string $limit
	 * @param bool   $stripslashes
	 * @return array
	 */
	public static function getAll( $where = array(), $order_by = '', $limit = '', $stripslashes = false ) {
		global $wpdb;
		$query = 'SELECT it.*, fi.type as field_type, fi.field_key as field_key,
            fi.required as required, fi.form_id as field_form_id, fi.name as field_name, fi.options as fi_options
			FROM ' . $wpdb->prefix . 'frm_item_metas it LEFT OUTER JOIN ' . $wpdb->prefix . 'frm_fields fi ON it.field_id=fi.id' .
			FrmDb::prepend_and_or_where( ' WHERE ', $where ) . $order_by . $limit;

		$cache_key = 'all_' . FrmAppHelper::maybe_json_encode( $where ) . $order_by . $limit;
		$results   = FrmDb::check_cache( $cache_key, 'frm_entry', $query, ( $limit == ' LIMIT 1' ? 'get_row' : 'get_results' ) );

		if ( ! $results || ! $stripslashes ) {
			return $results;
		}

		foreach ( $results as $k => $result ) {
			FrmAppHelper::unserialize_or_decode( $result->meta_value );
			$results[ $k ]->meta_value = wp_unslash( $result->meta_value );
			unset( $k, $result );
		}

		return $results;
	}

	public static function getEntryIds( $where = array(), $order_by = '', $limit = '', $unique = true, $args = array() ) {
		$defaults = array(
			'is_draft' => false,
			'user_id'  => '',
			'group_by' => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		$query = array();
		self::get_ids_query( $where, $order_by, $limit, $unique, $args, $query );
		$query = implode( ' ', $query );

		$cache_key = 'ids_' . FrmAppHelper::maybe_json_encode( $where ) . $order_by . 'l' . $limit . 'u' . $unique . FrmAppHelper::maybe_json_encode( $args );
		$type      = 'get_' . ( ' LIMIT 1' === $limit ? 'var' : 'col' );
		return FrmDb::check_cache( $cache_key, 'frm_entry', $query, $type );
	}

	/**
	 * Given a query including a form id and its child form ids, output an array of matching entry ids
	 * If a child entry id is matched, its parent will be returned in its place
	 *
	 * @param array $query
	 * @param array $args
	 * @return array
	 */
	public static function get_top_level_entry_ids( $query, $args ) {
		$args['return_parent_id_if_0_return_id'] = true;
		return self::getEntryIds( $query, '', '', true, $args );
	}

	/**
	 * @param array|string $where
	 * @param string       $order_by
	 * @param string       $limit
	 */
	private static function get_ids_query( $where, $order_by, $limit, $unique, $args, array &$query ) {
		global $wpdb;
		$query[]  = 'SELECT';
		$defaults = array(
			'return_parent_id'                => false,
			'return_parent_id_if_0_return_id' => false,
		);
		$args     = array_merge( $defaults, $args );

		if ( $unique ) {
			$query[] = 'DISTINCT';
		}

		if ( $args['return_parent_id_if_0_return_id'] ) {
			$query[] = 'IF ( e.parent_item_id = 0, it.item_id, e.parent_item_id )';
		} elseif ( $args['return_parent_id'] ) {
			$query[] = 'e.parent_item_id';
		} else {
			$query[] = 'it.item_id';
		}

		$query[] = 'FROM ' . $wpdb->prefix . 'frm_item_metas it LEFT OUTER JOIN ' . $wpdb->prefix . 'frm_fields fi ON it.field_id=fi.id';

		$query[] = 'INNER JOIN ' . $wpdb->prefix . 'frm_items e ON (e.id=it.item_id)';
		if ( is_array( $where ) ) {
			if ( ! $args['is_draft'] ) {
				$where['e.is_draft'] = 0;
			} elseif ( is_numeric( $args['is_draft'] ) ) {
				if ( class_exists( 'FrmAbandonmentHooksController', false ) ) {
					$where['e.is_draft'] = absint( $args['is_draft'] );
				} else {
					$where['e.is_draft'] = 1;
				}
			} elseif ( 'both' === $args['is_draft'] && class_exists( 'FrmAbandonmentHooksController', false ) ) {
				$where['e.is_draft'] = array( 0, 1 );
			} elseif ( false !== strpos( $args['is_draft'], ',' ) ) {
				$is_draft = array_reduce(
					explode( ',', $args['is_draft'] ),
					function ( $total, $current ) {
						if ( is_numeric( $current ) ) {
							$total[] = absint( $current );
						}
						return $total;
					},
					array()
				);
				if ( $is_draft ) {
					$where['e.is_draft'] = $is_draft;
				}
			}//end if

			if ( ! empty( $args['user_id'] ) ) {
				$where['e.user_id'] = $args['user_id'];
			}
			$query[] = FrmDb::prepend_and_or_where( ' WHERE ', $where ) . $order_by . $limit;

			if ( $args['group_by'] ) {
				$query[] = ' GROUP BY ' . sanitize_text_field( $args['group_by'] );
			}

			return;
		}//end if

		$draft_where = '';
		$user_where  = '';
		if ( ! $args['is_draft'] ) {
			$draft_where = $wpdb->prepare( ' AND e.is_draft=%d', 0 );
		} elseif ( $args['is_draft'] == 1 ) {
			$draft_where = $wpdb->prepare( ' AND e.is_draft=%d', 1 );
		}

		if ( ! empty( $args['user_id'] ) ) {
			$user_where = $wpdb->prepare( ' AND e.user_id=%d', $args['user_id'] );
		}

		if ( strpos( $where, ' GROUP BY ' ) ) {
			// don't inject WHERE filtering after GROUP BY
			$parts  = explode( ' GROUP BY ', $where );
			$where  = $parts[0];
			$where .= $draft_where . $user_where;
			$where .= ' GROUP BY ' . $parts[1];
		} else {
			$where .= $draft_where . $user_where;
		}

		// The query has already been prepared
		$query[] = FrmDb::prepend_and_or_where( ' WHERE ', $where ) . $order_by . $limit;
	}

	public static function search_entry_metas( $search, $field_id, $operator ) {
		$cache_key = 'search_' . FrmAppHelper::maybe_json_encode( $search ) . $field_id . $operator;
		$results   = wp_cache_get( $cache_key, 'frm_entry' );
		if ( false !== $results ) {
			return $results;
		}

		global $wpdb;
		if ( is_array( $search ) ) {
			$where = '';
			foreach ( $search as $field => $value ) {
				if ( $value <= 0 || ! in_array( $field, array( 'year', 'month', 'day' ) ) ) {
					continue;
				}

				switch ( $field ) {
					case 'year':
						$value = '%' . $value;
						break;
					case 'month':
						$value .= '%';
						break;
					case 'day':
						$value = '%' . $value . '%';
				}
				$where .= $wpdb->prepare( ' meta_value ' . $operator . ' %s and', $value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
			$where .= $wpdb->prepare( ' field_id=%d', $field_id );
			$query  = 'SELECT DISTINCT item_id FROM ' . $wpdb->prefix . 'frm_item_metas' . FrmDb::prepend_and_or_where( ' WHERE ', $where );
		} else {
			if ( $operator == 'LIKE' ) {
				$search = '%' . $search . '%';
			}
			$query = $wpdb->prepare( "SELECT DISTINCT item_id FROM {$wpdb->prefix}frm_item_metas WHERE meta_value {$operator} %s and field_id = %d", $search, $field_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}//end if

		$results = $wpdb->get_col( $query, 0 ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		FrmDb::set_cache( $cache_key, $results, 'frm_entry' );

		return $results;
	}
}
