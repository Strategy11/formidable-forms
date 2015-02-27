<?php
/**
 * @package Formidable
 */
if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

if(class_exists('FrmAppController'))
    return;

class FrmAppController{
    public static function load_hooks(){
        add_action('admin_menu', 'FrmAppController::menu', 1);
        add_action( 'admin_enqueue_scripts', 'FrmAppController::load_wp_admin_style' );
        add_filter('plugin_action_links_formidable/formidable.php', 'FrmAppController::settings_link', 10, 2 );
        add_filter('update_plugin_complete_actions', 'FrmAppController::update_action_links', 10, 2 );
        add_action('admin_notices', 'FrmAppController::pro_get_started_headline');
        add_filter('the_content', 'FrmAppController::page_route', 10);
        add_action('plugins_loaded', 'FrmAppController::load_lang');
        add_action('init', 'FrmAppController::front_head');
        add_action('wp_footer', 'FrmAppController::footer_js', 1, 0);
        add_action('admin_init', 'FrmAppController::admin_js', 11);
        register_activation_hook(FrmAppHelper::plugin_path().'/formidable.php', 'FrmAppController::install');
        add_action('wp_ajax_frm_install', 'FrmAppController::install');
        add_action('wp_ajax_frm_uninstall', 'FrmAppController::uninstall');
        add_action('wp_ajax_frm_deauthorize', 'FrmAppController::deauthorize');

        // Used to process standalone requests
        add_action('init', 'FrmAppController::parse_standalone_request', 40);
        // Update the session data
        add_action('init', 'FrmAppController::referer_session', 1);
    }
    
    public static function menu(){
        global $frm_vars, $frm_settings;
        
        if ( current_user_can('administrator') && !current_user_can('frm_view_forms') ) {
            global $current_user;
            $frm_roles = FrmAppHelper::frm_capabilities();
            foreach($frm_roles as $frm_role => $frm_role_description)
                $current_user->add_cap( $frm_role );
            unset($frm_roles);
            unset($frm_role);
            unset($frm_role_description);
        }
        
        $count = count(get_post_types( array( 'show_ui' => true, '_builtin' => false, 'show_in_menu' => true ) ));
        $pos = ((int)$count > 0) ? '22.7' : '29.3';
        $pos = apply_filters('frm_menu_position', $pos);
        
        if(current_user_can('frm_view_forms')){
            add_menu_page('Formidable', $frm_settings->menu, 'frm_view_forms', 'formidable', 'FrmFormsController::route', FrmAppHelper::plugin_url() .'/images/form_16.png', $pos);
        }else if(current_user_can('frm_view_entries') and $frm_vars['pro_is_installed']){
            add_menu_page('Formidable', $frm_settings->menu, 'frm_view_entries', 'formidable', 'FrmProEntriesController::route', FrmAppHelper::plugin_url() .'/images/form_16.png', $pos);
        }
        
        add_filter('admin_body_class', 'FrmAppController::wp_admin_body_class');
    }
    
    public static function load_wp_admin_style(){
        wp_enqueue_style( 'frm_fonts',  FrmAppHelper::plugin_url() .'/css/frm_fonts.css', array(), FrmAppHelper::plugin_version());
    }
    
    public static function get_form_nav($id, $show_nav=false){
        global $pagenow, $frm_vars;
        
        $show_nav = FrmAppHelper::get_param('show_nav', $show_nav);
        if(!$show_nav)
            return;
            
        $current_page = (isset($_GET['page'])) ? $_GET['page'] : (isset($_GET['post_type']) ? $_GET['post_type'] : 'None');
        if($id and is_numeric($id)){
            $frm_form = new FrmForm();
            $form = $frm_form->getOne($id);
            unset($frm_form);
        }else{
            $form = false;
        }
        
        include(FrmAppHelper::plugin_path() .'/classes/views/shared/form-nav.php');
    }

    // Adds a settings link to the plugins page
    public static function settings_link($links, $file){
        $settings = '<a href="'. admin_url('admin.php?page=formidable-settings') .'">' . __('Settings', 'formidable') . '</a>';
        array_unshift($links, $settings);
        
        return $links;
    }
    
