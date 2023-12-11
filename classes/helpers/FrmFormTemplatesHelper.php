<?php
/**
 * Form Templates Helper class.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Provides helper functions for managing form templates in the admin area.
 *
 * @since x.x
 */
class FrmFormTemplatesHelper {

	/**
	 * Updates template array with additional details and URL.
	 *
	 * @param array &$template Template data.
	 * @param string $pricing Upgrade link URL.
	 * @param string $license_type License type.
	 */
	public static function update_template_details( &$template, $pricing, $license_type ) {
		$template['is_featured']   = ! empty( $template['is_featured'] );
		$template['is_favorite']   = ! empty( $template['is_favorite'] );
		$template['is_custom']     = ! empty( $template['is_custom'] );
		$template['plan_required'] = FrmFormsHelper::get_plan_required( $template );

		$template['name'] = $template['is_custom']
						? $template['name']
						: preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] );

		$template['use_template'] = '#';
		if ( $template['is_custom'] ) {
			$template['use_template'] = $template['url'];
		} elseif ( ! $template['plan_required'] ) {
			$link = FrmFormsHelper::get_template_install_link( $template, compact( 'pricing', 'license_type' ) );

			$template['use_template'] = esc_url( $link['url'] );
		}
	}

	/**
	 * Echo attributes for a given template.
	 *
	 * @since x.x
	 *
	 * @param array $template The template data.
	 * @param bool $expired Whether the API request is expired or not.
	 * @return void
	 */
	public static function add_template_attributes( $template, $expired ) {
		$attributes                    = array();
		$attributes['tabindex']        = '0';
		$attributes['data-id']         = $template['id'];
		$attributes['frm-search-text'] = strtolower( $template['name'] );

		// Set 'data-slug' attribute.
		if ( ! empty( $template['slug'] ) ) {
			$attributes['data-slug'] = $template['slug'];
		}

		// Set 'data-categories' attribute.
		if ( ! empty( $template['category_slugs'] ) ) {
			$attributes['data-categories'] = implode( ',', $template['category_slugs'] );
		}

		// Set 'class' attribute.
		$class_names = array( 'frm-card-item' );
		if ( $template['is_featured'] ) {
			$class_names[] = 'frm-form-templates-featured-item';
		}
		if ( $template['is_favorite'] ) {
			$class_names[] = 'frm-form-templates-favorite-item';
		}
		if ( $template['is_custom'] ) {
			$class_names[] = 'frm-form-templates-custom-item';
		}

		// Handle attributes related to plan requirements.
		if ( $template['plan_required'] ) {
			$required_plan_slug               = sanitize_title( $template['plan_required'] );
			$attributes['data-required-plan'] = $expired && 'free' !== $required_plan_slug ? 'renew' : $required_plan_slug;
			if ( 'free' === $required_plan_slug ) {
				$attributes['data-key'] = $template['key'];
			}
			$class_names[] = 'frm-form-templates-locked-item frm-' . esc_attr( $required_plan_slug ) . '-template';
		}

		$attributes['class'] = implode( ' ', $class_names );

		FrmAppHelper::array_to_html_params( $attributes, true );
	}

	/**
	 * Echo attributes for the link to view a template.
	 *
	 * @since x.x
	 *
	 * @param array $template The template data.
	 * @return void
	 */
	public static function add_template_link_attributes( $template ) {
		$attributes = array(
			'class' => 'button button-secondary frm-button-secondary frm-small',
			'role'  => 'button',
			'href'  => $template['link'],
		);

		if ( ! $template['is_custom'] ) {
			$utm = array(
				'medium'  => 'form-templates',
				'content' => $template['slug'],
			);

			$attributes['href']   = FrmAppHelper::admin_upgrade_link( $utm, $attributes['href'] );
			$attributes['target'] = '_blank';
		}

		FrmAppHelper::array_to_html_params( $attributes, true );
	}
}
