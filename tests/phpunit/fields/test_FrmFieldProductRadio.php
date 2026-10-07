<?php
/**
 * @group fields
 */
class test_FrmFieldProductRadio extends FrmUnitTest {

	/**
	 * @covers FrmFieldProduct
	 */
	public function test_option_label_does_not_duplicate_for_attribute_when_wrapping_input() {
		$form_id = $this->factory->form->create();
		$field   = $this->factory->field->create_and_get(
			array(
				'type'          => 'product',
				'form_id'       => $form_id,
				'options'       => array( 'Product 1', 'Product 2' ),
				'field_options' => array( 'data_type' => 'radio' ),
			)
		);

		$field_array  = FrmFieldsHelper::setup_edit_vars( $field );
		$field_object = FrmFieldFactory::get_field_type( 'product', $field_array );

		$html = $field_object->prepare_field_html(
			array(
				'errors' => array(),
				'form'   => FrmForm::getOne( $form_id ),
			)
		);

		$this->assertMatchesRegularExpression(
			'/<label[^>]*>\s*<input type="radio"/',
			$html,
			'Expected the product-radio option label to wrap the input'
		);
		$this->assertDoesNotMatchRegularExpression(
			'/<label[^>]*\sfor="[^"]*"[^>]*>\s*<input type="radio"/',
			$html,
			'The product-radio option label should not also carry a for attribute when it already wraps the input -- Safari VoiceOver double-announces it'
		);
	}
}
