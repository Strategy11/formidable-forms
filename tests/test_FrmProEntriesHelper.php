<?php

/**
 * @group entries
 */
class WP_Test_FrmProEntriesHelper extends FrmUnitTest {
    /**
     * Search for a value in an entry
     */
    function test_search_by_field() {
		$form = $this->factory->form->create_and_get();
		$this->assertNotEmpty( $form );

		$field_id = $this->factory->field->create( array( 'type' => 'email', 'form_id' => $form->id ) );
		$this->assertNotEmpty( $field_id );
		$this->assertTrue( is_numeric( $field_id ) );

		$entry_data = $this->factory->field->generate_entry_array( $form );
		$this->factory->entry->create_many( 10, $entry_data );

	    $s_query = array( 'it.form_id' => $form->id );

        if ( is_callable('FrmProEntriesHelper::get_search_str') ) {
			$s = 'admin@example.org';
	        //$s_query = FrmProEntriesHelper::get_search_str( $s_query, $s, $form->id, $field_id );
        }

        $items = FrmEntry::getAll( $s_query, '', '', true, false );
		$this->assertNotEmpty( $items );
    }
}