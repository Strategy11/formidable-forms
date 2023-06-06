<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteAppController {

	/**
	 * Include the updater and show the Stripe connect message.
	 *
	 * @return void
	 */
	public static function include_updater() {
		self::install();
	}

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
	 * Add a Stripe gateway for the payment action.
	 *
	 * @param array $gateways
	 * @return array
	 */
	public static function add_gateway( $gateways ) {
		$gateways['stripe'] = array(
			'label'      => 'Stripe',
			'user_label' => __( 'Credit Card', 'formidable' ),
			'class'      => 'Strp',
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
}