    public static function update_action_links( $actions, $plugin ) {
        
    	if ( 'formidable/formidable.php' != $plugin )
    		return $actions;
        
        global $frm_vars;
        
        $db_version = get_option('frm_db_version');
        $pro_db_version = $frm_vars['pro_is_installed'] ? get_option('frmpro_db_version') : false;
        
        if ( ( (int) $db_version < (int) FrmAppHelper::$db_version ) ||
            ( $frm_vars['pro_is_installed'] && (int) $pro_db_version < (int) FrmAppHelper::$pro_db_version ) ) {
                
            return sprintf( '<a href="%s">%s</a>', add_query_arg(array('upgraded' => 'true'), menu_page_url( 'formidable', 0 )), __( 'Click here to complete the upgrade', 'formidable' ) );
                
    	} else {
    	    return $actions;
    	}
    }

    public static function pro_get_started_headline(){
        if ( isset($_GET['page']) && 'formidable' == $_GET['page'] && isset( $_REQUEST['upgraded'] ) && 'true' == $_REQUEST['upgraded'] ) {
            self::install();
            ?>
<div id="message" class="frm_message updated"><?php _e('Congratulations! Formidable is ready to roll.', 'formidable') ?></div>
<?php
            return;
        }
        
        // Don't display this error as we're upgrading the thing... cmon
        if(isset($_GET['action']) and $_GET['action'] == 'upgrade-plugin')
            return;
    
        if ( is_multisite() && !current_user_can('administrator') ) {
            return;
        }
        
        if(!isset($_GET['activate'])){  
            global $frm_vars;
            $db_version = get_option('frm_db_version');
            $pro_db_version = ($frm_vars['pro_is_installed']) ? get_option('frmpro_db_version') : false;
            if ( ( (int) $db_version < (int) FrmAppHelper::$db_version ) ||
                ( $frm_vars['pro_is_installed'] && (int) $pro_db_version < (int) FrmAppHelper::$pro_db_version ) ) {
            ?>
<div class="error" id="frm_install_message" style="padding:7px;"><?php _e('Your update is not complete yet.<br/>Please deactivate and reactivate the plugin to complete the update or', 'formidable'); ?> <a id="frm_install_link" href="javascript:void(0)"><?php _e('Update Now', 'formidable') ?></a></div>
<script type="text/javascript">
jQuery(document).ready(function($){ $('#frm_install_link').click(frm_install_now); });
function frm_install_now(){
	jQuery('#frm_install_message').html('<div style="line-height:24px;"><?php _e("Please wait while your site updates.", "formidable") ?><div class="spinner frm_spinner" style="float:left;display:block;"></div></div>');
	jQuery.ajax({
		type:"POST",url:ajaxurl,data:"action=frm_install",
		success:function(msg){jQuery("#frm_install_message").fadeOut("slow");}
	});
}
</script>
<?php
            }
        }
            
        if ( self::pro_is_authorized() && !self::pro_is_installed()) {
            // user is authorized, but running free version
            $inst_install_url = 'http://formidablepro.com/knowledgebase/manually-install-formidable-pro/';
        ?>
    <div class="error" style="padding:7px;"><?php echo apply_filters('frm_pro_update_msg', sprintf(__('This site has been previously authorized to run Formidable Pro.<br/>%1$sInstall the pro version%2$s or %3$sdeauthorize%4$s this site to continue running the free version and remove this message.', 'formidable'), '<a href="'. $inst_install_url .'" target="_blank">', '</a>', '<a href="javascript:void(0)" onclick="frm_deauthorize_now()" class="frm_deauthorize_link">', '</a>'), $inst_install_url); ?></div>
<script type="text/javascript">
function frm_deauthorize_now(){
if(!confirm("<?php esc_attr_e('Are you sure you want to deauthorize Formidable Pro on this site?', 'formidable') ?>"))
	return false;
jQuery('.frm_deauthorize_link').html('<span class="spinner" style="display:inline-block;margin-top:0;float:none;"></span>');
jQuery.ajax({type:'POST',url:ajaxurl,data:'action=frm_deauthorize&nonce='+wp_create_nonce('frm_ajax'),
success:function(msg){jQuery('.error').fadeOut('slow');}
});
return false;
}
</script>
        <?php 
        }
    }
    
