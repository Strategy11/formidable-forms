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
}
