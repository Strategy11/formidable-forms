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
		include FrmAppHelper::plugin_path() . '/classes/views/summary-emails/license-expired.php';
		$content = ob_get_clean();
		if ( ! $this->is_html ) {
			$content = html_entity_decode( strip_tags( $content ) );

			$content = str_replace(
				__( 'Renew Now', 'formidable' ),
				sprintf(
					__( 'Renew now at %s', 'formidable' ),
					$args['renew_url']
				),
				$content
			);
		}

		return $content;
	}

	protected function get_content_args() {
		$args = parent::get_content_args();

		$args['renew_url'] = FrmSummaryEmailsHelper::get_frm_url( 'account/downloads/', 'renew_url' );

		return $args;
	}
}
