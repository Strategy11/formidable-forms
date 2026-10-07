<?php

/**
 * @group fields
 */
#[\PHPUnit\Framework\Attributes\Group( 'fields' )]
class test_FrmFieldName extends FrmUnitTest {

	public function test_get_processed_sub_fields() {
		$field = $this->factory->field->create_and_get(
			array(
				'type'    => 'name',
				'form_id' => 1,
			)
		);

		$field->field_options['name_layout'] = 'first_middle_last';

		$name_field           = new FrmFieldName( $field );
		$processed_sub_fields = $this->run_private_method( array( $name_field, 'get_processed_sub_fields' ) );

		$this->assertSame( array( 'first', 'middle', 'last' ), array_keys( $processed_sub_fields ) );
		$this->assertStringContainsString( 'frm4', $processed_sub_fields['first']['wrapper_classes'] );
		$this->assertStringContainsString( 'frm4', $processed_sub_fields['middle']['wrapper_classes'] );
		$this->assertStringContainsString( 'frm4', $processed_sub_fields['last']['wrapper_classes'] );
	}

	/**
	 * A required name with only some sub fields missing names each one in its own error, and one
	 * with every sub field missing gets a single error for the whole field.
	 *
	 * @covers FrmFieldCombo::validate
	 */
	public function test_validate_required_sub_field_errors() {
		$field = $this->factory->field->create_and_get(
			array(
				'type'          => 'name',
				'form_id'       => 1,
				'required'      => 1,
				'field_options' => array(
					'first_desc' => 'First Name',
					'last_desc'  => 'Last Name',
				),
			)
		);

		$name_field = new FrmFieldName( $field );
		$error_key  = 'field' . $field->id;

		$errors = $name_field->validate(
			array(
				'id'    => $field->id,
				'value' => array( 'first' => 'Ann' ),
			)
		);

		$this->assertArrayNotHasKey( $error_key, $errors );
		$this->assertArrayNotHasKey( $error_key . '-first', $errors );
		$this->assertStringContainsString( 'Last Name', $errors[ $error_key . '-last' ] );

		$errors = $name_field->validate(
			array(
				'id'    => $field->id,
				'value' => array(),
			)
		);

		$this->assertSame( FrmFieldsHelper::get_error_msg( $field, 'blank' ), $errors[ $error_key ] );
		$this->assertSame( '', $errors[ $error_key . '-first' ] );
		$this->assertSame( '', $errors[ $error_key . '-last' ] );
	}

	/**
	 * Custom sub field descriptions must match in server errors and JS validation attributes.
	 */
	public function test_custom_sub_field_description_matches_validation_messages() {
		$form  = $this->factory->form->create_and_get();
		$field = $this->factory->field->create_and_get(
			array(
				'type'          => 'name',
				'name'          => 'Name',
				'required'      => 1,
				'form_id'       => $form->id,
				'field_options' => array(
					'last_desc' => 'Last',
					'blank'     => '[field_name] cannot be blank.',
				),
			)
		);

		$name_field = new FrmFieldName( $field );
		$errors     = $name_field->validate(
			array(
				'id'    => $field->id,
				'value' => array( 'first' => 'Ann' ),
			)
		);
		$message    = $errors[ 'field' . $field->id . '-last' ];

		$this->assertSame( 'Last cannot be blank.', $message, 'Server validation should use the custom description.' );

		$html    = do_shortcode( '[formidable id="' . $form->id . '"]' );
		$html_id = 'field_' . $field->field_key . '_last';
		$matched = preg_match( '/<input\b[^>]*\bid="' . preg_quote( $html_id, '/' ) . '"[^>]*>/', $html, $matches );

		$this->assertSame( 1, $matched, 'The last name input should render.' );
		$this->assertStringContainsString( 'data-reqmsg="' . esc_attr( $message ) . '"', $matches[0], 'The rendered JS message should match the server error.' );

		$prepared_field = FrmFieldsHelper::field_object_to_array( $field );

		ob_start();
		FrmComboFieldsController::add_atts_to_input(
			array(
				'field'     => $prepared_field,
				'key'       => 'last',
				'sub_field' => array( 'type' => 'text' ),
			)
		);
		$attributes = ob_get_clean();

		$this->assertStringContainsString( 'data-reqmsg="' . esc_attr( $message ) . '"', $attributes, 'The combo controller JS message should match the server error.' );
	}

	/**
	 * A sub field description that reads as a sentence is not used to name the sub field.
	 *
	 * @covers FrmFieldCombo::get_sub_field_label
	 */
	public function test_get_sub_field_label_ignores_sentence_description() {
		$field = $this->factory->field->create_and_get(
			array(
				'type'          => 'name',
				'form_id'       => 1,
				'field_options' => array(
					'first_desc' => 'We use this to personalize your emails.',
					'last_desc'  => 'Surname',
				),
			)
		);

		$name_field = new FrmFieldName( $field );

		$this->assertSame( 'Surname', $name_field->get_sub_field_label( 'last' ) );
		$this->assertStringNotContainsString( 'personalize', $name_field->get_sub_field_label( 'first' ) );
	}
}
