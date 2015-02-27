<?php
if(!defined('ABSPATH')) die(__('You are not allowed to call this page directly.', 'formidable'));

if(class_exists('FrmField'))
    return;

class FrmField{

    function create( $values, $return=true ){
        global $wpdb;

        $new_values = array();
        $key = isset($values['field_key']) ? $values['field_key'] : $values['name'];
        $new_values['field_key'] = FrmAppHelper::get_unique_key($key, $wpdb->prefix .'frm_fields', 'field_key');

        foreach ( array('name', 'description', 'type', 'default_value') as $col ) {
            $new_values[$col] = $values[$col];
        }
        
        $new_values['options'] = $values['options'];

        $new_values['field_order'] = isset($values['field_order']) ? (int)$values['field_order'] : NULL;
        $new_values['required'] = isset($values['required']) ? (int)$values['required'] : 0;
        $new_values['form_id'] = isset($values['form_id']) ? (int)$values['form_id'] : NULL;
        $new_values['field_options'] = $values['field_options'];
        $new_values['created_at'] = current_time('mysql', 1);
        if(isset($values['id'])){
            global $frm_duplicate_ids;
            $frm_duplicate_ids[$values['field_key']] = $new_values['field_key'];
            $new_values = apply_filters('frm_duplicated_field', $new_values);
        }
        
        foreach($new_values as $k => $v){
            if(is_array($v))
                $new_values[$k] = serialize($v);
            unset($k);
            unset($v);
        }
        
        //if(isset($values['id']) and is_numeric($values['id']))
        //    $new_values['id'] = $values['id'];
        
        $query_results = $wpdb->insert( $wpdb->prefix .'frm_fields', $new_values );
        if($return){
            if($query_results){
                delete_transient('frm_all_form_fields_'. $new_values['form_id']);
                $new_id = $wpdb->insert_id;
                if(isset($values['id']))
                    $frm_duplicate_ids[$values['id']] = $new_id;
                return $new_id;
            }else{
                return false;
            }
        }
    }

    function duplicate($old_form_id, $form_id, $copy_keys=false, $blog_id=false){
        global $frm_duplicate_ids, $wpdb;
        $fields = $this->getAll(array('fi.form_id' => $old_form_id), 'field_order', '', $blog_id);
        foreach ((array)$fields as $field){
            $values = array();
            $new_key = ($copy_keys) ? $field->field_key : '';
            if($copy_keys and substr($field->field_key, -1) == 2)
                $new_key = rtrim($new_key, 2);
            $values['field_key'] = FrmAppHelper::get_unique_key($new_key, $wpdb->prefix .'frm_fields', 'field_key');
            $values['options'] = maybe_serialize($field->options);
            $values['form_id'] = $form_id;
            foreach (array('name', 'description', 'type', 'default_value', 'field_order', 'required', 'field_options') as $col)
                $values[$col] = $field->{$col};
            $values = apply_filters('frm_duplicated_field', $values);
            $new_id = $this->create($values);
            $frm_duplicate_ids[$field->id] = $new_id;
            $frm_duplicate_ids[$field->field_key] = $new_id;
            unset($field);
        }
    }

    function update( $id, $values ){
        global $wpdb;
        
        if (isset($values['field_key']))
            $values['field_key'] = FrmAppHelper::get_unique_key($values['field_key'], $wpdb->prefix .'frm_fields', 'field_key', $id);

        if ( isset($values['required']) ) {
            $values['required'] = (int) $values['required'];
        }
        
        if (isset($values['default_value']) and is_array($values['default_value']))
            $values['default_value'] = serialize($values['default_value']);
        
        if (isset($values['field_options']) and is_array($values['field_options']))
            $values['field_options'] = serialize($values['field_options']);
            
        if (isset($values['options']) and is_array($values['options']))
            $values['options'] = serialize($values['options']);
        
        $query_results = $wpdb->update( $wpdb->prefix .'frm_fields', $values, array( 'id' => $id ) );
        
        if(isset($values['form_id'])){
            $form_id = $values['form_id'];
        }else{
            $field = $this->getOne($id);
            if($field)
                $form_id = $field->form_id;
            unset($field);
        }
        unset($values);
        
        if($query_results){
            wp_cache_delete( $id, 'frm_field' );
            delete_transient('frm_all_form_fields_'. $form_id);
        }
        
        return $query_results;
    }

    function destroy( $id ){
      global $wpdb;

      do_action('frm_before_destroy_field', $id);
      do_action('frm_before_destroy_field_'. $id);
      
      wp_cache_delete( $id, 'frm_field' );
      $field = $this->getOne($id);
      if ( !$field ) {
          return false;
      }
      
      delete_transient('frm_all_form_fields_'. $field->form_id);
      
      $wpdb->query("DELETE FROM {$wpdb->prefix}frm_item_metas WHERE field_id='$id'");
      return $wpdb->query("DELETE FROM {$wpdb->prefix}frm_fields WHERE id='$id'");
    }

