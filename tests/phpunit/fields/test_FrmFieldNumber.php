<?php

/**
 * @group fields
 */
class test_FrmFieldNumber extends FrmUnitTest {
	public function test_check_value_is_valid_with_step() {
		$number_field = new FrmFieldNumber();

		$this->assertEquals( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 9, 3 ) ) );
		$this->assertEquals( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 9.0, 3 ) ) );
		$this->assertEquals( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 9, 3.0 ) ) );
		$this->assertEquals( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 68.93, 0.01 ) ) );
		$this->assertEquals( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( '68.93', '0.01' ) ) );

		$this->assertEquals( array( 0, 3 ), $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 1, 3 ) ) );
		$this->assertEquals( array( 8, 10 ), $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 9, 2 ) ) );
		$this->assertEquals( array( 68.92, 68.94 ), $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 68.93, 0.02 ) ) );
		$this->assertEquals( array( 68.92, 68.94 ), $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( '68.93', '0.02' ) ) );
	}
}
