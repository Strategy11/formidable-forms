<?php
/**
 * @group fields
 */
class test_FrmFieldGdpr extends FrmUnitTest {

	/**
	 * @covers FrmFieldGdpr
	 */
	public function test_label_does_not_duplicate_for_attribute_when_wrapping_input() {
		$frm_settings              = FrmAppHelper::get_settings();
		$original_enable_gdpr      = $frm_settings->enable_gdpr;
		$frm_settings->enable_gdpr = true;

		try {
			$form_id = $this->factory->form->create();
			$field   = $this->factory->field->create_and_get(
				array(
					'type'    => 'gdpr',
					'form_id' => $form_id,
				)
			);

			$field_array  = FrmFieldsHelper::setup_edit_vars( $field );
			$field_object = FrmFieldFactory::get_field_type( 'gdpr', $field_array );

			$html = $field_object->prepare_field_html(
				array(
					'errors' => array(),
					'form'   => FrmForm::getOne( $form_id ),
				)
			);
		} finally {
			$frm_settings->enable_gdpr = $original_enable_gdpr;
		}

		$this->assert_label_wraps_input_without_for( $html, 'GDPR' );
	}
}
