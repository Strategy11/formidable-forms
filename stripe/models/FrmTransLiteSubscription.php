<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteSubscription extends FrmTransLiteDb {

	public $table_name = 'frm_subscriptions';
	public $singular   = 'subscription';

	/**
	 * Check whether the current user can cancel a subscription at its gateway.
	 *
	 * @since x.x
	 *
	 * @param string $sub_id The gateway subscription ID.
	 * @param string $paysys The gateway that owns the subscription.
	 *
	 * @return bool
	 */
	public static function current_user_can_cancel( $sub_id, $paysys ) {
		$user_id = get_current_user_id();

		if ( ! $user_id || ! $sub_id ) {
			return false;
		}

		if ( FrmAppHelper::current_user_can( 'frm_edit_entries' ) || current_user_can( 'administrator' ) ) {
			return true;
		}

		global $wpdb;
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT s.id FROM %i s INNER JOIN %i e ON e.id = s.item_id WHERE s.sub_id = %s AND s.paysys = %s AND e.user_id = %d LIMIT 1',
				$wpdb->prefix . 'frm_subscriptions',
				$wpdb->prefix . 'frm_items',
				$sub_id,
				$paysys,
				$user_id
			)
		);
	}

	/**
	 * @return array
	 */
	public function get_defaults() {
		return array(
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

	/**
	 * Get all active or future_cancel subscriptions without next_bill_date check.
	 *
	 * @since 6.32
	 *
	 * @return array
	 */
	public function get_active_subscriptions() {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM %i WHERE (status = 'active' OR status = 'future_cancel')",
				$wpdb->prefix . 'frm_subscriptions'
			)
		);
	}
}
