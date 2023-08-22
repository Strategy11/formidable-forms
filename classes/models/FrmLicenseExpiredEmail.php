<?php
/**
 * License expired email class
 *
 * @since x.x
 * @package Formidable
 */

class FrmLicenseExpiredEmail extends FrmSummaryEmail {

	protected function get_subject() {
		return __( 'Your Formidable Forms license is expired', 'formidable' );
	}

	protected function get_inner_content() {
		$args = $this->get_content_args();

		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/summary-emails/license-expired-email.php';
		return ob_get_clean();
	}

	protected function get_plain_inner_content() {
		// TODO: Implement get_plain_inner_content() method.
	}

	protected function get_content_args() {
		$args = parent::get_content_args();

		$args['renew_url'] = FrmSummaryEmailsHelper::add_url_data( 'https://formidableforms.com/knowledgebase/manage-licenses-and-sites/renewing-an-expired-license/' );

		return $args;
	}
}
