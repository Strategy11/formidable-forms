<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmWelcomeController {

	public static $menu_slug   = 'formidable-welcome';

	public static $option_name = 'frm_activation_redirect';

	/**
	 * Register all of the hooks related to the welcome screen functionality
	 *
	 * @access public
	 *
	 * @return void
	 */
	public static function load_hooks() {
		_deprecated_function( __METHOD__, 'x.x' );
	}

	/**
	 * Performs a safe (local) redirect to the welcome screen
	 * when the plugin is activated
	 *
	 * @deprecated x.x
	 * @return void
	 */
	public static function redirect() {
		_deprecated_function( __METHOD__, 'x.x' );
	}

	/**
	 * Add a submenu welcome screen for the formidable parent menu
	 *
	 * @deprecated x.x
	 * @return void
	 */
	public static function screen_page() {
		_deprecated_function( __METHOD__, 'x.x' );
	}

	/**
	 * Include html content for the welcome screem
	 *
	 * @deprecated x.x
	 * @return void
	 */
	public static function screen_content() {
		_deprecated_function( __METHOD__, 'x.x' );
	}

	/**
	 * Remove the welcome screen submenu page from the formidable parent menu
	 * since it is not necessary to show that link there
	 *
	 * @deprecated x.x
	 * @return void
	 */
	public static function remove_menu() {
		_deprecated_function( __METHOD__, 'x.x' );
	}

	/**
	 * Register the stylesheets for the welcome screen.
	 *
	 * @deprecated x.x
	 * @return void
	 */
	public static function enqueue_styles() {
		_deprecated_function( __METHOD__, 'x.x' );
	}

	/**
	 * Helps to confirm if the user is currently on the welcome screen
	 *
	 * @deprecated x.x
	 * @return bool
	 */
	public static function is_welcome_screen() {
		_deprecated_function( __METHOD__, 'x.x' );
		return false;
	}

	/**
	 * Build the admin URL link for the welcome screen
	 *
	 * @deprecated x.x
	 * @return string
	 */
	public static function settings_link() {
		_deprecated_function( __METHOD__, 'x.x' );
		return '';
	}

	/**
	 * @deprecated x.x
	 * @return void
	 */
	public static function upgrade_to_pro_button() {
		_deprecated_function( __METHOD__, 'x.x' );
	}

	/**
	 * @deprecated x.x
	 * @return void
	 */
	public static function maybe_show_license_box() {
		_deprecated_function( __METHOD__, 'x.x' );
	}

	/**
	 * @param string $plugin
	 * @param string $upgrade_link_args
	 *
	 * @deprecated x.x
	 * @return void
	 */
	public static function maybe_show_conditional_action_button( $plugin, $upgrade_link_args ) {
		_deprecated_function( __METHOD__, 'x.x' );
	}
}
