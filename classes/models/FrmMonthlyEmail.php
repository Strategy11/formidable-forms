<?php
/**
 * Monthly summary email class
 *
 * @since x.x
 * @package Formidable
 */

class FrmMonthlyEmail extends FrmStatsEmail {

	protected $has_inbox_notice = true;

	public function __construct() {
		$this->to_date        = date( 'Y-m-d' );
		$this->from_date      = date( 'Y-m-d', strtotime( '-29 days' ) );
		$this->prev_to_date   = date( 'Y-m-d', strtotime( $this->from_date . '-1 day' ) );
		$this->prev_from_date = date( 'Y-m-d', strtotime( $this->prev_to_date . '-29 days' ) );
	}

	protected function get_subject() {
		return __( 'Your month with Formidable Forms', 'formidable' );
	}

	protected function get_top_forms_label() {
		return __( 'Top forms this month', 'formidable' );
	}
}
