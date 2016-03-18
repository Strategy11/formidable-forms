<?php

/**
 * @group ajax
 */
class WP_Test_FrmFieldsAjax extends FrmAjaxUnitTest {
	
	public function setUp() {
		parent::setUp();

		// Set a user so the $post has 'post_author'
		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );

		$form = $this->factory->form->create_and_get();
		$this->assertNotEmpty( $form );
		$this->form_id = $form->id;
	}

	public function _test_load_field() {
		
	}

	/**
	 * @covers FrmFieldsController::create
	 */
    public function test_create() {
        wp_set_current_user( $this->user_id );
        $this->assertTrue(is_numeric($this->form_id));

		$_POST = array(
			'action'    => 'frm_insert_field',
            'nonce'     => wp_create_nonce('frm_ajax'),
			'form_id'   => $this->form_id,
            'field_type'     => 'text', //create text field
		);

		try {
			$this->_handleAjax( 'frm_insert_field' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

        global $wpdb;
        $this->field_id = $wpdb->insert_id;

        $this->assertTrue( is_numeric( $this->field_id ) );
        $this->assertNotEmpty( $this->field_id );

        // make sure the field exists
		$field = FrmField::getOne( $this->field_id );
        $this->assertTrue( is_object( $field ) );
    }

	/**
	 * @covers FrmProFieldsController::update_field_after_move
	 */
	function test_update_field_after_move() {
		$action = 'frm_update_field_after_move';
		$repeating_field =  $this->factory->field->get_object_by_id( 'repeating-section' );
		$old_form_id = $repeating_field->form_id;
		$new_form_id = $repeating_field->field_options['form_select'];
		$field = $this->factory->field->create_and_get( array( 'form_id' => $old_form_id ) );

		$_POST = array(
			'action'  => $action,
			'field'   => $field->id,
			'form_id' => $new_form_id,
			'nonce'   => wp_create_nonce( 'frm_ajax' ),
		);

		try {
			$this->_handleAjax( 'frm_update_field_after_move' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$updated_field =  $this->factory->field->get_object_by_id( $field->id );
		$this->assertEquals( $new_form_id, $updated_field->form_id );
	}

	/**
	 * @covers FrmFieldsController::edit_name
	 */
	function test_edit_name() {
		$form = $this->factory->form->get_object_by_id( 'contact-with-email' );
		$field = $this->factory->field->create_and_get( array('form_id' => $form->id ) );
        $this->assertNotEmpty( $field );

		$new_name = 'New Field Name';
		$_POST = array(
			'action'        => 'frm_field_name_in_place_edit',
            'element_id'    => $field->id,
            'update_value'  => $new_name,
			'nonce'         => wp_create_nonce( 'frm_ajax' ),
		);

		try {
			$this->_handleAjax( 'frm_field_name_in_place_edit' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = $this->_last_response;
		$this->assertEquals( $response, $new_name );

		// Check that the edit happened
		$field = $this->factory->field->get_object_by_id( $field->id );

        $this->assertTrue( is_object( $field ), 'Failed to get field ' . $field->id );
		$this->assertEquals( $field->name, $new_name );
	}
}