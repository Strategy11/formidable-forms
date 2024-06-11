<?php
/**
 * Plugin Name: Formidable Forms
 * Description: Quickly and easily create drag-and-drop forms
 * Version: 6.10
 * Plugin URI: https://formidableforms.com/
 * Author URI: https://formidableforms.com/
 * Author: Strategy11 Form Builder Team
 * Text Domain: formidable
 *
 * @package Formidable
 */

/*
	Copyright 2010  Formidable Forms

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

add_action( 'plugins_loaded', 'load_formidable_forms', 0 );
/**
 * @return void
 */
function load_formidable_forms() {
	global $frm_vars;
	$frm_vars = array(
		'load_css'          => false,
		'forms_loaded'      => array(),
		'created_entries'   => array(),
		'pro_is_authorized' => false,
	);

	// For reverse compatibility. Load Pro if it's still nested.
	$frm_path = __DIR__;
	if ( file_exists( $frm_path . '/pro/formidable-pro.php' ) ) {
		include $frm_path . '/pro/formidable-pro.php';
	}

	FrmHooksController::trigger_load_hook();
}

// If __autoload is active, put it on the spl_autoload stack.
if ( is_array( spl_autoload_functions() ) && in_array( '__autoload', spl_autoload_functions(), true ) ) {
	spl_autoload_register( '__autoload' );
}

// Add the autoloader.
spl_autoload_register( 'frm_forms_autoloader' );

/**
 * @return void
 */
function frm_forms_autoloader( $class_name ) {
	// Only load Frm classes here.
	if ( ! preg_match( '/^Frm.+$/', $class_name ) || preg_match( '/^FrmPro.+$/', $class_name ) ) {
		return;
	}

	frm_class_autoloader( $class_name, __DIR__ );
}

/**
 * Autoload the Formidable and Pro classes
 *
 * @since 3.0
 *
 * @return void
 */
function frm_class_autoloader( $class_name, $filepath ) {
	$deprecated        = array( 'FrmEDD_SL_Plugin_Updater' );
	$is_deprecated     = in_array( $class_name, $deprecated, true ) || preg_match( '/^.+Deprecate/', $class_name );
	$original_filepath = $filepath;

	if ( $is_deprecated ) {
		$filepath .= '/deprecated/';
	} else {
		$filepath .= '/classes/';
		if ( preg_match( '/^.+Helper$/', $class_name ) ) {
			$filepath .= 'helpers/';
		} elseif ( preg_match( '/^.+Controller$/', $class_name ) ) {
			$filepath .= 'controllers/';
		} elseif ( preg_match( '/^.+Factory$/', $class_name ) ) {
			$filepath .= 'factories/';
		} else {
			$filepath .= 'models/';
			if ( strpos( $class_name, 'Field' ) && ! file_exists( $filepath . $class_name . '.php' ) ) {
				$filepath .= 'fields/';
			}
		}
	}

	if ( file_exists( $filepath . $class_name . '.php' ) ) {
		require $filepath . $class_name . '.php';
		return;
	}

	if ( ! preg_match( '/^FrmStrpLite.+$/', $class_name ) && ! preg_match( '/^FrmTransLite.+$/', $class_name ) ) {
		// Exit early if the class does not match the Stripe Lite prefix.
		return;
	}

	// Autoload for /stripe/ folder.
	$filepath = $original_filepath . '/stripe/';
	if ( preg_match( '/^.+Helper$/', $class_name ) ) {
		$filepath .= 'helpers/';
	} elseif ( preg_match( '/^.+Controller$/', $class_name ) ) {
		$filepath .= 'controllers/';
	} else {
		$filepath .= 'models/';
	}

	$filepath .= $class_name . '.php';

	if ( file_exists( $filepath ) ) {
		require $filepath;
	}
}

add_action( 'activate_' . FrmAppHelper::plugin_folder() . '/formidable.php', 'frm_maybe_install' );

/**
 * This function is triggered when Formidable is activated.
 *
 * @return void
 */
function frm_maybe_install() {
	if ( get_transient( FrmOnboardingWizardController::TRANSIENT_NAME ) !== 'no' ) {
		set_transient(
			FrmOnboardingWizardController::TRANSIENT_NAME,
			FrmOnboardingWizardController::TRANSIENT_VALUE,
			60
		);
	}

	FrmAppController::handle_activation();
}

register_deactivation_hook(
	__FILE__,
	function () {
		if ( ! class_exists( 'FrmCronController', false ) ) {
			require_once __DIR__ . '/classes/controllers/FrmCronController.php';
		}

		FrmCronController::remove_crons();
	}
);
