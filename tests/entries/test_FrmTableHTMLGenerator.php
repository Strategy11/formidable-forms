<?php

/**
 * @since 3.0
 *
 * @group entries
 * @group free
 */
class test_FrmTableHTMLGenerator extends FrmUnitTest {

	/**
	 * @covers FrmTableHTMLGenerator::init_style_settings
	 * @covers FrmTableHTMLGenerator::get_color_markup
	 */
	public function test_init_style_settings() {
		$colors          = $this->_get_colors();
		$table_generator = new FrmTableHTMLGenerator( 'entry', $colors['start'] );

		$style_settings = $this->get_private_property( $table_generator, 'style_settings' );

		foreach ( $colors['expected'] as $name => $color ) {
			$actual = $style_settings[ $name ];
			$this->assertEquals( $color, $actual, $name . ' not converted from ' . $actual . ' to ' . $color );
		}
	}

	/**
	 * @covers FrmTableHTMLGenerator::is_color_setting
	 */
	public function test_is_color_setting() {
		$table_generator = new FrmTableHTMLGenerator( 'entry' );

		$colors = array( 'border_color', 'bg_color', 'text_color', 'alt_bg_color' );
		foreach ( $colors as $color ) {
			$is_color = $this->run_private_method( array( $table_generator, 'is_color_setting' ), array( $color ) );
			$this->assertTrue( $is_color, $color . ' is a color' );
		}

		$non_colors = array( 'font_size', 'border_width' );
		foreach ( $non_colors as $color ) {
			$is_color = $this->run_private_method( array( $table_generator, 'is_color_setting' ), array( $color ) );
			$this->assertFalse( $is_color, $color . ' is not a color' );
		}
	}

	private function _get_colors() {
		$atts = array(
			'alt_bg_color' => '#fff',
			'bg_color'     => 'red',
			'border_color' => 'ffffff',
			'text_color'   => '999',
		);

		$expected = array(
			'alt_bg_color' => '#fff',
			'bg_color'     => 'red',
			'border_color' => '#ffffff',
			'text_color'   => '#999',
		);

		return array(
			'expected' => $expected,
			'start'    => $atts,
		);
	}
}
