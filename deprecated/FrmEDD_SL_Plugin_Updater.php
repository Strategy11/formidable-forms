<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allows plugins to use their own update API.
 *
 * @author Easy Digital Downloads
 * @version 1.6.15
 */
class FrmEDD_SL_Plugin_Updater {

	/**
	 * Class constructor.
	 *
	 * @uses plugin_basename()
	 * @uses hook()
	 *
	 * @param string  $_api_url     The URL pointing to the custom API endpoint.
	 * @param string  $_plugin_file Path to the plugin file.
	 * @param array   $_api_data    Optional data to send with API calls.
	 */
	public function __construct( $_api_url, $_plugin_file, $_api_data = array() ) {
		_deprecated_function( __METHOD__, '6.16.1' );
	}

	/**
	 * Set up WordPress filters to hook into WP's update process.
	 *
	 * @uses add_filter()
	 *
	 * @return void
	 */
	public function init() {
		_deprecated_function( __METHOD__, '6.16.1' );
	}

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update API just when WordPress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native WordPress plugin update code.
	 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
	 *
	 * @uses api_request()
	 *
	 * @param array   $_transient_data Update array build by WordPress.
	 *
	 * @return stdClass Modified update array with custom plugin data.
	 */
	public function check_update( $_transient_data ) {
		_deprecated_function( __METHOD__, '6.16.1' );
		if ( ! is_object( $_transient_data ) ) {
			$_transient_data = new stdClass();
		}
		return $_transient_data;
	}

	/**
	 * Updates information on the "View version 6.16.1 details" page with custom data.
	 *
	 * @uses api_request()
	 *
	 * @param mixed   $_data
	 * @param string  $_action
	 * @param object  $_args
	 * @return object $_data
	 */
	public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
		_deprecated_function( __METHOD__, '6.16.1' );
		return $_data;
	}

	/**
	 * Disable SSL verification in order to prevent download update failures
	 *
	 * @param array   $args
	 * @param string  $url
	 *
	 * @return array $array
	 */
	public function http_request_args( $args, $url ) {
		_deprecated_function( __METHOD__, '6.16.1' );
		return $args;
	}

	public function show_changelog() {
	}

	public function get_cached_version_info( $cache_key = '' ) {
		_deprecated_function( __METHOD__, '6.16.1' );
		return false;
	}

	public function set_version_info_cache( $value = '', $cache_key = '' ) {
		_deprecated_function( __METHOD__, '6.16.1' );
	}

}
