<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteConnectHelper {

	/**
	 * @return void
	 */
	public static function render_settings_container() {
		self::register_settings_scripts();
		?>
		<a id="frm_connect_square_with_oauth" class="button-primary frm-button-primary">
			<?php esc_html_e( 'Connect to Square', 'formidable' ); ?>
		</a>
		<?php
	}

	/**
	 * @return void
	 */
	private static function register_settings_scripts() {
		wp_register_script( 'formidable_square_settings', FrmSquareLiteAppHelper::plugin_url() . '/js/settings.js', array(), FrmAppHelper::plugin_version(), true );
		wp_enqueue_script( 'formidable_square_settings' );
	}

	public static function get_oauth_redirect_url() {
		$mode = self::get_mode_value();

		if ( self::get_account_id( $mode ) ) {
			// Do not allow for initialize if there is already a configured account id.
			return false;
		}

		$additional_body = array(
			'password'            => self::generate_client_password( $mode ),
			'user_id'             => get_current_user_id(),
			'frm_square_api_mode' => $mode,
		);

		// Clear the transient so it doesn't fail.
		delete_option( 'frm_square_last_verify_attempt' );
		$data = self::post_to_connect_server( 'oauth_request', $additional_body );

		if ( is_string( $data ) ) {
			return false;
		}

		if ( ! empty( $data->password ) ) {
			update_option( self::get_server_side_token_option_name( $mode ), $data->password, 'no' );
		}

		if ( ! is_object( $data ) || empty( $data->redirect_url ) ) {
			return false;
		}

		return $data->redirect_url;
	}

	/**
	 * @param string $action
	 * @param array  $additional_body
	 * @return object|string
	 */
	private static function post_to_connect_server( $action, $additional_body = array() ) {
		$body    = array(
			'frm_square_api_action' => $action,
			'frm_square_api_mode'   => FrmSquareLiteAppHelper::active_mode(),
		);
		$body    = array_merge( $body, $additional_body );
		$url     = self::get_url_to_connect_server();
		$headers = self::build_headers_for_post();

		if ( ! $headers ) {
			return 'Unable to build headers for post. Is your pro license configured properly?';
		}

		// (Seconds) default timeout is 5. we want a bit more time to work with.
		$timeout = 45;

		self::try_to_extend_server_timeout( $timeout );

		$args     = compact( 'body', 'headers', 'timeout' );
		$response = wp_remote_post( $url, $args );

		if ( ! self::validate_response( $response ) ) {
			return 'Response from server is invalid';
		}

		$body = self::pull_response_body( $response );
		if ( empty( $body->success ) ) {
			if ( ! empty( $body->data ) && is_string( $body->data ) ) {
				return $body->data;
			}
			return 'Response from server was not successful';
		}

		return isset( $body->data ) ? $body->data : array();
	}

	private static function pull_response_body( $response ) {
		$http_response   = $response['http_response'];
		$response_object = $http_response->get_response_object();
		return json_decode( $response_object->body );
	}

	/**
	 * @param mixed $response
	 * @return bool
	 */
	private static function validate_response( $response ) {
		return ! is_wp_error( $response ) && is_array( $response ) && isset( $response['http_response'] );
	}

	/**
	 * @return string
	 */
	private static function get_url_to_connect_server() {
		return 'http://dev-site.local/';
		//return 'https://api.strategy11.com/';
	}

	/**
	 * @return array
	 */
	private static function build_headers_for_post() {
		$password = self::maybe_get_pro_license();
		if ( false === $password ) {
			$password = 'lite_' . self::get_uuid();
		}

		$site_url = home_url();
		$site_url = self::maybe_fix_wpml_url( $site_url );
		// Remove protocol from url (our url cannot include the colon).
		$site_url = preg_replace( '#^https?://#', '', $site_url );
		// Remove port from url (mostly helpful in development).
		$site_url = preg_replace( '/:[0-9]+/', '', $site_url );
		$site_url = self::strip_lang_from_url( $site_url );

		// $password is either a Pro license or a uuid (See FrmUsage::uuid).
		return array(
			'Authorization' => 'Basic ' . base64_encode( $site_url . ':' . $password ),
		);
	}

	/**
	 * Get a unique ID to use for connecting Lite users.
	 *
	 * @return string
	 */
	private static function get_uuid() {
		$usage = new FrmUsage();
		return $usage->uuid();
	}

	/**
	 * WPML might add a language to the url. Don't send that to the server.
	 */
	private static function strip_lang_from_url( $url ) {
		$split_on_language = explode( '/?lang=', $url );
		if ( 2 === count( $split_on_language ) ) {
			$url = $split_on_language[0];
		}
		return $url;
	}

	/**
	 * WPML alters the output of home_url.
	 * If it is active, use the WPML "absolute home" URL which is not modified.
	 *
	 * @param string $url
	 * @return string
	 */
	private static function maybe_fix_wpml_url( $url ) {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
			global $wpml_url_converter;
			$url = $wpml_url_converter->get_abs_home();
		}
		return $url;
	}

	/**
	 * Get a Pro license when Pro is active.
	 * Otherwise we'll use a uuid to support Lite.
	 *
	 * @return false|string
	 */
	private static function maybe_get_pro_license() {
		if ( FrmAppHelper::pro_is_installed() ) {
			$pro_license = FrmAddonsController::get_pro_license();
			if ( $pro_license ) {
				$password = $pro_license;
			}
		}
		return ! empty( $password ) ? $password : false;
	}

	/**
	 * Try to make sure the server time limit exceeds the request time limit.
	 *
	 * @param int $timeout seconds.
	 *
	 * @return void
	 */
	private static function try_to_extend_server_timeout( $timeout ) {
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( $timeout + 10 );
		}
	}

	/**
	 * @param string $mode either 'auto', 'live', or 'test'.
	 * @return string
	 */
	private static function get_server_side_token_option_name( $mode = 'auto' ) {
		return self::get_square_connect_option_name( 'server_password', $mode );
	}

	/**
	 * Generate a new client password for authenticating with Connect Service and save it locally as an option.
	 *
	 * @param string $mode 'live' or 'test'.
	 * @return string the client password.
	 */
	private static function generate_client_password( $mode ) {
		$client_password = wp_generate_password();
		update_option( self::get_client_side_token_option_name( $mode ), $client_password, 'no' );
		return $client_password;
	}

	/**
	 * @param string $mode either 'auto', 'live', or 'test'.
	 * @return string
	 */
	private static function get_client_side_token_option_name( $mode = 'auto' ) {
		return self::get_square_connect_option_name( 'client_password', $mode );
	}

	/**
	 * @return string
	 */
	private static function get_mode_value() {
		return 'live';
	}

	/**
	 * @param string $mode
	 * @return bool|string
	 */
	public static function get_account_id( $mode = 'auto' ) {
		return get_option( self::get_account_id_option_name( $mode ) );
	}

	/**
	 * @param string $mode either 'auto', 'live', or 'test'.
	 * @return string
	 */
	private static function get_account_id_option_name( $mode = 'auto' ) {
		return self::get_square_connect_option_name( 'account_id', $mode );
	}

	/**
	 * @param string $key 'account_id', 'client_password', 'server_password', 'details_submitted'.
	 * @param string $mode either 'auto', 'live', or 'test'.
	 * @return string
	 */
	private static function get_square_connect_option_name( $key, $mode = 'auto' ) {
		return 'frm_square_connect_' . $key . self::get_active_mode_option_name_suffix( $mode );
	}

	/**
	 * @param string $mode either 'auto', 'live', or 'test'.
	 * @return string either _test or _live.
	 */
	private static function get_active_mode_option_name_suffix( $mode = 'auto' ) {
		if ( 'auto' !== $mode ) {
			return '_' . $mode;
		}
		return '_' . FrmSquareLiteAppHelper::active_mode();
	}
}
