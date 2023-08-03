<?php
/**
 * In-plugin summary emails controller
 *
 * @since x.x
 * @package Formidable
 */

/**
 * Class FrmSummaryEmailsController
 */
class FrmSummaryEmailsController {

	/**
	 * Maybe send summary emails.
	 */
	public static function maybe_send_emails() {
		if ( ! FrmSummaryEmailsHelper::is_enabled() ) {
			return;
		}

		if ( FrmSummaryEmailsHelper::should_send_license_expired_email() ) {
			FrmSummaryEmailsHelper::send_license_expired_email();
			return;
		}

		if ( FrmSummaryEmailsHelper::should_send_yearly_email() ) {
			FrmSummaryEmailsHelper::send_yearly_email();
			return;
		}

		if ( FrmSummaryEmailsHelper::should_send_monthly_email() ) {
			FrmSummaryEmailsHelper::send_monthly_email();
		}
	}
}
