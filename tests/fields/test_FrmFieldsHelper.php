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
	function test_value_meets_condition() {
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
		);

		foreach ( $tests as $test ) {
			$result = FrmFieldsHelper::value_meets_condition( $test['observed_value'], $test['condition'], $test['hide_opt'] );
			$this->assertEquals( $test['expected'], $result, $test['observed_value'] . ' ' . $test['condition'] . ' ' . $test['hide_opt'] . ' failed' );
		}
	}
}
