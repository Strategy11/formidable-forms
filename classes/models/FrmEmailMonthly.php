<?php
/**
 * Monthly summary email class
 *
 * @since x.x
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmEmailMonthly extends FrmEmailStats {

	protected $has_inbox_msg = true;

	public function __construct() {
		parent::__construct();

		$this->to_date        = gmdate( 'Y-m-d' );
		$this->from_date      = gmdate( 'Y-m-d', strtotime( '-29 days' ) );
		$this->prev_to_date   = gmdate( 'Y-m-d', strtotime( $this->from_date . '-1 day' ) );
		$this->prev_from_date = gmdate( 'Y-m-d', strtotime( $this->prev_to_date . '-29 days' ) );
	}

	protected function get_subject() {
		return __( 'How your forms performed this month', 'formidable' );
	}

	protected function get_top_forms_label() {
		return __( 'Top forms this month', 'formidable' );
	}
}
