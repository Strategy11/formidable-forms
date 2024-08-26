<?php
/**
 * Onboarding Wizard Helper class.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Provides helper functions for managing onboarding wizard in the admin area.
 *
 * @since 6.9
 */
class FrmOnboardingWizardHelper {

	/**
	 * Echo attributes for the addon's label tag.
	 *
	 * @since 6.9
	 *
	 * @param string $addon_key The key of addon.
	 * @param array  $addon     The array of addon's information.
	 * @return void
	 */
	public static function add_addon_label_attributes( $addon_key, $addon ) {
		$id         = 'frm-onboarding-' . $addon_key . '-addon';
		$attributes = array(
			'for'        => $id,
			'class'      => 'frm-option-box',
			'data-title' => $addon['title'],
		);

		if ( ! empty( $addon['is-checked'] ) ) {
			$attributes['class'] .= ' frm-checked';
		}
		if ( ! empty( $addon['is-disabled'] ) ) {
			$attributes['class'] .= ' frm-disabled';
		}
		if ( ! empty( $addon['rel'] ) ) {
			$attributes['rel'] = $addon['rel'];
		}
		if ( ! empty( $addon['is-vendor'] ) ) {
			$attributes['data-is-vendor'] = 'true';
		}

		FrmAppHelper::array_to_html_params( $attributes, true );
	}

	/**
	 * Echo attributes for the addon's input tag.
	 *
	 * @since 6.9
	 *
	 * @param string $addon_key The key of addon.
	 * @param array  $addon     The array of addon's information.
	 * @return void
	 */
	public static function add_addon_input_attributes( $addon_key, $addon ) {
		$id         = 'frm-onboarding-' . $addon_key . '-addon';
		$attributes = array(
			'type' => 'checkbox',
			'name' => $id,
			'id'   => $id,
		);

		if ( ! empty( $addon['is-checked'] ) ) {
			$attributes['checked'] = 'checked';
		}
		if ( ! empty( $addon['is-disabled'] ) ) {
			$attributes['disabled'] = 'disabled';
		}

		FrmAppHelper::array_to_html_params( $attributes, true );
	}

	/**
	 * Renders the Onboarding Wizard page footer in the WordPress admin area.
	 *
	 * @since 6.9
	 *
	 * @param array $args
	 * @return void
	 */
	public static function print_footer( $args = array() ) {
		$defaults = array(
			'footer-class'               => '',
			'display-back-button'        => true,
			// Primary Button Args.
			'primary-button-text'        => esc_html__( 'Next Step', 'formidable' ),
			'primary-button-class'       => '',
			'primary-button-href'        => '#',
			'primary-button-role'        => 'button',
			// Secondary Button Args.
			'secondary-button-text'      => esc_html__( 'Skip', 'formidable' ),
			'secondary-button-class'     => '',
			'secondary-button-href'      => '#',
			'secondary-button-role'      => 'button',
			'secondary-button-skip-step' => true,
		);
		$args     = wp_parse_args( $args, $defaults );

		// Set the primary button attributes.
		$primary_button_attributes          = array(
			'href' => $args['primary-button-href'],
		);
		$primary_button_attributes['class'] = trim( 'button button-primary frm-button-primary ' . $args['primary-button-class'] );
		if ( ! empty( $args['primary-button-id'] ) ) {
			$primary_button_attributes['id'] = $args['primary-button-id'];
		}
		if ( ! empty( $args['primary-button-plugin'] ) ) {
			$primary_button_attributes['data-plugin'] = $args['primary-button-plugin'];
		}
		if ( ! empty( $args['primary-button-role'] ) ) {
			$primary_button_attributes['role'] = $args['primary-button-role'];
		}

		// Set the secondary button attributes.
		$secondary_button_attributes          = array(
			'href' => $args['secondary-button-href'],
		);
		$secondary_button_attributes['class'] = trim( 'button button-secondary frm-button-secondary ' . $args['secondary-button-class'] );
		if ( $args['secondary-button-skip-step'] ) {
			$secondary_button_attributes['class'] .= ' frm-onboarding-skip-step';
		}
		if ( ! empty( $args['secondary-button-id'] ) ) {
			$secondary_button_attributes['id'] = $args['secondary-button-id'];
		}
		if ( ! empty( $args['secondary-button-role'] ) ) {
			$secondary_button_attributes['role'] = $args['secondary-button-role'];
		}

		require FrmOnboardingWizardController::get_view_path() . 'footer.php';
	}
}
