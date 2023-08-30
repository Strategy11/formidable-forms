<?php
/**
 * License expired email class
 *
 * @since x.x
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmLicenseExpiredEmail extends FrmSummaryEmail {

	protected function get_subject() {
		return __( 'Your Formidable Forms license is expired', 'formidable' );
	}

	protected function get_inner_content() {
		$args = $this->get_content_args();

		ob_start();
		include $this->get_include_file( 'license-expired' );
		return ob_get_clean();
	}

	protected function get_content_args() {
		$args = parent::get_content_args();

		$args['renew_url'] = FrmSummaryEmailsHelper::add_url_data( 'https://formidableforms.com/knowledgebase/manage-licenses-and-sites/renewing-an-expired-license/' );

		return $args;
	}
}
