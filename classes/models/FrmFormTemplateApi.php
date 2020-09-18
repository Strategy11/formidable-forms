<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormTemplateApi extends FrmFormApi {

	protected static $email_option_name = 'frm_email';
	protected static $code_option_name  = 'frm_free_license_code';

	protected $free_license;

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
		$url = 'https://formidableforms.com/wp-json/form-templates/v1/list';

		if ( empty( $this->license ) ) {
			$free_license = $this->get_free_license();

			if ( $free_license ) {
				$url .= '?l=' . urlencode( base64_encode( $free_license ) );
				$url .= '&v=' . FrmAppHelper::plugin_version();
			}
		}

		return $url;
	}

	/**
	 * @since 3.06
	 */
	protected function skip_categories() {
		return array();
	}

	/**
	 * @return string
	 */
	public function get_free_license() {
		if ( ! isset( $this->free_license ) ) {
			$this->free_license = get_option( self::$code_option_name );
		}

		return $this->free_license;
	}

	/**
	 * @param string $email
	 */
	private static function verify_email( $email ) {
		// TODO call the API with this email to trigger an email with the required code
		self::on_api_verify_email_success( $email );
		wp_send_json_success();
	}

	/**
	 * @param string $email
	 */
	private static function on_api_verify_email_success( $email ) {
		update_option( self::$email_option_name, $email );
	}

	/**
	 * @param string $code the code from the email sent from the API
	 */
	private static function verify_code( $code ) {
		// TODO call the API with email and code, verify that it's a valid code
		// $email = get_option( self::$email_option_name );

		// TODO remove this
		// start temporary workaround since API has not been updated
		// Return a success response if the code entered is "frm", for testing
		// Otherwise, return an error
		if ( $code === 'frm' ) {
			self::on_api_verify_code_success( $code );
		}
		wp_send_json_error();
		// end temporary workaround
	}

	/**
	 * @param string $code
	 */
	private static function on_api_verify_code_success( $code ) {
		update_option( self::$code_option_name, $code );

		$data = array();
		$key  = FrmAppHelper::get_param( 'key', '', 'post', 'sanitize_key' );

		if ( $key ) {
			// TODO return a url to use for creating the new form
		}

		wp_send_json_success( $data );
	}

	/**
	 * AJAX Hook for signing free users up for a template API key
	 */
	public static function signup() {
		$email = FrmAppHelper::get_param( 'email', '', 'post', 'sanitize_email' );
		$code  = FrmAppHelper::get_param( 'code', '', 'post' );

		if ( $email ) {
			if ( is_email( $email ) ) {
				self::verify_email( $email );
			} else {
				// TODO handle invalid email
			}
		} elseif ( $code ) {
			self::verify_code( $code );
		} else {
			// TODO handle invalid input
		}
	}
}
