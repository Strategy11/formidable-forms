<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteConnectHelper {

	/**
	 * Track the latest error when calling the Square API.
	 *
	 * @since 6.22
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
			<div>
				<em>
					<?php esc_html_e( 'Your site is not using SSL. Before using Square to collect payments, you will need to install an SSL certificate on your site.', 'formidable' ); // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong ?>
				</em>
			</div>
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
						<a id="frm_disconnect_square_<?php echo esc_attr( $mode ); ?>" class="button-secondary frm-button-secondary" href="#">
							<?php esc_html_e( 'Disconnect', 'formidable' ); ?>
						</a>
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
		$script_url     = FrmSquareLiteAppHelper::plugin_url() . '/js/settings.js';
		$dependencies   = array( 'formidable_dom' );
		$plugin_version = FrmAppHelper::plugin_version();
		wp_register_script( 'formidable_square_settings', $script_url, $dependencies, $plugin_version, true );
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
		delete_option( 'frm_square_lite_last_verify_attempt' );
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
		return 'https://api.strategy11.com/';
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
	 * @return string
	 */
	private static function get_location_id_option_name( $mode = 'auto' ) {
		return self::get_square_connect_option_name( 'merchant_location_id', $mode );
	}

	/**
	 * @return string
	 */
	private static function get_merchant_currency_option_name( $mode = 'auto' ) {
		return self::get_square_connect_option_name( 'merchant_currency', $mode );
	}

	/**
	 * @param string $key 'merchant_id', 'client_password', 'server_password'.
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
		wp_safe_redirect( self::get_url_for_square_settings( $connected ) );
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
		$mode = 'test' === FrmAppHelper::simple_get( 'mode' ) ? 'test' : 'live';

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

			$currency    = self::get_merchant_currency( true );
			$location_id = self::get_location_id( true );

			if ( $currency ) {
				update_option( self::get_merchant_currency_option_name( $mode ), $currency, 'no' );
			}

			if ( $location_id ) {
				update_option( self::get_location_id_option_name( $mode ), $location_id, 'no' );
			}

			FrmTransLiteAppController::install();

			return true;
		}

		return false;
	}

	public static function create_payment( $amount, $currency, $square_token, $verification_token, $description ) {
		return self::post_with_authenticated_body(
			'create_payment',
			array(
				'amount'             => $amount,
				'currency'           => $currency,
				'square_token'       => $square_token,
				'verification_token' => $verification_token,
				'description'        => $description,
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
		$mode = self::get_mode_value_from_post();
		return array(
			'merchant_id'     => get_option( self::get_merchant_id_option_name( $mode ) ),
			'server_password' => get_option( self::get_server_side_token_option_name( $mode ) ),
			'client_password' => get_option( self::get_client_side_token_option_name( $mode ) ),
		);
	}

	/**
	 * Check $_POST for live or test mode value as it can be updated in real time from Stripe Settings and can be configured before the update is saved.
	 *
	 * @return string 'test' or 'live'
	 */
	private static function get_mode_value_from_post() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST ) || ! array_key_exists( 'testMode', $_POST ) ) {
			return FrmSquareLiteAppHelper::active_mode();
		}
		$test_mode = FrmAppHelper::get_param( 'testMode', '', 'post', 'absint' );
		return $test_mode ? 'test' : 'live';
	}

	public static function get_latest_error_from_square_api() {
		return self::$latest_error_from_square_api;
	}

	public static function refund_payment( $receipt_id ) {
		return self::post_with_authenticated_body( 'refund_payment', array( 'receipt_id' => $receipt_id ) );
	}

	public static function create_subscription( $info ) {
		return self::post_with_authenticated_body( 'create_subscription', compact( 'info' ) );
	}

	/**
	 * @param bool $force
	 * @return false|string
	 */
	public static function get_location_id( $force = false ) {
		if ( ! $force ) {
			$location_id = get_option( self::get_location_id_option_name() );
			if ( $location_id ) {
				return $location_id;
			}
		}

		$response = self::post_with_authenticated_body( 'get_location_id' );
		if ( is_object( $response ) ) {
			update_option( self::get_location_id_option_name(), $response->id, 'no' );
			return $response->id;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public static function get_unprocessed_event_ids() {
		$data = self::post_with_authenticated_body( 'get_unprocessed_event_ids' );
		if ( false === $data || empty( $data->event_ids ) ) {
			return array();
		}
		return $data->event_ids;
	}

	/**
	 * @param string $event_id
	 * @return false|object
	 */
	public static function get_event( $event_id ) {
		$event = wp_cache_get( $event_id, 'frm_square' );
		if ( is_object( $event ) ) {
			return $event;
		}

		$event = self::post_with_authenticated_body( 'get_event', compact( 'event_id' ) );

		if ( false === $event || empty( $event->event ) ) {
			return false;
		}

		wp_cache_set( $event_id, $event->event, 'frm_square' );

		return $event->event;
	}

	/**
	 * @param string $event_id
	 * @return mixed
	 */
	public static function process_event( $event_id ) {
		return self::post_with_authenticated_body( 'process_event', compact( 'event_id' ) );
	}

	public static function get_payment( $payment_id ) {
		return self::post_with_authenticated_body( 'get_payment', compact( 'payment_id' ) );
	}

	public static function get_subscription_id_for_payment( $payment_id ) {
		return self::post_with_authenticated_body( 'get_subscription_id_for_payment', compact( 'payment_id' ) );
	}

	public static function cancel_subscription( $subscription_id ) {
		return self::post_with_authenticated_body( 'cancel_subscription', compact( 'subscription_id' ) );
	}

	/**
	 * @return void
	 */
	public static function handle_disconnect() {
		self::disconnect();
		self::reset_square_api_integration();
		wp_send_json_success();
	}

	/**
	 * @return false|object
	 */
	private static function disconnect() {
		$additional_body = array(
			'frm_square_api_mode' => self::get_mode_value_from_post(),
		);
		return self::post_with_authenticated_body( 'disconnect', $additional_body );
	}

	/**
	 * Delete every Square API option, calling when disconnecting.
	 *
	 * @return void
	 */
	public static function reset_square_api_integration() {
		$mode = self::get_mode_value_from_post();
		delete_option( self::get_merchant_id_option_name( $mode ) );
		delete_option( self::get_server_side_token_option_name( $mode ) );
		delete_option( self::get_client_side_token_option_name( $mode ) );
		delete_option( self::get_merchant_currency_option_name( $mode ) );
		delete_option( self::get_location_id_option_name( $mode ) );
	}

	/**
	 * @param bool $force
	 * @return false|string
	 */
	public static function get_merchant_currency( $force = false ) {
		if ( ! $force ) {
			$currency = get_option( self::get_merchant_currency_option_name() );
			if ( $currency ) {
				return $currency;
			}
		}

		$response = self::post_with_authenticated_body( 'get_merchant_currency' );
		if ( is_object( $response ) && ! empty( $response->currency ) ) {
			update_option( self::get_merchant_currency_option_name(), $response->currency, 'no' );
			return $response->currency;
		}

		return false;
	}

	/**
	 * @since 6.22
	 *
	 * @return bool
	 */
	public static function at_least_one_mode_is_setup() {
		return self::get_merchant_id( 'test' ) || self::get_merchant_id( 'live' );
	}

	/**
	 * Verify a site identifier is a match.
	 */
	public static function verify() {
		$option_name  = 'frm_square_lite_last_verify_attempt';
		$last_request = get_option( $option_name );

		if ( $last_request && $last_request > strtotime( '-1 day' ) ) {
			wp_send_json_error( 'Too many requests' );
		}

		$site_identifier = FrmAppHelper::get_post_param( 'site_identifier' );
		$usage           = new FrmUsage();
		$uuid            = $usage->uuid();

		update_option( $option_name, time() );

		if ( $site_identifier === $uuid ) {
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	public static function get_subscription( $subscription_id ) {
		$response = self::post_with_authenticated_body( 'get_subscription', array( 'subscription_id' => $subscription_id ) );
		if ( is_object( $response ) && is_object( $response->subscription ) ) {
			return $response->subscription;
		}
		return false;
	}
}
