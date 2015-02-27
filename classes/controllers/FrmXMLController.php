<?php
if ( !defined('ABSPATH') ) die('You are not allowed to call this page directly.');

class FrmXMLController{
    public static function load_hooks(){
        add_action('admin_menu', 'FrmXMLController::menu', 41);
        add_action('wp_ajax_frm_export_xml', 'FrmXMLController::export_xml');
    }
    
    public static function menu() {
        add_submenu_page('formidable', 'Formidable | Import/Export', 'Import/Export', 'frm_edit_forms', 'formidable-import', 'FrmXMLController::route');
    }
    
    public static function add_default_templates() {
        if ( !function_exists( 'libxml_disable_entity_loader' ) ){
    		// XML import is not enabled on your server
    		return;
    	}
    		
        include_once(FrmAppHelper::plugin_path() .'/classes/helpers/FrmXMLHelper.php');
            
        $set_err = libxml_use_internal_errors(true);
        $loader = libxml_disable_entity_loader( true );
        
        $files = apply_filters('frm_default_templates_files', array(FrmAppHelper::plugin_path() .'/classes/views/xml/default-templates.xml'));
        
        foreach ( (array) $files as $file) {
            $result = FrmXMLHelper::import_xml($file);
            unset($file);
        }
        if(is_wp_error($result))
            $errors[] = $result->get_error_message();
        else if($result)
            $message = $result;
            
        unset($files);
            
        libxml_use_internal_errors( $set_err );
    	libxml_disable_entity_loader( $loader );
    }
    
    public static function route() {
        $action = isset($_REQUEST['frm_action']) ? 'frm_action' : 'action';
        $action = FrmAppHelper::get_param($action);
        if($action == 'import_xml') {
            return self::import_xml();
        } else if($action == 'export_xml') {
            return self::export_xml();
        } else {
            if ( apply_filters('frm_xml_route', true, $action) ){
                return self::form();
            }
        }
    }
    
    public static function form($errors = array(), $message = '') {
        //wp_enqueue_script('jquery-chosen');
        //wp_enqueue_style('formidable');
        
        $frm_form = new FrmForm();
        $forms = $frm_form->getAll("status is NULL OR status = '' OR status = 'published'", ' ORDER BY name');
        unset($frm_form);
        
        $export_types = apply_filters('frm_xml_export_types', 
            array('forms' => __('Forms', 'formidable'))
        );
        
        $export_format = apply_filters('frm_export_formats', array( 
            'xml' => array( 'name' => 'XML', 'support' => 'forms', 'count' => 'multiple'),
        ));
        
        global $frmpro_settings;
        $csv_format = $frmpro_settings ? $frmpro_settings->csv_format : 'UTF-8';
        
        include(FrmAppHelper::plugin_path() .'/classes/views/xml/import_form.php');
    }
    
    public static function import_xml() {
        $errors = array();
        $message = '';
        
        if ( !current_user_can('frm_edit_forms') || ! isset($_POST['import-xml']) || ! wp_verify_nonce($_POST['import-xml'], 'import-xml-nonce') ) {
            global $frm_settings;
            $errors[] = $frm_settings->admin_permission;
            self::form($errors);
            return;
        }
        
        if ( !isset($_FILES) || !isset($_FILES['frm_import_file']) || empty($_FILES['frm_import_file']['name']) || (int)$_FILES['frm_import_file']['size'] < 1) {
            $errors[] = __( 'Oops, you didn\'t select a file.', 'formidable' );
            self::form($errors);
            return;
        }
        
        $file = $_FILES['frm_import_file']['tmp_name'];
        
        if ( !is_uploaded_file($file) ) {
            unset($file);
            $errors[] = __( 'The file does not exist, please try again.', 'formidable' );
            self::form($errors);
            return;
        }
        
        //add_filter('upload_mimes', 'FrmXMLController::allow_mime');
        
        $export_format = apply_filters('frm_export_formats', array( 
            'xml' => array( 'name' => 'XML', 'support' => 'forms', 'count' => 'multiple'),
        ));
        
        $file_type = strtolower(pathinfo($_FILES['frm_import_file']['name'], PATHINFO_EXTENSION));
        if ( $file_type != 'xml' && isset($export_format[$file_type]) ) {
            // allow other file types to be imported
            do_action('frm_before_import_'. $file_type );
            return;
        }
        unset($file_type);
        
        //$media_id = FrmProAppHelper::upload_file('frm_import_file');
        //if(is_numeric($media_id)){
            
            if ( !function_exists( 'libxml_disable_entity_loader' ) ) {
        		$errors[] = __('XML import is not enabled on your server.', 'formidable');
        		self::form($errors);
        		return;
        	}
        	
            include_once(FrmAppHelper::plugin_path() .'/classes/helpers/FrmXMLHelper.php');
            
            $set_err = libxml_use_internal_errors(true);
            $loader = libxml_disable_entity_loader( true );
            
            $result = FrmXMLHelper::import_xml($file);
            if ( is_wp_error($result) ) {
                $errors[] = $result->get_error_message();
            } else if ( $result ) {
                if ( is_array($result) ) {
                    $t_strings = array(
                        'imported'  => __('Imported', 'formidable'),
                        'updated'   => __('Updated', 'formidable'),
                    );
                    
                    $message = '<ul>';
                    foreach ( $result as $type => $results ) {
                        if ( !isset($t_strings[$type]) ) {
                            // only print imported and updated
                            continue;
                        }
                        
                        $s_message = array();
                        foreach ( $results as $k => $m ) {
                            if ( $m ) {
                                $strings = array(
                                    'forms'     => sprintf(_n( '%1$s Form', '%1$s Forms', $m, 'formidable' ), $m ),
                                    'fields'    => sprintf(_n( '%1$s Field', '%1$s Fields', $m, 'formidable' ), $m),
                                    'items'     => sprintf(_n( '%1$s Entry', '%1$s Entries', $m, 'formidable' ), $m),
                                    'views'     => sprintf(_n( '%1$s View', '%1$s Views', $m, 'formidable' ), $m),
                                    'posts'     => sprintf(_n( '%1$s Post', '%1$s Posts', $m, 'formidable' ), $m),
                                    'terms'     => sprintf(_n( '%1$s Term', '%1$s Terms', $m, 'formidable' ), $m),
                                );
                                
                                $s_message[] = isset($strings[$k]) ? $strings[$k] : $t_strings[$type] .' '. $m .' '. ucfirst($k);
                            }
                            unset($k);
                            unset($m);
                        }
                        
                        if ( !empty($s_message) ) {
                            $message .= '<li><strong>'. $t_strings[$type] .':</strong> ';
                            $message .= implode(', ', $s_message);
                            $message .= '</li>';
                        }
                        
                    }
                    
                    if ( $message == '<ul>' ) {
                        $message = '';
                        $errors[] = __('Nothing was imported or updated', 'formidable');
                    } else {
                        $message .= '</ul>';
                    }
                } else {
                    $message = $result;
                }
            }
            
            unset($file);
            
            libxml_use_internal_errors( $set_err );
        	libxml_disable_entity_loader( $loader );
        //}else{
        //    foreach ($media_id->errors as $error)
        //        echo $error[0];
        //}
        
        self::form($errors, $message);
    }
    
