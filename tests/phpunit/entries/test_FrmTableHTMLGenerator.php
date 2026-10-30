<?php

/**
 * @since 3.0
 *
 * @group entries
 * @group free
 *
 * @covers FrmTableHTMLGenerator
 */
#[\PHPUnit\Framework\Attributes\Group( 'entries' )]
#[\PHPUnit\Framework\Attributes\Group( 'free' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmTableHTMLGenerator::class )]
class test_FrmTableHTMLGenerator extends FrmUnitTest {

	public function test_init_style_settings() {
		$colors          = $this->_get_colors();
		$table_generator = new FrmTableHTMLGenerator( 'entry', $colors['start'] );
		$style_settings  = $this->get_private_property( $table_generator, 'style_settings' );

		foreach ( $colors['expected'] as $name => $color ) {
			$actual = $style_settings[ $name ];
			$this->assertSame( $color, $actual, $name . ' not converted from ' . $actual . ' to ' . $color );
		}
	}

	public function test_is_color_setting() {
		$table_generator = new FrmTableHTMLGenerator( 'entry' );
		$colors          = array( 'border_color', 'bg_color', 'text_color', 'alt_bg_color' );

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

	public function test_remove_border() {
		$table_generator = new FrmTableHTMLGenerator(
			'entry',
			array(
				'border_color' => 'eee',
				'border_width' => '3px',
			)
		);

		$html = '<div style="border-top:3px solid #eee;"></div>';
		$this->assertSame( '<div style=""></div>', $table_generator->remove_border( $html ) );
		$this->assertSame( $table_generator->remove_border( $html, 'bottom' ), $html );

		$html = '<div style="border-top:1px solid #eee;"></div>';
		$this->assertSame( $table_generator->remove_border( $html ), $html );
	}

	/**
	 * @return array
	 */
	private function _get_colors() {
		$atts = array(
			'border_color' => 'ffffff',
			'bg_color'     => 'red',
			'text_color'   => '999',
			'alt_bg_color' => '#fff',
		);

		$expected = array(
			'border_color' => '#ffffff',
			'bg_color'     => 'red',
			'text_color'   => '#999',
			'alt_bg_color' => '#fff',
		);

		return array(
			'start'    => $atts,
			'expected' => $expected,
		);
	}
}
