<?php

/**
 * Generic integration controls for storage, fields, and rendering.
 *
 * @group entries
 */
class test_FrmIntegrationGuards extends FrmUnitTest {

	/**
	 * @return void
	 */
	public function test_denied_settings_and_import_deletes_report_failure() {
		$this->assertTrue( $this->run_private_method( array( 'FrmXMLHelper', 'delete_removed_fields' ), array( false ) ), 'New imports have no previous fields to delete.' );
		$form_id  = $this->factory->form->create();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form_id,
				'type'    => 'captcha',
			)
		);
		add_filter( 'frm_can_destroy_field', '__return_false' );

		try {
			$this->assertFalse( $this->run_private_method( array( 'FrmFormsController', 'toggle_field_in_form' ), array( $form_id, 'captcha', false, 'Captcha' ) ) );
			$this->assertFalse( $this->run_private_method( array( 'FrmXMLHelper', 'delete_removed_fields' ), array( array( FrmField::getOne( $field_id ) ) ) ) );
			$this->assertNotFalse( FrmField::getOne( $field_id ) );
		} finally {
			remove_filter( 'frm_can_destroy_field', '__return_false' );
		}

		$message = '';
		$errors  = array();
		FrmXMLHelper::parse_message(
			array(
				'warnings' => array( 'Delete denied' ),
				'imported' => array( 'forms' => 0 ),
				'updated'  => array(
					'fields' => 1,
					'forms'  => 0,
				),
				'forms'    => array(),
			),
			$message,
			$errors
		);
		$this->assertContains( 'Delete denied', $errors );
	}

	public function test_entry_filter_blocks_standard_and_xml_writes() {
		$form_id  = $this->factory->form->create();
		$values   = array(
			'form_id' => $form_id,
			'name'    => 'Original',
		);
		$entry_id = FrmEntry::create( $values );
		$this->assertNotFalse( $entry_id, 'Ordinary entry creation should remain available.' );
		add_filter( 'frm_allow_entry_save', '__return_false' );

		try {
			$this->assertFalse( FrmEntry::create( $values ), 'The filter should block standard creation.' );
			$this->assertFalse( FrmEntry::create_entry_from_xml( $values ), 'The filter should block XML creation.' );
			$this->assertFalse( FrmEntry::update( $entry_id, array( 'name' => 'Changed' ) ), 'The filter should block standard updates without a form ID.' );
			$this->assertFalse( FrmEntry::update_entry_from_xml( $entry_id, array( 'name' => 'Changed' ) ), 'The filter should block XML updates.' );
			$this->assertSame( 'Original', FrmEntry::getOne( $entry_id )->name, 'Blocked updates should preserve the existing entry.' );
		} finally {
			remove_filter( 'frm_allow_entry_save', '__return_false' );
		}
	}

	/**
	 * @return void
	 */
	public function test_storage_option_blocks_form_and_parent_form() {
		$form_id  = $this->factory->form->create(
			array( 'options' => array( 'prevent_entry_storage' => 1 ) )
		);
		$child_id = $this->factory->form->create();
		$this->assertFalse( FrmEntry::create( array( 'form_id' => $form_id ) ), 'The storage option should block writes without an integration filter.' );
		$this->assertFalse( FrmEntry::create_entry_from_xml( array( 'form_id' => $form_id ) ), 'The storage option should also block imports.' );
		$this->assertFalse(
			FrmEntry::create(
				array(
					'form_id'        => $child_id,
					'parent_form_id' => $form_id,
				)
			),
			'A protected parent form should block child entry storage.'
		);
		$this->assertTrue( FrmEntry::allows_save( array( 'form_id' => $child_id ) ), 'Unprotected forms should still allow storage.' );
	}

	/**
	 * Values from the last frm_allow_entry_save call.
	 *
	 * @var array
	 */
	private $entry_save_calls = array();

	/**
	 * @return void
	 */
	public function test_allowing_filter_receives_entry_id_and_saves() {
		$form_id                = $this->factory->form->create();
		$this->entry_save_calls = array();
		add_filter( 'frm_allow_entry_save', array( $this, 'record_entry_save' ), 10, 3 );

		try {
			$entry_id = FrmEntry::create(
				array(
					'form_id' => $form_id,
					'name'    => 'Original',
				)
			);
			$this->assertNotFalse( $entry_id, 'An allowing filter with three arguments should not block creation.' );
			$this->assertSame( 0, $this->entry_save_calls[0]['entry_id'], 'New entries should pass 0 as the entry ID.' );

			// The entry was just created, so clear it from the list that blocks a second save in one request.
			global $frm_vars;
			$frm_vars['saved_entries'] = array();
			$this->entry_save_calls    = array();
			$this->assertNotFalse( FrmEntry::update( $entry_id, array( 'name' => 'Changed' ) ), 'An allowing filter should not block updates.' );
			$this->assertSame( 'Changed', FrmEntry::getOne( $entry_id )->name, 'The update should be saved.' );
			$this->assertCount( 1, $this->entry_save_calls, 'An update that keeps the form should be checked once.' );
			$this->assertSame( (int) $entry_id, $this->entry_save_calls[0]['entry_id'], 'Updates should pass the entry ID.' );
			$this->assertSame( (int) $form_id, (int) $this->entry_save_calls[0]['values']['form_id'], 'Updates without a form ID should include the entry form ID.' );
		} finally {
			remove_filter( 'frm_allow_entry_save', array( $this, 'record_entry_save' ), 10 );
		}
	}

	/**
	 * Record the arguments passed to frm_allow_entry_save and allow the save.
	 *
	 * @param bool  $allow    Whether the entry may be saved.
	 * @param array $values   Entry values.
	 * @param int   $entry_id The entry being updated, or 0 for a new entry.
	 *
	 * @return bool
	 */
	public function record_entry_save( $allow, $values, $entry_id ) {
		$this->entry_save_calls[] = array(
			'values'   => $values,
			'entry_id' => $entry_id,
		);
		return $allow;
	}

	/**
	 * Pro creates repeater child entries with the parent form in parent_form_id
	 * (see FrmProEntry::create_sub_entries). A protected parent should block them.
	 *
	 * @return void
	 */
	public function test_storage_option_blocks_repeater_child_entry_values() {
		$parent_id = $this->factory->form->create(
			array( 'options' => array( 'prevent_entry_storage' => 1 ) )
		);
		$child_id  = $this->factory->form->create( array( 'parent_form_id' => $parent_id ) );
		$field_id  = $this->factory->field->create( array( 'form_id' => $child_id ) );

		$entry_values = array(
			'form_id'        => $child_id,
			'item_meta'      => array( $field_id => 'Child value' ),
			'parent_item_id' => 0,
			'parent_form_id' => $parent_id,
			'parent_nonce'   => wp_create_nonce( 'parent' ),
		);

		$this->assertFalse( FrmEntry::create( $entry_values ), 'A repeater child entry of a protected form should not be stored.' );
		$this->assertSame( 0, (int) FrmDb::get_count( 'frm_items', array( 'form_id' => $child_id ) ), 'No child entry should be saved.' );
	}

	/**
	 * @return void
	 */
	public function test_existing_form_policy_blocks_updates_without_a_form_id() {
		$form_id  = $this->factory->form->create();
		$entry_id = FrmEntry::create(
			array(
				'form_id' => $form_id,
				'name'    => 'Original',
			)
		);
		FrmForm::update( $form_id, array( 'options' => array( 'prevent_entry_storage' => 1 ) ) );
		$this->assertFalse( FrmEntry::update( $entry_id, array( 'name' => 'Changed' ) ), 'Updates should honor the stored form policy when no form ID is supplied.' );
		$this->assertFalse( FrmEntry::update_entry_from_xml( $entry_id, array( 'name' => 'Changed' ) ), 'XML updates should honor the stored form policy.' );
		$this->assertSame( 'Original', FrmEntry::getOne( $entry_id )->name, 'The storage policy should preserve existing entries.' );
	}

	/**
	 * @return void
	 */
	public function test_field_deletion_filter_preserves_the_field() {
		$form_id  = $this->factory->form->create();
		$field_id = $this->factory->field->create( array( 'form_id' => $form_id ) );
		$this->assertTrue( FrmField::can_destroy( $field_id ), 'Ordinary fields should allow deletion by default.' );
		add_filter( 'frm_can_destroy_field', '__return_false' );

		try {
			$this->assertFalse( FrmField::can_destroy( $field_id ), 'Integrations should be able to deny deletion.' );
			$this->assertFalse( FrmField::destroy( $field_id ), 'The model should enforce the deletion filter.' );
			$this->assertNotFalse( FrmField::getOne( $field_id ), 'A denied deletion should preserve the field.' );
		} finally {
			remove_filter( 'frm_can_destroy_field', '__return_false' );
		}
		$this->assertNotFalse( FrmField::destroy( $field_id ), 'Removing the filter should allow normal deletion.' );
	}

	/**
	 * @return void
	 */
	public function test_field_update_filter_receives_the_field_id() {
		$form_id  = $this->factory->form->create();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form_id,
				'name'    => 'Original',
			)
		);
		add_filter( 'frm_field_values_before_update', array( $this, 'filter_field_values' ), 10, 2 );

		try {
			FrmField::update( $field_id, array( 'name' => 'Changed' ) );
			$this->assertSame( 'Protected ' . $field_id, FrmField::getOne( $field_id )->name, 'The filtered values and field ID should be used before persistence.' );
		} finally {
			remove_filter( 'frm_field_values_before_update', array( $this, 'filter_field_values' ) );
		}
	}

	/**
	 * Replace the submitted label using the existing field ID.
	 *
	 * @param array $values Submitted field values.
	 * @param int   $id Existing field ID.
	 *
	 * @return array
	 */
	public function filter_field_values( $values, $id ) {
		$values['name'] = 'Protected ' . $id;
		return $values;
	}

	/**
	 * @return void
	 */
	public function test_final_form_filter_receives_the_rendered_form() {
		$this->set_front_end();
		$form_id = $this->factory->form->create();
		add_filter( 'frm_filter_final_form', array( $this, 'filter_final_form' ), 10, 2 );

		try {
			$html = do_shortcode( '[formidable id="' . $form_id . '"]' );
			$this->assertStringContainsString( 'Form context: ' . $form_id, $html, 'The final output filter should receive the form being rendered.' );
		} finally {
			remove_filter( 'frm_filter_final_form', array( $this, 'filter_final_form' ) );
		}
	}

	/**
	 * Append a marker identifying the rendered form.
	 *
	 * @param string $html Rendered form output.
	 * @param object $form Form being rendered.
	 *
	 * @return string
	 */
	public function filter_final_form( $html, $form ) {
		return $html . 'Form context: ' . $form->id;
	}
}
