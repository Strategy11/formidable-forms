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
				'observed_value' => 6,
				'condition'      => '==',
				'hide_opt'       => 6,
				'expected'       => true,
			),
			array(
				'observed_value' => 6,
				'condition'      => '==',
				'hide_opt'       => 4,
				'expected'       => false,
			),
			array(
				'observed_value' => 6,
				'condition'      => '!=',
				'hide_opt'       => 4,
				'expected'       => true,
			),
			array(
				'observed_value' => 6,
				'condition'      => '!=',
				'hide_opt'       => 6,
				'expected'       => false,
			),
			array(
				'observed_value' => 6,
				'condition'      => '>',
				'hide_opt'       => 5,
				'expected'       => true,
			),
			array(
				'observed_value' => 6,
				'condition'      => '>',
				'hide_opt'       => 7,
				'expected'       => false,
			),
			array(
				'observed_value' => 6,
				'condition'      => '>=',
				'hide_opt'       => 5,
				'expected'       => true,
			),
			array(
				'observed_value' => 6,
				'condition'      => '>=',
				'hide_opt'       => 6,
				'expected'       => true,
			),
			array(
				'observed_value' => 6,
				'condition'      => '>=',
				'hide_opt'       => 7,
				'expected'       => false,
			),
			array(
				'observed_value' => 6,
				'condition'      => '<',
				'hide_opt'       => 7,
				'expected'       => true,
			),
			array(
				'observed_value' => 6,
				'condition'      => '<',
				'hide_opt'       => 5,
				'expected'       => false,
			),

			array(
				'observed_value' => 6,
				'condition'      => '<=',
				'hide_opt'       => 7,
				'expected'       => true,
			),
			array(
				'observed_value' => 6,
				'condition'      => '<=',
				'hide_opt'       => 6,
				'expected'       => true,
			),
			array(
				'observed_value' => 6,
				'condition'      => '<=',
				'hide_opt'       => 5,
				'expected'       => false,
			),
			array(
				'observed_value' => 'happy camper',
				'condition'      => 'LIKE',
				'hide_opt'       => 'happy',
				'expected'       => true,
			),
			array(
				'observed_value' => 'happy',
				'condition'      => 'LIKE',
				'hide_opt'       => 'happy camper',
				'expected'       => false,
			),
			array(
				'observed_value' => 'happy camper',
				'condition'      => 'LIKE',
				'hide_opt'       => 'sad',
				'expected'       => false,
			),
			array(
				'observed_value' => 'happy',
				'condition'      => 'not LIKE',
				'hide_opt'       => 'happy camper',
				'expected'       => true,
			),
			array(
				'observed_value' => 'happy camper',
				'condition'      => 'not LIKE',
				'hide_opt'       => 'sad',
				'expected'       => true,
			),
			array(
				'observed_value' => 'happy camper',
				'condition'      => 'not LIKE',
				'hide_opt'       => 'happy',
				'expected'       => false,
			),
			// starts_with
			array(
				'observed_value' => 'happy camper',
				'condition'      => 'LIKE%',
				'hide_opt'       => 'happy',
				'expected'       => true,
			),
			array(
				'observed_value' => 'happy camper',
				'condition'      => 'LIKE%',
				'hide_opt'       => 'camper',
				'expected'       => false,
			),
			array(
				'observed_value' => array( 'indifferent farmer', 'happy blacksmith' ),
				'condition'      => 'LIKE%',
				'hide_opt'       => 'happy',
				'expected'       => true,
			),
			array(
				'observed_value' => array( 'happy farmer', 'happy blacksmith' ),
				'condition'      => 'LIKE%',
				'hide_opt'       => 'farmer',
				'expected'       => false,
			),
			// ends_with
			array(
				'observed_value' => 'happy camper',
				'condition'      => '%LIKE',
				'hide_opt'       => 'happy',
				'expected'       => false,
			),
			array(
				'observed_value' => 'happy camper',
				'condition'      => '%LIKE',
				'hide_opt'       => 'camper',
				'expected'       => true,
			),
			array(
				'observed_value' => array( 'happy tourist', 'happy walker', 'happy camper' ),
				'condition'      => '%LIKE',
				'hide_opt'       => 'camper',
				'expected'       => true,
			),
			array(
				'observed_value' => array( 'happy tourist', 'happy walker', 'camper tourist' ),
				'condition'      => '%LIKE',
				'hide_opt'       => 'camper',
				'expected'       => false,
			),
		);

		foreach ( $tests as $test ) {
			$result         = FrmFieldsHelper::value_meets_condition( $test['observed_value'], $test['condition'], $test['hide_opt'] );
			$observed_value = is_array( $test['observed_value'] ) ? implode( ',', $test['observed_value'] ) : $test['observed_value'];
			$this->assertEquals( $test['expected'], $result, $observed_value . ' ' . $test['condition'] . ' ' . $test['hide_opt'] . ' failed' );
		}
	}

	/**
	 * Test the "sep" option for checkbox field shortcodes.
	 *
	 * @covers FrmFormsHelper::replace_content_shortcodes
	 */
	public function test_sep_option() {
		$form           = $this->factory->form->create_and_get();
		$form_id        = $form->id;
		$checkbox_field = $this->factory->field->create_and_get(
			array(
				'form_id' => $form_id,
				'type'    => 'checkbox',
				'options' => array(
					'Option 1',
					'Option 2',
				),
			)
		);
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$entry_data['item_meta'][ $checkbox_field->id ] = array(
			'Option 1',
			'Option 2',
		);
		$entry   = $this->factory->entry->create_and_get( $entry_data );

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
}
