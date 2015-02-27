<?php
/**
 * @package Formidable
 */

if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

if(class_exists('FrmFieldsController'))
    return;
 
class FrmFieldsController{
    public static function load_hooks(){
        add_action('wp_ajax_frm_load_field', 'FrmFieldsController::load_field');
        add_action('wp_ajax_frm_insert_field', 'FrmFieldsController::create');
        add_action('wp_ajax_frm_field_name_in_place_edit', 'FrmFieldsController::edit_name');
        add_action('wp_ajax_frm_field_desc_in_place_edit', 'FrmFieldsController::edit_description');
        add_action('wp_ajax_frm_update_ajax_option', 'FrmFieldsController::update_ajax_option');
        add_action('wp_ajax_frm_duplicate_field', 'FrmFieldsController::duplicate');
        add_action('wp_ajax_frm_delete_field', 'FrmFieldsController::destroy');
        add_action('wp_ajax_frm_add_field_option', 'FrmFieldsController::add_option');
        add_action('wp_ajax_frm_field_option_ipe', 'FrmFieldsController::edit_option');
        add_action('wp_ajax_frm_delete_field_option', 'FrmFieldsController::delete_option');
        add_action('wp_ajax_frm_import_choices', 'FrmFieldsController::import_choices');
        add_action('wp_ajax_frm_import_options', 'FrmFieldsController::import_options');
        add_action('wp_ajax_frm_update_field_order', 'FrmFieldsController::update_order');
        add_filter('frm_field_type', 'FrmFieldsController::change_type');
        add_filter('frm_display_field_options', 'FrmFieldsController::display_field_options');
        add_action('frm_field_input_html', 'FrmFieldsController::input_html');
        add_filter('frm_field_value_saved', 'FrmFieldsController::check_value', 50, 3);
        add_filter('frm_field_label_seen', 'FrmFieldsController::check_label', 10, 3);
    }
    
    public static function load_field(){
        $fields = $_POST['field'];
        if ( empty($fields) ) {
            die();
        }
        
        $_GET['page'] = 'formidable';
        $fields = stripslashes_deep($fields);
        
        $ajax = true;
        $values = array();
        $path = FrmAppHelper::plugin_path();
        $field_html = array();
        
        foreach ( $fields as $field ) {
            $field = htmlspecialchars_decode(nl2br($field));
            $field = json_decode($field, true);
            
            $field_id = $field['id'];
            
            if ( !isset($field['value']) ) {
                $field['value'] = '';
            }
            
            $field_name = "item_meta[$field_id]";
            
            ob_start();
            include($path .'/classes/views/frm-forms/add_field.php');
            $field_html[$field_id] = ob_get_contents();
            ob_end_clean();
        }
        
        unset($path);
        
        echo json_encode($field_html);
        
        die();
    }
    
    public static function create(){
        $field_data = $_POST['field'];
        $form_id = $_POST['form_id'];
        $values = array();
        if(class_exists('FrmProForm'))
            $values['post_type'] = FrmProFormsHelper::post_type($form_id);
        
        $field_values = apply_filters('frm_before_field_created', FrmFieldsHelper::setup_new_vars($field_data, $form_id));
        
        $frm_field = new FrmField();
        $field_id = $frm_field->create( $field_values );
        
        if ($field_id){
            $field = FrmFieldsHelper::setup_edit_vars($frm_field->getOne($field_id));
            $field_name = "item_meta[$field_id]";
            $id = $form_id;
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/add_field.php');
        }
        die();
    }
    
    public static function edit_name($field = 'name', $id = '') {
        if ( empty($field) ) {
            $field = 'name';
        }
        
        if ( empty($id) ) {
            $id = str_replace('field_label_', '', $_POST['element_id']);
        }
        
        $value = trim($_POST['update_value']);
        if ( trim(strip_tags($value)) == '' ) {
            // set blank value if there is no content
            $value = '';
        }
        
        $frm_field = new FrmField();
        $form = $frm_field->update($id, array($field => $value));
        echo stripslashes($value);  
        die();
    }
    

    public static function edit_description(){
        $id = str_replace('field_description_', '', $_POST['element_id']);
        self::edit_name('description', $id);
    }
    
    public static function update_ajax_option(){
        $frm_field = new FrmField();
        $field = $frm_field->getOne($_POST['field']);
        foreach ( array('clear_on_focus', 'separate_value', 'default_blank') as $val ) {
            if ( isset($_POST[$val]) ) {
                $new_val = $_POST[$val];
                if ( $val == 'separate_value' ) {
                    $new_val = (isset($field->field_options[$val]) && $field->field_options[$val]) ? 0 : 1;
                }
                
                $field->field_options[$val] = $new_val;   
                unset($new_val);       
            }  
            unset($val);
        }

        $frm_field->update($_POST['field'], array('field_options' => $field->field_options));
        die();
    }
    
