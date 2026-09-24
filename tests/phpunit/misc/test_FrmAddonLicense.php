<?php

/**
 * Covers the license checks every add-on runs against the store: the request
 * itself, the status read from it, activation, the weekly revocation check and
 * licenses defined in wp-config.php. No request leaves the machine, the store
 * answers through pre_http_request.
 *
 * @group addons
 */
class test_FrmAddonLicense extends FrmUnitTest {

	/**
	 * What the store answers with.
	 *
	 * @var array|WP_Error
	 */
	private $store_response;

	/**
	 * Every request sent to the store, with its url and args.
	 *
	 * @var array
	 */
	private $store_requests = array();

	public function setUp(): void {
		parent::setUp();

		$this->store_requests = array();
		$this->store_response = self::json_response( array( 'license' => 'valid' ) );
		add_filter( 'pre_http_request', array( $this, 'mock_store_request' ), 10, 3 );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_store_request' ), 10 );
		remove_filter( 'frm_installed_addons', array( $this, 'add_test_addon' ), 999 );
		unset( $_POST['license'] );
		parent::tearDown();
	}

	/**
	 * @param array|false|WP_Error $response Preemptive HTTP response.
	 * @param array                $args     Request args.
	 * @param string               $url      Request url.
	 *
	 * @return array|false|WP_Error
	 */
	public function mock_store_request( $response, $args, $url ) {
		if ( 0 !== strpos( $url, 'https://formidableforms.com?l=' ) ) {
			return $response;
		}

		$this->store_requests[] = array(
			'url'  => $url,
			'args' => $args,
		);

		return $this->store_response;
	}

	/**
	 * @var FrmAddon|null
	 */
	private $registered_addon;

	/**
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function add_test_addon( $plugins ) {
		$plugins['license_test'] = $this->registered_addon;
		return $plugins;
	}

	/**
	 * @param mixed $data The decoded body.
	 * @param int   $code The response code.
	 *
	 * @return array
	 */
	private static function json_response( $data, $code = 200 ) {
		return self::raw_response( wp_json_encode( $data ), $code );
	}

	/**
	 * @param string $body The response body.
	 * @param int    $code The response code.
	 *
	 * @return array
	 */
	private static function raw_response( $body, $code = 200 ) {
		return array(
			'headers'  => array(),
			'response' => array(
				'code'    => $code,
				'message' => get_status_header_desc( $code ),
			),
			'body'     => $body,
			'cookies'  => array(),
		);
	}

	/**
	 * @return WP_Error
	 */
	private static function connection_error() {
		return new WP_Error( 'http_request_failed', 'cURL error 28: Connection timed out' );
	}

	/**
	 * An add-on with its license saved, and no license in wp-config.php unless a
	 * test says otherwise.
	 *
	 * @param string $license Saved license key.
	 * @param array  $methods Extra methods to mock.
	 *
	 * @return FrmAddon|\PHPUnit\Framework\MockObject\MockObject
	 */
	private function get_addon( $license = 'SAVED-LICENSE-KEY', $methods = array() ) {
		$addon = $this->getMockBuilder( 'FrmAddon' )
			->disableOriginalConstructor()
			->onlyMethods( array_merge( array( 'get_defined_license', 'set_active' ), $methods ) )
			->getMock();

		$addon->plugin_name = 'License Test';
		$addon->plugin_slug = 'license_test';
		$addon->option_name = 'edd_license_test_license_';
		$addon->plugin_file = FrmAppHelper::plugin_path() . '/formidable.php';
		$addon->download_id = 12345;
		$addon->version     = '1.0';
		$addon->license     = $license;

		if ( $license ) {
			$addon->set_license( $license );
		}

		return $addon;
	}

	/**
	 * Run a call that may end the request with wp_send_json, and report whether it did.
	 *
	 * @param callable $callback
	 *
	 * @return array
	 */
	private function run_and_catch_die( $callback ) {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler' ) );
		ob_start();

		$result = array(
			'died'   => false,
			'return' => null,
		);

		try {
			$result['return'] = $callback();
		} catch ( WPDieException $e ) {
			$result['died'] = true;
		} finally {
			$result['output'] = ob_get_clean();
			remove_filter( 'wp_doing_ajax', '__return_true' );
			remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler' ) );
		}

		return $result;
	}

