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
			$unique_element_atts['disabled']     = '1';
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
			$read_only_element_atts['data-upgrade'] = __( 'Read only fields', 'formidable' );
			$read_only_element_atts['disabled']     = '1';
		}

		return $read_only_element_atts;
	}

	/**
	 * @since x.x
	 *
	 * @param array  $atts
	 * @param string $utm_content
	 * @param string $upgrade_text
	 * @param string $kb_slug
	 *
	 * @return array
	 */
	public static function add_upgrade_modal_atts( $atts, $utm_content, $upgrade_text, $kb_slug = '' ) {
		if ( empty( $atts['class'] ) ) {
			$atts['class'] = '';
		}

		$atts['class']          .= ' frm_show_upgrade';
		$atts['data-medium']     = 'lite';
		$atts['data-content']    = $utm_content;
		$atts['data-upgrade']    = $upgrade_text;
		$atts['data-learn-more'] = 'https://formidableforms.com/knowledgebase' . $kb_slug;

		return $atts;
	}
}