    public static function duplicate(){
        global $wpdb;
        
        $frm_field = new FrmField();
        $copy_field = $frm_field->getOne($_POST['field_id']);
        if (!$copy_field) return;
            
        $values = array();
        $values['field_key'] = FrmAppHelper::get_unique_key('', $wpdb->prefix . 'frm_fields', 'field_key');
        $values['options'] = maybe_serialize($copy_field->options);
        $values['default_value'] = maybe_serialize($copy_field->default_value);
        $values['form_id'] = $copy_field->form_id;
        foreach (array('name', 'description', 'type', 'field_options', 'required') as $col)
            $values[$col] = $copy_field->{$col};
        $field_count = FrmAppHelper::getRecordCount(array('form_id' => $copy_field->form_id), $wpdb->prefix . 'frm_fields');
        $values['field_order'] = $field_count + 1;
        
        $field_id = $frm_field->create($values);
        
        if ($field_id){
            $field = FrmFieldsHelper::setup_edit_vars($frm_field->getOne($field_id));
            $field_name = "item_meta[$field_id]";
            $id = $field['form_id'];
            if($field['type'] == 'html')
                $field['stop_filter'] = true;
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/add_field.php');
        }
        die();
    }
    
    public static function destroy(){
        $frm_field = new FrmField();
        $field_id = $frm_field->destroy($_POST['field_id']);
        die();
    }   

    /* Field Options */
    public static function add_option(){
        $frm_field = new FrmField();

        $id = $_POST['field_id'];
        $field = $frm_field->getOne($id);
        $options = maybe_unserialize($field->options);
        if(!empty($options))
            $last = max(array_keys($options));
        else
            $last = 0;
        
        $opt_key = $last + 1;
        $first_opt = reset($options);
        $next_opt = count($options);
        if($first_opt != '')
            $next_opt++;
        $opt = __('Option', 'formidable') .' '. $next_opt;
        unset($next_opt);
        
        $field_val = $opt;
        $options[$opt_key] = $opt;
        $frm_field->update($id, array('options' => maybe_serialize($options)));
        $checked = '';

        $field_data = $frm_field->getOne($id);
        $field_data->field_options = maybe_unserialize($field_data->field_options);
        $field = array();
        $field['type'] = $field_data->type;
        $field['id'] = $id;
        $field['separate_value'] = isset($field_data->field_options['separate_value']) ? $field_data->field_options['separate_value'] : 0;
        $field_name = "item_meta[$id]";
        
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/single-option.php');
        die();
    }

    public static function edit_option(){
        $ids = explode('-', $_POST['element_id']);
        $id = str_replace('field_', '', $ids[0]);
        if ( strpos($_POST['element_id'], 'key_') ) {
            $id = str_replace('key_', '', $id);
            $new_value = trim($_POST['update_value']);
        } else {
            $new_label = trim($_POST['update_value']);
        }
        
        $frm_field = new FrmField();
        $field = $frm_field->getOne($id);
        $options = maybe_unserialize($field->options);
        $this_opt = (array) $options[$ids[1]];
        
        $label = isset($this_opt['label']) ? $this_opt['label'] : reset($this_opt);
        if ( isset($this_opt['value']) ) {
            $value =  $this_opt['value'];
        }
            
        if ( !isset($new_label) ) {
            $new_label = $label;
        }
            
        if ( isset($new_value) || isset($value) ) {
            $update_value = isset($new_value) ? $new_value : $value;
        }
        
        if ( isset($update_value) && $update_value != $new_label ) {
            $options[$ids[1]] = array('value' => $update_value, 'label' => $new_label);
        } else {
            $options[$ids[1]] = trim($_POST['update_value']);
        }
        
        $frm_field->update($id, array('options' => maybe_serialize($options)));
        echo (trim($_POST['update_value']) == '') ? __('(Blank)', 'formidable') : stripslashes($_POST['update_value']);
        die();
    }

    public static function delete_option(){
        $frm_field = new FrmField();
        $field = $frm_field->getOne($_POST['field_id']);
        $options = maybe_unserialize($field->options);
        unset($options[$_POST['opt_key']]);
        $frm_field->update($_POST['field_id'], array('options' => maybe_serialize($options)));
        die();
    }
    
