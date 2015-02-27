<?php
if(!defined('ABSPATH')) die(__('You are not allowed to call this page directly.', 'formidable'));

if(class_exists('FrmSettings'))
    return;

class FrmSettings{

    function __construct(){
        $this->set_default_options();
    }
    
    function default_options(){
        return array(
            'menu'      => 'Formidable',
            'mu_menu'   => 0,
            'preview_page_id' => 0,
            'lock_keys' => false,
            'track'     => false,
            'use_html'  => true,
            'jquery_css' => false,
            'accordion_js' => false,
            
            'success_msg' => __('Your responses were successfully submitted. Thank you!', 'formidable'),
            'blank_msg' => __('This field cannot be blank.', 'formidable'),
            'unique_msg' => __('This value must be unique.', 'formidable'),
            'invalid_msg' => __('There was a problem with your submission. Errors are marked below.', 'formidable'),
            'failed_msg' => __('We\'re sorry. It looks like you\'ve  already submitted that.', 'formidable'),
            'submit_value' => __('Submit', 'formidable'),
            'login_msg' => __('You do not have permission to view this form.', 'formidable'),
            'admin_permission' => __('You do not have permission to do that', 'formidable'),
            
            'email_to' => '[admin_email]',
        );
    }

    function set_default_options(){
        if(!isset($this->pubkey)){
            $recaptcha_opt = is_multisite() ? get_site_option('recaptcha') : get_option('recaptcha'); // get the options from the database

            $this->pubkey = isset($recaptcha_opt['pubkey']) ? $recaptcha_opt['pubkey'] : ''; 
        } 
        
        if(!isset($this->privkey))
            $this->privkey = (isset($recaptcha_opt) and isset($recaptcha_opt['privkey'])) ? $recaptcha_opt['privkey'] : '';        

        if(!isset($this->re_theme))
            $this->re_theme = (isset($recaptcha_opt) and isset($recaptcha_opt['re_theme'])) ? $recaptcha_opt['re_theme'] : 'red';
            
        if(!isset($this->re_lang))
            $this->re_lang = (isset($recaptcha_opt) and isset($recaptcha_opt['re_lang'])) ? $recaptcha_opt['re_lang'] : 'en';
         
        if(!isset($this->re_msg) or empty($this->re_msg))
            $this->re_msg = __('The reCAPTCHA was not entered correctly', 'formidable');
            
        if(!isset($this->load_style)){
            if(!isset($this->custom_style))
                $this->custom_style = true;
            if(!isset($this->custom_stylesheet))
                $this->custom_stylesheet = false;
                
            $this->load_style = ($this->custom_stylesheet) ? 'none' : 'all';
        }
        
        $settings = $this->default_options();
        
        foreach($settings as $setting => $default){
            if(!isset($this->{$setting}))
                $this->{$setting} = $default;
            unset($setting);
            unset($default);
        }
        
        if(is_multisite() and is_admin()){
            $mu_menu = get_site_option('frm_admin_menu_name');
            if($mu_menu and !empty($mu_menu)){
                $this->menu = $mu_menu;
                $this->mu_menu = 1;
            }
        }
        
        $frm_roles = FrmAppHelper::frm_capabilities('pro');
        foreach($frm_roles as $frm_role => $frm_role_description){
            if(!isset($this->$frm_role))
                $this->$frm_role = 'administrator';
        }
    }

    function validate($params,$errors){
        $errors = apply_filters( 'frm_validate_settings', $errors, $params );
        return $errors;
    }

    function update($params){
        global $wp_roles;
        
        $this->mu_menu = isset($params['frm_mu_menu']) ? $params['frm_mu_menu'] : 0;
        if ( $this->mu_menu ) {
            update_site_option('frm_admin_menu_name', $this->menu);
        } else if ( current_user_can('administrator') ) {
            update_site_option('frm_admin_menu_name', false);
        }
        
        $this->pubkey = trim($params['frm_pubkey']);
        $this->privkey = $params['frm_privkey'];
        $this->re_theme = $params['frm_re_theme'];
        $this->re_lang = $params['frm_re_lang'];
        
        $settings = $this->default_options();
        
        foreach($settings as $setting => $default){
            if(isset($params['frm_'. $setting]))
                $this->{$setting} = $params['frm_'. $setting];
            
            unset($setting);
            unset($default);
        }
        
        $this->load_style = $params['frm_load_style'];
        $this->preview_page_id = (int)$params['frm-preview-page-id'];
        $this->lock_keys = isset($params['frm_lock_keys']) ? $params['frm_lock_keys'] : 0;
        $this->track = isset($params['frm_track']) ? $params['frm_track'] : 0;
        
        $this->use_html = isset($params['frm_use_html']) ? $params['frm_use_html'] : 0;
        //$this->custom_style = isset($params['frm_custom_style']) ? $params['frm_custom_style'] : 0;
        //$this->custom_stylesheet = isset($params['frm_custom_stylesheet']) ? $params['frm_custom_stylesheet'] : 0;
        $this->jquery_css = isset($params['frm_jquery_css']) ? $params['frm_jquery_css'] : 0;
        $this->accordion_js = isset($params['frm_accordion_js']) ? $params['frm_accordion_js'] : 0;
        
        //update roles
        $frm_roles = FrmAppHelper::frm_capabilities();
        $roles = get_editable_roles();
        foreach($frm_roles as $frm_role => $frm_role_description){
            $this->$frm_role = isset($params[$frm_role]) ? $params[$frm_role] : 'administrator';
            
            foreach ($roles as $role => $details){
                if($this->$frm_role == $role or ($this->$frm_role == 'editor' and $role == 'administrator') or ($this->$frm_role == 'author' and in_array($role, array('administrator', 'editor'))) or ($this->$frm_role == 'contributor' and in_array($role, array('administrator', 'editor', 'author'))) or $this->$frm_role == 'subscriber')
    			    $wp_roles->add_cap( $role, $frm_role );	
    			else
    			    $wp_roles->remove_cap( $role, $frm_role );
    		}	
		}
        
        do_action( 'frm_update_settings', $params );
    }

    function store(){
        // Save the posted value in the database

        update_option('frm_options', $this);
        
        delete_transient('frm_options');
        set_transient('frm_options', $this);

        do_action( 'frm_store_settings' );
    }
  
}
