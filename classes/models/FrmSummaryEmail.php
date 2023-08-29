<?php
/**
 * Summary email base class
 *
 * @since x.x
 * @package Formidable
 */

abstract class FrmSummaryEmail {

	protected $is_html = true;

	abstract protected function get_subject();

	abstract protected function get_inner_content();

	public function __construct() {
		$this->is_html = apply_filters( 'frm_html_summary_emails', true );
	}

	/**
	 * Gets receptions.
	 *
	 * @return string
	 */
	protected function get_receptions() {
		$receptions = FrmAppHelper::get_settings()->summary_emails_recipients;
		$receptions = str_replace( '[admin_email]', get_bloginfo( 'admin_email' ), $receptions );
		return $receptions;
	}

	protected function get_headers() {
		return array(
			'Content-Type: ' . ( $this->is_html ? 'text/html; charset=UTF-8' : 'text/plain' ),
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>',
		);
	}

	protected function get_content() {
		$args = $this->get_content_args();

		$suffix = $this->is_html ? '' : '-plain';

		ob_start();
		include $this->get_include_file( 'base' );
		$content = ob_get_clean();

		$content = str_replace( '%%INNER_CONTENT%%', $this->get_inner_content(), $content );

		return $content;
	}

	protected function get_include_file( $file_name ) {
		$suffix = $this->is_html ? '' : '-plain';
		return FrmAppHelper::plugin_path() . '/classes/views/summary-emails/' . $file_name . $suffix . '.php';
	}

	protected function get_content_args() {
		return array(
			'subject'         => $this->get_subject(),
			'site_url'        => FrmSummaryEmailsHelper::add_url_data( home_url( '/' ) ),
			'unsubscribe_url' => FrmSummaryEmailsHelper::add_url_data( site_url() . '/wp-admin/admin.php?page=formidable-settings&t=misc_settings' ),
		);
	}

	public function send() {
		$receptions = $this->get_receptions();
		$content    = $this->get_content();
		$subject    = $this->get_subject();
		$headers    = $this->get_headers();

		return wp_mail( $receptions, $subject, $content, $headers );
	}
}
