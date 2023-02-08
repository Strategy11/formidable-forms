<?php

/**
 * @group forms
 */
class test_FrmFormsController extends FrmUnitTest {

	public function test_register_widgets() {
		global $wp_widget_factory;
		$this->assertTrue( isset( $wp_widget_factory->widgets['FrmShowForm'] ) );
	}

	public function test_head() {
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

	public function test_get_form_shortcode() {
		$form = FrmFormsController::get_form_shortcode( array( 'id' => $this->contact_form_key ) );
		$this->assertNotEmpty( strpos( $form, '<form ' ), 'The form is missing' );
	}

	/**
	* @covers FrmFormsController::update
	* without ajax
	*/
	public function test_form_update_no_ajax() {
		if ( FrmAppHelper::doing_ajax() ) {
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

	private function _setup_post_values( $form_id ) {
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
			'field_options' => array(),
		);

		foreach ( $fields as $field ) {
			$_POST['frm_fields_submitted'][] = $field->id;
			$_POST[ 'default_value_' . $field->id ] = 'default';

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
	private function _check_doing_ajax() {
		if ( defined( 'DOING_AJAX' ) ) {
			$doing_ajax = true;
		} else {
			$doing_ajax = false;
		}

		$this->assertFalse( $doing_ajax, 'DOING_AJAX must be false for this test to work. Maybe run this test individually to make sure DOING_AJAX is false.' );
	}

	private function _check_updated_values( $form_id ) {
		$fields = FrmField::get_all_for_form( $form_id );

		// Compare to posted values
		foreach ( $fields as $field ) {
			// Check default value
			$posted_val = $_POST[ 'default_value_' . $field->id ];
			$actual_val = $field->default_value;
			$this->assertEquals( $posted_val, $actual_val, 'The default value was not updated correctly for field ' . $field->field_key . '.' );
		}
	}

	/**
	 * @covers FrmFormsController::front_head
	 */
	public function test_front_head() {
		$this->assertTrue( FrmFormsController::has_combo_js_file(), 'The combo file was not created' );

		FrmFormsController::front_head();
		$this->assertTrue( wp_script_is( 'formidable', 'registered' ), 'The formidable js was not registered' );

		global $wp_scripts;
		$formidable_js = $wp_scripts->registered['formidable'];

		if ( FrmAppHelper::js_suffix() == '.min' ) {
			$file = 'frm.min.js';
			if ( strpos( $formidable_js->src, $file ) === false ) {
				$file = 'formidable.min.js';
			}
		} else {
			$file = 'formidable.js';
		}
		$this->assertEquals( FrmAppHelper::plugin_url() . '/js/' . $file, $formidable_js->src, $file . ' was not loaded' );
	}

	private function create_on_submit_action( $form_id, $post_content ) {
		$post_data = array(
			'post_type'    => FrmFormActionsController::$action_post_type,
			'menu_order'   => $form_id,
			'post_excerpt' => FrmOnSubmitAction::$slug,
			'post_status'  => 'publish',
			'post_content' => FrmAppHelper::prepare_and_encode( $post_content ),
		);

		return $this->factory->post->create_and_get( $post_data );
	}

	public function test_multiple_on_submit_actions() {
		$test_page_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => 'Test page content',
			)
		);

		$form_id = $this->factory->form->create();

		$message_action = $this->create_on_submit_action(
			$form_id,
			array(
				'event'          => array( 'create' ),
				'success_action' => 'message',
				'success_msg'    => 'Done!',
			)
		);

		$page_action = $this->create_on_submit_action(
			$form_id,
			array(
				'event'           => array( 'create', 'update' ),
				'success_action'  => 'page',
				'success_page_id' => $test_page_id,
			)
		);

		$redirect_action_1 = $this->create_on_submit_action(
			$form_id,
			array(
				'event'          => array( 'create' ),
				'success_action' => 'redirect',
				'success_url'    => 'http://example.com',
			)
		);

		$redirect_action_2 = $this->create_on_submit_action(
			$form_id,
			array(
				'event'          => array( 'create', 'update' ),
				'success_action' => 'redirect',
				'success_url'    => 'https://abc2.test',
			)
		);

		// Update form object from cache.
		wp_cache_delete( $form_id, 'frm_form' );
		$form = FrmForm::getOne( $form_id );

		// Create entry.
		$entry_key = 'submit-actions';
		$response  = $this->post_new_entry( $form, $entry_key );

		$this->assertEmpty( $response );

		$entry_id = FrmEntry::get_id_by_key( $entry_key );
		$this->assertNotEmpty( $entry_id, 'No entry found with key ' . $entry_key );

		// Test get_met_on_submit_actions.
		$actions = FrmFormsController::get_met_on_submit_actions( compact( 'form', 'entry_id' ) );
		$this->assertEquals( wp_list_pluck( $actions, 'ID' ), array( $message_action->ID, $page_action->ID, $redirect_action_1->ID ) );

		$actions = FrmFormsController::get_met_on_submit_actions( compact( 'form', 'entry_id' ), 'update' );
		$this->assertEquals( wp_list_pluck( $actions, 'ID' ), array( $page_action->ID, $redirect_action_2->ID ) );

		// Test the output.
		$response = FrmFormsController::show_form( $form->id ); // this is where the message is returned
		$this->assertNotFalse( strpos( $response, '<div class="frm_message" role="status"><p>Done!</p>' ) );
		$this->assertNotFalse( strpos( $response, 'frmFrontForm.scrollMsg(' . $form->id . ')' ) );

		$this->assertNotFalse( strpos( $response, 'window.location="http://example.com"' ) );

		$this->assertNotFalse( strpos( $response, 'Test page content' ) );
	}

	/**
	 * Test redirect after create
	 *
	 * @covers FrmFormsController::redirect_after_submit
	 */
	public function test_redirect_after_create() {
		$form_id = $this->factory->form->create();

		$this->create_on_submit_action(
			$form_id,
			array(
				'event'          => array( 'create' ),
				'success_action' => 'redirect',
				'success_url'    => 'http://example.com',
			)
		);

		wp_cache_delete( $form_id, 'frm_form' );

		$form = $this->factory->form->get_object_by_id( $form_id );

		$entry_key = 'submit-redirect';
		$response = $this->post_new_entry( $form, $entry_key );

		if ( headers_sent() ) {
			// since headers are sent by phpunit, we will get the js redirect
			$this->assertNotFalse( strpos( $response, 'window.location="http://example.com"' ) );
		}

		$created_entry = FrmEntry::get_id_by_key( $entry_key );
		$this->assertNotEmpty( $created_entry, 'No entry found with key ' . $entry_key );

		$response = FrmFormsController::show_form( $form->id ); // this is where the redirect happens
		$this->assertNotFalse( strpos( $response, 'window.location="http://example.com"' ) );
	}

	/**
	 * @covers FrmFormsController::show_message_after_save
	 */
	public function test_message_after_create() {
		$this->run_message_after_create( 0 );
	}

	/**
	 * @covers FrmFormsController::show_message_after_save
	 */
	public function test_message_with_form_after_create() {
		$this->run_message_after_create( 1 );
	}

	public function run_message_after_create( $show_form = 0 ) {
		$form = $this->factory->form->create_and_get(
			array(
				'options' => array(
					'success_action' => 'message',
					'success_msg'    => 'Done!',
					'show_form'      => $show_form,
				),
			)
		);

		// Test default action.
		$this->assertEquals( $form->options['success_action'], 'message' );

		$this->create_on_submit_action(
			$form->id,
			array(
				'event'          => array( 'create' ),
				'success_action' => 'message',
				'success_msg'    => 'Done!',
				'show_form'      => $show_form,
			)
		);

		// Update $form object after action is created.
		wp_cache_delete( $form->id, 'frm_form' );

		$entry_key = 'submit-message';
		$response = $this->post_new_entry( $form, $entry_key );

		$this->assertEmpty( $response );

		$created_entry = FrmEntry::get_id_by_key( $entry_key );
		$this->assertNotEmpty( $created_entry, 'No entry found with key ' . $entry_key );

		$response = FrmFormsController::show_form( $form->id ); // this is where the message is returned
		$this->assertNotFalse( strpos( $response, '<div class="frm_message" role="status"><p>Done!</p>' ) );
		$this->assertNotFalse( strpos( $response, 'frmFrontForm.scrollMsg(' . $form->id . ')' ) );

		if ( $show_form ) {
			$this->assertNotFalse( strpos( $response, '<input type="hidden" name="form_id" value="' . $form->id . '" />' ) );
		} else {
			$this->assertFalse( strpos( $response, '<input type="hidden" name="form_id" value="' . $form->id . '" />' ) );
		}
	}

	private function post_new_entry( $form, $entry_key ) {
		$_POST = $this->factory->field->generate_entry_array( $form );
		$_POST['item_key'] = $entry_key;
		$_POST['frm_action'] = 'create';
		$_POST['action'] = 'create';

		ob_start();
		FrmEntriesController::process_entry();
		$response = ob_get_contents();
		ob_end_clean();

		return $response;
	}
}
