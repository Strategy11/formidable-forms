<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteAppController {

	/**
	 * Install required tables.
	 *
	 * @todo Call this when Stripe is connected for the first time.
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
	 * @since 2.07
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
}
