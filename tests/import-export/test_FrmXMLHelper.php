<?php
/**
 * @group xml
 */
class WP_Test_FrmXMLHelper extends FrmUnitTest {

	function test_imported_repeating_section_form() {
		$expected_parent_id = FrmForm::getIdByKey( $this->all_fields_form_key );
		$repeating_section_form_id = FrmForm::getIdByKey( $this->repeat_sec_form_key );
		$repeating_section_form = FrmForm::getOne( $repeating_section_form_id );

		$this->assertEquals( $expected_parent_id, $repeating_section_form->parent_form_id, 'The parent_form_id was not set properly on an imported repeating section.' );
	}

	function test_imported_fields(){
		$imported_fields = $this->get_all_fields_for_form_key( $this->all_fields_form_key );

		$total_fields_to_test = 2;
		$fields_tested = 0;
		foreach ( $imported_fields as $f ) {
			self::_check_imported_repeating_fields( $f, $fields_tested );
			self::_check_imported_embed_form_fields( $f, $fields_tested );
			self::_check_fields_in_section( $f );
			// Check fields inside repeating section
		}

		$this->assertEquals( $fields_tested, $total_fields_to_test, 'Only ' . $fields_tested . ' fields were tested, but ' . $total_fields_to_test . ' were expected.');
	}

	function _check_imported_repeating_fields( $f, &$fields_tested ){
		if ( ! FrmField::is_repeating_field( $f ) ) {
			return;
		}

		$fields_tested++;

		self::_check_form_select( $f, 'rep_sec_form' );
	}

	function _check_imported_embed_form_fields( $f, &$fields_tested ){
		if ( $f->type != 'form' ) {
			return;
		}

		$fields_tested++;

		self::_check_form_select( $f, $this->contact_form_key );
	}

	/**
	* @covers FrmXMLHelper::track_repeating_fields
	* @covers FrmXMLHelper::update_repeat_field_options
	*/
	function _check_form_select( $f, $expected_form_key ) {
		$this->assertNotEmpty( $f->field_options['form_select'], 'Imported repeating section has a blank form_select.' );

		// Check if the form_select setting matches the correct form
		$nested_form = FrmForm::getOne( $f->field_options['form_select'] );
		$this->assertNotEmpty( $nested_form, 'The form_select in an imported repeating section is not updating correctly.');
		$this->assertEquals( $expected_form_key, $nested_form->form_key, 'The form_select is not updating properly when a repeating section is imported.');
	}

	function _check_fields_in_section( $f ) {
		$fields_in_sections = array(
			'rich-text-field' => 'pro-fields-divider',
			'single-file-upload-field' => 'pro-fields-divider',
			'multi-file-upload-field' => 'pro-fields-divider',
			'number-field' => 'pro-fields-divider',
			'n0d580' => 'pro-fields-divider',
			'time-field' => 'pro-fields-divider',
			'date-field' => 'pro-fields-divider',
			'zwuclz' => 'pro-fields-divider',
			'qbrd2o' => 'pro-fields-divider',
			'repeating-text' => 'repeating-section',
			'repeating-checkbox' => 'repeating-section',
			'repeating-date' => 'repeating-section',
			'contact-subject' => 'email-information-section',
			'contact-message' => 'email-information-section',
		);

		if ( isset( $fields_in_sections[ $f->field_key ] ) ) {
			$expected_id = FrmField::get_id_by_key( $fields_in_sections[ $f->field_key ] );
			$this->assertEquals( $expected_id, $f->field_options['in_section'], 'The in_section variable is not set correctly for the ' . $f->field_key . ' field in a section on import.' );
		} else {
			$this->assertEquals( 0, $f->field_options['in_section'], 'The in_section variable is not set correctly for the ' . $f->field_key . ' field outside of a section on import.' );
		}
	}

	function test_imported_forms() {
		$imported_forms = FrmForm::getAll();

		self::_check_parent_form_id( $imported_forms );
	}

	function _check_parent_form_id( $imported_forms ) {
		$child_form_key = 'rep_sec_form';
		$expected_parent_id = FrmForm::getIdByKey( $this->all_fields_form_key );

		foreach ( $imported_forms as $form ) {
			if ( $form->form_key == $child_form_key ) {
				$this->assertTrue( $form->parent_form_id != 0, 'Parent form ID was removed when ' . $child_form_key . ' form was imported.' );
				$this->assertEquals( $expected_parent_id, $form->parent_form_id, 'The parent form was not updated correctly when the ' . $child_form_key . ' form was imported.' );
			} else {
				$this->assertEquals( 0, $form->parent_form_id, 'Parent form ID was added to ' . $form->form_key . ' on import.' );
			}
		}

	}

