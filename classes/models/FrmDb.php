<?php
if(!defined('ABSPATH')) die(__('You are not allowed to call this page directly.', 'formidable'));

if(class_exists('FrmDb'))
    return;

class FrmDb{
    
    function __construct(){
        global $wpdb;
        $this->fields         = $wpdb->prefix . "frm_fields";
        $this->forms          = $wpdb->prefix . "frm_forms";
        $this->entries        = $wpdb->prefix . "frm_items";
        $this->entry_metas    = $wpdb->prefix . "frm_item_metas";
    }
    
    function upgrade($old_db_version=false){
        global $wpdb;
        //$frm_db_version is the version of the database we're moving to
        $frm_db_version = FrmAppHelper::$db_version;
        $old_db_version = (float)$old_db_version;
        if(!$old_db_version)
            $old_db_version = get_option('frm_db_version');

        if ($frm_db_version != $old_db_version){
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      
        $charset_collate = '';
        if( $wpdb->has_cap( 'collation' ) ){
            if( !empty($wpdb->charset) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if( !empty($wpdb->collate) )
                $charset_collate .= " COLLATE $wpdb->collate";
        }

        /* Create/Upgrade Fields Table */
        $sql = "CREATE TABLE {$this->fields} (
                id int(11) NOT NULL auto_increment,
                field_key varchar(255) default NULL,
                name text default NULL,
                description text default NULL,
                type text default NULL,
                default_value longtext default NULL,
                options longtext default NULL,
                field_order int(11) default 0,
                required int(1) default NULL,
                field_options longtext default NULL,
                form_id int(11) default NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY form_id (form_id),
                UNIQUE KEY field_key (field_key)
              ) {$charset_collate};";

        dbDelta($sql);
        
        /* Create/Upgrade Forms Table */
        $sql = "CREATE TABLE {$this->forms} (
                id int(11) NOT NULL auto_increment,
                form_key varchar(255) default NULL,
                name varchar(255) default NULL,
                description text default NULL,
                parent_form_id int(11) default NULL,
                logged_in tinyint(1) default NULL,
                editable tinyint(1) default NULL,
                is_template tinyint(1) default 0,
                default_template tinyint(1) default 0,
                status varchar(255) default NULL,
                prli_link_id int(11) default NULL,
                options longtext default NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY form_key (form_key)
              ) {$charset_collate};";

        dbDelta($sql);

        /* Create/Upgrade Items Table */
        $sql = "CREATE TABLE {$this->entries} (
                id int(11) NOT NULL auto_increment,
                item_key varchar(255) default NULL,
                name varchar(255) default NULL,
                description text default NULL,
                ip text default NULL,
                form_id int(11) default NULL,
                post_id int(11) default NULL,
                user_id int(11) default NULL,
                parent_item_id int(11) default NULL,
                is_draft tinyint(1) default 0,
                updated_by int(11) default NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY form_id (form_id),
                KEY post_id (post_id),
                KEY user_id (user_id),
                KEY parent_item_id (parent_item_id),
                UNIQUE KEY item_key (item_key)
              ) {$charset_collate};";

        dbDelta($sql);

