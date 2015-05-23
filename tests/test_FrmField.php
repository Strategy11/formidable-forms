<?php

/**
 * @group fields
 */
class WP_Test_FrmField extends FrmUnitTest {
	function test_create() {
		$form_id = $this->factory->form->get_id_by_key( 'contact-db12' );
		$field_types = array_merge( FrmFieldsHelper::field_selection(), FrmFieldsHelper::pro_field_selection() );
		foreach ( $field_types as $field_type => $field_info ) {
			$field_id = $this->factory->field->create( array( 'type' => $field_type, 'form_id' => $form_id ) );
            $this->assertTrue( is_numeric( $field_id ) );
            $this->assertTrue( $field_id > 0 );
		}
	}

	/**
	 * @covers FrmField::getAll
	 */
	function test_getAll() {
		$form_id = $this->factory->form->get_id_by_key( $this->contact_form_key );
		$fields = FrmField::getAll( array( 'fi.form_id' => (int) $form_id ) );
		$this->assertNotEmpty( $fields );
		$this->assertTrue( count( $fields ) >= 7 );

		foreach ( $fields as $field ) {
			
		}
	}

	/**
	 * @covers FrmField::get_all_for_form
	 */
	function test_get_all_for_form() {
		$form_id = FrmForm::getIdByKey( $this->contact_form_key );
		$fields = $this->factory->field->get_fields_from_form( $form_id );
		$this->assertNotEmpty( $fields );
	}
}