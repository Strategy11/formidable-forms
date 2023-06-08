<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteSettingsController {

	/**
	 * Add Stripe section to Global Settings.
	 *
	 * @param array $sections
	 * @return array
	 */
	public static function add_settings_section( $sections ) {
		$sections['stripe'] = array(
			'class'    => __CLASS__,
			'function' => 'route',
			'icon'     => 'frm_icon_font frm_stripe_icon',
		);
		return $sections;
	}

	/**
	 * Handle global settings routing.
	 *
	 * @return void
	 */
	public static function route() {
		$action = FrmAppHelper::get_param( 'action' );
		if ( $action === 'process-form' ) {
			self::process_form();
			return;
		}
		self::global_settings_form();
	}

	/**
	 * Print the Stripe section for Global settings.
	 *
	 * @param array $atts
	 * @return void
	 */
	public static function global_settings_form( $atts = array() ) {
		$atts                             = array_merge( $atts, self::get_default_settings_atts() );
		$errors                           = $atts['errors'];
		$message                          = $atts['message'];
		$settings                         = FrmStrpLiteAppHelper::get_settings();
		$stripe_connect_is_live           = FrmStrpLiteConnectHelper::stripe_connect_is_setup( 'live' );
		$stripe_connect_is_on_for_test    = FrmStrpLiteConnectHelper::stripe_connect_is_setup( 'test' );

		include FrmStrpLiteAppHelper::plugin_path() . '/views/settings/form.php';
	}

	/**
	 * @return array
	 */
	private static function get_default_settings_atts() {
		return array(
			'errors'  => array(),
			'message' => '',
		);
	}

	/**
	 * Handle processing changes to global Stripe Settings.
	 *
	 * @return void
	 */
	public static function process_form() {
		// Does this need a nonce?

		$atts = array(
			'errors'  => array(),
			'message' => '',
		);

		$settings = FrmStrpLiteAppHelper::get_settings();
		$settings->update( $_POST );

		if ( empty( $atts['errors'] ) ) {
			$settings->store();
			$atts['message'] = __( 'Settings Saved', 'formidable' );
		}

		self::global_settings_form( $atts );
	}

	/**
	 * Move the card description to the main description.
	 *
	 * @since 2.0
	 *
	 * @param array    $field_array
	 * @param stdClass $field
	 * @return array
	 */
	public static function prepare_field_desc( $field_array, $field ) {
		if ( $field->type === 'credit_card' && isset( $field_array['month_desc'] ) && ! empty( $field_array['month_desc'] ) && empty( $field_array['description'] ) ) {
			$has_stripe_action = FrmStrpLiteActionsController::get_actions_before_submit( $field->form_id );
			if ( ! $has_stripe_action ) {
				// Fixes Pro issue #3833. We only want to move the card description if there are Stripe actions.
				// A credit card field without a Stripe action should use the month description.
				return $field_array;
			}

			$field_array['description'] = $field_array['month_desc'];
			$field_array['month_desc'] = '';
		}
		return $field_array;
	}
}
