<?php

/**
 * @group addons
 */
class test_FrmAddon extends FrmUnitTest {

	private $addon;

	public function setUp(): void {
		parent::setUp();

		$this->addon = $this->getMockBuilder( 'FrmTestAddon' )
							->setMethods( null )
							->getMock();
	}

	/**
	 * @covers FrmAddon::__construct
	 */
	public function test_construct() {
		$this->assertEquals( 'signature', $this->addon->plugin_slug );
		$this->assertEquals( 'edd_signature_license_', $this->addon->option_name );

		// TODO: Test this line: $this->license = $this->get_license();
	}

	/**
	 * @covers FrmAddon::insert_installed_addon
	 */
	public function test_insert_installed_addon() {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		$this->assertTrue( isset( $plugins['signature'] ) );
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
		$this->assertEquals( $license_key, $license );
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

		$this->run_private_method( array( $this->addon, 'update_last_checked' ) );
		$should_run = $this->run_private_method( array( $this->addon, 'checked_recently' ), array( '1 hour' ) );
		$this->assertTrue( $should_run, 'Time was set via update_last_checked' );
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

			$should_run = $this->run_private_method( array( $this->addon, 'checked_recently' ), array( '1 day' ) );
			$this->assertEquals( $time['expected'], $should_run, $time['time'] . 'not properly checking' );

		}
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
