<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteSubscription extends FrmTransLiteDb {

	public $table_name = 'frm_subscriptions';
	public $singular   = 'subscription';

	/**
	 * @return array
	 */
	public function get_defaults() {
		$values = array(
			'sub_id'         => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => '',
			),
			'item_id'        => array(
				'sanitize' => 'absint',
				'default'  => '',
			),
			'amount'         => array(
				'sanitize' => 'float',
				'default'  => '',
			),
			'first_amount'   => array(
				'sanitize' => 'float',
				'default'  => '',
			),
			'action_id'      => array(
				'sanitize' => 'absint',
				'default'  => 0,
			),
			'interval_count' => array(
				'sanitize' => 'absint',
				'default'  => 1,
			),
			'time_interval'  => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => '',
			),
			'end_count'      => array(
				'sanitize' => 'absint',
				'default'  => 9999,
			),
			'paysys'         => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => 'manual',
			),
			'status'         => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => 'pending',
			),
			'fail_count'     => array(
				'sanitize' => 'absint',
				'default'  => 0,
			),
			'created_at'     => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => current_time( 'mysql', 1 ),
			),
			'next_bill_date' => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => '0000-00-00',
			),
			'meta_value'     => array(
				'sanitize' => 'maybe_serialize',
				'default'  => '',
			),
			'test'           => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => null,
			),
		);

		return $values;
	}

	/**
	 * @return array
	 */
	public function get_overdue_subscriptions() {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `{$wpdb->prefix}frm_subscriptions`
				WHERE fail_count < %d
					AND next_bill_date < %s
					AND (status = 'active' OR status = 'future_cancel')",
				3,
				gmdate( 'Y-m-d' )
			)
		);
	}
}
