<?php

class FrmFieldsController {

    public static function load_field() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        $fields = $_POST['field'];
        if ( empty( $fields ) ) {
            wp_die();
        }

        $_GET['page'] = 'formidable';
        $fields = stripslashes_deep( $fields );

        $ajax = true;
		$values = array( 'id' => FrmAppHelper::get_post_param( 'form_id', '', 'absint' ) );
        $path = FrmAppHelper::plugin_path();
        $field_html = array();

        foreach ( $fields as $field ) {
            $field = htmlspecialchars_decode( nl2br( $field ) );
            $field = json_decode( $field, true );
            if ( ! isset( $field['id'] ) ) {
                // this field may have already been loaded
                continue;
            }

            $field_id = absint( $field['id'] );

            if ( ! isset( $field['value'] ) ) {
                $field['value'] = '';
            }

            $field_name = 'item_meta['. $field_id .']';
            $html_id = FrmFieldsHelper::get_html_id($field);

            ob_start();
            include($path .'/classes/views/frm-forms/add_field.php');
            $field_html[ $field_id ] = ob_get_contents();
            ob_end_clean();
        }

        unset($path);

        echo json_encode($field_html);

        wp_die();
    }

    public static function create() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_type = FrmAppHelper::get_post_param( 'field', '', 'sanitize_text_field' );
		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

        $field = self::include_new_field($field_type, $form_id);

        // this hook will allow for multiple fields to be added at once
        do_action('frm_after_field_created', $field, $form_id);

        wp_die();
    }

    /**
     * @param integer $form_id
     */
	public static function include_new_field( $field_type, $form_id ) {
        $values = array();
        if ( FrmAppHelper::pro_is_installed() ) {
            $values['post_type'] = FrmProFormsHelper::post_type($form_id);
        }

        $field_values = apply_filters('frm_before_field_created', FrmFieldsHelper::setup_new_vars($field_type, $form_id));
        $field_id = FrmField::create( $field_values );

        if ( ! $field_id ) {
            return false;
        }

        $field = self::include_single_field($field_id, $values, $form_id);

        return $field;
    }

    public static function update_form_id() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field', 0, 'absint' );
		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

        if ( ! $field_id || ! $form_id ) {
            wp_die();
        }

		$updated = FrmField::update( $field_id, compact( 'form_id' ) );
		echo absint( $updated );

        wp_die();
    }

	public static function edit_name( $field = 'name', $id = '' ) {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        if ( empty($field) ) {
            $field = 'name';
        }

        if ( empty($id) ) {
			$id = FrmAppHelper::get_post_param( 'element_id', '', 'sanitize_title' );
			$id = str_replace( 'field_label_', '', $id );
        }

		$value = FrmAppHelper::get_post_param( 'update_value', '', 'wp_kses_post' );
		$value = trim( $value );
        if ( trim(strip_tags($value)) == '' ) {
            // set blank value if there is no content
            $value = '';
        }

		FrmField::update( $id, array( $field => $value ) );

		do_action( 'frm_after_update_field_' . $field, compact( 'id', 'value' ) );

		echo stripslashes( wp_kses_post( $value ) );
        wp_die();
    }

    public static function update_ajax_option() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field', 0, 'absint' );
		if ( ! $field_id ) {
			wp_die();
		}

		$field = FrmField::getOne( $field_id );

		foreach ( array( 'clear_on_focus', 'separate_value', 'default_blank' ) as $val ) {
            if ( isset($_POST[ $val ]) ) {
				// all three of these options are boolean
				$new_val = FrmAppHelper::get_post_param( $val, 0, 'absint' );

                if ( $val == 'separate_value' ) {
					$new_val = FrmField::is_option_true( $field, $val ) ? 0 : 1;
                }

                $field->field_options[ $val ] = $new_val;
                unset($new_val);
            }
            unset($val);
        }

        FrmField::update( $field_id, array(
            'field_options' => $field->field_options,
			'form_id'		=> $field->form_id,
        ) );
        wp_die();
    }

    public static function duplicate() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        global $wpdb;

		$field_id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );
		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

		$copy_field = FrmField::getOne( $field_id );
        if ( ! $copy_field ) {
            wp_die();
        }

        do_action('frm_duplicate_field', $copy_field, $form_id);
        do_action('frm_duplicate_field_'. $copy_field->type, $copy_field, $form_id);

        $values = array( 'id' => $form_id );
        FrmFieldsHelper::fill_field( $values, $copy_field, $form_id );

		$field_count = FrmDb::get_count( $wpdb->prefix .'frm_fields fi LEFT JOIN '. $wpdb->prefix .'frm_forms fr ON (fi.form_id = fr.id)', array( 'or' => 1, 'fr.id' => $form_id, 'fr.parent_form_id' => $form_id ) );

        $values['field_order'] = $field_count + 1;

        if ( ! $field_id = FrmField::create($values) ) {
            wp_die();
        }

        self::include_single_field($field_id, $values);

        wp_die();
    }

    /**
     * Load a single field in the form builder along with all needed variables
     */
    public static function include_single_field( $field_id, $values, $form_id = 0 ) {
        $field = FrmFieldsHelper::setup_edit_vars(FrmField::getOne($field_id));
        $field_name = 'item_meta['. $field_id .']';
        $html_id = FrmFieldsHelper::get_html_id($field);
        $id = $form_id ? $form_id : $field['form_id'];
        if ( $field['type'] == 'html' ) {
            $field['stop_filter'] = true;
        }

        require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/add_field.php');

        return $field;
    }

    public static function destroy() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );
		FrmField::destroy( $field_id );
        wp_die();
    }

    /* Field Options */

    //Add Single Option or Other Option
    public static function add_option() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );
		$opt_type = FrmAppHelper::get_post_param( 'opt_type', '', 'sanitize_text_field' );

        //Get the field
        $field = FrmField::getOne($id);

		if ( ! empty( $field->options ) ) {
			$keys = array_keys( $field->options );
            $last = str_replace( 'other_', '', end( $keys ) );
        } else {
            $last = 0;
        }
        $opt_key = $last + 1;

        if ( 'other' == $opt_type ) {
			$opt = esc_html__( 'Other', 'formidable' );
            $other_val = '';
            $opt_key = 'other_' . $opt_key;

            //Update value of "other" in DB
            $field_options = maybe_unserialize( $field->field_options );
            $field_options['other'] = 1;
            FrmField::update( $id, array( 'field_options' => maybe_serialize( $field_options ) ) );
        } else {
			$first_opt = reset( $field->options );
			$next_opt = count( $field->options );
            if ( $first_opt != '' ) {
                $next_opt++;
            }
            $opt = esc_html__( 'Option', 'formidable' ) .' '. $next_opt;
            unset($next_opt);
        }
        $field_val = $opt;
		$field->options[ $opt_key ] = $opt;

        //Update options in DB
		FrmField::update( $id, array( 'options' => $field->options ) );

        $field_data = $field;
        $field = array(
            'type'  => $field_data->type,
            'id'    => $id,
            'separate_value' => isset($field_data->field_options['separate_value']) ? $field_data->field_options['separate_value'] : 0,
            'form_id' => $field_data->form_id,
            'field_key' => $field_data->field_key,
        );

        $field_name = 'item_meta['. $id .']';
        $html_id = FrmFieldsHelper::get_html_id($field);
        $checked = '';

        if ( 'other' == $opt_type ) {
            require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/other-option.php');
        } else {
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/single-option.php');
        }
        wp_die();
    }

    public static function edit_option() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$element_id = FrmAppHelper::get_post_param( 'element_id', '', 'sanitize_title' );
		$ids = explode( '-', $element_id );
		$id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );

		$orig_update_value = $update_value = trim( FrmAppHelper::get_post_param( 'update_value' ) );
		if ( strpos( $element_id, 'key_' ) ) {
            $new_value = $update_value;
        } else {
            $new_label = $update_value;
        }

        $field = FrmField::getOne($id);
        $separate_values = FrmField::is_option_true( $field, 'separate_value' );

        $this_opt_id = end($ids);
		$this_opt = (array) $field->options[ $this_opt_id ];
        $other_opt = ( $this_opt_id && strpos( $this_opt_id, 'other') !== false ? true : false );

        $label = isset($this_opt['label']) ? $this_opt['label'] : reset($this_opt);
        $value = isset($this_opt['value']) ? $this_opt['value'] : '';

        if ( ! isset( $new_label ) ) {
            $new_label = $label;
        }

        if ( isset($new_value) || isset($value) ) {
            $update_value = isset($new_value) ? $new_value : $value;
        }

		if ( $update_value != $new_label && $other_opt === false && $separate_values ) {
			$field->options[ $this_opt_id ] = array( 'value' => $update_value, 'label' => $new_label );
        } else {
			$field->options[ $this_opt_id ] = $orig_update_value;
        }

		FrmField::update( $field->id, array( 'options' => $field->options ) );
		echo ( $orig_update_value == '' ) ? esc_html__( '(Blank)', 'formidable' ) : stripslashes( $orig_update_value );
        wp_die();
    }

    public static function delete_option() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );
		$field = FrmField::getOne( $field_id );
		// Opt key will NOT be numeric for "Other" options
		$opt_key = FrmAppHelper::get_post_param( 'opt_key', 0, 'sanitize_title' );

		$options = $field->options;
        unset( $options[ $opt_key ] );
        $response = array( 'other' => true );

        //If the deleted option is an "other" option
		if ( FrmFieldsHelper::is_other_opt( $opt_key ) ) {
            //Assume all other options are gone, unless proven otherwise
            $other = false;

            //Check if all other options are really gone
            foreach ( $options as $o_key => $o_val ) {
                //If there is still an other option in the field, set other to true
				if ( FrmFieldsHelper::is_other_opt( $o_key ) ) {
                    $other = true;
                    break;
                }
                unset( $o_key, $o_val );
            }

            //If all other options are gone
            if ( false === $other ) {
                $field_options = maybe_unserialize( $field->field_options );
                $field_options['other'] = 0;
				FrmField::update( $field_id, array( 'field_options' => maybe_serialize( $field_options ) ) );
                $response = array( 'other' => false );
            }
        }
        echo json_encode( $response );

		FrmField::update( $field_id, array( 'options' => maybe_serialize( $options ) ) );

        wp_die();
    }

    public static function import_choices() {
        if ( ! current_user_can( 'frm_edit_forms' ) ) {
            wp_die();
        }

		$field_id = absint( $_REQUEST['field_id'] );

        global $current_screen, $hook_suffix;

        // Catch plugins that include admin-header.php before admin.php completes.
        if ( empty( $current_screen ) && function_exists( 'set_current_screen' ) ) {
            $hook_suffix = '';
        	set_current_screen();
        }

        if ( function_exists( 'register_admin_color_schemes' ) ) {
            register_admin_color_schemes();
        }

        $hook_suffix = $admin_body_class = '';

        if ( get_user_setting( 'mfold' ) == 'f' ) {
        	$admin_body_class .= ' folded';
        }

        if ( function_exists( 'is_admin_bar_showing' ) && is_admin_bar_showing() ) {
        	$admin_body_class .= ' admin-bar';
        }

        if ( is_rtl() ) {
        	$admin_body_class .= ' rtl';
        }

        $admin_body_class .= ' admin-color-' . sanitize_html_class( get_user_option( 'admin_color' ), 'fresh' );
        $prepop = array();
        FrmFieldsHelper::get_bulk_prefilled_opts($prepop);

        $field = FrmField::getOne($field_id);

        wp_enqueue_script( 'utils' );
        wp_enqueue_style( 'formidable-admin', FrmAppHelper::plugin_url(). '/css/frm_admin.css' );
        FrmAppHelper::load_admin_wide_js();

        include(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/import_choices.php');
        wp_die();
    }

    public static function import_options() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        if ( ! is_admin() || ! current_user_can('frm_edit_forms') ) {
            return;
        }

		$field_id = absint( $_POST['field_id'] );
        $field = FrmField::getOne($field_id);

		if ( ! in_array( $field->type, array( 'radio', 'checkbox', 'select' ) ) ) {
            return;
        }

        $field = FrmFieldsHelper::setup_edit_vars($field);
        $opts = stripslashes_deep($_POST['opts']);
        $opts = explode("\n", rtrim($opts, "\n"));
        if ( $field['separate_value'] ) {
            foreach ( $opts as $opt_key => $opt ) {
                if ( strpos($opt, '|') !== false ) {
                    $vals = explode('|', $opt);
                    if ( $vals[0] != $vals[1] ) {
                        $opts[ $opt_key ] = array( 'label' => trim( $vals[0] ), 'value' => trim( $vals[1] ) );
                    }
                    unset($vals);
                }
                unset($opt_key, $opt);
            }
        }

        //Keep other options after bulk update
        if ( isset( $field['field_options']['other'] ) && $field['field_options']['other'] == true ) {
            $other_array = array();
            foreach ( $field['options'] as $opt_key => $opt ) {
                if ( $opt_key && strpos( $opt_key, 'other' ) !== false ) {
                    $other_array[ $opt_key ] = $opt;
                }
                unset($opt_key, $opt);
            }
            if ( ! empty($other_array) ) {
                $opts = array_merge( $opts, $other_array);
            }
        }

        FrmField::update( $field_id, array( 'options' => maybe_serialize( $opts ) ) );

        $field['options'] = $opts;
        $field_name = $field['name'];

        // Get html_id which will be used in single-option.php
        $html_id = FrmFieldsHelper::get_html_id( $field );

        if ( $field['type'] == 'radio' || $field['type'] == 'checkbox' ) {
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/radio.php');
        } else {
            FrmFieldsHelper::show_single_option($field);
        }

        wp_die();
    }

    public static function update_order() {
        check_ajax_referer( 'frm_ajax', 'nonce' );
		$fields = FrmAppHelper::get_post_param( 'frm_field_id' );
		foreach ( (array) $fields as $position => $item ) {
			FrmField::update( absint( $item ), array( 'field_order' => absint( $position ) ) );
		}
        wp_die();
    }

	public static function change_type( $type ) {
        $type_switch = array(
            'scale'     => 'radio',
            '10radio'   => 'radio',
            'rte'       => 'textarea',
            'website'   => 'url',
        );
        if ( isset( $type_switch[ $type ] ) ) {
            $type = $type_switch[ $type ];
        }

		$frm_field_selection = FrmField::field_selection();
        $types = array_keys($frm_field_selection);
        if ( ! in_array($type, $types) && $type != 'captcha' ) {
            $type = 'text';
        }

        return $type;
    }

	public static function display_field_options( $display ) {
		switch ( $display['type'] ) {
            case 'captcha':
                $display['required'] = false;
                $display['invalid'] = true;
                $display['default_blank'] = false;
				$display['captcha_size'] = true;
            break;
            case 'radio':
                $display['default_blank'] = false;
            break;
            case 'text':
            case 'textarea':
                $display['size'] = true;
                $display['clear_on_focus'] = true;
            break;
            case 'select':
                $display['size'] = true;
            break;
            case 'url':
            case 'website':
            case 'email':
                $display['size'] = true;
                $display['clear_on_focus'] = true;
                $display['invalid'] = true;
        }

        return $display;
    }

    public static function input_html( $field, $echo = true ) {
        $class = array(); //$field['type'];
        self::add_input_classes($field, $class);

        $add_html = array();
        self::add_html_size($field, $add_html);
        self::add_html_length($field, $add_html);
        self::add_html_placeholder($field, $add_html, $class);
		//self::add_validation_messages( $field, $add_html ); uncomment this when the js validation is complete

        $class = apply_filters('frm_field_classes', implode(' ', $class), $field);

		FrmFormsHelper::add_html_attr( $class, 'class', $add_html );

        self::add_shortcodes_to_html($field, $add_html);

		$add_html = apply_filters( 'frm_field_extra_html', $add_html, $field );
		$add_html = ' ' . implode( ' ', $add_html ) . '  ';

        if ( $echo ) {
            echo $add_html;
        }

        return $add_html;
    }

	private static function add_input_classes( $field, array &$class ) {
        if ( isset($field['input_class']) && ! empty($field['input_class']) ) {
            $class[] = $field['input_class'];
        }

        if ( $field['type'] == 'hidden' || $field['type'] == 'user_id' ) {
            return;
        }

        global $frm_vars;
		if ( is_admin() && ! FrmAppHelper::is_preview_page() && ! in_array( $field['type'], array( 'scale', 'radio', 'checkbox', 'data' ) ) ) {
            $class[] = 'dyn_default_value';
        }

        if ( isset($field['size']) && $field['size'] > 0 ) {
            $class[] = 'auto_width';
        }
    }

	private static function add_html_size( $field, array &$add_html ) {
		if ( ! isset( $field['size'] ) || $field['size'] <= 0 || in_array( $field['type'], array( 'select', 'data', 'time', 'hidden' ) ) ) {
            return;
        }

        if ( FrmAppHelper::is_admin_page('formidable' ) ) {
            return;
        }

        if ( is_numeric($field['size']) ) {
            $field['size'] .= 'px';
        }

        $important = apply_filters('frm_use_important_width', 1, $field);
        // Note: This inline styling must stay since we cannot realistically set a class for every possible field size
        $add_html['style'] = 'style="width:'. esc_attr( $field['size'] ) . ( $important ? ' !important' : '' ) .'"';

        self::add_html_cols($field, $add_html);
    }

	private static function add_html_cols( $field, array &$add_html ) {
		if ( ! in_array( $field['type'], array( 'textarea', 'rte' ) ) ) {
            return;
        }

        // convert to cols for textareas
        $calc = array(
            ''      => 9,
            'px'    => 9,
            'rem'   => 0.444,
            'em'    => 0.544,
        );

        // include "col" for valid html
        $unit = trim(preg_replace('/[0-9]+/', '', $field['size']));

        if ( ! isset( $calc[ $unit ] ) ) {
            return;
        }

        $size = (float) str_replace( $unit, '', $field['size'] ) / $calc[ $unit ];

		$add_html['cols'] = 'cols="' . absint( $size ) . '"';
    }

	private static function add_html_length( $field, array &$add_html ) {
        // check for max setting and if this field accepts maxlength
		if ( FrmField::is_option_empty( $field, 'max' ) || in_array( $field['type'], array( 'textarea', 'rte', 'hidden' ) ) ) {
            return;
        }

        if ( FrmAppHelper::is_admin_page('formidable' ) ) {
            // don't load on form builder page
            return;
        }

		$add_html['maxlength'] = 'maxlength="' . esc_attr( $field['max'] ) . '"';
    }

	private static function add_html_placeholder( $field, array &$add_html, array &$class ) {
		if ( empty( $field['default_value'] ) || FrmAppHelper::is_admin_page( 'formidable' ) ) {
			return;
		}

        if ( ! FrmField::is_option_true( $field, 'clear_on_focus' ) ) {
			if ( is_array( $field['default_value'] ) ) {
				$field['default_value'] = json_encode( $field['default_value'] );
			}
			$add_html['data-frmval'] = 'data-frmval="' . esc_attr( $field['default_value'] ) . '"';
            return;
        }

        $frm_settings = FrmAppHelper::get_settings();

		if ( $frm_settings->use_html && ! in_array( $field['type'], array( 'select', 'radio', 'checkbox', 'hidden' ) ) ) {
            // use HMTL5 placeholder with js fallback
            $add_html['placeholder'] = 'placeholder="'. esc_attr($field['default_value']) .'"';
            wp_enqueue_script('jquery-placeholder');
        } else if ( ! $frm_settings->use_html ) {
			$val = str_replace( array( "\r\n", "\n" ), '\r', addslashes( str_replace( '&#039;', "'", esc_attr( $field['default_value'] ) ) ) );
            $add_html['data-frmval'] = 'data-frmval="'. esc_attr($val) .'"';
            $class[] = 'frm_toggle_default';

            if ( $field['value'] == $field['default_value'] ) {
                $class[] = 'frm_default';
            }
        }
    }

	private static function add_validation_messages( $field, array &$add_html ) {
		if ( FrmField::is_required( $field ) ) {
			$required_message = FrmFieldsHelper::get_error_msg( $field, 'blank' );
			$add_html['data-reqmsg'] = 'data-reqmsg="' . esc_attr( $required_message ) . '"';
		}

		if ( ! FrmField::is_option_empty( $field, 'invalid' ) ) {
			$invalid_message = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
			$add_html['data-invmsg'] = 'data-invmsg="' . esc_attr( $invalid_message ) . '"';
		}

		if ( $field['type'] == 'tel' ) {
			$format = FrmEntryValidate::phone_format( $field );
			$format = substr( $format, 2, -2 );
			$key = 'pattern';
			$add_html[ $key ] = $key . '="' . esc_attr( $format ) . '"';
		}
	}

    private static function add_shortcodes_to_html( $field, array &$add_html ) {
        if ( FrmField::is_option_empty( $field, 'shortcodes' ) ) {
            return;
        }

        foreach ( $field['shortcodes'] as $k => $v ) {
            if ( 'opt' === $k ) {
                continue;
            }

            if ( is_numeric($k) && strpos($v, '=') ) {
                $add_html[] = $v;
            } else if ( ! empty( $k ) && isset( $add_html[ $k ] ) ) {
                $add_html[ $k ] = str_replace( $k .'="', $k .'="'. $v, $add_html[ $k ] );
            } else {
				$add_html[ $k ] = $k . '="' . esc_attr( $v ) . '"';
            }

            unset($k, $v);
        }
    }

    public static function check_value( $opt, $opt_key, $field ) {
        if ( is_array( $opt ) ) {
            if ( FrmField::is_option_true( $field, 'separate_value' ) ) {
                $opt = isset( $opt['value'] ) ? $opt['value'] : ( isset( $opt['label'] ) ? $opt['label'] : reset( $opt ) );
            } else {
                $opt = isset( $opt['label'] ) ? $opt['label'] : reset( $opt );
            }
        }
        return $opt;
    }

	public static function check_label( $opt ) {
        if ( is_array($opt) ) {
            $opt = (isset($opt['label']) ? $opt['label'] : reset($opt));
        }

        return $opt;
    }
}
