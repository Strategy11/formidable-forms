<?php
/**
 * Summary data model class
 *
 * @since x.x
 * @package Formidable
 */

class FrmSummaryData {

	protected $from_date;

	protected $to_date;

	protected $data = array(
		'top_forms' => array(),
		'entries'   => 0,
	);

	public function __construct( $from_date, $to_date ) {
		$this->from_date = $from_date;
		$this->to_date   = $to_date;
	}

	public function get_data() {
		return $this->data;
	}
}
