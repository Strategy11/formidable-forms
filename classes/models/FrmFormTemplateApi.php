<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormTemplateApi extends FrmFormApi {

	protected static $code_option_name = 'frm_free_license_code';

	private static $base_api_url = 'https://formidableforms.com/wp-json/form-templates/v1/';

	protected static $free_license;

	/**
	 * @var int
	 */
	protected $new_days = 30;

	/**
	 * @var string
	 */
	protected $cache_timeout = '+12 hours';

	/**
	 * @since 3.06
	 *
	 * @return void
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_form_templates_l';

		if ( ! empty( $this->license ) ) {
			$this->cache_key .= md5( $this->license );
		} else {
			$free_license = $this->get_free_license();
			if ( $free_license ) {
				$this->cache_key .= md5( $free_license );
			}
		}
	}

	/**
	 * @since 3.06
	 *
	 * @return string
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
	 *
	 * @return string[]
	 */
	protected function skip_categories() {
		return array();
	}

	/**
	 * @return string
	 */
	public function get_free_license() {
		if ( ! isset( self::$free_license ) ) {
			self::$free_license = get_option( self::$code_option_name );
		}

		return self::$free_license;
	}

	/**
	 * Check to make sure the free code is being used.
	 *
	 * @since 4.09.02
	 *
	 * @return bool
	 */
	public function has_free_access() {
		$free_access = $this->get_free_license();
		if ( ! $free_access ) {
			return false;
		}

		$templates    = $this->get_api_info();
		$contact_form = 20872734;
		return isset( $templates[ $contact_form ] ) && ! empty( $templates[ $contact_form ]['url'] );
	}

	/**
	 * @param string $code the code from the email sent for the API.
	 *
	 * @return void
	 */
	private static function verify_code( $code ) {
		$base64_code = base64_encode( $code );
		$api_url     = self::$base_api_url . 'code?l=' . urlencode( $base64_code );
		$response    = wp_remote_get( $api_url );

		self::handle_verify_response_errors_if_any( $response );

		$decoded    = json_decode( $response['body'] );
		$successful = ! empty( $decoded->response );

		if ( $successful ) {
			self::on_api_verify_code_success( $code );
		} else {
			wp_send_json_error( new WP_Error( $decoded->code, $decoded->message ) );
		}
	}

	/**
	 * @return void
	 */
	private static function clear_template_cache_before_getting_free_templates() {
		delete_option( 'frm_form_templates_l' );
	}

	/**
	 * @param array $response
	 *
	 * @return void
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
	 * @param string $code The base64 encoded code.
	 *
	 * @return void
	 */
	private static function on_api_verify_code_success( $code ) {
		self::$free_license = $code;
		update_option( self::$code_option_name, $code, 'no' );

		$data = array();
		$key  = FrmAppHelper::get_param( 'key', '', 'post', 'sanitize_key' );

		// Remove message from Inbox page.
		$message = new FrmInbox();
		$message->remove( 'free_templates' );

		if ( $key ) {
			self::clear_template_cache_before_getting_free_templates();

			$data['urlByKey'] = array();
			$api              = new self();
			$templates        = $api->get_api_info();

			foreach ( $templates as $template ) {
				if ( ! isset( $template['url'] ) || ! in_array( 'free', $template['categories'], true ) ) {
					continue;
				}

				$data['urlByKey'][ $template['key'] ] = $template['url'];
			}

			if ( ! isset( $data['urlByKey'][ $key ] ) ) {
				$error = new WP_Error( 400, 'We were unable to retrieve the template' );
				wp_send_json_error( $error );
			}

			$data['url'] = $data['urlByKey'][ $key ];
		}//end if

		wp_send_json_success( $data );
	}

	/**
	 * AJAX Hook for signing free users up for a template API key
	 *
	 * @return void
	 */
	public static function signup() {
		$code = FrmAppHelper::get_param( 'code', '', 'post' );

		if ( ! $code ) {
			wp_send_json_error();
		}

		self::verify_code( $code );
	}
}
