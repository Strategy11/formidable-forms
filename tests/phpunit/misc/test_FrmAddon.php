<?php

/**
 * @group addons
 */
class test_FrmAddon extends FrmUnitTest {

	private $addon;
	private $license_http_response;

	public function setUp(): void {
		parent::setUp();

		$this->addon = $this->getMockBuilder( 'FrmTestAddon' )->setMethods()
							->getMock();
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_license_http_request' ) );
		parent::tearDown();
	}

	/**
	 * @param array|false|WP_Error $response Preemptive HTTP response.
	 *
	 * @return array|WP_Error
	 */
	public function mock_license_http_request( $response ) {
		return $this->license_http_response;
	}

	/**
	 * @covers FrmAddon::__construct
	 */
	public function test_construct() {
		$this->assertSame( 'signature', $this->addon->plugin_slug );
		$this->assertSame( 'edd_signature_license_', $this->addon->option_name );

		// TODO: Test this line: $this->license = $this->get_license();
	}

	/**
	 * @covers FrmAddon::insert_installed_addon
	 */
	public function test_insert_installed_addon() {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		$this->assertArrayHasKey( 'signature', $plugins );
	}

	/**
	 * @covers FrmAddon::get_defined_license
	 */
	public function test_get_defined_license() {
		$license = $this->addon->get_defined_license();
		$this->assertFalse( $license, 'Not defined, but license returned: ' . $license );

		$license_key = 'testlicense-232';
		define( 'FRM_SIGNATURE_LICENSE', $license_key );
		$license = $this->addon->get_defined_license();
		$this->assertSame( $license_key, $license );
	}

	/**
	 * @covers FrmAddon::checked_recently
	 * @covers FrmAddon::last_checked
	 * @covers FrmAddon::update_last_checked
	 */
	public function test_checked_recently() {
		$times = array(
			array(
				'time'     => time(),
				'expected' => true,
			),
			array(
				'time'     => false,
				'expected' => false,
			),
			array(
				'time'     => strtotime( '-2 days' ),
				'expected' => false,
			),
			array(
				'time'     => strtotime( '-2 hours' ),
				'expected' => true,
			),
		);

		$this->run_private_method( array( $this->addon, 'update_last_checked' ), array( true ) );
		$checked_recently = $this->run_private_method( array( $this->addon, 'checked_recently' ), array( '1 hour' ) );
		$this->assertTrue( $checked_recently, 'Time was set via update_last_checked' );
		$option_name = $this->run_private_method( array( $this->addon, 'transient_key' ) );

		foreach ( $times as $time ) {
			$save = array(
				'time' => gmdate( 'Y-m-d H:i:s', $time['time'] ),
			);

			if ( is_multisite() ) {
				update_site_option( $option_name, $save );
			} else {
				update_option( $option_name, $save );
			}

			$checked_recently = $this->run_private_method( array( $this->addon, 'checked_recently' ), array( '1 day' ) );
			$this->assertSame( $time['expected'], $checked_recently, $time['time'] . 'not properly checking' );
		}
	}

