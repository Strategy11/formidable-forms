<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormTemplateApi extends FrmFormApi {

	protected static $code_option_name  = 'frm_free_license_code';

	private static $base_api_url = 'https://formidableforms.com/wp-json/form-templates/v1/';

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
		$url = self::$base_api_url . 'list';

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
	 * @param string $code the code from the email sent for the API
	 */
	private static function verify_code( $code ) {
		$base64_code = base64_encode( $code );
		$api_url     = self::$base_api_url . 'code?l=' . $base64_code;
		$response    = wp_remote_get( $api_url );

		self::handle_verify_response_errors_if_any( $response );

		$decoded    = json_decode( $response['body'] );
		$successful = ! empty( $decoded->response );

		if ( $successful ) {
			self::on_api_verify_code_success( $base64_code );
		} else {
			wp_send_json_error( new WP_Error( $decoded->code, $decoded->message ) );
		}
	}

	/**
	 * @param array $response
	 */
	private static function handle_verify_response_errors_if_any( $response ) {
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response );
		}

		if ( ! is_array( $response ) ) {
			wp_send_json_error();
		}
	}

	/**
	 * @param string $code the base64 encoded code
	 */
	private static function on_api_verify_code_success( $code ) {
		update_option( self::$code_option_name, $code );

		$data = array();
		$key  = FrmAppHelper::get_param( 'key', '', 'post', 'sanitize_key' );

		if ( $key ) {
			$api       = new self();
			$templates = $api->get_api_info();

			foreach ( $templates as $template ) {
				if ( $key === $template['key'] ) {
					$data['url'] = $template['url'];
					break;
				}
			}
		}

		wp_send_json_success( $data );
	}

	/**
	 * AJAX Hook for signing free users up for a template API key
	 */
	public static function signup() {
		$code = FrmAppHelper::get_param( 'code', '', 'post' );

		if ( ! $code ) {
			wp_send_json_error();
		}

		self::verify_code( $code );
	}
}
