<?php
/**
 * Yearly summary email class
 *
 * @since x.x
 * @package Formidable
 */

class FrmYearlyEmail extends FrmStatsEmail {

	public function __construct() {
		parent::__construct();

		$this->to_date        = date( 'Y-m-d' );
		$this->from_date      = date( 'Y-m-d', strtotime( '-364 days' ) );
		$this->prev_to_date   = date( 'Y-m-d', strtotime( $this->from_date . '-1 day' ) );
		$this->prev_from_date = date( 'Y-m-d', strtotime( $this->prev_to_date . '-364 days' ) );
	}

	protected function get_subject() {
		return __( 'Your year with Formidable Forms', 'formidable' );
	}

	protected function get_top_forms_label() {
		return __( 'Top forms this year', 'formidable' );
	}
}
