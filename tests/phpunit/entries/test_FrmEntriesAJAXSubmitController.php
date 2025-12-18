<?php

/**
 * @group entries
 */
class test_FrmEntriesAJAXSubmitController extends FrmUnitTest {

	public $factory;
	/**
	 * @covers FrmEntriesAJAXSubmitController::maybe_modify_ajax_error
	 */
	public function test_maybe_modify_ajax_error() {
		$error    = 'This field cannot be blank.';
		$form     = $this->factory->form->create_and_get();
		$field_id = $this->factory->field->create(
			array(
				'form_id'       => $form->id,
				'field_key'     => 'modify_ajax_error_test_key',
				'type'          => 'text',
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
		$this->assertEquals(
			'<div class="frm_error my_custom_error_class" id="frm_error_field_modify_ajax_error_test_key">My custom error label: This field cannot be blank.</div>',
			$this->maybe_modify_ajax_error( $error, $field_id, $form )
		);
	}

	private function maybe_modify_ajax_error( $error, $field_id, $form, $errors = array() ) {
		return $this->run_private_method( array( 'FrmEntriesAJAXSubmitController', 'maybe_modify_ajax_error' ), array( $error, $field_id, $form, $errors ) );
	}
}