        /* Create/Upgrade Meta Table */
        $sql = "CREATE TABLE {$this->entry_metas} (
                id int(11) NOT NULL auto_increment,
                meta_value longtext default NULL,
                field_id int(11) NOT NULL,
                item_id int(11) NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY field_id (field_id),
                KEY item_id (item_id)
              ) {$charset_collate};";

        dbDelta($sql);
      
        /**** MIGRATE DATA ****/
        if ($frm_db_version >= 1.03 and $old_db_version < 1.03){
            global $frm_entry;
            $all_entries = $frm_entry->getAll();
            foreach($all_entries as $ent){
                $opts = maybe_unserialize($ent->description);
                if(is_array($opts) and in_array($opts['ip']))
                    $wpdb->update( $this->entries, array('ip' => $opts['ip']), array( 'id' => $ent->id ) );
            }
        }
      
        if($frm_db_version >= 4 and $old_db_version < 4){
            global $frm_entry_meta;
            $user_ids = $frm_entry_meta->getAll("fi.type='user_id'");
            foreach($user_ids as $user_id)
                $wpdb->update( $this->entries, array('user_id' => $user_id->meta_value), array('id' => $user_id->item_id) );
        }
      
        if($frm_db_version >= 6 and $old_db_version < 6){
            $fields = $wpdb->get_results("SELECT id, field_options FROM $this->fields WHERE type not in ('hidden', 'user_id', 'break', 'divider', 'html', 'captcha', 'form')");
            $default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="form-field [required_class] [error_class]">
    <label class="frm_pos_[label_position]">[field_name]
        <span class="frm_required">[required_label]</span>
    </label>
    [input]
    [if description]<div class="frm_description">[description]</div>[/if description]
</div>
DEFAULT_HTML;
            $old_default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="form-field [required_class] [error_class]">
    <label class="frm_pos_[label_position]">[field_name]
        <span class="frm_required">[required_label]</span>
    </label>
    [input]
    [if description]<p class="frm_description">[description]</p>[/if description]
</div>
DEFAULT_HTML;
            $new_default_html = FrmFieldsHelper::get_default_html('text');
            foreach($fields as $field){
                $field->field_options = maybe_unserialize($field->field_options);
                if ( !isset($field->field_options['custom_html']) || empty($field->field_options['custom_html']) || $field->field_options['custom_html'] == $default_html || $field->field_options['custom_html'] == $old_default_html ) {
                    $field->field_options['custom_html'] = $new_default_html;
                    $wpdb->update($this->fields, array('field_options' => maybe_serialize($field->field_options)), array( 'id' => $field->id ));
                }
                unset($field);
            }
            unset($default_html);
            unset($old_default_html);
            unset($fields);
        }
        
        if($frm_db_version >= 11 and $old_db_version < 11){
            $forms = $wpdb->get_results("SELECT id, options FROM $this->forms");
            $sending = __('Sending', 'formidable');
            $img = FrmAppHelper::plugin_url() .'/images/ajax_loader.gif';
            $old_default_html = <<<DEFAULT_HTML
<div class="frm_submit">
[if back_button]<input type="submit" value="[back_label]" name="frm_prev_page" formnovalidate="formnovalidate" [back_hook] />[/if back_button]
<input type="submit" value="[button_label]" [button_action] />
<img class="frm_ajax_loading" src="$img" alt="$sending" style="visibility:hidden;" />
</div>
DEFAULT_HTML;
            unset($sending);
            unset($img);
            
            $new_default_html = FrmFormsHelper::get_default_html('submit');
            $draft_link = FrmFormsHelper::get_draft_link();
            foreach($forms as $form){
                $form->options = maybe_unserialize($form->options);
                if(!isset($form->options['submit_html']) or empty($form->options['submit_html']))
                    continue;
                
                if ( $form->options['submit_html'] != $new_default_html && $form->options['submit_html'] == $old_default_html ) {
                    $form->options['submit_html'] = $new_default_html;
                    $wpdb->update($this->forms, array('options' => serialize($form->options)), array( 'id' => $form->id ));
                }else if(!strpos($form->options['submit_html'], 'save_draft')){
                    $form->options['submit_html'] = preg_replace('~\<\/div\>(?!.*\<\/div\>)~', $draft_link ."\r\n</div>", $form->options['submit_html']);
                    $wpdb->update($this->forms, array('options' => serialize($form->options)), array( 'id' => $form->id ));
                }
                unset($form);
            }
            unset($forms);
        }
        
        

        /**** ADD/UPDATE DEFAULT TEMPLATES ****/
        if ( class_exists('FrmXMLController') ) {
            FrmXMLController::add_default_templates();
        }

      
        /***** SAVE DB VERSION *****/
        update_option('frm_db_version', $frm_db_version);
        }

        do_action('frm_after_install');
    }
    
    function get_count($table, $args=array()){
        global $wpdb;
        extract(FrmDb::get_where_clause_and_values( $args ));

        $query = "SELECT COUNT(*) FROM {$table}{$where}";
        $query = $wpdb->prepare($query, $values);
        return $wpdb->get_var($query);
    }

    function get_where_clause_and_values( $args ){
        $where = '';
        $values = array();
        if(is_array($args)){
            foreach($args as $key => $value){
                $where .= (!empty($where)) ? ' AND' : ' WHERE';
                $where .= " {$key}=";
                $where .= is_numeric($value) ? (strpos($value, ".") !== false ? '%f' : '%d') : '%s';
				
                $values[] = $value;
            }
        }

        return compact('where', 'values');
    }
    
    function get_var($table, $args=array(), $field='id', $order_by=''){
        global $wpdb;

        extract(FrmDb::get_where_clause_and_values( $args ));
        if(!empty($order_by))
            $order_by = " ORDER BY {$order_by}";

        $query = $wpdb->prepare("SELECT {$field} FROM {$table}{$where}{$order_by} LIMIT 1", $values);
        return $wpdb->get_var($query);
    }
    
    function get_col($table, $args=array(), $field='id', $order_by=''){
        global $wpdb;

        extract(FrmDb::get_where_clause_and_values( $args ));
        if(!empty($order_by))
            $order_by = " ORDER BY {$order_by}";

        $query = $wpdb->prepare("SELECT {$field} FROM {$table}{$where}{$order_by}", $values);
        return $wpdb->get_col($query);
    }

    function get_one_record($table, $args=array(), $fields='*', $order_by=''){
        global $wpdb;

        extract(FrmDb::get_where_clause_and_values( $args ));
        
        if(!empty($order_by))
            $order_by = " ORDER BY {$order_by}";

        $query = "SELECT {$fields} FROM {$table}{$where} {$order_by} LIMIT 1";
        $query = $wpdb->prepare($query, $values);
        return $wpdb->get_row($query);
    }

    function get_records($table, $args=array(), $order_by='', $limit='', $fields='*'){
        global $wpdb;

        extract(FrmDb::get_where_clause_and_values( $args ));

        if ( !empty($order_by) && strpos($order_by, ' ORDER BY ') === false ) {
            $order_by = " ORDER BY {$order_by}";
        }

        if ( !empty($limit) && strpos($order_by, ' LIMIT ') === false ) {
            $limit = " LIMIT {$limit}";
        }

        $query = "SELECT {$fields} FROM {$table}{$where}{$order_by}{$limit}";
        $query = $wpdb->prepare($query, $values);
        return $wpdb->get_results($query);
    }
    
    function uninstall(){
        if ( !current_user_can('administrator') ) {
            global $frm_settings;
            wp_die($frm_settings->admin_permission);
        }
        
        global $wpdb, $wp_roles;
        
        $wpdb->query('DROP TABLE IF EXISTS '. $this->fields);
        $wpdb->query('DROP TABLE IF EXISTS '. $this->forms);
        $wpdb->query('DROP TABLE IF EXISTS '. $this->entries);
        $wpdb->query('DROP TABLE IF EXISTS '. $this->entry_metas);
        
        delete_option('frm_options');
        delete_option('frm_db_version');        
        
        //delete roles
        $frm_roles = FrmAppHelper::frm_capabilities();
        $roles = get_editable_roles();
        foreach($frm_roles as $frm_role => $frm_role_description){
            foreach ($roles as $role => $details){
                $wp_roles->remove_cap( $role, $frm_role );
                unset($role);
                unset($details);
    		}
    		unset($frm_role);
    		unset($frm_role_description);
		}
		unset($roles);
		unset($frm_roles);
        
        do_action('frm_after_uninstall');
    }
}
