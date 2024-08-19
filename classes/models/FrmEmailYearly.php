<?php
/**
 * Yearly summary email class
 *
 * @since 6.7
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEmailYearly extends FrmEmailStats {

	protected $date_range = 365;

	protected function get_subject() {
		return __( 'How your forms performed this year', 'formidable' );
	}

	protected function get_top_forms_label() {
		return __( 'Top forms this year', 'formidable' );
	}

	/**
	 * @since 6.8
	 */
	protected function get_content_args() {
		// Do not send yearly email if there is no entries this year.
		if ( ! FrmEmailSummaryHelper::get_entries_count( $this->from_date, $this->to_date ) ) {
			return false;
		}

		return parent::get_content_args();
	}
}
