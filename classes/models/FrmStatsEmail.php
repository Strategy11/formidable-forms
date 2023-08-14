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
}
