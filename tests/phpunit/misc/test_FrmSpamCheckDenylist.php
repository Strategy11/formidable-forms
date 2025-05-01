<?php

class test_FrmSpamCheckDenylist extends FrmUnitTest {

	private $form_id;

	private $text_field_id;

	private $email_field_id;

	private $email_field_id2;

	private $name_field_id;

	private $spam_check;

	private $custom_denylist_data;

	private $default_values;

	public function setUp(): void {
		parent::setUp();

		$this->form_id = $this->factory->form->create(
			array(
				'form_key' => 'test_form_spam',
			)
		);

		$fields              = FrmField::getAll( array( 'form_id' => $this->form_id ) );
		$this->text_field_id = $fields[0]->id;

		$this->email_field_id = $this->factory->field->create(
			array(
				'type'    => 'email',
				'form_id' => $this->form_id,
			)
		);

		$this->name_field_id = $this->factory->field->create(
			array(
				'type'    => 'name',
				'form_id' => $this->form_id,
			)
		);

		$this->email_field_id2 = $this->factory->field->create(
			array(
				'type'    => 'email',
				'form_id' => $this->form_id,
			)
		);

		$this->default_values = array(
			'form_id'   => $this->form_id,
			'item_meta' => array(
				$this->email_field_id  => 'test@gmail.com',
				$this->text_field_id   => 'this text contains test@domain.com',
				$this->name_field_id   => array(
					'first' => 'WordPress',
					'last'  => 'Plugin',
				),
				$this->email_field_id2 => 'john@doe.com',
			),
		);

		$this->spam_check = new FrmSpamCheckDenylist( $this->default_values );

		$this->custom_denylist_data = array(
			'denylist_with_all_fields'      => array(
				'words' => array( 'spamword' ),
			),
			'denylist_with_name_text_email' => array(
				'words'      => array( 'spamword' ),
				'field_type' => array( 'text', 'email', 'name' ),
			),
			'denylist_with_name'            => array(
				'words'      => array( 'spamword' ),
				'field_type' => array( 'name' ),
			),
			'denylist_with_email'           => array(
				'words'      => array( 'spamword' ),
				'field_type' => array( 'email' ),
			),
			'denylist_with_extract_email'   => array(
				'words'         => array( 'spamword' ),
				'field_type'    => array(),
				'extract_value' => array( 'FrmAntiSpamController', 'extract_emails_from_values' ),
			),
		);
	}

	private function set_denylist_data( $denylist_data ) {
		$this->set_private_property( $this->spam_check, 'denylist', $denylist_data );
	}

	private function set_values( $values ) {
		$this->set_private_property( $this->spam_check, 'values', $values );
	}

	public function test_get_field_ids_to_check() {
		// Test get_field_ids_to_check
		$field_ids_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_field_ids_to_check' ),
			array( $this->custom_denylist_data['denylist_with_all_fields'] )
		);
		$this->assertFalse( $field_ids_to_check );

		$field_ids_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_field_ids_to_check' ),
			array( $this->custom_denylist_data['denylist_with_name_text_email'] )
		);
		$this->assertEquals(
			array(
				$this->text_field_id,
				$this->email_field_id,
				$this->name_field_id,
				$this->email_field_id2,
			),
			$field_ids_to_check
		);

		$field_ids_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_field_ids_to_check' ),
			array( $this->custom_denylist_data['denylist_with_name'] )
		);
		$this->assertEquals( array( $this->name_field_id ), $field_ids_to_check );

		$field_ids_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_field_ids_to_check' ),
			array( $this->custom_denylist_data['denylist_with_email'] )
		);
		$this->assertEquals( array( $this->email_field_id, $this->email_field_id2 ), $field_ids_to_check );
	}

	public function test_get_values_to_check() {
		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_all_fields'] )
		);
		$this->assertEquals(
			$values_to_check,
			array(
				'test@gmail.com',
				'this text contains test@domain.com',
				'WordPress Plugin',
				'john@doe.com',
			)
		);

		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_name_text_email'] )
		);
		$this->assertEquals(
			$values_to_check,
			array(
				'test@gmail.com',
				'this text contains test@domain.com',
				'WordPress Plugin',
				'john@doe.com',
			)
		);

		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_name'] )
		);
		$this->assertEquals(
			$values_to_check,
			array(
				'WordPress Plugin',
			)
		);

		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_extract_email'] )
		);
		$this->assertEquals(
			$values_to_check,
			array(
				'test@gmail.com',
				'test@domain.com',
				'john@doe.com',
			)
		);

		$values_to_check = $this->run_private_method(
			array( $this->spam_check, 'get_values_to_check' ),
			array( $this->custom_denylist_data['denylist_with_email'] )
		);
		$this->assertEquals(
			$values_to_check,
			array(
				'test@gmail.com',
				'john@doe.com',
			)
		);
	}

	public function test_check() {
		$spam_check = new FrmSpamCheckDenylist( $this->default_values );
		$this->assertFalse( $spam_check->check() );
		$denylist = $this->custom_denylist_data['denylist_with_all_fields'];

		$this->set_denylist_data( array( $denylist ) );

		$this->assertFalse( $this->spam_check->check() );

		$denylist['words'] = array( '.com' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertTrue( $this->spam_check->check() );

		$denylist['compare'] = FrmSpamCheckDenylist::COMPARE_EQUALS;
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->spam_check->check() );

		$denylist['words']   = array( '@' );
		$denylist['compare'] = FrmSpamCheckDenylist::COMPARE_CONTAINS;
		$this->set_denylist_data( array( $denylist ) );
		$this->assertTrue( $this->spam_check->check() );

		$denylist          = $this->custom_denylist_data['denylist_with_name'];
		$denylist['words'] = array( '@' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->spam_check->check() );

		$denylist          = $this->custom_denylist_data['denylist_with_all_fields'];
		$denylist['words'] = array( 'plugin' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertTrue( $this->spam_check->check() );

		$denylist['extract_value'] = array( 'FrmAntiSpamController', 'extract_emails_from_values' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->spam_check->check() );

		$denylist         = $this->custom_denylist_data['denylist_with_all_fields'];
		$denylist['file'] = __DIR__ . '/denylist-email-contain.txt';
		unset( $denylist['words'] );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertTrue( $this->spam_check->check() );

		$denylist['extract_value'] = array( 'FrmAntiSpamController', 'extract_emails_from_values' );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->spam_check->check() );

		FrmAppHelper::get_settings()->update_setting( 'allowed_words', "wordpress\nplugin", 'sanitize_textarea_field' );
		unset( $denylist['extract_value'] );
		$this->set_denylist_data( array( $denylist ) );
		$this->assertFalse( $this->spam_check->check() );

		FrmAppHelper::get_settings()->update_setting( 'disallowed_words', "wordprezz\ndoe.com", 'sanitize_textarea_field' );
		$spam_check = new FrmSpamCheckDenylist( $this->default_values );
		$this->assertTrue( $spam_check->check() );

		// Test with regex.
		$values = $this->default_values;
		$values['item_meta'][ $this->email_field_id ] = 'someone@mail.ru';
		$spam_check = new FrmSpamCheckDenylist( $values );
		$this->assertTrue( $spam_check->check() );

		$values = $this->default_values;
		$values['item_meta'][ $this->text_field_id ] = 'This text contains someone@yandex.com email';
		$spam_check = new FrmSpamCheckDenylist( $values );
		$this->assertTrue( $spam_check->check() );
	}
}
