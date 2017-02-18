<?php

class FrmNotification {
	public function __construct() {
		if ( ! defined( 'ABSPATH' ) ) {
			die( 'You are not allowed to call this page directly.' );
		}
		add_action( 'frm_trigger_email_action', 'FrmNotification::trigger_email', 10, 3 );
	}

	/**
	 * TODO: deprecate
	 */
	public static function trigger_email( $action, $entry, $form ) {
		return FrmEmailController::trigger_email( $action, $entry, $form );
	}

	/**
	 * @deprecated 2.0
	 */
	public function entry_created( $entry_id, $form_id ) {
		$new_function = 'FrmFormActionsController::trigger_actions("create", ' . $form_id . ', ' . $entry_id . ', "email")';
		_deprecated_function( __FUNCTION__, '2.0', $new_function );
		FrmFormActionsController::trigger_actions( 'create', $form_id, $entry_id, 'email' );
	}

	/**
	 * @deprecated 2.03.04
	 */
	public static function remove_mandrill_br() {
		_deprecated_function( __FUNCTION__, '2.03.04', 'FrmEmailController::remove_mandrill_br' );

		return FrmEmailController::remove_mandrill_br();
	}

	/**
	 * @deprecated 2.03.04
	 */
	public static function send_email() {
		_deprecated_function( __FUNCTION__, '2.03.04', 'FrmEmail::send' );
	}

	/**
	 * @deprecated 2.0
	 */
	public function send_notification_email() {
		_deprecated_function( __FUNCTION__, '2.0', 'FrmEmail::send' );
	}

}
