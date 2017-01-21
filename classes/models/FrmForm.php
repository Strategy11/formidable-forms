<?php
if ( ! defined('ABSPATH') ) {
    die('You are not allowed to call this page directly.');
}

class FrmForm {

    /**
     * @return int|boolean id on success or false on failure
     */
    public static function create( $values ) {
        global $wpdb;

        $new_values = array(
			'form_key'      => FrmAppHelper::get_unique_key( $values['form_key'], $wpdb->prefix . 'frm_forms', 'form_key' ),
            'name'          => $values['name'],
            'description'   => $values['description'],
            'status'        => isset($values['status']) ? $values['status'] : 'draft',
            'logged_in'     => isset($values['logged_in']) ? $values['logged_in'] : 0,
            'is_template'   => isset($values['is_template']) ? (int) $values['is_template'] : 0,
			'parent_form_id' => isset( $values['parent_form_id'] ) ? absint( $values['parent_form_id'] ) : 0,
            'editable'      => isset($values['editable']) ? (int) $values['editable'] : 0,
            'default_template' => isset($values['default_template']) ? (int) $values['default_template'] : 0,
            'created_at'    => isset($values['created_at']) ? $values['created_at'] : current_time('mysql', 1),
        );

		$options = isset( $values['options'] ) ? (array) $values['options'] : array();
		FrmFormsHelper::fill_form_options( $options, $values );

        $options['before_html'] = isset($values['options']['before_html']) ? $values['options']['before_html'] : FrmFormsHelper::get_default_html('before');
        $options['after_html'] = isset($values['options']['after_html']) ? $values['options']['after_html'] : FrmFormsHelper::get_default_html('after');
        $options['submit_html'] = isset($values['options']['submit_html']) ? $values['options']['submit_html'] : FrmFormsHelper::get_default_html('submit');

        $options = apply_filters('frm_form_options_before_update', $options, $values);
        $new_values['options'] = serialize($options);

        //if(isset($values['id']) && is_numeric($values['id']))
        //    $new_values['id'] = $values['id'];

		$wpdb->insert( $wpdb->prefix . 'frm_forms', $new_values );

        $id = $wpdb->insert_id;

		// Clear form caching
		self::clear_form_cache();

        return $id;
    }

    /**
     * @return int|boolean ID on success or false on failure
     */
    public static function duplicate( $id, $template = false, $copy_keys = false, $blog_id = false ) {
        global $wpdb;

        $values = self::getOne( $id, $blog_id );
        if ( ! $values ) {
            return false;
        }

        $new_key = $copy_keys ? $values->form_key : '';

        $new_values = array(
			'form_key'      => FrmAppHelper::get_unique_key( $new_key, $wpdb->prefix . 'frm_forms', 'form_key' ),
            'name'          => $values->name,
            'description'   => $values->description,
            'status'        => $template ? 'published' : 'draft',
            'logged_in'     => $values->logged_in ? $values->logged_in : 0,
            'editable'      => $values->editable ? $values->editable : 0,
            'created_at'    => current_time('mysql', 1),
            'is_template'   => $template ? 1 : 0,
        );

        if ( $blog_id ) {
            $new_values['status'] = 'published';
            $new_options = maybe_unserialize($values->options);
            $new_options['email_to'] = get_option('admin_email');
            $new_options['copy'] = false;
            $new_values['options'] = $new_options;
        } else {
            $new_values['options'] = $values->options;
        }

        if ( is_array($new_values['options']) ) {
            $new_values['options'] = serialize($new_values['options']);
        }

		$query_results = $wpdb->insert( $wpdb->prefix . 'frm_forms', $new_values );

        if ( $query_results ) {
			// Clear form caching
			self::clear_form_cache();

            $form_id = $wpdb->insert_id;
            FrmField::duplicate($id, $form_id, $copy_keys, $blog_id);

            // update form settings after fields are created
			do_action( 'frm_after_duplicate_form', $form_id, $new_values, array( 'old_id' => $id ) );
            return $form_id;
        }

        return false;
    }

	public static function after_duplicate( $form_id, $values ) {
		$new_opts = maybe_unserialize( $values['options'] );
		$values['options'] = $new_opts;

        if ( isset($new_opts['success_msg']) ) {
            $new_opts['success_msg'] = FrmFieldsHelper::switch_field_ids($new_opts['success_msg']);
        }

        $new_opts = apply_filters('frm_after_duplicate_form_values', $new_opts, $form_id);

        if ( $new_opts != $values['options'] ) {
            global $wpdb;
			$wpdb->update( $wpdb->prefix . 'frm_forms', array( 'options' => maybe_serialize( $new_opts ) ), array( 'id' => $form_id ) );
        }
    }

