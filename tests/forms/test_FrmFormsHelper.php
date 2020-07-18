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
		$this->assert_form_is_visible( 'editor', 'loggedin', 'Editor can view form set to logged in users' );
		$this->assert_form_is_visible( 'editor', 'everybody', 'Editor can view form set to everybody' );
		$this->assert_form_is_visible( 'administrator', 'everybody', 'Administrator can view form set to everybody' );
		$this->assert_form_is_visible( 'subscriber', 'everybody', 'Subscriber can view form set to everybody' );
		$this->assert_form_is_visible( 'subscriber', 'loggedin', 'Subscriber can view form set to logged in users' );
	}

	/**
	 * @param string capability
	 * @param string|array visibility
	 * @return bool
	 */
	private function form_is_visible( $capability, $visibility ) {
		$this->set_user_by_role( $capability );
		$form = FrmForm::getOne( 'contact-db12' );
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