	/**
	 * @covers FrmAddon::send_mothership_request
	 */
	public function test_send_mothership_request_posts_license_details_to_store() {
		$this->get_addon()->send_mothership_request( 'activate_license' );

		$this->assertCount( 1, $this->store_requests, 'One request should be sent to the store.' );

		$request = $this->store_requests[0];
		$this->assertSame( 'https://formidableforms.com?l=' . urlencode( base64_encode( 'SAVED-LICENSE-KEY' ) ), $request['url'], 'The url should carry the encoded license.' );
		$this->assertSame( 'POST', $request['args']['method'], 'The request should be a POST.' );
		$this->assertSame( 25, $request['args']['timeout'], 'The request timeout should be unchanged.' );
		$this->assertSame(
			array(
				'edd_action' => 'activate_license',
				'license'    => 'SAVED-LICENSE-KEY',
				'url'        => home_url(),
				'item_id'    => 12345,
			),
			$request['args']['body'],
			'The request body should identify the action, license, site and item.'
		);
	}

	/**
	 * @covers FrmAddon::send_mothership_request
	 */
	public function test_send_mothership_request_uses_item_name_without_numeric_download_id() {
		$addon              = $this->get_addon();
		$addon->download_id = null;

		$addon->send_mothership_request( 'deactivate_license' );

		$body = $this->store_requests[0]['args']['body'];
		$this->assertSame( 'deactivate_license', $body['edd_action'], 'The action should be sent.' );
		$this->assertSame( rawurlencode( 'License Test' ), $body['item_name'], 'The plugin name should identify the item.' );
		$this->assertArrayNotHasKey( 'item_id', $body, 'No item id should be sent without a numeric download id.' );
	}

	/**
	 * @covers FrmAddon::send_mothership_request
	 *
	 * @dataProvider mothership_response_provider
	 *
	 * @param array|WP_Error $response The store response.
	 * @param array|string   $expected The decoded result, or text the error message contains.
	 */
	public function test_send_mothership_request_decodes_response( $response, $expected ) {
		$this->store_response = $response;

		$result = $this->get_addon()->send_mothership_request( 'activate_license' );

		if ( is_wp_error( $response ) ) {
			$this->assertIsString( $result, 'A connection error should return a message.' );
			$this->assertStringContainsString( 'You had an error communicating with the Formidable API.', $result, 'The message should describe the connection error.' );
			$this->assertStringContainsString( $expected, $result, 'The message should include the error from the request.' );
			return;
		}

		$this->assertSame( $expected, $result, 'The response should decode to the expected value.' );
	}

	/**
	 * @return array
	 */
	public static function mothership_response_provider() {
		return array(
			'license data'      => array(
				self::json_response(
					array(
						'success' => true,
						'license' => 'valid',
					)
				),
				array(
					'success' => true,
					'license' => 'valid',
				),
			),
			'error message'     => array( self::json_response( array( 'error' => 'revoked' ) ), 'revoked' ),
			'connection error'  => array( self::connection_error(), 'cURL error 28: Connection timed out' ),
			'http error body'   => array( self::raw_response( 'error' ), 'You had an HTTP error connecting to the Formidable API' ),
			'server error page' => array( self::raw_response( '<p>Server down</p>', 500 ), 'There was a 500 error: Internal Server Error Server down' ),
		);
	}

	/**
	 * The rate limit checks read the response code from here.
	 *
	 * @covers FrmAddon::send_mothership_request
	 */
	public function test_send_mothership_request_records_response_code() {
		$this->store_response = self::json_response( array( 'error' => 'Too many requests' ), 429 );
		$addon                = $this->get_addon();

		$addon->send_mothership_request( 'activate_license' );

		$save_status = $this->get_private_property( $addon, 'save_status' );
		$this->assertSame( 429, $save_status['response_code'], 'The response code should be recorded.' );
	}

