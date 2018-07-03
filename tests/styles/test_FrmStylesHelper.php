<?php

/**
 * @group styles
 */
class test_FrmStylesHelper extends FrmUnitTest {

	/**
	 * @covers FrmStylesHelper::get_upload_base
	 */
	public function test_get_upload_base() {
		$base = FrmStylesHelper::get_upload_base();
		$this->assertTrue( isset( $base['baseurl'] ) );
		$this->assertTrue( strpos( $base['baseurl'], 'http://' ) !== false );

		$_SERVER['HTTPS'] = 'on';
		$base = FrmStylesHelper::get_upload_base();
		$this->assertTrue( strpos( $base['baseurl'], 'https://' ) !== false );
	}

	/**
	 * @covers FrmStylesHelper::get_settings_for_output
	 */
	public function test_get_settings_for_output() {
		$frm_style = new FrmStyle( 'default' );
		$style = $frm_style->get_one();
		$settings = FrmStylesHelper::get_settings_for_output( $style );
		$expected = 'frm_style_' . $style->post_name . '.with_frm_style';
		$this->assertEquals( $expected, $settings['style_class'] );

		$_POST = array(
			'action'     => 'frm_change_styling',
			'style_name' => 'frm_style_test',
			'frm_style_setting' => array(
				'post_content'  => $frm_style->get_defaults(),
			),
		);

		$settings = FrmStylesHelper::get_settings_for_output( $style );
		$expected = 'frm_style_test.with_frm_style';
		$this->assertEquals( $expected, $settings['style_class'] );
		$this->assertEquals( '#000000', $settings['fieldset_color'] );
	}

	/**
	 * Test that the css includes the stylesheet once and only once
	 */
	public function test_single_instance_in_css() {
		$compiled_css = get_transient( 'frmpro_css' );
		$this->assertNotEmpty( $compiled_css, 'Generated CSS file is empty' );

		$frm_style = new FrmStyle( 'default' );
		$style = $frm_style->get_one();
		$settings = FrmStylesHelper::get_settings_for_output( $style );
		$css_contains = substr_count( $compiled_css, '}.frm_forms.' . $settings['style_class'] . '{' );
		$this->assertEquals( 1, $css_contains, 'Multiple or no occurances of style found' );
	}

	/**
	 * @covers FrmStylesHelper::hex2rgb
	 */
	public function test_hex2rgb() {
		$colors = array(
			'ffffff'  => '255,255,255',
			'#ffffff' => '255,255,255',
			'262626'  => '38,38,38',
		);

		foreach ( $colors as $hex => $rgb ) {
			$this->assertEquals( $rgb, FrmStylesHelper::hex2rgb( $hex ), 'Hex ' . $hex . ' did not convert to ' . $rgb );
		}
	}

	/**
	 * @covers FrmStylesHelper::adjust_brightness
	 */
	public function test_adjust_brightness() {
		$colors = array(
			array(
				'start' => '#999999',
				'steps' => 2,
				'end'   => '#9b9b9b',
			),
			array(
				'start' => '#999999',
				'steps' => -20,
				'end'   => '#858585',
			),
		);

		foreach ( $colors as $color ) {
			$result = FrmStylesHelper::adjust_brightness( $color['start'], $color['steps'] );
			$this->assertEquals( $color['end'], $result, $color['start'] . ' was not adusted as expected by ' . $color['steps'] . ' steps' );
		}
	}
}
