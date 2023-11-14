<?php

/**
 * @group fields
 */
class test_FrmFieldsController extends FrmUnitTest {

	/**
	 * @covers FrmFieldsController::prepare_placeholder
	 */
	public function test_prepare_placeholder() {
		$name        = 'Number';
		$field       = array(
			'type'        => 'number',
			'placeholder' => '',
			'label'       => 'inside',
			'name'        => $name,
			'required'    => 0,
		);
		$placeholder = $this->prepare_placeholder( $field );

		// Since Floating labels, placeholder is not replaced by field name anymore.
		$this->assertEquals( '', $placeholder );

		$field['placeholder'] = '0';
		$placeholder          = $this->prepare_placeholder( $field );
		$this->assertEquals( '0', $placeholder, '0 is a valid placeholder value.' );

		$field['placeholder'] = '';
		$field['type']        = 'hidden';
		$placeholder          = $this->prepare_placeholder( $field );
		$this->assertEquals( '', $placeholder, 'some types of fields are not "is_placeholder_field_type" and should be left empty.' );
	}

	private function prepare_placeholder( $field ) {
		return $this->run_private_method( array( 'FrmFieldsController', 'prepare_placeholder' ), array( $field ) );
	}

	/**
	 * @covers FrmFieldsController::pull_custom_error_body_from_custom_html
	 */
	public function test_pull_custom_error_body_from_custom_html() {
		$form       = $this->factory->form->create_and_get();
		$field      = $this->factory->field->create_and_get(
			array(
				'form_id' => $form->id,
				'type'    => 'text',
				'field_options' => array(
					'custom_html' => '
						<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
						<label for="field_[key]" id="field_[key]_label" class="frm_primary_label">[field_name]
							<span class="frm_required" aria-hidden="true">[required_label]</span>
						</label>
						[input]
						[if description]<div class="frm_description" id="frm_desc_field_[key]">[description]</div>[/if description]
						[if error]<div class="frm_error my_custom_error_class" id="frm_error_field_[key]">My custom error label: [error]</div>[/if error]
					</div>
					',
				),
			)
		);
		$field      = FrmFieldsHelper::setup_edit_vars( $field );
		$error_body = FrmFieldsController::pull_custom_error_body_from_custom_html( $form, $field );

		$this->assertEquals(
			'<div class="frm_error my_custom_error_class" id="frm_error_field_[key]">My custom error label: [error]</div>',
			$error_body
		);
	}
}
