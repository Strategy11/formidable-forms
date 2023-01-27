<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// add post action
class FrmDefPostAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_wordpress_icon frm-inverse frm_show_upgrade' );
		$action_ops['color'] = 'rgb(0,160,210)';

		parent::__construct( 'wppost', __( 'Create Post', 'formidable' ), $action_ops );
	}
}

// add register action
class FrmDefRegAction extends FrmFormAction {
	public function __construct() {
		$action_ops           = FrmFormAction::default_action_opts( 'frm_register_icon frm_show_upgrade' );
		$action_ops['plugin'] = 'registration';
		$action_ops['color']  = 'var(--pink)';
		parent::__construct( 'register', __( 'Register User', 'formidable' ), $action_ops );
	}
}

// add paypal action
class FrmDefPayPalAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_paypal_icon frm-inverse frm_show_upgrade' );
		$action_ops['color'] = 'var(--primary-700)';

		parent::__construct( 'paypal', 'PayPal', $action_ops );
	}
}

// add quiz action
class FrmDefQuizAction extends FrmFormAction {
	public function __construct() {
		$action_ops           = FrmFormAction::default_action_opts( 'frm_percent_icon frm_quiz_icon frm_show_upgrade' );
		$action_ops['plugin'] = 'quizzes';
		parent::__construct( 'quiz', __( 'Scored Quiz', 'formidable' ), $action_ops );
	}
}

// add quiz outcome action
class FrmDefQuizOutcomeAction extends FrmFormAction {
	public function __construct() {
		$action_ops           = FrmFormAction::default_action_opts( 'frm_check1_icon frm_quiz_icon frm_show_upgrade' );
		$action_ops['plugin'] = 'quizzes';
		parent::__construct( 'quiz_outcome', __( 'Quiz Outcome', 'formidable' ), $action_ops );
	}
}

// add aweber action
class FrmDefAweberAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_aweber_icon frm_show_upgrade' );
		$action_ops['color'] = 'var(--green)';
		parent::__construct( 'aweber', 'AWeber', $action_ops );
	}
}

// add mailchimp action
class FrmDefMlcmpAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_mailchimp_icon frm_show_upgrade frm-inverse' );
		$action_ops['color'] = 'var(--grey-700)';

		parent::__construct( 'mailchimp', 'Mailchimp', $action_ops );
	}
}

// add zapier action
class FrmDefZapierAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_zapier_icon frm_show_upgrade' );
		$action_ops['color'] = 'var(--orange)';
		parent::__construct( 'zapier', 'Zapier', $action_ops );
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
		$action_ops           = FrmFormAction::default_action_opts( 'frm_stripe_icon frm_credit_card_alt_icon frm_show_upgrade' );
		$action_ops['color']  = 'var(--green)';
		$action_ops['plugin'] = 'stripe';
		parent::__construct( 'payment', __( 'eCommerce', 'formidable' ), $action_ops );
	}
}

class FrmDefActiveCampaignAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_activecampaign_icon frm_show_upgrade' );
		$action_ops['color'] = 'var(--primary-700)';
		parent::__construct( 'activecampaign', 'ActiveCampaign', $action_ops );
	}
}

class FrmDefSalesforceAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_salesforce_icon frm-inverse frm_show_upgrade' );
		$action_ops['color'] = 'var(--primary-500)';
		parent::__construct( 'salesforce', 'Salesforce', $action_ops );
	}
}

class FrmDefConstContactAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_constant_contact_icon frm_show_upgrade' );
		$action_ops['color'] = 'rgb(0,160,210)';
		parent::__construct( 'constantcontact', 'Constant Contact', $action_ops );
	}
}

class FrmDefGetResponseAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_getresponse_icon frm_show_upgrade' );
		$action_ops['color'] = '#00baff';
		parent::__construct( 'getresponse', 'GetResponse', $action_ops );
	}
}

class FrmDefHubspotAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_hubspot_icon frm_show_upgrade' );
		$action_ops['color'] = 'var(--orange)';

		$action_ops['message'] = '';
		if ( ! FrmAppHelper::pro_is_installed() ) {
			$action_ops['message'] .= __( 'The HubSpot integration is not available on your plan. Did you know you can upgrade to unlock more awesome features?', 'formidable' ) . '<br/><br/>';
		}
		$link                   = FrmAppHelper::admin_upgrade_link( 'add-action', 'knowledgebase/hubspot-forms/' );
		$action_ops['message'] .= '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener" class="button button-secondary frm-button-secondary">Get Free HubSpot Account</a>';
		parent::__construct( 'hubspot', 'Hubspot', $action_ops );
	}
}

class FrmDefHighriseAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_building_icon frm_show_upgrade' );
		$action_ops['color'] = 'var(--purple)';
		parent::__construct( 'highrise', 'Highrise', $action_ops );
	}
}

class FrmDefMailpoetAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_mailpoet_icon frm_show_upgrade' );
		$action_ops['color'] = 'var(--orange)';
		parent::__construct( 'mailpoet', 'MailPoet', $action_ops );
	}
}

class FrmDefApiAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_feed_icon frm_show_upgrade' );
		$action_ops['color'] = 'var(--purple)';
		parent::__construct( 'api', __( 'Send API data', 'formidable' ), $action_ops );
	}
}

// add google sheets
class FrmDefGoogleSpreadsheetAction extends FrmFormAction {
	public function __construct() {
		$action_ops          = FrmFormAction::default_action_opts( 'frm_googlesheets_icon frm_show_upgrade' );
		$action_ops['color'] = 'var(--green)';
		parent::__construct( 'googlespreadsheet', __( 'Google Sheets', 'formidable' ), $action_ops );
	}
}
