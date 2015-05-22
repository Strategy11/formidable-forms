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

        $new_values = array(
            'item_key'  => FrmAppHelper::get_unique_key($values['item_key'], $wpdb->prefix .'frm_items', 'item_key'),
			'name'      => FrmAppHelper::truncate( ( isset( $values['name'] ) ? $values['name'] : $values['item_key'] ), 255, 1, '' ),
            'ip'        => FrmAppHelper::get_ip_address(),
            'is_draft'  => ( ( isset($values['frm_saving_draft']) && $values['frm_saving_draft'] == 1 ) ||  ( isset($values['is_draft']) && $values['is_draft'] == 1) ) ? 1 : 0,
            'form_id'   => isset($values['form_id']) ? (int) $values['form_id']: null,
            'post_id'   => isset($values['post_id']) ? (int) $values['post_id']: null,
            'parent_item_id' => isset($values['parent_item_id']) ? (int) $values['parent_item_id']: null,
            'created_at' => isset($values['created_at']) ? $values['created_at'] : current_time('mysql', 1),
            'updated_at' => isset($values['updated_at']) ? $values['updated_at'] : ( isset($values['created_at']) ? $values['created_at'] : current_time('mysql', 1) ),
        );

        if ( is_array($new_values['name']) ) {
            $new_values['name'] = reset($new_values['name']);
        }

        if ( isset($values['description']) && ! empty($values['description']) ) {
            $new_values['description'] = maybe_serialize($values['description']);
        } else {
            $referrerinfo = FrmAppHelper::get_server_value('HTTP_REFERER');

            $new_values['description'] = serialize( array(
                'browser' => FrmAppHelper::get_server_value('HTTP_USER_AGENT'),
                'referrer' => $referrerinfo,
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
        if ( defined('WP_IMPORTING') ) {
            return false;
        }

        $check_val = $new_values;
		$check_val['created_at >'] = date( 'Y-m-d H:i:s', ( strtotime( $new_values['created_at'] ) - 60 ) );

        unset($check_val['created_at'], $check_val['updated_at']);
        unset($check_val['is_draft'], $check_val['id'], $check_val['item_key']);

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
                return $is_duplicate;
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

        $new_values = array(
            'name'      => isset($values['name']) ? $values['name'] : '',
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

    public static function validate( $values, $exclude = false ) {
        global $wpdb;

        self::sanitize_entry_post( $values );
        $errors = array();

        if ( ! isset($values['form_id']) || ! isset($values['item_meta']) ) {
            $errors['form'] = __( 'There was a problem with your submission. Please try again.', 'formidable' );
            return $errors;
        }

		if ( FrmAppHelper::is_admin() && is_user_logged_in() && ( ! isset( $values[ 'frm_submit_entry_' . $values['form_id'] ] ) || ! wp_verify_nonce( $values[ 'frm_submit_entry_' . $values['form_id'] ], 'frm_submit_entry_nonce' ) ) ) {
            $errors['form'] = __( 'You do not have permission to do that', 'formidable' );
        }

        if ( ! isset($values['item_key']) || $values['item_key'] == '' ) {
            $_POST['item_key'] = $values['item_key'] = FrmAppHelper::get_unique_key('', $wpdb->prefix .'frm_items', 'item_key');
        }

        $where = apply_filters('frm_posted_field_ids', array( 'fi.form_id' => $values['form_id'] ) );
		// Don't get subfields
		$where['fr.parent_form_id'] = array( null, 0 );
		// Don't get excluded fields (like file upload fields in the ajax validation)
		if ( ! empty( $exclude ) ) {
			$where['fi.type not'] = $exclude;
		}

        $posted_fields = FrmField::getAll($where, 'field_order');

        // Pass exclude value to validate_field function so it can be used for repeating sections
        $args = array( 'exclude' => $exclude );

        foreach ( $posted_fields as $posted_field ) {
            self::validate_field($posted_field, $errors, $values, $args);
            unset($posted_field);
        }

        // check for spam
        self::spam_check($exclude, $values, $errors);

        $errors = apply_filters('frm_validate_entry', $errors, $values, compact('exclude'));

        return $errors;
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
            'name'          => 'sanitize_text_field',
            'frm_saving_draft' => 'absint',
            'is_draft'      => 'absint',
            'post_id'       => 'absint',
            'parent_item_id' => 'absint',
            'created_at'    => 'sanitize_title',
            'updated_at'    => 'sanitize_title',
        );

        FrmAppHelper::sanitize_request( $sanitize_method, $values );
    }

    public static function validate_field($posted_field, &$errors, $values, $args = array()) {
        $defaults = array(
            'id'    => $posted_field->id,
            'parent_field_id' => '', // the id of the repeat or embed form
            'key_pointer' => '', // the pointer in the posted array
            'exclude'   => array(), // exclude these field types from validation
        );
        $args = wp_parse_args( $args, $defaults );

        if ( empty($args['parent_field_id']) ) {
			$value = isset( $values['item_meta'][ $args['id'] ] ) ? $values['item_meta'][ $args['id'] ] : '';
        } else {
            // value is from a nested form
            $value = $values;
        }

        // Check for values in "Other" fields
        FrmEntriesHelper::maybe_set_other_validation( $posted_field, $value, $args );

        if ( isset($posted_field->field_options['default_blank']) && $posted_field->field_options['default_blank'] && $value == $posted_field->default_value ) {
            $value = '';
        }

		// Check for an array with only one value
		// Don't reset values in "Other" fields because array keys need to be preserved
		if ( is_array($value) && count( $value ) == 1 && $args['other'] !== true ) {
			$value = reset($value);
		}

        if ( $posted_field->required == '1' && ! is_array( $value ) && trim( $value ) == '' ) {
            $frm_settings = FrmAppHelper::get_settings();
			$errors[ 'field' . $args['id'] ] = ( ! isset( $posted_field->field_options['blank'] ) || $posted_field->field_options['blank'] == '' ) ? $frm_settings->blank_msg : $posted_field->field_options['blank'];
        } else if ( $posted_field->type == 'text' && ! isset( $_POST['name'] ) ) {
            $_POST['name'] = $value;
        }

        self::validate_url_field($errors, $posted_field, $value, $args);
        self::validate_email_field($errors, $posted_field, $value, $args);

        FrmEntriesHelper::set_posted_value($posted_field, $value, $args);

        self::validate_recaptcha($errors, $posted_field, $args);

        $errors = apply_filters('frm_validate_field_entry', $errors, $posted_field, $value, $args);
    }

    public static function validate_url_field(&$errors, $field, &$value, $args) {
		if ( $value == '' || ! in_array( $field->type, array( 'website', 'url', 'image' ) ) ) {
            return;
        }

        if ( trim($value) == 'http://' ) {
            $value = '';
        } else {
            $value = esc_url_raw( $value );
            $value = preg_match('/^(https?|ftps?|mailto|news|feed|telnet):/is', $value) ? $value : 'http://'. $value;
        }

        //validate the url format
        if ( ! preg_match('/^http(s)?:\/\/([\da-z\.-]+)\.([\da-z\.-]+)/i', $value) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
        }
    }

    public static function validate_email_field(&$errors, $field, $value, $args) {
        if ( $value == '' || $field->type != 'email' ) {
            return;
        }

        //validate the email format
        if ( ! is_email($value) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
        }
    }

    public static function validate_recaptcha(&$errors, $field, $args) {
        if ( $field->type != 'captcha' || FrmAppHelper::is_admin() ) {
            return;
        }

		$frm_settings = FrmAppHelper::get_settings();
		if ( empty( $frm_settings->pubkey ) ) {
			// don't require the captcha if it shouldn't be shown
			return;
		}

        if ( ! isset($_POST['g-recaptcha-response']) ) {
            // If captcha is missing, check if it was already verified
			if ( ! isset( $_POST['recaptcha_checked'] ) || ! wp_verify_nonce( $_POST['recaptcha_checked'], 'frm_ajax' ) ) {
                // There was no captcha submitted
				$errors[ 'field' . $args['id'] ] = __( 'The captcha is missing from this form', 'formidable' );
            }
            return;
        }

        $arg_array = array(
            'body'      => array(
				'secret'   => $frm_settings->privkey,
				'response' => $_POST['g-recaptcha-response'],
				'remoteip' => FrmAppHelper::get_ip_address(),
			),
		);
        $resp = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', $arg_array );
        $response = json_decode(wp_remote_retrieve_body( $resp ), true);

        if ( isset( $response['success'] ) && ! $response['success'] ) {
            // What happens when the CAPTCHA was entered incorrectly
			$errors[ 'field' . $args['id'] ] = ( ! isset( $field->field_options['invalid'] ) || $field->field_options['invalid'] == '' ) ? $frm_settings->re_msg : $field->field_options['invalid'];
        }
    }

    /**
     * check for spam
     * @param boolean $exclude
     * @param array $values
     * @param array $errors by reference
     */
    public static function spam_check($exclude, $values, &$errors) {
        if ( ! empty($exclude) || ! isset($values['item_meta']) || empty($values['item_meta']) || ! empty($errors) ) {
            // only check spam if there are no other errors
            return;
        }

        global $wpcom_api_key;
        if ( ( function_exists( 'akismet_http_post' ) || is_callable('Akismet::http_post') ) && ( get_option('wordpress_api_key') || $wpcom_api_key ) && self::akismet($values) ) {
            $form = FrmForm::getOne($values['form_id']);

            if ( isset($form->options['akismet']) && ! empty($form->options['akismet']) && ( $form->options['akismet'] != 'logged' || ! is_user_logged_in() ) ) {
	            $errors['spam'] = __( 'Your entry appears to be spam!', 'formidable' );
	        }
	    }

	    // check for blacklist keys
    	if ( self::blacklist_check($values) ) {
            $errors['spam'] = __( 'Your entry appears to be spam!', 'formidable' );
    	}
    }

    // check the blacklisted words
    public static function blacklist_check( $values ) {
        if ( ! apply_filters('frm_check_blacklist', true, $values) ) {
            return false;
        }

    	$mod_keys = trim( get_option( 'blacklist_keys' ) );

    	if ( empty( $mod_keys ) ) {
    		return false;
    	}

    	$content = FrmEntriesHelper::entry_array_to_string($values);

		if ( empty($content) ) {
		    return false;
		}

    	$words = explode( "\n", $mod_keys );

    	foreach ( (array) $words as $word ) {
    		$word = trim( $word );

    		if ( empty($word) ) {
    			continue;
    		}

    		if ( preg_match('#' . preg_quote( $word, '#' ) . '#', $content) ) {
    			return true;
    		}
    	}

    	return false;
    }

    /**
     * Check entries for spam
     *
     * @return boolean true if is spam
     */
    public static function akismet($values) {
	    $content = FrmEntriesHelper::entry_array_to_string($values);

		if ( empty($content) ) {
		    return false;
		}

        $datas = array();
        self::parse_akismet_array($datas, $content);

		$query_string = '';
		foreach ( $datas as $key => $data ) {
			$query_string .= $key . '=' . urlencode( stripslashes( $data ) ) . '&';
			unset($key, $data);
		}

        if ( is_callable('Akismet::http_post') ) {
            $response = Akismet::http_post($query_string, 'comment-check');
        } else {
            global $akismet_api_host, $akismet_api_port;
            $response = akismet_http_post( $query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );
        }

		return ( is_array($response) && $response[1] == 'true' ) ? true : false;
    }

    /**
     * Called by FrmEntry::akismet
     * @since 2.0
     *
     * @param string $content
     */
    private  static function parse_akismet_array( &$datas, $content ) {
        $datas['blog'] = FrmAppHelper::site_url();
        $datas['user_ip'] = preg_replace( '/[^0-9., ]/', '', FrmAppHelper::get_ip_address() );
		$datas['user_agent'] = FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' );
		$datas['referrer'] = isset( $_SERVER['HTTP_REFERER'] ) ? FrmAppHelper::get_server_value( 'HTTP_REFERER' ) : false;
        $datas['comment_type'] = 'formidable';
        $datas['comment_content'] = $content;

        if ( $permalink = get_permalink() ) {
            $datas['permalink'] = $permalink;
        }

        foreach ( $_SERVER as $key => $value ) {
			if ( ! in_array( $key, array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' ) ) && is_string( $value ) ) {
				$datas[ $key ] = wp_strip_all_tags( $value );
            } else {
				$datas[ $key ] = '';
            }

            unset($key, $value);
        }
    }
}