    public static function import_choices(){
        if ( !current_user_can('frm_edit_forms') ) {
            return;
        }
        
        $field_id = $_REQUEST['field_id'];
        	
        global $current_screen, $hook_suffix;

        // Catch plugins that include admin-header.php before admin.php completes.
        if ( empty( $current_screen ) && function_exists('set_current_screen') ) {
            $hook_suffix = '';
        	set_current_screen();
        }
        
        if ( function_exists('register_admin_color_schemes') ) {
            register_admin_color_schemes();
        }
        
        $hook_suffix = $admin_body_class = '';
        
        if ( get_user_setting('mfold') == 'f' )
        	$admin_body_class .= ' folded';

        if ( function_exists('is_admin_bar_showing') && is_admin_bar_showing() ) {
        	$admin_body_class .= ' admin-bar';
        }

        if ( is_rtl() )
        	$admin_body_class .= ' rtl';

        $admin_body_class .= ' admin-color-' . sanitize_html_class( get_user_option( 'admin_color' ), 'fresh' );
        $prepop = array();
        $prepop[__('Countries', 'formidable')] = FrmAppHelper::get_countries();
        
        $states = FrmAppHelper::get_us_states();
        $state_abv = array_keys($states);
        sort($state_abv);
        $prepop[__('U.S. State Abbreviations', 'formidable')] = $state_abv;
        $states = array_values($states);
        sort($states);
        $prepop[__('U.S. States', 'formidable')] = $states;
        unset($state_abv);
        unset($states);
        
        $prepop[__('Age', 'formidable')] = array(
            __('Under 18', 'formidable'), __('18-24', 'formidable'), __('25-34', 'formidable'), 
            __('35-44', 'formidable'), __('45-54', 'formidable'), __('55-64', 'formidable'),
            __('65 or Above', 'formidable'), __('Prefer Not to Answer', 'formidable')
        );
        
        $prepop[__('Satisfaction', 'formidable')] = array(
            __('Very Satisfied', 'formidable'), __('Satisfied', 'formidable'), __('Neutral', 'formidable'), 
            __('Unsatisfied', 'formidable'), __('Very Unsatisfied', 'formidable'), __('N/A', 'formidable')
        );

        $prepop[__('Importance', 'formidable')] = array(
            __('Very Important', 'formidable'), __('Important', 'formidable'), __('Neutral', 'formidable'), 
            __('Somewhat Important', 'formidable'), __('Not at all Important', 'formidable'), __('N/A', 'formidable')
        );
        
        $prepop[__('Agreement', 'formidable')] = array(
            __('Strongly Agree', 'formidable'), __('Agree', 'formidable'), __('Neutral', 'formidable'), 
            __('Disagree', 'formidable'), __('Strongly Disagree', 'formidable'), __('N/A', 'formidable')
        );
        
        $prepop = apply_filters('frm_bulk_field_choices', $prepop);
        
        $frm_field = new FrmField();
        $field = $frm_field->getOne($field_id);
        
        include(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/import_choices.php');
        die();
    }
    
    public static function import_options(){
        if(!is_admin() or !current_user_can('frm_edit_forms'))
            return;
        
        extract(stripslashes_deep($_POST));
        
        $frm_field = new FrmField();
        $field = $frm_field->getOne($field_id);
        
        if(!in_array($field->type, array('radio', 'checkbox', 'select')))
            return;
        
        $field = FrmFieldsHelper::setup_edit_vars($field);
        $opts = explode("\n", rtrim($opts, "\n"));
        if ( $field['separate_value'] ) {
            foreach ( $opts as $opt_key => $opt ) {
                if ( strpos($opt, '|') !== false ) {
                    $vals = explode('|', $opt);
                    if ( $vals[0] != $vals[1] ) {
                        $opts[$opt_key] = array('label' => trim($vals[0]), 'value' => trim($vals[1]));
                    }
                    unset($vals);
                }
                unset($opt_key);
                unset($opt);
            }
        }
        
        $frm_field->update($field_id, array('options' => maybe_serialize($opts)));
        
        $field['options'] = $opts;
        $field_name = $field['name'];
        
        if ( $field['type'] == 'radio' || $field['type'] == 'checkbox' ) {
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/radio.php');
        } else {
            foreach ( $field['options'] as $opt_key => $opt ) { 
                $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
                $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field);
                require(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/single-option.php');
            }
        }
        
        die();
    }

    public static function update_order(){
        if(isset($_POST) and isset($_POST['frm_field_id'])){
            $frm_field = new FrmField();
            
            foreach ($_POST['frm_field_id'] as $position => $item)
                $frm_field->update($item, array('field_order' => $position));
        }
        die();
    }
    
    public static function change_type($type){
        global $frm_vars;

        if ($frm_vars['pro_is_installed']) return $type;
        
        if($type == 'scale' || $type == '10radio')
            $type = 'radio';
        else if($type == 'rte')
            $type = 'textarea';
            
        $frm_field_selection = FrmFieldsHelper::field_selection();
        $types = array_keys($frm_field_selection);
        if (!in_array($type, $types) && $type != 'captcha')
            $type = 'text';

        return $type;
    }
    
    public static function display_field_options($display){
        switch($display['type']){
            case 'captcha':
                $display['required'] = false;
                $display['invalid'] = true;
                $display['default_blank'] = false;
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
        }
        
        return $display;
    }
    
    public static function input_html($field, $echo=true){
        global $frm_settings, $frm_vars;
        
        $class = ''; //$field['type'];
        
        if ( is_admin() && (!isset($frm_vars['preview']) || !$frm_vars['preview']) && !in_array($field['type'], array('scale', 'radio', 'checkbox', 'data')) ) {
            $class .= 'dyn_default_value';
        }
        
        $add_html = '';
        
        if(isset($field['size']) and $field['size'] > 0){
            if(!in_array($field['type'], array('textarea', 'select', 'data', 'time', 'hidden')))
                $add_html .= ' size="'. $field['size'] .'"';
            $class .= " auto_width";
        }
        
        if ( isset($field['max']) && !in_array($field['type'], array('textarea', 'rte', 'hidden')) && !empty($field['max']) && (!is_admin() || !isset($_GET) || !isset($_GET['page']) || $_GET['page'] != 'formidable') ) {
            $add_html .= ' maxlength="'. $field['max'] .'"';
        }
        
        if(!is_admin() or defined('DOING_AJAX') or !isset($_GET) or !isset($_GET['page']) or $_GET['page'] == 'formidable-entries'){
            /*if(isset($field['required']) and $field['required']){
                $action = isset($_REQUEST['frm_action']) ? 'frm_action' : 'action';
                $action = FrmAppHelper::get_param($action);
                
                //if($field['type'] != 'checkbox')
                //    $add_html .= ' required="required"';
                    
                if($field['type'] == 'file' and $action == 'edit'){
                    //don't add the required class if this is a file upload when editing
                }else{
                    $class .= " required";
                }
                unset($action);
            }*/
            
            if(isset($field['clear_on_focus']) and $field['clear_on_focus'] and !empty($field['default_value'])){
                
                if($frm_settings->use_html and !in_array($field['type'], array('select', 'radio', 'checkbox', 'hidden'))){ 
                    $add_html .= ' placeholder="'. esc_attr($field['default_value']) .'"';
                    FrmAppHelper::load_scripts('jquery-placeholder');
                }else if(!$frm_settings->use_html){
                    $val = str_replace(array("\r\n", "\n"), '\r', addslashes(str_replace('&#039;', "'", esc_attr($field['default_value']))));
                    $add_html .= ' onfocus="frmClearDefault('."'". $val ."'". ',this)" onblur="frmReplaceDefault('."'". $val ."'". ',this)"';
                    if($field['value'] == $field['default_value'])
                        $class .= ' frm_default';
                }
            }
        }
        
        if(isset($field['input_class']) and !empty($field['input_class']))
            $class .= ' '. $field['input_class'];
        
        $class = apply_filters('frm_field_classes', $class, $field);
        
        if(!empty($class))
            $add_html .= ' class="'. trim($class) .'"';
            
        if ( isset($field['shortcodes']) && !empty($field['shortcodes']) ) {
            foreach ( $field['shortcodes'] as $k => $v ) {
                if ( 'opt' === $k  || (!is_numeric($k) && strpos($add_html, " $k=")) ) {
                    continue;
                }
                
                if ( is_numeric($k) && strpos($v, '=') ) {
                    $add_html .= ' '. $v;
                } else {
                    $add_html .= ' '. $k .'="'. $v .'"';
                }
                
                unset($k, $v);
            }
        }
        
        if($echo)
            echo $add_html;
        
        return $add_html;
    }
    
    public static function check_value($opt, $opt_key, $field){
        if(is_array($opt)){
            if(isset($field['separate_value']) and $field['separate_value']){
                $opt = isset($opt['value']) ? $opt['value'] : (isset($opt['label']) ? $opt['label'] : reset($opt));
            }else{
                $opt = (isset($opt['label']) ? $opt['label'] : reset($opt));
            }
        }
        return $opt;
    }
    
    public static function check_label($opt, $opt_key, $field){
        if(is_array($opt))
            $opt = (isset($opt['label']) ? $opt['label'] : reset($opt));
            
        return $opt;
    }
}
