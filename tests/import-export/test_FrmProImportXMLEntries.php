<?php
/**
 * @group xml
 * @group pro
 */
class WP_Test_FrmProImportXMLEntries extends FrmUnitTest {

	/**
	* @covers FrmProXMLHelper::import_xml_entries
	*/
	function test_imported_xml_entries() {
		$args = array(
			'repeating_section_id' => FrmField::get_id_by_key( 'repeating-section' ),
			'parent_entry_id' => FrmEntry::get_id_by_key( 'jamie_entry_key' ),
			'repeating_entry_ids' => self::_get_repeating_entry_ids()
		);

		self::_check_child_entries_for_correct_parents( $args );
		self::_check_repeating_section_frm_item_metas( $args );
		self::_check_repeating_fields_frm_item_metas( $args );
	}

	function _get_repeating_entry_ids() {
		$repeating_entry_keys = array( 'jamie-entry', 'jamie-entry2', 'jamie-entry3' );
		$repeating_entry_ids = array();

		foreach ( $repeating_entry_keys as $e_key ) {
			$e_id = FrmEntry::get_id_by_key( $e_key );

			$this->assertTrue( ( $e_id !== false ), 'Entries not imported correctly from a repeating section.' );

			$repeating_entry_ids[] = $e_id;
 		}

		return $repeating_entry_ids;
	}

	function _check_child_entries_for_correct_parents( $args ) {
		global $wpdb;

		foreach ( $args['repeating_entry_ids'] as $child_id ) {
			$query = 'SELECT parent_item_id FROM ' . $wpdb->prefix . 'frm_items WHERE id=' . $child_id;
			$actual_parent = $wpdb->get_var( $query );

			$this->assertEquals( $args['parent_entry_id'], $actual_parent, 'The parent_item_id is not set correctly when importing repeating section data in an XML.' );
		}
	}

	function _check_repeating_section_frm_item_metas( $args ) {
		global $wpdb;

		// Check repeating field metas
		$query = 'SELECT meta_value FROM ' . $wpdb->prefix . 'frm_item_metas WHERE field_id=' . $args['repeating_section_id'] . ' AND item_id=' . $args['parent_entry_id'];
		$results = $wpdb->get_var( $query );
		$results = maybe_unserialize( $results );

		$this->assertEquals( $args['repeating_entry_ids'], $results, 'Entries aren\'t imported correctly with an XML file in a repeating section.' );
	}

	function _check_repeating_fields_frm_item_metas( $args ) {
		$expected_metas = self::_get_expected_repeating_metas( $args );

		foreach ( $args['repeating_entry_ids'] as $child_id ) {
			$child_entry = FrmEntry::getOne( $child_id, true );

			$this->assertTrue( ! empty( $child_entry->metas ), 'Data is not imported correctly in repeating fields.' );

			$this->assertEquals( $expected_metas[ $child_entry->item_key ], $child_entry->metas, 'Data is not imported correctly in repeating fields.' );

		}
	}

	function _get_expected_repeating_metas( $args ) {
		$repeating_fields = self::_get_fields_in_repeating_section( $args );

		$text_field_id = $checkbox_field_id = $date_field_id = 0;

		foreach ( $repeating_fields as $field ) {
			if ( $field->field_key == 'repeating-text' ) {
				$text_field_id = $field->id;
			} else if ( $field->field_key == 'repeating-checkbox' ) {
				$checkbox_field_id = $field->id;
			} else if ( $field->field_key == 'repeating-date' ) {
				$date_field_id = $field->id;
			}
		}

		// Loop through the fields inside of the repeating section. Make sure the data is correct
		$expected_metas = array(
			'jamie-entry' => array( $text_field_id => 'First', $checkbox_field_id => array( 'Option 1', 'Option 2' ), $date_field_id => '2015-05-27' ),
			'jamie-entry2' => array( $text_field_id => 'Second', $checkbox_field_id => array( 'Option 1', 'Option 2' ), $date_field_id => '2015-05-29' ),
			'jamie-entry3' => array( $text_field_id => 'Third', $checkbox_field_id => array( 'Option 2' ), $date_field_id => '2015-06-19' ),
		);

		return $expected_metas;
	}

	function _get_fields_in_repeating_section( $args ) {
		$repeating_section = FrmField::getOne( $args['repeating_section_id'] );

		$repeating_fields = FrmField::get_all_for_form( $repeating_section->field_options['form_select'] );

		return $repeating_fields;
	}

