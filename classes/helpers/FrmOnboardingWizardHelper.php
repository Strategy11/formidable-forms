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
 * @since x.x
 */
class FrmOnboardingWizardHelper {

	/**
	 * Echo attributes for the addon's label tag.
	 *
	 * @since x.x
	 *
	 * @param string $addon_key The key of addon.
	 * @param string $addon     The array of addon's information.
	 * @return void
	 */
	public static function add_addon_label_attributes( $addon_key, $addon ) {
		$id         = 'frm-onboarding-' . $addon_key . '-addon';
		$attributes = array(
			'for'   => $id,
			'class' => 'frm-option-box',
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
	 * @since x.x
	 *
	 * @param string $addon_key The key of addon.
	 * @param string $addon     The array of addon's information.
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
}
