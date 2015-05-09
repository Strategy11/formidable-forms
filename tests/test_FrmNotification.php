<?php

class WP_Test_FrmNotification extends FrmUnitTest {
    function setUp() {
		parent::setUp();
		$this->frm_install();
	}

	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	public function test_trigger_email(){
		// get the imported form with the email action
		$form = $this->get_one_form( 'contact-with-email' );

		// get the email settings
		$actions = FrmFormActionsHelper::get_action_for_form( $form->id, 'email' );
		$this->assertNotEmpty( $actions );

		$entry_data = array(
			'form_id'   => $form->id,
			'item_meta' => array(),
		);

		$form_fields = $this->get_fields( $form->id );
		foreach ( $form_fields as $field ) {
			$entry_data['item_meta'][ $field->id ] = $this->set_field_value( $field );
		}

		$entry_id = $this->create_entry( $entry_data );
		$entry = FrmEntry::getOne( $entry_id, true );
		$this->assertNotEmpty( $entry );

		foreach ( $actions as $action ) {
			FrmNotification::trigger_email( $action, $entry, $form );

			$this->assertEquals( 'address@tld.com', $GLOBALS['phpmailer']->mock_sent[0]['to'][0][0] );
			print_r($GLOBALS['phpmailer']->mock_sent[0]['header']);
			$this->assertNotEmpty( strpos( $GLOBALS['phpmailer']->mock_sent[0]['header'], 'Reply-To: test@test.com' ) );

			// TODO: check email body, cc, bcc, from
		}
	}
}