<?php
if ( ! defined('ABSPATH') ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEntriesHelper {

    public static function setup_new_vars( $fields, $form = '', $reset = false ) {
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
            $default = $field->default_value;
            $posted_val = false;
            $new_value = $default;

            if ( ! $reset && $_POST && isset( $_POST['item_meta'][ $field->id ] ) && $_POST['item_meta'][ $field->id ] != '' ) {
                $new_value = stripslashes_deep( $_POST['item_meta'][ $field->id ] );
                $posted_val = true;
            } else if ( isset($field->field_options['clear_on_focus']) && $field->field_options['clear_on_focus'] ) {
                $new_value = '';
            }

            $is_default = ($new_value == $default) ? true : false;

    		//If checkbox, multi-select dropdown, or checkbox data from entries field, set return array to true
            $return_array = FrmFieldsHelper::is_field_with_multiple_values( $field );

            $field->default_value = apply_filters('frm_get_default_value', $field->default_value, $field, true, $return_array);

            if ( ! is_array( $new_value ) ) {
                if ( $is_default ) {
                    $new_value = $field->default_value;
                } else if ( ! $posted_val ) {
                    $new_value = apply_filters('frm_filter_default_value', $new_value, $field);
                }

                $new_value = str_replace('"', '&quot;', $new_value);
            }

            unset($is_default, $posted_val);

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

    public static function setup_edit_vars($values, $record) {
		$values['item_key'] = FrmAppHelper::get_post_param( 'item_key', $record->item_key, 'sanitize_title' );
        $values['form_id'] = $record->form_id;
        $values['is_draft'] = $record->is_draft;
        return apply_filters('frm_setup_edit_entry_vars', $values, $record);
    }

    public static function get_admin_params( $form = null ) {
        $form_id = $form;
        if ( $form === null ) {
            $form_id = self::get_current_form_id();
        } else if ( $form && is_object($form) ) {
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

    public static function set_current_form($form_id) {
		global $frm_vars;

		$query = array();
        if ( $form_id ) {
			$query['id'] = $form_id;
        }

        $frm_vars['current_form'] = FrmForm::get_published_forms( $query, 1 );

        return $frm_vars['current_form'];
    }

    public static function get_current_form($form_id = 0) {
        global $frm_vars, $wpdb;

		if ( isset($frm_vars['current_form']) && $frm_vars['current_form'] && ( ! $form_id || $form_id == $frm_vars['current_form']->id ) ) {
			return $frm_vars['current_form'];
		}

		$form_id = FrmAppHelper::get_param('form', $form_id, 'get', 'absint' );
        return self::set_current_form($form_id);
    }

    public static function get_current_form_id() {
        $form = self::get_current_form();
        $form_id = $form ? $form->id : 0;

        return $form_id;
    }

    /**
     * If $entry is numeric, get the entry object
     * @param int|object $entry by reference
     *
     */
    public static function maybe_get_entry( &$entry ) {
        if ( $entry && is_numeric($entry) ) {
            $entry = FrmEntry::getOne($entry);
        }
    }

    public static function fill_entry_values($atts, $f, array &$values) {
		if ( FrmFieldsHelper::is_no_save_field( $f->type ) ) {
            return;
        }

        if ( $atts['default_email'] ) {
            $values[ $f->id ] = array( 'label' => '['. $f->id .' show=field_label]', 'val' => '['. $f->id .']' );
            return;
        }

        //Remove signature from default-message shortcode
        if ( $f->type == 'signature' ) {
            return;
        }

        if ( $atts['entry'] && ! isset( $atts['entry']->metas[ $f->id ] ) ) {
            // In case include_blank is set
            $atts['entry']->metas[ $f->id ] = '';

            if ( FrmAppHelper::pro_is_installed() ) {
                // If field is a post field
                if ( $atts['entry']->post_id  && ( $f->type == 'tag' || (isset($f->field_options['post_field']) && $f->field_options['post_field'])) ) {
                    $p_val = FrmProEntryMetaHelper::get_post_value($atts['entry']->post_id, $f->field_options['post_field'], $f->field_options['custom_field'], array(
                        'truncate' => (($f->field_options['post_field'] == 'post_category') ? true : false),
                        'form_id' => $atts['entry']->form_id, 'field' => $f, 'type' => $f->type,
						'exclude_cat' => ( isset( $f->field_options['exclude_cat'] ) ? $f->field_options['exclude_cat'] : 0 ),
                    ));
                    if ( $p_val != '' ) {
                        $atts['entry']->metas[ $f->id ] = $p_val;
                    }
                }

                // If field is in a repeating section
                if ( $atts['entry']->form_id != $f->form_id ) {
                    // get entry ids linked through repeat field or embeded form
                    $child_entries = FrmProEntry::get_sub_entries($atts['entry']->id, true);
                    $val = FrmProEntryMetaHelper::get_sub_meta_values($child_entries, $f);
                    if ( ! empty( $val ) ) {
						//Flatten multi-dimensional array for multi-file upload field
						self::flatten_multi_file_upload( $val, $f );
                        $atts['entry']->metas[ $f->id ] = $val;
                    }
				} else {
					$val = '';
					FrmProEntriesHelper::get_dynamic_list_values( $f, $atts['entry'], $val );
					$atts['entry']->metas[ $f->id ] = $val;
                }
            }
        }

        $val = '';
        if ( $atts['entry'] ) {
            $prev_val = maybe_unserialize( $atts['entry']->metas[ $f->id ] );
			$meta = array( 'item_id' => $atts['id'], 'field_id' => $f->id, 'meta_value' => $prev_val, 'field_type' => $f->type );

            //This filter applies to the default-message shortcode and frm-show-entry shortcode only
            if ( isset($atts['filter']) && $atts['filter'] == false ) {
                $val = $prev_val;
            } else {
                $val = apply_filters('frm_email_value', $prev_val, (object) $meta, $atts['entry']);
            }
        }

		// Don't include blank values
		if ( ! $atts['include_blank'] && FrmAppHelper::is_empty_value( $val ) ) {
			return;
		}

        self::textarea_display_value( $val, $f->type, $atts['plain_text'] );

        if ( is_array($val) && $atts['format'] == 'text' ) {
            $val = implode(', ', $val);
        }

        if ( $atts['format'] != 'text' ) {
            $values[ $f->field_key ] = $val;
        } else {
            $values[ $f->id ] = array( 'label' => $f->name, 'val' => $val );
        }
    }

	/**
	* Flatten multi-dimensional array for multi-file upload fields
	* @since 2.0
	*/
	public static function flatten_multi_file_upload( &$val, $field ) {
		if ( $field->type == 'file' && isset( $field->field_options['multiple'] ) && $field->field_options['multiple'] ) {
			$val = FrmAppHelper::array_flatten( $val );
		}
	}

    /**
     * Replace returns with HTML line breaks for display
     * @since 2.0
     */
    public static function textarea_display_value( &$value, $type, $plain_text ) {
        if ( $type == 'textarea' && ! $plain_text ) {
			$value = str_replace( array( "\r\n", "\r", "\n" ), ' <br/>', $value );
        }
    }

    public static function fill_entry_user_info($atts, array &$values) {
        if ( ! $atts['user_info'] ) {
            return;
        }

		$data  = self::get_entry_description_data( $atts );

		if ( $atts['default_email'] ) {
			$atts['entry']->ip = '[ip]';
		}

        if ( $atts['format'] != 'text' ) {
            $values['ip'] = $atts['entry']->ip;
            $values['browser'] = self::get_browser($data['browser']);
            $values['referrer'] = $data['referrer'];
        } else {
			$values['ip'] = array( 'label' => __( 'IP Address', 'formidable' ), 'val' => $atts['entry']->ip );
            $values['browser'] = array(
                'label' => __( 'User-Agent (Browser/OS)', 'formidable' ),
                'val' => self::get_browser($data['browser']),
            );
			$values['referrer'] = array( 'label' => __( 'Referrer', 'formidable' ), 'val' => $data['referrer'] );
        }
    }

	/**
	 * @param array $atts - include (object) entry, (boolean) default_email
	 * @since 2.0.8
	 */
	public static function get_entry_description_data( $atts ) {
		$default_data = array(
			'browser' => '',
			'referrer' => '',
		);
		$data = $default_data;

		if ( isset( $atts['entry']->description ) ) {
			$data = maybe_unserialize( $atts['entry']->description );
		} else if ( $atts['default_email'] ) {
			$data = array(
				'browser'  => '[browser]',
				'referrer' => '[referrer]',
			);
		}

		return array_merge( $default_data, $data );
	}

    public static function convert_entry_to_content($values, $atts, array &$content) {

        if ( $atts['plain_text'] ) {
            $bg_color_alt = $row_style = '';
        } else {
            $default_settings = apply_filters('frm_show_entry_styles', array(
                'border_color' => 'dddddd',
                'bg_color' => 'f7f7f7',
                'text_color' => '444444',
                'font_size' => '12px',
                'border_width' => '1px',
                'alt_bg_color' => 'ffffff',
            ) );

            // merge defaults, global settings, and shortcode options
            foreach ( $default_settings as $key => $setting ) {
                if ( $atts[ $key ] != '' ) {
                    continue;
                }

                $atts[ $key ] = $setting;
                unset($key, $setting);
            }

            unset($default_settings);

            $content[] = '<table cellspacing="0" style="font-size:'. $atts['font_size'] .';line-height:135%; border-bottom:'. $atts['border_width'] .' solid #'. $atts['border_color'] .';"><tbody>'."\r\n";
            $atts['bg_color'] = ' style="background-color:#'. $atts['bg_color'] .';"';
            $bg_color_alt = ' style="background-color:#'. $atts['alt_bg_color'] .';"';
            $row_style = 'style="text-align:'. ( $atts['direction'] == 'rtl' ? 'right' : 'left' ) .';color:#'. $atts['text_color'] .';padding:7px 9px;border-top:'. $atts['border_width'] .' solid #'. $atts['border_color'] .'"';
        }

        $odd = true;
        foreach ( $values as $id => $value ) {
            if ( $atts['plain_text'] ) {
                if ( 'rtl' == $atts['direction'] ) {
                    $content[] = $value['val'] . ' :'. $value['label'] ."\r\n";
                } else {
                    $content[] = $value['label'] . ': '. $value['val'] ."\r\n";
                }
                continue;
            }

            if ( $atts['default_email'] && is_numeric($id) ) {
                $content[] = '[if '. $id .']<tr style="[frm-alt-color]">';
            } else {
                $content[] = '<tr'. ( $odd ? $atts['bg_color'] : $bg_color_alt ) .'>';
            }

			$value['val'] = str_replace( "\r\n", '<br/>', $value['val'] );
            if ( 'rtl' == $atts['direction'] ) {
                $content[] = '<td '. $row_style .'>'. $value['val'] .'</td><th '. $row_style .'>'. $value['label'] . '</th>';
            } else {
                $content[] = '<th '. $row_style .'>'. $value['label'] .'</th><td '. $row_style .'>'. $value['val'] .'</td>';
            }
            $content[] = '</tr>'. "\r\n";

            if ( $atts['default_email'] && is_numeric($id) ) {
                $content[] = '[/if '. $id .']';
            }
            $odd = $odd ? false : true;
        }

        if ( ! $atts['plain_text'] ) {
            $content[] = '</tbody></table>';
        }
    }

    public static function replace_default_message($message, $atts) {
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

            $default = FrmEntriesController::show_entry_shortcode($this_atts);

            // Add the default message
            $message = str_replace( $shortcodes[0][ $short_key ], $default, $message );
        }

        return $message;
    }

    public static function prepare_display_value($entry, $field, $atts) {
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
    public static function display_value($value, $field, $atts = array()) {

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

    public static function set_posted_value($field, $value, $args) {
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

    public static function get_posted_value($field, &$value, $args) {
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
        if ( isset( $field->field_options['other'] ) && $field->field_options['other'] && isset( $_POST['item_meta']['other'][ $field->id ] ) ) {

            // Save original value
            $args['temp_value'] = $value;
            $args['other'] = true;
            $other_vals = $_POST['item_meta']['other'][ $field->id ];

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
        if ( isset( $field->field_options['other'] ) && $field->field_options['other'] && isset( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ]['other'][ $field->id ] ) ) {
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

    public static function entries_dropdown() {
        _deprecated_function( __FUNCTION__, '1.07.09');
    }

    public static function enqueue_scripts($params) {
        do_action('frm_enqueue_form_scripts', $params);
    }

    // Add submitted values to a string for spam checking
    public static function entry_array_to_string($values) {
        $content = '';
		foreach ( $values['item_meta'] as $val ) {
			if ( $content != '' ) {
				$content .= "\n\n";
			}

			if ( is_array($val) ) {
			    $val = implode(',', $val);
			}

			$content .= $val;
		}

		return $content;
    }

    public static function get_browser($u_agent) {
        $bname = __( 'Unknown', 'formidable' );
        $platform = __( 'Unknown', 'formidable' );
        $ub = '';

		// Get the operating system
		if ( preg_match('/windows|win32/i', $u_agent) ) {
			$platform = 'Windows';
		} else if ( preg_match('/android/i', $u_agent) ) {
			$platform = 'Android';
		} else if ( preg_match('/linux/i', $u_agent) ) {
			$platform = 'Linux';
		} else if ( preg_match('/macintosh|mac os x/i', $u_agent) ) {
			$platform = 'OS X';
		}

		$agent_options = array(
			'Chrome' => 'Google Chrome',
			'Safari' => 'Apple Safari',
			'Opera' => 'Opera',
			'Netscape' => 'Netscape',
			'Firefox' => 'Mozilla Firefox',
		);

        // Next get the name of the useragent yes seperately and for good reason
        if ( strpos( $u_agent, 'MSIE' ) !== false && strpos( $u_agent, 'Opera' ) === false ) {
            $bname = 'Internet Explorer';
            $ub = 'MSIE';
        } else {
			foreach ( $agent_options as $agent_key => $agent_name ) {
				if ( strpos( $u_agent, $agent_key ) !== false ) {
					$bname = $agent_name;
					$ub = $agent_key;
					break;
				}
			}
		}

        // finally get the correct version number
		$known = array( 'Version', $ub, 'other' );
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        preg_match_all($pattern, $u_agent, $matches); // get the matching numbers

        // see how many we have
        $i = count($matches['browser']);
        if ( $i != 1 ) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if ( strripos( $u_agent, 'Version' ) < strripos( $u_agent, $ub ) ) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
		if ( $version == '' ) {
            $version = '?';
        }

        return $bname .' '. $version .' / '. $platform;
    }

}