	/**
	 * @covers FrmAddon::get_license_status
	 */
	public function test_get_license_status_without_license_reports_missing_without_request() {
		$response = $this->run_private_method( array( $this->get_addon( '' ), 'get_license_status' ) );

		$this->assertSame( 'missing', $response['status'], 'No license should report missing.' );
		$this->assertFalse( $response['error'], 'No license is not an error.' );
		$this->assertCount( 0, $this->store_requests, 'Nothing should be sent without a license.' );
	}

	/**
	 * @covers FrmAddon::get_license_status
	 *
	 * @dataProvider reported_status_provider
	 *
	 * @param array  $response The store response.
	 * @param string $status   The status the check should report.
	 */
	public function test_get_license_status_reports_status_from_store( $response, $status ) {
		$this->store_response = $response;

		$result = $this->run_private_method( array( $this->get_addon(), 'get_license_status' ) );

		$this->assertSame( $status, $result['status'], 'The status should come from the store.' );
		$this->assertFalse( $result['error'], 'A status from the store is not an error.' );
	}

	/**
	 * @return array
	 */
	public static function reported_status_provider() {
		return array(
			'valid'               => array( self::json_response( array( 'license' => 'valid' ) ), 'valid' ),
			'invalid'             => array( self::json_response( array( 'license' => 'invalid' ) ), 'invalid' ),
			'revoked'             => array( self::json_response( array( 'error' => 'revoked' ) ), 'revoked' ),
			'blocked'             => array( self::json_response( array( 'error' => 'blocked' ) ), 'blocked' ),
			'disabled'            => array( self::json_response( array( 'error' => 'disabled' ) ), 'disabled' ),
			'missing'             => array( self::json_response( array( 'error' => 'missing' ) ), 'missing' ),
			'expired'             => array( self::json_response( array( 'error' => 'expired' ) ), 'expired' ),
			'no_activations_left' => array( self::json_response( array( 'error' => 'no_activations_left' ) ), 'no_activations_left' ),
		);
	}

	/**
	 * @covers FrmAddon::get_license_status
	 * @covers FrmAddon::update_last_checked
	 *
	 * @dataProvider last_check_provider
	 *
	 * @param array|WP_Error $response The store response.
	 * @param bool           $is_valid Whether the check should be recorded as valid.
	 */
	public function test_get_license_status_records_the_check( $response, $is_valid ) {
		$this->store_response = $response;
		$addon                = $this->get_addon();

		$this->run_private_method( array( $addon, 'get_license_status' ) );

		$last_checked = $this->run_private_method( array( $addon, 'last_checked' ) );
		$this->assertSame( $is_valid, $last_checked['is_valid'], 'The check result should be recorded.' );
		$this->assertTrue( $this->run_private_method( array( $addon, 'checked_recently' ), array( '2 minutes' ) ), 'The check time should be recorded.' );
		$this->assertEmpty( $this->run_private_method( array( $addon, 'is_running' ) ), 'The request lock should be released.' );
	}

	/**
	 * @return array
	 */
	public static function last_check_provider() {
		return array(
			'valid'            => array( self::json_response( array( 'license' => 'valid' ) ), true ),
			'invalid'          => array( self::json_response( array( 'license' => 'invalid' ) ), false ),
			'revoked'          => array( self::json_response( array( 'error' => 'revoked' ) ), false ),
			'connection error' => array( self::connection_error(), false ),
			'server error'     => array( self::raw_response( 'Server down', 500 ), false ),
		);
	}

	/**
	 * @covers FrmAddon::get_license_status
	 */
	public function test_get_license_status_valid_saves_status() {
		$addon = $this->get_addon();

		$this->run_private_method( array( $addon, 'get_license_status' ) );

		$save_status = $this->get_private_property( $addon, 'save_status' );
		$this->assertSame( 'valid', $save_status['status'], 'The valid status should be saved.' );
		$this->assertSame( 200, $save_status['response_code'], 'The response code should be saved.' );
	}

