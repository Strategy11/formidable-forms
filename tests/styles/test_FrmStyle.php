<?php

/**
 * @group styles
 */
class test_FrmStyle extends FrmUnitTest {

	/**
	 * @covers FrmStyle::maybe_sanitize_rgba_value
	 */
	public function test_maybe_sanitize_rgba_value() {
		$frm_style            = new FrmStyle();
		$invalid_color_values = array(
			'rgba(45, 45, 45,' => 'rgba(45,45,45,1)',
			'rgba(, , ,1)'     => 'rgba(0,0,0,1)',
			'rgba(45,45'       => 'rgba(45,45,0,1)',
			'rgba(355,,,0.5)'  => 'rgba(255,0,0,0.5)',
			'rgba(255,0,0,11)' => 'rgba(255,0,0,1)',
			'rgba(99,99,99,1)' => 'rgba(99,99,99,1)',
			'rgb(45, ,45)'     => 'rgb(45,0,45)',
			'rgb( , , )'       => 'rgb(0,0,0)',
			'rgb(300,0,-1)'    => 'rgb(255,0,0)',
			'rgb('             => 'rgb(0,0,0)',
			'rgb(255,255,255)' => 'rgb(255,255,255)',
			'(rgba(0,0,0,1)'   => 'rgba(0,0,0,1)',
			'((rgba(0,0,0,1)'  => 'rgba(0,0,0,1)',
			'(rgb(0,0,0)'      => 'rgb(0,0,0)',
			'rgba(0,0,0,1))'   => 'rgba(0,0,0,1)',
			' rgba(0,0,0,1)'   => 'rgba(0,0,0,1)',
			' (rgb(0,0,0)'     => 'rgb(0,0,0)',
			'rgba((0,0,0,1)'   => 'rgba(0,0,0,1)',
		);

		foreach ( $invalid_color_values as $color_val => $expected_color_val ) {
			$this->run_private_method( array( $frm_style, 'maybe_sanitize_rgba_value' ), array( &$color_val ) );
			$this->assertEquals( $expected_color_val, $color_val );
		}
	}

	/**
	 * @covers FrmStyle::sanitize_post_content
	 */
	public function test_sanitize_post_content() {
		$post_content = array(
			'bg_color'            => '000',
			'font_size'           => '14px',
			'title_margin_bottom' => '60px}',
			'field_height'        => ';12px',
			'field_width'         => '{10px',
		);
		$frm_style              = new FrmStyle();
		$sanitized_post_content = $frm_style->sanitize_post_content( $post_content );

		$this->assertIsArray( $sanitized_post_content );
		$this->assertEquals( '000', $sanitized_post_content['bg_color'] );
		$this->assertEquals( '14px', $sanitized_post_content['font_size'] );
		$this->assertEquals( '60px', $sanitized_post_content['title_margin_bottom'] );
		$this->assertEquals( '12px', $sanitized_post_content['field_height'] );
		$this->assertEquals( '10px', $sanitized_post_content['field_width'] );
	}
}
