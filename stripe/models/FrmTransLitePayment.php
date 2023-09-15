<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLitePayment extends FrmTransLiteDb {

	public $table_name = 'frm_payments';
	public $singular   = 'payment';

	/**
	 * @return array
	 */
	public function get_defaults() {
		$values = array(
			'receipt_id'  => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => '',
			),
			'invoice_id'  => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => '',
			),
			'sub_id'      => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => '',
			),
			'item_id'     => array(
				'sanitize' => 'absint',
				'default' => '',
			),
			'amount'      => array(
				'sanitize' => 'float',
				'default'  => '',
			),
			'status'      => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => 'pending',
			),
			'action_id'   => array(
				'sanitize' => 'absint',
				'default'  => 0,
			),
			'paysys'      => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => 'manual',
			),
			'created_at'  => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => current_time( 'mysql', 1 ),
			),
			'begin_date'  => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => current_time( 'mysql', 1 ),
			),
			'expire_date' => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => '0000-00-00',
			),
			'meta_value'  => array(
				'sanitize' => 'maybe_serialize',
				'default'  => '',
			),
		);
		return $values;
	}
}
