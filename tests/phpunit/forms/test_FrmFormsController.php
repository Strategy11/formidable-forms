<?php

/**
 * @group forms
 * @group forms-controller
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

		// Allow for Pro hooks to load.
		FrmHooksController::trigger_load_form_hooks();

		$form_id = $this->factory->form->get_id_by_key( $this->contact_form_key );
		$this->set_current_user_to_1();
		self::_setup_post_values( $form_id );
		self::_check_doing_ajax();

		ob_start();
		FrmFormsController::update();
		ob_end_clean();

		self::_check_updated_values( $form_id );
	}

	private function _setup_post_values( $form_id ) {
		$fields = FrmField::get_all_for_form( $form_id );
		$form   = FrmForm::getOne( $form_id );

		$_POST = array(
			'page'                 => 'formidable',
			'frm_action'           => 'update',
			'id'                   => $form_id,
			'action'               => 'update',
			'frm_save_form'        => wp_create_nonce( 'frm_save_form_nonce' ),
			'status'               => 'published',
			'new_status'           => '',
			'name'                 => $form->name,
			'frm_fields_submitted' => array(),
			'item_meta'            => array(),
			'field_options'        => array(),
		);

		foreach ( $fields as $field ) {
			$_POST['frm_fields_submitted'][]        = $field->id;
			$_POST[ 'default_value_' . $field->id ] = 'default';

			$field_options = array(
				'description_' . $field->id        => '',
				'type_' . $field->id               => '',
				'required_indicator_' . $field->id => '*',
				'field_key_' . $field->id          => $field->field_key,
				'classes_' . $field->id            => '',
				'label_' . $field->id              => '',
				'size_' . $field->id               => '',
				'max_' . $field->id                => '',
				'admin_only_' . $field->id         => '',
				'use_calc_' . $field->id           => 1,
				'calc_' . $field->id               => '',
				'calc_dec_' . $field->id           => '',
				'show_hide_' . $field->id          => 'show',
				'any_all_' . $field->id            => 'any',
				'blank_' . $field->id              => 'This field cannot be blank.',
				'unique_msg_' . $field->id         => '',
			);

			$_POST['field_options'] = array_merge( $_POST['field_options'], $field_options );
			$_REQUEST               = $_POST;
		}
	}

	/**
	 * Make sure DOING_AJAX is false.
	 */
	private function _check_doing_ajax() {
		$doing_ajax = defined( 'DOING_AJAX' );
		$this->assertFalse( $doing_ajax, 'DOING_AJAX must be false for this test to work. Maybe run this test individually to make sure DOING_AJAX is false.' );
	}

	private function _check_updated_values( $form_id ) {
		$fields = FrmField::get_all_for_form( $form_id );

		// Compare to posted values
		foreach ( $fields as $field ) {
			if ( FrmField::is_no_save_field( $field->type ) ) {
				continue;
			}

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

		if ( FrmAppHelper::js_suffix() === '.min' ) {
			$file = 'frm.min.js';

			if ( ! str_contains( $formidable_js->src, $file ) ) {
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
		$this->trigger_migrate_actions( $form_id );
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
		$contains = array(
			'frmFrontForm.scrollMsg(' . $form->id . ')',
			'Done!',
			'window.location="http://example.com"',
			'Test page content',
		);

		foreach ( $contains as $c ) {
			$this->assertStringContainsString( $c, $response );
		}
	}

	/**
	 * Test redirect after create
	 *
	 * @covers FrmFormsController::redirect_after_submit
	 */
	public function test_redirect_after_create() {
		$form_id  = $this->factory->form->create();
		$field_id = FrmDb::get_var( 'frm_fields', array( 'form_id' => $form_id ) );

		$this->create_on_submit_action(
			$form_id,
			array(
				'event'          => array( 'create' ),
				'success_action' => 'redirect',
				'success_url'    => 'http://example.com?param=[' . $field_id . ']',
			)
		);

		wp_cache_delete( $form_id, 'frm_form' );
		$this->trigger_migrate_actions( $form_id );

		$form             = $this->factory->form->get_object_by_id( $form_id );
		$entry_key        = 'submit-redirect';
		$response         = $this->post_new_entry( $form, $entry_key );
		$created_entry_id = FrmEntry::get_id_by_key( $entry_key );
		$this->assertNotEmpty( $created_entry_id, 'No entry found with key ' . $entry_key );

		$entry        = FrmEntry::getOne( $created_entry_id, true );
		$expected_url = 'http://example.com?param=' . $entry->metas[ $field_id ];

		$this->assertTrue( headers_sent() );

		// Since headers are sent by phpunit, we will get the js redirect.
		$this->assertStringContainsString( 'window.location="' . $expected_url . '"', $response );
	}

	/**
	 * Trigger migration check and the flag.
	 *
	 * @param int|string $form_id
	 *
	 * @return void
	 */
	private function trigger_migrate_actions( $form_id ) {
		FrmOnSubmitHelper::maybe_migrate_submit_settings_to_action( $form_id );
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
		$response  = $this->post_new_entry( $form, $entry_key );

		$this->assertEmpty( $response );

		$created_entry = FrmEntry::get_id_by_key( $entry_key );
		$this->assertNotEmpty( $created_entry, 'No entry found with key ' . $entry_key );

		$response = FrmFormsController::show_form( $form->id ); // this is where the message is returned
		$this->assertStringContainsString( '<div class="frm_message" role="status">Done!</div>', $response );
		$this->assertStringContainsString( 'frmFrontForm.scrollMsg(' . $form->id . ')', $response );

		if ( $show_form ) {
			$this->assertStringContainsString( '<input type="hidden" name="form_id" value="' . $form->id . '" />', $response );
		} else {
			$this->assertStringNotContainsString( '<input type="hidden" name="form_id" value="' . $form->id . '" />', $response );
		}
	}

	private function post_new_entry( $form, $entry_key ) {
		$fields       = FrmField::get_all_for_form( $form->id, '', 'include' );
		$class        = class_exists( 'FrmProFormState' ) ? 'FrmProFormState' : 'FrmFormState';
		$max_field_id = 0;

		foreach ( $fields as $field ) {
			$max_field_id = max( (int) $field->id, $max_field_id );
		}
		$class::set_initial_value( 'honeypot_field_id', $max_field_id + 1 );

		$_POST               = $this->factory->field->generate_entry_array( $form );
		$_POST['item_key']   = $entry_key;
		$_POST['frm_action'] = 'create';
		$_POST['action']     = 'create';

		ob_start();
		FrmEntriesController::process_entry();
		return ob_get_clean();
	}

	public function test_redirect_in_new_tab() {
		$form_id = $this->factory->form->create();

		$this->create_on_submit_action(
			$form_id,
			array(
				'event'           => array( 'create' ),
				'success_action'  => 'redirect',
				'success_url'     => 'http://example.com',
				'open_in_new_tab' => 1,
			)
		);

		wp_cache_delete( $form_id, 'frm_form' );
		$this->trigger_migrate_actions( $form_id );
		$form      = $this->factory->form->get_object_by_id( $form_id );
		$entry_key = 'submit-redirect';
		$response  = $this->post_new_entry( $form, $entry_key );

		$this->assertTrue( headers_sent() );

		// Since headers are sent by phpunit, we will get the js redirect.
		$this->assertStringContainsString( 'window.open("http://example.com"', $response );
		$this->assertStringContainsString( 'target="_blank">Click here</a>', $response );
	}
}
