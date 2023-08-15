<?php
/**
 * Yearly summary email class
 *
 * @since x.x
 * @package Formidable
 */

class FrmYearlyEmail extends FrmStatsEmail {

	public function __construct() {
		$this->to_date   = date( 'Y-m-d' );
		$this->from_date = date( 'Y-m-d', strtotime( '-365 days' ) );
	}

	protected function get_subject() {
		return __( 'Your year with Formidable Forms', 'formidable' );
	}

	protected function get_top_forms_label() {
		return __( 'Top forms this year', 'formidable' );
	}
}
