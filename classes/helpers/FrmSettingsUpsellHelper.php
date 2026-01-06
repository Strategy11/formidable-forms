<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Helper class for displaying upsell controls.
 *
 * @since x.x
 */
class FrmSettingsUpsellHelper {
	/**
	 * @since x.x
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	public static function get_unique_element_atts( $field ) {
		$pro_is_installed    = FrmAppHelper::pro_is_installed();
		$unique_element_atts = array(
			'type'  => 'checkbox',
			'name'  => 'field_options[unique_' . $field['id'] . ']',
			'id'    => 'frm_uniq_field_' . $field['id'],
			'value' => '1',
			'class' => 'frm_mark_unique',
		);

		if ( ! empty( $field['unique'] ) ) {
			$unique_element_atts['checked'] = 'checked';
		}

		if ( ! $pro_is_installed ) {
			$unique_element_atts['data-upgrade'] = __( 'Unique fields', 'formidable' );
		}

		return $unique_element_atts;
	}

	/**
	 * @since x.x
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	public static function get_read_only_element_atts( $field ) {
		$pro_is_installed       = FrmAppHelper::pro_is_installed();
		$read_only_element_atts = array(
			'type'  => 'checkbox',
			'name'  => 'field_options[read_only_' . $field['id'] . ']',
			'id'    => 'frm_read_only_field_' . $field['id'],
			'value' => '1',
		);

		if ( ! empty( $field['read_only'] ) ) {
			$read_only_element_atts['checked'] = 'checked';
		}

		if ( ! $pro_is_installed ) {
			$read_only_element_atts['data-upgrade'] = __( 'Unique fields', 'formidable' );
		}

		return $read_only_element_atts;
	}
}
