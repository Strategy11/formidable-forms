<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteConnectHelper {

	/**
	 * Track the latest error when calling stripe connect.
	 *
	 * @since 6.5
	 *
	 * @var string|null
	 */
	public static $latest_error_from_square_api;

	/**
	 * @return void
	 */
	public static function render_settings_container() {
		$settings = FrmSquareLiteAppHelper::get_settings();

		self::register_settings_scripts();

		?>
		<table class="form-table" style="width: 400px;">
			<tr class="form-field">
				<td>
					<?php esc_html_e( 'Test Mode', 'formidable' ); ?>
				</td>
				<td>
					<label>
						<input type="checkbox" name="frm_square_test_mode" id="frm_square_test_mode" value="1" <?php checked( $settings->settings->test_mode, 1 ); ?> />
						<?php esc_html_e( 'Use the Square test mode', 'formidable' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<div>
			<div class="frm_grid_container">
			<?php

			$modes = array( 'live', 'test' );
			foreach ( $modes as $mode ) {
				self::render_settings_for_mode( $mode );
			}
			?>
			</div>
		</div>
		<?php if ( ! is_ssl() ) { ?>
			<div><em><?php esc_html_e( 'Your site is not using SSL. Before using Square to collect live payments, you will need to install an SSL certificate on your site.', 'formidable' ); ?></em></div>
		<?php } ?>
		<?php
	}

	/**
	 * @param string $mode
	 * @return void
	 */
	private static function render_settings_for_mode( $mode ) {
		?>
		<div class="frm-card-item frm4">
			<div class="frm-flex-col">
				<div>
					<span style="font-size: var(--text-lg); font-weight: 500; margin-right: 5px;">
						<?php
						echo $mode === 'test' ? esc_html__( 'Test', 'formidable' ) : esc_html__( 'Live', 'formidable' );
						?>
					</span>
					<?php

					$connected = (bool) self::get_merchant_id( $mode );

					$tag_classes = '';
					if ( $connected ) {
						$tag_classes = 'frm-lt-green-tag';
					} else {
						$tag_classes = 'frm-grey-tag';
					}
					?>
					<div class="frm-meta-tag <?php echo esc_attr( $tag_classes ); ?>" style="font-size: var(--text-sm); font-weight: 600;">
						<?php
						if ( $connected ) {
							FrmAppHelper::icon_by_class( 'frm_icon_font frm_checkmark_icon', array( 'style' => 'width: 10px; position: relative; top: 2px; margin-right: 5px;' ) );
							echo 'Connected';
						} else {
							echo 'Not configured';
						}
						?>
					</div>
				</div>
				<div style="margin-top: 5px; flex: 1;">
					<?php
					if ( 'live' === $mode ) {
						esc_html_e( 'Live version to process real customer transactions', 'formidable' );
					} else {
						esc_html_e( 'Simulate payments and ensure everything works smoothly before going live.', 'formidable' );
					}
					?>
				</div>
				<div class="frm-card-bottom">
					<?php if ( $connected ) { ?>
						<a id="frm_disconnect_square" class="button-secondary frm-button-secondary" href="#"><?php esc_html_e( 'Disconnect', 'formidable' ); ?></a>
					<?php } else { ?>
						<a class="frm-connect-square-with-oauth button-secondary frm-button-secondary" data-mode="<?php echo esc_attr( $mode ); ?>" href="#">
							<?php esc_html_e( 'Connect', 'formidable' ); ?>
						</a>
					<?php } ?>
				</div>
			</div>
		</div>
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
		$mode = FrmAppHelper::get_post_param( 'mode', 'test', 'sanitize_text_field' );

		if ( self::get_merchant_id( $mode ) ) {
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
		$settings = FrmSquareLiteAppHelper::get_settings();
		return $settings->settings->test_mode ? 'test' : 'live';
	}

	/**
	 * @param string $mode
	 * @return bool|string
	 */
	public static function get_merchant_id( $mode = 'auto' ) {
		if ( 'auto' === $mode ) {
			$mode = self::get_mode_value();
		}
		return get_option( self::get_merchant_id_option_name( $mode ) );
	}

	/**
	 * @param string $mode either 'auto', 'live', or 'test'.
	 * @return string
	 */
	private static function get_merchant_id_option_name( $mode = 'auto' ) {
		return self::get_square_connect_option_name( 'merchant_id', $mode );
	}

	/**
	 * @param string $key 'merchant_id', 'client_password', 'server_password', 'details_submitted'.
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

	public static function check_for_redirects() {
		if ( self::user_landed_on_the_oauth_return_url() ) {
			self::redirect_oauth();
		}
	}

	/**
	 * @return bool
	 */
	private static function user_landed_on_the_oauth_return_url() {
		return isset( $_GET['frm_square_api_return_oauth'] );
	}

	private static function redirect_oauth() {
		$connected = self::check_server_for_oauth_merchant_id();
		header( 'Location: ' . self::get_url_for_square_settings( $connected ), true, 302 );
		exit;
	}

	/**
	 * @param bool $connected
	 * @return string
	 */
	private static function get_url_for_square_settings( $connected ) {
		return admin_url( 'admin.php?page=formidable-settings&t=square_settings&connected=' . intval( $connected ) );
	}

	/**
	 * @return bool
	 */
	private static function check_server_for_oauth_merchant_id() {
		$code = FrmAppHelper::simple_get( 'code' );
		$mode = 0 === strpos( $code, 'sandbox-' ) ? 'test' : 'live';

		if ( self::get_merchant_id( $mode ) ) {
			// Do not allow for initialize if there is already a configured merchant id.
			return false;
		}

		$body = array(
			'server_password'     => get_option( self::get_server_side_token_option_name( $mode ) ),
			'client_password'     => get_option( self::get_client_side_token_option_name( $mode ) ),
			'frm_square_api_mode' => $mode,
		);
		$data = self::post_to_connect_server( 'oauth_merchant_status', $body );

		if ( is_object( $data ) && ! empty( $data->merchant_id ) ) {
			update_option( self::get_merchant_id_option_name( $mode ), $data->merchant_id, 'no' );
			return true;
		}

		return false;
	}

	public static function create_payment( $amount, $currency, $square_token, $verification_token ) {
		return self::post_with_authenticated_body(
			'create_payment',
			array(
				'amount'             => $amount,
				'currency'           => $currency,
				'square_token'       => $square_token,
				'verification_token' => $verification_token,
			)
		);
	}

	/**
	 * @param string $action
	 * @param array  $additional_body
	 *
	 * @return false|object
	 */
	private static function post_with_authenticated_body( $action, $additional_body = array() ) {
		$body     = array_merge( self::get_standard_authenticated_body(), $additional_body );
		$response = self::post_to_connect_server( $action, $body );
		if ( is_object( $response ) ) {
			return $response;
		}
		if ( is_array( $response ) ) {
			// reformat empty arrays as empty objects
			// if the response is an array, it's because it's empty. Everything with data is already an object.
			return new stdClass();
		}
		if ( is_string( $response ) ) {
			self::$latest_error_from_square_api = $response;
			FrmTransLiteLog::log_message( 'Square API Error', $response );
		} else {
			self::$latest_error_from_square_api = '';
		}
		return false;
	}

	private static function get_standard_authenticated_body() {
		$mode = 'live';//self::get_mode_value_from_post();
		return array(
			'merchant_id'      => get_option( self::get_merchant_id_option_name( $mode ) ),
			'server_password' => get_option( self::get_server_side_token_option_name( $mode ) ),
			'client_password' => get_option( self::get_client_side_token_option_name( $mode ) ),
		);
	}

	public static function get_latest_error_from_square_api() {
		return self::$latest_error_from_square_api;
	}
}
