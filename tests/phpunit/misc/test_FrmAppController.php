<?php
/**
 * @group app
 */
class test_FrmAppController extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
		$this->create_users();
	}

	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	/**
	 * Back-end tests
	 */
	public function test_menu() {
		$this->set_admin_screen();
		$this->set_user_by_role( 'administrator' );

		$this->markTestIncomplete( 'Needs work' );

		ob_start();
		require ABSPATH . 'wp-admin/menu.php';
		$menu = ob_get_contents();
		ob_end_clean();
		echo $menu;
		$this->assertTrue( current_user_can( 'frm_view_forms' ), 'The user cannot frm_view_forms' );

		$this->check_menu();
	}

	public function test_block_menu() {
		$this->set_user_by_role( 'subscriber' );
		$this->assertFalse( current_user_can( 'frm_view_forms' ) );

		$this->check_menu( 'block' );
	}

	private function check_menu( $allow = 'allow' ) {
		$url = get_option( 'siteurl', true );
		do_action( 'admin_menu' );

		$expected = array(
			'formidable' => $url . '/wp-admin/admin.php?page=formidable',
		);

		foreach ( $expected as $name => $value ) {
			$menu_page = menu_page_url( $name, false );

			if ( $allow === 'allow' ) {
				$this->assertEquals( $value, $menu_page );
			} else {
				$this->assertNotEquals( $value, $menu_page );
			}
		}
	}

	/**
	 * @covers FrmAppController::add_admin_class
	 * @covers FrmAppController::is_white_page
	 */
	public function test_add_admin_class() {
		$this->set_admin_screen();
		$class          = 'other-class';
		$filtered_class = apply_filters( 'admin_body_class', $class );
		$this->assertTrue( str_contains( $filtered_class, $class ), '"' . $class . '" is missing from admin classes' );
		$this->assertFalse( strpos( $filtered_class, 'frm-white-body' ), '"frm-white-body" was added to admin classes' );

		$this->set_admin_screen( 'admin.php?page=formidable' );
		$class          = 'other-class';
		$filtered_class = apply_filters( 'admin_body_class', $class );
		$this->assertTrue( str_contains( $filtered_class, $class ), '"' . $class . '" is missing from admin classes' );
		$this->assertTrue( str_contains( $filtered_class, ' frm-white-body' ), '"frm-white-body" is missing from admin classes' );
	}

	/**
	 * @covers FrmAppController::load_wp_admin_style
	 */
	public function test_load_wp_admin_style() {
		$this->set_admin_screen();

		ob_start();
		do_action( 'admin_enqueue_scripts' );
		do_action( 'admin_print_styles' );
		$styles = ob_get_contents();
		ob_end_clean();

		$this->assertNotEmpty( $styles );
		$this->assertTrue( str_contains( $styles, FrmAppHelper::plugin_url() . '/css/frm_fonts.css' ), 'The frm_fonts stylesheet is missing' );
	}

	/**
	 * @covers FrmAppController::needs_update
	 */
	public function test_needs_update() {
		update_option( 'frm_db_version', 1 );
		$needs_update = FrmAppController::needs_update();
		$this->assertTrue( $needs_update, 'The DB needs update but is skipping it' );
	}

	/**
	 * @covers FrmAppController::compare_for_update
	 */
	public function test_compare_for_update() {
		$tests = array(
			array(
				'version'  => '',
				'db'       => 74,
				'expected' => true,
			),
			array(
				'version'  => '',
				'db'       => 50,
				'expected' => true,
			),
			array(
				'version'  => '2.05.09',
				'db'       => 75,
				'expected' => true,
			),
			array(
				'version'  => '3.0',
				'db'       => 51,
				'expected' => true,
			),
			array(
				'version'  => '5.0',
				'db'       => 98,
				'expected' => true,
			),
			array(
				'version'  => '7.0', // This version should be later than the current version (Bump this to 8 when v7.0 is released).
				'db'       => FrmAppHelper::$db_version + 1,
				'expected' => false,
			),
			array(
				'version'  => '7.01.10',
				'db'       => 900,
				'expected' => false,
			),
			array(
				'version'  => FrmAppHelper::plugin_version(),
				'db'       => FrmAppHelper::$db_version,
				'expected' => false,
			),
			array(
				'version'  => FrmAppHelper::plugin_version(),
				'db'       => FrmAppHelper::$db_version - 1, // previous version
				'expected' => true,
			),
			array(
				'version'  => FrmAppHelper::plugin_version(),
				'db'       => FrmAppHelper::$db_version + 1, // next version
				'expected' => false,
			),
		);

		foreach ( $tests as $test ) {
			$current = $test['version'] . '-' . $test['db'];
			update_option( 'frm_db_version', $current );
			$option = get_option( 'frm_db_version' );
			$this->assertSame( $current, $option );

			$upgrade = FrmAppController::compare_for_update(
				array(
					'option'             => 'frm_db_version',
					'new_db_version'     => FrmAppHelper::$db_version,
					'new_plugin_version' => FrmAppHelper::plugin_version(),
				)
			);
			$this->assertEquals( $test['expected'], $upgrade, $test['version'] . ' db: ' . $test['db'] . ' => ' . $current . ( $upgrade ? ' needs no update ' : ' needs an update' ) . ' from ' . $option );
		}
	}

	/**
	 * @covers FrmAppController::api_install
	 */
	public function test_api_install() {
		delete_option( 'frm_install_running' );

		if ( FrmAppHelper::doing_ajax() ) {
			$this->markTestSkipped( 'Run without ajax' );
		}

		$current_db  = FrmAppHelper::plugin_version() . '-' . FrmAppHelper::$db_version;
		$previous_db = FrmAppHelper::plugin_version() . '-' . ( absint( FrmAppHelper::$db_version ) - 1 );
		update_option( 'frm_db_version', $previous_db );
		FrmAppController::admin_init();
		$new_db = get_option( 'frm_db_version' );
		$this->assertSame( $current_db, $new_db, 'The DB did not update correctly' );
	}

	/**
	 * @covers FrmAppController::network_upgrade_site
	 */
	public function test_network_upgrade_site() {
		FrmAppController::network_upgrade_site();
		$this->addToAssertionCount( 1 );
	}
}
