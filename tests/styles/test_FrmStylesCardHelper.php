<?php

/**
 * @group styles
 */
class test_FrmStylesCardHelper extends FrmUnitTest {

	/**
	 * @covers FrmStylesCardHelper::has_dark_background
	 */
	public function test_has_dark_background() {
		$this->assert_bg_is_dark( '000000' );
		$this->assert_bg_is_dark( '000' );
		$this->assert_bg_is_dark( 'rgba(130,36,227,1)' );
		$this->assert_bg_is_dark( 'rgb(130,36,227)' );

		$this->assert_bg_is_dark( 'fff', false );
		$this->assert_bg_is_dark( 'ffffff', false );
		$this->assert_bg_is_dark( 'rgba(130,36,227,0)', false );
	}

	private function assert_bg_is_dark( $color, $expected = true ) {
		$style               = new stdClass();
		$style->post_content = array(
			'fieldset_bg_color' => $color,
		);
		$this->assertEquals( $expected, $this->has_dark_background( $style ) );
	}

	private function has_dark_background( $style ) {
		return $this->run_private_method( array( 'FrmStylesCardHelper', 'has_dark_background' ), array( $style ) );
	}
}