    /**
     * @return int|boolean
     */
    public static function update( $id, $values, $create_link = false ) {
        global $wpdb;

        if ( ! isset( $values['status'] ) && ( $create_link || isset( $values['options'] ) || isset( $values['item_meta'] ) || isset( $values['field_options'] ) ) ) {
            $values['status'] = 'published';
        }

        if ( isset($values['form_key']) ) {
			$values['form_key'] = FrmAppHelper::get_unique_key( $values['form_key'], $wpdb->prefix . 'frm_forms', 'form_key', $id );
        }

		$form_fields = array( 'form_key', 'name', 'description', 'status', 'parent_form_id' );

        $new_values = self::set_update_options( array(), $values);

        foreach ( $values as $value_key => $value ) {
			if ( $value_key && in_array( $value_key, $form_fields ) ) {
				$new_values[ $value_key ] = $value;
            }
        }

        if ( isset( $values['new_status'] ) && ! empty( $values['new_status'] ) ) {
            $new_values['status'] = $values['new_status'];
        }

        if ( ! empty( $new_values ) ) {
			$query_results = $wpdb->update( $wpdb->prefix . 'frm_forms', $new_values, array( 'id' => $id ) );
            if ( $query_results ) {
				self::clear_form_cache();
            }
        } else {
            $query_results = true;
        }
        unset($new_values);

        $values = self::update_fields($id, $values);

		do_action( 'frm_update_form', $id, $values );
		do_action( 'frm_update_form_' . $id, $values );

        return $query_results;
    }

    /**
     * @return array
     */
	public static function set_update_options( $new_values, $values ) {
        if ( ! isset($values['options']) ) {
            return $new_values;
        }

		$options = isset( $values['options'] ) ? (array) $values['options'] : array();
		FrmFormsHelper::fill_form_options( $options, $values );

        $options['custom_style'] = isset($values['options']['custom_style']) ? $values['options']['custom_style'] : 0;
        $options['before_html'] = isset($values['options']['before_html']) ? $values['options']['before_html'] : FrmFormsHelper::get_default_html('before');
        $options['after_html'] = isset($values['options']['after_html']) ? $values['options']['after_html'] : FrmFormsHelper::get_default_html('after');
        $options['submit_html'] = (isset($values['options']['submit_html']) && $values['options']['submit_html'] != '') ? $values['options']['submit_html'] : FrmFormsHelper::get_default_html('submit');

        $options = apply_filters('frm_form_options_before_update', $options, $values);
        $new_values['options'] = serialize($options);

        return $new_values;
    }