    public static function admin_js(){
        global $pagenow;
        
        if ( 'admin-ajax.php' == $pagenow && isset($_GET['action']) && $_GET['action'] != 'frm_import_choices' ) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_register_script('bootstrap_tooltip', FrmAppHelper::plugin_url() .'/js/bootstrap.min.js', array('jquery'), '3.0.3');
        
        if ( isset($_GET) && ((isset($_GET['page']) && strpos($_GET['page'], 'formidable') === 0 ) ||
            ($pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'frm_display'))
        ) {
            $version = FrmAppHelper::plugin_version();
            add_filter('admin_body_class', 'FrmAppController::admin_body_class');
            
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('admin-widgets');
            wp_enqueue_style('widgets');
            wp_enqueue_script('formidable');
            wp_enqueue_script('formidable_admin', FrmAppHelper::plugin_url() .'/js/formidable_admin.js', array('formidable', 'jquery', 'jquery-ui-draggable', 'bootstrap_tooltip'), $version, true);
            self::localize_script('admin');
            
            wp_enqueue_style('formidable-admin', FrmAppHelper::plugin_url() .'/css/frm_admin.css', array(), $version);
            add_thickbox();
            
            wp_register_script('formidable-editinplace', FrmAppHelper::plugin_url() .'/js/jquery/jquery.editinplace.packed.js', array('jquery'), '2.3.0');
            wp_register_script('jquery-frm-themepicker', FrmAppHelper::plugin_url() .'/js/jquery/jquery-ui-themepicker.js', array('jquery'), $version);
            
            
        }else if($pagenow == 'post.php' or ($pagenow == 'post-new.php' and isset($_REQUEST['post_type']) and $_REQUEST['post_type'] == 'frm_display')){
            if(isset($_REQUEST['post_type'])){
                $post_type = $_REQUEST['post_type'];
            }else if(isset($_REQUEST['post']) and !empty($_REQUEST['post'])){
                $post = get_post($_REQUEST['post']);
                if(!$post)
                    return;
                $post_type = $post->post_type;
            }else{
                return;
            }
            
            if($post_type == 'frm_display'){
                $version = FrmAppHelper::plugin_version();
                wp_enqueue_script('jquery-ui-draggable');
                wp_enqueue_script('formidable_admin', FrmAppHelper::plugin_url() . '/js/formidable_admin.js', array('formidable', 'jquery', 'jquery-ui-draggable', 'bootstrap_tooltip'), $version);
                wp_enqueue_style('formidable-admin', FrmAppHelper::plugin_url(). '/css/frm_admin.css', array(), $version);
                self::localize_script('admin');
            }
        }
    }
    
    public static function admin_body_class($classes){
        global $wp_version;
        
        //we only need this class on Formidable pages
        if(version_compare( $wp_version, '3.4.9', '>'))
            $classes .= ' frm_35_trigger';
        
        return $classes;
    }
    
    public static function wp_admin_body_class($classes){
        global $wp_version;
        //we need this class everywhere in the admin for the menu
        if(version_compare( $wp_version, '3.7.2', '>'))
            $classes .= ' frm_38_trigger';
        
        return $classes;
    }
    
    public static function load_lang(){
        load_plugin_textdomain('formidable', false, 'formidable/languages/' );
    }
    
    public static function front_head(){
        global $frm_settings;

        if (is_multisite()){
            global $frm_vars;
            $old_db_version = get_option('frm_db_version');
            $pro_db_version = ($frm_vars['pro_is_installed']) ? get_option('frmpro_db_version') : false;
            if ( ( (int) $old_db_version < (int) FrmAppHelper::$db_version ) ||
                ( $frm_vars['pro_is_installed'] && (int) $pro_db_version < (int) FrmAppHelper::$pro_db_version ) ) {
                self::install($old_db_version);
            }
        }
        
        $version = FrmAppHelper::plugin_version();
        wp_register_script('formidable', FrmAppHelper::plugin_url() . '/js/formidable.min.js', array('jquery'), $version, true);
        wp_register_script('jquery-placeholder', FrmAppHelper::plugin_url() .'/js/jquery/jquery.placeholder.js', array('jquery'), '2.0.7', true);
        wp_register_script('recaptcha-ajax', 'http'. (is_ssl() ? 's' : '').'://www.google.com/recaptcha/api/js/recaptcha_ajax.js', '', true);
        
        if ( is_admin() && !defined('DOING_AJAX') ) {
            // don't load this in back-end
            return;
        }
        
        self::localize_script('front');
        
        wp_enqueue_script('jquery');
        
        $style = apply_filters('get_frm_stylesheet', array('frm-forms' => FrmAppHelper::plugin_url() .'/css/frm_display.css'));
        if($style){
            foreach((array)$style as $k => $file){
                wp_register_style($k, $file, array(), $version);
                if ( 'all' == $frm_settings->load_style ) {
                    wp_enqueue_style($k);
                }
                unset($k, $file);
            }
        }
        unset($style);
        
        if ( $frm_settings->load_style == 'all' ) {                
            global $frm_vars;
            $frm_vars['css_loaded'] = true;
        }
    }
    
