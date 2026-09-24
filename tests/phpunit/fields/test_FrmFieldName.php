<?php

/**
 * @group fields
 */
class test_FrmFieldName extends FrmUnitTest {

	public function test_get_processed_sub_fields() {
		$field = $this->factory->field->create_and_get(
			array(
				'type'    => 'name',
				'form_id' => 1,
			)
		);

		$field->field_options['name_layout'] = 'first_middle_last';

		$name_field           = new FrmFieldName( $field );
		$processed_sub_fields = $this->run_private_method( array( $name_field, 'get_processed_sub_fields' ) );

		$this->assertSame( array( 'first', 'middle', 'last' ), array_keys( $processed_sub_fields ) );
		$this->assertStringContainsString( 'frm4', $processed_sub_fields['first']['wrapper_classes'] );
		$this->assertStringContainsString( 'frm4', $processed_sub_fields['middle']['wrapper_classes'] );
		$this->assertStringContainsString( 'frm4', $processed_sub_fields['last']['wrapper_classes'] );
	}

	/**
	 * A required name with only some sub fields missing names each one in its own error, and one
	 * with every sub field missing gets a single error for the whole field.
	 *
	 * @covers FrmFieldCombo::validate
	 */
	public function test_validate_required_sub_field_errors() {
		$field = $this->factory->field->create_and_get(
			array(
				'type'          => 'name',
				'form_id'       => 1,
				'required'      => 1,
				'field_options' => array(
					'first_desc' => 'First Name',
					'last_desc'  => 'Last Name',
				),
			)
		);

		$name_field = new FrmFieldName( $field );
		$error_key  = 'field' . $field->id;

		$errors = $name_field->validate(
			array(
				'id'    => $field->id,
				'value' => array( 'first' => 'Ann' ),
			)
		);

		$this->assertArrayNotHasKey( $error_key, $errors );
		$this->assertArrayNotHasKey( $error_key . '-first', $errors );
		$this->assertStringContainsString( 'Last Name', $errors[ $error_key . '-last' ] );

		$errors = $name_field->validate(
			array(
				'id'    => $field->id,
				'value' => array(),
			)
		);

		$this->assertSame( FrmFieldsHelper::get_error_msg( $field, 'blank' ), $errors[ $error_key ] );
		$this->assertSame( '', $errors[ $error_key . '-first' ] );
		$this->assertSame( '', $errors[ $error_key . '-last' ] );
	}
}
