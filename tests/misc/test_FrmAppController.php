<?php
/**
 * @group app
 */
class test_FrmAppController extends FrmUnitTest {
	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	/* Back-end tests */

	public function test_menu() {
		$this->set_admin_screen();
		$this->set_user_by_role( 'administrator' );

		$this->markTestIncomplete( 'Needs work' );

        ob_start();
        require( ABSPATH . 'wp-admin/menu.php' );
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
			if ( $allow == 'allow' ){
				$this->assertEquals( $value, $menu_page );
			} else {
				$this->assertNotEquals( $value, $menu_page );
			}
		}
	}

	/**
	 * @covers FrmAppController::add_admin_class
	 */
	public function test_add_admin_class() {
		$this->set_admin_screen();
		$class = 'other-class';
		$filtered_class = apply_filters( 'admin_body_class', $class );
		$this->assertTrue( strpos( $filtered_class, $class ) !== false, '"' . $class . '" is missing from admin classes' );
		$this->assertFalse( strpos( $filtered_class, 'frm-white-body' ), '"frm-white-body" was added to admin classes' );

		$this->set_admin_screen( 'admin.php?page=formidable' );
		$class = 'other-class';
		$filtered_class = apply_filters( 'admin_body_class', $class );
		$this->assertTrue( strpos( $filtered_class, $class ) !== false, '"' . $class . '" is missing from admin classes' );
		$this->assertTrue( strpos( $filtered_class, ' frm-white-body' ) !== false, '"frm-white-body" is missing from admin classes' );
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
		$this->assertTrue( strpos( $styles, FrmAppHelper::plugin_url() . '/css/frm_fonts.css' ) !== false, 'The frm_fonts stylesheet is missing' );
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
	 * @covers FrmAppController::api_install
	 */
	public function test_api_install() {
		$current_db = FrmAppHelper::$db_version;
		update_option( 'frm_db_version', absint( $current_db ) - 1 );
		FrmAppController::admin_init();
		$new_db = get_option( 'frm_db_version' );
		$this->assertSame( $new_db, $current_db, 'The DB did not update correctly' );
	}
}
