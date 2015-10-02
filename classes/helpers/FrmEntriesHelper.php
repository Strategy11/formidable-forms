<?php
if ( ! defined('ABSPATH') ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEntriesHelper {

    public static function setup_new_vars( $fields, $form = '', $reset = false, $args = array() ) {
        global $frm_vars;
        $values = array();
		foreach ( array( 'name' => '', 'description' => '', 'item_key' => '' ) as $var => $default ) {
            $values[ $var ] = FrmAppHelper::get_post_param( $var, $default );
        }

        $values['fields'] = array();
        if ( empty($fields) ) {
            return apply_filters('frm_setup_new_entry', $values);
        }

        foreach ( (array) $fields as $field ) {
            $new_value = self::get_field_value_for_new_entry( $field, $reset );

            $field_array = array(
                'id' => $field->id,
                'value' => $new_value,
                'default_value' => $field->default_value,
                'name' => $field->name,
                'description' => $field->description,
                'type' => apply_filters('frm_field_type', $field->type, $field, $new_value),
                'options' => $field->options,
                'required' => $field->required,
                'field_key' => $field->field_key,
                'field_order' => $field->field_order,
                'form_id' => $field->form_id,
				'parent_form_id' => isset( $args['parent_form_id'] ) ? $args['parent_form_id'] : $field->form_id,
            );

            $opt_defaults = FrmFieldsHelper::get_default_field_opts($field_array['type'], $field, true);
            $opt_defaults['required_indicator'] = '';
			$opt_defaults['original_type'] = $field->type;

			foreach ( $opt_defaults as $opt => $default_opt ) {
                $field_array[ $opt ] = ( isset( $field->field_options[ $opt ] ) && $field->field_options[ $opt ] != '' ) ? $field->field_options[ $opt ] : $default_opt;
                unset($opt, $default_opt);
            }

            unset($opt_defaults);

            if ( $field_array['custom_html'] == '' ) {
                $field_array['custom_html'] = FrmFieldsHelper::get_default_html($field->type);
            }

            $field_array = apply_filters('frm_setup_new_fields_vars', $field_array, $field);
            $field_array = array_merge( $field->field_options, $field_array );

            $values['fields'][] = $field_array;

            if ( ! $form || ! isset($form->id) ) {
                $form = FrmForm::getOne($field->form_id);
            }
        }

        $form->options = maybe_unserialize($form->options);
        if ( is_array($form->options) ) {
            foreach ( $form->options as $opt => $value ) {
                $values[ $opt ] = FrmAppHelper::get_post_param( $opt, $value );
                unset($opt, $value);
            }
        }

		$form_defaults = FrmFormsHelper::get_default_opts();

		$frm_settings = FrmAppHelper::get_settings();
		$form_defaults['custom_style']  = ( $frm_settings->load_style != 'none' );

		$values = array_merge( $form_defaults, $values );

		return apply_filters( 'frm_setup_new_entry', $values );
    }

	/**
	* Set the value for each field
	* This function is used when the form is first loaded and on all page turns *for a new entry*
	*
	* @since 2.0.13
	*
	* @param object $field - this is passed by reference since it is an object
	* @param boolean $reset
	* @return string|array $new_value
	*/
	private static function get_field_value_for_new_entry( $field, $reset ) {
		//If checkbox, multi-select dropdown, or checkbox data from entries field, the value should be an array
		$return_array = FrmField::is_field_with_multiple_values( $field );

		// Do any shortcodes in default value and allow customization of default value
		$field->default_value = apply_filters('frm_get_default_value', $field->default_value, $field, true, $return_array);
		// Calls FrmProFieldsHelper::get_default_value

		$new_value = $field->default_value;

		if ( ! $reset && $_POST && isset( $_POST['item_meta'][ $field->id ] ) ) {
			// If value was posted, get it

			$new_value = stripslashes_deep( $_POST['item_meta'][ $field->id ] );

		} else if ( FrmField::is_option_true( $field, 'clear_on_focus' ) ) {
			// If clear on focus is selected, the value should be blank (unless it was posted, of course)

			$new_value = '';
		}

		if ( ! is_array( $new_value ) ) {
			$new_value = str_replace('"', '&quot;', $new_value);
		}

		return $new_value;
	}

	public static function setup_edit_vars( $values, $record ) {
		$values['item_key'] = FrmAppHelper::get_post_param( 'item_key', $record->item_key, 'sanitize_title' );
        $values['form_id'] = $record->form_id;
        $values['is_draft'] = $record->is_draft;
        return apply_filters('frm_setup_edit_entry_vars', $values, $record);
    }

    public static function get_admin_params( $form = null ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmForm::get_admin_params' );
		return FrmForm::set_current_form( $form );
    }

	public static function set_current_form( $form_id ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmForm::set_current_form' );
		return FrmForm::set_current_form( $form_id );
	}

	public static function get_current_form( $form_id = 0 ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmForm::get_current_form' );
		return FrmForm::get_current_form( $form_id );
	}

    public static function get_current_form_id() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmForm::get_current_form_id' );
		return FrmForm::get_current_form_id();
    }

    public static function maybe_get_entry( &$entry ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntry::maybe_get_entry' );
		FrmEntry::maybe_get_entry( $entry );
    }

	public static function replace_default_message( $message, $atts ) {
        if ( strpos($message, '[default-message') === false &&
            strpos($message, '[default_message') === false &&
            ! empty( $message ) ) {
            return $message;
        }

        if ( empty($message) ) {
            $message = '[default-message]';
        }

        preg_match_all("/\[(default-message|default_message)\b(.*?)(?:(\/))?\]/s", $message, $shortcodes, PREG_PATTERN_ORDER);

        foreach ( $shortcodes[0] as $short_key => $tag ) {
            $add_atts = shortcode_parse_atts( $shortcodes[2][ $short_key ] );
            if ( $add_atts ) {
                $this_atts = array_merge($atts, $add_atts);
            } else {
                $this_atts = $atts;
            }

			$default = FrmEntryFormat::show_entry( $this_atts );

            // Add the default message
            $message = str_replace( $shortcodes[0][ $short_key ], $default, $message );
        }

        return $message;
    }

	public static function prepare_display_value( $entry, $field, $atts ) {
		$field_value = isset( $entry->metas[ $field->id ] ) ? $entry->metas[ $field->id ] : false;
        if ( FrmAppHelper::pro_is_installed() ) {
			FrmProEntriesHelper::get_dynamic_list_values( $field, $entry, $field_value );
        }

        if ( $field->form_id == $entry->form_id || empty($atts['embedded_field_id']) ) {
            return self::display_value($field_value, $field, $atts);
        }

        // this is an embeded form
        $val = '';

	    if ( strpos($atts['embedded_field_id'], 'form') === 0 ) {
            //this is a repeating section
			$child_entries = FrmEntry::getAll( array( 'it.parent_item_id' => $entry->id ) );
        } else {
            // get all values for this field
	        $child_values = isset( $entry->metas[ $atts['embedded_field_id'] ] ) ? $entry->metas[ $atts['embedded_field_id'] ] : false;

            if ( $child_values ) {
	            $child_entries = FrmEntry::getAll( array( 'it.id' => (array) $child_values ) );
	        }
	    }

	    $field_value = array();

        if ( ! isset($child_entries) || ! $child_entries || ! FrmAppHelper::pro_is_installed() ) {
            return $val;
        }

        foreach ( $child_entries as $child_entry ) {
            $atts['item_id'] = $child_entry->id;
            $atts['post_id'] = $child_entry->post_id;

            // get the value for this field -- check for post values as well
            $entry_val = FrmProEntryMetaHelper::get_post_or_meta_value($child_entry, $field);

            if ( $entry_val ) {
                // foreach entry get display_value
                $field_value[] = self::display_value($entry_val, $field, $atts);
            }

            unset($child_entry);
        }

        $val = implode(', ', (array) $field_value );
		$val = wp_kses_post( $val );

        return $val;
    }

    /**
     * Prepare the saved value for display
     * @return string
     */
	public static function display_value( $value, $field, $atts = array() ) {

        $defaults = array(
            'type' => '', 'html' => false, 'show_filename' => true,
            'truncate' => false, 'sep' => ', ', 'post_id' => 0,
            'form_id' => $field->form_id, 'field' => $field, 'keepjs' => 0,
			'return_array' => false,
        );

        $atts = wp_parse_args( $atts, $defaults );
        $atts = apply_filters('frm_display_value_atts', $atts, $field, $value);

        if ( ! isset($field->field_options['post_field']) ) {
            $field->field_options['post_field'] = '';
        }

        if ( ! isset($field->field_options['custom_field']) ) {
            $field->field_options['custom_field'] = '';
        }

        if ( FrmAppHelper::pro_is_installed() && $atts['post_id'] && ( $field->field_options['post_field'] || $atts['type'] == 'tag' ) ) {
            $atts['pre_truncate'] = $atts['truncate'];
            $atts['truncate'] = true;
            $atts['exclude_cat'] = isset($field->field_options['exclude_cat']) ? $field->field_options['exclude_cat'] : 0;

            $value = FrmProEntryMetaHelper::get_post_value($atts['post_id'], $field->field_options['post_field'], $field->field_options['custom_field'], $atts);
            $atts['truncate'] = $atts['pre_truncate'];
        }

        if ( $value == '' ) {
            return $value;
        }

        $value = apply_filters('frm_display_value_custom', maybe_unserialize($value), $field, $atts);

        $new_value = '';

        if ( is_array($value) && $atts['type'] != 'file' ) {
            foreach ( $value as $val ) {
                if ( is_array($val) ) {
					//TODO: add options for display (li or ,)
                    $new_value .= implode($atts['sep'], $val);
                    if ( $atts['type'] != 'data' ) {
                        $new_value .= '<br/>';
                    }
                }
                unset($val);
            }
        }

        if ( ! empty($new_value) ) {
            $value = $new_value;
        } else if ( is_array($value) && $atts['type'] != 'file' && ! $atts['return_array'] ) {
            $value = implode($atts['sep'], $value);
        }

        if ( $atts['truncate'] && $atts['type'] != 'image' ) {
            $value = FrmAppHelper::truncate($value, 50);
        }

		if ( ! $atts['keepjs'] && ! is_array( $value ) ) {
			$value = wp_kses_post( $value );
		}

        return apply_filters('frm_display_value', $value, $field, $atts);
    }

	public static function set_posted_value( $field, $value, $args ) {
        // If validating a field with "other" opt, set back to prev value now
        if ( isset( $args['other'] ) && $args['other'] ) {
            $value = $args['temp_value'];
        }
        if ( empty($args['parent_field_id']) ) {
            $_POST['item_meta'][ $field->id ] = $value;
        } else {
            $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field->id ] = $value;
        }
    }

	public static function get_posted_value( $field, &$value, $args ) {
		$field_id = is_object( $field ) ? $field->id : $field;

        if ( empty($args['parent_field_id']) ) {
            $value = isset( $_POST['item_meta'][ $field_id ] ) ? $_POST['item_meta'][ $field_id ] : '';
        } else {
            $value = isset( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field_id ] ) ? $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field_id ] : '';
        }
    }

    /**
    * Check if field has an "Other" option and if any other values are posted
    *
    * @since 2.0
    *
    * @param object $field
    * @param string|array $value
    * @param array $args
    */
    public static function maybe_set_other_validation( $field, &$value, &$args ) {
        $args['other'] = false;
        if ( ! $value || empty( $value ) || ! FrmAppHelper::pro_is_installed() ) {
            return;
        }

        // Get other value for fields in repeating section
        self::set_other_repeating_vals( $field, $value, $args );

        // Check if there are any posted "Other" values
		if ( FrmField::is_option_true( $field, 'other' ) && isset( $_POST['item_meta']['other'][ $field->id ] ) ) {

            // Save original value
            $args['temp_value'] = $value;
            $args['other'] = true;
            $other_vals = stripslashes_deep( $_POST['item_meta']['other'][ $field->id ] );

            // Set the validation value now
            self::set_other_validation_val( $value, $other_vals, $field, $args );
        }
    }

    /**
    * Sets radio or checkbox value equal to "other" value if it is set - FOR REPEATING SECTIONS
    *
    * @since 2.0
    *
    * @param object $field
    * @param string|array $value
    * @param array $args
    */
    public static function set_other_repeating_vals( $field, &$value, &$args ) {
        if ( ! $args['parent_field_id'] ) {
            return;
        }

        // Check if there are any other posted "other" values for this field
		if ( FrmField::is_option_true( $field, 'other' ) && isset( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ]['other'][ $field->id ] ) ) {
            // Save original value
            $args['temp_value'] = $value;
            $args['other'] = true;

            $other_vals = $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ]['other'][ $field->id ];

            // Set the validation value now
            self::set_other_validation_val( $value, $other_vals, $field, $args );
        }
    }

    /**
    * Modify value used for validation
    * This function essentially removes the "Other" radio or checkbox value from the $value being validated.
    * It also adds any text from the free text fields to the value
    *
    * Needs to accommodate for times when other opt is selected, but no other free text is entered
    *
    * @since 2.0
    *
    * @param string|array $value
    * @param string|array $other_vals (usually of posted values)
    * @param object $field
    * @param array $args
    */
    public static function set_other_validation_val( &$value, $other_vals, $field, &$args ) {
        // Checkboxes and multi-select dropdowns
        if ( is_array( $value ) && $field->type == 'checkbox' ) {
            // Combine "Other" values with checked values. "Other" values will override checked box values.
            $value = array_merge( $value, $other_vals );
            $value = array_filter( $value );
            if ( count( $value ) == 0 ) {
                $value = '';
            }
        } else {
			// Radio and dropdowns
            $other_key = array_filter( array_keys($field->options), 'is_string');
            $other_key = reset( $other_key );

            // Multi-select dropdown
            if ( is_array( $value ) ) {
                $o_key = array_search( $field->options[ $other_key ], $value );

				if ( $o_key !== false ) {
					// Modify the original value so other key will be preserved
					$value[ $other_key ] = $value[ $o_key ];

					// By default, the array keys will be numeric for multi-select dropdowns
					// If going backwards and forwards between pages, the array key will match the other key
					if ( $o_key != $other_key ) {
						unset( $value[ $o_key ] );
					}

					$args['temp_value'] = $value;
					$value[ $other_key ] = reset( $other_vals );
				}
            } else if ( $field->options[ $other_key ] == $value ) {
                $value = $other_vals;
            }
        }
    }

	public static function enqueue_scripts( $params ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmFormsController::enqueue_scripts' );
		return FrmFormsController::enqueue_scripts( $params );
	}

    // Add submitted values to a string for spam checking
	public static function entry_array_to_string( $values ) {
        $content = '';
		foreach ( $values['item_meta'] as $val ) {
			if ( $content != '' ) {
				$content .= "\n\n";
			}

			if ( is_array($val) ) {
				$val = FrmAppHelper::array_flatten( $val );
			    $val = implode(',', $val);
			}

			$content .= $val;
		}

		return $content;
    }

	public static function fill_entry_values( $atts, $f, array &$values ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryFormat::fill_entry_values' );
		FrmEntryFormat::fill_entry_values( $atts, $f, $values );
	}

	public static function flatten_multi_file_upload( &$val, $field ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryFormat::flatten_multi_file_upload' );
		FrmEntryFormat::flatten_multi_file_upload( $field, $val );
	}

	public static function textarea_display_value( &$value, $type, $plain_text ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryFormat::textarea_display_value' );
		FrmEntryFormat::textarea_display_value( $type, $plain_text, $value );
	}

	public static function fill_entry_user_info( $atts, array &$values ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryFormat::fill_entry_user_info' );
		FrmEntryFormat::fill_entry_user_info( $atts, $values );
	}

	public static function get_entry_description_data( $atts ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryFormat::get_entry_description_data' );
		return FrmEntryFormat::get_entry_description_data( $atts );
	}

	public static function convert_entry_to_content( $values, $atts, array &$content ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryFormat::convert_entry_to_content' );
		return FrmEntryFormat::convert_entry_to_content( $values, $atts, $content );
	}

	public static function get_browser( $u_agent ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryFormat::get_browser' );
		return FrmEntryFormat::get_browser( $u_agent );
	}

	public static function entries_dropdown() {
		_deprecated_function( __FUNCTION__, '1.07.09');
	}
}
