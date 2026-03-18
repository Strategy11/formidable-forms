<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Add post action.
 */
class FrmDefPostAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_wordpress_icon frm_show_upgrade' );
		$action_ops['color']       = 'rgb(0,160,210)';
		$action_ops['description'] = __( 'Content publishing', 'formidable' );

		parent::__construct( 'wppost', __( 'Create Post', 'formidable' ), $action_ops );
	}
}

/**
 * Add register action.
 */
class FrmDefRegAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_register_icon frm_show_upgrade' );
		$action_ops['plugin']      = 'registration';
		$action_ops['color']       = 'var(--pink)';
		$action_ops['description'] = __( 'Account creation', 'formidable' );
		parent::__construct( 'register', __( 'Register User', 'formidable' ), $action_ops );
	}
}

/**
 * Add paypal action.
 */
class FrmDefPayPalAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_paypal_icon frm_show_upgrade' );
		$action_ops['color']       = 'var(--primary-700)';
		$action_ops['description'] = __( 'Payment gateway', 'formidable' );

		parent::__construct( 'paypal', 'PayPal', $action_ops );
	}
}

/**
 * Add quiz action.
 */
class FrmDefQuizAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_percent_icon frm_quiz_icon frm_show_upgrade' );
		$action_ops['plugin']      = 'quizzes';
		$action_ops['description'] = __( 'Automated grading', 'formidable' );
		parent::__construct( 'quiz', __( 'Scored Quiz', 'formidable' ), $action_ops );
	}
}

/**
 * Add quiz outcome action.
 */
class FrmDefQuizOutcomeAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_check1_icon frm_quiz_icon frm_show_upgrade' );
		$action_ops['plugin']      = 'quizzes';
		$action_ops['description'] = __( 'Result logic', 'formidable' );
		parent::__construct( 'quiz_outcome', __( 'Quiz Outcome', 'formidable' ), $action_ops );
	}
}

/**
 * Add aweber action.
 */
class FrmDefAweberAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_aweber_icon frm_show_upgrade' );
		$action_ops['color']       = 'var(--green)';
		$action_ops['description'] = __( 'List triggers', 'formidable' );
		parent::__construct( 'aweber', 'AWeber', $action_ops );
	}
}

/**
 * Add mailchimp action.
 */
class FrmDefMlcmpAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_mailchimp_icon frm_show_upgrade' );
		$action_ops['color']       = 'var(--grey-700)';
		$action_ops['description'] = __( 'Subscription confirmation', 'formidable' );

		parent::__construct( 'mailchimp', 'Mailchimp', $action_ops );
	}
}

/**
 * Add zapier action.
 */
class FrmDefZapierAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_zapier_icon frm_show_upgrade' );
		$action_ops['color']       = 'var(--orange)';
		$action_ops['description'] = __( 'App automation', 'formidable' );
		parent::__construct( 'zapier', 'Zapier', $action_ops );
	}
}

/**
 * Add n8n action.
 */
class FrmDefN8NAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_n8n_icon frm_show_upgrade' );
		$action_ops['color']       = '#EA4B71';
		$action_ops['description'] = __( 'Workflow automation', 'formidable' );
		parent::__construct( 'n8n', 'n8n', $action_ops );
	}
}

/**
 * Add twilio action.
 */
class FrmDefTwilioAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_sms_icon frm_show_upgrade' );
		$action_ops['description'] = __( 'Text notifications', 'formidable' );
		parent::__construct( 'twilio', __( 'Twilio SMS', 'formidable' ), $action_ops );
	}
}

class FrmDefActiveCampaignAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_activecampaign_icon frm_show_upgrade' );
		$action_ops['color']       = 'var(--primary-700)';
		$action_ops['description'] = __( 'Contact automation', 'formidable' );
		parent::__construct( 'activecampaign', 'ActiveCampaign', $action_ops );
	}
}

class FrmDefSalesforceAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_salesforce_icon frm_show_upgrade' );
		$action_ops['color']       = 'var(--primary-500)';
		$action_ops['description'] = __( 'Lead automation', 'formidable' );
		parent::__construct( 'salesforce', 'Salesforce', $action_ops );
	}
}

class FrmDefConstContactAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_constant_contact_icon frm_show_upgrade' );
		$action_ops['color']       = 'rgb(0,160,210)';
		$action_ops['description'] = __( 'Content distribution', 'formidable' );
		parent::__construct( 'constantcontact', 'Constant Contact', $action_ops );
	}
}

class FrmDefGetResponseAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_getresponse_icon frm_show_upgrade' );
		$action_ops['color']       = '#00baff';
		$action_ops['description'] = __( 'Success notifications', 'formidable' );
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

		$link                      = FrmAppHelper::admin_upgrade_link( 'add-action', 'knowledgebase/hubspot-forms/' );
		$action_ops['message']    .= '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener" class="button button-secondary frm-button-secondary">Get Free HubSpot Account</a>';
		$action_ops['description'] = __( 'CRM alerts', 'formidable' );
		parent::__construct( 'hubspot', 'Hubspot', $action_ops );
	}
}

class FrmDefMailpoetAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_mailpoet_icon frm_show_upgrade' );
		$action_ops['color']       = 'var(--orange)';
		$action_ops['description'] = __( 'Plugin automation', 'formidable' );
		parent::__construct( 'mailpoet', 'MailPoet', $action_ops );
	}
}

class FrmDefApiAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_feed_icon frm_show_upgrade' );
		$action_ops['color']       = 'var(--purple)';
		$action_ops['description'] = __( 'System integration', 'formidable' );
		parent::__construct( 'api', __( 'Send API data', 'formidable' ), $action_ops );
	}
}

/**
 * Add google sheets.
 */
class FrmDefGoogleSpreadsheetAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_googlesheets_icon frm_show_upgrade' );
		$action_ops['color']       = 'var(--green)';
		$action_ops['description'] = __( 'Spreadsheet sync', 'formidable' );
		parent::__construct( 'googlespreadsheet', __( 'Google Sheets', 'formidable' ), $action_ops );
	}
}

class FrmDefHrsAction extends FrmFormAction {
	public function __construct() {
		_deprecated_function( __METHOD__, '6.5' );

		$action_ops           = FrmFormAction::default_action_opts( 'frm_stripe_icon frm_credit_card_alt_icon frm_show_upgrade' );
		$action_ops['color']  = 'var(--green)';
		$action_ops['plugin'] = 'stripe';
		parent::__construct( 'payment', __( 'eCommerce', 'formidable' ), $action_ops );
	}
}

class FrmDefConvertKitAction extends FrmFormAction {
	public function __construct() {
		$action_ops                = FrmFormAction::default_action_opts( 'frm_convertkit_icon frm_show_upgrade' );
		$action_ops['color']       = 'rgb(68 177 255)';
		$action_ops['description'] = __( 'Broadcast publishing', 'formidable' );
		parent::__construct( 'convertkit', 'Kit', $action_ops );
	}
}
