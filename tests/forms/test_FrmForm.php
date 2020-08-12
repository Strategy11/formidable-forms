<?php

/**
 * @group forms
 */
class test_FrmForm extends FrmUnitTest {
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

	/**
	 * @group visibility
	 * @covers FrmFormsHelper::is_form_visible_to_user
	 */
	public function test_is_form_visible_to_user() {
		$this->assert_form_is_visible( 'administrator', 'editor', 'Administrator can view a form set to editor' );
		$this->assert_form_is_hidden( 'editor', 'administrator', 'Editor cannot view form set to administrator' );
		$this->assert_form_is_visible( 'editor', array( 'administrator', 'editor' ), 'Editor can view form set to both administrator and editor' );

		// The Logged-In Users option is actually an empty string
		$this->assert_form_is_visible( 'editor', '', 'Editor can view form set to logged in users' );
		$this->assert_form_is_visible( 'subscriber', '', 'Subscriber can view form set to logged in users' );
		$this->assert_form_is_visible( 'subscriber', '', 'Subscriber can view form set to logged in users' );
		$this->assert_form_is_hidden( 'loggedout', '', 'Logged out user cannot view form set to logged in users' );

		$this->assert_form_is_hidden( 'loggedout', 'editor', 'Logged out user cannot view form set to editors' );

		// Array options are expected to only match directly
		$this->assert_form_is_hidden( 'editor', array( 'subscriber' ), 'Editors should not set a form assigned to subscribers' );
		$this->assert_form_is_hidden( 'editor', array( 'contributor', 'author' ), 'Editors should not set a form assigned to contributors and authors' );
		$this->assert_form_is_hidden( 'subscriber', array( 'editor', 'author' ), 'Contributors should not set a form assigned to editors and authors' );
		$this->assert_form_is_hidden( 'subscriber', array( 'author', 'administrator' ), 'Contributors should not set a form assigned to authors and administrators' );

		// test custom roles
		$this->assert_form_is_visible( 'formidable_custom_role', 'formidable_custom_role', 'Custom role should be able to see a form assigned to it' );
		$this->assert_form_is_visible( 'formidable_custom_role', '', 'Custom role should be able to see a form assigned to logged in users' );
		$this->assert_form_is_hidden( 'formidable_custom_role', array( 'administrator' ), 'Custom role should not be able to see a form not assigned to it' );
		$this->assert_form_is_hidden( 'formidable_custom_role', array( 'editor', 'subscriber' ), 'Custom role should not be able to see a form not assigned to it' );
	}

	/**
	 * @param string capability
	 * @param string|array visibility
	 * @return bool
	 */
	private function form_is_visible( $capability, $visibility ) {
		$form = FrmForm::getOne( 'contact-db12' );

		$this->use_frm_role( $capability );

		$form->logged_in = 1;
		$form->options['logged_in_role'] = $visibility;
		return FrmForm::is_visible_to_user( $form );
	}

	/**
	 * @param string capability
	 * @param string|array visibility
	 * @param string $message
	 */
	private function assert_form_is_visible( $capability, $visibility, $message = '' ) {
		$this->assertTrue( $this->form_is_visible( $capability, $visibility ), $message );
	}

	/**
	 * @param string capability
	 * @param string|array visibility
	 * @param string $message
	 */
	private function assert_form_is_hidden( $capability, $visibility, $message = '' ) {
		$this->assertFalse( $this->form_is_visible( $capability, $visibility ), $message );
	}
}
