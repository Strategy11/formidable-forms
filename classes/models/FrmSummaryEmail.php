<?php
/**
 * Summary email base class
 *
 * @since x.x
 * @package Formidable
 */

abstract class FrmSummaryEmail {

	abstract protected function get_subject();

	abstract protected function get_inner_content();

	abstract protected function get_plain_inner_content();

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
		return array();
	}

	protected function get_content() {
		return '<div style="background-color: cyan;">' . $this->get_inner_content() . '</div>' . $this->get_footer_content();
	}

	protected function get_footer_content() {
		return '<p>Unsubscribe this email</p>';
	}

	protected function get_url_data() {

	}

	public function send() {
		$receptions = $this->get_receptions();
		$content    = $this->get_content();
		$subject    = $this->get_subject();
		$headers    = $this->get_headers();
		wp_mail( $receptions, $subject, $content, $headers );
	}
}
