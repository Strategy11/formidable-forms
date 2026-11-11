<?php

/**
 * @covers FrmFieldPlacementHelper
 * @covers FrmField
 */
class test_FrmFieldPlacement extends FrmUnitTest {

	public function test_repeater_rejects_incompatible_creates_and_moves() {
		$parent   = $this->factory->form->create();
		$repeater = $this->factory->form->create( array( 'parent_form_id' => $parent ) );
		$types    = array( 'captcha', 'submit', 'gdpr', 'summary', 'break', 'divider', 'form', 'tabs', 'accordion' );

		foreach ( $types as $type ) {
			$values = FrmFieldsHelper::setup_new_vars( $type, $repeater );
			$this->assertFalse( FrmField::create( $values ), $type . ' must not be created in a repeater.' );

			$values['form_id'] = $parent;
			$field_id          = FrmField::create( $values );
			$this->assertNotFalse( $field_id, $type . ' should be allowed at the top level.' );
			$this->assertFalse( FrmField::update( $field_id, array( 'form_id' => $repeater ) ), $type . ' must not move into a repeater.' );
			$this->assertSame( (int) $parent, (int) FrmField::getOne( $field_id )->form_id, 'A rejected move must preserve its original form.' );
		}
	}

	public function test_section_rejects_incompatible_fields() {
		$form_id = $this->factory->form->create();
		$section = FrmField::create( FrmFieldsHelper::setup_new_vars( 'divider', $form_id ) );

		foreach ( array( 'break', 'submit', 'divider', 'form' ) as $type ) {
			$values                                = FrmFieldsHelper::setup_new_vars( $type, $form_id );
			$values['field_options']['in_section'] = $section;
			$this->assertFalse( FrmField::create( $values ), $type . ' must not be created inside a regular section.' );
			$this->assertFalse( FrmFieldPlacementHelper::is_allowed( $type, $form_id, $section ), 'The placement policy must reject ' . $type . '.' );
		}
	}

	public function test_repeater_destination_is_checked_when_parent_form_is_passed() {
		$form_id                           = $this->factory->form->create();
		$values                            = FrmFieldsHelper::setup_new_vars( 'divider', $form_id );
		$values['field_options']['repeat'] = 1;
		$section                           = FrmField::create( $values );

		$this->assertFalse(
			FrmFieldPlacementHelper::is_allowed( 'captcha', $form_id, $section ),
			'A repeater section must be detected when the caller passes the parent form ID.'
		);
		$this->assertFalse( FrmFieldPlacementHelper::is_allowed( 'divider|repeat', $form_id, $section ), 'Palette repeater variants must not be nested.' );
	}

	public function test_normal_inputs_and_end_markers_remain_allowed() {
		$parent   = $this->factory->form->create();
		$repeater = $this->factory->form->create( array( 'parent_form_id' => $parent ) );

		foreach ( array( 'text', 'email', 'hidden', 'user_id', 'html', 'end_divider', 'product', 'quantity', 'total' ) as $type ) {
			$values = FrmFieldsHelper::setup_new_vars( $type, $repeater );
			$this->assertNotFalse( FrmField::create( $values ), $type . ' should remain valid inside a repeater.' );
		}
	}

	public function test_changing_a_repeater_input_to_captcha_is_rejected() {
		$parent   = $this->factory->form->create();
		$repeater = $this->factory->form->create( array( 'parent_form_id' => $parent ) );
		$field_id = FrmField::create( FrmFieldsHelper::setup_new_vars( 'text', $repeater ) );

		$this->assertFalse( FrmField::update( $field_id, array( 'type' => 'captcha' ) ), 'Changing the type must enforce the existing placement.' );
		$this->assertSame( 'text', FrmField::getOne( $field_id )->type, 'A rejected type change must preserve the original input.' );
	}

	public function test_replacing_options_can_move_a_field_out_of_a_section() {
		$form_id                               = $this->factory->form->create();
		$section                               = FrmField::create( FrmFieldsHelper::setup_new_vars( 'divider', $form_id ) );
		$values                                = FrmFieldsHelper::setup_new_vars( 'text', $form_id );
		$values['field_options']['in_section'] = $section;
		$field_id                              = FrmField::create( $values );
		$result                                = FrmField::update(
			$field_id,
			array(
				'type'          => 'submit',
				'field_options' => array(),
			)
		);

		$this->assertNotFalse( $result, 'Replacing options can remove section membership before changing the field type.' );
		$this->assertSame( 'submit', FrmField::getOne( $field_id )->type, 'A Submit field must remain allowed outside sections.' );
	}

	public function test_ability_rejects_before_creation_filters_run() {
		$parent   = $this->factory->form->create();
		$repeater = $this->factory->form->create_and_get( array( 'parent_form_id' => $parent ) );
		$before   = did_filter( 'frm_before_field_created' );
		$result   = FrmAbilitiesFieldsController::prepare_new_field( array( 'type' => 'summary' ), $repeater );

		$this->assertWPError( $result, 'An incompatible field must return an error.' );
		$this->assertSame( 'frm_invalid_field_placement', $result->get_error_code(), 'The error must identify the placement problem.' );
		$this->assertSame( $before, did_filter( 'frm_before_field_created' ), 'Invalid placement must be rejected before hooks can create related fields.' );
	}

	public function test_addon_policy_is_used_by_storage_and_builder() {
		$parent   = $this->factory->form->create();
		$repeater = $this->factory->form->create( array( 'parent_form_id' => $parent ) );
		$filter   = static function ( $class, $type ) {
			return 'placement_test' === $type ? FrmPlacementTestField::class : $class;
		};
		add_filter( 'frm_get_field_type_class', $filter, 10, 2 );

		try {
			$this->assertFalse( FrmFieldPlacementHelper::is_allowed( 'placement_test', $repeater ), 'An add-on can opt out of repeaters.' );
			$field = FrmFieldFactory::get_field_type( 'placement_test' );
			$this->assertStringContainsString( 'frm-no-repeater', $field->form_builder_classes( 'placement_test' ), 'Saved and deferred fields must carry the same restriction.' );
			ob_start();
			FrmFieldsHelper::show_add_field_link(
				array(
					'key'  => 'placement_test',
					'name' => 'Restricted field',
					'icon' => 'frmfont frm_text_icon',
				)
			);
			$html = ob_get_clean();
			$this->assertStringContainsString( 'frm-no-repeater', $html, 'The palette button must carry the add-on restriction.' );
		} finally {
			remove_filter( 'frm_get_field_type_class', $filter, 10 );
		}
	}
}

class FrmPlacementTestField extends FrmFieldText {
	protected $allow_in_repeater = false;
}
