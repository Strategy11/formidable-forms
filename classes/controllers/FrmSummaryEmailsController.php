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
		$emails = FrmSummaryEmailsHelper::should_send_emails();
		if ( ! $emails ) {
			return;
		}

		foreach ( $emails as $email ) {
			if ( method_exists( 'FrmSummaryEmailsHelper', 'send_' . $email ) ) {
				call_user_func( array( 'FrmSummaryEmailsHelper', 'send_' . $email ) );
			}
		}
	}
}
