<?php

/**
 * @group styles
 */
class test_FrmStyle extends FrmUnitTest {

	/**
	 * @covers FrmStyle::maybe_sanitize_rgba_value
	 */
	public function test_maybe_sanitize_rgba_value() {
		$frm_style            = new FrmStyle();
		$invalid_color_values = array(
			'rgba(45, 45, 45,' => 'rgba(45, 45, 45,1)',
			'rgba(, , ,1)'     => 'rgba(0,0,0,1)',
			'rgba(45,45'       => 'rgba(45,45,0,1)',
			'rgba(355,,,0.5)'  => 'rgba(255,0,0,0.5)',
			'rgba(255,0,0,11)' => 'rgba(255,0,0,1)',
			'rgba(99,99,99,1)' => 'rgba(99,99,99,1)',
			'rgb(45, ,45)'     => 'rgb(45,0,45)',
			'rgb( , , )'       => 'rgb(0,0,0)',
			'rgb(300,0,-1)'    => 'rgb(255,0,0)',
			'rgb('             => 'rgb(0,0,0)',
			'rgb(255,255,255)' => 'rgb(255,255,255)',
		);

		foreach ( $invalid_color_values as $color_val => $expected_color_val ) {
			$this->run_private_method( array( $frm_style, 'maybe_sanitize_rgba_value' ), array( &$color_val ) );
			$this->assertEquals( $expected_color_val, $color_val );
		}
	}
}
