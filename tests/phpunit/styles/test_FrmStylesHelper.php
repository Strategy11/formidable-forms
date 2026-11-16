<?php

/**
 * @group styles
 *
 * @covers FrmStylesHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'styles' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmStylesHelper::class )]
class test_FrmStylesHelper extends FrmUnitTest {

	public function test_get_upload_base() {
		$base = FrmStylesHelper::get_upload_base();
		$this->assertArrayHasKey( 'baseurl', $base );
		$this->assertStringContainsString( 'http://', $base['baseurl'] );

		$_SERVER['HTTPS'] = 'on';
		$base             = FrmStylesHelper::get_upload_base();
		$this->assertStringContainsString( 'https://', $base['baseurl'] );
	}

	public function test_get_settings_for_output() {
		$frm_style = new FrmStyle( 'default' );
		$style     = $frm_style->get_one();
		$settings  = FrmStylesHelper::get_settings_for_output( $style );
		$expected  = 'frm_style_' . $style->post_name . '.with_frm_style';
		$this->assertSame( $expected, $settings['style_class'] );

		$_POST = array(
			'action'            => 'frm_change_styling',
			'style_name'        => 'frm_style_test',
			'frm_style_setting' => array(
				'post_content' => $frm_style->get_defaults(),
			),
		);

		$settings = FrmStylesHelper::get_settings_for_output( $style );
		$expected = 'frm_style_test.with_frm_style';
		$this->assertSame( $expected, $settings['style_class'] );
		$this->assertSame( '#000000', $settings['fieldset_color'] );
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
		$this->assertSame( 1, $css_contains, 'Multiple or no occurrences of style found' );
	}

	public function test_hex2rgb() {
		$colors = array(
			'ffffff'            => '255,255,255',
			'#ffffff'           => '255,255,255',
			'262626'            => '38,38,38',
			'rgb(255,255,255)'  => '255,255,255',
			'rgba(211,77,40,1)' => '211,77,40',
		);

		foreach ( $colors as $hex => $rgb ) {
			$this->assertSame( $rgb, FrmStylesHelper::hex2rgb( $hex ), 'Hex ' . $hex . ' did not convert to ' . $rgb );
		}
	}

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
			$this->assertSame( $color['end'], $result, $color['start'] . ' was not adjusted as expected by ' . $color['steps'] . ' steps' );
		}
	}

	public function test_color_formats_in_generated_colors() {
		$colors = array(
			'#f80'                       => '255,136,0',
			'#ff880080'                  => '255,136,0',
			'RGB(255, 136, 0)'           => '255,136,0',
			'rgb(100% 50% 0% / 50%)'     => '255,128,0',
			'hsl(30, 100%, 50%)'         => '255,128,0',
			'hsla(30, 100%, 50%, 0.25)'  => '255,128,0',
			'hsl(390deg 100% 50% / .25)' => '255,128,0',
			'hsl(-330, 100%, 50%)'       => '255,128,0',
			'hsl(.5turn 100% 50%)'       => '0,255,255',
			'hsl(33.5, 100%, 50%)'       => '255,142,0',
		);

		foreach ( $colors as $color => $expected ) {
			$this->assertSame( $expected, FrmStylesHelper::hex2rgb( $color ), $color );
			$this->assertSame( 'rgba(' . $expected . ',0.4)', FrmStylesHelper::hex2rgba( $color, 0.4 ), $color );
			$this->assertTrue( FrmStylesHelper::is_valid_color( $color ), $color );
		}

		$this->assertSame( '#ff8a0a', FrmStylesHelper::adjust_brightness( 'rgb(255, 128, 0)', 10 ) );
		$this->assertSame( '#ff8a0a', FrmStylesHelper::adjust_brightness( 'hsl(30, 100%, 50%)', 10 ) );
		$this->assertSame( 'rgba(255,138,10,0.25)', FrmStylesHelper::adjust_brightness( 'hsla(30, 100%, 50%, .25)', 10 ) );
		$this->assertSame( 'rgba(255,10,10,0.533)', FrmStylesHelper::adjust_brightness( '#f008', 10 ) );
		$this->assertSame( 'rgba(255,10,10,0)', FrmStylesHelper::adjust_brightness( '#ff000000', 10 ) );
		$this->assertSame( 151.381, FrmStylesHelper::get_color_brightness( 'hsl(30, 100%, 50%)' ) );
	}

	public function test_invalid_functional_colors() {
		$colors = array(
			'rgb(1/2/3)',
			'rgb(1, 2 3)',
			'rgb(1 2 3 .5)',
			'rgb()',
			'rgb(nope)',
			'rgb(1,2)',
			'hsl(30,50,50)',
			'hsl()',
			'hsl(20, 30%, 40%);color:red',
			'rgb(1,2,3) url(test)',
		);

		foreach ( $colors as $color ) {
			$this->assertFalse( FrmStylesHelper::is_valid_color( $color ), $color );
			$this->assertSame( '0,0,0', FrmStylesHelper::hex2rgb( $color ), $color );
			$this->assertSame( 0, FrmStylesHelper::get_color_brightness( $color ), $color );
			$this->assertSame( $color, FrmStylesHelper::adjust_brightness( $color, 10 ), $color );
		}
	}

	public function test_get_form_count_for_style() {
		$new_style_id = $this->factory->post->create(
			array( 'post_type' => FrmStylesController::$post_type )
		);
		$this->assertSame( 0, FrmStylesHelper::get_form_count_for_style( $new_style_id, false ) );

		$this->factory->form->create(
			array(
				'status'  => 'published',
				'options' => array(
					'custom_style' => (string) $new_style_id,
				),
			)
		);
		$this->assertSame( 1, FrmStylesHelper::get_form_count_for_style( $new_style_id, false ) );

		$data_for_all_published_forms = FrmDb::get_results( 'frm_forms', array( 'status' => 'published' ), 'id, options' );
		$default_count                = 0;

		foreach ( $data_for_all_published_forms as $row ) {
			$options = $row->options;
			FrmAppHelper::unserialize_or_decode( $options );

			if ( ! isset( $options['custom_style'] ) || in_array( $options['custom_style'], array( '1', 1 ), true ) ) {
				++$default_count;
			}

			unset( $options );
		}

		unset( $data_for_all_published_forms );

		$this->assertSame( 1 + $default_count, FrmStylesHelper::get_form_count_for_style( $new_style_id, true ) );

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

		$this->assertSame( 0, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, true ) );

		// Add a form with the conversational default.
		$this->factory->form->create(
			array(
				'options' => array(
					'custom_style' => 1,
					'chat'         => 1,
				),
			)
		);

		$this->assertSame( 1, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, true ) );
		$this->assertSame( 0, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, false ) );

		// Create a second conversational form.
		$this->factory->form->create(
			array(
				'options' => array(
					'custom_style' => (string) $conversational_style_id,
					'chat'         => 1,
				),
			)
		);

		$this->assertSame( 1, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, false ) );
		$this->assertSame( 2, FrmStylesHelper::get_form_count_for_style( $conversational_style_id, true ) );
	}

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
		$this->assert_color_brightness( $white_brightness, '#ffffff' );
		$this->assert_color_brightness( $black_brightness, '#000000' );
		$this->assert_color_brightness( $red_brightness, '#FF0000' );
		$this->assert_color_brightness( $green_brightness, '#00ff00' );
		$this->assert_color_brightness( $blue_brightness, '#0000ff' );
		$this->assert_color_brightness( $white_brightness, '#fff' );
		$this->assert_color_brightness( $black_brightness, '#000' );
		$this->assert_color_brightness( $red_brightness, '#F00' );
		$this->assert_color_brightness( $green_brightness, '0f0' );
		$this->assert_color_brightness( $blue_brightness, '00f' );

		// Test hex colors with alpha.
		$this->assert_color_brightness( $red_brightness, '#ff000080' );
		$this->assert_color_brightness( $red_brightness, 'ff000080' );
		$this->assert_color_brightness( $green_brightness, '#00FF0000' );
		$this->assert_color_brightness( $blue_brightness, '0000ffff' );

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
		$this->assertSame( $expected, FrmStylesHelper::get_color_brightness( $color ) );
	}

	public function test_get_color_brightness_with_invalid_hex() {
		foreach ( array( '', '#', 'transparent', 'var(--color)', '#gggggg', '12', '12345', '#1234567', '#ff0000gg', 'ff0000gg', '#ff0000800' ) as $color ) {
			$this->assertSame( 0, FrmStylesHelper::get_color_brightness( $color ), $color );
		}
	}

	public function test_get_bottom_value() {
		$expected = array(
			''                   => '',
			'10px'               => '10px',
			'5em 10px'           => '5em',
			'5em 10px 5px'       => '5px',
			'5em 10px 15px 20px' => '15px',
		);

		foreach ( $expected as $input => $output ) {
			$this->assertSame( $output, FrmStylesHelper::get_bottom_value( $input ) );
		}
	}

	/**
	 * @covers FrmStylesHelper::get_live_preview_settings
	 */
	public function test_get_live_preview_settings() {
		$settings = FrmStylesHelper::get_live_preview_settings();

		$this->assertContains( 'title_color', $settings );
		$this->assertContains( 'fieldset_bg_color', $settings );
		$this->assertSame( array_values( array_unique( $settings ) ), $settings );

		// Pro versions that are still supported use these directly in their CSS templates.
		$used_directly_by_pro = array(
			'font_size',
			'label_color',
			'field_pad',
			'collapse_pos',
			'error_text',
			'success_text_color',
			'section_border_color',
			'progress_border_color',
			'progress_size',
		);

		foreach ( $used_directly_by_pro as $key ) {
			$this->assertNotContains( $key, $settings );
		}

		$filter = function ( $settings ) {
			$settings   = array_diff( $settings, array( 'title_color' ) );
			$settings[] = 'toggle_on_color';
			$settings[] = 'toggle_on_color';
			$settings[] = 123;
			$settings[] = array( 'not_a_key' );
			return $settings;
		};
		add_filter( 'frm_style_live_preview_settings', $filter );
		$filtered = FrmStylesHelper::get_live_preview_settings();
		remove_filter( 'frm_style_live_preview_settings', $filter );

		$this->assertNotContains( 'title_color', $filtered );
		$this->assertContains( 'toggle_on_color', $filtered );
		$this->assertSame( array_values( array_unique( $filtered ) ), $filtered );
		$this->assertCount( count( $settings ), $filtered );

		foreach ( $filtered as $key ) {
			$this->assertIsString( $key );
		}

		add_filter( 'frm_style_live_preview_settings', '__return_null' );
		$this->assertSame( array(), FrmStylesHelper::get_live_preview_settings() );
		remove_filter( 'frm_style_live_preview_settings', '__return_null' );
	}

	/**
	 * @covers FrmStylesHelper::get_base_font_size_settings
	 */
	public function test_get_base_font_size_settings() {
		$settings = FrmStylesHelper::get_base_font_size_settings();
		$this->assertContains( 'font_size', $settings );
		$this->assertContains( 'field_font_size', $settings );

		foreach ( $settings as $key ) {
			$this->assertIsString( $key );
		}
	}
}
