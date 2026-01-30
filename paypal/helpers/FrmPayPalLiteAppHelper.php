<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteAppHelper {

	/**
	 * @var FrmPayPalLiteSettings|null
	 */
	private static $settings;

	/**
	 * @return string
	 */
	public static function plugin_path() {
		return FrmAppHelper::plugin_path() . '/paypal/';
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
		return FrmAppHelper::plugin_url() . '/paypal/';
	}

	/**
	 * @return FrmPayPalLiteSettings
	 */
	public static function get_settings() {
		if ( ! isset( self::$settings ) ) {
			self::$settings = new FrmPayPalLiteSettings();
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
	 * Add education about PayPal fees.
	 *
	 * @param string             $medium  Medium identifier for the tip (for example 'tip').
	 * @param array|false|string $gateway Gateway or list of gateways this applies to.
	 *
	 * @return void
	 */
	public static function fee_education( $medium = 'tip', $gateway = false ) {
		$license_type = FrmAddonsController::license_type();

		if ( in_array( $license_type, array( 'elite', 'business' ), true ) ) {
			return;
		}

		$classes = 'frm-light-tip show_paypal';

		if ( $gateway && ! array_intersect( (array) $gateway, array( 'paypal' ) ) ) {
			$classes .= ' frm_hidden';
		}

		FrmTipsHelper::show_tip(
			array(
				'link'  => array(
					'content' => 'paypal-fee',
					'medium'  => $medium,
				),
				'tip'   => 'Pay as you go pricing: 3% fee per-transaction + PayPal fees.',
				'call'  => __( 'Upgrade to save on fees.', 'formidable' ),
				'class' => $classes,
			),
			'p'
		);
	}
}
