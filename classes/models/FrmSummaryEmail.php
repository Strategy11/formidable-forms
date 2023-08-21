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
		return array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>',
		);
	}

	protected function get_content() {
		$args = $this->get_content_args();

		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/summary-emails/base-email.php';
		$content = ob_get_clean();

		$content = str_replace( '%%INNER_CONTENT%%', $this->get_inner_content(), $content );

		return $content;
	}

	protected function get_content_args() {
		return array(
			'subject'         => $this->get_subject(),
			'site_url'        => home_url( '/' ),
			'unsubscribe_url' => site_url() . '/wp-admin/admin.php?page=formidable-settings&t=misc_settings',
		);
	}

	protected function get_url_data() {

	}

	public function send() {
		$receptions = $this->get_receptions();
		$content    = $this->get_content();
		header( 'Content-Type: text/html' ); die( $content ); // TODO: Remove this.
		$subject    = $this->get_subject();
		$headers    = $this->get_headers();
		error_log( 'Sending mail:' );
		error_log( $receptions );
		error_log( $subject );
		$result = wp_mail( $receptions, $subject, $content, $headers );
		error_log( $result );
	}
}
