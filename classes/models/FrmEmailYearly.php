<?php
/**
 * Yearly summary email class
 *
 * @since x.x
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEmailYearly extends FrmEmailStats {

	public function __construct() {
		parent::__construct();

		$this->to_date        = gmdate( 'Y-m-d' );
		$this->from_date      = gmdate( 'Y-m-d', strtotime( '-364 days' ) );
		$this->prev_to_date   = gmdate( 'Y-m-d', strtotime( $this->from_date . '-1 day' ) );
		$this->prev_from_date = gmdate( 'Y-m-d', strtotime( $this->prev_to_date . '-364 days' ) );
	}

	protected function get_subject() {
		return __( 'How your forms performed this year', 'formidable' );
	}

	protected function get_top_forms_label() {
		return __( 'Top forms this year', 'formidable' );
	}
}
