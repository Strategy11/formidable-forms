<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteAppController {

	/**
	 * Install required tables.
	 *
	 * @param mixed $old_db_version
	 * @return void
	 */
	public static function install( $old_db_version = false ) {
		FrmTransLiteAppController::install( $old_db_version );
	}

	/**
	 * Remove Stripe database items after uninstall.
	 *
	 * @since 6.5, introduced in v2.07 of the Stripe add on.
	 *
	 * @return void
	 */
	public static function uninstall() {
		if ( ! current_user_can( 'administrator' ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			wp_die( esc_html( $frm_settings->admin_permission ) );
		}

		$options_to_delete = array(
			FrmStrpLiteEventsController::$events_to_skip_option_name,
			'frm_strp_options',
		);

		$modes            = array( 'test', 'live' );
		$option_name_keys = array( 'account_id', 'client_password', 'server_password', 'details_submitted' );
		foreach ( $modes as $mode ) {
			foreach ( $option_name_keys as $key ) {
				$options_to_delete[] = 'frm_strp_connect_' . $key . '_' . $mode;
			}
		}

		foreach ( $options_to_delete as $option_name ) {
			delete_option( $option_name );
		}
	}

	/**
	 * Redirect to Stripe settings when payments are not yet installed
	 * and the payments page is accessed by its URL.
	 *
	 * @return void
	 */
	public static function maybe_redirect_to_stripe_settings() {
		if ( ! FrmAppHelper::is_admin_page( 'formidable-payments' ) || self::payments_are_installed() ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=formidable-settings&t=stripe_settings' ) );
		die();
	}

	/**
	 * @return bool
	 */
	private static function payments_are_installed() {
		$db     = new FrmTransLiteDb();
		$option = get_option( $db->db_opt_name );
		return false !== $option;
	}

	/**
	 * Add the gateway for compatibility with the Payments submodule.
	 * This adds the Stripe checkbox option to the list of gateways.
	 *
	 * @param array $gateways
	 * @return array
	 */
	public static function add_gateway( $gateways ) {
		$gateways['stripe'] = array(
			'label'      => 'Stripe',
			'user_label' => __( 'Payment', 'formidable' ),
			'class'      => 'StrpLite',
			'recurring'  => true,
			'include'    => array(
				'billing_first_name',
				'billing_last_name',
				'credit_card',
				'billing_address',
			),
		);
		return $gateways;
	}
}
