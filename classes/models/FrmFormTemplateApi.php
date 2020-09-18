<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormTemplateApi extends FrmFormApi {

	/**
	 * @since 3.06
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_form_templates_l' . ( empty( $this->license ) ? '' : md5( $this->license ) );
	}

	/**
	 * @since 3.06
	 */
	protected function api_url() {
		return 'https://formidableforms.com/wp-json/form-templates/v1/list';
	}

	/**
	 * @since 3.06
	 */
	protected function skip_categories() {
		return array();
	}

	/**
	 * @param string $email
	 */
	private static function verify_email( $email ) {
		// TODO call the API with this email to trigger an email with the required code
		// TODO save this email somewhere in the database
		wp_send_json_success();
	}

	/**
	 * @param string $code
	 */
	private static function verify_code( $code ) {
		// TODO call the API with this code, verify that it's a valid code
		// TODO Save the code in the database for later use when calling the API for free templates

		// TODO remove this
		// start temporary workaround since API has not been updated
		// Return a success response if the code entered is "frm", for testing
		// Otherwise, return an error
		if ( $code === 'frm' ) {
			wp_send_json_success();
		}
		wp_send_json_error();
		// end temporary workaround
	}

	public static function signup() {
		$email = FrmAppHelper::get_param( 'email', '', 'post', 'sanitize_email' );
		$code  = FrmAppHelper::get_param( 'code', '', 'post' );

		if ( $email ) {
			if ( is_email( $email ) ) {
				self::verify_email( $email );
			} else {
				// todo handle invalid email
			}
		} elseif ( $code ) {
			self::verify_code( $code );
		} else {
			// todo handle invalid input
		}
	}
}
