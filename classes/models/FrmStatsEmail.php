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

abstract class FrmStatsEmail extends FrmSummaryEmail {

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

		$stats_data = FrmSummaryEmailsHelper::get_summary_data( $this->from_date, $this->to_date );

		$args['inbox_msg']       = $this->has_inbox_msg ? FrmSummaryEmailsHelper::get_latest_inbox_message() : false;
		$args['from_date']       = $this->from_date;
		$args['to_date']         = $this->to_date;
		$args['top_forms']       = $stats_data['top_forms'];
		$args['top_forms_label'] = $this->get_top_forms_label();
		$args['dashboard_url']   = FrmSummaryEmailsHelper::add_url_data( site_url() . '/wp-admin/admin.php?page=formidable' );
		$args['stats']           = array(
			'entries'        => array(
				'label'   => __( 'Entries created', 'formidable' ),
				'count'   => $stats_data['entries'],
				'compare' => 0,
			),
			'payments_count' => array(
				'label'   => __( 'Payments collected', 'formidable' ),
				'count'   => $stats_data['payments']['count'],
				'compare' => 0,
			),
			'payments_total' => array(
				'label'   => __( 'Total', 'formidable' ),
				'count'   => $stats_data['payments']['total'],
				'display' => $this->get_displayed_price( $stats_data['payments']['total'] ),
				'compare' => 0,
			),
		);

		if ( $this->has_out_of_date_plugins ) {
			$args['out_of_date_plugins'] = FrmSummaryEmailsHelper::get_out_of_date_plugins();
			$args['plugins_url']         = FrmSummaryEmailsHelper::add_url_data( site_url() . '/wp-admin/plugins.php' );
		}

		if ( $this->has_comparison ) {
			$prev_stats_data = FrmSummaryEmailsHelper::get_summary_data( $this->prev_from_date, $this->prev_to_date );
			$args['stats']['entries']['compare']        = $this->get_compare_diff( $stats_data['entries'], $prev_stats_data['entries'] );
			$args['stats']['payments_count']['compare'] = $this->get_compare_diff( $stats_data['payments']['count'], $prev_stats_data['payments']['count'] );
			$args['stats']['payments_total']['compare'] = $this->get_compare_diff( $stats_data['payments']['total'], $prev_stats_data['payments']['total'] );
		}

		return $args;
	}

	/**
	 * Gets comparison diff between 2 values.
	 *
	 * @param float $current Current value.
	 * @param float $prev    Previous value.
	 * @return float
	 */
	protected function get_compare_diff( $current, $prev ) {
		if ( ! $current && ! $prev ) {
			return 0; // No comparison if both are zero.
		}

		if ( ! $prev ) {
			return 1; // Increase 100%;
		}

		return ( $current - $prev ) / $prev;
	}

	/**
	 * Gets displayed string for price.
	 *
	 * @param float $amount Price amount.
	 * @return string
	 */
	protected function get_displayed_price( $amount ) {
		$settings = FrmAppHelper::get_settings();
		$currency = FrmCurrencyHelper::get_currency( $settings->currency );
		FrmTransLiteAppHelper::format_amount_for_currency( $currency, $amount );
		return $amount;
	}

	/**
	 * Gets label of Top forms section.
	 *
	 * @return string
	 */
	abstract protected function get_top_forms_label();
}