	/**
	 * @covers FrmAddon::get_license_status
	 *
	 * @dataProvider unreachable_store_provider
	 *
	 * @param array|WP_Error $response The store response.
	 * @param string         $message  Text the status should contain.
	 */
	public function test_get_license_status_unreachable_store_reports_the_error( $response, $message ) {
		$this->store_response = $response;

		$result = $this->run_private_method( array( $this->get_addon(), 'get_license_status' ) );

		$this->assertStringContainsString( $message, $result['status'], 'The status should describe the failed request.' );
		$this->assertNotSame( 'valid', $result['status'], 'A failed request is never valid.' );
	}

	/**
	 * @return array
	 */
	public static function unreachable_store_provider() {
		return array(
			'connection error' => array( self::connection_error(), 'You had an error communicating with the Formidable API.' ),
			'server error'     => array( self::raw_response( 'Server down', 500 ), 'There was a 500 error' ),
			'http error body'  => array( self::raw_response( 'error' ), 'You had an HTTP error connecting to the Formidable API' ),
		);
	}

	/**
	 * @covers FrmAddon::is_license_revoked
	 *
	 * @dataProvider revoked_status_provider
	 *
	 * @param string $status The status the store reports.
	 */
	public function test_is_license_revoked_clears_license_the_store_rejects( $status ) {
		$this->store_response = self::json_response( array( 'error' => $status ) );
		$addon                = $this->get_addon();
		update_option( $addon->option_name . 'active', 'valid' );

		$this->run_private_method( array( $addon, 'is_license_revoked' ) );

		$this->assertCount( 1, $this->store_requests, 'The license should be checked.' );
		$this->assertFalse( get_option( $addon->option_name . 'key' ), 'The saved license should be removed.' );
		$this->assertFalse( get_option( $addon->option_name . 'active' ), 'The active flag should be removed.' );
	}

	/**
	 * @return array
	 */
	public static function revoked_status_provider() {
		return array(
			'revoked'  => array( 'revoked' ),
			'blocked'  => array( 'blocked' ),
			'disabled' => array( 'disabled' ),
			'missing'  => array( 'missing' ),
		);
	}

	/**
	 * @covers FrmAddon::is_license_revoked
	 *
	 * @dataProvider kept_license_provider
	 *
	 * @param array|WP_Error $response The store response.
	 */
	public function test_is_license_revoked_keeps_license( $response ) {
		$this->store_response = $response;
		$addon                = $this->get_addon();
		update_option( $addon->option_name . 'active', 'valid' );

		$this->run_private_method( array( $addon, 'is_license_revoked' ) );

		$this->assertCount( 1, $this->store_requests, 'The license should be checked.' );
		$this->assertSame( 'SAVED-LICENSE-KEY', get_option( $addon->option_name . 'key' ), 'The saved license should be kept.' );
		$this->assertSame( 'valid', get_option( $addon->option_name . 'active' ), 'The active flag should be kept.' );
		$this->assertSame( 'SAVED-LICENSE-KEY', $addon->license, 'The add-on should keep its license.' );
	}

	/**
	 * @return array
	 */
	public static function kept_license_provider() {
		return array(
			'valid'                  => array( self::json_response( array( 'license' => 'valid' ) ) ),
			'invalid'                => array( self::json_response( array( 'license' => 'invalid' ) ) ),
			'no activations left'    => array( self::json_response( array( 'error' => 'no_activations_left' ) ) ),
			'rate limited'           => array( self::json_response( array( 'error' => 'revoked' ), 429 ) ),
			'rate limited html page' => array( self::raw_response( 'Too Many Requests', 429 ) ),
			'connection error'       => array( self::connection_error() ),
			'server error'           => array( self::raw_response( 'Server down', 500 ) ),
			'http error body'        => array( self::raw_response( 'error' ) ),
		);
	}

	/**
	 * A check that fails still counts, so a site does not ask the store again on
	 * every page load while the store is unreachable.
	 *
	 * @covers FrmAddon::is_license_revoked
	 * @covers FrmAddon::checked_recently
	 *
	 * @dataProvider kept_license_provider
	 *
	 * @param array|WP_Error $response The store response.
	 */
	public function test_is_license_revoked_checks_at_most_once_per_window( $response ) {
		$this->store_response = $response;
		$addon                = $this->get_addon();

		$this->run_private_method( array( $addon, 'is_license_revoked' ) );
		$this->run_private_method( array( $addon, 'is_license_revoked' ) );

		$this->assertCount( 1, $this->store_requests, 'A second check right after the first should not reach the store.' );
	}

