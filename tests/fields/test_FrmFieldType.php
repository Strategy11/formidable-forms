<?php

/**
 * @group fields
 */
class test_FrmFieldType extends FrmUnitTest {

	/**
	 * @covers FrmFieldNumber::add_min_max
	 */
	public function test_html_min_number() {
		$form_id = $this->factory->form->create();
		$field   = $this->factory->field->create_and_get(
			array(
				'type'    => 'number',
				'form_id' => $form_id,
				'field_options' => array(
					'minnum' => 10,
					'maxnum' => 999,
					'step'   => 'any',
				),
			)
		);
		$this->assertNotEmpty( $field );

		$form = FrmFormsController::get_form_shortcode(
			array(
				'id' => $form_id,
			)
		);
		$this->assertNotFalse( strpos( $form, ' min="10"' ) );
		$this->assertNotFalse( strpos( $form, ' max="999"' ) );
		$this->assertNotFalse( strpos( $form, ' step="any"' ) );
	}

	/**
	 * @covers FrmFieldType::sanitize_value
	 */
	public function test_sanitize_value() {
		$frm_field_type = new FrmFieldDefault();

		$values = array(
			array(
				'type'     => 'default',
				'value'    => '<script></script>test',
				'expected' => 'test',
			),
			array(
				'type'     => 'text',
				'value'    => '1 > 2',
				'expected' => '1 > 2',
			),
			array(
				'type'     => 'textarea',
				'value'    => '<div class="here"></div>',
				'expected' => '<div class="here"></div>',
			),
			array(
				'type'     => 'text',
				'value'    => 'test > with \' < & characters "like “ and ‘ this',
				'expected' => 'test > with \' < & characters "like “ and ‘ this',
			),
			array(
				'type'     => 'text',
				'value'    => '&lt;span&gt;2 < 1&lt;/span&gt;',
				'expected' => '&lt;span>2 < 1&lt;/span>',
			),
			array(
				'type'     => 'email',
				'value'    => 'johndoe@yahoo.co.uk',
				'expected' => 'johndoe@yahoo.co.uk',
			),
			array(
				'type'     => 'select',
				'value'    => 'Option 1',
				'expected' => 'Option 1',
			),
			array(
				'type'     => 'url',
				'value'    => 'https://team.strategy11.com/?foo=bar&baz=bam',
				'expected' => 'https://team.strategy11.com/?foo=bar&baz=bam',
			),
			array(
				'type'     => 'default',
				'value'    => array(
					'<script></script>test',
					'another test',
				),
				'expected' => array(
					'test',
					'another test',
				),
			),
			array(
				'type'     => 'phone',
				'value'    => array(
					'(555) 555-1234',
					'1-541-754-3010',
					'+1(408) 785-9969',
				),
				'expected' => array(
					'(555) 555-1234',
					'1-541-754-3010',
					'+1(408) 785-9969',
				),
			),
			array(
				'type'     => 'number',
				'value'    => array(
					'1009',
					'1.5',
				),
				'expected' => array(
					'1009',
					'1.5',
				),
			),
			array(
				'type'     => 'user_id',
				'value'    => array(
					'6',
					'2a',
					'a1',
				),
				'expected' => array(
					'6',
					'2',
					'0',
				),
			),
		);
		foreach ( $values as $value ) {
			$frm_field_type = FrmFieldFactory::get_field_type( $value['type'] );
			$frm_field_type->sanitize_value( $value['value'] );
			$this->assertEquals( $value['expected'], $value['value'] );
		}
	}

	/**
	 * @covers FrmFieldType::get_import_value
	 */
	public function test_get_import_value() {
		$field = new stdClass();
		$field->type = 'checkbox';
		$field->options = array(
			array(
				'value' => 'a',
				'label' => 'A',
			),
			array(
				'value' => 'b',
				'label' => 'B',
			),
			array(
				'value' => 'c',
				'label' => 'C',
			),
			array(
				'value' => 'a,b',
				'label' => 'A, B',
			),
			array(
				'value' => 'a,b,c',
				'label' => 'A, B, C',
			),
			array(
				'value' => 'a, b, c',
				'label' => 'A, B, C',
			),
		);

		$checkbox = FrmFieldFactory::get_field_type( 'checkbox', $field );

		$this->assertEquals( $checkbox->get_import_value( 'a,b' ), 'a,b' );
		$this->assertEquals( $checkbox->get_import_value( 'a,c' ), array( 'a', 'c' ) );
		$this->assertEquals( $checkbox->get_import_value( 'a,b,c' ), 'a,b,c' );
	}

	/**
	 * @covers FrmFieldType::is_not_unique
	 */
	public function test_is_not_unique() {

		$form_id = $this->factory->form->create();
		$field1 = $this->factory->field->create_and_get(
			array(
				'type'    => 'number',
				'form_id' => $form_id,
			)
		);

		$field_object1 = FrmFieldFactory::get_field_type( 'text', $field1 );
		$entry_id     = 0;

		$this->assertFalse( $field_object1->is_not_unique( 'First', $entry_id ), 'the first iteration of a new value should be flagged as okay' );
		$this->assertTrue( $field_object1->is_not_unique( 'First', $entry_id ), 'the second iteration of a new value should should be flagged as a duplicate' );

		$this->assertFalse( $field_object1->is_not_unique( 'Second', $entry_id ), 'the first iteration of a second new value should be flagged as okay' );
		$this->assertFalse( $field_object1->is_not_unique( 'Third', $entry_id ), 'the first iteration of a third new value should be flagged as okay' );

		$this->assertTrue( $field_object1->is_not_unique( 'Third', $entry_id ) );
		$this->assertTrue( $field_object1->is_not_unique( 'Second', $entry_id ) );

		$field_object2 = FrmFieldFactory::get_field_type( 'text', $field1 );
		$this->assertTrue( $field_object2->is_not_unique( 'First', $entry_id ), 'another field object for the same field should also be flagging a duplicate' );

		$field2 = $this->factory->field->create_and_get(
			array(
				'type'    => 'number',
				'form_id' => $form_id,
			)
		);
		$field_object3 = FrmFieldFactory::get_field_type( 'text', $field2 );

		$this->assertFalse( $field_object3->is_not_unique( 'First', $entry_id ), 'a field object for another field should not flag a duplicate' );
	}
}
