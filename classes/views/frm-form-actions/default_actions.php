<?php
// add post action
class FrmDefPostAction extends FrmFormAction {
	public function __construct() {
	    $action_ops = FrmFormActionsHelper::default_action_opts();
	    $action_ops['classes'] = 'ab-icon frm_dashicon_font dashicons-before';

		$this->FrmFormAction('wppost', __( 'Create Post', 'formidable' ), $action_ops);
	}
}


// add register action
class FrmDefRegAction extends FrmFormAction {
	public function __construct() {
	    $action_ops = FrmFormActionsHelper::default_action_opts('frm_register_icon');

		$this->FrmFormAction('register', __( 'Register User', 'formidable' ), $action_ops);
	}
}

// add paypal action
class FrmDefPayPalAction extends FrmFormAction {
	public function __construct() {
	    $action_ops = FrmFormActionsHelper::default_action_opts('frm_paypal_icon');

		$this->FrmFormAction('paypal', __( 'Collect Payment', 'formidable' ), $action_ops);
	}
}

// add aweber action
class FrmDefAweberAction extends FrmFormAction {
	public function __construct() {
	    $action_ops = FrmFormActionsHelper::default_action_opts('frm_aweber_icon');

		$this->FrmFormAction('aweber', __( 'Aweber', 'formidable' ), $action_ops);
	}
}

// add mailchimp action
class FrmDefMlcmpAction extends FrmFormAction {
	public function __construct() {
	    $action_ops = FrmFormActionsHelper::default_action_opts('frm_mailchimp_icon');

		$this->FrmFormAction('mailchimp', __( 'MailChimp', 'formidable' ), $action_ops);
	}
}

// add twilio action
class FrmDefTwilioAction extends FrmFormAction {
	public function __construct() {
	    $action_ops = FrmFormActionsHelper::default_action_opts('frm_sms_icon');

		$this->FrmFormAction('twilio', __( 'Twilio', 'formidable' ), $action_ops);
	}
}

// add highrise action
class FrmDefHrsAction extends FrmFormAction {
	public function __construct() {
	    $action_ops = FrmFormActionsHelper::default_action_opts('frm_highrise_icon');

		$this->FrmFormAction('highrise', __( 'Highrise', 'formidable' ), $action_ops);
	}
}
