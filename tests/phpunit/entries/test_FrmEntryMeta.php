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
	 * A text field leaves a non-numeric value alone while a number field coerces it to a float,
	 * so the pair tells us which field packed a given value.
	 *
	 * @return array The form, the text field id and the number field id.
	 */
	private function create_text_and_number_fields() {
		$form = $this->factory->form->create_and_get();

		$text_field_id = $this->factory->field->create(
			array(
				'form_id'   => $form->id,
				'type'      => 'text',
				'field_key' => 'meta_text_' . $form->id,
			)
		);

		$number_field_id = $this->factory->field->create(
			array(
				'form_id'   => $form->id,
				'type'      => 'number',
				'field_key' => 'meta_number_' . $form->id,
			)
		);

		return array( $form, $text_field_id, $number_field_id );
	}

	/**
	 * Callers may hand set_value_before_save() a field they have already loaded, so that
	 * update_entry_metas() does not look the same field up a second time for every value.
	 * The packing has to follow the field that was passed, otherwise the argument is being
	 * ignored and the second lookup is back.
	 *
	 * @covers FrmEntryMeta::set_value_before_save
	 */
	public function test_set_value_before_save_packs_with_the_field_that_was_passed() {
		list( , $text_field_id ) = $this->create_text_and_number_fields();

		$as_number       = clone FrmField::getOne( $text_field_id );
		$as_number->type = 'number';

		$values = array(
			'item_id'    => 1,
			'field_id'   => $text_field_id,
			'meta_value' => 'abc',
		);
		$this->run_private_method( array( 'FrmEntryMeta', 'set_value_before_save' ), array( &$values, $as_number ) );
		$this->assertEqualsWithDelta( 0.0, $values['meta_value'], PHP_FLOAT_EPSILON, 'The passed number field should coerce a non-numeric value to a float.' );

		$values = array(
			'item_id'    => 1,
			'field_id'   => $text_field_id,
			'meta_value' => 'abc',
		);
		$this->run_private_method( array( 'FrmEntryMeta', 'set_value_before_save' ), array( &$values, null ) );
		$this->assertSame( 'abc', $values['meta_value'], 'With no field passed the stored text field should be looked up and leave the value alone.' );
	}

	/**
	 * The field argument has to stay optional. add_entry_meta() and update_entry_meta() are
	 * called with four arguments from dozens of places across the add-ons, and those callers
	 * ship on their own release cycles.
	 *
	 * @covers FrmEntryMeta::add_entry_meta
	 * @covers FrmEntryMeta::update_entry_meta
	 */
	public function test_entry_meta_writers_still_accept_four_arguments() {
		foreach ( array( 'add_entry_meta', 'update_entry_meta' ) as $method ) {
			$reflection = new ReflectionMethod( 'FrmEntryMeta', $method );
			$this->assertSame( 4, $reflection->getNumberOfRequiredParameters(), $method . '() should keep exactly four required parameters.' );
		}

		list( $form, $text_field_id ) = $this->create_text_and_number_fields();

		$entry_id = $this->factory->entry->create( $this->factory->field->generate_entry_array( $form ) );

		FrmEntryMeta::update_entry_meta( $entry_id, $text_field_id, '', 'Updated with four arguments' );

		$stored = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $text_field_id );
		$this->assertSame( 'Updated with four arguments', $stored, 'A four argument update should still store the value.' );
	}

	/**
	 * The loop in update_entry_metas() hands each writer the field it loaded for that value.
	 * Packing two field types in one call proves each value is packed with its own field, rather
	 * than one iteration's field leaking into the next.
	 *
	 * @covers FrmEntryMeta::update_entry_metas
	 */
	public function test_update_entry_metas_packs_each_existing_value_with_its_own_field() {
		list( $form, $text_field_id, $number_field_id ) = $this->create_text_and_number_fields();

		$entry_id = $this->factory->entry->create( $this->factory->field->generate_entry_array( $form ) );

		FrmEntryMeta::update_entry_metas(
			$entry_id,
			array(
				$text_field_id   => 'abc',
				$number_field_id => 'abc',
			)
		);

		$this->assertSame( 'abc', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $text_field_id ), 'The text field should keep a non-numeric value as typed.' );
		$this->assertSame( '0', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $number_field_id ), 'The number field should coerce a non-numeric value to zero.' );
	}

	/**
	 * The same has to hold on the insert path, which runs for any field that has no row on the
	 * entry yet. That is every field on a later page of a multi-page form.
	 *
	 * @covers FrmEntryMeta::update_entry_metas
	 */
	public function test_update_entry_metas_packs_each_new_value_with_its_own_field() {
		$form = $this->factory->form->create_and_get();

		$this->factory->field->create(
			array(
				'form_id'   => $form->id,
				'type'      => 'text',
				'field_key' => 'meta_seed_' . $form->id,
			)
		);

		// Created before the fields below, so neither of them has a row on this entry yet.
		$entry_id = $this->factory->entry->create( $this->factory->field->generate_entry_array( $form ) );

		$text_field_id = $this->factory->field->create(
			array(
				'form_id'   => $form->id,
				'type'      => 'text',
				'field_key' => 'meta_new_text_' . $form->id,
			)
		);

		$number_field_id = $this->factory->field->create(
			array(
				'form_id'   => $form->id,
				'type'      => 'number',
				'field_key' => 'meta_new_number_' . $form->id,
			)
		);

		$this->assertNull( FrmEntryMeta::get_entry_meta_by_field( $entry_id, $number_field_id ), 'The number field should have no row before the update.' );

		FrmEntryMeta::update_entry_metas(
			$entry_id,
			array(
				$text_field_id   => 'abc',
				$number_field_id => 'abc',
			)
		);

		$this->assertSame( 'abc', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $text_field_id ), 'A newly inserted text value should be stored as typed.' );
		$this->assertSame( '0', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $number_field_id ), 'A newly inserted number value should be coerced to zero.' );
	}

	/**
	 * Values can be keyed by field key instead of field id, in which case the field handed to the
	 * writers is the one resolved from that key. A mix of both keying styles in one call has to
	 * still pack every value with the right field.
	 *
	 * @covers FrmEntryMeta::update_entry_metas
	 */
	public function test_update_entry_metas_packs_values_keyed_by_field_key_with_the_resolved_field() {
		list( $form, $text_field_id, $number_field_id ) = $this->create_text_and_number_fields();

		$entry_id = $this->factory->entry->create( $this->factory->field->generate_entry_array( $form ) );

		FrmEntryMeta::update_entry_metas(
			$entry_id,
			array(
				FrmField::get_key_by_id( $text_field_id ) => 'abc',
				$number_field_id                          => 'abc',
			)
		);

		$stored_text   = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $text_field_id );
		$stored_number = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $number_field_id );

		$this->assertSame( 'abc', $stored_text, 'A value keyed by field key should be packed with the text field it resolves to.' );
		$this->assertSame( '0', $stored_number, 'A value keyed by field id alongside it should still be packed with the number field.' );
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
}