	/**
	 * Builds an add-on with a saved license whose API request comes back with the
	 * given payload, so the license checks can run without a request leaving the
	 * machine.
	 *
	 * @param mixed $payload What the API request should come back with.
	 *
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	private function get_licensed_addon( $payload ) {
		$addon = $this->getMockBuilder( 'FrmTestAddon' )
						->disableOriginalConstructor()
						->setMethods( array( 'clear_license', 'get_defined_license' ) )
						->getMock();

		$this->license_http_response = is_wp_error( $payload ) ? $payload : array(
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'body'     => wp_json_encode( is_string( $payload ) ? array( 'error' => $payload ) : $payload ),
		);
		add_filter( 'pre_http_request', array( $this, 'mock_license_http_request' ) );

		$addon->plugin_file = FrmAppHelper::plugin_path() . '/formidable.php';
		$addon->plugin_slug = 'test_license';
		$addon->option_name = 'edd_test_license_license_';
		$addon->license     = 'TEST-LICENSE-KEY';

		// Clear the weekly throttle so the check under test actually runs.
		$key = $this->run_private_method( array( $addon, 'transient_key' ) );
		delete_option( $key );
		delete_site_option( $key );

		return $addon;
	}

	/**
	 * A check that never got an answer about the license must report itself as
	 * inconclusive, so nothing downstream treats it as a verdict.
	 *
	 * @covers FrmAddon::get_license_status
	 *
	 * @dataProvider license_status_provider
	 *
	 * @param mixed  $payload         What the API request comes back with.
	 * @param bool   $is_inconclusive Whether the check should report no verdict.
	 * @param string $status          The status the check should report.
	 *
	 * @return void
	 */
	public function test_get_license_status_only_reports_a_verdict_from_the_api( $payload, $is_inconclusive, $status ) {
		$addon    = $this->get_licensed_addon( $payload );
		$response = $this->run_private_method( array( $addon, 'get_license_status' ) );

		$this->assertSame( $is_inconclusive, ! empty( $response['inconclusive'] ) );

		if ( is_wp_error( $payload ) ) {
			$this->assertStringContainsString( $status, $response['status'], 'The connection error should be shown.' );
		} else {
			$this->assertSame( $status, $response['status'], 'The license status should match the response.' );
		}
	}

	/**
	 * @return \Iterator<string, array<int, mixed>>
	 */
	public function license_status_provider(): \Iterator {
		return new \ArrayIterator(
			array(
				'valid'               => array( array( 'license' => 'valid' ), false, 'valid' ),
				'invalid'             => array( array( 'license' => 'invalid' ), false, 'invalid' ),
				'revoked'             => array( 'revoked', false, 'revoked' ),
				'disabled'            => array( 'disabled', false, 'disabled' ),
				'missing'             => array( 'missing', false, 'missing' ),
				'bundle rejection'    => array( 'bundle_activation_not_allowed', false, 'bundle_activation_not_allowed' ),
				'key mismatch'        => array( 'key_mismatch', false, 'key_mismatch' ),
				'expired'             => array( array( 'license' => 'expired' ), false, 'expired' ),
				'no_activations_left' => array( array( 'license' => 'no_activations_left' ), false, 'no_activations_left' ),
				'error payload'       => array( array( 'code' => 500 ), true, '' ),
				'empty payload'       => array( array(), true, '' ),
				'connection error'    => array( new WP_Error( 'http_request_failed', 'Connection failed' ), true, 'You had an error communicating with the Formidable API.' ),
			)
		);
	}

	/**
	 * A license that activated before stays in place until the API says otherwise.
	 * Losing the connection is not a revocation.
	 *
	 * @covers FrmAddon::is_license_revoked
	 *
	 * @dataProvider revoked_license_provider
	 *
	 * @param mixed $payload      What the API request comes back with.
	 * @param bool  $should_clear Whether the saved license should be dropped.
	 *
	 * @return void
	 */
	public function test_is_license_revoked_only_clears_on_a_reported_revocation( $payload, $should_clear ) {
		$addon = $this->get_licensed_addon( $payload );

		$addon->expects( $should_clear ? $this->once() : $this->never() )->method( 'clear_license' );

		$this->run_private_method( array( $addon, 'is_license_revoked' ) );
	}

	/**
	 * @return \Iterator<string, array<int, mixed>>
	 */
	public function revoked_license_provider(): \Iterator {
		return new \ArrayIterator(
			array(
				'revoked'          => array( 'revoked', true ),
				'blocked'          => array( 'blocked', true ),
				'disabled'         => array( 'disabled', true ),
				'missing'          => array( 'missing', true ),
				'valid'            => array( array( 'license' => 'valid' ), false ),
				'invalid'          => array( array( 'license' => 'invalid' ), false ),
				'expired'          => array( array( 'license' => 'expired' ), false ),
				'error payload'    => array( array( 'code' => 500 ), false ),
				'empty payload'    => array( array(), false ),
				'connection error' => array( new WP_Error( 'http_request_failed', 'Connection failed' ), false ),
			)
		);
	}

