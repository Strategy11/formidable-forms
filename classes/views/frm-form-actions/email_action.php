<?php

class FrmEmailAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
		    'classes'   => 'frm_email_icon frm_icon_font',
            'active'    => true,
			'event'     => array( 'create' ),
            'limit'     => 99,
            'priority'  => 10,
            'ajax_load' => false,
		);
		$action_ops = apply_filters('frm_email_control_settings', $action_ops);

		parent::__construct('email', __( 'Email Notification', 'formidable' ), $action_ops);
	}

	public function form( $form_action, $args = array() ) {
	    extract($args);

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-form-actions/_email_settings.php' );
	}

	public function get_defaults() {
	    return array(
            'email_to'      => '[admin_email]',
            'cc'            => '',
            'bcc'           => '',
            'from'          => '[sitename] <[admin_email]>',
            'reply_to'      => '',
            'email_subject' => '',
            'email_message' => '[default-message]',
            'inc_user_info' => 0,
            'plain_text'    => 0,
			'event'         => array( 'create' ),
	    );
	}
}
