<?php
/**
 * @group ajax
 */
class Tests_Frm_Ajax extends FrmAjaxUnitTest {

	function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( 'formidable/formidable.php' ) );
	}

    /**
	 * Prevent unauthorized user from uninstalling
	 */
	function test_block_uninstall() {
		// log in as user
		$role = 'editor';
		$user_id = $this->factory->user->create( compact( 'role' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( current_user_can( $role ) );

		$_POST = array(
			'action' => 'frm_uninstall',
		);

		$response = $this->trigger_action( 'frm_uninstall' );

		$frm_settings = FrmAppHelper::get_settings();
		$expected = $frm_settings->admin_permission;

		$this->assertSame( $expected, $response );

        global $wpdb;
        $exists = $wpdb->query( 'DESCRIBE '. $wpdb->prefix . 'frm_fields' );
        $this->assertNotEmpty( $exists );
	}
}
