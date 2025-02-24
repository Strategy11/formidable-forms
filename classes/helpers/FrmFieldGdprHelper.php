<?php
/**
 * GDPR field helper
 *
 * @since x.x
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
	 * @since x.x
	 * @var string
	 */
	const FIELD_TYPE = 'gdpr';

	/**
	 * Field class
	 *
	 * @since x.x
	 * @var string
	 */
	const FIELD_CLASS = 'FrmFieldGdpr';

	/**
	 * Load hooks
	 *
	 * @since x.x
	 * @return void
	 */
	public static function load_hooks() {
		$settings = FrmAppHelper::get_settings();
		if ( $settings->enable_gdpr ) {
			add_filter( 'frm_available_fields', array( __CLASS__, 'add_gdpr_field' ), 10, 1 );
			add_filter( 'frm_get_field_type_class', array( __CLASS__, 'init_gdpr_field_class' ), 10, 2 );
		}
	}

	/**
	 * Add GDPR field to form builder
	 *
	 * @since x.x
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
	 * @since x.x
	 * @param string $class
	 * @param string $field_type
	 * @return string
	 */
	public static function init_gdpr_field_class( $class, $field_type = '' ) {
		if ( self::FIELD_TYPE === $field_type ) {
			return self::FIELD_CLASS;
		}
		return $class;
	}
}
