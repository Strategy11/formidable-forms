<?php

/**
 * @group fields
 */
class test_FrmFieldType extends FrmUnitTest {

	/**
	 * @covers FrmFieldNumber::add_min_max
	 */
	function test_html_min_number() {
		$form_id = $this->factory->form->create();
		$field = $this->factory->field->create_and_get( array(
			'type'    => 'number',
			'form_id' => $form_id,
			'field_options' => array(
				'minnum' => 10,
				'maxnum' => 999,
				'step'   => 'any',
			),
		) );
		$this->assertNotEmpty( $field );
		
		$form = FrmFormsController::get_form_shortcode( array(
			'id' => $form_id,
		) );
		$this->assertContains( ' min="10"', $form );
		$this->assertContains( ' max="999"', $form );
		$this->assertContains( ' step="any"', $form );
	}

	/**
	 * @covers FrmFieldType::sanitize_value
	 */
	function test_sanitize_value() {
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
					'a1'
				),
				'expected' => array(
					'6',
					'2',
					'0'
				),
			),
		);
		foreach ( $values as $value ) {
			$frm_field_type = FrmFieldFactory::get_field_type( $value['type'] );
			$frm_field_type->sanitize_value( $value['value'] );
			$this->assertEquals( $value['expected'], $value['value'] );
		}
	}
}
