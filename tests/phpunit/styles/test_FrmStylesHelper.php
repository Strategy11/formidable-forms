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
		$base             = FrmStylesHelper::get_upload_base();
		$this->assertTrue( strpos( $base['baseurl'], 'https://' ) !== false );
	}

	/**
	 * @covers FrmStylesHelper::get_settings_for_output
	 */
	public function test_get_settings_for_output() {
		$frm_style = new FrmStyle( 'default' );
		$style     = $frm_style->get_one();
		$settings  = FrmStylesHelper::get_settings_for_output( $style );
		$expected  = 'frm_style_' . $style->post_name . '.with_frm_style';
		$this->assertEquals( $expected, $settings['style_class'] );

		$_POST = array(
			'action'            => 'frm_change_styling',
			'style_name'        => 'frm_style_test',
			'frm_style_setting' => array(
				'post_content' => $frm_style->get_defaults(),
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

		$frm_style    = new FrmStyle( 'default' );
		$style        = $frm_style->get_one();
		$settings     = FrmStylesHelper::get_settings_for_output( $style );
		$css_contains = substr_count( $compiled_css, '}.frm_forms.' . $settings['style_class'] . '{' );
		$this->assertEquals( 1, $css_contains, 'Multiple or no occurrences of style found' );
	}

	/**
	 * @covers FrmStylesHelper::hex2rgb
	 */
	public function test_hex2rgb() {
		$colors = array(
			'ffffff'            => '255,255,255',
			'#ffffff'           => '255,255,255',
			'262626'            => '38,38,38',
			'rgb(255,255,255)'  => '255,255,255',
			'rgba(211,77,40,1)' => '211,77,40',
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
			array(
				'start' => 'rgba(32,14,237,1)',
				'steps' => 45,
				'end'   => 'rgba(77,59,255,1)',
			),
		);

		foreach ( $colors as $color ) {
			$result = FrmStylesHelper::adjust_brightness( $color['start'], $color['steps'] );
			$this->assertEquals( $color['end'], $result, $color['start'] . ' was not adjusted as expected by ' . $color['steps'] . ' steps' );
		}
	}

	/**
	 * @covers FrmStylesHelper::get_form_count_for_style
	 */
	public function test_get_form_count_for_style() {
		$new_style_id = $this->factory->post->create(
			array( 'post_type' => FrmStylesController::$post_type )
		);
		$this->assertEquals( 0, FrmStylesHelper::get_form_count_for_style( $new_style_id, false ) );

		$this->factory->form->create(
			array(
				'status'  => 'published',
				'options' => array(
					'custom_style' => (string) $new_style_id,
				),
			)
		);
		$this->assertEquals( 1, FrmStylesHelper::get_form_count_for_style( $new_style_id, false ) );

		$data_for_all_published_forms = FrmDb::get_results( 'frm_forms', array( 'status' => 'published' ), 'id, options' );
		$default_count                = 0;
		foreach ( $data_for_all_published_forms as $row ) {
			$form_id = $row->id;
			$options = $row->options;
			FrmAppHelper::unserialize_or_decode( $options );

			if ( ! isset( $options['custom_style'] ) || in_array( $options['custom_style'], array( '1', 1 ), true ) ) {
				++$default_count;
			}

			unset( $options );
		}

		unset( $data_for_all_published_forms );

		$this->assertEquals( 1 + $default_count, FrmStylesHelper::get_form_count_for_style( $new_style_id, true ) );

		$conversational_style_id = $this->factory->post->create(
			array(
				'post_type' => FrmStylesController::$post_type,
				'post_name' => 'lines-no-boxes',
			)
		);

		// Clear the cache after adding the conversational style.
		$where     = array( 'post_name' => 'lines-no-boxes' );
		$args      = array( 'limit' => 1 );
		$cache_key = FrmDb::generate_cache_key( $where, $args, 'ID', 'var' );
		wp_cache_delete( $cache_key, 'post' );

		$this->assertEquals( 0, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, true ) );

		// Add a form with the conversational default.
		$this->factory->form->create(
			array(
				'options' => array(
					'custom_style' => 1,
					'chat'         => 1,
				),
			)
		);

		$this->assertEquals( 1, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, true ) );
		$this->assertEquals( 0, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, false ) );

		// Create a second conversational form.
		$this->factory->form->create(
			array(
				'options' => array(
					'custom_style' => (string) $conversational_style_id,
					'chat'         => 1,
				),
			)
		);

		$this->assertEquals( 1, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, false ) );
		$this->assertEquals( 2, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, true ) );
	}

	/**
	 * @covers FrmStylesHelper::get_color_brightness
	 */
	public function test_get_color_brightness() {
		$white_brightness = 255;
		$black_brightness = 0;
		$red_brightness   = 76.245;
		$green_brightness = 149.685;
		$blue_brightness  = 29.07;

		// Test hex colors.
		$this->assert_color_brightness( $white_brightness, 'ffffff' );
		$this->assert_color_brightness( $black_brightness, '000000' );
		$this->assert_color_brightness( $red_brightness, 'ff0000' );
		$this->assert_color_brightness( $green_brightness, '00ff00' );
		$this->assert_color_brightness( $blue_brightness, '0000ff' );

		// Test rgb colors.
		$this->assert_color_brightness( $white_brightness, 'rgb(255, 255, 255)' );
		$this->assert_color_brightness( $black_brightness, 'rgb(0, 0, 0)' );
		$this->assert_color_brightness( $red_brightness, 'rgb(255, 0, 0)' );
		$this->assert_color_brightness( $green_brightness, 'rgb(0, 255, 0)' );
		$this->assert_color_brightness( $blue_brightness, 'rgb(0, 0, 255)' );

		// Test rgba.
		$this->assert_color_brightness( $white_brightness, 'rgba(255, 255, 255, 1)' );
		$this->assert_color_brightness( $black_brightness, 'rgba(0, 0, 0, 1)' );
		$this->assert_color_brightness( $red_brightness, 'rgba(255, 0, 0, 1)' );
		$this->assert_color_brightness( $green_brightness, 'rgba(0, 255, 0,1 )' );
		$this->assert_color_brightness( $blue_brightness, 'rgba(0, 0, 255, 1)' );

		// Test hsl colors.
		$this->assert_color_brightness( $white_brightness, 'hsl(0, 0%, 100%)' );
		$this->assert_color_brightness( $black_brightness, 'hsl(0, 0%, 0%)' );
		$this->assert_color_brightness( $red_brightness, 'hsl(0, 100%, 50%)' );
		$this->assert_color_brightness( $green_brightness, 'hsl(120, 100%, 50%)' );
		$this->assert_color_brightness( $blue_brightness, 'hsl(240, 100%, 50%)' );

		// Test hsla colors.
		$this->assert_color_brightness( $white_brightness, 'hsla(0, 0%, 100%, 1)' );
		$this->assert_color_brightness( $black_brightness, 'hsla(0, 0%, 0%, 1)' );
		$this->assert_color_brightness( $red_brightness, 'hsla(0, 100%, 50%, 1)' );
		$this->assert_color_brightness( $green_brightness, 'hsla(120, 100%, 50%, 1)' );
		$this->assert_color_brightness( $blue_brightness, 'hsla(240, 100%, 50%, 1)' );
	}

	/**
	 * @param float  $expected
	 * @param string $color
	 *
	 * @return void
	 */
	private function assert_color_brightness( $expected, $color ) {
		$this->assertEquals( $expected, FrmStylesHelper::get_color_brightness( $color ) );
	}

	/**
	 * @covers FrmStylesHelper::get_bottom_value
	 */
	public function test_get_bottom_value() {
		$expected = array(
			''                   => '',
			'10px'               => '10px',
			'5em 10px'           => '5em',
			'5em 10px 5px'       => '5px',
			'5em 10px 15px 20px' => '15px',
		);

		foreach ( $expected as $input => $output ) {
			$this->assertEquals( $output, FrmStylesHelper::get_bottom_value( $input ) );
		}
	}
}
