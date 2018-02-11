<?php
/**
 * @group app
 */
class test_FrmAppHelper extends FrmUnitTest {

	/**
	 * @covers FrmAppHelper::plugin_version
	 */
	function test_plugin_version() {
		$version = FrmAppHelper::plugin_version();
		$this->assertNotEmpty( $version );

		$plugin_data = get_plugin_data( dirname( dirname( __FILE__ ) ) . '/../formidable.php' );
		$expected_version = $plugin_data['Version'];
		$this->assertEquals( $version, $expected_version );
	}

	/**
	 * @covers FrmAppHelper::plugin_folder
	 */
	function test_plugin_folder() {
		$folder = FrmAppHelper::plugin_folder();
		$expected = array( 'formidable', 'formidable-forms' );
		$this->assertTrue( in_array( $folder, $expected ) );
	}

	/**
	 * @covers FrmAppHelper::plugin_path
	 */
	function test_plugin_path() {
		$path = FrmAppHelper::plugin_path();
		$expected_file = $path . '/formidable.php';
		$this->assertTrue( file_exists( $expected_file ) );
	}

	/**
	 * @covers FrmAppHelper::plugin_url
	 */
	function test_plugin_url() {
		$url = FrmAppHelper::plugin_url();
		$this->assertNotEmpty( $url );
	}

	/**
	 * @covers FrmAppHelper::get_settings
	 */
	function test_get_settings() {
		$settings = FrmAppHelper::get_settings();
		$this->assertNotEmpty( $settings );
		$this->assertTrue( is_object( $settings ) );
		$this->assertNotEmpty( $settings->success_msg );
	}

	/**
	 * @covers FrmAppHelper::pro_is_installed
	 */
	function test_pro_is_installed() {
		$active = FrmAppHelper::pro_is_installed();
		if ( is_callable( 'FrmProEddController::pro_is_authorized' ) ) {
			$this->assertTrue( $active );
		} else {
			$this->assertFalse( $active );
		}
	}

	/**
	 * @covers FrmAppHelper::is_empty_value
	 */
	function test_is_empty_value() {
		$empty_value = FrmAppHelper::is_empty_value( '' );
		$this->assertTrue( $empty_value );

		$empty_value = FrmAppHelper::is_empty_value( array() );
		$this->assertTrue( $empty_value );

		$not_empty_value = FrmAppHelper::is_empty_value( 'test' );
		$this->assertFalse( $not_empty_value );

		$not_empty_value = FrmAppHelper::is_empty_value( array( 'test' ) );
		$this->assertFalse( $not_empty_value );
	}

	/**
	 * @covers FrmAppHelper::get_server_value
	 */
	function test_get_server_value() {
		$url = FrmAppHelper::get_server_value( 'HTTP_HOST' );
		$this->assertEquals( $url, 'example.org' );

		$_SERVER['HTTP_HOST'] = '<script>alert()</script>example.org';
		$url = FrmAppHelper::get_server_value( 'HTTP_HOST' );
		$this->assertEquals( $url, 'example.org' );
	}

	/**
	 * @covers FrmAppHelper::get_param
	 */
	function test_get_param() {
		$set_value = '<script></script>test';
		$expected_value = 'test';
		$_GET['test'] = $_POST['test2'] = $_POST['item_meta'][25] = $set_value;

		$result = FrmAppHelper::get_param( 'test', '', 'get', 'sanitize_text_field' );
		$this->assertEquals( $result, $expected_value );

		$result = FrmAppHelper::get_param( 'test2', '', 'post', 'sanitize_text_field' );
		$this->assertEquals( $result, $expected_value );

		$result = FrmAppHelper::get_param( 'item_meta[25]', '', 'post', 'sanitize_text_field' );
		$this->assertEquals( $result, $expected_value );
	}

