<?php
/**
 * GDPR field helper
 *
 * @since 6.19
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmFieldGdprHelper
 */
class FrmFieldGdprHelper {

	/**
	 * Field type
	 *
	 * @since 6.19
	 * @var string
	 */
	const FIELD_TYPE = 'gdpr';

	/**
	 * Field class
	 *
	 * @since 6.19
	 * @var string
	 */
	const FIELD_CLASS = 'FrmFieldGdpr';

	/**
	 * Hide GDPR field
	 *
	 * @since 6.19
	 * @return bool
	 */
	public static function hide_gdpr_field() {
		$settings = FrmAppHelper::get_settings();
		return ! $settings->enable_gdpr;
	}

	/**
	 * Add GDPR field to form builder
	 *
	 * @since 6.19
	 * @param array $fields
	 * @return array
	 */
	public static function add_gdpr_field( $fields ) {
		$fields[ self::FIELD_TYPE ] = array(
			'name' => __( 'GDPR', 'formidable' ),
			'icon' => 'frm_icon_font frm-gdpr-icon',
		);
		return $fields;
	}

	/**
	 * Initialize GDPR field Class name
	 *
	 * @since 6.19
	 * @param string $field_type
	 * @return string
	 */
	public static function get_gdpr_field_class( $field_type = '' ) {
		if ( self::FIELD_TYPE === $field_type ) {
			return self::FIELD_CLASS;
		}
		return '';
	}
}
