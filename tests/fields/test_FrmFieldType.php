<?php

/**
 * @group fields
 */
class test_FrmFieldType extends FrmUnitTest {

	/**
	 * @covers FrmFieldNumber->add_min_max
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
}
