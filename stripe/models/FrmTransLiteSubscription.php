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
			'action_id'      => array(
				'default'  => 0,
				'sanitize' => 'absint',
			),
			'amount'         => array(
				'default'  => '',
				'sanitize' => 'float',
			),
			'created_at'     => array(
				'default'  => current_time( 'mysql', 1 ),
				'sanitize' => 'sanitize_text_field',
			),
			'end_count'      => array(
				'default'  => 9999,
				'sanitize' => 'absint',
			),
			'fail_count'     => array(
				'default'  => 0,
				'sanitize' => 'absint',
			),
			'first_amount'   => array(
				'default'  => '',
				'sanitize' => 'float',
			),
			'interval_count' => array(
				'default'  => 1,
				'sanitize' => 'absint',
			),
			'item_id'        => array(
				'default'  => '',
				'sanitize' => 'absint',
			),
			'meta_value'     => array(
				'default'  => '',
				'sanitize' => 'maybe_serialize',
			),
			'next_bill_date' => array(
				'default'  => '0000-00-00',
				'sanitize' => 'sanitize_text_field',
			),
			'paysys'         => array(
				'default'  => 'manual',
				'sanitize' => 'sanitize_text_field',
			),
			'status'         => array(
				'default'  => 'pending',
				'sanitize' => 'sanitize_text_field',
			),
			'sub_id'         => array(
				'default'  => '',
				'sanitize' => 'sanitize_text_field',
			),
			'test'           => array(
				'default'  => null,
				'sanitize' => 'sanitize_text_field',
			),
			'time_interval'  => array(
				'default'  => '',
				'sanitize' => 'sanitize_text_field',
			),
		);

		return $values;
	}

	/**
	 * @return array|object|null
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
