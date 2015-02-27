<?php

if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

if(class_exists('FrmEntriesHelper'))
    return;

class FrmEntriesHelper{

    public static function setup_new_vars($fields, $form='', $reset=false){
        global $frm_settings, $frm_vars;
        $values = array();
        foreach (array('name' => '', 'description' => '', 'item_key' => '') as $var => $default)
            $values[$var] = FrmAppHelper::get_post_param($var, $default);
        
        $values['fields'] = array();
        if (empty($fields)){
            return apply_filters('frm_setup_new_entry', $values);
        }
        
        foreach ( (array) $fields as $field ) {
            $field->field_options = maybe_unserialize($field->field_options);
            $default = $field->default_value;
            $posted_val = false;
            
            if ( $reset ) {
                $new_value = $default;
            } else if ( $_POST && isset($_POST['item_meta'][$field->id]) && $_POST['item_meta'][$field->id] != '' ) {
                $new_value = stripslashes_deep($_POST['item_meta'][$field->id]);
                $posted_val = true;
            } else if ( isset($field->field_options['clear_on_focus']) && $field->field_options['clear_on_focus'] ) {
                $new_value = '';
            } else {
                $new_value = $default;
            }
            
            $is_default = ($new_value == $default) ? true : false;
              
    		//If checkbox, multi-select dropdown, or checkbox data from entries field, set return array to true
    		if ( $field && ( ( $field->type == 'data' && $field->field_options['data_type'] == 'checkbox' ) || $field->type == 'checkbox' || ( $field->type == 'select' && isset($field->field_options['multiple']) && $field->field_options['multiple'] == 1 ) ) ) {
                $return_array = true;
    		} else {
    		    $return_array = false;
    		}
            
            $field->default_value = apply_filters('frm_get_default_value', $field->default_value, $field, true, $return_array);
                
            if ( !is_array($new_value) ) {
                if ( $is_default ) {
                    $new_value = $field->default_value;
                } else if ( !$posted_val ) {
                    $new_value = apply_filters('frm_filter_default_value', $new_value, $field);
                }
                
                $new_value = str_replace('"', '&quot;', $new_value);
            }
            
            unset($is_default);
            unset($posted_val);
                
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
                    'form_id' => $field->form_id
                );

                $opt_defaults = FrmFieldsHelper::get_default_field_opts($field_array['type'], $field, true);
                $opt_defaults['required_indicator'] = '';
                
                foreach ($opt_defaults as $opt => $default_opt){
                    $field_array[$opt] = (isset($field->field_options[$opt]) && $field->field_options[$opt] != '') ? $field->field_options[$opt] : $default_opt;
                    unset($opt);
                    unset($default_opt);
                }
                  
                unset($opt_defaults);
                
                if ($field_array['size'] == '')
                    $field_array['size'] = isset($frm_vars['sidebar_width']) ? $frm_vars['sidebar_width'] : '';
            
                
                if ($field_array['custom_html'] == '')
                    $field_array['custom_html'] = FrmFieldsHelper::get_default_html($field->type);
                    
                $field_array = apply_filters('frm_setup_new_fields_vars', $field_array, $field);
                
                foreach((array)$field->field_options as $k => $v){
                    if(!isset($field_array[$k]))
                        $field_array[$k] = $v;
                    unset($k);
                    unset($v);
                }
                
                $values['fields'][] = $field_array;
             
                if (!$form or !isset($form->id)){
                    $frm_form = new FrmForm();
                    $form = $frm_form->getOne($field->form_id);
                }
        }

            $form->options = maybe_unserialize($form->options);
            if (is_array($form->options)){
                foreach ($form->options as $opt => $value)
                    $values[$opt] = FrmAppHelper::get_post_param($opt, $value);
            }
            
            if (!isset($values['custom_style']))
                $values['custom_style'] = ($frm_settings->load_style != 'none');
                
            if (!isset($values['email_to']))
                $values['email_to'] = '';

            if (!isset($values['submit_value']))
                $values['submit_value'] = $frm_settings->submit_value;

            if (!isset($values['success_msg']))
                $values['success_msg'] = $frm_settings->success_msg;

            if (!isset($values['akismet']))
                $values['akismet'] = '';

            if (!isset($values['before_html']))
                $values['before_html'] = FrmFormsHelper::get_default_html('before');

            if (!isset($values['after_html']))
                $values['after_html'] = FrmFormsHelper::get_default_html('after');
                
            if (!isset($values['submit_html']))
                $values['submit_html'] = FrmFormsHelper::get_default_html('submit');
        
        return apply_filters('frm_setup_new_entry', $values);
    }
    
    public static function setup_edit_vars($values, $record){
        //$values['description'] = maybe_unserialize( $record->description );
        $values['item_key'] = isset($_POST['item_key']) ? $_POST['item_key'] : $record->item_key;
        $values['form_id'] = $record->form_id;
        $values['is_draft'] = $record->is_draft;
        return apply_filters('frm_setup_edit_entry_vars', $values, $record);
    }
    
    public static function replace_default_message($message, $atts) {
        if ( strpos($message, '[default-message') === false && 
            strpos($message, '[default_message') === false && 
            !empty($message) ) {
            return $message;
        }
        
        if ( empty($message) ) {
            $message = '[default-message]';
        }
        
        preg_match_all("/\[(default-message|default_message)\b(.*?)(?:(\/))?\]/s", $message, $shortcodes, PREG_PATTERN_ORDER);
        
        foreach ( $shortcodes[0] as $short_key => $tag ) {
            $add_atts = shortcode_parse_atts( $shortcodes[2][$short_key] );
            if ( $add_atts ){
                $this_atts = array_merge($atts, $add_atts);
            } else {
                $this_atts = $atts;
            }
            
            $default = FrmEntriesController::show_entry_shortcode($this_atts);
            
            // Add the default message
            $message = str_replace($shortcodes[0][$short_key], $default, $message);
        }

        return $message;
    }
    


    public static function entries_dropdown( $form_id, $field_name, $field_value='', $blank=true, $blank_label='', $onchange=false ){
        _deprecated_function( __FUNCTION__, '1.07.09');
    }
    
    public static function enqueue_scripts($params){
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
}
