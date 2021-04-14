<?php

/**
 * @group fields
 */
class test_FrmFieldCombo extends FrmUnitTest {

	public function test_extra_field_opts() {
		$test_combo = new TestFrmFieldComboWithoutSubFieldOptions();

		$this->assertEquals( $this->run_private_method( [ $test_combo, 'extra_field_opts' ] ), [] );

		$test_combo = new TestFrmFieldComboWithSubFieldOptions();

		$this->assertEquals(
			$this->run_private_method( [ $test_combo, 'extra_field_opts' ] ),
			[
				'name_desc'         => '',
				'email_placeholder' => '',
				'dob_desc'          => '',
				'dob_custom_opt'    => '',
			]
		);
	}

	public function test_get_default_value() {
		$test_combo = new TestFrmFieldComboWithoutSubFieldOptions();

		$this->assertEquals(
			$this->run_private_method( [ $test_combo, 'get_default_value' ] ),
			[ 'first_child' => '', 'second_child' => '', 'third_child' => '', 'forth_child' => '' ]
		);

		$test_combo = new TestFrmFieldComboWithSubFieldOptions();

		$this->assertEquals(
			$this->run_private_method( [ $test_combo, 'get_default_value' ] ),
			[ 'name' => '', 'email' => '', 'dob' => '' ]
		);
	}
}

class TestFrmFieldComboWithoutSubFieldOptions extends FrmFieldCombo {

	protected function get_sub_fields() {
		return [
			'first_child'  => [
				'type'     => 'text',
				'label'    => 'First child',
			],
			'second_child' => [
				'type'  => 'text',
				'label' => 'Second child',
			],
			'third_child'  => [
				'type'  => 'text',
				'label' => 'Third child'
			],
			'forth_child'  => [
				'type'  => 'text',
				'label' => 'Forth child',
			],
		];
	}
}

class TestFrmFieldComboWithSubFieldOptions extends FrmFieldCombo {

	protected function get_sub_fields() {
		return [
			'name' => [
				'type' => 'text',
				'label' => 'Name',
				'options' => [
					'desc',
				],
			],
			'email' => [
				'type' => 'email',
				'label' => 'Email',
				'options' => [
					'placeholder',
					'default_value',
				],
			],
			'dob' => [
				'type' => 'date',
				'label' => 'Date of Birth',
				'options' => [
					'default_value',
					'desc',
					[
						'name' => 'custom_opt',
						'type' => 'text',
					],
				],
			],
		];
	}
}
