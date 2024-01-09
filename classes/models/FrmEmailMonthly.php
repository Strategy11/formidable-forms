<?php
/**
 * Monthly summary email class
 *
 * @since 6.7
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmEmailMonthly extends FrmEmailStats {

	protected $date_range = 30;

	protected $has_inbox_msg = true;

	protected function get_subject() {
		return __( 'How your forms performed this month', 'formidable' );
	}

	protected function get_top_forms_label() {
		return __( 'Top forms this month', 'formidable' );
	}
}