	/**
	 * @covers FrmAppHelper::get_post_param
	 * @covers FrmAppHelper::get_simple_request
	 */
	function test_get_post_param() {
		$set_value = '<script></script>test';
		$expected_value = 'test';
		$_POST['test3'] = $set_value;

		$result = FrmAppHelper::get_post_param( 'test3', '', 'sanitize_text_field' );
		$this->assertEquals( $result, $expected_value );
	}

	/**
	 * @covers FrmAppHelper::sanitize_value
	 */
	function test_sanitize_value() {
		$set_value = '<script></script>test';
		$expected_value = 'test';
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $set_value );
		$this->assertEquals( $set_value, $expected_value );
	}

	/**
	 * @covers FrmAppHelper::simple_get
	 * @covers FrmAppHelper::get_simple_request
	 */
	function test_simple_get() {
		$set_value = '<script></script>test';
		$expected_value = 'test';
		$_GET['test4'] = $set_value;

		$result = FrmAppHelper::simple_get( 'test4' );
		$this->assertEquals( $result, $expected_value );
	}

	/**
	 * @covers FrmAppHelper::sanitize_request
	 */
	function test_sanitize_request() {
		$values = array(
			'form_id' => '<script></script>12',
            'frm_action' => '<script></script>create me',
            'form_key'   => '<script></script>This is a <b>text</b> field',
			'content'    => '<script></script>This is a <b>text</b> field',
		);

        $sanitize_method = array(
            'form_id'    => 'absint',
            'frm_action' => 'sanitize_title',
            'form_key'   => 'sanitize_text_field',
			'content'    => 'wp_kses_post',
        );

        FrmAppHelper::sanitize_request( $sanitize_method, $values );

		$this->assertEquals( $values['form_id'], absint( $values['form_id'] ) );
		$this->assertEquals( $values['frm_action'], sanitize_title( $values['frm_action'] ) );
		$this->assertEquals( $values['form_key'], sanitize_text_field( $values['form_key'] ) );
		$this->assertEquals( $values['content'], wp_kses_post( $values['content'] ) );
	}

	/**
	 * @covers FrmAppHelper::kses
	 */
	function test_kses() {
		$start_value = '<script><script>';
		$safe_value = 'Hello, <a href="/test">click here</a>';
		$start_value .= $safe_value;

		$stripped_value = FrmAppHelper::kses( $start_value );
		$this->assertEquals( $stripped_value, 'Hello, click here' );

		$stripped_value = FrmAppHelper::kses( $start_value, array( 'a' ) );
		$this->assertEquals( $stripped_value, $safe_value );
	}

	/**
	 * @covers FrmAppHelper::remove_get_action
	 */
	function test_remove_get_action() {
		$_GET['action'] = 'bulk_trash';
		$start_url = $_SERVER['REQUEST_URI'] = admin_url( 'admin.php?page=formidable&action=bulk_trash' );
		FrmAppHelper::remove_get_action();
		$new_url = FrmAppHelper::get_server_value( 'REQUEST_URI' );
		$this->assertNotEquals( $new_url, $start_url );
	}

	/**
	 * @covers FrmAppHelper::get_query_var
	 */
    function test_get_query_var() {
		$new_post_id = $this->go_to_new_post();
		$get_post_id = FrmAppHelper::get_query_var( '', 'p' );
		$this->assertEquals( $new_post_id, $get_post_id );
    }

	/**
	 * @covers FrmAppHelper::maybe_add_permissions
	 */
    function test_maybe_add_permissions() {
		$this->set_user_by_role( 'subscriber' );
		$this->assertFalse( current_user_can( 'frm_view_forms' ), 'Subscriber can frm_view_forms' );
		$this->assertFalse( current_user_can( 'frm_edit_forms' ), 'Subscriber can frm_edit_forms' );

		$this->set_user_by_role( 'administrator' );
        $frm_roles = FrmAppHelper::frm_capabilities();
        foreach ( $frm_roles as $frm_role => $frm_role_description ) {
			$this->assertTrue( current_user_can( $frm_role ), 'Admin cannot ' . $frm_role );
        }
    }
}
