<?php
/**
 * @group pro
 * @group fields
 */
class test_FrmProFieldDate extends FrmUnitTest {
	
	/**
	 * @covers FrmProFieldDate::validate_year_is_within_range
	 */
	public function test_validate_year_is_within_range() {
		$mock_field = $this->createMock( 'FrmField' );
		$field_type = FrmFieldFactory::get_field_type( 'date', $mock_field );
		
		$test_values = $this->get_year_range_test_values_and_expected_results();
		foreach ( $test_values as $test_value ) {
			$start_year      = $test_value[0];
			$end_year        = $test_value[1];
			$input           = strval( $test_value[2] );
			$expected_result = $test_value[3];
			
			// Inject settings.
			$mock_field->field_options['start_year'] = $start_year;
			$mock_field->field_options['end_year']   = $end_year;
			
			$actual_result   = $this->run_private_method( array( $field_type, 'validate_year_is_within_range' ), array( $input ) );
			
			$this->assertEquals( $expected_result, $actual_result, "Year {$input} is within range {$start_year}:{$end_year}" );
		}
    } 
	
	private function get_year_range_test_values_and_expected_results() {
		// Entries are arrays with: start year, end year, input, expected value.
		$current_year = (int) date( 'Y' );
		
		return array(
			array( '-5', '+150', $current_year, true ),
			array( '-5', '+150', $current_year - 6, false ),
			array( '-5', '+150', $current_year + 151, false ),
			array( '-0', '0', $current_year, true ),
			array( '-0', '0', $current_year + 1, false ),
			array( '-0', '0', $current_year - 1, false ),
			array( '1', '2017', '2015', true ),
			array( '1', '2017', '2019', false ),
			array( '-10', $current_year, $current_year - 5, true ),
			array( '-10', $current_year, $current_year - 15, false ),
			array( '2000', '+10', $current_year, true ),
			array( '2000', '+10', $current_year + 11, false ),
		);
	}
}
