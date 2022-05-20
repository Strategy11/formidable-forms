<?php
/**
 * @group app
 */
class test_FrmAppHelper extends FrmUnitTest {

	/**
	 * @covers FrmAppHelper::plugin_version
	 */
	public function test_plugin_version() {
		$version = FrmAppHelper::plugin_version();
		$this->assertNotEmpty( $version );

		$plugin_data = get_plugin_data( dirname( dirname( __FILE__ ) ) . '/../formidable.php' );
		$expected_version = $plugin_data['Version'];
		$this->assertEquals( $version, $expected_version );
	}

	/**
	 * @covers FrmAppHelper::plugin_folder
	 */
	public function test_plugin_folder() {
		$folder = FrmAppHelper::plugin_folder();
		$expected = array( 'formidable', 'formidable-forms' );
		$this->assertTrue( in_array( $folder, $expected ) );
	}

	/**
	 * @covers FrmAppHelper::plugin_path
	 */
	public function test_plugin_path() {
		$path = FrmAppHelper::plugin_path();
		$expected_file = $path . '/formidable.php';
		$this->assertTrue( file_exists( $expected_file ) );
	}

	/**
	 * The path is relative if it starts with /
	 *
	 * @covers FrmAppHelper::relative_plugin_url
	 */
	public function test_relative_plugin_url() {
		$path = FrmAppHelper::relative_plugin_url();
		$this->assertEquals( strpos( $path, '/' ), 0 );
	}

	/**
	 * @covers FrmAppHelper::site_url
	 */
	public function test_site_url() {
		$url = FrmAppHelper::site_url();
		$this->assertEquals( 'http://example.org', $url );
	}

	/**
	 * @covers FrmAppHelper::plugin_url
	 */
	public function test_plugin_url() {
		$url = FrmAppHelper::plugin_url();
		$this->assertNotEmpty( $url );
	}