	/**
	 * @covers FrmAddon::activate_license
	 * @covers FrmAddon::maybe_set_active
	 *
	 * @return void
	 */
	public function test_failed_activation_keeps_existing_license() {
		$addon = $this->get_licensed_addon( new WP_Error( 'http_request_failed', 'Connection failed' ) );
		update_option( $addon->option_name . 'key', 'TEST-LICENSE-KEY' );
		update_option( $addon->option_name . 'active', 'valid' );

		$response = $this->run_private_method( array( $addon, 'activate_license' ), array( 'NEW-LICENSE-KEY' ) );

		$this->assertFalse( $response['success'], 'An unreachable API should not activate the new key.' );
		$this->assertTrue( $response['inconclusive'], 'A connection failure should be inconclusive.' );
		$this->assertStringContainsString( 'error communicating', $response['message'], 'The message should describe the connection failure.' );
		$this->assertSame( 'TEST-LICENSE-KEY', get_option( $addon->option_name . 'key' ), 'The saved key should survive.' );
		$this->assertSame( 'valid', get_option( $addon->option_name . 'active' ), 'The active flag should survive.' );
		$this->assertSame( 'TEST-LICENSE-KEY', $addon->license, 'The add-on should retain its previous key.' );
	}

	/**
	 * @covers FrmAddon::activate_defined_license
	 *
	 * @return void
	 */
	public function test_failed_defined_license_activation_keeps_defined_key() {
		$addon = $this->get_licensed_addon( new WP_Error( 'http_request_failed', 'Connection failed' ) );
		$addon->method( 'get_defined_license' )->willReturn( 'DEFINED-LICENSE-KEY' );
		delete_option( $addon->option_name . 'active' );

		$this->assertSame( 'DEFINED-LICENSE-KEY', $addon->activate_defined_license(), 'The defined key should survive a connection failure.' );
	}

	/**
	 * @covers FrmAddon::activate_license
	 *
	 * @return void
	 */
	public function test_server_rejection_clears_rejected_key() {
		$addon = $this->get_licensed_addon( 'bundle_activation_not_allowed' );
		update_option( $addon->option_name . 'key', 'TEST-LICENSE-KEY' );
		update_option( $addon->option_name . 'active', 'valid' );
		$addon->expects( $this->once() )->method( 'clear_license' );

		$response = $this->run_private_method( array( $addon, 'activate_license' ), array( 'REJECTED-LICENSE-KEY' ) );

		$this->assertFalse( $response['success'], 'A rejected key should not activate.' );
		$this->assertFalse( $response['inconclusive'], 'A server rejection should be conclusive.' );
		$this->assertFalse( get_option( $addon->option_name . 'active' ), 'The active flag should be cleared.' );
		$this->assertFalse( get_option( $addon->option_name . 'key' ), 'The rejected key should be removed.' );
	}

	/**
	 * @covers FrmAddon::update_pro_capabilities
	 */
	public function test_update_pro_capabilities() {
		// Remove the roles first so we're not getting false positives for data that already exists prior to running FrmAddon::update_pro_capabilities.
		$caps       = array_keys( FrmAppHelper::frm_capabilities( 'pro_only' ) );
		$admin_role = get_role( 'administrator' );

		foreach ( $caps as $cap ) {
			$admin_role->remove_cap( $cap );
		}

		$this->run_private_method( array( $this->addon, 'update_pro_capabilities' ) );

		// The global $wp_roles object stores an internal role_objects array.
		// We need to reset the $wp_roles object in order to avoid stale WP_Role capabilities.
		global $wp_roles;
		$wp_roles = new WP_Roles(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride

		$admin_role = get_role( 'administrator' );

		foreach ( $caps as $cap ) {
			$this->assertTrue( $admin_role->has_cap( $cap ) );
		}
	}
}

class FrmTestAddon extends FrmAddon {

	public $plugin_name = 'Signature';
	public $download_id = 163248;
	public $version     = '1.10';

	public function __construct() {
		$this->plugin_file = FrmAppHelper::plugin_path() . '/signature.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmTestAddon();
	}
}
