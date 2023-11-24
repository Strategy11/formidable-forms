<?php
/**
 * Stats email class
 *
 * @since x.x
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

abstract class FrmEmailStats extends FrmEmailSummary {

	/**
	 * Should show inbox notice section?
	 *
	 * @var bool
	 */
	protected $has_inbox_msg = false;

	/**
	 * Should show comparison with the stats data of the previous date range?
	 *
	 * @var bool
	 */
	protected $has_comparison = true;

	/**
	 * Should show out of date plugin section?
	 *
	 * @var bool
	 */
	protected $has_out_of_date_plugins = true;

	/**
	 * From date.
	 *
	 * @var string
	 */
	protected $from_date;

	/**
	 * To date.
	 *
	 * @var string
	 */
	protected $to_date;

	/**
	 * From date of the previous date range.
	 *
	 * @var string
	 */
	protected $prev_from_date;

	/**
	 * To date of the previous date range.
	 *
	 * @var string
	 */
	protected $prev_to_date;

	/**
	 * @return mixed
	 */
	protected function get_inner_content() {
		$args = $this->get_content_args();

		ob_start();
		include $this->get_include_file( 'stats' );
		return ob_get_clean();
	}

	protected function get_content_args() {
		$args = parent::get_content_args();

		$entries_count = FrmSummaryEmailsHelper::get_entries_count( $this->from_date, $this->to_date );

		$args['inbox_msg']       = $this->has_inbox_msg ? FrmSummaryEmailsHelper::get_latest_inbox_message() : false;
		$args['from_date']       = $this->from_date;
		$args['to_date']         = $this->to_date;
		$args['top_forms']       = FrmSummaryEmailsHelper::get_top_forms( $this->from_date, $this->to_date );
		$args['top_forms_label'] = $this->get_top_forms_label();
		$args['dashboard_url']   = site_url() . '/wp-admin/admin.php?page=formidable';
		$args['stats']           = array(
			'entries' => array(
				'label'   => __( 'Entries created', 'formidable' ),
				'count'   => $entries_count,
				'compare' => 0,
			),
		);

		if ( $this->has_out_of_date_plugins ) {
			$args['out_of_date_plugins'] = FrmSummaryEmailsHelper::get_out_of_date_plugins();
			$args['plugins_url']         = site_url() . '/wp-admin/plugins.php';
		}

		$this->add_entries_comparison_data( $args['stats'] );
		$this->add_payments_data( $args['stats'] );

		return $args;
	}

	/**
	 * Adds entries comparison data.
	 *
	 * @param array $stats Statistics section data.
	 */
	protected function add_entries_comparison_data( &$stats ) {
		if ( ! $this->has_comparison ) {
			return;
		}

		$prev_entries_count = FrmSummaryEmailsHelper::get_entries_count( $this->prev_from_date, $this->prev_to_date );
		$stats['entries']['compare'] = $this->get_compare_diff( $stats['entries']['count'], $prev_entries_count );
	}

	/**
	 * Adds payments count and total data.
	 *
	 * @param array $stats Statistics section data.
	 */
	protected function add_payments_data( &$stats ) {
		$payment_data  = FrmSummaryEmailsHelper::get_payments_data( $this->from_date, $this->to_date );
		$stats['payments_count'] = array(
			'label'   => __( 'Payments collected', 'formidable' ),
			'count'   => $payment_data['count'],
			'compare' => 0,
		);

		// Build total for each currency.
		foreach ( $payment_data['total'] as $currency => $amount ) {
			$stats[ 'payments_total_' . $currency ] = array(
				'label'   => sprintf( __( 'Total %s', 'formidable' ), strtoupper( $currency ) ),
				'count'   => $amount,
				'display' => $this->get_formatted_price( $amount, $currency ),
				'compare' => 0,
			);
		}

		if ( $this->has_comparison ) {
			$prev_payment_data  = FrmSummaryEmailsHelper::get_payments_data( $this->prev_from_date, $this->prev_to_date );

			if ( ! $payment_data['count'] && ! $prev_payment_data['count'] ) {
				// Maybe this site doesn't collect payment, hide these sections.
				unset( $stats['payments_count'] );
				return;
			}

			$stats['payments_count']['compare'] = $this->get_compare_diff( $payment_data['count'], $prev_payment_data['count'] );

			// Compare total for each currency.
			foreach ( $payment_data['total'] as $currency => $amount ) {
				if ( ! isset( $prev_payment_data['total'][ $currency ] ) ) {
					$stats[ 'payments_total_' . $currency ]['compare'] = 1;
					continue;
				}

				$stats[ 'payments_total_' . $currency ]['compare'] = $this->get_compare_diff( $amount, $prev_payment_data['total'][ $currency ] );
				unset( $prev_payment_data['total'][ $currency ] );
			}

			// If prev month has more currencies.
			foreach ( $prev_payment_data['total'] as $currency => $amount ) {
				$stats[ 'payments_total_' . $currency ] = array(
					'label'   => sprintf( __( 'Total %s', 'formidable' ), strtoupper( $currency ) ),
					'count'   => 0,
					'display' => $this->get_formatted_price( 0, $currency ),
					'compare' => -1,
				);
			}
		}
	}

	/**
	 * Gets formatted price.
	 *
	 * @param float $amount Amount.
	 * @param string|array $currency Currency string value or array.
	 * @return string
	 */
	protected function get_formatted_price(&$amount, $currency ) {
		if ( ! is_array( $currency ) ) {
			$currency = FrmCurrencyHelper::get_currency( $currency );
		}
		FrmTransLiteAppHelper::format_amount_for_currency( $currency, $amount );
		return $amount;
	}

	/**
	 * Gets comparison diff between 2 values.
	 *
	 * @param float $current Current value.
	 * @param float $prev    Previous value.
	 * @return float
	 */
	public function get_compare_diff( $current, $prev ) {
		if ( ! $current && ! $prev ) {
			return 0; // No comparison if both are zero.
		}

		if ( ! $prev ) {
			return 1; // Increase 100%;
		}

		return ( $current - $prev ) / $prev;
	}

	/**
	 * Gets label of Top forms section.
	 *
	 * @return string
	 */
	abstract protected function get_top_forms_label();
}