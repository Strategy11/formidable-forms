<?php
/**
 * @group xml
 */
class WP_Test_FrmXMLHelper extends FrmUnitTest {

	public function test_imported_fields(){
		$imported_fields = $this->get_all_fields_for_form_key( $this->all_fields_form_key );

		$total_fields_to_test = 2;
		$fields_tested = 0;
		foreach ( $imported_fields as $f ) {
			self::_check_imported_repeating_fields( $f, $fields_tested );
			self::_check_imported_embed_form_fields( $f, $fields_tested );
		}

		$this->assertEquals( $fields_tested, $total_fields_to_test, 'Only ' . $fields_tested . ' fields were tested, but ' . $total_fields_to_test . ' were expected.');
	}

	public function _check_imported_repeating_fields( $f, &$fields_tested ){
		if ( ! FrmField::is_repeating_field( $f ) ) {
			return;
		}

		$fields_tested++;

		self::_check_form_select( $f, 'rep_sec_form' );
	}

	public function _check_imported_embed_form_fields( $f, &$fields_tested ){
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
	public function _check_form_select( $f, $expected_form_key ) {
		$this->assertNotEmpty( $f->field_options['form_select'], 'Imported repeating section has a blank form_select.' );

		// Check if the form_select setting matches the correct form
		$nested_form = FrmForm::getOne( $f->field_options['form_select'] );
		$this->assertNotEmpty( $nested_form, 'The form_select in an imported repeating section is not updating correctly.');
		$this->assertEquals( $expected_form_key, $nested_form->form_key, 'The form_select is not updating properly when a repeating section is imported.');
	}
}