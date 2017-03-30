<?php
if ( ! defined('ABSPATH') ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmField {

    static $use_cache = true;
	static $transient_size = 200;

	public static function field_selection() {
		$fields = apply_filters('frm_available_fields', array(
			'text'      => __( 'Single Line Text', 'formidable' ),
			'textarea'  => __( 'Paragraph Text', 'formidable' ),
			'checkbox'  => __( 'Checkboxes', 'formidable' ),
			'radio'     => __( 'Radio Buttons', 'formidable' ),
			'select'    => __( 'Dropdown', 'formidable' ),
			'email'     => __( 'Email Address', 'formidable' ),
			'url'       => __( 'Website/URL', 'formidable' ),
			'captcha'   => __( 'reCAPTCHA', 'formidable' ),
		));

		return $fields;
	}

	public static function pro_field_selection() {
		return apply_filters('frm_pro_available_fields', array(
			'end_divider' => array(
				'name'  => __( 'End Section', 'formidable' ),
				'switch_from' => 'divider',
			),
			'divider'   => __( 'Section', 'formidable' ),
			'break'     => __( 'Page Break', 'formidable' ),
			'file'      => __( 'File Upload', 'formidable' ),
			'rte'       => __( 'Rich Text', 'formidable' ),
			'number'    => __( 'Number', 'formidable' ),
			'phone'     => __( 'Phone Number', 'formidable' ),
			'date'      => __( 'Date', 'formidable' ),
			'time'      => __( 'Time', 'formidable' ),
			'image'     => __( 'Image URL', 'formidable' ),
			'scale'     => __( 'Scale', 'formidable' ),
			'data'      => __( 'Dynamic Field', 'formidable' ),
			'lookup'	=> __( 'Lookup', 'formidable' ),
			'form'      => __( 'Embed Form', 'formidable' ),
			'hidden'    => __( 'Hidden Field', 'formidable' ),
			'user_id'   => __( 'User ID (hidden)', 'formidable' ),
			'password'  => __( 'Password', 'formidable' ),
			'html'      => __( 'HTML', 'formidable' ),
			'tag'       => __( 'Tags', 'formidable' ),
			'credit_card' => __( 'Credit Card', 'formidable' ),
			'address'   => __( 'Address', 'formidable' ),
		));
	}

    public static function create( $values, $return = true ) {
        global $wpdb, $frm_duplicate_ids;

        $new_values = array();
        $key = isset($values['field_key']) ? $values['field_key'] : $values['name'];
		$new_values['field_key'] = FrmAppHelper::get_unique_key( $key, $wpdb->prefix . 'frm_fields', 'field_key' );

		foreach ( array( 'name', 'description', 'type', 'default_value' ) as $col ) {
			$new_values[ $col ] = $values[ $col ];
        }

        $new_values['options'] = $values['options'];

        $new_values['field_order'] = isset($values['field_order']) ? (int) $values['field_order'] : null;
        $new_values['required'] = isset($values['required']) ? (int) $values['required'] : 0;
        $new_values['form_id'] = isset($values['form_id']) ? (int) $values['form_id'] : null;
        $new_values['field_options'] = $values['field_options'];
        $new_values['created_at'] = current_time('mysql', 1);

		if ( isset( $values['id'] ) ) {
			$frm_duplicate_ids[ $values['field_key'] ] = $new_values['field_key'];
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

		$query_results = $wpdb->insert( $wpdb->prefix . 'frm_fields', $new_values );
		$new_id = 0;
		if ( $query_results ) {
			self::delete_form_transient( $new_values['form_id'] );
			$new_id = $wpdb->insert_id;
		}

		if ( ! $return ) {
			return false;
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

		$where = array( array( 'or' => 1, 'fi.form_id' => $old_form_id, 'fr.parent_form_id' => $old_form_id ) );
		$fields = self::getAll( $where, 'field_order', '', $blog_id );

        foreach ( (array) $fields as $field ) {
            $new_key = ($copy_keys) ? $field->field_key : '';
            if ( $copy_keys && substr($field->field_key, -1) == 2 ) {
                $new_key = rtrim($new_key, 2);
            }

            $values = array();
            FrmFieldsHelper::fill_field( $values, $field, $form_id, $new_key );

			// If this is a repeating section, create new form
			if ( self::is_repeating_field( $field ) ) {
				// create the repeatable form
				$new_repeat_form_id = apply_filters( 'frm_create_repeat_form', 0, array( 'parent_form_id' => $form_id, 'field_name' => $field->name ) );

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

	public static function update( $id, $values ) {
        global $wpdb;

		$id = absint( $id );

		if ( isset( $values['field_key'] ) ) {
			$values['field_key'] = FrmAppHelper::get_unique_key( $values['field_key'], $wpdb->prefix . 'frm_fields', 'field_key', $id );
		}

        if ( isset($values['required']) ) {
            $values['required'] = (int) $values['required'];
        }

		self::preserve_format_option_backslashes( $values );

		if ( isset( $values['type'] ) ) {
			$values = apply_filters( 'frm_clean_' . $values['type'] . '_field_options_before_update', $values );
		}

		// serialize array values
		foreach ( array( 'default_value', 'field_options', 'options' ) as $opt ) {
			if ( isset( $values[ $opt ] ) && is_array( $values[ $opt ] ) ) {
				$values[ $opt ] = serialize( $values[ $opt ] );
			}
		}

		$query_results = $wpdb->update( $wpdb->prefix . 'frm_fields', $values, array( 'id' => $id ) );

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
                self::delete_form_transient( $form_id );
            }
        }

        return $query_results;
    }

	/**
	* Keep backslashes in the phone format option
	*
	* @since 2.0.8
	* @param $values array - pass by reference
	*/
	private static function preserve_format_option_backslashes( &$values ) {
		if ( isset( $values['field_options']['format'] ) ) {
			$values['field_options']['format'] = FrmAppHelper::preserve_backslashes( $values['field_options']['format'] );
		}
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

	public static function delete_form_transient( $form_id ) {
		$form_id = absint( $form_id );
		delete_transient( 'frm_form_fields_' . $form_id . 'excludeinclude' );
		delete_transient( 'frm_form_fields_' . $form_id . 'includeinclude' );
		delete_transient( 'frm_form_fields_' . $form_id . 'includeexclude' );
		delete_transient( 'frm_form_fields_' . $form_id . 'excludeexclude' );

		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s', '_transient_timeout_frm_form_fields_' . $form_id . 'ex%', '_transient_frm_form_fields_' . $form_id . 'ex%', '_transient_timeout_frm_form_fields_' . $form_id . 'in%', '_transient_frm_form_fields_' . $form_id . 'in%' ) );

		FrmAppHelper::cache_delete_group( 'frm_field' );

        $form = FrmForm::getOne($form_id);
        if ( $form && $form->parent_form_id && $form->parent_form_id != $form_id ) {
            self::delete_form_transient( $form->parent_form_id );
        }
    }

	/**
	 * If $field is numeric, get the field object
	 */
	public static function maybe_get_field( &$field ) {
		if ( ! is_object( $field ) ) {
			$field = self::getOne( $field );
		}
	}

	public static function getOne( $id ) {
		if ( empty( $id ) ) {
			return null;
		}

        global $wpdb;

        $where = is_numeric($id) ? 'id=%d' : 'field_key=%s';
		$query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'frm_fields WHERE ' . $where, $id );

        $results = FrmAppHelper::check_cache( $id, 'frm_field', $query, 'get_row', 0 );

        if ( empty($results) ) {
            return $results;
        }

        if ( is_numeric($id) ) {
			FrmAppHelper::set_cache( $results->field_key, $results, 'frm_field' );
        } else if ( $results ) {
			FrmAppHelper::set_cache( $results->id, $results, 'frm_field' );
        }

		self::prepare_options( $results );

        return stripslashes_deep($results);
    }

    /**
     * Get the field type by key or id
     * @param int|string The field id or key
	 * @param mixed $col The name of the column in the fields database table
     */
    public static function get_type( $id, $col = 'type' ) {
        $field = FrmAppHelper::check_cache( $id, 'frm_field' );
        if ( $field ) {
            $type = $field->{$col};
        } else {
            $type = FrmDb::get_var( 'frm_fields', array( 'or' => 1, 'id' => $id, 'field_key' => $id ), $col );
        }

        return $type;
    }

	public static function get_all_types_in_form( $form_id, $type, $limit = '', $inc_sub = 'exclude' ) {
        if ( ! $form_id ) {
            return array();
        }

		$results = self::get_fields_from_transients( $form_id, array( 'inc_embed' => $inc_sub, 'inc_repeat' => $inc_sub ) );
		if ( ! empty( $results ) ) {
            $fields = array();
            $count = 0;
            foreach ( $results as $result ) {
                if ( $type != $result->type ) {
                    continue;
                }

				$fields[ $result->id ] = $result;
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

		$where = array( 'fi.form_id' => (int) $form_id, 'fi.type' => $type );
		self::maybe_include_repeating_fields( $inc_sub, $where );
		$results = self::getAll( $where, 'field_order', $limit );
        self::$use_cache = true;
        self::include_sub_fields($results, $inc_sub, $type);

        return $results;
    }

	public static function get_all_for_form( $form_id, $limit = '', $inc_embed = 'exclude', $inc_repeat = 'include' ) {
        if ( ! (int) $form_id ) {
            return array();
        }

		$results = self::get_fields_from_transients( $form_id, array( 'inc_embed' => $inc_embed, 'inc_repeat' => $inc_repeat ) );
		if ( ! empty( $results ) ) {
            if ( empty($limit) ) {
				return $results;
            }

            $fields = array();
            $count = 0;
            foreach ( $results as $result ) {
				$fields[ $result->id ] = $result;
                if ( ! empty($limit) && $count >= $limit ) {
                    break;
                }
            }

			return $fields;
        }

        self::$use_cache = false;

		$where = array( 'fi.form_id' => absint( $form_id ) );
		self::maybe_include_repeating_fields( $inc_repeat, $where );
		$results = self::getAll( $where, 'field_order', $limit );

        self::$use_cache = true;

		self::include_sub_fields( $results, $inc_embed, 'all' );

        if ( empty($limit) ) {
			self::set_field_transient( $results, $form_id, 0, array( 'inc_embed' => $inc_embed, 'inc_repeat' => $inc_repeat ) );
        }

		return $results;
    }

	/**
	* If repeating fields should be included, adjust $where accordingly
	*
	* @param string $inc_repeat
	* @param array $where - pass by reference
	*/
	private static function maybe_include_repeating_fields( $inc_repeat, &$where ) {
		if ( $inc_repeat == 'include' ) {
			$form_id = $where['fi.form_id'];
			$where[] = array( 'or' => 1, 'fi.form_id' => $form_id, 'fr.parent_form_id' => $form_id );
			unset( $where['fi.form_id'] );
		}
	}

	public static function include_sub_fields( &$results, $inc_embed, $type = 'all' ) {
		if ( 'include' != $inc_embed || empty( $results ) ) {
            return;
        }

        $form_fields = $results;
		$index_offset = 1;
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
				$index = $k + $index_offset;
				$index_offset += count( $sub_fields );
				array_splice($results, $index, 0, $sub_fields);
            }
            unset($field, $sub_fields);
        }
    }

	public static function getAll( $where = array(), $order_by = '', $limit = '', $blog_id = false ) {
		$cache_key = maybe_serialize( $where ) . $order_by . 'l' . $limit . 'b' . $blog_id;
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
				$prefix = $wpmuBaseTablePrefix . $blog_id . '_';
            } else {
                $prefix = $wpdb->get_blog_prefix( $blog_id );
            }

			$table_name = $prefix . 'frm_fields';
			$form_table_name = $prefix . 'frm_forms';
		} else {
			$table_name = $wpdb->prefix . 'frm_fields';
			$form_table_name = $wpdb->prefix . 'frm_forms';
        }

		if ( ! empty( $order_by ) && strpos( $order_by, 'ORDER BY' ) === false ) {
			$order_by = ' ORDER BY ' . $order_by;
		}

        $limit = FrmAppHelper::esc_limit($limit);

        $query = "SELECT fi.*, fr.name as form_name  FROM {$table_name} fi LEFT OUTER JOIN {$form_table_name} fr ON fi.form_id=fr.id";
        $query_type = ( $limit == ' LIMIT 1' || $limit == 1 ) ? 'row' : 'results';

        if ( is_array($where) ) {
            $results = FrmDb::get_var( $table_name . ' fi LEFT OUTER JOIN ' . $form_table_name . ' fr ON fi.form_id=fr.id', $where, 'fi.*, fr.name as form_name', array( 'order_by' => $order_by, 'limit' => $limit ), '', $query_type );
		} else {
			// if the query is not an array, then it has already been prepared
            $query .= FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;

			$function_name = ( $query_type == 'row' ) ? 'get_row' : 'get_results';
			$results = $wpdb->$function_name( $query );
        }
        unset( $where );

		self::format_field_results( $results );

		FrmAppHelper::set_cache( $cache_key, $results, 'frm_field' );

		return stripslashes_deep( $results );
	}

	/**
	 * @since 2.0.8
	 */
	private static function format_field_results( &$results ) {
		if ( is_array( $results ) ) {
			foreach ( $results as $r_key => $result ) {
				FrmAppHelper::set_cache( $result->id, $result, 'frm_field' );
				FrmAppHelper::set_cache( $result->field_key, $result, 'frm_field' );

				$results[ $r_key ]->field_options = maybe_unserialize( $result->field_options );
				$results[ $r_key ]->options = maybe_unserialize( $result->options );
				$results[ $r_key ]->default_value = maybe_unserialize( $result->default_value );

				unset( $r_key, $result );
			}
		} else if ( $results ) {
			FrmAppHelper::set_cache( $results->id, $results, 'frm_field' );
			FrmAppHelper::set_cache( $results->field_key, $results, 'frm_field' );

			self::prepare_options( $results );
		}
	}

	/**
	 * Unserialize all the serialized field data
	 * @since 2.0
	 */
	private static function prepare_options( &$results ) {
		$results->field_options = maybe_unserialize( $results->field_options );

		$results->options = maybe_unserialize($results->options);
		$results->default_value = maybe_unserialize($results->default_value);
	}

	/**
	 * If a form has too many fields, thay won't all save into a single transient.
	 * We'll break them into groups of 200
	 * @since 2.0.1
	 */
	private static function get_fields_from_transients( $form_id, $args ) {
		$fields = array();
		self::get_next_transient( $fields, 'frm_form_fields_' . $form_id . $args['inc_embed'] . $args['inc_repeat'] );
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
	private static function set_field_transient( &$fields, $form_id, $next = 0, $args = array() ) {
		$base_name = 'frm_form_fields_' . $form_id . $args['inc_embed'] . $args['inc_repeat'];
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

	public static function is_no_save_field( $type ) {
		return in_array( $type, self::no_save_fields() );
	}

	public static function no_save_fields() {
		return array( 'divider', 'end_divider', 'captcha', 'break', 'html', 'form' );
	}

	/**
	 * Check if this field can hold an array of values
	 *
	 * @since 2.0.9
	 *
	 * @param array|object $field
	 * @return boolean
	 */
	public static function is_field_with_multiple_values( $field ) {
		if ( ! $field ) {
			return false;
		}

		if ( is_array( $field ) ) {

			$is_multi_value_field = (
				$field['type'] == 'checkbox' ||
				$field['type'] == 'address' ||
				( $field['type'] == 'data' && isset($field['data_type']) && $field['data_type'] == 'checkbox' ) ||
				( $field['type'] == 'lookup' && isset($field['data_type']) && $field['data_type'] == 'checkbox' ) ||
				self::is_multiple_select( $field )
			);

		} else {
			$is_multi_value_field = (
				$field->type == 'checkbox' ||
				$field->type == 'address' ||
				( $field->type == 'data' && isset( $field->field_options['data_type'] ) && $field->field_options['data_type'] == 'checkbox' ) ||
				( $field->type == 'lookup' && isset( $field->field_options['data_type'] ) && $field->field_options['data_type'] == 'checkbox' ) ||
				self::is_multiple_select( $field )
			);
		}

		return $is_multi_value_field;
	}

	/**
	 * Check if this is a multiselect dropdown field
	 *
	 * @since 2.0.9
	 * @return boolean
	 */
	public static function is_multiple_select( $field ) {
		if ( is_array( $field ) ) {
			return self::is_option_true( $field, 'multiple' ) && ( ( $field['type'] == 'select' || ( $field['type'] == 'data' && isset( $field['data_type'] ) && $field['data_type'] == 'select') ) );
		} else {
			return self::is_option_true( $field, 'multiple' ) && ( ( $field->type == 'select' || ( $field->type == 'data' && isset($field->field_options['data_type'] ) && $field->field_options['data_type'] == 'select') ) );
		}
	}

	/**
	 * Check if a field is read only. Read only can be set in the field options,
	 * but disabled with the shortcode options
	 *
	 * @since 2.0.9
	 */
	public static function is_read_only( $field ) {
		global $frm_vars;
		return ( self::is_option_true( $field, 'read_only' ) && ( ! isset( $frm_vars['readonly'] ) || $frm_vars['readonly'] != 'disabled' ) );
	}

	/**
	 * @since 2.0.9
	 */
	public static function is_required( $field ) {
		$required = ( $field['required'] != '0' );
		$required = apply_filters( 'frm_is_field_required', $required, $field );
		return $required;
	}

	/**
	 * @since 2.0.9
	 */
	public static function is_option_true( $field, $option ) {
		if ( is_array( $field ) ) {
			return self::is_option_true_in_array( $field, $option );
		} else {
			return self::is_option_true_in_object( $field, $option );
		}
	}

	/**
	 * @since 2.0.9
	 */
	public static function is_option_empty( $field, $option ) {
		if ( is_array( $field ) ) {
			return self::is_option_empty_in_array( $field, $option );
		} else {
			return self::is_option_empty_in_object( $field, $option );
		}
	}

	public static function is_option_true_in_array( $field, $option ) {
		return isset( $field[ $option ] ) && $field[ $option ];
	}

	public static function is_option_true_in_object( $field, $option ) {
		return isset( $field->field_options[ $option ] ) && $field->field_options[ $option ];
	}

	public static function is_option_empty_in_array( $field, $option ) {
		return ! isset( $field[ $option ] ) || empty( $field[ $option ] );
	}

	public static function is_option_empty_in_object( $field, $option ) {
		return ! isset( $field->field_options[ $option ] ) || empty( $field->field_options[ $option ] );
	}

	public static function is_option_value_in_object( $field, $option ) {
		return isset( $field->field_options[ $option ] ) && $field->field_options[ $option ] != '';
	}

	/**
	 * @since 2.0.18
	 */
	public static function get_option( $field, $option ) {
		if ( is_array( $field ) ) {
			$option = self::get_option_in_array( $field, $option );
		} else {
			$option = self::get_option_in_object( $field, $option );
		}
		return $option;
	}

	public static function get_option_in_array( $field, $option ) {
		return $field[ $option ];
	}

	public static function get_option_in_object( $field, $option ) {
		return isset( $field->field_options[ $option ] ) ? $field->field_options[ $option ] : '';
	}

	/**
	* @since 2.0.09
	*/
	public static function is_repeating_field( $field ) {
		if ( is_array( $field ) ) {
			$is_repeating_field = ( 'divider' == $field['type'] );
		} else {
			$is_repeating_field = ( 'divider' == $field->type );
		}
		return ( $is_repeating_field && self::is_option_true( $field, 'repeat' ) );
	}

    /**
     * @param string $key
     * @return int field id
     */
	public static function get_id_by_key( $key ) {
        $id = FrmDb::get_var( 'frm_fields', array( 'field_key' => sanitize_title( $key ) ) );
        return $id;
    }

	/**
	 * @param string $id
	 * @return string
	 */
	public static function get_key_by_id( $id ) {
		return FrmDb::get_var( 'frm_fields', array( 'id' => $id ), 'field_key' );
	}
}
