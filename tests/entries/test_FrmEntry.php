<?php

/**
 * @group entries
 */
class test_FrmEntry extends FrmUnitTest {

	/**
	 * @covers FrmEntry::create
	 * @covers FrmEntry::is_duplicate
	 */
	public function test_is_duplicate() {
		$form = $this->factory->form->get_object_by_id( $this->contact_form_key );
		$this->assertNotEmpty( $form, 'Form not found with id ' . $this->contact_form_key );
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$entry = $this->factory->entry->create_object( $entry_data );

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
		$entry = $this->factory->entry->create_object( $entry_data );
		$this->assertNotEmpty( $entry, 'False Positive for duplicate entry (A != A + B)' );
	}

	/**
	 * @covers FrmEntry::getAll
	 */
	public function test_getAll() {
		$form = $this->factory->form->get_object_by_id( $this->contact_form_key );
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$this->factory->entry->create_many( 10, $entry_data );

		$items = FrmEntry::getAll( array( 'it.form_id' => $form->id ) );
		$this->assertTrue( count( $items ) >= 10, 'There are no entries in form ' . $form->id );
	}
}
