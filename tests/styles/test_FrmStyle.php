<?php

/**
 * @group styles
 */
class test_FrmStyle extends FrmUnitTest {
	/**
	 * @covers maybe_sanitize_rgba_value
	 */
	public function test_maybe_sanitize_rgba_value() {
		$frm_style            = new FrmStyle();
		$invalid_color_values = array(
			'rgba(45, 45, 45,' => 'rgba(45, 45, 45,1)',
			'rgba(, , ,1)'     => 'rgba(0,0,0,1)',
			'rgba(45,45'       => 'rgba(45,45,0,1)',
			'rgba(45, , ,0.5)' => 'rgba(45,0,0,0.5)',
			'rgb(45, ,45)'     => 'rgb(45,0,45)',
			'rgb( , , )'       => 'rgb(0,0,0)',
		);

		$patterns = array( '/rgba\((\s*\d+\s*,){3}[\d\.]+\)/', '/rgb\((\s*\d+\s*,){2}\s*[\d]+\)/' );

		foreach ( $invalid_color_values as $color_val => $expected_color_val ) {
			$frm_style->maybe_sanitize_rgba_value( $color_val );
			$this->assertEquals( $expected_color_val, $color_val );
		}
	}
}
