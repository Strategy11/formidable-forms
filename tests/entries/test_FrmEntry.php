<?php

/**
 * @group entries
 */
class WP_Test_FrmEntry extends FrmUnitTest {
	/**
	 * @covers FrmEntry::create
	 * @covers FrmEntry::is_duplicate
	 */
	function test_is_duplicate() {
		$form = $this->factory->form->get_object_by_id( $this->contact_form_key );
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$entry = $this->factory->entry->create_object( $entry_data );

		$this->assertNotEmpty( $entry );
		$this->assertTrue( is_numeric( $entry ) );

		$entry = $this->factory->entry->create_object( $entry_data );
		$this->assertEmpty( $entry, 'Failed to detect duplicate entry' );
	}

	/**
	 * @covers FrmEntry::getAll
	 */
    function test_getAll() {
		$form = $this->factory->form->get_object_by_id( $this->contact_form_key );
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$entry_id = $this->factory->entry->create_many( 10, $entry_data );

        $items = FrmEntry::getAll( array( 'it.form_id' => $form->id ) );
        $this->assertTrue( count( $items ) >= 10, 'There are no entries in form ' . $form->id );
    }
}
