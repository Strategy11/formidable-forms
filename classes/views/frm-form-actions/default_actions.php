<?php
// add post action
class FrmDefPostAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
	    $action_ops['classes'] = 'ab-icon frm_dashicon_font dashicons-before frm_show_upgrade';

		parent::__construct( 'wppost', __( 'Create Post', 'formidable' ), $action_ops );
	}
}

// add register action
class FrmDefRegAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts( 'frm_register_icon frm_show_upgrade' );
		parent::__construct( 'register', __( 'Register User', 'formidable' ), $action_ops );
	}
}

// add paypal action
class FrmDefPayPalAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts( 'frm_paypal_icon frm_show_upgrade' );
		parent::__construct( 'paypal', __( 'PayPal Payment', 'formidable' ), $action_ops );
	}
}

// add aweber action
class FrmDefAweberAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts( 'frm_aweber_icon frm_show_upgrade' );
		parent::__construct( 'aweber', __( 'Aweber', 'formidable' ), $action_ops );
	}
}

// add mailchimp action
class FrmDefMlcmpAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts( 'frm_mailchimp_icon frm_show_upgrade' );
		parent::__construct( 'mailchimp', __( 'MailChimp', 'formidable' ), $action_ops );
	}
}

// add twilio action
class FrmDefTwilioAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts( 'frm_sms_icon frm_show_upgrade' );
		parent::__construct( 'twilio', __( 'Twilio SMS', 'formidable' ), $action_ops );
	}
}

// add payment action
class FrmDefHrsAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts( 'frm_stripe_icon frm_credit-card-alt_icon frm_show_upgrade' );
		parent::__construct( 'payment', __( 'Credit Card Payment', 'formidable' ), $action_ops );
	}
}
