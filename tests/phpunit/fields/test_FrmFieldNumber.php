<?php

/**
 * @group fields
 */
#[\PHPUnit\Framework\Attributes\Group( 'fields' )]
class test_FrmFieldNumber extends FrmUnitTest {
	public function test_check_value_is_valid_with_step() {
		$number_field = new FrmFieldNumber();

		$this->assertSame( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 9, 3 ) ) );
		$this->assertSame( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 9.0, 3 ) ) );
		$this->assertSame( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 9, 3.0 ) ) );
		$this->assertSame( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 68.93, 0.01 ) ) );
		$this->assertSame( 0, $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( '68.93', '0.01' ) ) );

		$this->assertSame( array( 0.0, 3.0 ), $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 1, 3 ) ) );
		$this->assertSame( array( 8.0, 10.0 ), $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 9, 2 ) ) );
		$this->assertSame( array( 68.92, 68.94 ), $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( 68.93, 0.02 ) ) );
		$this->assertSame( array( 68.92, 68.94 ), $this->run_private_method( array( $number_field, 'check_value_is_valid_with_step' ), array( '68.93', '0.02' ) ) );
	}

	/**
	 * @covers FrmFieldType::add_min_max
	 */
	public function test_add_min_max_step() {
		$this->assertStringContainsString( 'step="any"', $this->get_min_max_html( array( 'step' => '' ) ) );
		$this->assertStringContainsString( 'step="any"', $this->get_min_max_html( array() ) );
		$this->assertStringContainsString( 'step="any"', $this->get_min_max_html( array( 'step' => 'any' ) ) );
		$this->assertStringContainsString( 'step="0.5"', $this->get_min_max_html( array( 'step' => '0.5' ) ) );
	}

	/**
	 * @covers FrmFieldNumber::add_extra_html_atts
	 */
	public function test_add_extra_html_atts_includes_range_messages() {
		$number     = new FrmFieldNumber(
			array(
				'id'            => 0,
				'type'          => 'number',
				'field_options' => array(),
			)
		);
		$input_html = '';
		$this->run_private_method( array( $number, 'add_extra_html_atts' ), array( array(), &$input_html ) );

		$this->assertStringContainsString( 'data-minmsg="Please select a higher number"', $input_html );
		$this->assertStringContainsString( 'data-maxmsg="Please select a lower number"', $input_html );
	}

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	private function get_min_max_html( $options ) {
		$field      = array(
			'id'            => 0,
			'type'          => 'number',
			'field_options' => $options,
		);
		$number     = new FrmFieldNumber( $field );
		$input_html = '';
		$this->run_private_method( array( $number, 'add_min_max' ), array( array(), &$input_html ) );
		return $input_html;
	}
}
