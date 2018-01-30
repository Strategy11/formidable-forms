<?php

/**
 * @group forms
 */
class WP_Test_FrmFormsController extends FrmUnitTest {

	function test_register_widgets() {
		global $wp_widget_factory;
		$this->assertTrue( isset( $wp_widget_factory->widgets['FrmShowForm'] ) );
	}

	function test_head() {
		$this->set_front_end();
		$edit_in_place = wp_script_is( 'formidable-editinplace', 'enqueued' );
		$this->assertFalse( $edit_in_place, 'The edit-in-place script should not be enqueued' );

		/*
		$this->set_admin_screen( 'formidable-edit' );

		$edit_in_place = wp_script_is( 'formidable-editinplace', 'enqueued' );
		$this->assertTrue( $edit_in_place, 'The edit-in-place script was not enqueued' );

		if ( wp_is_mobile() ) {
			$touchpunch = wp_script_is( 'jquery-touch-punch', 'enqueued' );
			$this->assertTrue( $touchpunch, 'The touch punch script was not enqueued' );
		}
		*/
	}

	function test_get_form_shortcode() {
		$form = FrmFormsController::get_form_shortcode( array( 'id' => $this->contact_form_key ) );
		$this->assertNotEmpty( strpos( $form, '<form ' ), 'The form is missing' );
	}

	/**
	* @covers FrmFormsController::update
	* without ajax
	*/
	function test_form_update_no_ajax() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->markTestSkipped( 'Run with --filter test_form_update_no_ajax' );
		}

		$form_id = $this->factory->form->get_id_by_key( $this->contact_form_key );
		$this->set_current_user_to_1();
		self::_setup_post_values( $form_id );
		self::_check_doing_ajax();

		ob_start();
		FrmFormsController::update();
		$html = ob_get_contents();
		ob_end_clean();

		self::_check_updated_values( $form_id );
	}

	function _setup_post_values( $form_id ){
		$fields = FrmField::get_all_for_form( $form_id );

		$form = FrmForm::getOne( $form_id );

		$_POST = array(
			'page' => 'formidable',
			'frm_action' => 'update',
			'id' => $form_id,
			'action' => 'update',
			'frm_save_form' => wp_create_nonce( 'frm_save_form_nonce' ),
			//'_wp_http_referer' =>
			'status' => 'published',
			'new_status' => '',
			'name' => $form->name,
			'frm_fields_submitted' => array(),
			'item_meta' => array(),
			'field_options' => array()
		);

		foreach ( $fields as $field ) {
			$_POST['frm_fields_submitted'][] = $field->id;
			$_POST['item_meta'][ $field->id ] = 'default';

			$field_options = array(
				'description_' . $field->id => '',
				'type_' . $field->id => '',
				'required_indicator_' . $field->id => '*',
				'field_key_' . $field->id => $field->field_key,
				'classes_' . $field->id => '',
				'label_' . $field->id => '',
				'size_' . $field->id => '',
				'max_' . $field->id => '',
				'admin_only_' . $field->id => '',
				'use_calc_' . $field->id => 1,
				'calc_' . $field->id => '',
				'calc_dec_' . $field->id => '',
				'show_hide_' . $field->id => 'show',
				'any_all_' . $field->id => 'any',
				'blank_' . $field->id => 'This field cannot be blank.',
				'unique_msg_' . $field->id => '',
			);

			$_POST['field_options'] = array_merge( $_POST['field_options'], $field_options );

			$_REQUEST = $_POST;
		}
	}

	// Make sure DOING_AJAX is false
	function _check_doing_ajax() {
		if ( defined( 'DOING_AJAX' ) ) {
			$doing_ajax = true;
		} else {
			$doing_ajax = false;
		}

		$this->assertFalse( $doing_ajax, 'DOING_AJAX must be false for this test to work. Maybe run this test individually to make sure DOING_AJAX is false.' );
	}

	function _check_updated_values( $form_id ) {
		$fields = FrmField::get_all_for_form( $form_id );

		// Compare to posted values
		foreach ( $fields as $field ) {
			// Check default value
			$posted_val = $_POST['item_meta'][ $field->id ];
			$actual_val = $field->default_value;
			$this->assertEquals( $posted_val, $actual_val, 'The default value was not updated correctly for field ' . $field->field_key . '.' );
		}
	}

	/**
	 * @covers FrmFormsController::front_head
	 */
	function test_front_head() {
		$this->assertTrue( FrmFormsController::has_combo_js_file(), 'The combo file was not created' );

		FrmFormsController::front_head();
		$this->assertTrue( wp_script_is( 'formidable', 'registered' ), 'The formidable js was not registered' );

		global $wp_version;
		if ( $wp_version <= 4.0 ) {
			$this->markTestSkipped('Min JS seems to fail in WP 4.0');
		}

		global $wp_scripts;
		$formidable_js = $wp_scripts->registered['formidable'];

		if ( FrmAppHelper::js_suffix() == '.min' ) {
			$this->assertEquals( FrmAppHelper::plugin_url() . '/js/frm.min.js', $formidable_js->src, 'frm.min.js was not loaded' );
		} else {
			$this->assertEquals( FrmAppHelper::plugin_url() . '/js/formidable.js', $formidable_js->src, 'formidable.js was not loaded' );
		}
	}

	/**
	 * Test redirect after create
	 * @group testme
	 */
	function test_redirect_after_submit() {
		$form = $this->factory->form->create_and_get( array(
			'options' => array(
				'success_action' => 'redirect',
				'success_url'    => 'http://example.com',
			),
		) );
		$this->assertEquals( $form->options['success_action'], 'redirect' );

		add_filter( 'wp_redirect', array( $this, 'check_redirect' ) );

		$_POST = $this->factory->field->generate_entry_array( $form );
		$entry_key = 'redirect-after-submit';
		$_POST['item_key'] = $entry_key;
		$_POST['action'] = 'create';

		// since headers are sent, we will get the js redirect
		ob_start();
		FrmEntriesController::process_entry();
		$response = ob_get_contents();
		ob_end_clean();
		$this->assertContains( "window.location='http://example.com'", $response );

		$created_entry = FrmEntry::get_id_by_key( $entry_key );
		$this->assertNotEmpty( $created_entry );

		$response = FrmFormsController::show_form( $form->id ); // this is where the redirect happens
		$this->assertContains( "window.location='http://example.com'", $response );
	}
}
