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
		self::maybe_skip_email( $emails );

		if ( ! $emails ) {
			return;
		}

		foreach ( $emails as $email ) {
			if ( method_exists( 'FrmEmailSummaryHelper', 'send_' . $email ) ) {
				call_user_func( array( 'FrmEmailSummaryHelper', 'send_' . $email ) );
			}
		}
	}

	/**
	 * Skips yearly email if there is no entry in that period.
	 *
	 * @since x.x
	 *
	 * @param array $emails Summary emails should be sent.
	 */
	private static function maybe_skip_email( &$emails ) {
		$index = array_search( FrmEmailSummaryHelper::YEARLY, $emails );
		if ( false === $index ) {
			return;
		}

		$to_date       = FrmEmailSummaryHelper::get_date_from_today();
		$from_date     = gmdate( 'Y-m-d', strtotime( $to_date . '-364 days' ) );
		$entries_count = FrmEmailSummaryHelper::get_entries_count( $from_date, $to_date );
		if ( ! $entries_count ) {
			unset( $emails[ $index ] );
		}
	}
}
