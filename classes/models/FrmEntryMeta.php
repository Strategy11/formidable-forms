<?php
if(!defined('ABSPATH')) die(__('You are not allowed to call this page directly.', 'formidable'));

if(class_exists('FrmEntryMeta'))
    return;

class FrmEntryMeta{

    function add_entry_meta($entry_id, $field_id, $meta_key = null, $meta_value) {
        global $wpdb;
        
        if ( (is_array($meta_value) && empty($meta_value) ) || ( !is_array($meta_value) && trim($meta_value) == '' ) ) {
            // don't save blank fields
            return;
        }
        
        $new_values = array(
            'meta_value'    => is_array($meta_value) ? serialize(array_filter($meta_value)) : trim($meta_value),
            'item_id'       => $entry_id,
            'field_id'      => $field_id,
            'created_at'    => current_time('mysql', 1),
        );
        
        $new_values = apply_filters('frm_add_entry_meta', $new_values);

        $query_results = $wpdb->insert( $wpdb->prefix .'frm_item_metas', $new_values );
        
        $id = $query_results ? $wpdb->insert_id : 0;
        
        return $id;
    }

    function update_entry_meta($entry_id, $field_id, $meta_key = null, $meta_value){
        if ( ! $field_id ) {
            return false;
        }
        
        global $wpdb;
        
        $values = $where_values = array( 'item_id' => $entry_id, 'field_id' => $field_id );
        $values['meta_value'] = $meta_value;
        $values = apply_filters('frm_update_entry_meta', $values);
		if ( is_array($meta_value) ) {
			$meta_value = array_filter($meta_value);
		}
        $meta_value = maybe_serialize($values['meta_value']);
        
        return $wpdb->update( $wpdb->prefix .'frm_item_metas', array( 'meta_value' => $meta_value ), $where_values );
    }
  
