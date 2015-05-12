<?php

class WP_Test_FrmAppController extends FrmUnitTest {
	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	/** Front-end tests */

	/**
	 * Make sure the stylesheet is loaded at the right times
	 */
	public function test_front_head() {
		$this->set_front_end();

        ob_start();
        do_action( 'wp_head' );
        $styles = ob_get_contents();
        ob_end_clean();

		$this->assertNotEmpty( $styles );

		$frm_settings = FrmAppHelper::get_settings();
		$stylesheet_urls = $this->get_custom_stylesheet();
		$style_included = strpos( $styles, $stylesheet_urls['formidable'] );
		if ( $frm_settings->load_style == 'all' ) {
			$this->assertTrue( $style_included !== false, 'The formidablepro stylesheet is missing' );
		} else {
			$this->assertTrue( $style_included === false, 'The formidablepro stylesheet is included when it should not be' );
		}
	}

	/**
	 * @covers FrmAppController::custom_stylesheet
	 */
	private function get_custom_stylesheet() {
		global $frm_vars;
		$frm_vars['css_loaded'] = false;

		$stylesheet_urls = FrmAppController::custom_stylesheet();
		$this->assertTrue( isset( $stylesheet_urls['formidable'] ), 'The stylesheet array is empty' );
		return $stylesheet_urls;
	}

	/* Back-end tests */

	public function test_menu() {
		$this->set_admin_screen();

		$current_user = get_current_user_id();
		$this->set_as_user_role( 'administrator' );
		//$this->assertTrue( current_user_can( 'frm_view_forms' ), 'The user does not have frm_view_forms permission' );

		//$this->check_menu();

		wp_set_current_user( $current_user );
	}

	public function test_block_menu() {
		$current_user = get_current_user_id();
		$this->set_as_user_role( 'subscriber' );
		$this->assertFalse( current_user_can( 'frm_view_forms' ) );

		$this->check_menu( 'block' );

		wp_set_current_user( $current_user );
	}

	private function check_menu( $allow = 'allow' ) {
		update_option( 'siteurl', 'http://example.com' );
		do_action( 'admin_menu' );

		$expected = array(
			'formidable' => 'http://example.com/wp-admin/admin.php?page=formidable',
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

	public function test_load_wp_admin_style() {
        ob_start();
		do_action( 'admin_enqueue_scripts' );
        do_action( 'admin_print_styles' );
        $styles = ob_get_contents();
        ob_end_clean();

		$this->assertNotEmpty( $styles );
		// TODO: make this function fire from admin
		//$this->assertTrue( strpos( $styles, FrmAppHelper::plugin_url() . '/css/frm_fonts.css' ) !== false, 'The frm_fonts stylesheet is missing' );
	}

	public function test_install() {
		$this->frm_install();
	}
}