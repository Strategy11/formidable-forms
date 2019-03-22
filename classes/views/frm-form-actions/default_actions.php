<?php
// add post action
class FrmDefPostAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'frm_show_upgrade';

		parent::__construct( 'wppost', __( 'Create Post', 'formidable' ), $action_ops );
	}
}

// add register action
class FrmDefRegAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'frm_show_upgrade';
		$action_ops['plugin']  = 'registration';
		parent::__construct( 'register', __( 'Register User', 'formidable' ), $action_ops );
	}
}

// add paypal action
class FrmDefPayPalAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'fab fa-paypal frm_show_upgrade';
		$action_ops['color'] = 'rgb(0,112,185)';

		parent::__construct( 'paypal', 'PayPal', $action_ops );
	}
}

// add aweber action
class FrmDefAweberAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts( 'frm_aweber_icon frm_show_upgrade' );
		parent::__construct( 'aweber', 'AWeber', $action_ops );
	}
}

// add mailchimp action
class FrmDefMlcmpAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'fab fa-mailchimp frm_show_upgrade frm-inverse';
		$action_ops['color']   = 'var(--dark-grey)';

		parent::__construct( 'mailchimp', 'MailChimp', $action_ops );
	}
}

// add twilio action
class FrmDefTwilioAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'fas fa-mobile-alt frm_show_upgrade';
		parent::__construct( 'twilio', __( 'Twilio SMS', 'formidable' ), $action_ops );
	}
}

// add payment action
class FrmDefHrsAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts( 'frm_stripe_icon frm_credit-card-alt_icon frm_show_upgrade' );
		$action_ops['plugin'] = 'stripe';
		parent::__construct( 'payment', __( 'eCommerce', 'formidable' ), $action_ops );
	}
}

class FrmDefActiveCampaignAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'frm_show_upgrade';
		parent::__construct( 'activecampaign', 'ActiveCampaign', $action_ops );
	}
}

class FrmDefSalesforceAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'frm_show_upgrade';
		parent::__construct( 'salesforce', 'Salesforce', $action_ops );
	}
}

class FrmDefConstContactAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'frm_show_upgrade';
		parent::__construct( 'constantcontact', 'Constant Contact', $action_ops );
	}
}

class FrmDefGetResponseAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'frm_show_upgrade';
		parent::__construct( 'getresponse', 'GetResponse', $action_ops );
	}
}

class FrmDefHubspotAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'frm_show_upgrade';
		parent::__construct( 'hubspot', 'Hubpost', $action_ops );
	}
}

class FrmDefHighriseAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'frm_show_upgrade';
		parent::__construct( 'highrise', 'Highrise', $action_ops );
	}
}

class FrmDefMailpoetAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts();
		$action_ops['classes'] = 'frm_show_upgrade';
		parent::__construct( 'mailpoet', 'MailPoet', $action_ops );
	}
}

class FrmDefApiAction extends FrmFormAction {
	public function __construct() {
		$action_ops = FrmFormAction::default_action_opts( 'frm_feed_icon frm_show_upgrade' );
		parent::__construct( 'api', __( 'Send API data', 'formidable' ), $action_ops );
	}
}