	function test_xml_import_to_update_entries() {
		$args = self::_get_xml_update_args();
		$path = self::_generate_xml_for_all_fields_form( $args );
		$message = FrmXMLHelper::import_xml( $path );

		self::_check_xml_update_repeating_section_values( $args );
		self::_check_xml_updated_number_of_entries( $args );
		//self::_check_xml_updated_repeating_entries();

		self::_check_the_imported_and_updated_numbers( $args, $message );

		// Delete the temp.XML file
		unlink( $path );
	}

	function _get_xml_update_args() {
		$repeating_section_id = FrmField::get_id_by_key( 'repeating-section' );
		$repeating_section_values = self::_get_repeating_section_values( $repeating_section_id );
		$repeating_section = FrmField::getOne( $repeating_section_id );
		$repeating_fields = FrmField::get_all_for_form( $repeating_section->field_options['form_select'] );
		$parent_form_id = FrmForm::get_id_by_key( $this->all_fields_form_key );
		$parent_entries = FrmEntry::getAll( array( 'form_id' => $parent_form_id ) );
		$child_entries = FrmEntry::getAll( array( 'form_id' => $repeating_section->field_options['form_select'] ) );
		$embed_form_id = FrmForm::get_id_by_key( $this->contact_form_key );
		$embedded_entries = FrmEntry::getAll( array( 'form_id' => $embed_form_id, 'parent_item_id !' => 0 ) );

		$args = array(
			'repeating_section' => $repeating_section,
			'repeating_section_values' => $repeating_section_values,
			'repeating_fields' => $repeating_fields,
			'parent_form_id' => $parent_form_id,
			'parent_entries' => $parent_entries,
			'child_entries' => $child_entries,
			'embedded_entries' => $embedded_entries
		);

		return $args;
	}

	function _get_repeating_section_values( $repeating_section_id ) {
		global $wpdb;

		$query = 'SELECT meta_value FROM ' . $wpdb->prefix . 'frm_item_metas WHERE field_id=' . $repeating_section_id;
		$meta_values = $wpdb->get_col( $query );

		return $meta_values;
	}

	function _check_xml_update_repeating_section_values( $args ) {
		$new_values = self::_get_repeating_section_values( $args['repeating_section']->id );

		$this->assertEquals( $args['repeating_section_values'], $new_values, 'The meta_value for a repeating section was modified on XML import (update) when it should not have been.' );
	}

	function _check_xml_updated_number_of_entries( $args ) {
		$parent_entries = FrmEntry::getAll( array( 'form_id' => $args['parent_form_id'] ) );
		$this->assertEquals( count( $args['parent_entries'] ), count( $parent_entries ), 'The number of entries in form ' . $args['parent_form_id'] . ' should be the same after an XML update.' );

		$rep_sec_form_id = FrmForm::get_id_by_key( $this->repeat_sec_form_key );
		$child_entries = FrmEntry::getAll( array( 'form_id' => $rep_sec_form_id ) );
		$this->assertEquals( count( $args['child_entries'] ), count( $child_entries ), 'The number of entries in form ' . $rep_sec_form_id . ' should be the same after an XML update.' );

		$embed_form_id = FrmForm::get_id_by_key( $this->contact_form_key );
		$embedded_entries = FrmEntry::getAll( array( 'form_id' => $embed_form_id, 'parent_item_id !' => 0 ) );
		$this->assertEquals( count( $args['embedded_entries'] ), count( $embedded_entries ), 'The number of entries in the embedded form should be the same after an XML update.' );
	}

	function _generate_xml_for_all_fields_form( $args ) {
		$type = array( 'forms','items','actions' );

		$xml_args = array(
			'ids' => array( $args['parent_form_id'] )
		);

		$path = FrmUnitTest::generate_xml( $type, $xml_args );

		return $path;
	}

	function _check_the_imported_and_updated_numbers( $args, $message ) {
		foreach ( $message['imported'] as $type => $number ) {
			$this->assertEquals( 0, $number, $number . ' ' . $type . ' were imported but they should have been updated.' );
		}

		$expected_numbers = array(
			'items' => count( $args['parent_entries'] ) + count( $args['child_entries'] ) + count( $args['embedded_entries'] )
		);

		foreach ( $expected_numbers as $type => $e_number ) {
			$this->assertEquals( $e_number, $message['updated'][ $type ], 'There is a discrepancy between the number of ' . $type . ' expected to be updated vs. the actual number of updated ' . $type . '. Before digging into this, check the $expected_numbers to make sure it is correct.' );
		}
	}
}