    function update_entry_metas($entry_id, $values){
        global $frm_field, $wpdb;
        
        $prev_values = $wpdb->get_col($wpdb->prepare("SELECT field_id FROM {$wpdb->prefix}frm_item_metas WHERE item_id=%d AND field_id != %d", $entry_id, 0));
        
        foreach ( $values as $field_id => $meta_value ) {
            
            if ( $prev_values && in_array($field_id, $prev_values) ) {
                if ( (is_array($meta_value) && empty($meta_value) ) || ( !is_array($meta_value) && trim($meta_value) == '' ) ) {
                    // remove blank fields
                    unset($values[$field_id]);
                } else {
                    // if value exists, then update it
                    $this->update_entry_meta($entry_id, $field_id, '', $values[$field_id]);
                }
            } else {
                // if value does not exist, then create it
                $this->add_entry_meta($entry_id, $field_id, '', $values[$field_id]);
            }
            
        }
        
        if ( empty($prev_values) ) {
            return;
        }
        
        $prev_values = array_diff($prev_values, array_keys($values));
        
        if ( empty($prev_values) ) {
            return;
        }
        
        // Delete any leftovers
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}frm_item_metas WHERE item_id=%d AND field_id in", $entry_id) ."  (". implode(',', $prev_values) .")");
    }
  
    function duplicate_entry_metas($old_id, $new_id){
        $metas = $this->get_entry_meta_info($old_id);
        foreach ( $metas as $meta ) {
            $this->add_entry_meta($new_id, $meta->field_id, null, $meta->meta_value);
            unset($meta);
        }
    }

    function delete_entry_meta($entry_id, $field_id){
        global $wpdb;
        return $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}frm_item_metas WHERE field_id=%d AND item_id=%d", $field_id, $entry_id));
    }
  
    function delete_entry_metas($entry_id, $where=''){
        _deprecated_function( __FUNCTION__, '1.07.05', '$frm_entry_meta->delete_entry_meta()' );
    }
  
    function get_entry_meta_by_field($entry_id, $field_id, $return_var=true){
        global $wpdb;
      
        $entry_id = (int)$entry_id;
      
        $cached = wp_cache_get( $entry_id, 'frm_entry' );
        if($cached and isset($cached->metas) and isset($cached->metas[$field_id])){
            $result = $cached->metas[$field_id];
            return stripslashes_deep($result);
        }
            
        if (is_numeric($field_id))
            $query = $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE field_id=%d and item_id=%d", $field_id, $entry_id);
        else
            $query = $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}frm_item_metas it LEFT OUTER JOIN {$wpdb->prefix}frm_fields fi ON it.field_id=fi.id WHERE fi.field_key=%s and item_id=%d", $field_id, $entry_id);
          
        if($return_var){
            $result = maybe_unserialize($wpdb->get_var("{$query} LIMIT 1"));
            if($cached){
                if(!isset($cached->metas))
                    $cached->metas = array();
                $cached->metas[$field_id] = $result;
                wp_cache_set($entry_id, $cached, 'frm_entry');
            }
            $result = stripslashes_deep($result);
        }else{
            $result = $wpdb->get_col($query, 0);
        }
          
        return $result;
    }
  
    function get_entry_meta($entry_id, $field_id, $return_var=true){
        global $wpdb;
      
        $entry = wp_cache_get($entry_id, 'frm_entry');
        if($return_var and $entry and isset($entry->metas) and isset($entry->metas[$field_id])){
            $var = $entry->metas[$field_id];
            return stripslashes_deep($var);
        }
        
        $query = $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE field_id=%d and item_id=%d", $field_id, $entry_id);

        if($return_var){
            $var = $wpdb->get_var("{$query} LIMIT 1");
            if($var)
              $var = stripslashes_deep(maybe_unserialize($var));
        }else{
            $var = $wpdb->get_col($query, 0);
        }
        return $var;
    }

    function get_entry_metas($entry_id){
        global $wpdb;
        return $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id=%d", $entry_id));
    }
  
    function get_entry_metas_for_field($field_id, $order='', $limit='', $args=array()){
        global $wpdb;
      
        $defaults = array('value' => false, 'unique' => false, 'stripslashes' => true, 'is_draft' => false);
        extract(wp_parse_args( $args, $defaults ));
      
        $query = "SELECT ";
        $query .= ($unique) ? "DISTINCT(em.meta_value)" : "em.meta_value";
        $query .= " FROM {$wpdb->prefix}frm_item_metas em ";
        
        if(!$is_draft)
            $query .= " INNER JOIN {$wpdb->prefix}frm_items e ON (e.id=em.item_id) ";
        $query .= (is_numeric($field_id)) ? "WHERE em.field_id='{$field_id}'" : "LEFT JOIN {$wpdb->prefix}frm_fields fi ON (em.field_id = fi.id) WHERE fi.field_key='{$field_id}'";
        
        if(!$is_draft)
            $query .= " AND e.is_draft=0";
        
        if($value)
            $query .= " AND meta_value='$value'";
        $query .= "{$order}{$limit}";

        $values = $wpdb->get_col($query);
        if($stripslashes){
            foreach($values as $k => $v){
                $values[$k] = maybe_unserialize($v);
                unset($k);
                unset($v);
            }
            $values = stripslashes_deep($values);
        }

        return $values;
    }
  
    function get_entry_meta_info($entry_id){
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}frm_item_metas WHERE item_id=%d", $entry_id));
    }
    
    function getAll($where = '', $order_by = '', $limit = '', $stripslashes = false){
        global $wpdb, $frm_field;
        $query = "SELECT it.*, fi.type as field_type, fi.field_key as field_key, 
                fi.required as required, fi.form_id as field_form_id, fi.name as field_name, fi.options as fi_options 
                FROM {$wpdb->prefix}frm_item_metas it LEFT OUTER JOIN {$wpdb->prefix}frm_fields fi ON it.field_id=fi.id" . 
                FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;

        $results = ($limit == ' LIMIT 1') ? $wpdb->get_row($query) : $wpdb->get_results($query);
    
        if($results and $stripslashes){
            foreach($results as $k => $result){
                $results[$k]->meta_value = stripslashes_deep(maybe_unserialize($result->meta_value));
                unset($k);
                unset($result);
            }
        }
    
        return $results;     
    }
  
    function getEntryIds($where = '', $order_by = '', $limit = '', $unique=true, $drafts=false){
        global $wpdb;
        $query = "SELECT ";
        $query .= ($unique) ? "DISTINCT(it.item_id)" : "it.item_id";
        $query .= " FROM {$wpdb->prefix}frm_item_metas it LEFT OUTER JOIN {$wpdb->prefix}frm_fields fi ON it.field_id=fi.id ";
    
        if ( !$drafts ) {
            $query .= "INNER JOIN {$wpdb->prefix}frm_items e ON (e.id=it.item_id) ";
            if ( is_array($where) ) {
                $where['e.is_draft'] = 0;
            } else {
                if ( strpos($where, ' GROUP BY ') ) {
                    // don't inject WHERE filtering after GROUP BY
                    $parts = explode(' GROUP BY ', $where);
                    $where = $parts[0];
                    $where .= ' AND e.is_draft=0';
                    $where .= ' GROUP BY '. $parts[1];
                } else {
                    $where .= ' AND e.is_draft=0';
                }
            }
        }

        $query .= FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        if ($limit == ' LIMIT 1')
            $results = $wpdb->get_var($query);
        else    
            $results = $wpdb->get_col($query);

        return $results;    
    }
  
    function search_entry_metas($search, $field_id='', $operator){
        global $wpdb;
        if (is_array($search)){
            $where = '';
            foreach ($search as $field => $value){
                if ($field == 'year' and $value > 0)
                    $where .= " meta_value {$operator} '%{$value}' and";
                if ($field == 'month' and $value > 0)
                    $where .= " meta_value {$operator} '{$value}%' and";
                if ($field == 'day' and $value > 0)
                    $where .= " meta_value {$operator} '%/{$value}/%' and";      
            }
            $where .= " field_id='{$field_id}'";
            $query = "SELECT DISTINCT item_id FROM {$wpdb->prefix}frm_item_metas". FrmAppHelper::prepend_and_or_where(' WHERE ', $where);
        }else{
            if ($operator == 'LIKE')
                $search = "%{$search}%";
            $query = $wpdb->prepare("SELECT DISTINCT item_id FROM {$wpdb->prefix}frm_item_metas WHERE meta_value {$operator} %s and field_id = %d", $search, $field_id);
        }
        return $wpdb->get_col($query, 0);
    }

}
