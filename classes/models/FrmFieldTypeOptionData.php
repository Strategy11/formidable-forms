<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * This class keeps a static record of field type options for each type.
 * The result is stored in memory so it can be re-used.
 *
 * @since 6.9.1
 */
class FrmFieldTypeOptionData {

	private static $data = array();

	/**
	 * @param string $type
	 * @return array
	 */
	public static function get_field_types( $type ) {
		if ( ! isset( self::$data[ $type ] ) ) {
			self::$data[ $type ] = FrmFieldsHelper::get_field_types( $type );
		}
		return self::$data[ $type ];
	}
}
