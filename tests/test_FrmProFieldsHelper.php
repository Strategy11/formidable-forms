<?php

/**
 * @group fields
 */
class WP_Test_FrmProFieldsHelper extends FrmUnitTest {

	/**
	* @covers FrmProFieldsHelper::convert_to_static_year
	* Check if dynamic year (in date field) is converted to static correctly
	*/
	function test_convert_to_static_year(){
		$values_to_test = array( '', '0', '-10', '-100', '10', '+10', '+100', '1900', '2015' );
		foreach ( $values_to_test as $dynamic_val ) {
			$year = FrmProFieldsHelper::convert_to_static_year( $dynamic_val );
			$this->assertTrue( ( strlen( $year ) == 4 && is_numeric ( $year ) && strpos( $year, '-' ) === false && strpos( $year, '+' ) === false ), 'The dynamic value ' . $dynamic_val . ' was not correctly converted to a year. It was converted to: ' . $year  );
		}
	}
}