	/**
	 * @covers FrmAddon::is_license_revoked
	 */
	public function test_is_license_revoked_skips_defined_license() {
		$this->store_response = self::json_response( array( 'error' => 'revoked' ) );
		$addon                = $this->get_addon();
		$addon->method( 'get_defined_license' )->willReturn( 'SAVED-LICENSE-KEY' );

		$this->run_private_method( array( $addon, 'is_license_revoked' ) );

		$this->assertCount( 0, $this->store_requests, 'A license from wp-config.php should not be checked.' );
		$this->assertSame( 'SAVED-LICENSE-KEY', get_option( $addon->option_name . 'key' ), 'The license should be kept.' );
	}

	/**
	 * @covers FrmAddon::is_license_revoked
	 */
	public function test_is_license_revoked_skips_when_checked_this_week() {
		$addon = $this->get_addon();
		$this->run_private_method( array( $addon, 'update_last_checked' ), array( true ) );

		$this->run_private_method( array( $addon, 'is_license_revoked' ) );

		$this->assertCount( 0, $this->store_requests, 'A license checked this week should not be checked again.' );
	}

	/**
	 * @covers FrmAddon::is_license_revoked
	 */
	public function test_is_license_revoked_skips_while_another_check_runs() {
		$addon = $this->get_addon();
		$this->run_private_method( array( $addon, 'set_running' ) );

		$this->run_private_method( array( $addon, 'is_license_revoked' ) );

		$this->assertCount( 0, $this->store_requests, 'Only one check should run at a time.' );
	}

	/**
	 * @covers FrmAddon::is_license_revoked
	 */
	public function test_is_license_revoked_skips_license_being_saved() {
		$_POST['license'] = 'NEW-LICENSE-KEY';

		$this->run_private_method( array( $this->get_addon(), 'is_license_revoked' ) );

		$this->assertCount( 0, $this->store_requests, 'A license being saved should not be checked for revocation.' );
	}

	/**
	 * @covers FrmAddon::is_license_revoked
	 */
	public function test_is_license_revoked_skips_without_license() {
		$this->run_private_method( array( $this->get_addon( '' ), 'is_license_revoked' ) );
		$this->assertCount( 0, $this->store_requests, 'Nothing should be checked without a license.' );
	}

	/**
	 * @covers FrmAddon::activate_license_for_plugin
	 * @covers FrmAddon::activate_license
	 * @covers FrmAddon::maybe_set_active
	 */
	public function test_activate_license_valid_saves_license() {
		$addon = $this->get_addon( '' );
		$addon->expects( $this->once() )->method( 'set_active' )->with( 'valid' );
		$this->registered_addon = $addon;
		add_filter( 'frm_installed_addons', array( $this, 'add_test_addon' ), 999 );

		$response = FrmAddon::activate_license_for_plugin( 'NEW-LICENSE-KEY', 'license_test' );

		$this->assertTrue( $response['success'], 'A valid license should activate.' );
		$this->assertSame( 'Your license has been activated. Enjoy!', $response['message'], 'The success message should be shown.' );
		$this->assertSame( 'valid', $response['status'], 'The status should be returned.' );
		$this->assertSame( 'NEW-LICENSE-KEY', get_option( $addon->option_name . 'key' ), 'The license should be saved.' );
		$this->assertSame( 'valid', get_option( $addon->option_name . 'active' ), 'The license should be marked active.' );
		$this->assertSame( 'NEW-LICENSE-KEY', $addon->license, 'The add-on should use the new license.' );
		$this->assertSame( 'NEW-LICENSE-KEY', $this->store_requests[0]['args']['body']['license'], 'The new license should be sent to the store.' );

		$last_checked = $this->run_private_method( array( $addon, 'last_checked' ) );
		$this->assertTrue( $last_checked['is_valid'], 'The check should be recorded as valid.' );
	}

