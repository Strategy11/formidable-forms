<?php

/**
 * @group addons
 */
class test_FrmAddon extends FrmUnitTest {

	private $addon;

	public function setUp() {
		parent::setUp();

		$this->check_php_version( '5.4' );
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

		//TODO: Test this line: $this->license = $this->get_license();
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
	 * @covers FrmAddon::is_time_to_auto_activate
	 * @covers FrmAddon::set_auto_activate_time
	 */
	public function test_is_time_to_auto_activate() {
		$times = array(
			array(
				'time'     => time(),
				'expected' => false,
			),
			array(
				'time'     => false,
				'expected' => true,
			),
			array(
				'time'     => strtotime( '-2 days' ),
				'expected' => true,
			),
			array(
				'time'     => strtotime( '-2 hours' ),
				'expected' => false,
			)
		);

		$this->run_private_method( array( $this->addon, 'set_auto_activate_time' ) );
		$should_run = $this->run_private_method( array( $this->addon, 'is_time_to_auto_activate' ) );
		$this->assertFalse( $should_run, 'Time was set via set_auto_activate_time' );
		$option_name = $this->addon->option_name . 'last_activate';

		foreach ( $times as $time ) {
			update_option( $option_name, $time['time'] );
			$should_run = $this->run_private_method( array( $this->addon, 'is_time_to_auto_activate' ) );
			$this->assertEquals( $time['expected'], $should_run, $time['time'] . 'not properly checking' );

		}
	}
}

class FrmTestAddon extends FrmAddon {

	public $plugin_name = 'Signature';
	public $download_id = 163248;
	public $version = '1.10';

	public function __construct() {
		$this->plugin_file = FrmAppHelper::plugin_path() . '/signature.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmTestAddon();
	}
}
