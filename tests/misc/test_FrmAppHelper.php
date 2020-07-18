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
	 * The path is relative if it starts with /
	 * @covers FrmAppHelper::relative_plugin_url
	 */
	function test_relative_plugin_url() {
		$path = FrmAppHelper::relative_plugin_url();
		$this->assertEquals( strpos( $path, '/' ), 0 );
	}

	/**
	 * @covers FrmAppHelper::site_url
	 */
	function test_site_url() {
		$url = FrmAppHelper::site_url();
		$this->assertEquals( 'http://example.org', $url );
	}

	/**
	 * @covers FrmAppHelper::plugin_url
	 */
	function test_plugin_url() {
		$url = FrmAppHelper::plugin_url();
		$this->assertNotEmpty( $url );
	}

	/**
	 * @covers FrmAppHelper::make_affiliate_url
	 */
	function test_make_affiliate_url() {
		add_filter( 'frm_affiliate_id', '__return_false' );
		$urls = array( 'http://site.com', 'https://site.com/page/' );
		foreach ( $urls as $url ) {
			$new_url = FrmAppHelper::make_affiliate_url( $url );
			$this->assertEquals( $url, $new_url );
		}

		add_filter( 'frm_affiliate_id', '__return_true' );
		$urls = array(
			'http://site.com'        => 'site.com',
			'https://site.com/page/' => 'site.com/page/',
		);
		foreach ( $urls as $url => $expected ) {
			$new_url = FrmAppHelper::make_affiliate_url( $url );
			$expected = 'http://www.shareasale.com/r.cfm?u=1&b=841990&m=64739&afftrack=plugin&urllink=' . urlencode( $expected );
			$this->assertEquals( $expected, $new_url );
		}
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
	 * @covers FrmAppHelper::is_formidable_admin
	 */
	function test_is_formidable_admin() {
		$page_names = array(
			'nope'               => false,
			'formidable'         => true,
			'formidable-entries' => true,
			'entry-formidable'   => true,
		);
		foreach ( $page_names as $page => $expected ) {
			$_GET['page'] = $page;
			$is_admin = FrmAppHelper::is_formidable_admin();
			$this->assertEquals( $expected, $is_admin );
		}

		$_GET['page'] = '';

		$page = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$view = $this->factory->post->create( array( 'post_type' => 'frm_display' ) );

		$admin_pages = array(
			'index.php'                      => false,
			'edit.php?post_type=frm_display' => true,
			'edit.php?post_type=post'        => false,
			'post.php?post=' . $view . '&action=edit&view=1' => true,
			'post.php?post=' . $page . '&action=edit' => false,
		);
		foreach ( $admin_pages as $admin_page => $expected ) {
			$this->set_admin_screen( $admin_page );
			$is_admin = FrmAppHelper::is_formidable_admin();
			$this->assertEquals( $expected, $is_admin, $admin_page . ' returned unexpected result' );
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
		$values = array(
			array(
				'value'    => '<script></script>test',
				'expected' => 'test',
			),
			array(
				'value'    => array(
					'<script></script>test',
					'another test',
				),
				'expected' => array(
					'test',
					'another test',
				),
			),
		);
		foreach ( $values as $value ) {
			FrmAppHelper::sanitize_value( 'sanitize_text_field', $value['value'] );
			$this->assertEquals( $value['expected'], $value['value'] );
		}
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
	 * @covers FrmAppHelper::get_simple_request
	 */
	function test_get_simple_request() {
		$result = FrmAppHelper::get_simple_request( array(
			'type'  => 'request',
			'param' => 'test5',
		) );
		$this->assertEquals( '', $result );

		$set_value = '<script></script>test';
		$expected = 'test';
		$_REQUEST['test5'] = $set_value;

		$result = FrmAppHelper::get_simple_request( array(
			'type'  => 'request',
			'param' => 'test5',
		) );
		$this->assertEquals( $expected, $result );
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
	 * @covers FrmAppHelper::allowed_html
	 */
	function test_allowed_html() {
		$safe_html = $this->run_private_method( array( 'FrmAppHelper', 'safe_html' ), array() );
		$tests = array(
			array(
				'start'    => 'all',
				'expected' => $safe_html,
			),
			array(
				'start'    => array( 'a' ),
				'expected' => array(
					'a' => $safe_html['a'],
				),
			),
			array(
				'start'    => array( 'a', 'br' ),
				'expected' => array(
					'a'  => $safe_html['a'],
					'br' => $safe_html['br'],
				),
			),
		);
		foreach ( $tests as $test ) {
			$allowed = $this->run_private_method( array( 'FrmAppHelper', 'allowed_html' ), array( $test['start'] ) );
			$this->assertSame( $test['expected'], $allowed );
		}
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
	
	/**
	 * @group visibility
	 * @covers FrmAppHelper::wp_roles_dropdown (single, private)
	 */
	function test_wp_roles_dropdown() {
		FrmAppHelper::wp_roles_dropdown( 'field_options[admin_only_2][]', 'administrator' );
		
		$this->assert_output_contains( 'name="field_options[admin_only_2][]"' );
		$this->assert_output_contains( 'id="field_options_admin_only_2"' );
		$this->assert_output_not_contains( 'multiple="multiple"', 'default is single' );

		$this->assert_output_contains( '>Administrator' );

		$this->assert_output_not_contains( 'Everyone', 'private by default should not include Everyone' );
		$this->assert_output_not_contains( 'Logged-out Users', 'private by default should not include Logged-Out Users' );

		$this->assert_output_contains( 'Logged-in Users', 'private still includes Logged-In Users' );
	}

	/**
	 * @group visibility
	 * @covers FrmAppHelper::wp_roles_dropdown (multiple, public)
	 */
	function test_wp_roles_dropdown_multiple_public() {
		FrmAppHelper::wp_roles_dropdown( 'field_options', array( 'loggedin', 'loggedout' ), 'multiple', 'public' );

		$this->assert_output_contains( 'name="field_options"' );
		$this->assert_output_contains( 'id="field_options"' );
		$this->assert_output_contains( 'multiple="multiple"' );

		$this->assert_output_contains( 'Everyone', 'in additon to private options, public should include Everyone' );
		$this->assert_output_contains( 'Logged-out Users', 'in additon to private options, public should include Logged-Out Users' );
	}

	/**
	 * @group visibility
	 * @covers FrmAppHelper::roles_options ($public = 'private')
	 */
	function test_roles_options_private() {
		FrmAppHelper::roles_options( 'editor' );

		$this->assert_output_contains( '>Administrator' );
		$this->assert_output_contains( 'selected="selected">Editor' );
		$this->assert_output_contains( '>Author' );
		$this->assert_output_contains( '>Contributor' );
		$this->assert_output_contains( '>Subscriber' );

		$this->assert_output_not_contains( 'Everyone', 'private by default should not include Everyone' );
		$this->assert_output_not_contains( 'Logged-out Users', 'private by default should not include Logged-Out Users' );

		$this->assert_output_contains( 'Logged-in Users', 'private still includes Logged-In Users' );
	}

	/**
	 * @group visibility
	 * @covers FrmAppHelper::roles_options ($public = 'private') with '' value (which should show that "Logged-In Users" is selected)
	 */
	function test_roles_options_private_empty_string_option() {
		FrmAppHelper::roles_options( '' );

		$this->assert_output_contains( '>Editor' );
		$this->assert_output_not_contains( 'selected="selected">Editor' );
		$this->assert_output_contains( 'selected="selected">Logged-in Users' );

		$this->assert_output_not_contains( 'Everyone', 'private by default should not include Everyone' );
		$this->assert_output_not_contains( 'Logged-out Users', 'private by default should not include Logged-Out Users' );

		$this->assert_output_contains( 'Logged-in Users', 'private still includes Logged-In Users' );
	}

	/**
	 * @group visibility
	 * @covers FrmAppHelper::roles_options ($public = 'public')
	 */
	function test_roles_options_public() {
		FrmAppHelper::roles_options( array( 'author', 'contributor' ), 'public' );

		$this->assert_output_not_contains( 'selected="selected">Editor' );

		$this->assert_output_contains( '>Administrator' );
		$this->assert_output_contains( '>Editor' );
		$this->assert_output_contains( 'selected="selected">Author' );
		$this->assert_output_contains( 'selected="selected">Contributor' );
		$this->assert_output_contains( '>Subscriber' );
		$this->assert_output_contains( 'Logged-in Users' );

		$this->assert_output_contains( 'Everyone', 'in additon to private options, public should include Everyone' );
		$this->assert_output_contains( 'Logged-out Users', 'in additon to private options, public should include Logged-Out Users' );
	}

	/**
	 * @group visibility
	 * @covers FrmAppHelper::roles_options ($public = 'public') with the '' value (Which should show that "Everyone" is selected)
	 */
	function test_roles_options_public_empty_string_option() {
		FrmAppHelper::roles_options( '', 'public' );

		$this->assert_output_not_contains( 'selected="selected">Editor' );

		$this->assert_output_contains( '>Administrator' );
		$this->assert_output_contains( '>Editor' );
		$this->assert_output_not_contains( 'selected="selected">Author' );
		$this->assert_output_not_contains( 'selected="selected">Contributor' );
		$this->assert_output_contains( '>Subscriber' );
		$this->assert_output_contains( 'Logged-in Users' );
		$this->assert_output_not_contains( 'selected="selected">Logged-in Users', 'Logged-in Users potion should not be selected' );

		$this->assert_output_contains( 'selected="selected">Everyone', 'Everyone option should be selected' );
		$this->assert_output_contains( 'Logged-out Users', 'in additon to private options, public should include Logged-Out Users' );
	}

	/**
	 * @param string $substring
	 * @param string $message
	 */
	private function assert_output_contains( $substring, $message = '' ) {
		$this->assertTrue( strpos( $this->getActualOutput(), $substring ) !== FALSE, $message );
	}

	/**
	 * @param string $substring
	 * @param string $message
	 */
	private function assert_output_not_contains( $substring, $message = '' ) {
		$this->assertTrue( strpos( $this->getActualOutput(), $substring ) === FALSE, $message );
	}
}
