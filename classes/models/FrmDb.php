<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmDb {
	public $fields;
	public $forms;
	public $entries;
	public $entry_metas;

	public function __construct() {
		if ( ! defined( 'ABSPATH' ) ) {
			die( 'You are not allowed to call this page directly.' );
		}

		_deprecated_function( __METHOD__, '2.05.06', 'FrmMigrate' );
		global $wpdb;
		$this->fields      = $wpdb->prefix . 'frm_fields';
		$this->forms       = $wpdb->prefix . 'frm_forms';
		$this->entries     = $wpdb->prefix . 'frm_items';
		$this->entry_metas = $wpdb->prefix . 'frm_item_metas';
	}

	/**
	 * Change array into format $wpdb->prepare can use
	 *
	 * @param array  $args
	 * @param string $starts_with
	 * @return void
	 */
	public static function get_where_clause_and_values( &$args, $starts_with = ' WHERE ' ) {
		if ( empty( $args ) ) {
			// add an arg to prevent prepare from failing
			$args = array(
				'where'  => $starts_with . '1=%d',
				'values' => array( 1 ),
			);

			return;
		}

		$where  = '';
		$values = array();

		if ( is_array( $args ) ) {
			$base_where = $starts_with;
			self::parse_where_from_array( $args, $base_where, $where, $values );
		}

		$args = compact( 'where', 'values' );
	}

	/**
	 * @param array  $args
	 * @param string $base_where
	 * @param string $where
	 * @param array  $values
	 */
	public static function parse_where_from_array( $args, $base_where, &$where, &$values ) {
		$condition = ' AND';
		if ( isset( $args['or'] ) ) {
			$condition = ' OR';
			unset( $args['or'] );
		}

		foreach ( $args as $key => $value ) {
			$where         .= empty( $where ) ? $base_where : $condition;
			$array_inc_null = ( ! is_numeric( $key ) && is_array( $value ) && in_array( null, $value ) );
			if ( is_numeric( $key ) || $array_inc_null ) {
				$where       .= ' ( ';
				$nested_where = '';
				if ( $array_inc_null ) {
					foreach ( $value as $val ) {
						$parse_where = array(
							$key => $val,
							'or' => 1,
						);
						self::parse_where_from_array( $parse_where, '', $nested_where, $values );
					}
				} else {
					self::parse_where_from_array( $value, '', $nested_where, $values );
				}
				$where .= $nested_where;
				$where .= ' ) ';
			} else {
				self::interpret_array_to_sql( $key, $value, $where, $values );
			}
		}//end foreach
	}

	/**
	 * @param string       $key
	 * @param array|string $value
	 * @param string       $where
	 * @param array        $values
	 * @return void
	 */
	private static function interpret_array_to_sql( $key, $value, &$where, &$values ) {
		$key = trim( $key );

		if ( strpos( $key, 'created_at' ) !== false || strpos( $key, 'updated_at' ) !== false ) {
			$k      = explode( ' ', $key );
			$where .= ' CAST(' . reset( $k ) . ' as CHAR) ' . str_replace( reset( $k ), '', $key );
		} else {
			$where .= ' ' . $key;
		}

		$lowercase_key = explode( ' ', strtolower( $key ) );
		$lowercase_key = end( $lowercase_key );

		if ( is_array( $value ) ) {
			// translate array of values to "in"
			if ( strpos( $lowercase_key, 'like' ) !== false ) {
				$where  = preg_replace( '/' . $key . '$/', '', $where );
				$where .= '(';
				$start  = true;
				foreach ( $value as $v ) {
					if ( ! $start ) {
						$where .= ' OR ';
					}
					$start    = false;
					$where   .= $key . ' %s';
					$values[] = '%' . self::esc_like( $v ) . '%';
				}
				$where .= ')';
			} elseif ( ! empty( $value ) ) {
				$where .= ' in (' . self::prepare_array_values( $value, '%s' ) . ')';
				$values = array_merge( $values, $value );
			}
		} elseif ( strpos( $lowercase_key, 'like' ) !== false ) {
			/**
			 * Allow string to start or end with the value
			 * If the key is like% then skip the first % for starts with
			 * If the key is %like then skip the last % for ends with
			 */
			$start = '%';
			$end   = '%';
			if ( $lowercase_key == 'like%' ) {
				$start = '';
				$where = rtrim( $where, '%' );
			} elseif ( $lowercase_key == '%like' ) {
				$end    = '';
				$where  = rtrim( rtrim( $where, '%like' ), '%LIKE' );
				$where .= 'like';
			}

			$where   .= ' %s';
			$values[] = $start . self::esc_like( $value ) . $end;

		} elseif ( $value === null ) {
			$where .= ' IS NULL';
		} else {
			// allow a - to prevent = from being added
			if ( substr( $key, - 1 ) == '-' ) {
				$where = rtrim( $where, '-' );
			} else {
				$where .= '=';
			}

			self::add_query_placeholder( $key, $value, $where );

			$values[] = $value;
		}//end if
	}

	/**
	 * Add %d, or %s to query
	 *
	 * @since 2.02.05
	 *
	 * @param string     $key
	 * @param int|string $value
	 * @param string     $where
	 */
	private static function add_query_placeholder( $key, $value, &$where ) {
		if ( is_numeric( $value ) && ( strpos( $key, 'meta_value' ) === false || strpos( $key, '+0' ) !== false ) ) {
			// Switch string to number.
			$value  = $value + 0;
			$where .= is_float( $value ) ? '%f' : '%d';
		} else {
			$where .= '%s';
		}
	}

	/**
	 * @param string $table
	 * @param array  $where
	 * @param array  $args
	 *
	 * @return int
	 */
	public static function get_count( $table, $where = array(), $args = array() ) {
		$count = self::get_var( $table, $where, 'COUNT(*)', $args );

		return (int) $count;
	}

	/**
	 * @param string $table
	 * @param array  $where
	 * @param string $field
	 * @param array  $args
	 * @param string $limit
	 * @param string $type
	 *
	 * @return array|object|string|null
	 */
	public static function get_var( $table, $where = array(), $field = 'id', $args = array(), $limit = '', $type = 'var' ) {
		$group = '';
		self::get_group_and_table_name( $table, $group );
		self::convert_options_to_array( $args, '', $limit );
		if ( $type === 'var' && ! isset( $args['limit'] ) ) {
			$args['limit'] = 1;
		}

		$query = self::generate_query_string_from_pieces( $field, $table, $where, $args );

		$cache_key = self::generate_cache_key( $where, $args, $field, $type );
		$results   = self::check_cache( $cache_key, $group, $query, 'get_' . $type );

		return $results;
	}

	/**
	 * Generate a cache key from the where query, field, type, and other arguments
	 *
	 * @since 2.03.07
	 *
	 * @param array  $where
	 * @param array  $args
	 * @param string $field
	 * @param string $type
	 *
	 * @return string
	 */
	public static function generate_cache_key( $where, $args, $field, $type ) {
		$cache_key = '';
		$where     = FrmAppHelper::array_flatten( $where );
		foreach ( $where as $key => $value ) {
			$cache_key .= $key . '_' . $value;
		}
		$cache_key .= implode( '_', $args ) . $field . '_' . $type;
		$cache_key  = str_replace( array( ' ', ',' ), '_', $cache_key );

		return $cache_key;
	}

	/**
	 * @param string $table
	 * @param array  $where
	 * @param string $field
	 * @param array  $args
	 * @param string $limit
	 *
	 * @return mixed
	 */
	public static function get_col( $table, $where = array(), $field = 'id', $args = array(), $limit = '' ) {
		return self::get_var( $table, $where, $field, $args, $limit, 'col' );
	}

	/**
	 * @since 2.0
	 *
	 * @param string $table
	 * @param array  $where
	 * @param string $fields
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public static function get_row( $table, $where = array(), $fields = '*', $args = array() ) {
		$args['limit'] = 1;

		return self::get_var( $table, $where, $fields, $args, '', 'row' );
	}

	/**
	 * Prepare a key/value array before DB call
	 *
	 * @since 2.0
	 *
	 * @param string $table
	 * @param array  $where
	 * @param string $fields
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public static function get_results( $table, $where = array(), $fields = '*', $args = array() ) {
		return self::get_var( $table, $where, $fields, $args, '', 'results' );
	}

	/**
	 * Check for like, not like, in, not in, =, !=, >, <, <=, >=
	 * Return a value to append to the where array key
	 *
	 * @param string $where_is
	 *
	 * @return string
	 */
	public static function append_where_is( $where_is ) {
		$switch_to = array(
			'='        => '',
			'!='       => '!',
			'<='       => '<',
			'>='       => '>',
			'like'     => 'like',
			'not like' => 'not like',
			'in'       => '',
			'not in'   => 'not',
			'like%'    => 'like%',
			'%like'    => '%like',
		);

		$where_is = strtolower( $where_is );
		if ( isset( $switch_to[ $where_is ] ) ) {
			return ' ' . $switch_to[ $where_is ];
		}

		// > and < need a little more work since we don't want them switched to >= and <=
		if ( $where_is == '>' || $where_is == '<' ) {
			// The - indicates that the = should not be added later.
			return ' ' . $where_is . '-';
		}

		// fallback to = if the query is none of these
		return '';
	}

	/**
	 * Get 'frm_forms' from wp_frm_forms or a longer table param that includes a join
	 * Also add the wpdb->prefix to the table if it's missing
	 *
	 * @param string $table
	 * @param string $group
	 */
	private static function get_group_and_table_name( &$table, &$group ) {
		global $wpdb, $wpmuBaseTablePrefix;

		$table_parts = explode( ' ', $table );
		$group       = reset( $table_parts );
		self::maybe_remove_prefix( $wpdb->prefix, $group );

		$prefix = $wpmuBaseTablePrefix ? $wpmuBaseTablePrefix : $wpdb->base_prefix;
		self::maybe_remove_prefix( $prefix, $group );

		if ( $group == $table ) {
			$table = $wpdb->prefix . $table;
		}

		// switch to singular group name
		$group = rtrim( $group, 's' );
	}

	/**
	 * Only remove the db prefix when at the beginning.
	 *
	 * @since 4.04.02
	 */
	private static function maybe_remove_prefix( $prefix, &$name ) {
		if ( substr( $name, 0, strlen( $prefix ) ) === $prefix ) {
			$name = substr( $name, strlen( $prefix ) );
		}
	}

	private static function convert_options_to_array( &$args, $order_by = '', $limit = '' ) {
		if ( ! is_array( $args ) ) {
			$args = array( 'order_by' => $args );
		}

		if ( ! empty( $order_by ) ) {
			$args['order_by'] = $order_by;
		}

		if ( ! empty( $limit ) ) {
			$args['limit'] = $limit;
		}

		$temp_args = $args;
		foreach ( $temp_args as $k => $v ) {
			if ( $v == '' ) {
				unset( $args[ $k ] );
				continue;
			}

			$db_name = strtoupper( str_replace( '_', ' ', $k ) );
			if ( strpos( $v, $db_name ) === false ) {
				$args[ $k ] = $db_name . ' ' . $v;
			}
		}

		// Make sure LIMIT is the last argument
		if ( isset( $args['order_by'] ) && isset( $args['limit'] ) ) {
			$temp_limit = $args['limit'];
			unset( $args['limit'] );
			$args['limit'] = $temp_limit;
		}
	}

	/**
	 * Get the associative array results for the given columns, table, and where query
	 *
	 * @since 2.02.05
	 *
	 * @param string $columns
	 * @param string $table
	 * @param array  $where
	 *
	 * @return mixed
	 */
	public static function get_associative_array_results( $columns, $table, $where ) {
		$group = '';
		self::get_group_and_table_name( $table, $group );

		$query = self::generate_query_string_from_pieces( $columns, $table, $where );

		$cache_key = str_replace( array( ' ', ',' ), '_', trim( implode( '_', FrmAppHelper::array_flatten( $where ) ) . $columns . '_results_ARRAY_A', ' WHERE' ) );
		$results   = self::check_cache( $cache_key, $group, $query, 'get_associative_results' );

		return $results;
	}

	/**
	 * Combine the pieces of a query to form a full, prepared query
	 *
	 * @since 2.02.05
	 *
	 * @param string $columns
	 * @param string $table
	 * @param mixed  $where
	 * @param array  $args
	 *
	 * @return string
	 */
	private static function generate_query_string_from_pieces( $columns, $table, $where, $args = array() ) {
		$query = 'SELECT ' . $columns . ' FROM ' . $table;

		self::esc_query_args( $args );

		if ( is_array( $where ) || empty( $where ) ) {
			self::get_where_clause_and_values( $where );
			global $wpdb;
			$query = $wpdb->prepare( $query . $where['where'] . ' ' . implode( ' ', $args ), $where['values'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			/**
			 * Allow the $where to be prepared before we recieve it here.
			 * This is a fallback for reverse compatibility, but is not recommended
			 */
			_deprecated_argument( 'where', '2.0', esc_html__( 'Use the query in an array format so it can be properly prepared.', 'formidable' ) );
			$query .= $where . ' ' . implode( ' ', $args );
		}

		return $query;
	}

	/**
	 * @since 2.05.07
	 */
	private static function esc_query_args( &$args ) {
		foreach ( $args as $param => $value ) {
			if ( $param == 'order_by' ) {
				$args[ $param ] = self::esc_order( $value );
			} elseif ( $param == 'limit' ) {
				$args[ $param ] = self::esc_limit( $value );
			}

			if ( $args[ $param ] == '' ) {
				unset( $args[ $param ] );
			}
		}
	}

	/**
	 * Added for < WP 4.0 compatability
	 *
	 * @since 2.05.06
	 *
	 * @param string $term The value to escape.
	 *
	 * @return string The escaped value
	 */
	public static function esc_like( $term ) {
		global $wpdb;

		return $wpdb->esc_like( $term );
	}

	/**
	 * @since 2.05.06
	 *
	 * @param string $order_query
	 */
	public static function esc_order( $order_query ) {
		if ( empty( $order_query ) ) {
			return '';
		}

		// remove ORDER BY before santizing
		$order_query = strtolower( $order_query );
		if ( strpos( $order_query, 'order by' ) !== false ) {
			$order_query = str_replace( 'order by', '', $order_query );
		}

		$order_query = explode( ' ', trim( $order_query ) );

		$order      = trim( reset( $order_query ) );
		$safe_order = array( 'count(*)' );
		if ( ! in_array( strtolower( $order ), $safe_order ) ) {
			$order = preg_replace( '/[^a-zA-Z0-9\-\_\.\+]/', '', $order );
		}

		$order_by = '';
		if ( count( $order_query ) > 1 ) {
			$order_by = end( $order_query );
			self::esc_order_by( $order_by );
		}

		return ' ORDER BY ' . $order . ' ' . $order_by;
	}

	/**
	 * Make sure this is ordering by either ASC or DESC
	 *
	 * @since 2.05.06
	 */
	public static function esc_order_by( &$order_by ) {
		$sort_options = array( 'asc', 'desc' );
		if ( ! in_array( strtolower( $order_by ), $sort_options, true ) ) {
			$order_by = 'asc';
		}
	}

	/**
	 * @since 2.05.06
	 * @param string $limit
	 */
	public static function esc_limit( $limit ) {
		if ( empty( $limit ) ) {
			return '';
		}

		$limit = trim( str_replace( 'limit ', '', strtolower( $limit ) ) );
		if ( is_numeric( $limit ) ) {
			return ' LIMIT ' . $limit;
		}

		$limit = explode( ',', trim( $limit ) );
		foreach ( $limit as $k => $l ) {
			if ( is_numeric( $l ) ) {
				$limit[ $k ] = $l;
			}
		}

		$limit = implode( ',', $limit );

		return ' LIMIT ' . $limit;
	}

	/**
	 * Get an array of values ready to go through $wpdb->prepare
	 *
	 * @since 2.05.06
	 */
	public static function prepare_array_values( $array, $type = '%s' ) {
		$placeholders = array_fill( 0, count( $array ), $type );

		return implode( ', ', $placeholders );
	}

	/**
	 * @since 2.05.06
	 *
	 * @param string       $starts_with
	 * @param array|string $where
	 * @return string
	 */
	public static function prepend_and_or_where( $starts_with = ' WHERE ', $where = '' ) {
		if ( empty( $where ) ) {
			$where = '';
		} elseif ( is_array( $where ) ) {
				global $wpdb;
				self::get_where_clause_and_values( $where, $starts_with );
				$where = $wpdb->prepare( $where['where'], $where['values'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$where = $starts_with . $where;
		}

		/**
		 * Allows modifying where clause when using FrmDb::prepend_and_or_where() method.
		 *
		 * @since 5.0.16
		 *
		 * @param string $where       Where string.
		 * @param string $starts_with The start of where string.
		 */
		return apply_filters( 'frm_prepend_and_or_where', $where, $starts_with );
	}

	/**
	 * Prepare and save settings in styles and actions
	 *
	 * @since 2.05.06
	 * @param array  $settings
	 * @param string $group
	 */
	public static function save_settings( $settings, $group ) {
		$settings                 = (array) $settings;
		$settings['post_content'] = FrmAppHelper::prepare_and_encode( $settings['post_content'] );

		if ( empty( $settings['ID'] ) ) {
			unset( $settings['ID'] );
		}

		// delete all caches for this group
		self::cache_delete_group( $group );

		return self::save_json_post( $settings );
	}

	/**
	 * Since actions are JSON encoded, we don't want any filters messing with it.
	 * Remove the filters and then add them back in case any posts or views are
	 * also being imported.
	 *
	 * Used when saving form actions and styles
	 *
	 * @since 2.05.06
	 *
	 * @param array $settings
	 * @return int|WP_Error
	 */
	public static function save_json_post( $settings ) {
		global $wp_filter;
		if ( isset( $wp_filter['content_save_pre'] ) ) {
			$filters = $wp_filter['content_save_pre'];
		}

		// Remove the balanceTags filter in case WordPress is trying to validate the XHTML
		remove_all_filters( 'content_save_pre' );

		$post = wp_insert_post( $settings );

		// add the content filters back for views or posts
		if ( isset( $filters ) ) {
			$wp_filter['content_save_pre'] = $filters;
		}

		return $post;
	}

	/**
	 * Check cache before fetching values and saving to cache
	 *
	 * @since 2.05.06
	 *
	 * @param string $cache_key The unique name for this cache.
	 * @param string $group     The name of the cache group.
	 * @param string $query     If blank, don't run a db call.
	 * @param string $type      The wpdb function to use with this query.
	 *
	 * @return mixed $results The cache or query results
	 */
	public static function check_cache( $cache_key, $group = '', $query = '', $type = 'get_var', $time = 300 ) {
		$results = wp_cache_get( $cache_key, $group );
		if ( ! FrmAppHelper::is_empty_value( $results, false ) || empty( $query ) ) {
			return $results;
		}

		if ( 'get_posts' == $type ) {
			$results = get_posts( $query );
		} elseif ( 'get_associative_results' == $type ) {
			global $wpdb;
			$results = $wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			global $wpdb;
			$results = $wpdb->{$type}( $query );
		}

		self::set_cache( $cache_key, $results, $group, $time );

		return $results;
	}

	/**
	 * @since 2.05.06
	 */
	public static function set_cache( $cache_key, $results, $group = '', $time = 300 ) {
		if ( ! FrmAppHelper::prevent_caching() ) {
			self::add_key_to_group_cache( $cache_key, $group );
			wp_cache_set( $cache_key, $results, $group, $time );
		}
	}

	/**
	 * Keep track of the keys cached in each group so they can be deleted
	 * in Redis and Memcache
	 *
	 * @since 2.05.06
	 */
	public static function add_key_to_group_cache( $key, $group ) {
		$cached         = self::get_group_cached_keys( $group );
		$cached[ $key ] = $key;
		wp_cache_set( 'cached_keys', $cached, $group, 300 );
	}

	/**
	 * @since 2.05.06
	 */
	public static function get_group_cached_keys( $group ) {
		$cached = wp_cache_get( 'cached_keys', $group );
		if ( ! $cached || ! is_array( $cached ) ) {
			$cached = array();
		}

		return $cached;
	}

	/**
	 * @since 2.05.06
	 *
	 * @param string $cache_key
	 */
	public static function delete_cache_and_transient( $cache_key, $group = 'default' ) {
		delete_transient( $cache_key );
		wp_cache_delete( $cache_key, $group );
	}

	/**
	 * Delete all caching in a single group
	 *
	 * @since 2.05.06
	 *
	 * @param string $group The name of the cache group.
	 */
	public static function cache_delete_group( $group ) {
		$cached_keys = self::get_group_cached_keys( $group );

		if ( ! empty( $cached_keys ) ) {
			foreach ( $cached_keys as $key ) {
				wp_cache_delete( $key, $group );
			}

			wp_cache_delete( 'cached_keys', $group );
		}
	}

	/**
	 * Checks if a DB column exists.
	 *
	 * @since 6.7
	 *
	 * @param string $table Table name without `$wpdb->prefix`.
	 * @param string $column Column name.
	 * @return bool
	 */
	public static function db_column_exists( $table, $column ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $wpdb->prefix . $table . ' LIKE %s', $column ) );
		return ! empty( $result );
	}
}
