<?php
/**
 * In-plugin summary emails controller
 *
 * @since 6.7
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmEmailSummaryController
 */
class FrmEmailSummaryController {

	/**
	 * Maybe send summary emails.
	 */
	public static function maybe_send_emails() {
		$emails = FrmEmailSummaryHelper::should_send_emails();
		if ( ! $emails ) {
			return;
		}

		foreach ( $emails as $email ) {
			if ( method_exists( 'FrmEmailSummaryHelper', 'send_' . $email ) ) {
				call_user_func( array( 'FrmEmailSummaryHelper', 'send_' . $email ) );
			}
		}
	}
}
