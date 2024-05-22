<?php

/**
 * @group entries
 */
class test_FrmEntry extends FrmUnitTest {

	public static function wpSetUpBeforeClass() {
		$_POST = array();
		self::empty_tables();
		self::frm_install();
	}

	/**
	 * @covers FrmEntry::create
	 * @covers FrmEntry::is_duplicate
	 */
	public function test_is_duplicate() {
		$form = $this->factory->form->get_object_by_id( $this->contact_form_key );
		$this->assertNotEmpty( $form, 'Form not found with id ' . $this->contact_form_key );
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$entry      = $this->factory->entry->create_object( $entry_data );

		$this->assertNotEmpty( $entry );
		$this->assertTrue( is_numeric( $entry ) );

		$entry = $this->factory->entry->create_object( $entry_data );
		$this->assertEmpty( $entry, 'Failed to detect duplicate entry' );

		// test an empty field in second entry: A + B != A
		$website_field = $this->factory->field->get_id_by_key( 'contact-website' );
		$entry_data    = $this->factory->field->generate_entry_array( $form );
		$entry         = $this->factory->entry->create_object( $entry_data );

		$this->assertNotEmpty( $entry, 'False Positive duplicate entry (A + B != A)' );

		unset( $entry_data['item_meta'][ $website_field ] );
		$entry = $this->factory->entry->create_object( $entry_data );
		$this->assertNotEmpty( $entry, 'False Positive for duplicate entry' );

		// test an empty field in first entry: A != A + B
		$entry_data = $this->factory->field->generate_entry_array( $form );
		unset( $entry_data['item_meta'][ $website_field ] );
		$this->factory->entry->create_object( $entry_data );
		$entry_data['item_meta'][ $website_field ] = 'http://test.com';
		$entry                                     = $this->factory->entry->create_object( $entry_data );
		$this->assertNotEmpty( $entry, 'False Positive for duplicate entry (A != A + B)' );
	}

	/**
	 * @covers FrmEntry::getAll
	 */
	public function test_getAll() {
		$form       = $this->factory->form->get_object_by_id( $this->contact_form_key );
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$this->factory->entry->create_many( 10, $entry_data );

		// Test with $meta = false.
		$items = FrmEntry::getAll( array( 'it.form_id' => $form->id ) );
		$this->assertGreaterThanOrEqual( 10, count( $items ), 'There should be more entries in form ' . $form->id );

		$first_item = reset( $items );

		$this->assertIsObject( $first_item );
		$this->assertTrue( empty( $first_item->metas ), 'Entries should not include metas unless $meta = true is set.' );

		// Test with $meta = true.
		$items      = FrmEntry::getAll( array( 'it.form_id' => $form->id ), '', '', true, false );
		$first_item = reset( $items );

		$this->assertIsObject( $first_item );
		$this->assertTrue( ! empty( $first_item->metas ) );
		$this->assertIsArray( $first_item->metas );

		$email_field = FrmField::getOne( 'contact-email' );
		$this->assertArrayHasKey( $email_field->id, $first_item->metas );

		$email = $first_item->metas[ $email_field->id ];
		$this->assertIsString( $email );
		$this->assertStringContainsString( '@', $email );

		// Test $meta = true with a serialized array in a checkbox field.
		$form_id           = $this->factory->form->create();
		$checkbox_field_id = $this->factory->field->create(
			array(
				'form_id' => $form_id,
				'type'    => 'checkbox',
				'options' => serialize( array( 'Option 1', 'Option 2', 'Option 3' ) ),
			)
		);

		$entry_data                                    = $this->factory->field->generate_entry_array( FrmForm::getOne( $form_id ) );
		$entry_data['item_meta'][ $checkbox_field_id ] = array( 'Option 1', 'Option 2' );
		$this->factory->entry->create( $entry_data );

		$items = FrmEntry::getAll( array( 'it.form_id' => $form_id ), '', '', true, false );
		$item  = reset( $items );

		$this->assertIsObject( $item );
		$this->assertTrue( ! empty( $item->metas ) );
		$this->assertIsArray( $item->metas );
		$this->assertArrayHasKey( $checkbox_field_id, $item->metas );

		$checkbox_value = $item->metas[ $checkbox_field_id ];
		$this->assertIsArray( $checkbox_value );
		$this->assertEquals( array( 'Option 1', 'Option 2' ), $checkbox_value );
	}
}
