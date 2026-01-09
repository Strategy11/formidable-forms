<?php
/**
 * @group styles
 */
class test_FrmStylesController extends FrmUnitTest {

	/**
	 * Make sure the stylesheet is loaded at the right times
	 */
	public function test_front_head() {
		$this->set_front_end();

		// reset if the style was loaded in another test
		global $frm_vars, $wp_styles;
		$frm_vars['css_loaded'] = false;

		if ( in_array( 'formidable', $wp_styles->done, true ) ) {
			$k = array_search( 'formidable', $wp_styles->done, true );
			unset( $wp_styles->done[ $k ] );
		}

		ob_start();
		wp_head();
		$styles = ob_get_contents();
		ob_end_clean();
		$this->assertNotEmpty( $styles );

		$frm_settings    = FrmAppHelper::get_settings();
		$stylesheet_urls = $this->get_custom_stylesheet();
		$css_html        = "<link rel='stylesheet' id='formidable-css'";

		if ( $frm_settings->load_style === 'all' ) {
			$this->assertNotFalse( strpos( $styles, $css_html ), 'The formidablepro stylesheet is missing' );
			// $this->assertContains( $stylesheet_urls['formidable'], $styles, 'The formidablepro stylesheet is missing' );
		} else {
			$this->assertFalse( strpos( $styles, $css_html ), 'The formidablepro stylesheet is missing' );
			$this->assertFalse( strpos( $styles, $stylesheet_urls['formidable'] ), 'The formidablepro stylesheet is included when it should not be' );
		}
	}

	/**
	 * @covers FrmStylesController::custom_stylesheet
	 */
	private function get_custom_stylesheet() {
		global $frm_vars;
		$frm_vars['css_loaded'] = false;
		$stylesheet_urls        = FrmStylesController::custom_stylesheet();
		$this->assertTrue( isset( $stylesheet_urls['formidable'] ), 'The stylesheet array is empty' );
		return $stylesheet_urls;
	}

	/**
	 * @covers FrmStylesController::save_style
	 * @covers FrmStyle::update
	 */
	public function test_save() {
		$frm_style = new FrmStyle( 'default' );
		$style     = $frm_style->get_one();

		$_POST = array(
			'ID'                => $style->ID,
			'style_name'        => $style->post_name,
			'frm_style'         => wp_create_nonce( 'frm_style_nonce' ),
			'frm_action'        => 'save',
			'frm_style_setting' => array(
				'post_title'   => $style->post_title . ' Updated',
				'post_content' => $style->post_content,
			),
		);

		FrmStylesController::save_style();

		ob_start();
		FrmStylesController::save();
		$returned = ob_get_contents();
		ob_end_clean();

		$this->assertNotFalse( strpos( $returned, 'Your styling settings have been saved.' ) );
		$frm_style     = new FrmStyle( $style->ID );
		$updated_style = $frm_style->get_one();
		$this->assertEquals( $style->post_title . ' Updated', $updated_style->post_title );
	}
}
