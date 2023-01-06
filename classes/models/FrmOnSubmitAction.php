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
			'event'     => 'create',
			'limit'     => 99,
			'priority'  => 9,
			'color'     => 'rgb(49, 119, 199)',
		);
		$action_ops = apply_filters( 'frm_' . self::$slug . '_control_settings', $action_ops );

		parent::__construct( self::$slug, self::get_name(), $action_ops );
	}

	public static function get_name() {
		return __( 'On Submit', 'formidable' );
	}

	public function form( $instance, $args = array() ) {
		include FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/on_submit_settings.php';
	}

	public function get_defaults() {
		return array(
			'success_action'  => FrmOnSubmitHelper::get_default_action_type(),
			'success_msg'     => FrmOnSubmitHelper::get_default_msg(),
			'show_form'       => '',
			'success_url'     => '',
			'success_page_id' => '',
			'redirect_msg'    => FrmOnSubmitHelper::get_default_redirect_msg(),
		);
	}
}
