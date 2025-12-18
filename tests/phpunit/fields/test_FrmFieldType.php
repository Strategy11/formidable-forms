<?php

/**
 * @group fields
 */
class test_FrmFieldType extends FrmUnitTest {

	/**
	 * @covers FrmFieldNumber::add_min_max
	 */
	public function test_html_min_number() {
		$form_id = $this->factory->form->create();
		$field   = $this->factory->field->create_and_get(
			array(
				'type'          => 'number',
				'form_id'       => $form_id,
				'field_options' => array(
					'minnum' => 10,
					'maxnum' => 999,
					'step'   => 'any',
				),
			)
		);
		$this->assertNotEmpty( $field );

		$form = FrmFormsController::get_form_shortcode(
			array(
				'id' => $form_id,
			)
		);
		$this->assertNotFalse( strpos( $form, ' min="10"' ) );
		$this->assertNotFalse( strpos( $form, ' max="999"' ) );
		$this->assertNotFalse( strpos( $form, ' step="any"' ) );
	}

	/**
	 * @covers FrmFieldType::sanitize_value
	 */
	public function test_sanitize_value() {
		$this->set_current_user_to_1();
		$frm_field_type = new FrmFieldDefault();

		$values = array(
			array(
				'type'     => 'default',
				'value'    => '<script></script>test',
				'expected' => 'test',
			),
			array(
				'type'     => 'text',
				'value'    => '1 > 2',
				'expected' => '1 > 2',
			),
			array(
				'type'     => 'textarea',
				'value'    => '<div class="here"></div>',
				'expected' => '<div class="here"></div>',
			),
			array(
				'type'     => 'text',
				'value'    => 'test > with \' < & characters "like “ and ‘ this',
				'expected' => 'test > with \' < & characters "like “ and ‘ this',
			),
			array(
				'type'     => 'text',
				'value'    => '&lt;span&gt;2 < 1&lt;/span&gt;',
				'expected' => '&lt;span>2 < 1&lt;/span>',
			),
			array(
				'type'     => 'email',
				'value'    => 'johndoe@yahoo.co.uk',
				'expected' => 'johndoe@yahoo.co.uk',
			),
			array(
				'type'     => 'select',
				'value'    => 'Option 1',
				'expected' => 'Option 1',
			),
			array(
				'type'     => 'url',
				'value'    => 'https://team.strategy11.com/?foo=bar&baz=bam',
				'expected' => 'https://team.strategy11.com/?foo=bar&baz=bam',
			),
			array(
				'type'     => 'default',
				'value'    => array(
					'<script></script>test',
					'another test',
				),
				'expected' => array(
					'test',
					'another test',
				),
			),
			array(
				'type'     => 'phone',
				'value'    => array(
					'(555) 555-1234',
					'1-541-754-3010',
					'+1(408) 785-9969',
				),
				'expected' => array(
					'(555) 555-1234',
					'1-541-754-3010',
					'+1(408) 785-9969',
				),
			),
			array(
				'type'     => 'number',
				'value'    => array(
					'1009',
					'1.5',
				),
				'expected' => array(
					'1009',
					'1.5',
				),
			),
			array(
				'type'     => 'user_id',
				'value'    => array(
					'6',
					'2a',
					'a1',
				),
				'expected' => array(
					'6',
					'2',
					'0',
				),
			),
		);

		foreach ( $values as $value ) {
			$frm_field_type = FrmFieldFactory::get_field_type( $value['type'] );
			$frm_field_type->sanitize_value( $value['value'] );
			$this->assertEquals( $value['expected'], $value['value'] );
		}

		$this->use_frm_role( 'loggedout' );
		$values = array(
			array(
				'type'     => 'default',
				'value'    => '<script></script>test',
				'expected' => 'test',
			),
			array(
				'type'     => 'textarea',
				'value'    => '<div class="here"></div>',
				'expected' => '',
			),
			array(
				'type'     => 'textarea',
				'value'    => '<p>Here</p>',
				'expected' => '<p>Here</p>',
			),
		);

		foreach ( $values as $value ) {
			$frm_field_type = FrmFieldFactory::get_field_type( $value['type'] );
			$frm_field_type->sanitize_value( $value['value'] );
			$this->assertEquals( $value['expected'], $value['value'] );
		}
	}

