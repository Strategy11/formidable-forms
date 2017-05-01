<?php

/**
 * @group forms
 */
class WP_Test_FrmProFormsHelper extends FrmUnitTest {

	/**
	 * @covers FrmProFormsHelper::user_can_submit_form
	 */
	function test_user_can_submit_form() {
		$this->form = $this->factory->form->create_and_get();
		$this->assertNotEmpty( $this->form, 'The form was not created or fetched' );

		$this->form->options['single_entry'] = 1;
		$this->form->options['single_entry_type'] = 'user';
		$this->assertTrue( FrmProFormsHelper::user_can_submit_form( $this->form ), 'The user should be able to submit an entry' );

		$this->form->options['single_entry_type'] = 'ip';
		$this->assertTrue( FrmProFormsHelper::user_can_submit_form( $this->form ), 'This IP should be able to submit an entry' );

		$this->form->options['single_entry_type'] = 'cookie';
		$this->assertTrue( FrmProFormsHelper::user_can_submit_form( $this->form ), 'There is no cookie, so they should be able to submit an entry' );

		$entry_data = $this->factory->field->generate_entry_array( $this->form );
		$entry = $this->factory->entry->create_and_get( $entry_data );

		self::allow_submit_with_user_id();
		self::allow_submit_with_ip();
		//self::allow_submit_with_cookie( $entry->id ); Uncomment this after testing cookie creation
		self::allow_save_draft_entry();
	}

	function allow_submit_with_user_id() {
		$this->set_user_by_role( 'subscriber' );

		$form = $this->form;
		$form->options['single_entry_type'] = 'user';
		$this->assertTrue( FrmProFormsHelper::user_can_submit_form( $form ), 'This user should be able to submit a form' );

		// User creates entry
		$entry_data = $this->factory->field->generate_entry_array( $this->form );
		$entry = $this->factory->entry->create_and_get( $entry_data );

		// Now user should not be able to submit an entry
		$this->assertFalse( FrmProFormsHelper::user_can_submit_form( $form ), 'This user already submitted a form' );

		$form->editable = 1;
		$this->assertTrue( FrmProFormsHelper::user_can_submit_form( $form ), 'This user should be able to submit an editable entry' );
	}

	function allow_submit_with_ip() {
		$form = $this->form;
		$form->options['single_entry_type'] = 'ip';
		$this->assertFalse( FrmProFormsHelper::user_can_submit_form( $form ), 'This IP already submitted an entry' );
	}

	function allow_submit_with_cookie( $entry_id ) {
		$form = $this->form;
		FrmProEntriesController::set_cookie( $entry_id, $form->id );
		$this->assertTrue( isset( $_COOKIE[ 'frm_form' . $form->id . '_' . COOKIEHASH ] ), 'The cookie was not created' );
		$this->assertFalse( FrmProFormsHelper::user_can_submit_form( $form ) );
	}

	function allow_save_draft_entry() {
		$form = $this->form;
		$form->options['single_entry_type'] = 'user';
		$form->options['save_draft'] = 1;
		$form->editable = 0;
		$this->assertFalse( FrmProFormsHelper::user_can_submit_form( $form ) );
	}
}