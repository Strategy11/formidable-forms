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
 * @since 6.7
 */
class FrmFormTemplatesHelper {

	/**
	 * Updates template array with additional details and URL.
	 *
	 * @param array  $template Template data.
	 * @param string $pricing Upgrade link URL.
	 * @param string $license_type License type.
	 */
	public static function prepare_template_details( &$template, $pricing, $license_type ) {
		$template['is_featured']   = ! empty( $template['is_featured'] );
		$template['is_favorite']   = ! empty( $template['is_favorite'] );
		$template['is_custom']     = ! empty( $template['is_custom'] );
		$template['plan_required'] = FrmFormsHelper::get_plan_required( $template );

		if ( ! empty( $template['name'] ) ) {
			$template['name'] = $template['is_custom'] ? $template['name'] : preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] );
		} else {
			$template['name'] = '';
		}

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
	 * @since 6.7
	 *
	 * @param array $template The template data.
	 * @param bool  $expired Whether the API request is expired or not.
	 * @return void
	 */
	public static function add_template_attributes( $template, $expired ) {
		$attributes = array(
			'tabindex'        => '0',
			'data-id'         => $template['id'],
			'frm-search-text' => strtolower( $template['name'] ),
		);

		// Set 'data-slug' attribute.
		if ( ! empty( $template['slug'] ) ) {
			$attributes['data-slug'] = $template['slug'];
		}

		// Set 'data-categories' attribute.
		if ( ! empty( $template['category_slugs'] ) ) {
			$attributes['data-categories'] = implode( ',', $template['category_slugs'] );
		}

		$attributes['class'] = self::prepare_single_template_classes( $template );
		self::prepare_single_template_plan( $template, $expired, $attributes );

		FrmAppHelper::array_to_html_params( $attributes, true );
	}

	/**
	 * Add classes for a given template.
	 *
	 * @since 6.7
	 *
	 * @param array $template The template data.
	 * @return string
	 */
	private static function prepare_single_template_classes( $template ) {
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

		return implode( ' ', $class_names );
	}

	/**
	 * Add info about the required plan for this template.
	 *
	 * @since 6.7
	 *
	 * @param array $template The template data.
	 * @param bool  $expired Whether the license is expired.
	 * @param array $attributes The template attributes.
	 * @return void
	 */
	private static function prepare_single_template_plan( $template, $expired, &$attributes ) {
		if ( ! $template['plan_required'] ) {
			return;
		}

		$required_plan_slug               = sanitize_title( $template['plan_required'] );
		$attributes['data-required-plan'] = $expired && 'free' !== $required_plan_slug ? 'renew' : $required_plan_slug;
		if ( 'free' === $required_plan_slug ) {
			$attributes['data-key'] = $template['key'];
		}

		$attributes['class'] .= ' frm-form-templates-locked-item frm-' . esc_attr( $required_plan_slug ) . '-template';
	}

	/**
	 * Echo attributes for the link to view a template.
	 *
	 * @since 6.7
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

	/**
	 * Show the CTA to upgrade or renew.
	 *
	 * @since 6.7
	 *
	 * @param array $args {
	 *    Arguments for the CTA.
	 *
	 *    @type string $upgrade_link Upgrade link URL.
	 *    @type string $renew_link Renew link URL.
	 * }
	 * @return void
	 */
	public static function show_upgrade_renew_cta( $args ) {
		// Show 'renew' banner for expired users.
		if ( $args['expired'] ) {
			FrmTipsHelper::show_admin_cta(
				array(
					'title'       => esc_html__( 'Get Super Powers with Pre-built Forms', 'formidable' ),
					'description' => esc_html__( 'Unleash the potential of hundreds of form templates and save precious time. Renew today for unparalleled form-building speed.', 'formidable' ),
					'link_text'   => esc_html__( 'Renew Now', 'formidable' ),
					'link_url'    => $args['renew_link'],
					'id'          => 'frm-renew-subscription-banner',
				)
			);
			return;
		}

		// Show 'upgrade' banner for non-elite users.
		if ( ! in_array( FrmAddonsController::license_type(), array( 'elite', 'business' ), true ) ) {
			FrmTipsHelper::show_admin_cta(
				array(
					'title'       => sprintf(
						/* translators: %1$s: Open span tag, %2$s: Close span tag */
						esc_html__( 'Get Super Powers with %1$s%2$s More Pre-built Forms', 'formidable' ) . ' 🦸',
						'<span class="frm-form-templates-extra-templates-count">',
						'</span>'
					),
					'description' => esc_html__( 'Unleash the potential of hundreds of additional form templates and save precious time. Upgrade today for unparalleled form-building capabilities.', 'formidable' ),
					'link_text'   => esc_html__( 'Upgrade to PRO', 'formidable' ),
					'link_url'    => $args['upgrade_link'],
					'id'          => 'frm-upgrade-banner',
				)
			);
		}
	}
}
