<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEmailAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
			'classes'  => 'frm_icon_font frm_email_solid_icon',
			'active'   => true,
			'event'    => array( 'create' ),
			'limit'    => 99,
			'priority' => 10,
			'color'    => 'rgb(49, 119, 199)',
		);
		$action_ops = apply_filters( 'frm_email_control_settings', $action_ops );

		parent::__construct( 'email', __( 'Send Email', 'formidable' ), $action_ops );
	}

	/**
	 * @return void
	 */
	public function form( $form_action, $args = array() ) {
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

		include FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/_email_settings.php';
	}

	/**
	 * @return array
	 */
	public function get_defaults() {
		$frm_settings = FrmAppHelper::get_settings();
		$to_email     = ! empty( $frm_settings->default_email ) && is_email( $frm_settings->default_email ) ? $frm_settings->default_email : '[admin_email]';
		$from_email   = ! empty( $frm_settings->from_email ) && is_email( $frm_settings->from_email ) ? $frm_settings->from_email : '[admin_email]';
		return array(
			'email_to'      => $to_email,
			'cc'            => '',
			'bcc'           => '',
			'from'          => '[sitename] <' . $from_email . '>',
			'reply_to'      => '',
			'email_subject' => '',
			'email_message' => '[default-message]',
			'inc_user_info' => 0,
			'plain_text'    => 0,
			'event'         => array( 'create' ),
		);
	}

	/**
	 * @return string
	 */
	protected function get_upgrade_text() {
		return __( 'Conditional emails', 'formidable' );
	}
}
