<?php
if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

class FrmEntryMeta{

    public static function add_entry_meta($entry_id, $field_id, $meta_key = null, $meta_value) {
        global $wpdb;

        if ( ( is_array( $meta_value ) && empty( $meta_value ) ) || ( ! is_array( $meta_value ) && trim( $meta_value ) == '' ) ) {
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

    public static function update_entry_meta($entry_id, $field_id, $meta_key = null, $meta_value){
        if ( ! $field_id ) {
            return false;
        }

        global $wpdb;

        $values = $where_values = array( 'item_id' => $entry_id, 'field_id' => $field_id );
        $values['meta_value'] = $meta_value;
        $values = apply_filters('frm_update_entry_meta', $values);
		if ( is_array($values['meta_value']) ) {
			$values['meta_value'] = array_filter($values['meta_value']);
		}
        $meta_value = maybe_serialize($values['meta_value']);

        wp_cache_delete( $entry_id, 'frm_entry');

        return $wpdb->update( $wpdb->prefix .'frm_item_metas', array( 'meta_value' => $meta_value ), $where_values );
    }

    public static function update_entry_metas($entry_id, $values){
        global $wpdb;

        $prev_values = $wpdb->get_col($wpdb->prepare("SELECT field_id FROM {$wpdb->prefix}frm_item_metas WHERE item_id=%d AND field_id != %d", $entry_id, 0));

        foreach ( $values as $field_id => $meta_value ) {
            // set the value for the file upload field and add new tags (in Pro version)
            $values = apply_filters( 'frm_prepare_data_before_db', $values, $field_id, $entry_id );

            if ( $prev_values && in_array($field_id, $prev_values) ) {
                if ( ( is_array( $meta_value ) && empty( $meta_value ) ) || ( ! is_array( $meta_value ) && trim( $meta_value ) == '' ) ) {
                    // remove blank fields
                    unset($values[$field_id]);
                } else {
                    // if value exists, then update it
                    self::update_entry_meta($entry_id, $field_id, '', $values[$field_id]);
                }
            } else {
                // if value does not exist, then create it
                self::add_entry_meta($entry_id, $field_id, '', $values[$field_id]);
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

    public static function duplicate_entry_metas($old_id, $new_id){
        $metas = self::get_entry_meta_info($old_id);
        foreach ( $metas as $meta ) {
            self::add_entry_meta($new_id, $meta->field_id, null, $meta->meta_value);
            unset($meta);
        }
    }

    public static function delete_entry_meta($entry_id, $field_id){
        global $wpdb;
        return $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}frm_item_metas WHERE field_id=%d AND item_id=%d", $field_id, $entry_id));
    }

    public static function get_entry_meta_by_field($entry_id, $field_id) {
        global $wpdb;

        $entry_id = (int) $entry_id;

        $cached = FrmAppHelper::check_cache( $entry_id, 'frm_entry' );
        if ( $cached && isset($cached->metas) && isset($cached->metas[$field_id]) ) {
            $result = $cached->metas[$field_id];
            return stripslashes_deep($result);
        }

        if ( is_numeric($field_id) ) {
            $query = $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE field_id=%d and item_id=%d", $field_id, $entry_id);
        } else {
            $query = $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}frm_item_metas it LEFT OUTER JOIN {$wpdb->prefix}frm_fields fi ON it.field_id=fi.id WHERE fi.field_key=%s and item_id=%d", $field_id, $entry_id);
        }
        $query .= ' LIMIT 1';

        $cache_key = 'get_entry_meta_by_field_'. $entry_id .'f'. $field_id;
        $result = FrmAppHelper::check_cache($cache_key, 'frm_entry', $query, 'get_var');
        $result = maybe_unserialize($result);

        if ( $cached ) {
            if ( ! isset( $cached->metas ) ) {
                $cached->metas = array();
            }
            $cached->metas[$field_id] = $result;
            wp_cache_set($entry_id, $cached, 'frm_entry');
        }
        $result = stripslashes_deep($result);

        return $result;
    }

    public static function get_entry_metas($entry_id){
        _deprecated_function( __FUNCTION__, '1.07.10');

        global $wpdb;
        return $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id=%d", $entry_id));
    }

    public static function get_entry_metas_for_field($field_id, $order='', $limit='', $args=array()){
        $defaults = array('value' => false, 'unique' => false, 'stripslashes' => true, 'is_draft' => false);
        $args = wp_parse_args( $args, $defaults );

        $query = array();
        self::meta_field_query($field_id, $order, $limit, $args, $query);
        $query = implode(' ', $query);

        $cache_key = 'entry_metas_for_field_'. $field_id . $order . $limit . maybe_serialize($args);
        $values = FrmAppHelper::check_cache($cache_key, 'frm_entry', $query, 'get_col');

        if ( ! $args['stripslashes'] ) {
            return $values;
        }

        foreach($values as $k => $v){
            $values[$k] = maybe_unserialize($v);
            unset($k, $v);
        }

        return stripslashes_deep($values);
    }

    private static function meta_field_query($field_id, $order, $limit, $args, array &$query) {
        global $wpdb;
        $query[] = 'SELECT';
        $query[] = $args['unique'] ? 'DISTINCT(em.meta_value)' : 'em.meta_value';
        $query[] = 'FROM '. $wpdb->prefix .'frm_item_metas em ';

        if ( ! $args['is_draft'] ) {
            $query[] = 'INNER JOIN '. $wpdb->prefix .'frm_items e ON (e.id=em.item_id)';
        }

        if ( is_numeric($field_id) ) {
            $query[] = $wpdb->prepare('WHERE em.field_id=%d', $field_id);
        } else {
            $query[] = $wpdb->prepare('LEFT JOIN '. $wpdb->prefix .'frm_fields fi ON (em.field_id = fi.id) WHERE fi.field_key=%s', $field_id);
        }

        if ( ! $args['is_draft'] ) {
            $query[] = 'AND e.is_draft=0';
        }

        if ( $args['value'] ) {
            $query[] = $wpdb->prepare(' AND meta_value=%s', $args['value']);
        }
        $query[] = $order . $limit;
    }

    public static function get_entry_meta_info($entry_id){
        global $wpdb;

        $cache_key = 'entry_meta_info_'. $entry_id;
        $query = $wpdb->prepare('SELECT * FROM '. $wpdb->prefix .'frm_item_metas WHERE item_id=%d', $entry_id);

        $results = FrmAppHelper::check_cache($cache_key, 'frm_entry', $query, 'get_results');

        return $results;
    }

    public static function getAll($where = '', $order_by = '', $limit = '', $stripslashes = false){
        global $wpdb;
        $query = 'SELECT it.*, fi.type as field_type, fi.field_key as field_key,
            fi.required as required, fi.form_id as field_form_id, fi.name as field_name, fi.options as fi_options
            FROM '. $wpdb->prefix .'frm_item_metas it LEFT OUTER JOIN '. $wpdb->prefix .'frm_fields fi ON it.field_id=fi.id' .
            FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;

        $cache_key = 'all_'. maybe_serialize($where) . $order_by . $limit;
        $results = FrmAppHelper::check_cache($cache_key, 'frm_entry', $query, ($limit == ' LIMIT 1' ? 'get_row' : 'get_results'));

        if ( ! $results || ! $stripslashes ) {
            return $results;
        }

        foreach ( $results as $k => $result ) {
            $results[$k]->meta_value = stripslashes_deep(maybe_unserialize($result->meta_value));
            unset($k, $result);
        }

        return $results;
    }

    public static function getEntryIds($where = '', $order_by = '', $limit = '', $unique=true, $args = array()) {
        $defaults = array('is_draft' => false, 'user_id' => '');
        $args = wp_parse_args($args, $defaults);

        $query = array();
        self::get_ids_query($where, $order_by, $limit, $unique, $args, $query);
        $query = implode(' ', $query);

        $cache_key = 'ids_'. maybe_serialize($where) . $order_by . 'l'. $limit . 'u'. $unique . maybe_serialize($args);
        $results = FrmAppHelper::check_cache($cache_key, 'frm_entry', $query, ($limit == ' LIMIT 1' ? 'get_var' : 'get_col'));

        return $results;
    }

    private static function get_ids_query($where, $order_by, $limit, $unique, $args, array &$query) {
        global $wpdb;
        $query[] = 'SELECT';
        $query[] = $unique ? 'DISTINCT(it.item_id)' : 'it.item_id';
        $query[] = 'FROM '. $wpdb->prefix .'frm_item_metas it LEFT OUTER JOIN '. $wpdb->prefix .'frm_fields fi ON it.field_id=fi.id';

        $query[] = 'INNER JOIN '. $wpdb->prefix .'frm_items e ON (e.id=it.item_id)';
        if ( is_array($where) ) {
            if ( ! $args['is_draft'] ) {
                $where['e.is_draft'] = 0;
            } else if ( $args['is_draft'] == 1 ){
                $where['e.is_draft'] = 1;
            }

            if ( ! empty($args['user_id']) ) {
                $where['e.user_id'] = $args['user_id'];
            }
            $query[] = FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
            return;
        }

        $draft_where = $user_where = '';
        if ( ! $args['is_draft'] ) {
            $draft_where = ' AND e.is_draft=0';
        } else if ( $args['is_draft'] == 1 ) {
            $draft_where = ' AND e.is_draft=1';
        }

        if ( ! empty($args['user_id']) ) {
            $user_where = $wpdb->prepare(' AND e.user_id=%d', $args['user_id']);
        }

        if ( strpos($where, ' GROUP BY ') ) {
            // don't inject WHERE filtering after GROUP BY
            $parts = explode(' GROUP BY ', $where);
            $where = $parts[0];
            $where .= $draft_where . $user_where;
            $where .= ' GROUP BY '. $parts[1];
        } else {
            $where .= $draft_where . $user_where;
        }

        $query[] = FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
    }

    public static function search_entry_metas($search, $field_id='', $operator){
        $cache_key = 'search_'. maybe_serialize($search) . $field_id . $operator;
        $results = wp_cache_get($cache_key, 'frm_entry');
        if ( false !== $results ) {
            return $results;
        }

        global $wpdb;
        if (is_array($search)){
            $where = '';
            foreach ($search as $field => $value){
                if ( $value <= 0 || ! in_array($field, array('year', 'month', 'day')) ) {
                    continue;
                }

                switch ( $field ) {
                    case 'year':
                        $value = '%'. $value;
                    break;
                    case 'month':
                        $value .= '%';
                    break;
                    case 'day':
                        $value = '%'. $value .'%';
                }
                $where .= $wpdb->prepare(' meta_value '. $operator .' %s and', $value);
            }
            $where .= $wpdb->prepare(' field_id=%d', $field_id);
            $query = "SELECT DISTINCT item_id FROM {$wpdb->prefix}frm_item_metas". FrmAppHelper::prepend_and_or_where(' WHERE ', $where);
        }else{
            if ($operator == 'LIKE')
                $search = "%{$search}%";
            $query = $wpdb->prepare("SELECT DISTINCT item_id FROM {$wpdb->prefix}frm_item_metas WHERE meta_value {$operator} %s and field_id = %d", $search, $field_id);
        }

        $results = $wpdb->get_col($query, 0);
        wp_cache_set($cache_key, $results, 'frm_entry', 300);

        return $results;
    }

}
