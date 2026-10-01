<?php

/**
 * @group styles
 *
 * @covers FrmSliderStyleComponent
 */
#[\PHPUnit\Framework\Attributes\Group( 'styles' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmSliderStyleComponent::class )]
class test_FrmSliderStyleComponent extends FrmUnitTest {

	/**
	 * @dataProvider is_value_measured_provider
	 *
	 * @param string $value
	 * @param bool   $expected
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'is_value_measured_provider' )]
	public function test_is_value_measured( $value, $expected ) {
		$this->assertSame( $expected, FrmSliderStyleComponent::is_value_measured( $value ) );
	}

	public static function is_value_measured_provider() {
		return array(
			'px'        => array( '10px', true ),
			'em'        => array( '1.5em', true ),
			'percent'   => array( '50%', true ),
			'unitless'  => array( '12.5', false ),
			'auto'      => array( 'auto', false ),
			'empty'     => array( '', false ),
		);
	}

	/**
	 * @dataProvider echo_label_attributes_provider
	 *
	 * @param string $value
	 * @param string $expected
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'echo_label_attributes_provider' )]
	public function test_echo_label_attributes( $value, $expected ) {
		$style               = new stdClass();
		$style->post_content = array( 'submit_width' => $value );

		ob_start();
		FrmSliderStyleComponent::echo_label_attributes( $style, 'submit_width', 'frm_submit_width-value' );

		$this->assertSame( $expected, ob_get_clean() );
	}

	public static function echo_label_attributes_provider() {
		$marker = ' data-slider-label-for="frm_submit_width-value"';

		return array(
			'measured'    => array( '10px', $marker . ' for="frm_submit_width-value"' ),
			'unitless'    => array( '12.5', $marker ),
			'auto'        => array( 'auto', $marker ),
			'empty'       => array( '', $marker ),
			'multi value' => array( '10px 5px', $marker . ' for="frm_submit_width-value"' ),
			'multi auto'  => array( 'auto 5px', $marker ),
		);
	}
}
