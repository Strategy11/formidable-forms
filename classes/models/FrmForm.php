<?php
if(!defined('ABSPATH')) die(__('You are not allowed to call this page directly.', 'formidable'));

if(class_exists('FrmForm'))
    return;

class FrmForm{

    function create( $values ) {
        global $wpdb, $frm_settings;

        $new_values = array(
            'form_key'      => FrmAppHelper::get_unique_key($values['form_key'], $wpdb->prefix .'frm_forms', 'form_key'),
            'name'          => $values['name'],
            'description'   => $values['description'],
            'status'        => isset($values['status']) ? $values['status'] : 'draft',
            'is_template'   => isset($values['is_template']) ? (int) $values['is_template'] : 0,
            'editable'      => isset($values['editable']) ? (int) $values['editable'] : 0,
            'default_template' => isset($values['default_template']) ? (int) $values['default_template'] : 0,
            'created_at'    => isset($values['created_at']) ? $values['created_at'] : current_time('mysql', 1),
        );
        
        $options = array();

        $defaults = FrmFormsHelper::get_default_opts();
        foreach ($defaults as $var => $default) {
            $options[$var] = isset($values['options'][$var]) ? $values['options'][$var] : $default;
            unset($var);
            unset($default);
        }

        $options['before_html'] = isset($values['options']['before_html']) ? $values['options']['before_html'] : FrmFormsHelper::get_default_html('before');
        $options['after_html'] = isset($values['options']['after_html']) ? $values['options']['after_html'] : FrmFormsHelper::get_default_html('after');
        $options['submit_html'] = isset($values['options']['submit_html']) ? $values['options']['submit_html'] : FrmFormsHelper::get_default_html('submit');

        $options = apply_filters('frm_form_options_before_update', $options, $values);
        $new_values['options'] = serialize($options);

        //if(isset($values['id']) && is_numeric($values['id']))
        //    $new_values['id'] = $values['id'];

        $query_results = $wpdb->insert( $wpdb->prefix .'frm_forms', $new_values );

        return $wpdb->insert_id;
    }
  
