<?php
/**
 * On Submit form action
 *
 * @package Formidable
 * @since 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmOnSubmitAction extends FrmFormAction {

	public static $slug = 'on_submit';

	public function __construct() {
		$action_ops = array(
			'classes'   => 'frm_icon_font frm_checkmark_icon',
			'active'    => true,
			'event'     => array( 'create' ),
			'limit'     => 99,
			'priority'  => 9,
			'color'     => 'rgb(66, 193, 178)',
			'keywords'  => __( 'redirect, success, confirmation, submit', 'formidable' ),
		);
		$action_ops = apply_filters( 'frm_' . self::$slug . '_control_settings', $action_ops );

		$this->maybe_save_edit_trigger( $action_ops );

		parent::__construct( self::$slug, self::get_name(), $action_ops );
	}

	/**
	 * @return string
	 */
	public static function get_name() {
		return __( 'Confirmation', 'formidable' );
	}

	/**
	 * @return void
	 */
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
		);
	}

	/**
	 * Add a workaround in case Pro hasn't been updated. We don't want to
	 * lose the trigger and have edit messages start showing on create.
	 *
	 * @param array $action_ops The action setup details.
	 *
	 * @return void
	 */
	protected function maybe_save_edit_trigger( &$action_ops ) {
		if ( $action_ops['event'] === array( 'create' ) && FrmAppHelper::pro_is_installed() ) {
			$action_ops['event'][] = 'update';
		}
	}
}
