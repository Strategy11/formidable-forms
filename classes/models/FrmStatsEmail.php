<?php
/**
 * Stats email class
 *
 * @since x.x
 * @package Formidable
 */

abstract class FrmStatsEmail extends FrmSummaryEmail {

	protected $has_inbox_msg = false;

	protected $has_comparison = true;

	protected $from_date;

	protected $to_date;

	protected $prev_from_date;

	protected $prev_to_date;

	/**
	 * @return mixed
	 */
	protected function get_inner_content() {
		$args = $this->get_content_args();

		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/summary-emails/stats-email.php';
		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	protected function get_plain_inner_content() {
		// TODO: Implement get_plain_inner_content() method.
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
			'entries' => array(
				'label' => __( 'Entries created', 'formidable' ),
				'count' => $stats_data['entries'],
				'compare' => 0,
			),
		);

		if ( $this->has_comparison && $stats_data['entries'] ) {
			$prev_stats_data = FrmSummaryEmailsHelper::get_summary_data( $this->prev_from_date, $this->prev_to_date );
			$args['stats']['entries']['compare'] = ( $stats_data['entries'] - $prev_stats_data['entries'] ) / $stats_data['entries'];
		}

		return $args; // TODO: add filter.
	}

	abstract protected function get_top_forms_label();
}
