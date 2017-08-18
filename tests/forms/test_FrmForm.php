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

		if ( $this->is_pro_active ) {
			// For repeating sections
			self::_check_if_child_forms_duplicate( $form->id, $id );
			self::_check_if_form_select_updates( $form->id, $id );
		}
	}

	function _check_if_child_forms_duplicate( $old_form_id, $new_form_id ) {
		// Check if there are any child forms in the original form
		$old_child_forms = FrmForm::getAll( array( 'parent_form_id' => $old_form_id ) );
		if ( ! $old_child_forms ) {
			return;
		}

		// Check if there are any child forms in the new form
		$new_child_forms = FrmForm::getAll( array( 'parent_form_id' => $new_form_id ) );

		// Check if there are the same number of child forms in the duplicated form
		$this->assertEquals( count( $old_child_forms ), count( $new_child_forms ), 'When a form is duplicated, the child forms are not duplicated correctly.' );

		self::_check_if_child_fields_duplicate( $old_child_forms, $new_child_forms );
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

	function _check_if_form_select_updates( $old_form_id, $new_form_id ) {
		// Get all repeating sections in both forms
		$old_repeating_sections = array_values( FrmField::get_all_types_in_form( $old_form_id, 'divider' ) );
		$new_repeating_sections = array_values( FrmField::get_all_types_in_form( $new_form_id, 'divider' ) );

		if ( ! $old_repeating_sections ) {
			return;
		}

		foreach ( $old_repeating_sections as $key => $section ) {
			if ( ! FrmField::is_repeating_field( $section ) ) {
				continue;
			}

			$old_form_select = $section->field_options['form_select'];
			$new_form_select = $new_repeating_sections[ $key ]->field_options['form_select'];

			$this->assertNotEquals( $old_form_select, $new_form_select, 'A form was duplicated, but the form_select was not updated for the repeating section :/');
		}
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