    public static function localize_script($location){
        wp_localize_script('formidable', 'frm_js', array(
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'images_url' => FrmAppHelper::plugin_url() .'/images',
            'loading'   => __('Loading&hellip;'),
            'remove'    => __('Remove', 'formidable'),
            'offset'    => apply_filters('frm_scroll_offset', 4),
        ));
        
        if($location == 'admin'){
            global $frm_settings;
            wp_localize_script('formidable_admin', 'frm_admin_js', array(
                'confirm_uninstall' => __('Are you sure you want to do this? Clicking OK will delete all forms, form data, and all other Formidable data. There is no Undo.', 'formidable'),
                'get_page'          => (isset($_GET) && isset($_GET['page'])) ? $_GET['page'] : '',
                'desc'              => __('(Click here to add a description or instructions)', 'formidable'),
                'blank'             => __('(Blank)', 'formidable'),
                'saving'            => esc_attr(__('Saving', 'formidable')),
                'saved'             => esc_attr(__('Saved', 'formidable')),
                'ok'                => __('OK'),
                'cancel'            => __('Cancel', 'formidable'),
                'clear_default'     => __('Clear default value when typing', 'formidable'),
                'no_clear_default'  => __('Do not clear default value when typing', 'formidable'),
                'valid_default'     => __('Default value will pass form validation', 'formidable'),
                'no_valid_default'  => __('Default value will NOT pass form validation', 'formidable'),
                'deauthorize'       => __('Are you sure you want to deactivate Formidable Pro on this site?', 'formidable'),
                'confirm'           => __('Are you sure?', 'formidable'),
                'default_unique'    => $frm_settings->unique_msg,
                'import_complete'   => __('Import Complete', 'formidable'),
                'updating'          => __('Please wait while your site updates.', 'formidable'),
                'nonce'             => wp_create_nonce('frm_ajax'),
            ));
        }
    }
    
    public static function footer_js($location='footer'){
        global $frm_settings, $frm_vars;

        if($frm_vars['load_css'] and (!is_admin() or defined('DOING_AJAX')) and ($frm_settings->load_style != 'none')){
            if(isset($frm_vars['css_loaded']) && $frm_vars['css_loaded'])
                $css = apply_filters('get_frm_stylesheet', array());
            else
                $css = apply_filters('get_frm_stylesheet', array('frm-forms' => FrmAppHelper::plugin_url() .'/css/frm_display.css'));
             
            if(!empty($css)){
                echo "\n".'<script type="text/javascript">';
                foreach((array)$css as $css_key => $file){
                    echo 'jQuery("head").append(unescape("%3Clink rel=\'stylesheet\' id=\''. ($css_key + (isset($frm_vars['css_loaded']) ? $frm_vars['css_loaded'] : false)) .'-css\' href=\''. $file. '\' type=\'text/css\' media=\'all\' /%3E"));';
                    //wp_enqueue_style($css_key);
                    unset($css_key);
                    unset($file);
                }
                unset($css);

                echo '</script>'."\n";
            }
        }

        if((!is_admin() or defined('DOING_AJAX')) and $location != 'header' and !empty($frm_vars['forms_loaded'])) //load formidable js  
            FrmAppHelper::load_scripts(array('formidable'));
    }
  
    public static function install($old_db_version=false){
        global $frmdb;
        $frmdb->upgrade($old_db_version);
    }
    
    public static function uninstall(){
        check_ajax_referer( 'frm_ajax', 'nonce' );
        
        if ( current_user_can('administrator') ) {
            global $frmdb;
            $frmdb->uninstall();
            echo true;
        } else {
            global $frm_settings;
            wp_die($frm_settings->admin_permission);
        }
        die();
    }
    
    // Routes for wordpress pages -- we're just replacing content here folks.
    public static function page_route($content){
        global $post, $frm_settings;

        if( $post && $post->ID == $frm_settings->preview_page_id && isset($_GET['form'])){
            $content = FrmFormsController::page_preview();
        }

        return $content;
    }
    
