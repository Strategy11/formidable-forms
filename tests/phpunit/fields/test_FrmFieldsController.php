<?php

/**
 * @group fields
 *
 * @covers FrmFieldsController
 */
#[\PHPUnit\Framework\Attributes\Group( 'fields' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFieldsController::class )]
class test_FrmFieldsController extends FrmUnitTest {

	public function test_deferred_builder_settings_keep_preview_and_layout_inputs_available() {
		$form_id = $this->factory->form->create();

		foreach ( array( 'text', 'number', 'radio', 'select' ) as $type ) {
			$field    = $this->factory->field->create_and_get(
				array(
					'form_id'       => $form_id,
					'type'          => $type,
					'field_order'   => 12,
					'field_options' => array( 'classes' => 'frm_half custom_class' ),
				)
			);
			$settings = (object) array( 'html' => '' );
			ob_start();
			FrmFieldsController::load_single_field(
				$field,
				array(
					'id'                => $form_id,
					'doing_ajax'        => true,
					'deferred_settings' => $settings,
				)
			);
			$preview = ob_get_clean();

			$this->assertStringContainsString( 'id="frm_field_id_' . $field->id . '"', $preview );
			$this->assertStringNotContainsString( 'id="frm-single-settings-', $preview );
			$this->assertStringNotContainsString( 'frm-deferred-settings-meta', $preview );
			$this->assertSame( 12, (int) $settings->meta['order'] );
			$this->assertSame( 'frm_half custom_class', $settings->meta['classes'] );
			$this->assertSame( $field->name, $settings->meta['name'] );
			$this->assertSame( $type, $settings->meta['type'] );
			$this->assertSame( $field->field_key, $settings->meta['key'] );
			$this->assertStringContainsString( 'id="frm-single-settings-' . $field->id . '"', $settings->html );
			$this->assertStringContainsString( 'name="frm_fields_submitted[]"', $settings->html );
			$this->assertStringContainsString( 'name="field_options[type_' . $field->id . ']"', $settings->html );
		}
	}

	public function test_builder_settings_remain_inline_without_deferred_collector() {
		$form_id = $this->factory->form->create();
		$field   = $this->factory->field->create_and_get( array( 'form_id' => $form_id ) );
		ob_start();
		FrmFieldsController::load_single_field( $field, array( 'doing_ajax' => true ) );
		$html = ob_get_clean();

		$this->assertStringContainsString( 'id="frm-single-settings-' . $field->id . '"', $html );
		$this->assertStringNotContainsString( 'frm-deferred-settings-meta', $html );
	}

	public function test_builder_placeholder_manifest_preserves_attributes_and_order() {
		$form_id  = $this->factory->form->create();
		$manifest = (object) array(
			'definitions' => array(),
			'fields'      => array(),
		);
		$expected = array();

		foreach ( array( 'text', 'text', 'html' ) as $type ) {
			$field = $this->factory->field->create_and_get(
				array(
					'form_id' => $form_id,
					'type'    => $type,
				)
			);
			ob_start();
			FrmFieldsController::load_single_field(
				$field,
				array(
					'ajax_load' => true,
					'count'     => 11,
				)
			);
			$legacy_html = ob_get_clean();

			ob_start();
			FrmFieldsController::load_single_field(
				$field,
				array(
					'ajax_load'            => true,
					'count'                => 11,
					'placeholder_manifest' => $manifest,
				)
			);
			$placeholder = ob_get_clean();
			$index       = count( $expected );
			$this->assertSame( '<li data-frm-placeholder="' . $index . '"></li>', $placeholder );
			$this->assertSame( (int) $field->id, $manifest->fields[ $index ][0] );
			$definition = $manifest->definitions[ $manifest->fields[ $index ][1] ];
			$this->assertStringContainsString( 'class="' . esc_attr( $definition[0] ) . '"', $legacy_html );
			$this->assertStringContainsString( 'data-formid="' . esc_attr( $definition[1] ) . '"', $legacy_html );
			$this->assertStringContainsString( 'data-ftype="' . esc_attr( $definition[2] ) . '"', $legacy_html );
			$expected[] = (int) $field->id;
		}

		$this->assertCount( 2, $manifest->definitions );
		$this->assertSame( $expected, array_column( $manifest->fields, 0 ) );
		$this->assertSame( $manifest->fields[0][1], $manifest->fields[1][1] );
	}

	public function test_builder_placeholder_manifest_keeps_initial_and_non_ajax_fields_rendered() {
		$form_id  = $this->factory->form->create();
		$field    = $this->factory->field->create_and_get(
			array(
				'form_id' => $form_id,
				'type'    => 'text',
			)
		);
		$manifest = (object) array(
			'definitions' => array(),
			'fields'      => array(),
		);

		foreach ( array( array( true, 10 ), array( false, 11 ) ) as $settings ) {
			ob_start();
			FrmFieldsController::load_single_field(
				$field,
				array(
					'ajax_load'            => $settings[0],
					'count'                => $settings[1],
					'placeholder_manifest' => $manifest,
				)
			);
			$html = ob_get_clean();
			$this->assertStringContainsString( 'id="frm_field_id_' . $field->id . '"', $html );
			$this->assertStringNotContainsString( 'data-frm-placeholder=', $html );
		}
		$this->assertSame( array(), $manifest->fields );
	}

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
		// $keep_leading_blank is the caller's decision - see this method's docblock.
		$opts = $this->parse_bulk_edit_opts( "\nOne\n\nTwo", true );

		$this->assertSame( array( '', 'One', 'Two' ), $opts );
	}

	public function test_parse_bulk_edit_opts_drops_leading_blank_when_not_flagged() {
		$opts = $this->parse_bulk_edit_opts( "\nOne\nTwo", false );
		$this->assertSame( array( 'One', 'Two' ), $opts );
	}

	public function test_parse_bulk_edit_opts_wholly_blank_keeps_nothing_even_when_flagged() {
		// A wholly-cleared textarea saves zero options, not a single
		// leftover blank one - the leading blank only makes sense as the
		// first row of a real list.
		$opts = $this->parse_bulk_edit_opts( "\n\n", true );
		$this->assertSame( array(), $opts );
	}

	/**
	 * @param string $opts
	 * @param bool   $keep_leading_blank
	 */
	private function parse_bulk_edit_opts( $opts, $keep_leading_blank ) {
		return $this->run_private_method( array( 'FrmFieldsController', 'parse_bulk_edit_opts' ), array( $opts, $keep_leading_blank ) );
	}

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
		// $keep_leading_blank is the caller's decision - see this method's docblock.
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

	public function test_remove_blank_separated_values_wholly_blank_keeps_nothing_even_when_flagged() {
		// Same reasoning as parse_bulk_edit_opts()'s wholly-blank case: a
		// lone "|" line with nothing else isn't a real option list with a
		// placeholder row, so it doesn't get to keep the placeholder either.
		$opts = $this->remove_blank_separated_values(
			array(
				array(
					'label' => '',
					'value' => '',
				),
			),
			true
		);

		$this->assertSame( array(), $opts );
	}

	/**
	 * @param array $opts
	 * @param bool  $keep_leading_blank
	 */
	private function remove_blank_separated_values( $opts, $keep_leading_blank = false ) {
		return $this->run_private_method( array( 'FrmFieldsController', 'remove_blank_separated_values' ), array( $opts, $keep_leading_blank ) );
	}

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