    function duplicate( $id, $template = false, $copy_keys = false, $blog_id = false ) {
        global $wpdb;

        $frm_form = new FrmForm();
        $values = $frm_form->getOne( $id, $blog_id );
        if ( !$values ) {
            return false;
        }
        
        $new_key = $copy_keys ? $values->form_key : '';
        
        $new_values = array(
            'form_key'      => FrmAppHelper::get_unique_key($new_key, $wpdb->prefix .'frm_forms', 'form_key'),
            'name'          => $values->name,
            'description'   => $values->description,
            'status'        => $template ? '' : 'draft',
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

        $query_results = $wpdb->insert( $wpdb->prefix .'frm_forms', $new_values );
    
        if ( $query_results ) {
            global $frm_field;
            $form_id = $wpdb->insert_id;
            $frm_field->duplicate($id, $form_id, $copy_keys, $blog_id);
            
            // update form settings after fields are created
            do_action('frm_after_duplicate_form', $form_id, $new_values);
            return $form_id;
        } else {
            return false;
        }
    }
    
    function after_duplicate($form_id, $values) {
        $new_opts = $values['options'] = maybe_unserialize($values['options']);
        
        if ( isset($new_opts['success_msg']) ) {
            $new_opts['success_msg'] = FrmFieldsHelper::switch_field_ids($new_opts['success_msg']);
        }
        
        $new_opts = apply_filters('frm_after_duplicate_form_values', $new_opts, $form_id);
        
        if ( $new_opts != $values['options'] ) {
            global $wpdb;
            $wpdb->update($wpdb->prefix .'frm_forms', array('options' => maybe_serialize($new_opts)), array('id' => $form_id));
        }
    }

    function update( $id, $values, $create_link = false ) {
        global $wpdb, $frm_field, $frm_settings;

        if ( $create_link || isset($values['options']) || isset($values['item_meta']) || isset($values['field_options']) ) {
            $values['status'] = 'published';
        }

        if ( isset($values['form_key']) ) {
            $values['form_key'] = FrmAppHelper::get_unique_key($values['form_key'], $wpdb->prefix .'frm_forms', 'form_key', $id);
        }

        $form_fields = array( 'form_key', 'name', 'description', 'status', 'prli_link_id' );

        $new_values = array();

        if (isset($values['options'])){
            $options = array();

            $defaults = FrmFormsHelper::get_default_opts();
            foreach ($defaults as $var => $default) {
                if ( $var == 'notification' && !defined('WP_IMPORTING')) {
                    $options[$var] = isset($values[$var]) ? $values[$var] : $default;
                } else {
                    $options[$var] = isset($values['options'][$var]) ? $values['options'][$var] : $default;
                }
            }
            
            $options['custom_style'] = isset($values['options']['custom_style']) ? $values['options']['custom_style'] : 0;
            $options['before_html'] = isset($values['options']['before_html']) ? $values['options']['before_html'] : FrmFormsHelper::get_default_html('before');
            $options['after_html'] = isset($values['options']['after_html']) ? $values['options']['after_html'] : FrmFormsHelper::get_default_html('after');
            $options['submit_html'] = (isset($values['options']['submit_html']) && $values['options']['submit_html'] != '') ? $values['options']['submit_html'] : FrmFormsHelper::get_default_html('submit');

            $options = apply_filters('frm_form_options_before_update', $options, $values);
            $new_values['options'] = serialize($options);
        }

        foreach ( $values as $value_key => $value ) {
            if ( in_array($value_key, $form_fields) ) {
                $new_values[$value_key] = $value;
            }
        }

        if ( !empty($new_values) ) {
            $query_results = $wpdb->update( $wpdb->prefix .'frm_forms', $new_values, array( 'id' => $id ) );
            if ( $query_results ) {
                wp_cache_delete( $id, 'frm_form');
            }
        } else {
            $query_results = true;
        }

        $all_fields = $frm_field->getAll(array('fi.form_id' => $id), 'field_order');
        if ( $all_fields && (isset($values['options']) || isset($values['item_meta']) || isset($values['field_options'])) ) {
            if ( !isset($values['item_meta']) ) {
                $values['item_meta'] = array();
            }
            $existing_keys = array_keys($values['item_meta']);

            foreach ( $all_fields as $fid ) {
                if ( !in_array($fid->id, $existing_keys) && ( isset($values['frm_fields_submitted']) && in_array($fid->id, $values['frm_fields_submitted']) ) || isset($values['options']) ) {
                    $values['item_meta'][$fid->id] = '';
                }
            }
        
            foreach ( $values['item_meta'] as $field_id => $default_value ) { 
                $field = $frm_field->getOne($field_id);
                if (!$field) continue;
                $field_options = maybe_unserialize($field->field_options);

                if ( isset($values['options']) || isset($values['field_options']['custom_html_'. $field_id]) ) {
                    //updating the settings page
                    if(isset($values['field_options']['custom_html_'.$field_id])){
                        $field_options['custom_html'] = isset($values['field_options']['custom_html_'.$field_id]) ? $values['field_options']['custom_html_'.$field_id] : (isset($field_options['custom_html']) ? $field_options['custom_html'] : FrmFieldsHelper::get_default_html($field->type));
                        $field_options = apply_filters('frm_update_form_field_options', $field_options, $field, $values);
                        $frm_field->update($field_id, array('field_options' => $field_options));
                    }else if($field->type == 'hidden' || $field->type == 'user_id'){
                        $prev_opts = $field_options;
                        $field_options = apply_filters('frm_update_form_field_options', $field_options, $field, $values);
                        if($prev_opts != $field_options)
                            $frm_field->update($field_id, array('field_options' => $field_options));
                        unset($prev_opts);
                    }
                }
                
                if ( (!isset($values['options']) && !isset($values['field_options']['custom_html_'. $field_id])) || defined('WP_IMPORTING') ) {
                    //updating the form
                    foreach ( array('size', 'max', 'label', 'invalid', 'blank', 'classes') as $opt ) {
                        $field_options[$opt] = isset($values['field_options'][$opt.'_'.$field_id]) ? trim($values['field_options'][$opt.'_'.$field_id]) : '';
                    }

                    $field_options['required_indicator'] = isset($values['field_options']['required_indicator_'. $field_id]) ? trim($values['field_options']['required_indicator_'. $field_id]) : '*'; 
                    $field_options['separate_value'] = isset($values['field_options']['separate_value_'.$field_id]) ? trim($values['field_options']['separate_value_'.$field_id]) : 0; 

                    $field_options = apply_filters('frm_update_field_options', $field_options, $field, $values);
                    $default_value = maybe_serialize($values['item_meta'][$field_id]);
                    $field_key = (isset($values['field_options']['field_key_'.$field_id])) ? $values['field_options']['field_key_'.$field_id] : $field->field_key;
                    $required = (isset($values['field_options']['required_'.$field_id])) ? $values['field_options']['required_'.$field_id] : false;
                    $field_type = (isset($values['field_options']['type_'.$field_id])) ? $values['field_options']['type_'.$field_id] : $field->type;
                    $field_description = (isset($values['field_options']['description_'.$field_id])) ? $values['field_options']['description_'.$field_id] : $field->description;

                    $frm_field->update($field_id, array('field_key' => $field_key, 'type' => $field_type, 'default_value' => $default_value, 'field_options' => $field_options, 'description' => $field_description, 'required' => $required));
                }
            }
        }
        
        do_action('frm_update_form', $id, $values);
        do_action('frm_update_form_'. $id, $values);

        return $query_results;
    }

    function destroy( $id ){
        global $wpdb, $frm_entry;

        $form = $this->getOne($id);
        if ( !$form || $form->default_template ) {
            return false;
        }

        // Disconnect the items from this form
        $entries = $frm_entry->getAll(array('it.form_id' => $id));
        foreach ( $entries as $item ) {
            $frm_entry->destroy($item->id);
            unset($item);
        }
        // Disconnect the fields from this form
        $query_results = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}frm_fields WHERE form_id=%d", $id));

        $query_results = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}frm_forms WHERE id=%d", $id));
        if ( $query_results ) {
            do_action('frm_destroy_form', $id);
            do_action('frm_destroy_form_'. $id);
        }
        return $query_results;
    }
  
    function getName( $id ) {
        global $wpdb;
        $cache = wp_cache_get($id, 'frm_form');
        if ( $cache ) {
            return stripslashes($cache->name);
        }
        $query = "SELECT name FROM {$wpdb->prefix}frm_forms WHERE ";
        $query .= (is_numeric($id)) ? "id=%d" : "form_key=%s";
        $query = $wpdb->prepare($query, $id);
      
        $r = $wpdb->get_var($query);
        return stripslashes($r);
    }
  
    function getIdByKey( $key ){
        global $wpdb;
        $query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}frm_forms WHERE form_key=%s LIMIT 1", $key);
        return $wpdb->get_var($query);
    }

    function getOne( $id, $blog_id=false ){
        global $wpdb, $frmdb;
      
        if ( $blog_id && is_multisite() ) {
            global $wpmuBaseTablePrefix;
            $prefix = $wpmuBaseTablePrefix ? "{$wpmuBaseTablePrefix}{$blog_id}_" : $wpdb->get_blog_prefix( $blog_id );
              
            $table_name = "{$prefix}frm_forms";
        } else {
            $table_name = $wpdb->prefix .'frm_forms';
            $cache = wp_cache_get($id, 'frm_form');
            if ( $cache ) {
                if ( isset($cache->options) ) {
                    $cache->options = maybe_unserialize($cache->options);
                }
              
                return stripslashes_deep($cache);
            }
        }
      
        $where = $wpdb->prepare( is_numeric($id) ? 'id=%d' : 'form_key=%s', $id );
         
        $results = $wpdb->get_row("SELECT * FROM $table_name WHERE $where");
      
        if ( isset($results->options) ) {
            wp_cache_set($results->id, $results, 'frm_form');
            $results->options = maybe_unserialize($results->options);
        }
        return stripslashes_deep($results);
    }

    function getAll( $where = array(), $order_by = '', $limit = '' ){
        global $wpdb, $frmdb;
        
        if(is_numeric($limit))
            $limit = " LIMIT {$limit}";
            
        $query = 'SELECT * FROM ' . $wpdb->prefix .'frm_forms' . FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
            
        if ($limit == ' LIMIT 1' || $limit == 1){
            if(is_array($where))
                $results = $frmdb->get_one_record($wpdb->prefix .'frm_forms', $where, '*', $order_by);
            else
                $results = $wpdb->get_row($query);
                
            if($results){
                wp_cache_set($results->id, $results, 'frm_form');
                $results->options = maybe_unserialize($results->options);
            }
        }else{
            if ( is_array($where) && !empty($where) ) {
                $results = $frmdb->get_records($wpdb->prefix .'frm_forms', $where, $order_by, $limit);
            } else {
                $results = $wpdb->get_results($query);
            }
            
            if($results){
                foreach($results as $result){
                    wp_cache_set($result->id, $result, 'frm_form');
                    $result->options = maybe_unserialize($result->options);
                }
            }
        }
      
        return stripslashes_deep($results);
    }

    function validate( $values ){
        $errors = array();

      /*if( $values['form_key'] == null || $values['form_key'] == '' ){
          if( $values['name'] == null || $values['name'] == '' )
              $errors[] = "Key can't be blank";
          else 
             $_POST['form_key'] = $values['name'];
      }*/
      
        return apply_filters('frm_validate_form', $errors, $values);
    }

}