	/**
	 * Pro adds the license type to the response through this method.
	 *
	 * @covers FrmAddon::activate_license
	 * @covers FrmAddon::add_activation_response_data
	 */
	public function test_activate_license_valid_passes_response_through_activation_data() {
		$addon = $this->get_addon( '', array( 'add_activation_response_data' ) );
		$addon->expects( $this->once() )
			->method( 'add_activation_response_data' )
			->willReturnCallback(
				function ( $response ) {
					$response['license_type'] = 'Elite';
					return $response;
				}
			);

		$response = $this->run_private_method( array( $addon, 'activate_license' ), array( 'NEW-LICENSE-KEY' ) );

		$this->assertTrue( $response['success'], 'A valid license should activate.' );
		$this->assertSame( 'Elite', $response['license_type'], 'The extra activation details should be returned.' );
	}

	/**
	 * @covers FrmAddon::activate_license
	 * @covers FrmAddon::maybe_set_active
	 *
	 * @dataProvider rejected_license_provider
	 *
	 * @param array  $response The store response.
	 * @param string $message  The message shown for the rejection.
	 */
	public function test_activate_license_rejected_discards_license( $response, $message ) {
		$this->store_response = $response;
		$addon                = $this->get_addon( 'OLD-LICENSE-KEY', array( 'add_activation_response_data' ) );
		update_option( $addon->option_name . 'active', 'valid' );
		$addon->expects( $this->never() )->method( 'set_active' );
		$addon->expects( $this->never() )->method( 'add_activation_response_data' );

		$result = $this->run_private_method( array( $addon, 'activate_license' ), array( 'REJECTED-LICENSE-KEY' ) );

		$this->assertFalse( $result['success'], 'A rejected license should not activate.' );
		$this->assertSame( $message, $result['message'], 'The rejection message should be shown.' );
		$this->assertFalse( get_option( $addon->option_name . 'key' ), 'The rejected license should not be saved.' );
		$this->assertFalse( get_option( $addon->option_name . 'active' ), 'The active flag should be removed.' );
		$this->assertSame( '', $addon->license, 'The add-on should drop the rejected license.' );
	}

	/**
	 * @return array
	 */
	public static function rejected_license_provider() {
		return array(
			'invalid'             => array( self::json_response( array( 'license' => 'invalid' ) ), 'That license key is invalid' ),
			'revoked'             => array( self::json_response( array( 'error' => 'revoked' ) ), 'That license has been refunded' ),
			'expired'             => array( self::json_response( array( 'error' => 'expired' ) ), 'That license is expired' ),
			'missing'             => array( self::json_response( array( 'error' => 'missing' ) ), 'That license key is invalid' ),
			'no_activations_left' => array( self::json_response( array( 'error' => 'no_activations_left' ) ), 'That license has been used on too many sites' ),
			'invalid_item_id'     => array( self::json_response( array( 'error' => 'invalid_item_id' ) ), 'Oops! That is the wrong license key for this plugin.' ),
			'custom message'      => array(
				self::json_response( array( 'error' => 'License <a href="https://formidableforms.com/">paused</a>' ) ),
				'License <a href="https://formidableforms.com/">paused</a>',
			),
		);
	}

	/**
	 * @covers FrmAddon::activate_license
	 */
	public function test_activate_license_sanitizes_store_message() {
		$this->store_response = self::json_response( array( 'error' => 'Renew <a href="https://formidableforms.com/">here</a><script>alert(1)</script>' ) );

		$result = $this->run_private_method( array( $this->get_addon(), 'activate_license' ), array( 'REJECTED-LICENSE-KEY' ) );

		$this->assertStringContainsString( '<a href="https://formidableforms.com/">here</a>', $result['message'], 'Links in the message should be kept.' );
		$this->assertStringNotContainsString( '<script', $result['message'], 'Scripts in the message should be removed.' );
	}