	/**
	 * @covers FrmAppHelper::make_affiliate_url
	 */
	public function test_make_affiliate_url() {
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
	public function test_get_settings() {
		$settings = FrmAppHelper::get_settings();
		$this->assertNotEmpty( $settings );
		$this->assertTrue( is_object( $settings ) );
		$this->assertNotEmpty( $settings->success_msg );
	}

	/**
	 * @covers FrmAppHelper::pro_is_installed
	 */
	public function test_pro_is_installed() {
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
	public function test_is_formidable_admin() {
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
	public function test_is_empty_value() {
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
	public function test_get_server_value() {
		$url = FrmAppHelper::get_server_value( 'HTTP_HOST' );
		$this->assertEquals( $url, 'example.org' );

		$_SERVER['HTTP_HOST'] = '<script>alert()</script>example.org';
		$url = FrmAppHelper::get_server_value( 'HTTP_HOST' );
		$this->assertEquals( $url, 'example.org' );
	}

	/**
	 * @covers FrmAppHelper::get_param
	 */
	public function test_get_param() {
		$set_value              = '<script></script>test';
		$expected_value         = 'test';
		$_GET['test']           = $set_value;
		$_POST['test2']         = $set_value;
		$_POST['item_meta'][25] = $set_value;

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
	public function test_get_post_param() {
		$set_value = '<script></script>test';
		$expected_value = 'test';
		$_POST['test3'] = $set_value;

		$result = FrmAppHelper::get_post_param( 'test3', '', 'sanitize_text_field' );
		$this->assertEquals( $result, $expected_value );
	}

	/**
	 * @covers FrmAppHelper::sanitize_value
	 */
	public function test_sanitize_value() {
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
	public function test_simple_get() {
		$set_value = '<script></script>test';
		$expected_value = 'test';
		$_GET['test4'] = $set_value;

		$result = FrmAppHelper::simple_get( 'test4' );
		$this->assertEquals( $result, $expected_value );
	}

	/**
	 * @covers FrmAppHelper::get_simple_request
	 */
	public function test_get_simple_request() {
		$result = FrmAppHelper::get_simple_request(
			array(
				'type'  => 'request',
				'param' => 'test5',
			)
		);
		$this->assertEquals( '', $result );

		$set_value = '<script></script>test';
		$expected = 'test';
		$_REQUEST['test5'] = $set_value;

		$result = FrmAppHelper::get_simple_request(
			array(
				'type'  => 'request',
				'param' => 'test5',
			)
		);
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers FrmAppHelper::sanitize_request
	 */
	public function test_sanitize_request() {
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
	public function test_kses() {
		$start_value  = '<script><script>';
		$safe_value   = 'Hello, <a href="/test">click here</a>';
		$start_value .= $safe_value;

		$stripped_value = FrmAppHelper::kses( $start_value );
		$this->assertEquals( $stripped_value, 'Hello, click here' );

		$stripped_value = FrmAppHelper::kses( $start_value, array( 'a' ) );
		$this->assertEquals( $stripped_value, $safe_value );
	}

	/**
	 * @covers FrmAppHelper::kses_submit_button
	 */
	public function test_kses_submit_button() {
		$default_submit_button_html = '<div class="frm_submit">
[if back_button]<button type="submit" name="frm_prev_page" formnovalidate="formnovalidate" class="frm_prev_page" [back_hook]>[back_label]</button>[/if back_button]
<button class="frm_button_submit" type="submit" [button_action]>[button_label]</button>
[if save_draft]<a href="#" tabindex="0" class="frm_save_draft" [draft_hook]>[draft_label]</a>[/if save_draft]
</div>';
		$this->assertEquals(
			$default_submit_button_html,
			FrmAppHelper::kses_submit_button( $default_submit_button_html )
		);

		$with_custom_class = '<div class="frm_submit">
[if back_button]<button type="submit" name="frm_prev_page" formnovalidate="formnovalidate" class="frm_prev_page" [back_hook]>[back_label]</button>[/if back_button]
<button class="frm_button_submit frm_inline_submit" type="submit" [button_action]>[button_label]</button>
[if save_draft]<a href="#" tabindex="0" class="frm_save_draft" [draft_hook]>[draft_label]</a>[/if save_draft]
</div>';
		$this->assertEquals(
			$with_custom_class,
			FrmAppHelper::kses_submit_button( $with_custom_class )
		);

		$previous_default_html = '<div class="frm_submit">
[if back_button]<input type="button" value="[back_label]" name="frm_prev_page" formnovalidate="formnovalidate" class="frm_prev_page" [back_hook] />[/if back_button]
<input type="submit" value="[button_label]" [button_action] />
<img class="frm_ajax_loading" src="[frmurl]/images/ajax_loader.gif" alt="Sending" style="visibility:hidden" />
[if save_draft]<a class="frm_save_draft" [draft_hook]>[draft_label]</a>[/if save_draft]
</div>';
		$this->assertEquals(
			$previous_default_html,
			FrmAppHelper::kses_submit_button( $previous_default_html )
		);
	}

	/**
	 * @covers FrmAppHelper::kses_icon
	 */
	public function test_kses_icon() {
		$icon = '<svg class="frmsvg frm_zapier_icon frm_show_upgrade" style="--primary-hover:var(--purple)"><use xlink:href="#frm_zapier_icon" /></svg>';
		$this->assertEquals( $icon, FrmAppHelper::kses_icon( $icon ) );

		$icon = '<svg class="frmsvg frm_zapier_icon frm_show_upgrade" style="--primary-hover:rgb(0,160,210)"><use xlink:href="#frm_zapier_icon" /></svg>';
		$this->assertEquals( $icon, FrmAppHelper::kses_icon( $icon ) );

		$icon = '<svg class="frmsvg frm_zapier_icon frm_show_upgrade" style="--primary-hover:#efefef"><use xlink:href="#frm_zapier_icon" /></svg>';
		$this->assertEquals( $icon, FrmAppHelper::kses_icon( $icon ) );

		$icon = '<svg class="frmsvg frm_more_horiz_solid_icon frm-show-inline-modal" data-open="frm-layout-classes-box" title="Toggle Options"><use xlink:href="#frm_more_horiz_solid_icon" /></svg>';
		$this->assertEquals( $icon, FrmAppHelper::kses_icon( $icon ) );

		$icon = '<svg class="frmsvg" aria-label="WordPress" style="width:90px;height:90px"><use xlink:href="#frm_wordpress_icon" /></svg>';
		$this->assertEquals( $icon, FrmAppHelper::kses_icon( $icon ) );
	}

	/**
	 * @covers FrmAppHelper::is_a_valid_color
	 */
	public function test_is_a_valid_color() {
		$this->assertTrue( $this->is_a_valid_color( 'rgb(49, 119, 199)' ) );
		$this->assertTrue( $this->is_a_valid_color( 'rgba(49, 119, 199, .5)' ) );
		$this->assertTrue( $this->is_a_valid_color( '#fff' ) );
		$this->assertTrue( $this->is_a_valid_color( '#efefef' ) );

		$this->assertFalse( $this->is_a_valid_color( 'Not a color' ) );
	}

	private function is_a_valid_color( $value ) {
		return $this->run_private_method( array( 'FrmAppHelper', 'is_a_valid_color' ), array( $value ) );
	}

	/**
	 * @covers FrmAppHelper::remove_get_action
	 */
	public function test_remove_get_action() {
		$_GET['action']         = 'bulk_trash';
		$start_url              = admin_url( 'admin.php?page=formidable&action=bulk_trash' );
		$_SERVER['REQUEST_URI'] = $start_url;
		FrmAppHelper::remove_get_action();
		$new_url = FrmAppHelper::get_server_value( 'REQUEST_URI' );
		$this->assertNotEquals( $new_url, $start_url );
	}

	/**
	 * @covers FrmAppHelper::get_query_var
	 */
	public function test_get_query_var() {
		$new_post_id = $this->go_to_new_post();
		$get_post_id = FrmAppHelper::get_query_var( '', 'p' );
		$this->assertEquals( $new_post_id, $get_post_id );
	}

	/**
	 * @covers FrmAppHelper::allowed_html
	 */
	public function test_allowed_html() {
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
	public function test_maybe_add_permissions() {
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
	 * @covers FrmAppHelper::wp_roles_dropdown (single)
	 */
	public function test_wp_roles_dropdown() {
		ob_start();
		FrmAppHelper::wp_roles_dropdown( 'field_options', 'administrator' );
		$output = ob_get_contents();
		ob_end_clean();

		$this->assert_output_contains( $output, 'name="field_options"' );
		$this->assert_output_contains( $output, 'id="field_options"' );
		$this->assert_output_not_contains( $output . 'multiple="multiple"', 'default is single' );
		$this->assert_output_contains( $output, '>Administrator' );
	}

	/**
	 * @group visibility
	 * @covers FrmAppHelper::roles_options ($public = 'private')
	 */
	public function test_roles_options() {
		ob_start();
		FrmAppHelper::roles_options( 'editor' );
		$output = ob_get_contents();
		ob_end_clean();

		$this->assert_output_contains( $output, '>Administrator' );
		$this->assert_output_contains( $output, "selected='selected'>Editor" );
		$this->assert_output_contains( $output, '>Author' );
		$this->assert_output_contains( $output, '>Contributor' );
		$this->assert_output_contains( $output, '>Subscriber' );
	}

	/**
	 * @group visibility
	 * @covers FrmAppHelper::roles_options
	 */
	public function test_roles_options_empty_string_option() {
		ob_start();
		FrmAppHelper::roles_options( '' );
		$output = ob_get_contents();
		ob_end_clean();

		$this->assert_output_contains( $output, '>Editor' );
		$this->assert_output_not_contains( $output, "selected='selected'>Editor" );
	}

	/**
	 * @param string $substring
	 * @param string $message
	 */
	private function assert_output_contains( $output, $substring, $message = '' ) {
		$this->assertTrue( strpos( $output, $substring ) !== false, $message );
	}

	/**
	 * @param string $substring
	 * @param string $message
	 */
	private function assert_output_not_contains( $output, $substring, $message = '' ) {
		$this->assertTrue( strpos( $output, $substring ) === false, $message );
	}

	/**
	 * @covers FrmAppHelper::get_unique_key
	 */
	public function test_get_unique_key() {
		global $wpdb;

		// Test field keys
		$table_name = 'frm_fields';
		$column     = 'field_key';

		$name = 'lrk2p3994ed7b17086290a2b7c3ca5e65c944451f9c2d457602cae34661ec7f32998cc21b037a67695662e4b9fb7e177a5b28a6c0f';
		$key  = FrmAppHelper::get_unique_key( $name, $table_name, $column );
		$this->assertTrue( strlen( $key ) < 100, 'field key length should never be over 100' );

		$name = 'key';
		$key  = FrmAppHelper::get_unique_key( $name, $table_name, $column );
		$this->assertTrue( 'key' !== $key, 'key is a reserved key so get_unique_key should never return it.' );

		$name = 123;
		$key  = FrmAppHelper::get_unique_key( $name, $table_name, $column );
		$this->assertFalse( is_numeric( $key ), 'key should never be numeric.' );

		$super_long_form_key = 'formkeywithlikeseventycharacterscanyouevenimaginehavingthismanyletters';
		// reserve the form key so one has to be generated with this as the base.
		$this->factory->form->create(
			array( 'form_key' => $super_long_form_key )
		);

		$name    = 'examplefieldkey';
		$form_id = $this->factory->form->create();
		$this->add_field_to_form( $form_id, $name );
		$key = FrmAppHelper::get_unique_key( $name, $table_name, $column );
		$this->assertNotEquals( $name, $key, 'Field key should be unique' );
		$this->assertEquals( strlen( $name ) + 1, strlen( $key ), 'Field key should be the previous key + "2" incremented counter value' );
		$this->assertEquals( $name . 2, $key, 'Key value should increment' );

		$this->add_field_to_form( $form_id, $key );
		$key = FrmAppHelper::get_unique_key( $name, $table_name, $column );
		$this->assertEquals( $name . 3, $key, 'Key value should increment' );

		add_filter( 'frm_unique_field_key_separator', array( __CLASS__, 'underscore_key_separator' ) );

		$key = FrmAppHelper::get_unique_key( $name, $table_name, $column );
		$this->assertNotEquals( $name, $key, 'Field key should be unique' );
		$this->assertStringContainsString( '___', $key, 'Field key should contain custom separator' );
		$this->assertEquals( strlen( $name ) + 4, strlen( $key ), 'Field key should be the previous key + 3 character separator + "2" incremented counter value' );
		$this->assertEquals( $name . '___2', $key );

		remove_filter( 'frm_unique_field_key_separator', array( __CLASS__, 'underscore_key_separator' ) );

		// Test form keys
		$table_name = 'frm_forms';
		$column     = 'form_key';
		$unique_key = FrmAppHelper::get_unique_key( $super_long_form_key, $table_name, $column );
		$this->assertTrue( strlen( $unique_key ) <= 70 );
		$this->assertNotEquals( $super_long_form_key, $unique_key );
	}

	private function add_field_to_form( $form_id, $field_key ) {
		$type = 'text';
		$this->factory->field->create( compact( 'type', 'form_id', 'field_key' ) );
	}

	public static function underscore_key_separator() {
		return '___';
	}

	/**
	 * @covers FrmAppHelper::ctype_xdigit
	 */
	public function test_ctype_xdigit() {
		$this->assertTrue( FrmAppHelper::ctype_xdigit( 'fff' ) );
		$this->assertTrue( FrmAppHelper::ctype_xdigit( 'a1a1a1' ) );
		$this->assertTrue( FrmAppHelper::ctype_xdigit( 'FFF' ) );
		$this->assertFalse( FrmAppHelper::ctype_xdigit( 'fgf' ) );
		$this->assertFalse( FrmAppHelper::ctype_xdigit( 'z1z1z1' ) );
		$this->assertFalse( FrmAppHelper::ctype_xdigit( 'FGF' ) );
	}

	public function test_count_decimals() {
		$this->assertFalse( FrmAppHelper::count_decimals( 'str' ) );
		$this->assertFalse( FrmAppHelper::count_decimals( '1.0.0' ) );
		$this->assertEquals( 0, FrmAppHelper::count_decimals( 13 ) );
		$this->assertEquals( 0, FrmAppHelper::count_decimals( '13' ) );
		$this->assertEquals( 1, FrmAppHelper::count_decimals( 13.1 ) );
		$this->assertEquals( 1, FrmAppHelper::count_decimals( '13.1' ) );
		$this->assertEquals( 3, FrmAppHelper::count_decimals( 13.123 ) );
		$this->assertEquals( 3, FrmAppHelper::count_decimals( '13.123' ) );
	}
}
