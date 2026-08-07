<?php

/**
 * @group entries
 */
class test_FrmEntryMeta extends FrmUnitTest {

	/**
	 * @covers FrmEntryMeta::update_entry_metas
	 */
	public function test_update_entry_metas() {
		$form       = $this->factory->form->create_and_get();
		$field_id   = $this->factory->field->create(
			array(
				'form_id' => $form->id,
			)
		);
		$entry_data = $this->factory->field->generate_entry_array( $form );

		$entry_data['item_meta'][ $field_id ] = 'Original value';

		$entry_id = $this->factory->entry->create( $entry_data );

		// Test field ID.
		$values = array(
			$field_id => 'Updated value by field ID',
		);
		FrmEntryMeta::update_entry_metas( $entry_id, $values );

		$meta = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id );

		$this->assertSame( 'Updated value by field ID', $meta );

		// Test field key.
		$field_key = FrmField::get_key_by_id( $field_id );
		$values    = array(
			$field_key => 'Updated value by field key',
		);
		FrmEntryMeta::update_entry_metas( $entry_id, $values );

		$meta = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id );

		$this->assertSame( 'Updated value by field key', $meta );

		// Test with an empty value. It should be null because the row should be deleted from the db.
		$values = array(
			$field_key => '',
		);
		FrmEntryMeta::update_entry_metas( $entry_id, $values );

		$meta = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id );

		$this->assertNull( $meta );
	}

	/**
	 * @covers FrmEntryMeta::should_join_fields_table
	 */
	public function test_should_join_fields_table() {
		$where = 'fi.form_id=123';
		$this->assertFalse( $this->run_private_method( array( 'FrmEntryMeta', 'should_join_fields_table' ), array( &$where ) ) );
		$this->assertSame( 'e.form_id=123', $where );

		$where = 'fi.id=123 AND fi.form_id=456';
		$this->assertTrue( $this->run_private_method( array( 'FrmEntryMeta', 'should_join_fields_table' ), array( &$where ) ) );
		$this->assertSame( 'fi.id=123 AND fi.form_id=456', $where );

		$where = array(
			'fi.id'      => 123,
			'fi.form_id' => 456,
		);
		$this->assertTrue( $this->run_private_method( array( 'FrmEntryMeta', 'should_join_fields_table' ), array( &$where ) ) );
		$this->assertSame(
			array(
				'fi.id'      => 123,
				'fi.form_id' => 456,
			),
			$where
		);

		$where = array(
			'fi.form_id' => 456,
		);
		$this->assertFalse( $this->run_private_method( array( 'FrmEntryMeta', 'should_join_fields_table' ), array( &$where ) ) );
		$this->assertSame( array( 'e.form_id' => 456 ), $where );
	}

	/**
	 * @covers FrmEntryMeta::delete_entry_meta
	 */
	public function test_delete_entry_meta() {
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
			)
		);

		$entry_data = $this->factory->field->generate_entry_array( $form );

		$entry_data['item_meta'][ $field_id ] = 'Value to delete';

		$entry_id = $this->factory->entry->create( $entry_data );

		$this->assertSame( 'Value to delete', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id ) );

		FrmEntryMeta::delete_entry_meta( $entry_id, $field_id );

		$this->assertNull( FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id ) );
	}

	/**
	 * @covers FrmEntryMeta::get_entry_metas_for_field
	 */
	public function test_get_entry_metas_for_field() {
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
			)
		);

		$entry_data = $this->factory->field->generate_entry_array( $form );

		$entry_data['item_meta'][ $field_id ] = 'Meta value to find';

		$this->factory->entry->create( $entry_data );

		// Look up by field id.
		$values = FrmEntryMeta::get_entry_metas_for_field( $field_id );
		$this->assertContains( 'Meta value to find', $values );

		// Look up by field key, which joins the fields table.
		$field_key = FrmField::get_key_by_id( $field_id );
		$values    = FrmEntryMeta::get_entry_metas_for_field( $field_key );
		$this->assertContains( 'Meta value to find', $values );
	}

	/**
	 * @covers FrmEntryMeta::search_entry_metas
	 */
	public function test_search_entry_metas() {
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
			)
		);

		$entry_data = $this->factory->field->generate_entry_array( $form );

		$entry_data['item_meta'][ $field_id ] = 'Findable value';

		$entry_id         = (int) $this->factory->entry->create( $entry_data );
		$other_entry_data = $this->factory->field->generate_entry_array( $form );

		$other_entry_data['item_meta'][ $field_id ] = 'Something else';

		$other_entry_id = (int) $this->factory->entry->create( $other_entry_data );
		$matches        = array_map( 'intval', FrmEntryMeta::search_entry_metas( 'Findable', $field_id, 'LIKE' ) );

		$this->assertContains( $entry_id, $matches );
		$this->assertNotContains( $other_entry_id, $matches );

		$matches = array_map( 'intval', FrmEntryMeta::search_entry_metas( 'Something else', $field_id, '=' ) );

		$this->assertContains( $other_entry_id, $matches );
	}
}
