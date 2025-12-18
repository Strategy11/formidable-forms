<?php

/**
 * @group fields
 */
class test_FrmFieldName extends FrmUnitTest {

	public $factory;
	public function test_get_processed_sub_fields() {
		$field = $this->factory->field->create_and_get(
			array(
				'type'    => 'name',
				'form_id' => 1,
			)
		);

		$field->field_options['name_layout'] = 'first_middle_last';

		$name_field = new FrmFieldName( $field );

		$processed_sub_fields = $this->run_private_method( array( $name_field, 'get_processed_sub_fields' ) );

		$this->assertEquals( array( 'first', 'middle', 'last' ), array_keys( $processed_sub_fields ) );
		$this->assertNotFalse( strpos( $processed_sub_fields['first']['wrapper_classes'], 'frm4' ) );
		$this->assertNotFalse( strpos( $processed_sub_fields['middle']['wrapper_classes'], 'frm4' ) );
		$this->assertNotFalse( strpos( $processed_sub_fields['last']['wrapper_classes'], 'frm4' ) );
	}
}
