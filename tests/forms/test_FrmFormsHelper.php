<?php

/**
 * @group forms
 */
class test_FrmFormsHelper extends FrmUnitTest {
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
		$this->assert_form_is_hidden( 'administrator', array( 'subscriber' ), 'Adminstrator should not be able to see a form without an exact array match' );
		$this->assert_form_is_hidden( 'administrator', array( 'editor', 'author' ), 'Adminstrator should not be able to see a form without an exact array match' );
		
		// test custom roles
		$this->assert_form_is_visible( 'formidable_custom_role', 'formidable_custom_role', 'Custom role should be able to see a form assigned to it' );
		$this->assert_form_is_visible( 'formidable_custom_role', '', 'Custom role should be able to see a form assigned to logged in users' );
		$this->assert_form_is_hidden( 'formidable_custom_role', 'editor', 'Custom role should not be able to see a form not assigned to it' );
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
		return FrmFormsHelper::is_form_visible_to_user( $form );
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
