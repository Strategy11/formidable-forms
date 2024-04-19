<?php

/**
 * @group fields
 * @group conditional-logic
 * @group value-meets-condition
 */
class test_FrmFieldsHelper extends FrmUnitTest {

	/**
	 * Tests where $observed_value is a single value, not an array.
	 *
	 * @covers FrmFieldsHelper::value_meets_condition
	 */
	public function test_value_meets_condition() {
		$tests = array(
			array(
				'condition'      => '==',
				'expected'       => true,
				'hide_opt'       => 6,
				'observed_value' => 6,
			),
			array(
				'condition'      => '==',
				'expected'       => false,
				'hide_opt'       => 4,
				'observed_value' => 6,
			),
			array(
				'condition'      => '!=',
				'expected'       => true,
				'hide_opt'       => 4,
				'observed_value' => 6,
			),
			array(
				'condition'      => '!=',
				'expected'       => false,
				'hide_opt'       => 6,
				'observed_value' => 6,
			),
			array(
				'condition'      => '>',
				'expected'       => true,
				'hide_opt'       => 5,
				'observed_value' => 6,
			),
			array(
				'condition'      => '>',
				'expected'       => false,
				'hide_opt'       => 7,
				'observed_value' => 6,
			),
			array(
				'condition'      => '>=',
				'expected'       => true,
				'hide_opt'       => 5,
				'observed_value' => 6,
			),
			array(
				'condition'      => '>=',
				'expected'       => true,
				'hide_opt'       => 6,
				'observed_value' => 6,
			),
			array(
				'condition'      => '>=',
				'expected'       => false,
				'hide_opt'       => 7,
				'observed_value' => 6,
			),
			array(
				'condition'      => '<',
				'expected'       => true,
				'hide_opt'       => 7,
				'observed_value' => 6,
			),
			array(
				'condition'      => '<',
				'expected'       => false,
				'hide_opt'       => 5,
				'observed_value' => 6,
			),

			array(
				'condition'      => '<=',
				'expected'       => true,
				'hide_opt'       => 7,
				'observed_value' => 6,
			),
			array(
				'condition'      => '<=',
				'expected'       => true,
				'hide_opt'       => 6,
				'observed_value' => 6,
			),
			array(
				'condition'      => '<=',
				'expected'       => false,
				'hide_opt'       => 5,
				'observed_value' => 6,
			),
			array(
				'condition'      => 'LIKE',
				'expected'       => true,
				'hide_opt'       => 'happy',
				'observed_value' => 'happy camper',
			),
			array(
				'condition'      => 'LIKE',
				'expected'       => false,
				'hide_opt'       => 'happy camper',
				'observed_value' => 'happy',
			),
			array(
				'condition'      => 'LIKE',
				'expected'       => false,
				'hide_opt'       => 'sad',
				'observed_value' => 'happy camper',
			),
			array(
				'condition'      => 'not LIKE',
				'expected'       => true,
				'hide_opt'       => 'happy camper',
				'observed_value' => 'happy',
			),
			array(
				'condition'      => 'not LIKE',
				'expected'       => true,
				'hide_opt'       => 'sad',
				'observed_value' => 'happy camper',
			),
			array(
				'condition'      => 'not LIKE',
				'expected'       => false,
				'hide_opt'       => 'happy',
				'observed_value' => 'happy camper',
			),
			// starts_with
			array(
				'condition'      => 'LIKE%',
				'expected'       => true,
				'hide_opt'       => 'happy',
				'observed_value' => 'happy camper',
			),
			array(
				'condition'      => 'LIKE%',
				'expected'       => false,
				'hide_opt'       => 'camper',
				'observed_value' => 'happy camper',
			),
			array(
				'condition'      => 'LIKE%',
				'expected'       => true,
				'hide_opt'       => 'happy',
				'observed_value' => array( 'indifferent farmer', 'happy blacksmith' ),
			),
			array(
				'condition'      => 'LIKE%',
				'expected'       => false,
				'hide_opt'       => 'farmer',
				'observed_value' => array( 'happy farmer', 'happy blacksmith' ),
			),
			// ends_with
			array(
				'condition'      => '%LIKE',
				'expected'       => false,
				'hide_opt'       => 'happy',
				'observed_value' => 'happy camper',
			),
			array(
				'condition'      => '%LIKE',
				'expected'       => true,
				'hide_opt'       => 'camper',
				'observed_value' => 'happy camper',
			),
			array(
				'condition'      => '%LIKE',
				'expected'       => true,
				'hide_opt'       => 'camper',
				'observed_value' => array( 'happy tourist', 'happy walker', 'happy camper' ),
			),
			array(
				'condition'      => '%LIKE',
				'expected'       => false,
				'hide_opt'       => 'camper',
				'observed_value' => array( 'happy tourist', 'happy walker', 'camper tourist' ),
			),
		);

		foreach ( $tests as $test ) {
			$result         = FrmFieldsHelper::value_meets_condition( $test['observed_value'], $test['condition'], $test['hide_opt'] );
			$observed_value = is_array( $test['observed_value'] ) ? implode( ',', $test['observed_value'] ) : $test['observed_value'];
			$this->assertEquals( $test['expected'], $result, $observed_value . ' ' . $test['condition'] . ' ' . $test['hide_opt'] . ' failed' );
		}
	}

