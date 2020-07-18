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
		$this->assert_form_is_visible( 'subscriber', 'loggedin', 'Subscriber can view form set to logged in users' );
		$this->assert_form_is_visible( 'subscriber', 'loggedin', 'Subscriber can view form set to logged in users' );
		$this->assert_form_is_hidden( 'loggedout', 'loggedin', 'Logged out user cannot view form set to logged in users' );
		$this->assert_form_is_hidden( 'loggedout', 'loggedin', 'Logged out user cannot view form set to editors' );
	}

	/**
	 * @param string capability
	 * @param string|array visibility
	 * @return bool
	 */
	private function form_is_visible( $capability, $visibility ) {
		$form = FrmForm::getOne( 'contact-db12' );

		if ( $capability === 'loggedout' ) {
			wp_set_current_user( null );
		} else {
			$this->set_user_by_role( $capability );
		}

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