	/**
	 * @covers FrmFieldType::get_import_value
	 */
	public function test_get_import_value() {
		$field          = new stdClass();
		$field->type    = 'checkbox';
		$field->options = array(
			array(
				'value' => 'a',
				'label' => 'A',
			),
			array(
				'value' => 'b',
				'label' => 'B',
			),
			array(
				'value' => 'c',
				'label' => 'C',
			),
			array(
				'value' => 'a,b',
				'label' => 'A, B',
			),
			array(
				'value' => 'a,b,c',
				'label' => 'A, B, C',
			),
			array(
				'value' => 'a, b, c',
				'label' => 'A, B, C',
			),
		);

		$checkbox = FrmFieldFactory::get_field_type( 'checkbox', $field );

		$this->assertEquals( $checkbox->get_import_value( 'a,b' ), 'a,b' );
		$this->assertEquals( $checkbox->get_import_value( 'a,c' ), array( 'a', 'c' ) );
		$this->assertEquals( $checkbox->get_import_value( 'a,b,c' ), 'a,b,c' );
	}

	/**
	 * @covers FrmFieldType::is_not_unique
	 */
	public function test_is_not_unique() {

		$form_id = $this->factory->form->create();
		$field1  = $this->factory->field->create_and_get(
			array(
				'type'    => 'number',
				'form_id' => $form_id,
			)
		);

		$field_object1 = FrmFieldFactory::get_field_type( 'text', $field1 );
		$entry_id      = 0;

		$this->assertFalse( $field_object1->is_not_unique( 'First', $entry_id ), 'the first iteration of a new value should be flagged as okay' );
		$this->assertTrue( $field_object1->is_not_unique( 'First', $entry_id ), 'the second iteration of a new value should should be flagged as a duplicate' );

		$this->assertFalse( $field_object1->is_not_unique( 'Second', $entry_id ), 'the first iteration of a second new value should be flagged as okay' );
		$this->assertFalse( $field_object1->is_not_unique( 'Third', $entry_id ), 'the first iteration of a third new value should be flagged as okay' );

		$this->assertTrue( $field_object1->is_not_unique( 'Third', $entry_id ) );
		$this->assertTrue( $field_object1->is_not_unique( 'Second', $entry_id ) );

		$field_object2 = FrmFieldFactory::get_field_type( 'text', $field1 );
		$this->assertTrue( $field_object2->is_not_unique( 'First', $entry_id ), 'another field object for the same field should also be flagging a duplicate' );

		$field2        = $this->factory->field->create_and_get(
			array(
				'type'    => 'number',
				'form_id' => $form_id,
			)
		);
		$field_object3 = FrmFieldFactory::get_field_type( 'text', $field2 );

		$this->assertFalse( $field_object3->is_not_unique( 'First', $entry_id ), 'a field object for another field should not flag a duplicate' );
	}

