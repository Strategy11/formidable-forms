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
		$invalid_color_values = array( 'rgba(45, 45, 45,', 'rgba(,,,1)', 'rgba(45, 45', 'rgba(45,,,0.5)', 'rgb(45,,45)', 'rgb(,,)' );
		$patters              = array( '/rgba\((\s*\d+\s*,){3}[\d\.]+\)/', '/rgb\((\s*\d+\s*,){2}\s*[\d]+\)/' );

		foreach ( $invalid_color_values as $color_val ) {
			$frm_style->maybe_sanitize_rgba_value( $color_val );

			if ( strpos( $color_val, '(' ) === 4 ) {
				$this->assertEquals( 1, preg_match( $patters[0], $color_val ) );
			} elseif ( strpos( $color_val, '(' ) === 3 ) {
				$this->assertEquals( 1, preg_match( $patters[1], $color_val ) );
			}
		}
	}
}
