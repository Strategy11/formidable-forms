<?php
/**
 * @group fields
 */
class test_FrmFieldCheckbox extends FrmUnitTest {

	/**
	 * @covers FrmFieldCheckbox
	 */
	public function test_option_label_does_not_duplicate_for_attribute_when_wrapping_input() {
		$form_id = $this->factory->form->create();
		$field   = $this->factory->field->create_and_get(
			array(
				'type'    => 'checkbox',
				'form_id' => $form_id,
				'options' => array( 'Option 1', 'Option 2' ),
			)
		);

		$field_array  = FrmFieldsHelper::setup_edit_vars( $field );
		$field_object = FrmFieldFactory::get_field_type( 'checkbox', $field_array );

		$html = $field_object->prepare_field_html(
			array(
				'errors' => array(),
				'form'   => FrmForm::getOne( $form_id ),
			)
		);

		$this->assert_label_wraps_input_without_for( $html, 'checkbox option' );
	}
}