	function test_xml_import_to_update_fields_and_forms() {
		$args = self::_get_xml_update_args();
		$path = self::_generate_xml_for_all_fields_form( $args );
		$message = FrmXMLHelper::import_xml( $path );

		self::_check_xml_updated_forms_parent_id( $args );
		self::_check_xml_updated_fields( $args );
		self::_check_xml_updated_repeating_fields( $args );
		self::_check_xml_updated_repeating_section( $args );

		self::_check_the_imported_and_updated_numbers( $message );

		// Delete the temp.XML file
		unlink( $path );
	}

	function _get_xml_update_args() {
		$parent_form_id = FrmForm::getIdByKey( $this->all_fields_form_key );
		$repeating_section_id = FrmField::get_id_by_key( 'repeating-section' );
		$all_fields = FrmField::get_all_for_form( $parent_form_id, '', 'include', 'include' );
		$repeating_section = FrmField::getOne( $repeating_section_id );
		$rep_sec_form = FrmForm::getOne( $repeating_section->field_options['form_select'] );
		$repeating_fields = FrmField::get_all_for_form( $repeating_section->field_options['form_select'] );

		$args = array(
			'parent_form_id' => $parent_form_id,
			'repeating_section' => $repeating_section,
			'all_fields' => $all_fields,
			'rep_sec_form' => $rep_sec_form,
			'repeating_fields' => $repeating_fields
		);

		return $args;
	}

	function _check_xml_updated_forms_parent_id( $args ) {
		$original_parent_id = $args['rep_sec_form']->parent_form_id;
		$new_form = FrmForm::getOne( $args['rep_sec_form']->id );
		$new_parent_id = $new_form->parent_form_id;

		$this->assertEquals( $original_parent_id, $new_parent_id, 'The repeating section form\'s parent ID was modified on XML import when it should not have been updated.' );
	}

	function _check_xml_updated_fields( $args ) {
		$fields = FrmField::get_all_for_form( $args['parent_form_id'], '', 'include', 'include' );

		$this->assertEquals( count( $args['all_fields'] ), count( $fields ), 'Fields were either added or removed on XML import, but they should have been updated.' );
	}

	function _check_xml_updated_repeating_fields( $args ) {
		$fields = FrmField::get_all_for_form( $args['repeating_section']->field_options['form_select'] );

		// Check if the number of fields in repeating form is correct
		$this->assertEquals( count( $args['repeating_fields'] ), count( $fields ), 'Fields in repeating section were either added or deleted when they should have been updated.' );

		// Make sure the same fields are still in the section
		$repeating_field_keys = array( 'repeating-text', 'repeating-checkbox', 'repeating-date' );
		foreach ( $fields as $field ) {
			$this->assertTrue( in_array( $field->field_key, $repeating_field_keys ), 'A field with the key ' . $field->field_key . ' was created when it should have been upated.' );
		}
	}

	function _check_xml_updated_repeating_section( $args ) {
		$expected_form_select = $args['repeating_section']->field_options['form_select'];
		$new_repeating_section = FrmField::getOne( $args['repeating_section']->id );
		$new_form_select = $new_repeating_section->field_options['form_select'];

		$this->assertEquals( $expected_form_select, $new_form_select, 'A repeating section\'s form_select was changed on XML import, but it should have remained the same.' );
	}

	function _check_the_imported_and_updated_numbers( $message ) {
		foreach ( $message['imported'] as $type => $number ) {
			$this->assertEquals( 0, $number, $number . ' ' . $type . ' were imported but they should have been updated.' );
		}

		$expected_numbers = array(
			'forms' => 2,
			'fields' => 37,
		);

		foreach ( $expected_numbers as $type => $e_number ) {
			$this->assertEquals( $e_number, $message['updated'][ $type ], 'There is a discrepancy between the number of ' . $type . ' expected to be updated vs. the actual number of updated ' . $type . '. Before digging into this, check the $expected_numbers to make sure it is correct.' );
		}
	}

	function _generate_xml_for_all_fields_form( $args ) {
		$type = array( 'forms','items','actions' );

		$xml_args = array(
			'ids' => array( $args['parent_form_id'] )
		);

		$path = FrmUnitTest::generate_xml( $type, $xml_args );

		return $path;
	}
}