	/**
	 * Covers FrmFieldsHelper::get_draft_field_results
	 */
	public function test_get_draft_field_results() {
		$form_id = $this->factory->form->create();
		$this->assertEquals( array(), FrmFieldsHelper::get_draft_field_results( $form_id ) );

		$draft_field_options = array(
			'field_options' => array(
				'draft' => 1,
			),
			'form_id'       => $form_id,
			'type'          => 'text',
		);
		$draft_field_id      = $this->factory->field->create( $draft_field_options );

		// Test a single draft field.
		$results = FrmFieldsHelper::get_draft_field_results( $form_id );
		$ids     = wp_list_pluck( $results, 'id' );
		$this->assertEquals( array( $draft_field_id ), $ids );

		// Test with two draft fields.
		$draft_field_id2 = $this->factory->field->create( $draft_field_options );
		$results         = FrmFieldsHelper::get_draft_field_results( $form_id );
		$ids             = wp_list_pluck( $results, 'id' );
		$this->assertEquals( array( $draft_field_id, $draft_field_id2 ), $ids );

		// Test the $field_ids parameter. If this is not empty, we only want o query for these IDs.
		$results = FrmFieldsHelper::get_draft_field_results( $form_id, array( $draft_field_id2 ) );
		$ids     = wp_list_pluck( $results, 'id' );
		$this->assertEquals( array( $draft_field_id2 ), $ids );
	}

	/**
	 * Test the "sep" option for checkbox field shortcodes.
	 *
	 * @covers FrmFieldsHelper::replace_content_shortcodes
	 */
	public function test_sep_option() {
		$form           = $this->factory->form->create_and_get();
		$form_id        = $form->id;
		$checkbox_field = $this->factory->field->create_and_get(
			array(
				'form_id' => $form_id,
				'options' => array(
					'Option 1',
					'Option 2',
				),
				'type'    => 'checkbox',
			)
		);
		$entry_data     = $this->factory->field->generate_entry_array( $form );
		$entry_data['item_meta'][ $checkbox_field->id ] = array(
			'Option 1',
			'Option 2',
		);
		$entry = $this->factory->entry->create_and_get( $entry_data );

		$shortcode  = '[' . $checkbox_field->id . ' sep="</div><div>"]';
		$shortcodes = FrmFieldsHelper::get_shortcodes( $shortcode, $form->id );
		$this->assertEquals(
			implode( '</div><div>', array( 'Option 1', 'Option 2' ) ),
			FrmFieldsHelper::replace_content_shortcodes( $shortcode, $entry, $shortcodes )
		);

		$shortcode  = '[' . $checkbox_field->id . ' sep=", "]';
		$shortcodes = FrmFieldsHelper::get_shortcodes( $shortcode, $form->id );
		$this->assertEquals(
			'Option 1, Option 2',
			FrmFieldsHelper::replace_content_shortcodes( $shortcode, $entry, $shortcodes )
		);
	}

	/**
	 * @covers FrmFieldsHelper::get_error_msg
	 */
	public function test_get_error_msg() {
		$form_id = $this->factory->form->create();

		// Test a field with no name. We should see "This field" (or "This value" for unique validation).
		$field = $this->factory->field->create_and_get(
			array(
				'field_options' => array(
					'blank'      => '[field_name] cannot be blank',
					'unique_msg' => '[field_name] must be unique',
				),
				'form_id'       => $form_id,
				'name'          => '',
				'type'          => 'text',
			)
		);

		$error_message = FrmFieldsHelper::get_error_msg( $field, 'blank' );
		$this->assertEquals( 'This field cannot be blank', $error_message );

		$error_message = FrmFieldsHelper::get_error_msg( $field, 'unique_msg' );
		$this->assertEquals( 'This value must be unique', $error_message );

		// Test with a field name.
		$field->name = 'My example field';

		$error_message = FrmFieldsHelper::get_error_msg( $field, 'blank' );
		$this->assertEquals( 'My example field cannot be blank', $error_message );

		$error_message = FrmFieldsHelper::get_error_msg( $field, 'unique_msg' );
		$this->assertEquals( 'My example field must be unique', $error_message );

		// Test that "This field" and "This value" are automatically replaced.
		$field->field_options['blank']      = 'This field cannot be blank';
		$field->field_options['unique_msg'] = 'This value must be unique';

		$error_message = FrmFieldsHelper::get_error_msg( $field, 'blank' );
		$this->assertEquals( 'My example field cannot be blank', $error_message );

		$error_message = FrmFieldsHelper::get_error_msg( $field, 'unique_msg' );
		$this->assertEquals( 'My example field must be unique', $error_message );
	}
}