    function getOne( $id ){
        global $wpdb;
        $results = wp_cache_get( $id, 'frm_field' );
        if(!$results){
          
            $where = (is_numeric($id)) ? 'id=%d' : 'field_key=%s';
            $results = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}frm_fields WHERE $where", $id));
            
            if($results){
                wp_cache_set( $results->id, $results, 'frm_field' );
                wp_cache_set( $results->field_key, $results, 'frm_field' );
            }
        }
        
        if($results){
            $results->field_options = maybe_unserialize($results->field_options);
            if(isset($results->field_options['format']) and !empty($results->field_options['format']))
                $results->field_options['format'] = addslashes($results->field_options['format']);
            $results->options = maybe_unserialize($results->options);
            $results->default_value = maybe_unserialize($results->default_value);
        }
        
        return stripslashes_deep($results);
    }

    function getAll($where=array(), $order_by = '', $limit = '', $blog_id=false){
        global $wpdb;
        
        if ($blog_id and is_multisite()){
            global $wpmuBaseTablePrefix;
            if($wpmuBaseTablePrefix)
                $prefix = "{$wpmuBaseTablePrefix}{$blog_id}_";
            else
                $prefix = $wpdb->get_blog_prefix( $blog_id );
            
            $table_name = "{$prefix}frm_fields"; 
            $form_table_name = "{$prefix}frm_forms";
        }else{
            $table_name = $wpdb->prefix .'frm_fields';
            $form_table_name = $wpdb->prefix .'frm_forms';
        }
        
        if(!empty($order_by) and !preg_match("/ORDER BY/", $order_by))
            $order_by = " ORDER BY {$order_by}";

        if(is_numeric($limit))
            $limit = " LIMIT {$limit}";
        
        $query = "SELECT fi.*, fr.name as form_name  FROM {$table_name} fi LEFT OUTER JOIN {$form_table_name} fr ON fi.form_id=fr.id";
        $old_where = $where;         
        if(is_array($where)){  
            global $frmdb;     
            extract($frmdb->get_where_clause_and_values( $where ));

            $query .= "{$where}{$order_by}{$limit}";
            $query = $wpdb->prepare($query, $values);
        }else{
            $query .= FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        }
        
        if ($limit == ' LIMIT 1' or $limit == 1){
            $results = $wpdb->get_row($query);
        }else{
            $ak = is_array($old_where) ? array_keys($old_where) : $old_where; 
            if($order_by == ' ORDER BY field_order' and empty($limit) and empty($blog_id) and is_array($old_where) and count($old_where) == 1 and reset($ak) == 'fi.form_id'){
                $save_cache = true;
                $results = get_transient('frm_all_form_fields_'. reset($old_where));
                if($results and (is_array($results) or is_object($results)))
                    $cached = true;
            }
            unset($ak);
            if(!isset($cached))
                $results = $wpdb->get_results($query);
        }
        
        if($results and !isset($cached)){
            if(is_array($results)){
                foreach($results as $r_key => $result){
                    wp_cache_set($result->id, $result, 'frm_field');
                    wp_cache_set($result->field_key, $result, 'frm_field');
                    $results[$r_key]->field_options = maybe_unserialize($result->field_options);
                    if(isset($results[$r_key]->field_options['format']) and !empty($results[$r_key]->field_options['format']))
                        $results[$r_key]->field_options['format'] = addslashes($results[$r_key]->field_options['format']);
                    $results[$r_key]->options = maybe_unserialize($result->options);
                    $results[$r_key]->default_value = maybe_unserialize($result->default_value);
                    $form_id = $result->form_id;
                    
                    unset($r_key, $result);
                }
                if(isset($save_cache))
                    set_transient('frm_all_form_fields_'. $form_id, $results, 60*60*6);
                unset($form_id);
            }else{
                wp_cache_set($results->id, $results, 'frm_field');
                wp_cache_set($results->field_key, $results, 'frm_field');
                $results->field_options = maybe_unserialize($results->field_options);
                if(isset($results->field_options['format']) and !empty($results->field_options['format']))
                    $results->field_options['format'] = addslashes($results->field_options['format']);
                $results->options = maybe_unserialize($results->options);
                $results->default_value = maybe_unserialize($results->default_value);
            }
        }
        
        return stripslashes_deep($results);
    }

    function getIds($where = '', $order_by = '', $limit = ''){
        global $wpdb;
        
        if ( !empty($order_by) && !preg_match("/ORDER BY/", $order_by) ){
            $order_by = ' ORDER BY '. $order_by;
        }
        
        $query = "SELECT fi.id  FROM {$wpdb->prefix}frm_fields fi " .
                 "LEFT OUTER JOIN {$wpdb->prefix}frm_forms fr ON fi.form_id=fr.id" . 
                 FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        if ($limit == ' LIMIT 1' or $limit == 1)
            $results = $wpdb->get_var($query);
        else
            $results = $wpdb->get_col($query);
        return $results;
    }
}
