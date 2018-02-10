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
	function test_block_uninstall(){
        $this->set_as_user_role( 'editor' );

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
