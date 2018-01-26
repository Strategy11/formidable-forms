<?php

/**
 * @group entries
 */
class WP_Test_FrmEntriesController extends FrmUnitTest {

	/**
	 * @covers FrmEntriesController::delete_entry_after_save
	 * @covers FrmEntriesController::_delete_entry
	 * @covers FrmEntriesController::unlink_post
	 */
	function test_delete_entry_after_save() {
		$save_form = $this->create_form();
		$this->assertEmpty( $save_form->options['no_save'] );

		$entry_key = 'test' . $save_form->id . 'entry1';
		$this->create_post_entry( $save_form, $entry_key );
		$this->assertNotEmpty( FrmEntry::get_id_by_key( $entry_key ) );

		$no_save_form = $this->create_form( array( 'no_save' => 1 ) );
		$this->assertNotEmpty( $no_save_form->options['no_save'] );

		$entry_key = 'test' . $no_save_form->id . 'entry2';
		$created_post = $this->create_post_entry( $no_save_form, $entry_key );
		$this->assertEmpty( FrmEntry::get_id_by_key( $entry_key ), 'Entry was not deleted' );

		$post = get_post( $created_post );
		$this->assertNotEmpty( $post );
	}

	private function create_form( $options = array() ) {
		return $this->factory->form->create_and_get( array(
			'options' => $options,
		) );
	}

	private function create_post_entry( $form, $entry_key ) {
		$exists = FrmEntry::get_id_by_key( $entry_key );
		if ( $exists ) {
			FrmEntry::destroy( $exists );
		}

		$_POST = $this->factory->field->generate_entry_array( $form );
		$_POST['item_key'] = $entry_key;
		$_POST['action'] = 'create';
		FrmEntriesController::process_entry();

		$created_entry = FrmEntry::get_id_by_key( $entry_key );
		$this->assertNotEmpty( $created_entry );

		$new_post = $this->factory->post->create_and_get();
		global $wpdb;
		$wpdb->update( $wpdb->prefix .'frm_items', array( 'post_id' => $new_post->ID ), array( 'id' => $created_entry ) );

		FrmFormsController::get_form( $form, false, false ); // this is where the entry is deleted

		return $new_post->ID;
	}
}