    public static function referer_session() {
    	global $frm_settings;
    	
    	if ( !isset($frm_settings->track) || !$frm_settings->track || defined('WP_IMPORTING') ) {
    	    return;
    	}
    	
    	// keep the page history below 100
    	$max = 100;
    	    
    	if ( !isset($_SESSION) )
    		session_start();
    	
    	if ( !isset($_SESSION['frm_http_pages']) or !is_array($_SESSION['frm_http_pages']) )
    		$_SESSION['frm_http_pages'] = array("http://". $_SERVER['SERVER_NAME']. $_SERVER['REQUEST_URI']);
    	
    	if ( !isset($_SESSION['frm_http_referer']) or !is_array($_SESSION['frm_http_referer']) )
    		$_SESSION['frm_http_referer'] = array();
    	
    	if (!isset($_SERVER['HTTP_REFERER']) or (isset($_SERVER['HTTP_REFERER']) and (strpos($_SERVER['HTTP_REFERER'], FrmAppHelper::site_url()) === false) and ! (in_array($_SERVER['HTTP_REFERER'], $_SESSION['frm_http_referer'])) )) {
    		if (! isset($_SERVER['HTTP_REFERER'])){
    		    $direct = __('Type-in or bookmark', 'formidable');
    		    if(!in_array($direct, $_SESSION['frm_http_referer']))
    			    $_SESSION['frm_http_referer'][] = $direct;
    		}else{
    			$_SESSION['frm_http_referer'][] = $_SERVER['HTTP_REFERER'];	
    		}
    	}
    	
    	if ($_SESSION['frm_http_pages'] and !empty($_SESSION['frm_http_pages']) and (end($_SESSION['frm_http_pages']) != "http://". $_SERVER['SERVER_NAME']. $_SERVER['REQUEST_URI']))
    		$_SESSION['frm_http_pages'][] = "http://". $_SERVER['SERVER_NAME']. $_SERVER['REQUEST_URI'];
    		
    	//keep the page history below the max
    	if(count($_SESSION['frm_http_pages']) > $max){
    	    foreach($_SESSION['frm_http_pages'] as $pkey => $ppage){
    	        if(count($_SESSION['frm_http_pages']) <= $max)
    	            break;
    	            
    		    unset($_SESSION['frm_http_pages'][$pkey]);
    		}
    	}
    }

    public static function parse_standalone_request(){
        $plugin = FrmAppHelper::get_param('plugin');
        $action = isset($_REQUEST['frm_action']) ? 'frm_action' : 'action';
        $action = FrmAppHelper::get_param($action);  
        $controller = FrmAppHelper::get_param('controller');
        
        if( !empty($plugin) and $plugin == 'formidable' and !empty($controller) ){
            _deprecated_function( __FUNCTION__, '1.07.02', 'wp_ajax_nopriv()' );
            
            if($controller == 'forms')
                FrmFormsController::preview(FrmAppHelper::get_param('form'));
            else
                do_action('frm_standalone_route', $controller, $action);

            do_action('frm_ajax_'. $controller .'_'. $action);
            die();
        }
    }
    
    //formidable shortcode
    public static function get_form_shortcode($atts){
        _deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::get_form_shortcode()' );
        return FrmFormsController::get_form_shortcode($atts); 
    }

    public static function widget_text_filter_callback( $matches ) {
        return do_shortcode( $matches[0] );
    }
    
    public static function update_message($features){
        include(FrmAppHelper::plugin_path() .'/classes/views/shared/update_message.php');
    }
    
    public static function get_postbox_class(){
        if(version_compare( $GLOBALS['wp_version'], '3.3.2', '>'))
            return 'postbox-container';
        else
            return 'inner-sidebar';
    }
    
    public static function pro_is_installed(){
        return file_exists(FrmAppHelper::plugin_path() . '/pro/formidable-pro.php');
    }
    
    public static function pro_is_authorized(){
        return get_site_option('frmpro-authorized');
    }
    
    public static function deauthorize(){
        check_ajax_referer( 'frm_ajax', 'nonce' );
        
        delete_option('frmpro-credentials');
        delete_option('frmpro-authorized');
        delete_site_option('frmpro-credentials');
        delete_site_option('frmpro-authorized');
        die();
    }
}
