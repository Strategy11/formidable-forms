<?php
if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');;

if(class_exists('FrmField'))
    return;

class FrmField{
    static $use_cache = true;

    public static function create( $values, $return=true ){
        global $wpdb, $frm_duplicate_ids;

        $new_values = array();
        $key = isset($values['field_key']) ? $values['field_key'] : $values['name'];
        $new_values['field_key'] = FrmAppHelper::get_unique_key($key, $wpdb->prefix .'frm_fields', 'field_key');

        foreach ( array('name', 'description', 'type', 'default_value') as $col ) {
            $new_values[$col] = $values[$col];
        }

        $new_values['options'] = $values['options'];

        $new_values['field_order'] = isset($values['field_order']) ? (int) $values['field_order'] : NULL;
        $new_values['required'] = isset($values['required']) ? (int) $values['required'] : 0;
        $new_values['form_id'] = isset($values['form_id']) ? (int) $values['form_id'] : NULL;
        $new_values['field_options'] = $values['field_options'];
        $new_values['created_at'] = current_time('mysql', 1);

        if(isset($values['id'])){
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
                self::delete_form_transient($new_values['form_id']);
                $new_id = $wpdb->insert_id;
                if ( isset($values['id']) ) {
                    $frm_duplicate_ids[$values['id']] = $new_id;
                }
                return $new_id;
            }else{
                return false;
            }
        }
    }

    public static function duplicate($old_form_id, $form_id, $copy_keys=false, $blog_id=false){
        global $frm_duplicate_ids;
        $fields = self::getAll(array('fi.form_id' => $old_form_id), 'field_order', '', $blog_id);
        foreach ( (array) $fields as $field ) {
            $new_key = ($copy_keys) ? $field->field_key : '';
            if ( $copy_keys && substr($field->field_key, -1) == 2 ) {
                $new_key = rtrim($new_key, 2);
            }

            $values = array();
            FrmFieldsHelper::fill_field( $values, $field, $form_id, $new_key );

            $values = apply_filters('frm_duplicated_field', $values);
            $new_id = self::create($values);
            $frm_duplicate_ids[$field->id] = $new_id;
            $frm_duplicate_ids[$field->field_key] = $new_id;
            unset($field);
        }
    }

    public static function update( $id, $values ){
        global $wpdb;

        if (isset($values['field_key']))
            $values['field_key'] = FrmAppHelper::get_unique_key($values['field_key'], $wpdb->prefix .'frm_fields', 'field_key', $id);

        if ( isset($values['required']) ) {
            $values['required'] = (int) $values['required'];
        }

        if ( isset($values['default_value']) && is_array($values['default_value']) ) {
            $values['default_value'] = serialize($values['default_value']);
        }

        if ( isset($values['field_options']) && is_array($values['field_options']) ) {
            $values['field_options'] = serialize($values['field_options']);
        }

        if ( isset($values['options']) && is_array($values['options']) ) {
            $values['options'] = serialize($values['options']);
        }

        $query_results = $wpdb->update( $wpdb->prefix .'frm_fields', $values, array( 'id' => $id ) );

        $form_id = 0;
        if(isset($values['form_id'])){
            $form_id = $values['form_id'];
        }else{
            $field = self::getOne($id);
            if ( $field ) {
                $form_id = $field->form_id;
            }
            unset($field);
        }
        unset($values);

        if($query_results){
            wp_cache_delete( $id, 'frm_field' );
            if ( $form_id ) {
                self::delete_form_transient($form_id);
            }
        }

        return $query_results;
    }

    public static function destroy( $id ){
      global $wpdb;

      do_action('frm_before_destroy_field', $id);

      wp_cache_delete( $id, 'frm_field' );
      $field = self::getOne($id);
      if ( ! $field ) {
          return false;
      }

      self::delete_form_transient($field->form_id);

      $wpdb->query($wpdb->prepare('DELETE FROM '. $wpdb->prefix .'frm_item_metas WHERE field_id=%d', $id));
      return $wpdb->query($wpdb->prepare('DELETE FROM '. $wpdb->prefix .'frm_fields WHERE id=%d', $id));
    }

    public static function delete_form_transient($form_id) {
        delete_transient('frm_all_form_fields_'. $form_id .'exclude');
        delete_transient('frm_all_form_fields_'. $form_id .'include');

        $cache_key = serialize(array('fi.form_id' => (int) $form_id)) . 'field_orderlb';
        wp_cache_delete($cache_key, 'frm_field');

        $form = FrmForm::getOne($form_id);
        if ( $form && $form->parent_form_id ) {
            self::delete_form_transient( $form->parent_form_id );
        }
    }

    public static function getOne( $id ){
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

        $results->field_options = maybe_unserialize($results->field_options);
        if ( isset($results->field_options['format']) && ! empty($results->field_options['format']) ) {
            $results->field_options['format'] = addslashes($results->field_options['format']);
        }
        $results->options = maybe_unserialize($results->options);
        $results->default_value = maybe_unserialize($results->default_value);

        return stripslashes_deep($results);
    }

    public static function get_all_types_in_form($form_id, $type, $limit = '', $inc_sub = 'exclude') {
        if ( ! $form_id ) {
            return array();
        }

        $results = get_transient('frm_all_form_fields_'. $form_id . $inc_sub);
        if ( $results !== false ) {
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
        $results = self::getAll(array('fi.form_id' => (int) $form_id, 'fi.type' => $type), 'field_order', $limit);
        self::$use_cache = true;
        self::include_sub_fields($results, $inc_sub, $type);

        return $results;
    }

    public static function get_all_for_form($form_id, $limit = '', $inc_sub = 'exclude') {
        if ( ! (int) $form_id ) {
            return array();
        }

        $results = get_transient('frm_all_form_fields_'. $form_id . $inc_sub);
        if ( $results !== false ) {
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
        $results = self::getAll(array('fi.form_id' => (int) $form_id), 'field_order', $limit);
        self::$use_cache = true;
        self::include_sub_fields($results, $inc_sub, 'all');

        if ( empty($limit) ) {
            set_transient('frm_all_form_fields_'. $form_id . $inc_sub, $results, 60*60*6);
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
                $sub_fields = self::get_all_for_form($field->field_options['form_select']);
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
        if ( $order_by == 'field_order' ) {
            //make sure the older field is first if there are two with the same order number
            $order_by .= ',fi.id';
        }

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

        if ( ! empty($order_by) && ! preg_match("/ORDER BY/", $order_by) ) {
            $order_by = ' ORDER BY '. $order_by;
        }

        $limit = FrmAppHelper::esc_limit($limit);

        $query = "SELECT fi.*, fr.name as form_name  FROM {$table_name} fi LEFT OUTER JOIN {$form_table_name} fr ON fi.form_id=fr.id";

        $old_where = $where;
        if ( is_array($where) ) {
            $where = FrmAppHelper::get_where_clause_and_values( $where );

            $query .= $where['where'] . $order_by . $limit;
            $query = $wpdb->prepare($query, $where['values']);

            if ( count($old_where) == 1 && isset($old_where['fi.form_id']) ) {
                // add sub fields to query
                $form_id = $old_where['fi.form_id'];
                $query = str_replace('fi.form_id='. $form_id, '(fi.form_id='. $form_id .' OR fr.parent_form_id = '. $form_id .')', $query);
            }
        }else{
            $query .= FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        }

        if ( $limit == ' LIMIT 1' || $limit == 1 ) {
            $results = $wpdb->get_row($query);
        } else {
            $results = $wpdb->get_results($query);
        }

        if ( ! $results ) {
            stripslashes_deep($results);
        }

        if ( is_array($results) ) {
            foreach ( $results as $r_key => $result ) {
                wp_cache_set($result->id, $result, 'frm_field');
                wp_cache_set($result->field_key, $result, 'frm_field');

                $results[$r_key]->field_options = maybe_unserialize($result->field_options);
                if ( isset($results[$r_key]->field_options['format']) && !empty($results[$r_key]->field_options['format']) ) {
                    $results[$r_key]->field_options['format'] = addslashes($results[$r_key]->field_options['format']);
                }

                $results[$r_key]->options = maybe_unserialize($result->options);
                $results[$r_key]->default_value = maybe_unserialize($result->default_value);
                $form_id = $result->form_id;

                unset($r_key, $result);
            }

            unset($form_id);
        }else{
            wp_cache_set($results->id, $results, 'frm_field');
            wp_cache_set($results->field_key, $results, 'frm_field');

            $results->field_options = maybe_unserialize($results->field_options);
            if ( isset($results->field_options['format']) && !empty($results->field_options['format']) ) {
                $results->field_options['format'] = addslashes($results->field_options['format']);
            }

            $results->options = maybe_unserialize($results->options);
            $results->default_value = maybe_unserialize($results->default_value);
        }
        wp_cache_set($cache_key, $results, 'frm_field', 300);

        return stripslashes_deep($results);
    }

    public static function getIds($where = '', $order_by = '', $limit = ''){
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
