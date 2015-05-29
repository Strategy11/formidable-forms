<?php
/**
 * @group app
 */
class WP_Test_FrmAppController extends FrmUnitTest {
	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	/* Back-end tests */

	public function test_menu() {
		$this->set_admin_screen();
		$this->set_as_user_role( 'administrator' );

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
		$this->set_as_user_role( 'subscriber' );
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
	 * @covers FrmAppController::install
	 */
	public function test_install() {
		$this->frm_install();
	}
}