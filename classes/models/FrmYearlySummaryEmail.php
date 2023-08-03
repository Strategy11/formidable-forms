<?php
/**
 * Yearly summary email class
 *
 * @since x.x
 * @package Formidable
 */

class FrmYearlySummaryEmail extends FrmSummaryEmail {

	protected function get_subject() {
		return 'Yearly email';
	}

	protected function get_inner_content() {
		return '<div color="red">Yearly content</div>';
	}

	protected function get_plain_inner_content() {
		return 'Yearly content';
	}
}
