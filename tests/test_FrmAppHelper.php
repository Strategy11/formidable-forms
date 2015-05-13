<?php

class WP_Test_FrmAppHelper extends FrmUnitTest {
	/**
	 * @covers FrmAppHelper::plugin_version
	 */
	function test_plugin_version() {
		$version = FrmAppHelper::plugin_version();
		$this->assertNotEmpty( $version );

		$plugin_data = get_plugin_data( dirname( __FILE__ ) . '/../formidable.php' );
		$expected_version = $plugin_data['Version'];
		$this->assertEquals( $version, $expected_version );
	}

	/**
	 * @covers FrmAppHelper::plugin_folder
	 */
	function test_plugin_folder() {
		$folder = FrmAppHelper::plugin_folder();
		$expected = 'formidable';
		$this->assertEquals( $folder, $expected );
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
		if ( is_callable( 'FrmUpdatesController::pro_is_authorized' ) ) {
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
		$_GET['test'] = 'test';
		$result = FrmAppHelper::get_param( 'test', '', 'get', 'sanitize_text_field' );
		$this->assertEquals( $result, 'test' );

		$_POST['test2'] = 'test';
		$result = FrmAppHelper::get_param( 'test2', '', 'post', 'sanitize_text_field' );
		$this->assertEquals( $result, 'test' );

		$_POST['item_meta'][25] = 'test';
		$result = FrmAppHelper::get_param( 'item_meta[25]', '', 'post' );
		$this->assertEquals( $result, 'test' );
	}
}