	/**
	 * @covers FrmAddon::activate_license
	 *
	 * @dataProvider unreachable_store_provider
	 *
	 * @param array|WP_Error $response The store response.
	 * @param string         $message  Text the message should contain.
	 */
	public function test_activate_license_unreachable_store_does_not_activate( $response, $message ) {
		$this->store_response = $response;
		$addon                = $this->get_addon( '', array( 'add_activation_response_data' ) );
		$addon->expects( $this->never() )->method( 'set_active' );
		$addon->expects( $this->never() )->method( 'add_activation_response_data' );

		$result = $this->run_private_method( array( $addon, 'activate_license' ), array( 'NEW-LICENSE-KEY' ) );

		$this->assertFalse( $result['success'], 'A failed request should not activate the license.' );
		$this->assertStringContainsString( $message, $result['message'], 'The message should describe the failed request.' );
		$this->assertEmpty( get_option( $addon->option_name . 'active' ), 'The license should not be marked active.' );
	}

	/**
	 * Retrying the same key within two minutes is refused before anything is sent.
	 *
	 * @covers FrmAddon::activate_license
	 * @covers FrmAddon::die_if_not_allowed
	 */
	public function test_activate_license_retry_within_two_minutes_is_refused() {
		$this->store_response = self::json_response( array( 'license' => 'invalid' ) );
		$addon                = $this->get_addon( '' );

		$this->run_private_method( array( $addon, 'activate_license' ), array( 'REJECTED-LICENSE-KEY' ) );

		$retry = $this->run_and_catch_die(
			function () use ( $addon ) {
				return $this->run_private_method( array( $addon, 'activate_license' ), array( 'REJECTED-LICENSE-KEY' ) );
			}
		);

		$this->assertTrue( $retry['died'], 'The retry should be refused.' );
		$this->assertStringContainsString( 'Please wait two minutes before trying again.', $retry['output'], 'The wait message should be sent.' );
		$this->assertCount( 1, $this->store_requests, 'The retry should not reach the store.' );
	}

	/**
	 * @covers FrmAddon::maybe_set_active
	 */
	public function test_maybe_set_active_valid_activates_license() {
		$addon = $this->get_addon();
		$addon->expects( $this->once() )->method( 'set_active' )->with( 'valid' );

		$this->run_private_method( array( $addon, 'maybe_set_active' ), array( 'valid' ) );

		$this->assertSame( 'valid', get_option( $addon->option_name . 'active' ), 'The license should be marked active.' );
		$this->assertSame( 'SAVED-LICENSE-KEY', get_option( $addon->option_name . 'key' ), 'The license should be kept.' );
		$this->assertSame( 'SAVED-LICENSE-KEY', $addon->license, 'The add-on should keep its license.' );
	}

	/**
	 * The last check is kept, so a rejected license is not sent again right away.
	 *
	 * @covers FrmAddon::maybe_set_active
	 */
	public function test_maybe_set_active_invalid_discards_license_and_keeps_last_check() {
		$addon = $this->get_addon();
		update_option( $addon->option_name . 'active', 'valid' );
		$this->run_private_method( array( $addon, 'update_last_checked' ), array( false ) );
		$last_check_key = $this->run_private_method( array( $addon, 'transient_key' ) );
		$addon->expects( $this->never() )->method( 'set_active' );

		$this->run_private_method( array( $addon, 'maybe_set_active' ), array( false ) );

		$this->assertFalse( get_option( $addon->option_name . 'key' ), 'The license should be removed.' );
		$this->assertFalse( get_option( $addon->option_name . 'active' ), 'The active flag should be removed.' );
		$this->assertSame( '', $addon->license, 'The add-on should drop its license.' );
		$this->assertNotEmpty( is_multisite() ? get_site_option( $last_check_key ) : get_option( $last_check_key ), 'The last check should be kept.' );
	}

	/**
	 * @covers FrmAddon::activate_defined_license
	 */
	public function test_activate_defined_license_without_defined_license_sends_nothing() {
		$addon = $this->get_addon( '' );
		$addon->method( 'get_defined_license' )->willReturn( false );

		$this->assertFalse( $addon->activate_defined_license(), 'Nothing should be returned without a defined license.' );
		$this->assertCount( 0, $this->store_requests, 'Nothing should be sent without a defined license.' );
	}

