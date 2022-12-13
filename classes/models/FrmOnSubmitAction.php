<?php
/**
 * On Submit form action
 *
 * @package Formidable
 * @since 5.x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmOnSubmitAction extends FrmFormAction {

	public static $slug = 'on_submit';

	public function __construct() {
		$action_ops = array(
			'classes'   => 'frm_icon_font frm_location_arrow',
			'active'    => true,
			'event'     => array( 'create' ),
			'limit'     => 99,
			'priority'  => 9,
			'color'     => 'rgb(49, 119, 199)',
		);
		$action_ops = apply_filters( 'frm_on_submit_control_settings', $action_ops );

		parent::__construct( self::$slug, __( 'On Submit', 'formidable' ), $action_ops );
	}

	public function form( $form_action, $args = array() ) {
		include FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/on_submit_settings.php';
	}

	public function get_defaults() {
		return array(
			'success_action'  => 'message',
			'success_msg'     => __( 'Your responses were successfully submitted. Thank you!', 'formidable' ),
			'show_form'       => '',
			'success_url'     => '',
			'success_page_id' => '',
			'redirect_msg'    => __( 'Please wait while you are redirected.', 'formidable' ),
		);
	}
}
