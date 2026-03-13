<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteHooksController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'frm_enqueue_form_scripts', 'FrmPayPalLiteActionsController::maybe_load_scripts' );
		add_filter( 'frm_validate_credit_card_field_entry', 'FrmPayPalLiteActionsController::remove_cc_validation', 20, 3 );

		add_filter( 'frm_payment_gateways', 'FrmPayPalLiteAppController::add_gateway' );

		add_action( 'init', 'FrmPayPalLiteConnectHelper::check_for_redirects' );

		// Use 20 so this happens after the Stripe add-on.
		add_filter( 'frm_pro_show_card_callback', 'FrmPayPalLiteActionsController::maybe_show_card', 20, 2 );

		add_filter(
			'frm_show_normal_field_type',
			/**
			 * Hide the email, name, and address fields if they are marked as PayPal order fields.
			 *
			 * @since x.x
			 *
			 * @param bool   $show_normal_field_type Whether to show the field.
			 * @param string $type                   The field type.
			 * @param object $field                  The field object.
			 *
			 * @return bool
			 */
			function ( $show_normal_field_type, $type, $field ) {
				if ( ! in_array( $type, array( 'email', 'name', 'address' ), true ) ) {
					return $show_normal_field_type;
				}

				$is_paypal_order_field = FrmField::get_option( $field, 'is_paypal_order_field' );

				return $is_paypal_order_field ? false : $show_normal_field_type;
			},
			10,
			3
		);
	}

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		// Use 99 so we overwrite the PayPal add-on settings.
		// These are called explicitly below the Lite PayPal settings.
		add_filter( 'frm_add_settings_section', 'FrmPayPalLiteSettingsController::add_settings_section', 99 );
		add_action( 'frm_update_settings', 'FrmPayPalLiteSettingsController::process_form' );

		add_filter(
			'frm_paypal_action_name',
			function () {
				return 'PayPal Commerce';
			}
		);

		add_filter( 'frm_before_save_payment_action', 'FrmPayPalLiteActionsController::before_save_settings', 20, 2 );

		add_filter(
			'frm_paypal_action_options',
			function ( $options ) {
				// Make actions using the PayPal add-on use the same icon we use in Lite.
				$options['classes'] = 'frmfont frm_paypal_icon frm-inverse';
				return $options;
			}
		);

		add_action(
			'frm_field_options_before_label',
			/**
			 * @param array $field The field settings.
			 * @param array $display The display settings for the field.
			 * @param array $values The values associated with the field.
			 */
			function ( $field, $display, $values ) {
				// Add a note about PayPal order fields here.
				if ( FrmField::get_option( $field, 'is_paypal_order_field' ) ) {
					echo '<div class="frm_note_style">This is a PayPal order field. It is automatically populated when a payment is processed, and is automatically excluded from the form HTML.</div>';
				}
			},
			10,
			3
		);

		if ( defined( 'DOING_AJAX' ) ) {
			self::load_ajax_hooks();
		}
	}

	/**
	 * @return void
	 */
	private static function load_ajax_hooks() {
		add_action( 'wp_ajax_frm_paypal_oauth', 'FrmPayPalLiteAppController::handle_oauth' );
		add_action( 'wp_ajax_frm_paypal_disconnect', 'FrmPayPalLiteAppController::handle_disconnect' );

		$frm_paypal_events_controller = new FrmPayPalLiteEventsController();
		add_action( 'wp_ajax_nopriv_frm_paypal_process_events', array( &$frm_paypal_events_controller, 'process_events' ) );
		add_action( 'wp_ajax_frm_paypal_process_events', array( &$frm_paypal_events_controller, 'process_events' ) );

		// Verify PayPal Lite sites.
		add_action( 'wp_ajax_nopriv_frm_paypal_lite_verify', 'FrmPayPalLiteConnectHelper::verify' );

		add_action( 'wp_ajax_frm_paypal_get_amount', 'FrmPayPalLiteAppController::get_amount' );
		add_action( 'wp_ajax_nopriv_frm_paypal_get_amount', 'FrmPayPalLiteAppController::get_amount' );

		add_action( 'wp_ajax_frm_paypal_create_order', 'FrmPayPalLiteAppController::create_order' );
		add_action( 'wp_ajax_nopriv_frm_paypal_create_order', 'FrmPayPalLiteAppController::create_order' );

		add_action( 'wp_ajax_frm_paypal_create_subscription', 'FrmPayPalLiteAppController::create_subscription' );
		add_action( 'wp_ajax_nopriv_frm_paypal_create_subscription', 'FrmPayPalLiteAppController::create_subscription' );

		add_action( 'wp_ajax_frm_paypal_create_vault_setup_token', 'FrmPayPalLiteAppController::create_vault_setup_token' );
		add_action( 'wp_ajax_nopriv_frm_paypal_create_vault_setup_token', 'FrmPayPalLiteAppController::create_vault_setup_token' );

		add_action( 'wp_ajax_frm_paypal_render_seller_status', 'FrmPayPalLiteConnectHelper::handle_render_seller_status' );

		add_action( 'wp_ajax_frm_add_form_action', 'FrmPayPalLiteActionsController::maybe_modify_new_action_post_data', 1 );
	}
}
