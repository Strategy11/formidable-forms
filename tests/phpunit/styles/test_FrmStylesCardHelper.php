<?php

/**
 * @group styles
 *
 * @covers FrmStylesCardHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'styles' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmStylesCardHelper::class )]
class test_FrmStylesCardHelper extends FrmUnitTest {

	public function test_has_dark_background() {
		$this->assert_bg_is_dark( '000000' );
		$this->assert_bg_is_dark( '000' );
		$this->assert_bg_is_dark( 'rgba(130,36,227,1)' );
		$this->assert_bg_is_dark( 'rgb(130,36,227)' );

		$this->assert_bg_is_dark( 'hsl(30, 100%, 20%)' );
		$this->assert_bg_is_dark( 'hsla(30, 100%, 20%, .25)', false );
		$this->assert_bg_is_dark( '#0004', false );
		$this->assert_bg_is_dark( '00000000', false );
		$this->assert_bg_is_dark( 'rgb(0 0 0 / 25%)', false );
		$this->assert_bg_is_dark( 'fff', false );
		$this->assert_bg_is_dark( 'ffffff', false );
		$this->assert_bg_is_dark( 'rgba(130,36,227,0)', false );
	}

	public function test_card_color_formats() {
		foreach ( array( 'hsl(30, 100%, 50%)', 'hsla(30, 100%, 50%, .5)', 'RGB(255, 128, 0)', '#ff800080', 'transparent', 'var(--brand)' ) as $color ) {
			$style = (object) array(
				'post_content' => array(
					'fieldset_bg_color' => $color,
					'label_color'       => $color,
					'submit_bg_color'   => $color,
				),
			);
			$css   = FrmStylesCardHelper::get_style_param_for_card( $style );
			$this->assertStringContainsString( '--preview-background-color: ' . $color, $css );
			$this->assertStringContainsString( '--label-color:' . $color, $css );
			$this->assertStringContainsString( '--submit-bg-color:' . $color, $css );
		}
	}

	/**
	 * @param string $color
	 * @param bool $expected
	 */
	private function assert_bg_is_dark( $color, $expected = true ) {
		$style               = new stdClass();
		$style->post_content = array(
			'fieldset_bg_color' => $color,
		);
		$this->assertSame( $expected, $this->has_dark_background( $style ) );
	}

	private function has_dark_background( $style ) {
		return $this->run_private_method( array( 'FrmStylesCardHelper', 'has_dark_background' ), array( $style ) );
	}
}
