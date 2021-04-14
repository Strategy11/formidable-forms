<?php

/**
 * @group fields
 */
class test_FrmFieldCombo extends FrmUnitTest {

	public function test_get_default_value() {
		$test_combo = new TestFrmFieldCombo();

		$this->assertEquals(
			$this->run_private_method( [ $test_combo, 'get_default_value' ] ),
			[ 'first_child' => '', 'second_child' => '', 'third_child' => '', 'forth_child' => '' ]
		);
	}
}

class TestFrmFieldCombo extends FrmFieldCombo {

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
