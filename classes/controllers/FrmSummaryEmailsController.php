<?php
/**
 * In-plugin summary emails controller
 *
 * @since x.x
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

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

		$current_date = gmdate( 'Y-m-d' );

		// Check for license expired email.
		$last_expired = FrmSummaryEmailsHelper::get_last_sent_date( 'license' ); // TODO: clear this sent date after renewing.
		if ( ! $last_expired ) {
			// License expired email hasn't been sent. Check for the license.
			if ( FrmAddonsController::is_license_expired() ) {
				FrmSummaryEmailsHelper::send_license_expired_email();
			}
		}

		// Check for monthly or yearly email.
		$last_monthly = FrmSummaryEmailsHelper::get_last_sent_date( 'monthly' );
		$last_yearly  = FrmSummaryEmailsHelper::get_last_sent_date( 'yearly' );
		$last_stats   = max( $last_monthly, $last_yearly );

		// Do not send any email if it isn't enough 30 days from the last stats email.
		if ( $last_stats && 30 > FrmSummaryEmailsHelper::get_date_diff( $current_date, $last_stats ) ) {
			return;
		}

		if ( $last_yearly ) {
			// If this isn't the first yearly email, send the new one after 1 year.
			if ( $last_yearly && 365 <= FrmSummaryEmailsHelper::get_date_diff( $current_date, $last_yearly ) ) {
				FrmSummaryEmailsHelper::send_yearly_email();
				return;
			}
		} else {
			// If no yearly email has been sent, send it if it's less than 45 days until the renewal date.
			$renewal_date = FrmSummaryEmailsHelper::get_renewal_date();
			if ( $renewal_date && 45 <= FrmSummaryEmailsHelper::get_date_diff( $current_date, $renewal_date ) ) {
				FrmSummaryEmailsHelper::send_yearly_email();
				return;
			}
		}

		// If it isn't time for yearly email, it's time for monthly email.
		FrmSummaryEmailsHelper::send_monthly_email();
	}
}
