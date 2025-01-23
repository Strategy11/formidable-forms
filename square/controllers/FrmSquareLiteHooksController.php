<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteHooksController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'frm_enqueue_form_scripts', 'FrmSquareLiteActionsController::maybe_load_scripts' );
		add_filter( 'frm_validate_credit_card_field_entry', 'FrmSquareLiteActionsController::remove_cc_validation', 20, 3 );

		add_filter( 'frm_payment_gateways', 'FrmSquareLiteAppController::add_gateway' );

		// This filter flags the Pro credit card field that Stripe is enabled.
		add_filter(
			'frm_pro_show_card_callback',
			function () {
				return 'FrmSquareLiteActionsController::show_card';
			}
		);
	}

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_filter( 'frm_add_settings_section', 'FrmSquareLiteSettingsController::add_settings_section' );

		if ( defined( 'DOING_AJAX' ) ) {
			self::load_ajax_hooks();
		}
	}

	/**
	 * @return void
	 */
	private static function load_ajax_hooks() {

	}
}
