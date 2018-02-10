<?php

/**
 * @group forms
 */
class WP_Test_FrmForm extends FrmUnitTest {
	/**
	 * @covers FrmForm::create
	 */
	function test_create() {
		$values = FrmFormsHelper::setup_new_vars( false );
		$form_id = FrmForm::create( $values );
		$this->assertTrue( is_numeric( $form_id ) );
		$this->assertNotEmpty( $form_id );
	}

	/**
	 * @covers FrmForm::duplicate
	 */
	function test_duplicate(){
		$form = $this->factory->form->get_object_by_id( $this->all_fields_form_key );

		$id = FrmForm::duplicate( $form->id );
		$this->assertTrue( is_numeric( $id ) );
		$this->assertNotEmpty( $id );

		// check the number of form actions
		$original_actions = FrmFormAction::get_action_for_form( $form->id );
		$new_actions = FrmFormAction::get_action_for_form( $id );
		$this->assertEquals( count( $original_actions ), count( $new_actions ) );
	}

	function _check_if_child_fields_duplicate( $old_child_forms, $new_child_forms ) {
		// Just check the first form
		$old_child_form = reset( $old_child_forms );
		$new_child_form = reset( $new_child_forms );

		// Get all fields in each form
		$old_child_form_fields = FrmField::get_all_for_form( $old_child_form->id );
		$new_child_form_fields = FrmField::get_all_for_form( $new_child_form->id );

		// Check if there are the same number of child form fields in the duplicated child form
		$this->assertEquals( count( $old_child_form_fields ), count( $new_child_form_fields ), 'When a form is duplicated, the fields in the repeating section are not duplicated correctly.' );
	}

	/**
	 * @covers FrmForm::destroy
	 */
	function test_destroy(){
		$forms = FrmForm::getAll();
		$this->assertNotEmpty( count( $forms ) );

		foreach ( $forms as $form ) {
			if ( $form->is_template ) {
				continue;
			}

			$id = FrmForm::destroy( $form->id );
			$form_exists = FrmForm::getOne( $form->id );
			$this->assertEmpty( $form_exists, 'Failed to delete form ' . $form->form_key );

			$subforms_exist = FrmForm::getAll( array( 'parent_form_id' => $form->id ) );
			$this->assertEmpty( $subforms_exist, 'Failed to delete child forms for parent form ' . $form->form_key );
		}
	}
}
