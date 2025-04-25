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

	/**
	 * Square does not support the typical recurring payment repeat settings.
	 * Instead, we have the choice of the following "cadence" options.
	 *
	 * @return array
	 */
	public static function get_repeat_cadence_options() {
		return array(
			'DAILY'             => 'Daily',
			'WEEKLY'            => 'Weekly',
			'EVERY_TWO_WEEKS'   => 'Every Two Weeks',
			'THIRTY_DAYS'       => 'Every Thirty Days',
			'SIXTY_DAYS'        => 'Every Sixty Days',
			'NINETY_DAYS'       => 'Every Ninety Days',
			'MONTHLY'           => 'Monthly',
			'EVERY_TWO_MONTHS'  => 'Every Two Months',
			'QUARTERLY'         => 'Quarterly',
			'EVERY_FOUR_MONTHS' => 'Every Four Months',
			'EVERY_SIX_MONTHS'  => 'Every Six Months',
			'ANNUAL'            => 'Annual',
			'EVERY_TWO_YEARS'   => 'Every Two Years',
		);
	}
}
