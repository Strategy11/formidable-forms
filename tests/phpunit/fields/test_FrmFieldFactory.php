<?php

/**
 * @group fields
 */
class test_FrmFieldFactory extends FrmUnitTest {

	/**
	 * @covers FrmFieldFactory::get_field_object
	 */
	public function test_get_field_object_with_invalid_id_returns_default_field_type() {
		$field_object = FrmFieldFactory::get_field_object( 0 );
		$this->assertInstanceOf( 'FrmFieldDefault', $field_object );
	}

	/**
	 * @covers FrmFieldFactory::get_field_object
	 */
	public function test_get_field_object_with_valid_field_returns_matching_type() {
		$form_id = $this->factory->form->create();
		$field   = $this->factory->field->create_and_get(
			array(
				'type'    => 'text',
				'form_id' => $form_id,
			)
		);

		$field_object = FrmFieldFactory::get_field_object( $field->id );
		$this->assertInstanceOf( 'FrmFieldText', $field_object );
	}
}
