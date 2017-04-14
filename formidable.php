<?php
/*
Plugin Name: Formidable Forms
Description: Quickly and easily create drag-and-drop forms
Version: 2.03.06
Plugin URI: https://formidableforms.com/
Author URI: https://formidableforms.com/
Author: Strategy11
Text Domain: formidable
*/

/*  Copyright 2010  Formidable Forms

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

global $frm_vars;
$frm_vars = array(
    'load_css' => false, 'forms_loaded' => array(),
    'created_entries'   => array(),
    'pro_is_authorized' => false,
);

function frm_forms_autoloader( $class_name ) {
    // Only load Frm classes here
	if ( ! preg_match( '/^Frm.+$/', $class_name ) ) {
        return;
    }

    $filepath = dirname(__FILE__);
	if ( preg_match( '/^FrmPro.+$/', $class_name ) ) {
        $filepath .= '/pro';
    }
    $filepath .= '/classes';

	if ( preg_match( '/^.+Helper$/', $class_name ) ) {
        $filepath .= '/helpers/';
	} else if ( preg_match( '/^.+Controller$/', $class_name ) ) {
        $filepath .= '/controllers/';
    } else {
        $filepath .= '/models/';
    }

	$filepath .= $class_name . '.php';

    if ( file_exists($filepath) ) {
        include($filepath);
    }
}

// if __autoload is active, put it on the spl_autoload stack
if ( is_array(spl_autoload_functions()) && in_array( '__autoload', spl_autoload_functions()) ) {
    spl_autoload_register('__autoload');
}

// Add the autoloader
spl_autoload_register('frm_forms_autoloader');

$frm_path = dirname(__FILE__);
if ( file_exists($frm_path . '/pro/formidable-pro.php') ) {
	include( $frm_path . '/pro/formidable-pro.php' );
}

FrmHooksController::trigger_load_hook();

include_once( $frm_path . '/deprecated.php' );
unset($frm_path);
