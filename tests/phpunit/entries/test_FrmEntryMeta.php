<?php

/**
 * @group entries
 *
 * @covers FrmEntryMeta
 */
#[\PHPUnit\Framework\Attributes\Group( 'entries' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmEntryMeta::class )]
class test_FrmEntryMeta extends FrmUnitTest {

	/**
	 * Integrations can restore source whitespace while ordinary fields retain trimming.
	 */
	public function test_add_entry_meta_filter_receives_original_source() {
		$form_id  = $this->factory->form->create();
		$entry_id = $this->factory->entry->create( array( 'form_id' => $form_id ) );
		$field_id = $this->factory->field->create( array( 'form_id' => $form_id ) );
		$source   = "\n\tcode sample\n\n";

		add_filter( 'frm_add_entry_meta', array( $this, 'restore_original_source' ), 10, 2 );

		try {
			$meta_id = FrmEntryMeta::add_entry_meta( $entry_id, $field_id, '', $source );
			$this->assertNotFalse( $meta_id, 'The filtered metadata should be stored.' );
			$this->assertSame( $source, FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id ), 'The filter should preserve exact leading and trailing whitespace.' );
		} finally {
			remove_filter( 'frm_add_entry_meta', array( $this, 'restore_original_source' ) );
		}

		$field_id = $this->factory->field->create( array( 'form_id' => $form_id ) );
		FrmEntryMeta::add_entry_meta( $entry_id, $field_id, '', $source );
		$this->assertSame( trim( $source ), FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id ), 'Ordinary metadata should retain its existing trimming behavior.' );
	}

	/**
	 * Restore source after checking the prepared and original hook arguments.
	 *
	 * @param array  $values Prepared metadata columns.
	 * @param string $source Original source before trimming.
	 *
	 * @return array
	 */
	public function restore_original_source( $values, $source ) {
		$this->assertSame( "\n\tcode sample\n\n", $source, 'The second hook argument should contain the original source.' );
		$this->assertSame( trim( $source ), $values['meta_value'], 'The first hook argument should retain the prepared value.' );
		$values['meta_value'] = $source;
		return $values;
	}

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
	 * @see FrmEntryMeta::set_value_before_save
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
	 * @see FrmEntryMeta::add_entry_meta
	 * @see FrmEntryMeta::update_entry_meta
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
	 * @see FrmEntryMeta::update_entry_metas
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
	 * @see FrmEntryMeta::update_entry_metas
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
	 * @see FrmEntryMeta::update_entry_metas
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
	 * Saving a draft on every page turn submits every field again. A value that has not changed
	 * should not be written again, and should not fire the meta change hooks, while a changed
	 * value in the same save is still written.
	 *
	 * @see https://github.com/Strategy11/formidable-pro/issues/2048
	 * @see FrmEntryMeta::update_entry_metas
	 */
	public function test_update_entry_metas_skips_unchanged_values() {
		$form              = $this->factory->form->create_and_get();
		$text_field_id     = $this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'text',
			)
		);
		$checkbox_field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'checkbox',
				'options' => array( 'Red', 'Blue', 'Green' ),
			)
		);
		$changed_field_id  = $this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'text',
			)
		);

		$values   = array(
			$text_field_id     => 'Unchanged',
			$checkbox_field_id => array( 'Red', 'Green' ),
			$changed_field_id  => 'Before',
		);
		$entry_id = $this->factory->entry->create(
			array(
				'form_id'   => $form->id,
				'item_meta' => $values,
			)
		);

		$values[ $changed_field_id ] = 'After';
		$writes                      = $this->update_entry_metas_and_track_writes( $entry_id, $values );

		$this->assertSame( array( (int) $changed_field_id ), $writes['changed_fields'], 'Only the changed field should fire the meta change hook.' );
		$this->assertCount( 1, $writes['updates'], 'Only the changed value should be written.' );
		$this->assertSame( 'Unchanged', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $text_field_id ) );
		$this->assertSame( array( 'Red', 'Green' ), FrmEntryMeta::get_entry_meta_by_field( $entry_id, $checkbox_field_id ) );
		$this->assertSame( 'After', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $changed_field_id ) );

		$writes = $this->update_entry_metas_and_track_writes( $entry_id, $values );

		$this->assertSame( array(), $writes['updates'], 'Saving the same values again should not write anything.' );
		$this->assertSame( array(), $writes['changed_fields'], 'Saving the same values again should not fire the meta change hook.' );
	}

	/**
	 * The comparison has to use the value after the update filter runs, so a filter that changes
	 * the value still gets it written even when the submitted value matches the stored one.
	 *
	 * @see FrmEntryMeta::update_entry_metas
	 */
	public function test_update_entry_metas_compares_the_filtered_value() {
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'text',
			)
		);
		$entry_id = $this->factory->entry->create(
			array(
				'form_id'   => $form->id,
				'item_meta' => array( $field_id => 'Same' ),
			)
		);

		$filter = function ( $values ) {
			$values['meta_value'] .= ' (filtered)';
			return $values;
		};
		add_filter( 'frm_update_entry_meta', $filter );

		try {
			$writes = $this->update_entry_metas_and_track_writes( $entry_id, array( $field_id => 'Same' ) );
		} finally {
			remove_filter( 'frm_update_entry_meta', $filter );
		}

		$this->assertCount( 1, $writes['updates'], 'A value changed by the filter should be written.' );
		$this->assertSame( 'Same (filtered)', FrmEntryMeta::get_entry_meta_by_field( $entry_id, $field_id ) );
	}

	/**
	 * A field with more than one stored row has no single previous value to compare with, so it
	 * is always written. The update then brings every row to the submitted value.
	 *
	 * @see FrmEntryMeta::get_stored_meta_values
	 */
	public function test_update_entry_metas_always_writes_a_field_with_duplicate_rows() {
		global $wpdb;

		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'text',
			)
		);
		$entry_id = $this->factory->entry->create(
			array(
				'form_id'   => $form->id,
				'item_meta' => array( $field_id => 'Same' ),
			)
		);

		$wpdb->insert(
			$wpdb->prefix . 'frm_item_metas',
			array(
				'item_id'    => $entry_id,
				'field_id'   => $field_id,
				'meta_value' => 'Stale duplicate',
				'created_at' => current_time( 'mysql', 1 ),
			)
		);

		$writes = $this->update_entry_metas_and_track_writes( $entry_id, array( $field_id => 'Same' ) );

		$this->assertCount( 1, $writes['updates'], 'A field with duplicate rows should be written.' );

		$stored = $wpdb->get_col( $wpdb->prepare( 'SELECT meta_value FROM %i WHERE item_id = %d AND field_id = %d', $wpdb->prefix . 'frm_item_metas', $entry_id, $field_id ) );
		$this->assertSame( array( 'Same', 'Same' ), $stored, 'Every row for the field should hold the submitted value.' );
	}

	/**
	 * Run update_entry_metas() and record the meta UPDATE queries and meta change hooks it runs.
	 *
	 * @param int   $entry_id Entry ID.
	 * @param array $values   Values indexed by field ID.
	 *
	 * @return array{updates: string[], changed_fields: int[]}
	 */
	private function update_entry_metas_and_track_writes( $entry_id, $values ) {
		global $wpdb;

		$writes        = array(
			'updates'        => array(),
			'changed_fields' => array(),
		);
		$table         = $wpdb->prefix . 'frm_item_metas';
		$track_query   = function ( $query ) use ( &$writes, $table ) {
			if ( str_starts_with( ltrim( $query ), 'UPDATE `' . $table . '`' ) ) {
				$writes['updates'][] = $query;
			}
			return $query;
		};
		$track_changes = function ( $changed_entry_id, $field_id ) use ( &$writes ) {
			$writes['changed_fields'][] = (int) $field_id;
		};

		add_filter( 'query', $track_query );
		add_action( 'frm_after_entry_meta_change', $track_changes, 10, 2 );

		try {
			FrmEntryMeta::update_entry_metas( $entry_id, $values );
		} finally {
			remove_filter( 'query', $track_query );
			remove_action( 'frm_after_entry_meta_change', $track_changes, 10 );
		}

		return $writes;
	}

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
