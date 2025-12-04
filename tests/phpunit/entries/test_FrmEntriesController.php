<?php

/**
 * @group entries
 */
class test_FrmEntriesController extends FrmUnitTest {

	/**
	 * @covers FrmEntriesController::delete_entry_after_save
	 * @covers FrmEntriesController::_delete_entry
	 * @covers FrmEntriesController::unlink_post
	 */
	public function test_delete_entry_after_save() {
		$save_form = $this->create_form();
		$this->assertEmpty( $save_form->options['no_save'] );

		$entry_key = 'test' . $save_form->id . 'entry1';
		$post_id   = $this->create_post_entry( $save_form, $entry_key );
		$this->assertNotEmpty( FrmEntry::getOne( $entry_key ) );

		$post = get_post( $post_id );
		$this->assertNotEmpty( $post );
		$this->assertEquals( 'publish', $post->post_status );

		$no_save_form = $this->create_form( array( 'no_save' => 1 ) );
		$this->assertNotEmpty( $no_save_form->options['no_save'] );

		$entry_key    = 'test' . $no_save_form->id . 'entry2';
		$created_post = $this->create_post_entry( $no_save_form, $entry_key );
		$this->assertEmpty( FrmEntry::getOne( $entry_key ), 'Entry was not deleted' );

		$post = get_post( $created_post );
		$this->assertNotEmpty( $post );
		$this->assertEquals( 'publish', $post->post_status );
	}

	private function create_form( $options = array() ) {
		return $this->factory->form->create_and_get(
			array(
				'options' => $options,
			)
		);
	}

	private function create_post_entry( $form, $entry_key ) {
		$exists = FrmEntry::get_id_by_key( $entry_key );

		if ( $exists ) {
			FrmEntry::destroy( $exists );
		}

		$new_post = $this->factory->post->create_and_get();

		$_POST             = $this->factory->field->generate_entry_array( $form );
		$_POST['item_key'] = $entry_key;
		$_POST['action']   = 'create';
		$_POST['post_id']  = $new_post->ID;
		FrmEntriesController::process_entry();

		$entry = FrmEntry::getOne( $entry_key );
		$this->assertNotEmpty( $entry );
		$this->assertEquals( $entry->post_id, $new_post->ID );

		FrmFormsController::get_form( $form, false, false ); // this is where the entry is deleted

		return $new_post->ID;
	}

	/**
	 * @covers FrmEntriesController::hidden_columns
	 */
	public function test_hidden_columns() {
		// Confirm that a string option value doesn't trigger a fatal error.
		$columns = FrmEntriesController::hidden_columns( '' );
		$this->assertIsArray( $columns );
	}
}