    /**
     * @return array
     */
	public static function update_fields( $id, $values ) {

        if ( ! isset($values['item_meta']) && ! isset($values['field_options']) ) {
            return $values;
        }

        $all_fields = FrmField::get_all_for_form($id);
        if ( empty($all_fields) ) {
            return $values;
        }

        if ( ! isset($values['item_meta']) ) {
            $values['item_meta'] = array();
        }

        $field_array = array();
        $existing_keys = array_keys($values['item_meta']);
        foreach ( $all_fields as $fid ) {
            if ( ! in_array($fid->id, $existing_keys) && ( isset($values['frm_fields_submitted']) && in_array($fid->id, $values['frm_fields_submitted']) ) || isset($values['options']) ) {
				$values['item_meta'][ $fid->id ] = '';
            }
			$field_array[ $fid->id ] = $fid;
        }
        unset($all_fields);

        foreach ( $values['item_meta'] as $field_id => $default_value ) {
			if ( isset( $field_array[ $field_id ] ) ) {
				$field = $field_array[ $field_id ];
            } else {
                $field = FrmField::getOne($field_id);
            }

            if ( ! $field ) {
                continue;
            }

			$is_settings_page = ( isset( $values['options'] ) || isset( $values['field_options'][ 'custom_html_' . $field_id ] ) );
			if ( $is_settings_page ) {
                //updating the settings page
				if ( isset( $values['field_options'][ 'custom_html_' . $field_id ] ) ) {
					$field->field_options['custom_html'] = isset( $values['field_options'][ 'custom_html_' . $field_id ] ) ? $values['field_options'][ 'custom_html_' . $field_id ] : ( isset( $field->field_options['custom_html'] ) ? $field->field_options['custom_html'] : FrmFieldsHelper::get_default_html( $field->type ) );
                    $field->field_options = apply_filters('frm_update_form_field_options', $field->field_options, $field, $values);
					FrmField::update( $field_id, array( 'field_options' => $field->field_options ) );
                } else if ( $field->type == 'hidden' || $field->type == 'user_id' ) {
                    $prev_opts = $field->field_options;
                    $field->field_options = apply_filters('frm_update_form_field_options', $field->field_options, $field, $values);
                    if ( $prev_opts != $field->field_options ) {
						FrmField::update( $field_id, array( 'field_options' => $field->field_options ) );
                    }
                    unset($prev_opts);
                }
            }

			if ( $is_settings_page && ! defined( 'WP_IMPORTING' ) ) {
                continue;
            }

            //updating the form
			$update_options = FrmFieldsHelper::get_default_field_opts( $field->type, $field, true );
			unset( $update_options['custom_html'] ); // don't check for POST html
			$update_options = apply_filters( 'frm_field_options_to_update', $update_options );

			foreach ( $update_options as $opt => $default ) {
				$field->field_options[ $opt ] = isset( $values['field_options'][ $opt . '_' . $field_id ] ) ? trim( sanitize_text_field( $values['field_options'][ $opt . '_' . $field_id ] ) ) : $default;
            }

            $field->field_options = apply_filters('frm_update_field_options', $field->field_options, $field, $values);
			$default_value = maybe_serialize( $values['item_meta'][ $field_id ] );

			$new_field = array(
				'field_options' => $field->field_options,
				'default_value' => $default_value,
			);

			self::prepare_field_update_values( $field, $values, $new_field );

			FrmField::update( $field_id, $new_field );

            FrmField::delete_form_transient($field->form_id);
        }
		self::clear_form_cache();

        return $values;
    }

	private static function prepare_field_update_values( $field, $values, &$new_field ) {
		$field_cols = array(
			'field_key' => '', 'required' => false, 'type' => '',
			'description' => '', 'options' => '',
		);
		foreach ( $field_cols as $col => $default ) {
			$default = ( $default === '' ) ? $field->{$col} : $default;
			$new_field[ $col ] = isset( $values['field_options'][ $col . '_' . $field->id ] ) ? $values['field_options'][ $col . '_' . $field->id ] : $default;
		}
	}

    /**
     * @param string $status
     * @return int|boolean
     */
	public static function set_status( $id, $status ) {
        if ( 'trash' == $status ) {
            return self::trash($id);
        }

		$statuses  = array( 'published', 'draft', 'trash' );
        if ( ! in_array( $status, $statuses ) ) {
            return false;
        }

        global $wpdb;

        if ( is_array($id) ) {
			$where = array( 'id' => $id, 'parent_form_id' => $id, 'or' => 1 );
			FrmDb::get_where_clause_and_values( $where );
			array_unshift( $where['values'], $status );

			$query_results = $wpdb->query( $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'frm_forms SET status = %s ' . $where['where'], $where['values'] ) );
        } else {
			$query_results = $wpdb->update( $wpdb->prefix . 'frm_forms', array( 'status' => $status ), array( 'id' => $id ) );
			$wpdb->update( $wpdb->prefix . 'frm_forms', array( 'status' => $status ), array( 'parent_form_id' => $id ) );
        }

        if ( $query_results ) {
			self::clear_form_cache();
        }

