<?php
/**
 * @group xml
 */
class WP_Test_FrmProXMLHelper extends FrmUnitTest {

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
		// Note: The repeating_section_data.xml file has a form_select of 13. This could potentially change at any time and I need to find a way to make the XML file match whatever is correct

		$args = array(
			'repeating_section_id' => FrmField::get_id_by_key( 'repeating-section' )
		);

		$repeating_field_ids = self::_get_fields_in_repeating_section( $args );

		$path = FrmAppHelper::plugin_path() . '/tests/base/repeating_section_data.xml';
		$message = FrmXMLHelper::import_xml( $path );

		self::_check_xml_updated_fields();
		self::_check_xml_updated_repeating_fields( $repeating_field_ids );
		self::_check_xml_updated_repeating_section();
		//self::_check_xml_updated_repeating_entries();
		//self::_check_parent_entries();

		// Note: 3 parent entries should be updated and 9 repeating entries should be updated
		self::_check_the_imported_and_updated_numbers( $message );
	}

	function _check_xml_updated_fields() {
		$parent_form_id = FrmForm::getIdByKey( 'all_field_types' );
		$fields = FrmField::get_all_for_form( $parent_form_id, '', 'include', 'include' );

		// 36 fields expected
		$this->assertEquals( 36, count( $fields ), 'Fields were either added or removed on XML import, but they should have been updated.' );
	}

	function _check_xml_updated_repeating_fields( $repeating_field_ids ) {
		$repeating_section_id = FrmField::get_id_by_key( 'repeating-section' );
		$repeating_section = FrmField::getOne( $repeating_section_id );

		$fields = FrmField::get_all_for_form( $repeating_section->field_options['form_select'] );

		// Expected number of fields in repeating form: 3
		$this->assertEquals( 3, count( $fields ), 'Fields in repeating section were either added or deleted when they should have been updated.' );

		// Make sure the same fields are still in the section
		$repeating_field_keys = array( 'repeating-text', 'repeating-checkbox', 'repeating-date' );
		foreach ( $fields as $field ) {
			// Check field key
			$this->assertTrue( in_array( $field->field_key, $repeating_field_keys ), 'A field with the key ' . $field->field_key . ' was created when it should have been upated.' );
			// Check field ID
			$this->assertTrue( in_array( $field->id, $repeating_field_ids ), 'A field with the key ' . $field->field_key . ' and ID ' . $field->id . ' was created when it should have been upated.' );
		}

	}

	function _check_xml_updated_repeating_section() {
		$expected_form_select = FrmForm::getIdByKey( 'rep_sec_form' );
		$repeating_section_id = FrmField::get_id_by_key( 'repeating-section' );
		$repeating_section = FrmField::getOne( $repeating_section_id );

		$this->assertEquals( $expected_form_select, $repeating_section->field_options['form_select'], 'A repeating section\'s form_select was changed on XML import, but it should have remained the same.' );
	}

	function _check_the_imported_and_updated_numbers( $message ) {
		foreach ( $message['imported'] as $type => $number ) {
			$this->assertEquals( 0, $number, $number . ' ' . $type . ' were imported but they should have been updated.' );
		}

		$expected_numbers = array(
			'forms' => 2,
			'fields' => 36,
			'items' => 12
		);

		foreach ( $expected_numbers as $type => $e_number ) {
			$this->assertEquals( $e_number, $message['updated'][ $type ], 'There is a discrepancy between the number of ' . $type . ' expected to be updated vs. the actual number of updated ' . $type . '. Before digging into this, check the $expected_numbers to make sure it is correct.' );
		}
	}

	function _check_xml_updated_repeating_entries() {

	}

}