<?php

/**
 * @group styles
 */
class test_FrmStylesHelper extends FrmUnitTest {

	/**
	 * @covers FrmStylesHelper::jquery_css_url
	 */
	public function test_jquery_css_url() {
		$default = FrmAppHelper::plugin_url() . '/css/ui-lightness/jquery-ui.css';
		$css_options = array(
			-1             => '',
			''             => $default,
			'ui-lightness' => $default,
			false          => $default,
			'http://testing.com' => 'http://testing.com',
			'start'        => FrmAppHelper::jquery_ui_base_url() . '/themes/start/jquery-ui.min.css',
		);

		foreach ( $css_options as $setting => $expected ) {
			$css = FrmStylesHelper::jquery_css_url( $setting );
			$this->assertEquals( $expected, $css );
		}
	}

	/**
	 * @covers FrmStylesHelper::get_form_for_page
	 */
	public function test_get_form_for_page() {
		global $frm_vars;
		$frm_vars['forms_loaded'] = array();
		$this->assertEquals( 'default', FrmStylesHelper::get_form_for_page() );

		$form = $this->factory->form->get_object_by_id( $this->contact_form_key );
		$frm_vars['forms_loaded'][] = $form;
		$this->assertEquals( $form->id, FrmStylesHelper::get_form_for_page() );
	}

	/**
	 * @covers FrmStylesHelper::enqueue_jquery_css
	 */
	public function test_enqueue_jquery_css() {
		global $frm_vars;
		$frm_vars['forms_loaded'] = array();

		FrmStylesHelper::enqueue_jquery_css();
		$this->assertTrue( wp_style_is( 'jquery-theme', 'enqueued' ) );

		// TODO: Make sure script is not enqueued when no style is selected in styling settings
	}

	/**
	 * @covers FrmStylesHelper::enqueue_jquery_css
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