        return $query_results;
    }

    /**
     * @return int|boolean
     */
	public static function trash( $id ) {
        if ( ! EMPTY_TRASH_DAYS ) {
            return self::destroy( $id );
        }

        $form = self::getOne($id);
        if ( ! $form ) {
            return false;
        }

        $options = $form->options;
        $options['trash_time'] = time();

        global $wpdb;
        $query_results = $wpdb->update(
			$wpdb->prefix . 'frm_forms',
			array( 'status' => 'trash', 'options' => serialize( $options ) ),
			array( 'id' => $id )
        );

        $wpdb->update(
			$wpdb->prefix . 'frm_forms',
			array( 'status' => 'trash', 'options' => serialize( $options ) ),
			array( 'parent_form_id' => $id )
        );

        if ( $query_results ) {
			self::clear_form_cache();
        }

        return $query_results;
    }

    /**
     * @return int|boolean
     */
	public static function destroy( $id ) {
        global $wpdb;

        $form = self::getOne($id);
        if ( ! $form ) {
            return false;
        }
		$id = $form->id;

        // Disconnect the entries from this form
		$entries = FrmDb::get_col( $wpdb->prefix . 'frm_items', array( 'form_id' => $id ) );
        foreach ( $entries as $entry_id ) {
            FrmEntry::destroy($entry_id);
            unset($entry_id);
        }

        // Disconnect the fields from this form
		$wpdb->query( $wpdb->prepare( 'DELETE fi FROM ' . $wpdb->prefix . 'frm_fields AS fi LEFT JOIN ' . $wpdb->prefix . 'frm_forms fr ON (fi.form_id = fr.id) WHERE fi.form_id=%d OR parent_form_id=%d', $id, $id ) );

		$query_results = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_forms WHERE id=%d OR parent_form_id=%d', $id, $id ) );
        if ( $query_results ) {
            // Delete all form actions linked to this form
            $action_control = FrmFormActionsController::get_form_actions( 'email' );
            $action_control->destroy($id, 'all');

			// Clear form caching
			self::clear_form_cache();

			do_action( 'frm_destroy_form', $id );
			do_action( 'frm_destroy_form_' . $id );
        }

        return $query_results;
    }

	/**
	 * Delete trashed forms based on how long they have been trashed
	 * @return int The number of forms deleted
	 */
	public static function scheduled_delete( $delete_timestamp = '' ) {
		global $wpdb;

		$trash_forms = FrmDb::get_results( $wpdb->prefix . 'frm_forms', array( 'status' => 'trash' ), 'id, options' );

		if ( ! $trash_forms ) {
			return;
		}

		if ( empty( $delete_timestamp ) ) {
			$delete_timestamp = time() - ( DAY_IN_SECONDS * EMPTY_TRASH_DAYS );
		}

		$count = 0;
		foreach ( $trash_forms as $form ) {
			$form->options = maybe_unserialize( $form->options );
			if ( ! isset( $form->options['trash_time'] ) || $form->options['trash_time'] < $delete_timestamp ) {
				self::destroy( $form->id );
				$count++;
			}

			unset( $form );
		}
		return $count;
	}

    /**
     * @return string form name
     */
    public static function &getName( $id ) {
        global $wpdb;

        $form = FrmAppHelper::check_cache($id, 'frm_form');
        if ( $form ) {
            $r = stripslashes($form->name);
            return $r;
        }

        $query_key = is_numeric( $id ) ? 'id' : 'form_key';
        $r = FrmDb::get_var( 'frm_forms', array( $query_key => $id ), 'name' );
        $r = stripslashes($r);

        return $r;
    }

    /**
     * @param string $key
     * @return int form id
     */
	public static function &getIdByKey( $key ) {
        $id = FrmDb::get_var( 'frm_forms', array( 'form_key' => sanitize_title( $key ) ) );
        return $id;
    }

    /**
     * @param int $id
     * @return string form key
     */
	public static function &getKeyById( $id ) {
        $id = (int) $id;
        $cache = FrmAppHelper::check_cache($id, 'frm_form');
        if ( $cache ) {
            return $cache->form_key;
        }

        $key = FrmDb::get_var( 'frm_forms', array( 'id' => $id ), 'form_key' );

        return $key;
    }

	/**
	 * If $form is numeric, get the form object
	 * @param object|int $form
	 * @since 2.0.9
	 */
	public static function maybe_get_form( &$form ) {
		if ( ! is_object( $form ) && ! is_array( $form ) && ! empty( $form ) ) {
			$form = self::getOne( $form );
		}
	}

    /**
     * @return object form
     */
    public static function getOne( $id, $blog_id = false ) {
        global $wpdb;

        if ( $blog_id && is_multisite() ) {
            global $wpmuBaseTablePrefix;
			$prefix = $wpmuBaseTablePrefix ? $wpmuBaseTablePrefix . $blog_id . '_' : $wpdb->get_blog_prefix( $blog_id );

			$table_name = $prefix . 'frm_forms';
        } else {
			$table_name = $wpdb->prefix . 'frm_forms';
            $cache = wp_cache_get($id, 'frm_form');
            if ( $cache ) {
                if ( isset($cache->options) ) {
                    $cache->options = maybe_unserialize($cache->options);
                }

                return stripslashes_deep($cache);
            }
        }

        if ( is_numeric($id) ) {
            $where = array( 'id' => $id );
        } else {
            $where = array( 'form_key' => $id );
        }

        $results = FrmDb::get_row( $table_name, $where );

        if ( isset($results->options) ) {
			FrmAppHelper::set_cache( $results->id, $results, 'frm_form' );
            $results->options = maybe_unserialize($results->options);
        }
        return stripslashes_deep($results);
    }

    /**
     * @return object|array of objects
     */
	public static function getAll( $where = array(), $order_by = '', $limit = '' ) {
		if ( is_array( $where ) && ! empty( $where ) ) {
			$results = FrmDb::get_results( 'frm_forms', $where, '*', array( 'order_by' => $order_by, 'limit' => $limit ) );
		} else {
			global $wpdb;

			// the query has already been prepared if this is not an array
			$query = 'SELECT * FROM ' . $wpdb->prefix . 'frm_forms' . FrmAppHelper::prepend_and_or_where( ' WHERE ', $where ) . FrmAppHelper::esc_order( $order_by ) . FrmAppHelper::esc_limit( $limit );
			$results = $wpdb->get_results( $query );
		}

		if ( $results ) {
			foreach ( $results as $result ) {
				FrmAppHelper::set_cache( $result->id, $result, 'frm_form' );
				$result->options = maybe_unserialize( $result->options );
			}
		}

		if ( $limit == ' LIMIT 1' || $limit == 1 ) {
			// return the first form object if we are only getting one form
			$results = reset( $results );
		}

        return stripslashes_deep($results);
    }

	/**
	 * Get all published forms
	 * @since 2.0
	 * @return array of forms
	 */
	public static function get_published_forms( $query = array(), $limit = 999, $inc_children = 'exclude' ) {
		$query['is_template'] = 0;
		$query['status'] = array( null, '', 'published' );
		if ( $inc_children == 'exclude' ) {
			$query['parent_form_id'] = array( null, 0 );
		}

		$forms = self::getAll( $query, 'name', $limit );
		return $forms;
	}

    /**
     * @return int count of forms
     */
    public static function &get_count() {
    	global $wpdb;

    	$cache_key = 'frm_form_counts';

    	$counts = wp_cache_get( $cache_key, 'frm_form' );
    	if ( false !== $counts ) {
    	    return $counts;
    	}

        $results = (array) FrmDb::get_results( 'frm_forms', array( 'or' => 1, 'parent_form_id' => null, 'parent_form_id <' => 0 ), 'status, is_template' );

		$statuses = array( 'published', 'draft', 'template', 'trash' );
    	$counts = array_fill_keys( $statuses, 0 );

    	foreach ( $results as $row ) {
            if ( 'trash' != $row->status ) {
    	        if ( $row->is_template ) {
					$counts['template']++;
    	        } else {
					$counts['published']++;
    	        }
    	    } else {
				$counts['trash']++;
        	}

    	    if ( 'draft' == $row->status ) {
				$counts['draft']++;
    	    }

    		unset($row);
    	}

    	$counts = (object) $counts;
		FrmAppHelper::set_cache( $cache_key, $counts, 'frm_form' );

    	return $counts;
    }

	/**
	 * Clear form caching
	 * Called when a form is created, updated, duplicated, or deleted
	 * or when the form status is changed
	 *
	 * @since 2.0.4
	 */
	public static function clear_form_cache() {
		FrmAppHelper::cache_delete_group( 'frm_form' );
	}

    /**
     * @return array of errors
     */
	public static function validate( $values ) {
        $errors = array();

        return apply_filters('frm_validate_form', $errors, $values);
    }

	public static function get_params( $form = null ) {
		global $frm_vars;

		if ( ! $form ) {
			$form = self::getAll( array(), 'name', 1 );
		} else {
			self::maybe_get_form( $form );
		}

		if ( isset( $frm_vars['form_params'] ) && is_array( $frm_vars['form_params'] ) && isset( $frm_vars['form_params'][ $form->id ] ) ) {
			return $frm_vars['form_params'][ $form->id ];
		}

		$action_var = isset($_REQUEST['frm_action']) ? 'frm_action' : 'action';
		$action = apply_filters( 'frm_show_new_entry_page', FrmAppHelper::get_param( $action_var, 'new', 'get', 'sanitize_title' ), $form );

		$default_values = array(
			'id' => '', 'form_name' => '', 'paged' => 1, 'form' => $form->id, 'form_id' => $form->id,
			'field_id' => '', 'search' => '', 'sort' => '', 'sdir' => '', 'action' => $action,
		);

		$values = array();
		$values['posted_form_id'] = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		if ( ! $values['posted_form_id'] ) {
			$values['posted_form_id'] = FrmAppHelper::get_param( 'form', '', 'get', 'absint' );
		}

		if ( $form->id == $values['posted_form_id'] ) {
			//if there are two forms on the same page, make sure not to submit both
			foreach ( $default_values as $var => $default ) {
				if ( $var == 'action' ) {
					$values[ $var ] = FrmAppHelper::get_param( $action_var, $default, 'get', 'sanitize_title' );
				} else {
					$values[ $var ] = FrmAppHelper::get_param( $var, $default );
				}
				unset( $var, $default );
			}
		} else {
			foreach ( $default_values as $var => $default ) {
				$values[ $var ] = $default;
				unset( $var, $default );
			}
		}

		if ( in_array( $values['action'], array( 'create', 'update' ) ) && ( ! $_POST || ( ! isset( $_POST['action'] ) && ! isset( $_POST['frm_action'] ) ) ) ) {
			$values['action'] = 'new';
		}

		return $values;
	}

	public static function list_page_params() {
		$values = array();
		foreach ( array( 'template' => 0, 'id' => '', 'paged' => 1, 'form' => '', 'search' => '', 'sort' => '', 'sdir' => '' ) as $var => $default ) {
			$values[ $var ] = FrmAppHelper::get_param( $var, $default );
		}

		return $values;
	}

	public static function get_admin_params( $form = null ) {
		$form_id = $form;
		if ( $form === null ) {
			$form_id = FrmForm::get_current_form_id();
		} else if ( $form && is_object( $form ) ) {
			$form_id = $form->id;
		}

		$values = array();
		foreach ( array(
			'id' => '', 'form_name' => '', 'paged' => 1, 'form' => $form_id,
			'field_id' => '', 'search' => '', 'sort' => '', 'sdir' => '', 'fid' => '',
			'keep_post' => '',
		) as $var => $default ) {
			$values[ $var ] = FrmAppHelper::get_param( $var, $default );
		}

		return $values;
	}

	public static function get_current_form_id( $default_form = 'none' ) {
		if ( 'first' == $default_form ) {
			$form = self::get_current_form();
		} else {
			$form = self::maybe_get_current_form();
		}
		$form_id = $form ? $form->id : 0;

		return $form_id;
	}

	public static function maybe_get_current_form( $form_id = 0 ) {
		global $frm_vars;

		if ( isset( $frm_vars['current_form'] ) && $frm_vars['current_form'] && ( ! $form_id || $form_id == $frm_vars['current_form']->id ) ) {
			return $frm_vars['current_form'];
		}

		$form_id = FrmAppHelper::get_param( 'form', $form_id, 'get', 'absint' );
		if ( $form_id ) {
			$form_id = self::set_current_form( $form_id );
		}
		return $form_id;
	}

	public static function get_current_form( $form_id = 0 ) {
		$form = self::maybe_get_current_form( $form_id );
		if ( is_numeric( $form ) ) {
			 $form = self::set_current_form( $form );
		}
		return $form;
	}

	public static function set_current_form( $form_id ) {
		global $frm_vars;

		$query = array();
		if ( $form_id ) {
			$query['id'] = $form_id;
		}

		$frm_vars['current_form'] = self::get_published_forms( $query, 1 );

		return $frm_vars['current_form'];
	}

	public static function is_form_loaded( $form, $this_load, $global_load ) {
		global $frm_vars;
		$small_form = new stdClass();
		foreach ( array( 'id', 'form_key', 'name' ) as $var ) {
			$small_form->{$var} = $form->{$var};
			unset($var);
		}

		$frm_vars['forms_loaded'][] = $small_form;

		if ( $this_load && empty( $global_load ) ) {
			$global_load = true;
			$frm_vars['load_css'] = true;
		}

		return ( ( ! isset($frm_vars['css_loaded']) || ! $frm_vars['css_loaded'] ) && $global_load );
	}

	public static function show_submit( $form ) {
		$show = ( ! $form->is_template && $form->status == 'published' && ! FrmAppHelper::is_admin() );
		$show = apply_filters( 'frm_show_submit_button', $show, $form );
		return $show;
	}

	/**
	 * @since 2.3
	 */
	public static function get_option( $atts ) {
		$form = $atts['form'];
		return isset( $form->options[ $atts['option'] ] ) ? $form->options[ $atts['option'] ] : $atts['default'];
	}
}