	/**
	 * @covers FrmAddon::activate_defined_license
	 */
	public function test_activate_defined_license_already_active_sends_nothing() {
		$addon = $this->get_addon( '' );
		$addon->method( 'get_defined_license' )->willReturn( 'DEFINED-LICENSE-KEY' );
		update_option( $addon->option_name . 'active', 'valid' );

		$this->assertSame( 'DEFINED-LICENSE-KEY', $addon->activate_defined_license(), 'The defined license should be used.' );
		$this->assertCount( 0, $this->store_requests, 'An active license should not be activated again.' );
	}

	/**
	 * @covers FrmAddon::activate_defined_license
	 */
	public function test_activate_defined_license_valid_activates_license() {
		$addon = $this->get_addon( '' );
		$addon->method( 'get_defined_license' )->willReturn( 'DEFINED-LICENSE-KEY' );
		$addon->expects( $this->once() )->method( 'set_active' )->with( 'valid' );

		$this->assertSame( 'DEFINED-LICENSE-KEY', $addon->activate_defined_license(), 'The defined license should be used.' );
		$this->assertSame( 'DEFINED-LICENSE-KEY', $this->store_requests[0]['args']['body']['license'], 'The defined license should be sent.' );
		$this->assertSame( 'DEFINED-LICENSE-KEY', get_option( $addon->option_name . 'key' ), 'The defined license should be saved.' );
		$this->assertSame( 'valid', get_option( $addon->option_name . 'active' ), 'The license should be marked active.' );
	}

	/**
	 * @covers FrmAddon::activate_defined_license
	 *
	 * @dataProvider rejected_license_provider
	 *
	 * @param array $response The store response.
	 */
	public function test_activate_defined_license_rejected_is_not_used( $response ) {
		$this->store_response = $response;
		$addon                = $this->get_addon( '' );
		$addon->method( 'get_defined_license' )->willReturn( 'DEFINED-LICENSE-KEY' );

		$this->assertSame( '', $addon->activate_defined_license(), 'A rejected defined license should not be used.' );
		$this->assertFalse( get_option( $addon->option_name . 'key' ), 'A rejected defined license should not be saved.' );
	}

	/**
	 * Every add-on runs activate_defined_license while it loads, on every page, with
	 * no license set yet. Whatever the first attempt returned, the next page loads
	 * must neither ask the store again for a day nor end the request.
	 *
	 * @covers FrmAddon::activate_defined_license
	 * @covers FrmAddon::activate_license
	 * @covers FrmAddon::checked_recently
	 *
	 * @dataProvider defined_license_attempt_provider
	 *
	 * @param array|WP_Error $response The store response.
	 */
	public function test_activate_defined_license_is_attempted_once_per_day( $response ) {
		$this->store_response = $response;

		// The first page load, as the add-on constructor runs it.
		$first = $this->get_addon( null );
		$first->method( 'get_defined_license' )->willReturn( 'DEFINED-LICENSE-KEY' );
		$first->activate_defined_license();

		$this->assertCount( 1, $this->store_requests, 'The first page load should try to activate the license.' );

		// The next page load builds a fresh add-on.
		$next = $this->get_addon( null );
		$next->method( 'get_defined_license' )->willReturn( 'DEFINED-LICENSE-KEY' );
		$result = $this->run_and_catch_die( array( $next, 'activate_defined_license' ) );

		$this->assertFalse( $result['died'], 'Loading the add-on should never end the request. Output: ' . $result['output'] );
		$this->assertCount( 1, $this->store_requests, 'The next page load should not ask the store again.' );
	}

	/**
	 * @return array
	 */
	public static function defined_license_attempt_provider() {
		return array(
			'valid'            => array( self::json_response( array( 'license' => 'valid' ) ) ),
			'invalid'          => array( self::json_response( array( 'license' => 'invalid' ) ) ),
			'revoked'          => array( self::json_response( array( 'error' => 'revoked' ) ) ),
			'rate limited'     => array( self::raw_response( 'Too Many Requests', 429 ) ),
			'connection error' => array( self::connection_error() ),
			'server error'     => array( self::raw_response( 'Server down', 500 ) ),
		);
	}
}
