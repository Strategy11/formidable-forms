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
	 * @covers FrmStyle::strip_invalid_characters
	 */
	public function test_sanitize_post_content() {
		$post_content           = array(
			'bg_color'             => '000',
			'font_size'            => '14px',
			'title_margin_bottom'  => '60px}',
			'field_height'         => ';12px',
			'field_width'          => '{10px',
			'width'                => 'calc(100% / 3)',
			'section_color'        => 'rgba(255,255,255,1)',
			'submit_border_color'  => 'ffffff',
			'submit_active_color'  => 'rgb(255,255,255)',
			'progress_bg_color'    => '000(',
			'success_bg_color'     => ')fff',
			'section_border_width' => '[12px',
			'section_font_size'    => '16px]',
			'unsupported_key'      => 'fff',
			'custom_css'           => '.my-class { color: red; }',
		);
		$frm_style              = new FrmStyle();
		$sanitized_post_content = $frm_style->sanitize_post_content( $post_content );

		$this->assertIsArray( $sanitized_post_content );
		$this->assertEquals( '000', $sanitized_post_content['bg_color'] );
		$this->assertEquals( '14px', $sanitized_post_content['font_size'] );
		$this->assertEquals( '60px', $sanitized_post_content['title_margin_bottom'] );
		$this->assertEquals( '12px', $sanitized_post_content['field_height'] );
		$this->assertEquals( '10px', $sanitized_post_content['field_width'] );
		$this->assertEquals( 'calc(100% / 3)', $sanitized_post_content['width'] );
		$this->assertEquals( 'rgba(255,255,255,1)', $sanitized_post_content['section_color'] );
		$this->assertEquals( 'ffffff', $sanitized_post_content['submit_border_color'] );
		$this->assertEquals( 'rgb(255,255,255)', $sanitized_post_content['submit_active_color'] );
		$this->assertEquals( '000', $sanitized_post_content['progress_bg_color'] );
		$this->assertEquals( 'fff', $sanitized_post_content['success_bg_color'] );
		$this->assertEquals( '12px', $sanitized_post_content['section_border_width'] );
		$this->assertEquals( '16px', $sanitized_post_content['section_font_size'] );
		$this->assertEquals( '.my-class { color: red; }', $sanitized_post_content['custom_css'] );
		$this->assertFalse( array_key_exists( 'unsupported_key', $sanitized_post_content ) );
	}

	/**
	 * @covers FrmStyle::strip_invalid_characters
	 */
	public function test_strip_invalid_characters() {
		// Make sure that braces don't get added to sizes but removed instead.
		$this->assertEquals( '12px', $this->strip_invalid_characters( '12px(' ) );
		$this->assertEquals( '2rem', $this->strip_invalid_characters( ')2rem' ) );
		$this->assertEquals( '10pt', $this->strip_invalid_characters( '(10pt' ) );
		$this->assertEquals( '100%', $this->strip_invalid_characters( '100%)' ) );
		$this->assertEquals( '14px', $this->strip_invalid_characters( '(14px)' ) );
		$this->assertEquals( '20PX', $this->strip_invalid_characters( ')20PX' ), 'strip_invalid_characters should be case insensitive' );

		// Test CSS vars.
		$this->assertEquals( 'var(--grey)', $this->strip_invalid_characters( '(var(--grey)' ) );
		$this->assertEquals( 'var(--white)', $this->strip_invalid_characters( '(var(--white)))' ) );

		// Test some calc() rules with extra braces.
		$this->assertEquals( 'calc(50%/3)', $this->strip_invalid_characters( '(calc(50%/3)' ) );
		$this->assertEquals( 'calc(10%*5)', $this->strip_invalid_characters( ')calc(10%*5)' ) );

		// Test some things that should not change.
		$this->assertEquals( 'fff', $this->strip_invalid_characters( 'fff' ) );
		$this->assertEquals( '12px', $this->strip_invalid_characters( '12px' ) );
		$this->assertEquals( 'rgb(0,0,0)', $this->strip_invalid_characters( 'rgb(0,0,0)' ) );
		$this->assertEquals( 'calc(100%/6)', $this->strip_invalid_characters( 'calc(100%/6)' ) );
	}

	private function strip_invalid_characters( $input ) {
		$frm_style = new FrmStyle();
		return $this->run_private_method( array( $frm_style, 'strip_invalid_characters' ), array( $input ) );
	}

	/**
	 * @covers FrmStyle::force_balanced_quotation
	 */
	public function test_force_balanced_quotation() {
		$frm_style = new FrmStyle();

		// Test a case where nothing changes.
		$this->assertEquals( '"Arial"', $frm_style->force_balanced_quotation( '"Arial"' ) );

		// Balance a missing " at the end.
		$this->assertEquals( '"Verdana"', $frm_style->force_balanced_quotation( '"Verdana' ) );

		// Balance a missing ' at the end.
		$this->assertEquals( "'Times New Roman'", $frm_style->force_balanced_quotation( "'Times New Roman" ) );

		// Balance a missing " at the front.
		$this->assertEquals( '"Helvetica"', $frm_style->force_balanced_quotation( 'Helvetica"' ) );

		// Balance a missing ' at the front.
		$this->assertEquals( "'Comic Sans'", $frm_style->force_balanced_quotation( "Comic Sans'" ) );
	}

	/**
	 * @gcovers FrmStyle::trim_braces
	 */
	public function test_trim_braces() {
		$this->assertEquals( 'calc(100%)', $this->trim_braces( '(calc(100%)))' ) );
		$this->assertEquals( 'skewX(5px)', $this->trim_braces( '((skewX(5px)' ) );
		$this->assertEquals( 'var(--grey)', $this->trim_braces( '(var(--grey))' ) );
		$this->assertEquals( 'scale(2)', $this->trim_braces( '(scale(2)))' ) );
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	private function trim_braces( $value ) {
		$frm_style = new FrmStyle();
		return $this->run_private_method( array( $frm_style, 'trim_braces' ), array( $value ) );
	}
}
