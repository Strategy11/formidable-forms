<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Used from template view files.
 *
 * @since x.x
 */
class FrmFormTemplatesHelper {

	/**
	 * Echo attributes for the link to view a template.
	 *
	 * @since x.x
	 *
	 * @param array $template The template data.
	 * @return void
	 */
	public static function add_link_attributes( $template ) {
		$view_demo_attributes = array(
			'class' => 'button button-secondary frm-button-secondary frm-small',
			'role'  => 'button',
			'href'  => $template['link'],
		);

		$is_custom_template = ! empty( $template['is_custom'] );
		if ( ! $is_custom_template ) {
			$utm = array(
				'medium'  => 'form-templates',
				'content' => $template['slug'],
			);

			$view_demo_attributes['href']   = FrmAppHelper::admin_upgrade_link( $utm, $view_demo_attributes['href'] );
			$view_demo_attributes['target'] = '_blank';
		}
		FrmAppHelper::array_to_html_params( $view_demo_attributes, true );
	}
}
