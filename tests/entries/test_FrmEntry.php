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

	/**
	 * @covers FrmEntry::package_entry_to_update
	 */
	function test_package_entry_to_update(){
		if ( ! $this->is_pro_active ) {
			$this->markTestSkipped( 'Pro is not active' );
		}

		$entry = FrmEntry::getOne( 'post-entry-1', true );
		$this->assertNotEmpty( $entry, 'Entry not found: post-entry-1' );
		$values = $this->_setup_test_update_values( $entry );

		$new_values = $this->run_private_method( array( 'FrmEntry', 'package_entry_to_update' ), array( $entry->id, $values ) );

		$this->_check_packaged_entry_name( $values, $new_values );
		$this->_check_packaged_form_id( $values, $new_values );
		$this->_check_packaged_is_draft( $values, $new_values );
		$this->_check_packaged_updated_at( $values, $new_values );
		$this->_check_packaged_updated_by( $values, $new_values );
		$this->_check_packaged_post_id( $values, $new_values );
		$this->_check_packaged_item_key( $values, $new_values );
		$this->_check_packaged_parent_item_id( $values, $new_values );
		$this->_check_packaged_frm_user_id( $values, $new_values );

	}

	function _setup_test_update_values( $entry ) {
		$form = FrmForm::getOne( $entry->form_id );

		$this->set_current_user_to_1();

		$values = array(
			'form_id' => $entry->form_id,
			'frm_hide_fields_' . $entry->form_id => '',
			'frm_helers_' . $entry->form_id => '',
			'form_key' => $form->form_key,
			'item_meta' => $entry->metas,
			'frm_submit_entry_' . $entry->form_id => wp_create_nonce( 'frm_submit_entry_' . $entry->form_id ),
			'_wp_http_referer' => '/features/create-a-post-no-categories/?frm_action=edit&entry=' . $entry->id,
			'id' => $entry->id,
			'item_key' => $entry->item_key,
			'item_name' => $entry->name,
			'frm_user_id' => $entry->user_id,
			'frm_skip_cookie' => 1
		);

		return $values;
	}

	function _check_packaged_entry_name( $values, $new_values ) {
		$expected_name = isset( $values['item_name'] ) ? $values['item_name'] : ( isset( $values['name'] ) ? $values['name'] : '' );
		$this->assertEquals( $expected_name, $new_values['name'], 'The item name is not correct when an entry is getting updated.' );
	}

	function _check_packaged_form_id( $values, $new_values ) {
		$expected_form_id = isset( $values['form_id'] ) ? (int) $values['form_id'] : null;
		$this->assertEquals( $expected_form_id, $new_values['form_id'], 'The form_id is not correct when an entry is getting updated.' );
	}

	function _check_packaged_is_draft( $values, $new_values ) {
		$expected_is_draft = ( ( isset($values['frm_saving_draft']) && $values['frm_saving_draft'] == 1 ) ||  ( isset($values['is_draft']) && $values['is_draft'] == 1) ) ? 1 : 0;
		$this->assertEquals( $expected_is_draft, $new_values['is_draft'], 'The is_draft is not correct when an entry is getting updated.' );
	}

	function _check_packaged_updated_at( $values, $new_values ) {
		$expected_updated_at = current_time('mysql', 1);
		$this->assertEquals( $expected_updated_at, $new_values['updated_at'], 'The updated_at value is not correct when an entry is getting updated.' );
	}

	function _check_packaged_updated_by( $values, $new_values ) {
		$user_id = get_current_user_id();
		$expected_updated_by = isset($values['updated_by']) ? $values['updated_by'] : $user_id;
		$this->assertEquals( $expected_updated_by, $new_values['updated_by'], 'The updated_by value is not correct when an entry is getting updated.' );
	}

	function _check_packaged_post_id( $values, $new_values ) {
	    if ( isset($values['post_id']) ) {
			$expected_post_id = (int) $values['post_id'];
		}

		$statement1 = isset( $expected_post_id ) && $new_values['post_id'] == $expected_post_id;
		$statement2 = ! isset( $expected_post_id ) && ! isset( $new_values['post_id'] );
		$this->assertTrue( $statement1 || $statement2, 'The post ID is set or removed when it should not be (updating an entry).' );
	}

	function _check_packaged_item_key( $values, $new_values ) {
	    if ( isset( $values['item_key'] ) ) {
			$expected_item_key = $values['item_key'];
		}

		$statement1 = isset( $expected_item_key ) && $new_values['item_key'] == $expected_item_key;
		$statement2 = ! isset( $expected_item_key ) && ! isset( $new_values['item_key'] );
		$this->assertTrue( $statement1 || $statement2, 'The item key is set or removed when it should not be (updating an entry).' );
	}

	function _check_packaged_parent_item_id( $values, $new_values ) {
	    if ( isset( $values['parent_item_id'] ) ) {
			$expected_parent_id = (int) $values['parent_item_id'];
		}

		$statement1 = isset( $expected_parent_id ) && $new_values['parent_item_id'] == $expected_parent_id;
		$statement2 = ! isset( $expected_parent_id ) && ! isset( $new_values['parent_item_id'] );
		$this->assertTrue( $statement1 || $statement2, 'The parent ID is set or removed when it should not be (updating an entry).' );
	}

	function _check_packaged_frm_user_id( $values, $new_values ) {
	    if ( isset( $values['frm_user_id'] ) && is_numeric( $values['frm_user_id'] ) ) {
			$expected_user_id = (int) $values['frm_user_id'];
		}

		$statement1 = isset( $expected_user_id ) && $new_values['user_id'] == $expected_user_id;
		$statement2 = ! isset( $expected_user_id ) && ! isset( $new_values['user_id'] );
		$this->assertTrue( $statement1 || $statement2, 'The user ID is set or removed when it should not be (updating an entry).' );
	}
}
