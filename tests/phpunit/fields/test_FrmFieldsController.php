<?php

/**
 * @group fields
 *
 * @covers FrmFieldsController
 */
#[\PHPUnit\Framework\Attributes\Group( 'fields' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFieldsController::class )]
class test_FrmFieldsController extends FrmUnitTest {

	public function test_builder_batches_omit_only_received_definitions() {
		$definitions                   = array(
			'known' => array( 'value' => 'Saved option' ),
			'new'   => array( 'value' => 'New option' ),
		);
		$_POST['known_select_options'] = 'known,unknown,known';
		$_POST['known_tooltips']       = 'new';

		try {
			$this->assertSame(
				array( 'new' => $definitions['new'] ),
				$this->run_private_method( array( 'FrmFieldsController', 'get_missing_builder_definitions' ), array( $definitions, 'known_select_options' ) )
			);
			$this->assertSame(
				array( 'known' => $definitions['known'] ),
				$this->run_private_method( array( 'FrmFieldsController', 'get_missing_builder_definitions' ), array( $definitions, 'known_tooltips' ) )
			);
		} finally {
			unset( $_POST['known_select_options'], $_POST['known_tooltips'] );
		}
	}

	public function test_builder_batches_keep_definitions_for_older_or_malformed_requests() {
		$definitions = array( 'known' => 'Tooltip text' );
		unset( $_POST['known_tooltips'] );

		try {
			foreach ( array( null, '', array( 'known' ) ) as $known_keys ) {
				if ( null !== $known_keys ) {
					$_POST['known_tooltips'] = $known_keys;
				}
				$this->assertSame(
					$definitions,
					$this->run_private_method( array( 'FrmFieldsController', 'get_missing_builder_definitions' ), array( $definitions, 'known_tooltips' ) )
				);
			}

			$_POST['known_tooltips'] = 'known';
			$this->assertSame(
				array(),
				$this->run_private_method( array( 'FrmFieldsController', 'get_missing_builder_definitions' ), array( $definitions, 'known_tooltips' ) )
			);
		} finally {
			unset( $_POST['known_tooltips'] );
		}
	}

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
		$this->assertSame( '', $placeholder );

		$field['placeholder'] = '0';
		$placeholder          = $this->prepare_placeholder( $field );
		$this->assertSame( '0', $placeholder, '0 is a valid placeholder value.' );

		$field['placeholder'] = '';
		$field['type']        = 'hidden';
		$placeholder          = $this->prepare_placeholder( $field );
		$this->assertSame( '', $placeholder, 'some types of fields are not "is_placeholder_field_type" and should be left empty.' );
	}

	private function prepare_placeholder( $field ) {
		return $this->run_private_method( array( 'FrmFieldsController', 'prepare_placeholder' ), array( $field ) );
	}

	public function test_pull_custom_error_body_from_custom_html() {
		$form       = $this->factory->form->create_and_get();
		$field      = $this->factory->field->create_and_get(
			array(
				'form_id'       => $form->id,
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
		$field      = FrmFieldsHelper::setup_edit_vars( $field );
		$error_body = FrmFieldsController::pull_custom_error_body_from_custom_html( $form, $field );

		$this->assertSame(
			'<div class="frm_error my_custom_error_class" id="frm_error_field_[key]">My custom error label: [error]</div>',
			$error_body
		);
	}

	public function test_include_new_field() {
		$form_id = $this->factory->form->create();
		ob_start();
		$new_field    = FrmFieldsController::include_new_field( 'text', $form_id );
		$field_output = ob_get_clean();

		$this->assertSame( 0, strpos( trim( $field_output ), '<li id="frm_field_id_' . $new_field['id'] . '"' ) );

		// Confirm field is an array with type and form id keys.
		$this->assertIsArray( $new_field );
		$this->assertArrayHasKey( 'type', $new_field );
		$this->assertSame( 'text', $new_field['type'] );
		$this->assertArrayHasKey( 'form_id', $new_field );
		$this->assertEquals( $form_id, $new_field['form_id'] );

		// Confirm new fields are flagged as "draft".
		$this->assertArrayHasKey( 'draft', $new_field );
		$this->assertSame( 1, $new_field['draft'] );
	}

	public function test_add_validation_messages() {
		$form_id  = $this->factory->form->create();
		$field    = $this->factory->field->create_and_get(
			array(
				'form_id' => $form_id,
				'type'    => 'email',
			)
		);
		$field    = FrmFieldsHelper::setup_edit_vars( $field );
		$add_html = array();
		$this->run_private_method( array( 'FrmFieldsController', 'add_validation_messages' ), array( $field, &$add_html ) );
		$this->assertArrayHasKey( 'data-invmsg', $add_html );
		$this->assertArrayNotHasKey( 'data-reqmsg', $add_html );

		$field['required'] = '1';
		$add_html          = array();
		$this->run_private_method( array( 'FrmFieldsController', 'add_validation_messages' ), array( $field, &$add_html ) );
		$this->assertArrayHasKey( 'data-reqmsg', $add_html );

		$field['type'] = 'hidden';
		$add_html      = array();
		$this->run_private_method( array( 'FrmFieldsController', 'add_validation_messages' ), array( $field, &$add_html ) );
		$this->assertArrayNotHasKey( 'data-invmsg', $add_html );
		$this->assertArrayNotHasKey( 'data-reqmsg', $add_html );
	}
}
