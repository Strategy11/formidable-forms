<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteAppHelper {

	/**
	 * @var FrmSquareLiteSettings|null
	 */
	private static $settings;

	/**
	 * @return string
	 */
	public static function plugin_path() {
		return FrmAppHelper::plugin_path() . '/square/';
	}

	/**
	 * @return string
	 */
	public static function plugin_folder() {
		return basename( self::plugin_path() );
	}

	/**
	 * @return string
	 */
	public static function plugin_url() {
		return FrmAppHelper::plugin_url() . '/square/';
	}

	/**
	 * @return FrmSquareLiteSettings
	 */
	public static function get_settings() {
		if ( ! isset( self::$settings ) ) {
			self::$settings = new FrmSquareLiteSettings();
		}
		return self::$settings;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'live'|'test'
	 */
	public static function active_mode() {
		$settings = self::get_settings();
		return $settings->settings->test_mode ? 'test' : 'live';
	}
}
