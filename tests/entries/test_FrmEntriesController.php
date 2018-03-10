<?php

/**
 * @group entries
 */
class test_FrmEntriesController extends FrmUnitTest {

	/**
	 * @group testme
	 * @covers FrmEntriesController::delete_form_entries
	 */
	public function test_delete_form_entries() {
		$form = $this->create_form();
		$field = $this->factory->field->create_and_get( array(
			'type' => 'text',
			'form_id' => $form->id,
		) );

		$entry_data = $this->factory->field->generate_entry_array( $form );
		$this->factory->entry->create_many( 10, $entry_data );

		global $wpdb;
		$meta_query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}frm_item_metas as em INNER JOIN {$wpdb->prefix}frm_items as e on (em.item_id=e.id) WHERE form_id=%d", $form->id );
		$item_query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}frm_items WHERE form_id=%d", $form->id );

		$item_metas = $wpdb->get_var( $meta_query );
		$this->assertTrue( $item_metas >= 10, 'There are ' . $item_metas . ' entry metas in form ' . $form->id );

		$entries = $wpdb->get_var( $item_query );
		$this->assertTrue( $entries >= 10, 'There are ' . $entries . ' entries in form ' . $form->id );

		$this->run_private_method( array( 'FrmEntriesController', 'delete_form_entries' ), array( $form->id ) );

		$item_metas = $wpdb->get_var( $meta_query );
		$this->assertEquals( $item_metas, 0, 'There are still entry metas in form ' . $form->id );

		$entries = $wpdb->get_var( $item_query );
		$this->assertEquals( $entries, 0, 'There are still entries in form ' . $form->id );
	}

	/**
	 * @covers FrmEntriesController::delete_entry_after_save
	 * @covers FrmEntriesController::_delete_entry
	 * @covers FrmEntriesController::unlink_post
	 */
	function test_delete_entry_after_save() {
		$save_form = $this->create_form();
		$this->assertEmpty( $save_form->options['no_save'] );

		$entry_key = 'test' . $save_form->id . 'entry1';
		$post_id = $this->create_post_entry( $save_form, $entry_key );
		$this->assertNotEmpty( FrmEntry::getOne( $entry_key ) );

		$post = get_post( $post_id );
		$this->assertNotEmpty( $post );
		$this->assertEquals( 'publish', $post->post_status );

		$no_save_form = $this->create_form( array( 'no_save' => 1 ) );
		$this->assertNotEmpty( $no_save_form->options['no_save'] );

		$entry_key = 'test' . $no_save_form->id . 'entry2';
		$created_post = $this->create_post_entry( $no_save_form, $entry_key );
		$this->assertEmpty( FrmEntry::getOne( $entry_key ), 'Entry was not deleted' );

		$post = get_post( $created_post );
		$this->assertNotEmpty( $post );
		$this->assertEquals( 'publish', $post->post_status );
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

		$new_post = $this->factory->post->create_and_get();

		$_POST = $this->factory->field->generate_entry_array( $form );
		$_POST['item_key'] = $entry_key;
		$_POST['action'] = 'create';
		$_POST['post_id'] = $new_post->ID;
		FrmEntriesController::process_entry();

		$entry = FrmEntry::getOne( $entry_key );
		$this->assertNotEmpty( $entry );
		$this->assertEquals( $entry->post_id, $new_post->ID );

		FrmFormsController::get_form( $form, false, false ); // this is where the entry is deleted

		return $new_post->ID;
	}
}
