<?php
/**
 * @package Formidable
 */

if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

if(class_exists('FrmSettingsController'))
    return;

class FrmSettingsController{
    public static function load_hooks(){
        add_action('admin_menu', 'FrmSettingsController::menu', 45);
        add_action('frm_before_settings', 'FrmSettingsController::license_box');
    }

    public static function menu(){
        add_submenu_page('formidable', 'Formidable | '. __('Global Settings', 'formidable'), __('Global Settings', 'formidable'), 'frm_change_settings', 'formidable-settings', 'FrmSettingsController::route');
    }
    
    public static function license_box(){
        $a = isset($_GET['t']) ? $_GET['t'] : 'general_settings';
        include(FrmAppHelper::plugin_path() .'/classes/views/frm-settings/license_box.php');
    }

    public static function display_form($errors=array(), $message=''){
        global $frm_settings, $frm_vars;
        
        $frm_roles = FrmAppHelper::frm_capabilities();
      
        $uploads = wp_upload_dir();
        $target_path = $uploads['basedir'] . '/formidable/css';
        $sections = apply_filters('frm_add_settings_section', array(
            'styling' => array('name' => __('Form Styling', 'formidable'), 'class' => 'FrmSettingsController', 'function' => 'styling_tab')
        ));
        
        $recaptcha_themes = array(
            'red' => __('Red', 'formidable'),
            'white' => __('White', 'formidable'),
            'blackglass' => __('Black Glass', 'formidable'),
            'clean' => __('Clean', 'formidable'),
            //'custom' => __('Custom', 'formidable'),
        );
        
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-settings/form.php');
    }

    public static function process_form($stop_load=false){        
        global $frm_settings, $frm_vars;
        
        if(!isset($_POST['process_form']) or !wp_verify_nonce($_POST['process_form'], 'process_form_nonce'))
            wp_die($frm_settings->admin_permission);
        
        $errors = array();
        $message = '';
        
        if(!isset($frm_vars['settings_routed']) or !$frm_vars['settings_routed']){
            //$errors = $frm_settings->validate($_POST,array());
            $frm_settings->update(stripslashes_deep($_POST));

            if( empty($errors) ){
                $frm_settings->store();
                $message = __('Settings Saved', 'formidable');
            }
        }else{
            $message = __('Settings Saved', 'formidable');
        }
        
        if($stop_load == 'stop_load'){
            $frm_vars['settings_routed'] = true;
            return;
        }
        
        self::display_form($errors, $message);
    }
    
    public static function styling_tab(){
        include(FrmAppHelper::plugin_path() .'/classes/views/frm-settings/styling_tab.php');
    }
    
    public static function route($stop_load=false){
        $action = isset($_REQUEST['frm_action']) ? 'frm_action' : 'action';
        $action = FrmAppHelper::get_param($action);
        if($action == 'process-form')
            return self::process_form($stop_load);
        else if($stop_load != 'stop_load')
            return self::display_form();
    }
}
