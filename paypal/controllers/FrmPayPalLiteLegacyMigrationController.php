<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Shows the "Convert to PayPal Commerce" option inside legacy PayPal actions and handles the request.
 *
 * @since x.x
 */
class FrmPayPalLiteLegacyMigrationController {

	/**
	 * Print the migration notice at the top of a legacy PayPal action's settings.
	 *
	 * @since x.x
	 *
	 * @param object $form_action The form action whose settings are being shown.
	 *
	 * @return void
	 */
	public static function show_migration_notice( $form_action ) {
		if ( ! FrmPayPalLiteLegacyMigrationHelper::is_legacy_action( $form_action ) || empty( $form_action->ID ) || ! current_user_can( 'frm_edit_forms' ) ) {
			return;
		}

		$blocker = FrmPayPalLiteLegacyMigrationHelper::get_migration_blocker( (int) $form_action->menu_order );

		include FrmPayPalLiteAppHelper::plugin_path() . 'views/settings/legacy-migration-notice.php';
	}

	/**
	 * Convert a legacy PayPal action into a PayPal Commerce payment action.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function migrate_action() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$action_id = FrmAppHelper::get_post_param( 'action_id', 0, 'absint' );
		$result    = FrmPayPalLiteLegacyMigrationHelper::migrate( $action_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}
}
