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

	/**
	 * @covers FrmFieldsController::parse_bulk_edit_opts
	 */
	public function test_parse_bulk_edit_opts_drops_blank_lines() {
		// A blank line (or one that is only whitespace) must be dropped, not
		// kept as an option with an empty string value - an empty value
		// collides with an unset field value in FrmAppHelper::check_selected()
		// and renders as selected by default (formidable-pro#3385).
		$opts = $this->parse_bulk_edit_opts( "One\n\nTwo\n   \nThree", false );

		$this->assertSame( array( 'One', 'Two', 'Three' ), $opts );
	}

	public function test_parse_bulk_edit_opts_keeps_zero_value() {
		// '0' is falsy but a valid option value - only truly blank lines drop.
		$opts = $this->parse_bulk_edit_opts( "0\nOne", false );

		$this->assertSame( array( '0', 'One' ), $opts );
	}

	public function test_parse_bulk_edit_opts_keeps_leading_blank_when_flagged() {
		// $keep_leading_blank is the caller's decision (select field with a
		// placeholder configured - see select_has_placeholder()) that a
		// blank first line is a legitimate manual placeholder option rather
		// than the bug this method otherwise drops blank lines for.
		$opts = $this->parse_bulk_edit_opts( "\nOne\n\nTwo", true );

		$this->assertSame( array( '', 'One', 'Two' ), $opts );
	}

	public function test_parse_bulk_edit_opts_drops_leading_blank_when_not_flagged() {
		$opts = $this->parse_bulk_edit_opts( "\nOne\nTwo", false );
		$this->assertSame( array( 'One', 'Two' ), $opts );
	}

	/**
	 * @param string $opts
	 * @param bool   $keep_leading_blank
	 */
	private function parse_bulk_edit_opts( $opts, $keep_leading_blank ) {
		return $this->run_private_method( array( 'FrmFieldsController', 'parse_bulk_edit_opts' ), array( $opts, $keep_leading_blank ) );
	}

	/**
	 * @covers FrmFieldsController::remove_blank_separated_values
	 */
	public function test_remove_blank_separated_values_drops_blank_value() {
		// A "label|" line with nothing after the separator produces a
		// blank value half, the same collision as a blank textarea line
		// (formidable-pro#3385), just reached via separate-value mode.
		$opts = $this->remove_blank_separated_values(
			array(
				array(
					'label' => 'One',
					'value' => '1',
				),
				array(
					'label' => 'Blank',
					'value' => '',
				),
				'Two',
			)
		);

		$this->assertSame(
			array(
				array(
					'label' => 'One',
					'value' => '1',
				),
				'Two',
			),
			$opts
		);
	}

	public function test_remove_blank_separated_values_keeps_blank_label_with_real_value() {
		// A "|value" line with nothing before the separator has a blank
		// label but a real value - no collision with an unset field value
		// (FrmAppHelper::check_selected() only ever compares the value
		// half), and dropdown-field.php renders a blank label as a real,
		// selectable option, so this is left alone.
		$opts = $this->remove_blank_separated_values(
			array(
				array(
					'label' => 'One',
					'value' => '1',
				),
				array(
					'label' => '',
					'value' => 'no-label',
				),
			)
		);

		$this->assertSame(
			array(
				array(
					'label' => 'One',
					'value' => '1',
				),
				array(
					'label' => '',
					'value' => 'no-label',
				),
			),
			$opts
		);
	}

	public function test_remove_blank_separated_values_keeps_leading_blank_pair_when_flagged() {
		// A "|" line (blank label and blank value) at position 0 is the
		// separate-value equivalent of parse_bulk_edit_opts()'s leading
		// blank line - kept only when $keep_leading_blank says so.
		$opts = $this->remove_blank_separated_values(
			array(
				array(
					'label' => '',
					'value' => '',
				),
				array(
					'label' => 'Yes',
					'value' => '1',
				),
			),
			true
		);

		$this->assertSame(
			array(
				array(
					'label' => '',
					'value' => '',
				),
				array(
					'label' => 'Yes',
					'value' => '1',
				),
			),
			$opts
		);
	}

	public function test_remove_blank_separated_values_drops_leading_blank_pair_when_not_flagged() {
		$opts = $this->remove_blank_separated_values(
			array(
				array(
					'label' => '',
					'value' => '',
				),
				array(
					'label' => 'Yes',
					'value' => '1',
				),
			)
		);

		$this->assertSame(
			array(
				array(
					'label' => 'Yes',
					'value' => '1',
				),
			),
			$opts
		);
	}

	/**
	 * @param array $opts
	 * @param bool  $keep_leading_blank
	 */
	private function remove_blank_separated_values( $opts, $keep_leading_blank = false ) {
		return $this->run_private_method( array( 'FrmFieldsController', 'remove_blank_separated_values' ), array( $opts, $keep_leading_blank ) );
	}

	/**
	 * @covers FrmFieldsController::select_has_placeholder
	 */
	public function test_select_has_placeholder_true_when_placeholder_set() {
		$this->assertTrue( $this->select_has_placeholder( array( 'placeholder' => 'Choose one' ) ) );
	}

	public function test_select_has_placeholder_false_when_no_placeholder() {
		$this->assertFalse( $this->select_has_placeholder( array( 'placeholder' => '' ) ) );
	}

	/**
	 * @param array $field
	 */
	private function select_has_placeholder( $field ) {
		return $this->run_private_method( array( 'FrmFieldsController', 'select_has_placeholder' ), array( $field ) );
	}

	/**
	 * @covers FrmFieldsController::pull_custom_error_body_from_custom_html
	 */
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

	/**
	 * @covers FrmFieldsController::include_new_field
	 */
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

	/**
	 * @covers FrmFieldsController::add_validation_messages
	 */
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