    public static function export_xml() {
        if ( !current_user_can('frm_edit_forms') ) {
            global $frm_settings;
            echo $frm_settings->admin_permission;
            die();
        }
            
        if (isset($_POST['frm_export_forms'])) {
            $ids = $_POST['frm_export_forms'];
        } else {
            $ids = array();
        }
            
        if ( isset($_POST['type']) ){
            $type = $_POST['type'];
        }
        
        $format = isset($_POST['format']) ? $_POST['format'] : 'xml';
            
        if ( !headers_sent() && (!isset($type) || !$type) ) {
            wp_redirect(admin_url('admin.php?page=formidable-import'));
            die();
        }
        
        if ( $format == 'xml' ) {
            self::generate_xml($type, compact('ids'));
        } else {
            do_action('frm_export_format_'. $format, compact('ids'));
        }
        
        die();
    }
    
    public static function export_xml_direct($controller = 'forms', $ids = false) {
        if ( !current_user_can('frm_edit_forms') ) {
            global $frm_settings;
            wp_die($frm_settings->admin_permission);
        }
        $is_template = FrmAppHelper::get_param('is_template', false);
        self::generate_xml($controller, compact('ids', 'is_template'));
        die();
    }
    
    public static function generate_xml($type, $args = array() ) {
    	global $wpdb;
	    
	    $type = (array)$type;
	    $tables = array(
	        'items' => $wpdb->prefix .'frm_items',
	        'forms' => $wpdb->prefix .'frm_forms',
	        'views' => $wpdb->posts 
	    );
	        
	    $defaults = array('ids' => false);
	    $args = wp_parse_args( $args, $defaults );
    	
        $sitename = sanitize_key( get_bloginfo( 'name' ) );
    	    
    	if ( ! empty($sitename) ) $sitename .= '.';
    	$filename = $sitename . 'formidable.' . date( 'Y-m-d' ) . '.xml';

    	header( 'Content-Description: File Transfer' );
    	header( 'Content-Disposition: attachment; filename=' . $filename );
    	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
        
        //make sure ids are numeric
    	if(is_array($args['ids']) && !empty($args['ids']))
	        $args['ids'] = implode(',', array_filter( $args['ids'], 'is_numeric' ));
	    
	    $records = array();
	    
	    foreach($type as $tb_type){
            $where = $join = '';
            $table = $tables[$tb_type];
            
            $select = "$table.id";

            if($tb_type == 'forms'){
                //add forms
                $where = $wpdb->prepare( "$table.status != %s" , 'draft' );
                if ( $args['ids'] )
            	    $where .= " AND $table.id IN (". $args['ids'] .")";

            } else if($tb_type == 'items') {
                //$join = "INNER JOIN {$wpdb->prefix}frm_item_metas im ON ($table.id = im.item_id)";
                if ( $args['ids'] ) {
                    $where = "$table.form_id IN (". $args['ids'] .")";
                }
            } else {
                $select = "$table.ID";
                $join = "INNER JOIN $wpdb->postmeta pm ON (pm.post_id=$table.ID)";
                $where = "pm.meta_key='frm_form_id' AND pm.meta_value ";
                if ( empty($args['ids']) ) {
                    $where .= "> 0";
                } else {
                    $where .= "IN (". $args['ids'] .")";
                }
            }

            if(!empty($where))
                $where = "WHERE ". $where;
            
            $records[$tb_type] = $wpdb->get_col( "SELECT $select FROM $table $join $where" );
            unset($tb_type);
        }
        
        include_once(FrmAppHelper::plugin_path() .'/classes/helpers/FrmXMLHelper.php');

        $frm_field = new FrmField();
        
        echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";
        include(FrmAppHelper::plugin_path() .'/classes/views/xml/xml.php');
    }
    
    function allow_mime($mimes) {
        if ( !isset($mimes['csv']) ) {
            // allow csv files
            $mimes['csv'] = 'text/csv';
        }
        
        if ( !isset($mimes['xml']) ) {
            // allow xml
            $mimes['xml'] = 'text/xml';
        }

        return $mimes;
    }
    
}