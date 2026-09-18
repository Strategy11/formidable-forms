<?php

/**
 * @group addons
 */
class test_FrmAddon extends FrmUnitTest {

	private $addon;

	public function setUp(): void {
		parent::setUp();

		$this->addon = $this->getMockBuilder( 'FrmTestAddon' )->setMethods()
							->getMock();
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
	 * @since x.x
	 *
	 * @param mixed $payload What the API request should come back with.
	 *
	 * @return PHPUnit\Framework\MockObject\MockObject
	 */
	private function get_licensed_addon( $payload ) {
		$addon = $this->getMockBuilder( 'FrmTestAddon' )
						->disableOriginalConstructor()
						->setMethods( array( 'send_mothership_request', 'clear_license' ) )
						->getMock();

		$addon->method( 'send_mothership_request' )->willReturn( $payload );

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
	 * @since x.x
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
		$this->assertSame( $status, $response['status'] );
	}

	/**
	 * @since x.x
	 *
	 * @return void mixed>, mixed>>
	 */
	public function license_status_provider(): \Iterator {
		// The API reported on the license, so the status is a verdict.
		yield 'valid' => array( array( 'license' => 'valid' ), false, 'valid' );
		yield 'invalid' => array( array( 'license' => 'invalid' ), false, 'invalid' );
		yield 'revoked' => array( array( 'license' => 'revoked' ), false, 'revoked' );
		yield 'disabled' => array( array( 'license' => 'disabled' ), false, 'disabled' );
		yield 'expired' => array( array( 'license' => 'expired' ), false, 'expired' );
		yield 'no_activations_left' => array( array( 'license' => 'no_activations_left' ), false, 'no_activations_left' );
		// Nothing came back about the license, so there is no verdict to report.
		yield 'error payload' => array( array( 'code' => 500 ), true, 'missing' );
		yield 'empty payload' => array( array(), true, 'missing' );
		yield 'connection error' => array( 'You had an error communicating with the Formidable API.', true, 'You had an error communicating with the Formidable API.' );
		yield 'no body' => array( null, true, 'missing' );
	}

	/**
	 * A license that activated before stays in place until the API says otherwise.
	 * Losing the connection is not a revocation.
	 *
	 * @since x.x
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
	 * @since x.x
	 *
	 * @return void mixed>, mixed>>
	 */
	public function revoked_license_provider(): \Iterator {
		// The API reported the license is no longer usable.
		yield 'revoked' => array( array( 'license' => 'revoked' ), true );
		yield 'blocked' => array( array( 'license' => 'blocked' ), true );
		yield 'disabled' => array( array( 'license' => 'disabled' ), true );
		yield 'missing' => array( array( 'license' => 'missing' ), true );
		// The license is still usable, or nothing came back about it.
		yield 'valid' => array( array( 'license' => 'valid' ), false );
		yield 'invalid' => array( array( 'license' => 'invalid' ), false );
		yield 'expired' => array( array( 'license' => 'expired' ), false );
		yield 'error payload' => array( array( 'code' => 500 ), false );
		yield 'empty payload' => array( array(), false );
		yield 'connection error' => array( 'You had an HTTP error connecting to the Formidable API', false );
		yield 'no body' => array( null, false );
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
