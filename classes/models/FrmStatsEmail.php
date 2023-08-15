<?php
/**
 * Stats email class
 *
 * @since x.x
 * @package Formidable
 */

abstract class FrmStatsEmail extends FrmSummaryEmail {

	protected $has_inbox_notice = false;

	protected $from_date;

	protected $to_date;

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

		$args['from_date'] = $this->from_date;
		$args['to_date']   = $this->to_date;
		$args['top_forms'] = $stats_data['top_forms'];
		$args['top_forms_label'] = $this->get_top_forms_label();

		return $args;
	}

	abstract protected function get_top_forms_label();
}
