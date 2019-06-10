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
				'value'    => '<script></script>test',
				'expected' => 'test',
			),
			array(
				'value'    => '1 > 2',
				'expected' => '1 > 2',
			),
			array(
				'value'    => '<div class="here"></div>',
				'expected' => '<div class="here"></div>',
			),
			array(
				'value'    => 'Dolce & Gabbana',
				'expected' => 'Dolce & Gabbana',
			),
			array(
				'value'    => array(
					'<script></script>test',
					'another test',
				),
				'expected' => array(
					'test',
					'another test',
				),
			),
		);
		foreach ( $values as $value ) {
			$frm_field_type->sanitize_value( $value['value'] );
			$this->assertEquals( $value['expected'], $value['value'] );
		}
	}
}
