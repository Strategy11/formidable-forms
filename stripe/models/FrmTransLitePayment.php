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
				'default'  => '',
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
			'test'        => array(
				'sanitize' => 'sanitize_text_field',
				'default'  => null,
			),
		);
		return $values;
	}

	/**
	 * Gets payments statistic data.
	 *
	 * @since 6.7
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array            Contains `count` and `total`.
	 */
	public function get_payments_stats( $from_date = null, $to_date = null ) {
		$data = array(
			'count' => 0,
			'total' => array(),
		);

		if ( ! FrmTransLiteAppHelper::payments_table_exists() ) {
			return $data;
		}

		$where = array();
		if ( null !== $from_date ) {
			$where['created_at >'] = $from_date;
		}
		if ( null !== $to_date ) {
			$where['created_at <'] = $to_date . ' 23:59:59';
		}

		// Do not collect test payment, this is a new feature of Stripe lite.
		if ( 6 <= get_option( $this->db_opt_name ) ) {
			$where['test'] = array( null, 0 );
		}

		// If only Paypal is used, and the DB isn't migrated (to version 4), use `completed` column instead of `status`.
		if ( 4 > get_option( $this->db_opt_name ) && FrmDb::db_column_exists( $this->table_name, 'completed' ) ) {
			$where['completed'] = 1;
		} else {
			$where['status'] = 'complete';
		}

		$payments = FrmDb::get_results( $this->table_name, $where, 'action_id,amount' );

		if ( ! $payments ) {
			return $data;
		}

		$data['count'] = count( $payments );
		$data['total'] = $this->get_payment_total_data( $payments );

		return $data;
	}

	/**
	 * Gets payment total data.
	 *
	 * @since 6.7
	 *
	 * @param object[] $payments Array of payment objects.
	 * @return array Return array of total amount for each currency.
	 */
	private function get_payment_total_data( $payments ) {
		$data = array();
		foreach ( $payments as $payment ) {
			list( $amount, $currency ) = FrmTransLiteAppHelper::get_amount_and_currency_from_payment( $payment );

			if ( ! isset( $data[ $currency ] ) ) {
				$data[ $currency ] = 0;
			}

			$data[ $currency ] += floatval( $amount );
		}

		return $data;
	}
}
