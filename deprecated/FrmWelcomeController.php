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
		_deprecated_function( __METHOD__, '6.7' );
	}

	/**
	 * Performs a safe (local) redirect to the welcome screen
	 * when the plugin is activated
	 *
	 * @deprecated 6.7
	 * @return void
	 */
	public static function redirect() {
		_deprecated_function( __METHOD__, '6.7' );
	}

	/**
	 * Add a submenu welcome screen for the formidable parent menu
	 *
	 * @deprecated 6.7
	 * @return void
	 */
	public static function screen_page() {
		_deprecated_function( __METHOD__, '6.7' );
	}

	/**
	 * Include html content for the welcome screem
	 *
	 * @deprecated 6.7
	 * @return void
	 */
	public static function screen_content() {
		_deprecated_function( __METHOD__, '6.7' );
	}

	/**
	 * Remove the welcome screen submenu page from the formidable parent menu
	 * since it is not necessary to show that link there
	 *
	 * @deprecated 6.7
	 * @return void
	 */
	public static function remove_menu() {
		_deprecated_function( __METHOD__, '6.7' );
	}

	/**
	 * Register the stylesheets for the welcome screen.
	 *
	 * @deprecated 6.7
	 * @return void
	 */
	public static function enqueue_styles() {
		_deprecated_function( __METHOD__, '6.7' );
	}

	/**
	 * Helps to confirm if the user is currently on the welcome screen
	 *
	 * @deprecated 6.7
	 * @return bool
	 */
	public static function is_welcome_screen() {
		_deprecated_function( __METHOD__, '6.7' );
		return false;
	}

	/**
	 * Build the admin URL link for the welcome screen
	 *
	 * @deprecated 6.7
	 * @return string
	 */
	public static function settings_link() {
		_deprecated_function( __METHOD__, '6.7' );
		return '';
	}

	/**
	 * @deprecated 6.7
	 * @return void
	 */
	public static function upgrade_to_pro_button() {
		_deprecated_function( __METHOD__, '6.7' );
	}

	/**
	 * @deprecated 6.7
	 * @return void
	 */
	public static function maybe_show_license_box() {
		_deprecated_function( __METHOD__, '6.7' );
	}

	/**
	 * @param string $plugin
	 * @param string $upgrade_link_args
	 *
	 * @deprecated 6.7
	 * @return void
	 */
	public static function maybe_show_conditional_action_button( $plugin, $upgrade_link_args ) {
		_deprecated_function( __METHOD__, '6.7' );
	}
}
