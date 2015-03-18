<?php

class WP_Test_FrmAppController extends FrmUnitTest {
	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	public function test_menu() {
		$current_user = get_current_user_id();
		$this->set_as_user_role( 'administrator' );
		//$this->assertTrue( current_user_can( 'frm_view_forms' ) );
		
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

	public function test_install() {
		$this->frm_install();
	}

	public function test_uninstall() {
		$this->set_as_user_role('administrator');

		$frmdb = new FrmDb();
		$uninstalled = $frmdb->uninstall();
		$this->assertTrue( $uninstalled );
	}

    public static function atts_clickable($replace_with) {
		$replace_with = make_clickable( $replace_with );
		$replace_with = preg_replace( "/(<a href='[^']*') rel='nofollow'/", '$1', $replace_with );
        return $replace_with;
    }
	
}