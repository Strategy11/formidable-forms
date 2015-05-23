<?php

/**
 * @group entries
 */
class WP_Test_FrmEntry extends FrmUnitTest {
	/**
	 * @covers FrmEntry::create
	 */
	function test_create() {
		$form = $this->factory->form->get_object_by_id( $this->contact_form_key );
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$entry = FrmEntry::create( $entry_data );

		$this->assertNotEmpty( $entry );
		$this->assertTrue( is_numeric( $entry ) );
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