	/**
	 * @covers FrmFieldType::add_aria_description
	 */
	public function test_add_aria_description() {
		$form_id = $this->factory->form->create();
		$field   = $this->factory->field->create_and_get(
			array(
				'type'    => 'input',
				'form_id' => $form_id,
			)
		);

		$field_object = FrmFieldFactory::get_field_type( 'text', $field );
		$args         = array(
			'field_id' => 1,
			'html_id'  => 'field_' . $field->field_key,
			'errors'   => array(
				'field1' => 'This field cannot be blank.',
			),
		);

		$input_html_actual_expected = array(
			' data-reqmsg="This field cannot be blank." aria-required="true" data-invmsg="Name is invalid" aria-describedby="my_custom_aria_describedby" aria-invalid="true" ' =>
			' data-reqmsg="This field cannot be blank." aria-required="true" data-invmsg="Name is invalid" aria-describedby="frm_error_field_' . $field->field_key . ' my_custom_aria_describedby frm_desc_field_' . $field->field_key . '" aria-invalid="true" ',

			' data-reqmsg="This field cannot be blank." aria-required="true" data-invmsg="Name is invalid" aria-invalid="true"' =>
			' data-reqmsg="This field cannot be blank." aria-required="true" data-invmsg="Name is invalid" aria-invalid="true" aria-describedby="frm_error_field_' . $field->field_key . ' frm_desc_field_' . $field->field_key . '"',

			' data-reqmsg="This field cannot be blank." aria-required="true" data-invmsg="Name is invalid" aria-describedby="frm_desc_field_custom frm_error_field_custom" aria-invalid="true"' =>
			' data-reqmsg="This field cannot be blank." aria-required="true" data-invmsg="Name is invalid" aria-describedby="frm_desc_field_' . $field->field_key . ' frm_desc_field_custom frm_error_field_custom" aria-invalid="true" data-error-first="0"',

			// Make sure that a duplicate description ID is not added.
			'aria-describedby="frm_desc_field_' . $field->field_key . '"' => 'aria-describedby="frm_error_field_' . $field->field_key . ' frm_desc_field_' . $field->field_key . '"',
		);

		foreach ( $input_html_actual_expected as $actual => $expected ) {
			$this->run_private_method( array( $field_object, 'add_aria_description' ), array( $args, &$actual ) );
			$this->assertEquals( $expected, $actual );
		}
	}

	/**
	 * @covers FrmFieldType::prepare_field_html
	 */
	public function test_prepare_field_html() {
		$form    = $this->factory->form->create_and_get();
		$form_id = $form->id;

		// Test a basic text field.
		$field        = $this->factory->field->create_and_get(
			array(
				'type'    => 'text',
				'form_id' => $form_id,
			)
		);
		$field_array  = FrmFieldsHelper::setup_edit_vars( $field );
		$field_object = FrmFieldFactory::get_field_type( 'text', $field_array );

		$args = array(
			'errors' => array(),
			'form'   => FrmForm::getOne( $form_id ),
		);
		$html = $field_object->prepare_field_html( $args );

		$this->make_text_field_html_assertions( $html, $field );

		// Test a draft field (the HTML should be nothing).
		$field->field_options['draft'] = 1;
		FrmField::update( $field->id, array( 'field_options' => $field->field_options ) );

		$field        = FrmField::getOne( $field->id );
		$field_array  = FrmFieldsHelper::setup_edit_vars( $field );
		$field_object = FrmFieldFactory::get_field_type( 'text', $field_array );

		$html = $field_object->prepare_field_html( $args );
		$this->assertEquals( '', $html );

		// Test a draft field on a preview page for a privileged user (the HTML should not be empty).
		$this->reset_should_hide_draft_fields_flag();

		$this->use_frm_role( 'administrator' );
		add_filter(
			'user_has_cap',
			function ( $caps ) {
				$caps['frm_edit_forms'] = true;
				return $caps;
			}
		);

		global $pagenow;
		$pagenow        = 'admin-ajax.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$_GET['action'] = 'frm_forms_preview';

		$html = $field_object->prepare_field_html( $args );
		$this->make_text_field_html_assertions( $html, $field );
	}

	/**
	 * This value is determined once per request and memoized.
	 * This needs to be reset in test_prepare_field_html so the check can happen again.
	 *
	 * @return void
	 */
	private function reset_should_hide_draft_fields_flag() {
		$reflection_class    = new ReflectionClass( 'FrmFieldType' );
		$reflection_property = $reflection_class->getProperty( 'should_hide_draft_fields' );
		$reflection_property->setAccessible( true );
		$reflection_property->setValue( null, null );
	}

	/**
	 * @param string   $html
	 * @param stdClass $field
	 *
	 * @return void
	 */
	private function make_text_field_html_assertions( $html, $field ) {
		$this->assertStringContainsString( 'id="frm_field_' . $field->id . '_container"', $html );
		$this->assertStringContainsString( 'class="frm_form_field form-field', $html );
		$this->assertStringContainsString( '<input type="text"', $html );
		$this->assertStringContainsString( 'name="item_meta[' . $field->id . ']"', $html );
		$this->assertStringContainsString( 'id="field_' . $field->field_key . '"', $html );
	}
}
