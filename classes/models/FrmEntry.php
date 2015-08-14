<?php
if ( ! defined('ABSPATH') ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEntry {

    public static function create( $values ) {
        global $wpdb;

        self::sanitize_entry_post( $values );

        $values = apply_filters('frm_pre_create_entry', $values);

		if ( ! isset( $values['item_key'] ) ) {
			$values['item_key'] = '';
		}

		$item_name = self::get_new_entry_name( $values, $values['item_key'] );
        $new_values = array(
            'item_key'  => FrmAppHelper::get_unique_key($values['item_key'], $wpdb->prefix .'frm_items', 'item_key'),
			'name'      => FrmAppHelper::truncate( $item_name, 255, 1, '' ),
            'ip'        => FrmAppHelper::get_ip_address(),
            'is_draft'  => ( ( isset($values['frm_saving_draft']) && $values['frm_saving_draft'] == 1 ) ||  ( isset($values['is_draft']) && $values['is_draft'] == 1) ) ? 1 : 0,
            'form_id'   => isset($values['form_id']) ? (int) $values['form_id']: null,
			'post_id'   => isset( $values['post_id'] ) ? (int) $values['post_id']: 0,
			'parent_item_id' => isset( $values['parent_item_id'] ) ? (int) $values['parent_item_id']: 0,
            'created_at' => isset($values['created_at']) ? $values['created_at'] : current_time('mysql', 1),
            'updated_at' => isset($values['updated_at']) ? $values['updated_at'] : ( isset($values['created_at']) ? $values['created_at'] : current_time('mysql', 1) ),
        );

        if ( is_array($new_values['name']) ) {
            $new_values['name'] = reset($new_values['name']);
        }

        if ( isset($values['description']) && ! empty($values['description']) ) {
            $new_values['description'] = maybe_serialize($values['description']);
        } else {
            $new_values['description'] = serialize( array(
				'browser'  => FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' ),
				'referrer' => FrmAppHelper::get_server_value( 'HTTP_REFERER' ),
            ) );
        }

        //if(isset($values['id']) and is_numeric($values['id']))
        //    $new_values['id'] = $values['id'];

        if ( isset($values['frm_user_id']) && ( is_numeric($values['frm_user_id']) || FrmAppHelper::is_admin() ) ) {
            $new_values['user_id'] = $values['frm_user_id'];
        } else {
            $user_ID = get_current_user_id();
            $new_values['user_id'] = $user_ID ? $user_ID : 0;
        }

        $new_values['updated_by'] = isset($values['updated_by']) ? $values['updated_by'] : $new_values['user_id'];

        // don't create duplicate entry
        if ( self::is_duplicate($new_values, $values) ) {
            return false;
        }

        $query_results = $wpdb->insert( $wpdb->prefix .'frm_items', $new_values );

        if ( ! $query_results ) {
            return false;
        }

        $entry_id = $wpdb->insert_id;

        global $frm_vars;
        if ( ! isset($frm_vars['saved_entries']) ) {
            $frm_vars['saved_entries'] = array();
        }
        $frm_vars['saved_entries'][] = (int) $entry_id;

        if ( isset($values['item_meta']) ) {
            FrmEntryMeta::update_entry_metas($entry_id, $values['item_meta']);
        }

		self::clear_cache();

		// this is a child entry
		$is_child = isset( $values['parent_form_id'] ) && isset( $values['parent_nonce'] ) && ! empty( $values['parent_form_id'] ) && wp_verify_nonce( $values['parent_nonce'], 'parent' );

		do_action( 'frm_after_create_entry', $entry_id, $new_values['form_id'], compact( 'is_child' ) );
		do_action( 'frm_after_create_entry_'. $new_values['form_id'], $entry_id , compact( 'is_child' ) );

        return $entry_id;
    }

    /**
     * check for duplicate entries created in the last minute
     * @return boolean
     */
    public static function is_duplicate($new_values, $values) {
		if ( defined('WP_IMPORTING') && WP_IMPORTING ) {
            return false;
        }

		$duplicate_entry_time = apply_filters( 'frm_time_to_check_duplicates', 60, $new_values );
		if ( empty( $duplicate_entry_time ) ) {
			return false;
		}

        $check_val = $new_values;
		$check_val['created_at >'] = date( 'Y-m-d H:i:s', ( strtotime( $new_values['created_at'] ) - absint( $duplicate_entry_time ) ) );

		unset( $check_val['created_at'], $check_val['updated_at'] );
		unset( $check_val['is_draft'], $check_val['id'], $check_val['item_key'] );

        if ( $new_values['item_key'] == $new_values['name'] ) {
            unset($check_val['name']);
        }

        global $wpdb;
		$entry_exists = FrmDb::get_col( $wpdb->prefix . 'frm_items', $check_val, 'id', array( 'order_by' => 'created_at DESC' ) );

        if ( ! $entry_exists || empty($entry_exists) || ! isset($values['item_meta']) ) {
            return false;
        }

        $is_duplicate = false;
        foreach ( $entry_exists as $entry_exist ) {
            $is_duplicate = true;

            //add more checks here to make sure it's a duplicate
            $metas = FrmEntryMeta::get_entry_meta_info($entry_exist);
            $field_metas = array();
            foreach ( $metas as $meta ) {
				$field_metas[ $meta->field_id ] = $meta->meta_value;
            }

            // If prev entry is empty and current entry is not, they are not duplicates
            $filtered_vals = array_filter( $values['item_meta'] );
            if ( empty( $field_metas ) && ! empty( $filtered_vals ) ) {
                return false;
            }

            $diff = array_diff_assoc($field_metas, array_map('maybe_serialize', $values['item_meta']));
            foreach ( $diff as $field_id => $meta_value ) {
                if ( ! empty($meta_value) ) {
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

    public static function duplicate( $id ) {
        global $wpdb;

        $values = self::getOne( $id );

        $new_values = array();
        $new_values['item_key'] = FrmAppHelper::get_unique_key('', $wpdb->prefix .'frm_items', 'item_key');
        $new_values['name'] = $values->name;
        $new_values['is_draft'] = $values->is_draft;
        $new_values['user_id'] = $new_values['updated_by'] = (int) $values->user_id;
        $new_values['form_id'] = $values->form_id ? (int) $values->form_id: null;
        $new_values['created_at'] = $new_values['updated_at'] = current_time('mysql', 1);

        $query_results = $wpdb->insert( $wpdb->prefix .'frm_items', $new_values );
        if ( ! $query_results ) {
            return false;
        }

        $entry_id = $wpdb->insert_id;

        global $frm_vars;
        if ( ! isset($frm_vars['saved_entries']) ) {
            $frm_vars['saved_entries'] = array();
        }
        $frm_vars['saved_entries'][] = (int) $entry_id;

        FrmEntryMeta::duplicate_entry_metas($id, $entry_id);
		self::clear_cache();

		do_action( 'frm_after_duplicate_entry', $entry_id, $new_values['form_id'], array( 'old_id' => $id ) );
        return $entry_id;
    }

    public static function update( $id, $values ) {
        global $wpdb, $frm_vars;
        if ( isset($frm_vars['saved_entries']) && is_array($frm_vars['saved_entries']) && in_array( (int) $id, (array) $frm_vars['saved_entries'] ) ) {
            return;
        }

        $values = apply_filters('frm_pre_update_entry', $values, $id);

        $user_ID = get_current_user_id();

		$item_name = self::get_new_entry_name( $values );
        $new_values = array(
			'name'      => $item_name,
            'form_id'   => isset($values['form_id']) ? (int) $values['form_id'] : null,
            'is_draft'  => ( ( isset($values['frm_saving_draft']) && $values['frm_saving_draft'] == 1 ) ||  ( isset($values['is_draft']) && $values['is_draft'] == 1) ) ? 1 : 0,
            'updated_at' => current_time('mysql', 1),
            'updated_by' => isset($values['updated_by']) ? $values['updated_by'] : $user_ID,
        );

        if ( isset($values['post_id']) ) {
            $new_values['post_id'] = (int) $values['post_id'];
        }

        if ( isset($values['item_key']) ) {
            $new_values['item_key'] = FrmAppHelper::get_unique_key($values['item_key'], $wpdb->prefix .'frm_items', 'item_key', $id);
        }

        if ( isset($values['parent_item_id']) ) {
            $new_values['parent_item_id'] = (int) $values['parent_item_id'];
        }

        if ( isset($values['frm_user_id']) && is_numeric($values['frm_user_id']) ) {
            $new_values['user_id'] = $values['frm_user_id'];
        }

        $new_values = apply_filters('frm_update_entry', $new_values, $id);
        $query_results = $wpdb->update( $wpdb->prefix .'frm_items', $new_values, compact('id') );

        if ( $query_results ) {
			self::clear_cache();
        }

        if ( ! isset( $frm_vars['saved_entries'] ) ) {
            $frm_vars['saved_entries'] = array();
        }

        $frm_vars['saved_entries'][] = (int) $id;

        if ( isset($values['item_meta']) ) {
            FrmEntryMeta::update_entry_metas($id, $values['item_meta']);
        }
        do_action('frm_after_update_entry', $id, $new_values['form_id']);
        do_action('frm_after_update_entry_'. $new_values['form_id'], $id);
        return $query_results;
    }

	public static function &destroy( $id ) {
        global $wpdb;
        $id = (int) $id;

		$entry = self::getOne( $id );
        if ( ! $entry ) {
            $result = false;
            return $result;
        }

        do_action('frm_before_destroy_entry', $id, $entry);

        $wpdb->query( $wpdb->prepare('DELETE FROM ' . $wpdb->prefix .'frm_item_metas WHERE item_id=%d', $id) );
        $result = $wpdb->query( $wpdb->prepare('DELETE FROM ' . $wpdb->prefix .'frm_items WHERE id=%d', $id) );

		self::clear_cache();

        return $result;
    }

	public static function &update_form( $id, $value, $form_id ) {
        global $wpdb;
        $form_id = isset($value) ? $form_id : null;
		$result = $wpdb->update( $wpdb->prefix . 'frm_items', array( 'form_id' => $form_id ), array( 'id' => $id ) );
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
		FrmAppHelper::cache_delete_group( 'frm_entry' );
		FrmAppHelper::cache_delete_group( 'frm_item' );
		FrmAppHelper::cache_delete_group( 'frm_entry_meta' );
		FrmAppHelper::cache_delete_group( 'frm_item_meta' );
	}

	/**
	 * After switching to the wp_loaded hook for processing entries,
	 * we can no longer use 'name', but check it as a fallback
	 * @since 2.0.11
	 */
	public static function get_new_entry_name( $values, $default = '' ) {
		return isset( $values['item_name'] ) ? $values['item_name'] : ( isset( $values['name'] ) ? $values['name'] : $default );
	}

	/**
	 * If $entry is numeric, get the entry object
	 * @param int|object $entry by reference
	 * @since 2.0.9
	 */
	public static function maybe_get_entry( &$entry ) {
		if ( $entry && is_numeric( $entry ) ) {
			$entry = self::getOne( $entry );
		}
	}

    public static function getOne( $id, $meta = false) {
        global $wpdb;

        $query = "SELECT it.*, fr.name as form_name, fr.form_key as form_key FROM {$wpdb->prefix}frm_items it
                  LEFT OUTER JOIN {$wpdb->prefix}frm_forms fr ON it.form_id=fr.id WHERE ";

        $query .= is_numeric($id) ? 'it.id=%d' : 'it.item_key=%s';
        $query_args = array( $id );
        $query = $wpdb->prepare( $query, $query_args );

        if ( ! $meta ) {
            $entry = FrmAppHelper::check_cache( $id .'_nometa', 'frm_entry', $query, 'get_row' );
            return stripslashes_deep($entry);
        }

        $entry = FrmAppHelper::check_cache( $id, 'frm_entry' );
        if ( $entry !== false ) {
            return stripslashes_deep($entry);
        }

        $entry = $wpdb->get_row( $query );
        $entry = self::get_meta($entry);

        return stripslashes_deep($entry);
    }

    public static function get_meta($entry) {
        if ( ! $entry ) {
            return $entry;
        }

        global $wpdb;
		$metas = FrmDb::get_results( $wpdb->prefix . 'frm_item_metas m LEFT JOIN ' . $wpdb->prefix . 'frm_fields f ON m.field_id=f.id', array( 'item_id' => $entry->id, 'field_id !' => 0 ), 'field_id, meta_value, field_key, item_id' );

        $entry->metas = array();

		$include_key = apply_filters( 'frm_include_meta_keys', false );
        foreach ( $metas as $meta_val ) {
            if ( $meta_val->item_id == $entry->id ) {
				$entry->metas[ $meta_val->field_id ] = maybe_unserialize( $meta_val->meta_value );
				if ( $include_key ) {
					$entry->metas[ $meta_val->field_key ] = $entry->metas[ $meta_val->field_id ];
				}
                 continue;
            }

            // include sub entries in an array
			if ( ! isset( $entry_metas[ $meta_val->field_id ] ) ) {
				$entry->metas[ $meta_val->field_id ] = array();
            }

			$entry->metas[ $meta_val->field_id ][] = maybe_unserialize( $meta_val->meta_value );

            unset($meta_val);
        }
        unset($metas);

        wp_cache_set( $entry->id, $entry, 'frm_entry');

        return $entry;
    }

    /**
     * @param string $id
     */
	public static function &exists( $id ) {
        global $wpdb;

        if ( FrmAppHelper::check_cache( $id, 'frm_entry' ) ) {
            $exists = true;
            return $exists;
        }

        if ( is_numeric($id) ) {
            $where = array( 'id' => $id );
        } else {
            $where = array( 'item_key' => $id );
        }
        $id = FrmDb::get_var( $wpdb->prefix .'frm_items', $where );

        $exists = ($id && $id > 0) ? true : false;
        return $exists;
    }

    public static function getAll( $where, $order_by = '', $limit = '', $meta = false, $inc_form = true ) {
		global $wpdb;

        $limit = FrmAppHelper::esc_limit($limit);

        $cache_key = maybe_serialize($where) . $order_by . $limit . $inc_form;
        $entries = wp_cache_get($cache_key, 'frm_entry');

        if ( false === $entries ) {
            $fields = 'it.id, it.item_key, it.name, it.ip, it.form_id, it.post_id, it.user_id, it.parent_item_id, it.updated_by, it.created_at, it.updated_at, it.is_draft';
            $table = $wpdb->prefix .'frm_items it ';

            if ( $inc_form ) {
                $fields = 'it.*, fr.name as form_name,fr.form_key as form_key';
                $table .= 'LEFT OUTER JOIN ' . $wpdb->prefix . 'frm_forms fr ON it.form_id=fr.id ';
            }

            if ( preg_match( '/ meta_([0-9]+)/', $order_by, $order_matches ) ) {
    		    // sort by a requested field
                $field_id = (int) $order_matches[1];
				$fields .= ', (SELECT meta_value FROM '. $wpdb->prefix .'frm_item_metas WHERE field_id = '. $field_id .' AND item_id = it.id) as meta_'. $field_id;
				unset( $order_matches, $field_id );
		    }

			// prepare the query
			$query = 'SELECT ' . $fields . ' FROM ' . $table . FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;

            $entries = $wpdb->get_results($query, OBJECT_K);
            unset($query);

			if ( ! FrmAppHelper::prevent_caching() ) {
				wp_cache_set( $cache_key, $entries, 'frm_entry', 300 );
			}
        }

        if ( ! $meta || ! $entries ) {
            return stripslashes_deep($entries);
        }
        unset($meta);

        if ( ! is_array( $where ) && preg_match('/^it\.form_id=\d+$/', $where) ) {
			$where = array( 'it.form_id' => substr( $where, 11 ) );
        }

        $meta_where = array( 'field_id !' => 0 );
        if ( $limit == '' && is_array($where) && count($where) == 1 && isset($where['it.form_id']) ) {
            $meta_where['fi.form_id'] = $where['it.form_id'];
        } else {
            $meta_where['item_id'] = array_keys( $entries );
        }

        $metas = FrmDb::get_results( $wpdb->prefix . 'frm_item_metas it LEFT OUTER JOIN ' . $wpdb->prefix . 'frm_fields fi ON (it.field_id = fi.id)', $meta_where, 'item_id, meta_value, field_id, field_key, form_id' );

        unset( $meta_where );

        if ( ! $metas ) {
            return stripslashes_deep($entries);
        }

        foreach ( $metas as $m_key => $meta_val ) {
            if ( ! isset( $entries[ $meta_val->item_id ] ) ) {
                continue;
            }

            if ( ! isset( $entries[ $meta_val->item_id ]->metas ) ) {
				$entries[ $meta_val->item_id ]->metas = array();
            }

			$entries[ $meta_val->item_id ]->metas[ $meta_val->field_id ] = maybe_unserialize( $meta_val->meta_value );

            unset($m_key, $meta_val);
        }

		if ( ! FrmAppHelper::prevent_caching() ) {
			foreach ( $entries as $entry ) {
				wp_cache_set( $entry->id, $entry, 'frm_entry' );
				unset( $entry );
			}
		}

        return stripslashes_deep($entries);
    }

    // Pagination Methods
    public static function getRecordCount( $where = '' ) {
        global $wpdb;
        $table_join = $wpdb->prefix .'frm_items it LEFT OUTER JOIN '. $wpdb->prefix .'frm_forms fr ON it.form_id=fr.id';

        if ( is_numeric($where) ) {
            $table_join = 'frm_items';
            $where = array( 'form_id' => $where );
        }

        if ( is_array( $where ) ) {
            $count = FrmDb::get_count( $table_join, $where );
        } else {
            global $wpdb;
            $cache_key = 'count_'. maybe_serialize($where);
            $query = 'SELECT COUNT(*) FROM '. $table_join . FrmAppHelper::prepend_and_or_where(' WHERE ', $where);
            $count = FrmAppHelper::check_cache($cache_key, 'frm_entry', $query, 'get_var');
        }

        return $count;
    }

    public static function getPageCount( $p_size, $where = '' ) {
        if ( is_numeric($where) ) {
            return ceil( (int) $where / (int) $p_size );
        } else {
            return ceil( (int) self::getRecordCount($where) / (int) $p_size );
        }
    }

    /**
     * Sanitize the POST values before we use them
     *
     * @since 2.0
     * @param array $values The POST values by reference
     */
    public static function sanitize_entry_post( &$values ) {
        $sanitize_method = array(
            'form_id'       => 'absint',
            'frm_action'    => 'sanitize_title',
            'form_key'      => 'sanitize_title',
            'item_key'      => 'sanitize_title',
            'item_name'     => 'sanitize_text_field',
            'frm_saving_draft' => 'absint',
            'is_draft'      => 'absint',
            'post_id'       => 'absint',
            'parent_item_id' => 'absint',
            'created_at'    => 'sanitize_text_field',
            'updated_at'    => 'sanitize_text_field',
        );

        FrmAppHelper::sanitize_request( $sanitize_method, $values );
    }

    /**
     * @param string $key
     * @return int entry_id
     */
	public static function get_id_by_key( $key ) {
        $entry_id = FrmDb::get_var( 'frm_items', array( 'item_key' => sanitize_title( $key ) ) );
        return $entry_id;
    }

	public static function validate( $values, $exclude = false ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryValidate::validate' );
		return FrmEntryValidate::validate( $values, $exclude );
	}

	public static function validate_field( $posted_field, &$errors, $values, $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryValidate::validate_field' );
		FrmEntryValidate::validate_field( $posted_field, $errors, $values, $args );
	}

	public static function validate_url_field( &$errors, $field, &$value, $args ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryValidate::validate_url_field' );
		FrmEntryValidate::validate_url_field( $errors, $field, $value, $args );
	}

	public static function validate_email_field( &$errors, $field, $value, $args ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryValidate::validate_email_field' );
		FrmEntryValidate::validate_email_field( $errors, $field, $value, $args );
	}

	public static function validate_recaptcha( &$errors, $field, $args ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryValidate::validate_recaptcha' );
		FrmEntryValidate::validate_recaptcha( $errors, $field, $args );
	}

	public static function spam_check( $exclude, $values, &$errors ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryValidate::spam_check' );
		FrmEntryValidate::spam_check( $exclude, $values, $errors );
	}

	public static function blacklist_check( $values ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryValidate::blacklist_check' );
		return FrmEntryValidate::blacklist_check( $values );
	}

	public static function akismet( $values ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryValidate::akismet' );
		return FrmEntryValidate::akismet( $values );
	}

}
