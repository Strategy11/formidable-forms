<?php

/**
 * @group entries
 */
class test_FrmEntryMeta extends FrmUnitTest {

	/**
	 * @covers FrmEntryMeta::update_entry_metas
	 * @group mike
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

		$values   = array(
			$field_id => 'Updated value by field ID',
		);
		FrmEntryMeta::update_entry_metas( $entry_id, $values );

		$meta = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id );

		$this->assertEquals( 'Updated value by field ID', $meta );

		$field_key = FrmField::get_key_by_id( $field_id );
		$values   = array(
			$field_key => 'Updated value by field key',
		);
		FrmEntryMeta::update_entry_metas( $entry_id, $values );

		$meta = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id );

		$this->assertEquals( 'Updated value by field key', $meta );
	}
}
