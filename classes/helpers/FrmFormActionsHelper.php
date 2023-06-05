<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormActionsHelper {

	/**
	 * @deprecated 2.0.9
	 * @since 6.1.3 - Uncommented the deprecated message.
	 */
	public static function get_action_for_form( $form_id, $type = 'all', $limit = 99 ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmFormAction::get_action_for_form' );
		return FrmFormAction::get_action_for_form( $form_id, $type, $limit );
	}

	/**
	 * @deprecated 2.0.9
	 * @since 6.1.3 - Uncommented the deprecated message.
	 */
	public static function default_action_opts( $class = '' ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmFormAction::default_action_opts' );
		return FrmFormAction::default_action_opts( $class );
	}
}
