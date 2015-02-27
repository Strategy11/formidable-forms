<?php

// 1.07.02
define('FRM_PATH', dirname( __FILE__ ));

global $frm_siteurl; //deprecated: use FrmAppHelper::site_url()
$frm_siteurl = site_url();
if(is_ssl() and (!preg_match('/^https:\/\/.*\..*$/', $frm_siteurl) or !preg_match('/^https:\/\/.*\..*$/', WP_PLUGIN_URL))){
    $frm_siteurl = str_replace('http://', 'https://', $frm_siteurl);
    define('FRM_URL', str_replace('http://', 'https://', WP_PLUGIN_URL.'/formidable'));
}else{
    define('FRM_URL', WP_PLUGIN_URL.'/formidable');  //deprecated: use FrmAppHelper::plugin_url()
}

global $frm_version, $frm_ajax_url;
$frm_version = FrmAppHelper::plugin_version(); //deprecated: use FrmAppHelper::plugin_version()
$frm_ajax_url = admin_url('admin-ajax.php');

global $frmpro_is_installed;
$frmpro_is_installed = FrmAppController::pro_is_installed();

// 1.07.05
if ( class_exists('FrmProEntry') ) {
    global $frmpro_entry;
    global $frmpro_entry_meta;
    global $frmpro_field;
    global $frmpro_form;

    $frmpro_entry       = new FrmProEntry();
    $frmpro_entry_meta  = new FrmProEntryMeta();
    $frmpro_field       = new FrmProField();
    $frmpro_form        = new FrmProForm();
    
    new FrmProDb();
}