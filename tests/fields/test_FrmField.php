<?php

/**
 * @group fields
 */
class WP_Test_FrmField extends FrmUnitTest {
	function test_create() {
		$form_id = $this->factory->form->get_id_by_key( 'contact-db12' );
		$field_types = array_merge( FrmField::field_selection(), FrmField::pro_field_selection() );
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
		$forms = array(
			$this->contact_form_key => 8,
			$this->all_fields_form_key => 34,
		);

		foreach ( $forms as $form_key => $expected_count ) {
			$form_id = $this->factory->form->get_id_by_key( $form_key );
			$fields = FrmField::getAll( array( 'fi.form_id' => (int) $form_id ) );
			$this->assertNotEmpty( $fields );
			$this->assertEquals( $expected_count, count( $fields ), 'An incorrect number of fields are retrieved with FrmField::getAll.' );
		}
	}

	/**
	 * @covers FrmField::get_all_for_form
	 */
	function test_get_all_for_form() {
		$forms = array(
			'basic_test' => array( 'form_key' => $this->contact_form_key, 'count' => 8 ),
			'repeat' => array( 'form_key' => $this->all_fields_form_key, 'count' => 34 + 3 ),
			'no_repeat_or_embed' => array( 'form_key' => $this->all_fields_form_key, 'count' => 34 ),
			'repeat_and_embed' => array( 'form_key' => $this->all_fields_form_key, 'count' => 34 + 3 + 8 )
		);

		foreach ( $forms as $test => $args ) {
			$form_id = FrmForm::getIdByKey( $args['form_key'] );

			if ( $test == 'no_repeat_or_embed' ) {
				$fields = FrmField::get_all_for_form( $form_id, '', 'exclude', 'exclude' );
			} else if ( $test == 'repeat_and_embed' ) {
				$fields = FrmField::get_all_for_form( $form_id, '', 'include', 'include' );
			} else {
				$fields = FrmField::get_all_for_form( $form_id );
			}

			$this->assertNotEmpty( $fields );
			$this->assertEquals( $args['count'], count( $fields ), 'An incorrect number of fields are retrieved with FrmField::get_all_for_form.' );
		}
	}
}