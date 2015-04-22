<?php
if ( ! defined('ABSPATH') ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmField{
    static $use_cache = true;
	static $transient_size = 200;

    public static function create( $values, $return = true ) {
        global $wpdb, $frm_duplicate_ids;

        $new_values = array();
        $key = isset($values['field_key']) ? $values['field_key'] : $values['name'];
        $new_values['field_key'] = FrmAppHelper::get_unique_key($key, $wpdb->prefix .'frm_fields', 'field_key');

        foreach ( array( 'name', 'description', 'type', 'default_value') as $col ) {
            $new_values[$col] = $values[$col];
        }

        $new_values['options'] = $values['options'];

        $new_values['field_order'] = isset($values['field_order']) ? (int) $values['field_order'] : null;
        $new_values['required'] = isset($values['required']) ? (int) $values['required'] : 0;
        $new_values['form_id'] = isset($values['form_id']) ? (int) $values['form_id'] : null;
        $new_values['field_options'] = $values['field_options'];
        $new_values['created_at'] = current_time('mysql', 1);

		if ( isset( $values['id'] ) ) {
            $frm_duplicate_ids[$values['field_key']] = $new_values['field_key'];
            $new_values = apply_filters('frm_duplicated_field', $new_values);
        }

		foreach ( $new_values as $k => $v ) {
            if ( is_array( $v ) ) {
				$new_values[ $k ] = serialize( $v );
			}
            unset( $k, $v );
        }

        //if(isset($values['id']) and is_numeric($values['id']))
        //    $new_values['id'] = $values['id'];

        $query_results = $wpdb->insert( $wpdb->prefix .'frm_fields', $new_values );
		if ( $query_results ) {
			self::delete_form_transient( $new_values['form_id'] );
			$new_id = $wpdb->insert_id;
		}

		if ( ! $return ) {
			return;
		}

		if ( $query_results ) {
			if ( isset( $values['id'] ) ) {
				$frm_duplicate_ids[ $values['id'] ] = $new_id;
			}
			return $new_id;
		} else {
			return false;
		}
    }

    public static function duplicate( $old_form_id, $form_id, $copy_keys = false, $blog_id = false ) {
        global $frm_duplicate_ids;
        $fields = self::getAll( array( 'fi.form_id' => $old_form_id), 'field_order', '', $blog_id);
        foreach ( (array) $fields as $field ) {
            $new_key = ($copy_keys) ? $field->field_key : '';
            if ( $copy_keys && substr($field->field_key, -1) == 2 ) {
                $new_key = rtrim($new_key, 2);
            }

            $values = array();
            FrmFieldsHelper::fill_field( $values, $field, $form_id, $new_key );

			// If this is a repeating section, create new form
			if ( $field->type == 'divider' && isset( $field->field_options['repeat'] ) && $field->field_options['repeat'] ) {
				// create the repeatable form
				$repeat_form_values = FrmFormsHelper::setup_new_vars( array( 'parent_form_id' => $form_id ) );
				$new_repeat_form_id = FrmForm::create( $repeat_form_values );

				// Save old form_select
				$old_repeat_form_id = $field->field_options['form_select'];

				// Update form_select for repeating field
				$values['field_options']['form_select'] = $new_repeat_form_id;
			}

			// If this is a field inside of a repeating section, associate it with the correct form
			if ( $field->form_id != $old_form_id && isset( $old_repeat_form_id ) && isset( $new_repeat_form_id ) && $field->form_id == $old_repeat_form_id ) {
				$values['form_id'] = $new_repeat_form_id;
			}

            $values = apply_filters('frm_duplicated_field', $values);
            $new_id = self::create($values);
            $frm_duplicate_ids[ $field->id ] = $new_id;
            $frm_duplicate_ids[ $field->field_key ] = $new_id;
            unset($field);
        }
    }

    public static function update( $id, $values ){
        global $wpdb;

		$id = absint( $id );

		if ( isset( $values['field_key'] ) ) {
            $values['field_key'] = FrmAppHelper::get_unique_key($values['field_key'], $wpdb->prefix .'frm_fields', 'field_key', $id);
		}

        if ( isset($values['required']) ) {
            $values['required'] = (int) $values['required'];
        }

		// serialize array values
		foreach ( array( 'default_value', 'field_options', 'options') as $opt ) {
			if ( isset( $values[ $opt ] ) && is_array( $values[ $opt ] ) ) {
				$values[ $opt ] = serialize( $values[ $opt ] );
			}
		}

        $query_results = $wpdb->update( $wpdb->prefix .'frm_fields', $values, array( 'id' => $id ) );

        $form_id = 0;
		if ( isset( $values['form_id'] ) ) {
            $form_id = absint( $values['form_id'] );
		} else {
            $field = self::getOne($id);
            if ( $field ) {
                $form_id = $field->form_id;
            }
            unset($field);
        }
        unset($values);

		if ( $query_results ) {
            wp_cache_delete( $id, 'frm_field' );
            if ( $form_id ) {
                self::delete_form_transient($form_id);
            }
        }

        return $query_results;
    }

    public static function destroy( $id ) {
		global $wpdb;

		do_action( 'frm_before_destroy_field', $id );

		wp_cache_delete( $id, 'frm_field' );
		$field = self::getOne( $id );
		if ( ! $field ) {
			return false;
		}

		self::delete_form_transient( $field->form_id );

		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_item_metas WHERE field_id=%d', $id ) );
		return $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_fields WHERE id=%d', $id ) );
    }

    public static function delete_form_transient($form_id) {
		$form_id = absint( $form_id );
		delete_transient( 'frm_form_fields_'. $form_id .'exclude' );
		delete_transient( 'frm_form_fields_'. $form_id .'include' );

		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '. $wpdb->options .' WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s', '_transient_timeout_frm_form_fields_' . $form_id .'ex%', '_transient_frm_form_fields_' . $form_id .'ex%', '_transient_timeout_frm_form_fields_' . $form_id .'in%', '_transient_frm_form_fields_' . $form_id .'in%' ) );

		$cache_key = serialize( array( 'fi.form_id' => $form_id ) ) . 'field_orderlb';
        wp_cache_delete($cache_key, 'frm_field');

		// this cache key is autogenerated in FrmDb::get_var
		wp_cache_delete( '(__fi.form_id=%d_OR_fr.parent_form_id=%d_)__' . $form_id . '_' . $form_id . '_ORDER_BY_field_orderfi.*__fr.name_as_form_name_results', 'frm_field' );

        $form = FrmForm::getOne($form_id);
        if ( $form && $form->parent_form_id ) {
            self::delete_form_transient( $form->parent_form_id );
        }
    }

    public static function getOne( $id ){
		if ( empty( $id ) ) {
			return;
		}

        global $wpdb;

        $where = is_numeric($id) ? 'id=%d' : 'field_key=%s';
        $query = $wpdb->prepare('SELECT * FROM '. $wpdb->prefix .'frm_fields WHERE '. $where, $id);

        $results = FrmAppHelper::check_cache( $id, 'frm_field', $query, 'get_row', 0 );

        if ( empty($results) ) {
            return $results;
        }

        if ( is_numeric($id) ) {
            wp_cache_set( $results->field_key, $results, 'frm_field' );
        } else if ( $results ) {
            wp_cache_set( $results->id, $results, 'frm_field' );
        }

		self::prepare_options( $results );

        return stripslashes_deep($results);
    }

    /**
     * Get the field type by key or id
     * @param int|string The field id or key
     */
    public static function &get_type( $id ) {
        $field = FrmAppHelper::check_cache( $id, 'frm_field' );
        if ( $field ) {
            $type = $field->type;
        } else {
            $type = FrmDb::get_var( 'frm_fields', array( 'or' => 1, 'id' => $id, 'field_key' => $id ), 'type' );
        }

        return $type;
    }

    public static function get_all_types_in_form($form_id, $type, $limit = '', $inc_sub = 'exclude') {
        if ( ! $form_id ) {
            return array();
        }

		$results = self::get_fields_from_transients( $form_id, $inc_sub );
		if ( ! empty( $results ) ) {
            $fields = array();
            $count = 0;
            foreach ( $results as $result ) {
                if ( $type != $result->type ) {
                    continue;
                }

                $fields[$result->id] = $result;
                $count++;
                if ( $limit == 1 ) {
                    $fields = $result;
                    break;
                }

                if ( ! empty($limit) && $count >= $limit ) {
                    break;
                }

                unset($result);
            }
            return stripslashes_deep($fields);
        }

        self::$use_cache = false;
        $results = self::getAll( array( 'fi.form_id' => (int) $form_id, 'fi.type' => $type), 'field_order', $limit);
        self::$use_cache = true;
        self::include_sub_fields($results, $inc_sub, $type);

        return $results;
    }

	public static function get_all_for_form( $form_id, $limit = '', $inc_sub = 'exclude' ) {
        if ( ! (int) $form_id ) {
            return array();
        }

		$results = self::get_fields_from_transients( $form_id, $inc_sub );
		if ( ! empty( $results ) ) {
            if ( empty($limit) ) {
                return stripslashes_deep($results);
            }

            $fields = array();
            $count = 0;
            foreach ( $results as $result ) {
                $fields[$result->id] = $result;
                if ( ! empty($limit) && $count >= $limit ) {
                    break;
                }
            }

            return stripslashes_deep($fields);
        }

        self::$use_cache = false;

		// get the fields, but make sure to not get the subfields if set to exclude
		$results = self::getAll( array( 'fi.form_id' => absint( $form_id ) ), 'field_order', $limit );
        self::$use_cache = true;

		self::include_sub_fields( $results, $inc_sub, 'all' );

        if ( empty($limit) ) {
			self::set_field_transient( $results, $form_id, $inc_sub );
        }

        return $results;
    }

    public static function include_sub_fields(&$results, $inc_sub, $type = 'all') {
        if ( 'include' != $inc_sub ) {
            return;
        }

        $form_fields = $results;
        foreach ( $form_fields as $k => $field ) {
            if ( 'form' != $field->type || ! isset($field->field_options['form_select']) ) {
                continue;
            }

            if ( $type == 'all' ) {
                $sub_fields = self::get_all_for_form( $field->field_options['form_select'] );
            } else {
                $sub_fields = self::get_all_types_in_form($field->form_id, $type);
            }

            if ( ! empty($sub_fields) ) {
                array_splice($results, $k, 1, $sub_fields);
            }
            unset($field, $sub_fields);
        }
    }

    public static function getAll($where = array(), $order_by = '', $limit = '', $blog_id = false) {
        $cache_key = maybe_serialize($where) . $order_by .'l'. $limit .'b'. $blog_id;
        if ( self::$use_cache ) {
            // make sure old cache doesn't get saved as a transient
            $results = wp_cache_get($cache_key, 'frm_field');
            if ( false !== $results ) {
                return stripslashes_deep($results);
            }
        }

        global $wpdb;

        if ( $blog_id && is_multisite() ) {
            global $wpmuBaseTablePrefix;
            if ( $wpmuBaseTablePrefix ) {
                $prefix = $wpmuBaseTablePrefix . $blog_id .'_';
            } else {
                $prefix = $wpdb->get_blog_prefix( $blog_id );
            }

            $table_name = $prefix .'frm_fields';
            $form_table_name = $prefix .'frm_forms';
        }else{
            $table_name = $wpdb->prefix .'frm_fields';
            $form_table_name = $wpdb->prefix .'frm_forms';
        }

		if ( ! empty( $order_by ) && strpos( $order_by, 'ORDER BY' ) === false ) {
            $order_by = ' ORDER BY '. $order_by;
		}

        $limit = FrmAppHelper::esc_limit($limit);

        $query = "SELECT fi.*, fr.name as form_name  FROM {$table_name} fi LEFT OUTER JOIN {$form_table_name} fr ON fi.form_id=fr.id";
        $query_type = ( $limit == ' LIMIT 1' || $limit == 1 ) ? 'row' : 'results';

        if ( is_array($where) ) {
            if ( isset( $where['fi.form_id'] ) && count( $where ) == 1 ) {
                // add sub fields to query
                $form_id = $where['fi.form_id'];
                $where[] = array( 'or' => 1, 'fi.form_id' => $form_id, 'fr.parent_form_id' => $form_id );
                unset( $where['fi.form_id'] );
            }

            $results = FrmDb::get_var( $table_name . ' fi LEFT OUTER JOIN ' . $form_table_name . ' fr ON fi.form_id=fr.id', $where, 'fi.*, fr.name as form_name', array( 'order_by' => $order_by, 'limit' => $limit ), '', $query_type );
        }else{
			// if the query is not an array, then it has already been prepared
            $query .= FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;

			$function_name = ( $query_type == 'row' ) ? 'get_row' : 'get_results';
			$results = $wpdb->$function_name( $query );
        }
        unset( $where );

        if ( ! $results ) {
            stripslashes_deep($results);
        }

        if ( is_array($results) ) {
            foreach ( $results as $r_key => $result ) {
                wp_cache_set($result->id, $result, 'frm_field');
                wp_cache_set($result->field_key, $result, 'frm_field');

                $results[$r_key]->field_options = maybe_unserialize($result->field_options);
                if ( isset( $results[ $r_key ]->field_options['format'] ) && ! empty( $results[ $r_key ]->field_options['format'] ) ) {
                    $results[ $r_key ]->field_options['format'] = addslashes( $results[ $r_key ]->field_options['format'] );
                }

                $results[ $r_key ]->options = maybe_unserialize( $result->options );
                $results[ $r_key ]->default_value = maybe_unserialize( $result->default_value );
                $form_id = $result->form_id;

                unset($r_key, $result);
            }

            unset($form_id);
		} else if ( $results ) {
            wp_cache_set($results->id, $results, 'frm_field');
            wp_cache_set($results->field_key, $results, 'frm_field');

			self::prepare_options( $results );
        }

        wp_cache_set($cache_key, $results, 'frm_field', 300);

        return stripslashes_deep($results);
    }

	/**
	 * Unserialize all the serialized field data
	 * @since 2.0
	 */
	private static function prepare_options( &$results ) {
		$results->field_options = maybe_unserialize( $results->field_options );
		if ( isset( $results->field_options['format'] ) && ! empty( $results->field_options['format'] ) ) {
			$results->field_options['format'] = addslashes( $results->field_options['format'] );
		}

		$results->options = maybe_unserialize($results->options);
		$results->default_value = maybe_unserialize($results->default_value);
	}

	/**
	 * If a form has too many fields, thay won't all save into a single transient.
	 * We'll break them into groups of 200
	 * @since 2.0.1
	 */
	private static function get_fields_from_transients( $form_id, $inc_sub = 'exclude' ) {
		$fields = array();
		self::get_next_transient( $fields, 'frm_form_fields_' . $form_id . $inc_sub );
		return $fields;
	}

	/**
	 * Called by get_fields_from_transients
	 * @since 2.0.1
	 */
	private static function get_next_transient( &$fields, $base_name, $next = 0 ) {
		$name = $next ? $base_name . $next : $base_name;
		$next_fields = get_transient( $name );

		if ( $next_fields ) {
			$fields = array_merge( $fields, $next_fields );

			if ( count( $next_fields ) >= self::$transient_size ) {
				// if this transient is full, check for another
				$next++;
				self::get_next_transient( $fields, $base_name, $next );
			}
		}
	}

	/**
	 * Save the transients in chunks for large forms
	 * @since 2.0.1
	 */
	private static function set_field_transient( &$fields, $form_id, $inc_sub, $next = 0 ) {
		$base_name = 'frm_form_fields_' . $form_id . $inc_sub;
		$field_chunks = array_chunk( $fields, self::$transient_size );

		foreach ( $field_chunks as $field ) {
			$name = $next ? $base_name . $next : $base_name;
			$set = set_transient( $name, $field, 60 * 60 * 6 );
			if ( ! $set ) {
				// the transient didn't save
				if ( $name != $base_name ) {
					// if the first saved an others fail, this will show an incmoplete form
					self::delete_form_transient( $form_id );
				}
				return;
			}

			$next++;
		}
	}

    public static function getIds($where = '', $order_by = '', $limit = ''){
		_deprecated_function( __FUNCTION__, '2.0' );
        global $wpdb;
        if ( ! empty($order_by) && ! strpos($order_by, 'ORDER BY') !== false ) {
            $order_by = ' ORDER BY '. $order_by;
        }

        $query = 'SELECT fi.id  FROM '. $wpdb->prefix .'frm_fields fi ' .
                 'LEFT OUTER JOIN '. $wpdb->prefix .'frm_forms fr ON fi.form_id=fr.id' .
                 FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;

        $method = ( $limit == ' LIMIT 1' || $limit == 1 ) ? 'get_var' : 'get_col';
        $cache_key = 'getIds_'. maybe_serialize($where) . $order_by . $limit;
        $results = FrmAppHelper::check_cache($cache_key, 'frm_field', $query, $method);

        return $results;
    }

}
