<?php
/**
 * @group styles
 */
class WP_Test_FrmStylesController extends FrmUnitTest {
	/**
	 * Make sure the stylesheet is loaded at the right times
	 */
	public function test_front_head() {
		$this->set_front_end();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->markTestSkipped( 'Run with --group styles' );
		}

        ob_start();
        do_action( 'wp_head' );
        $styles = ob_get_contents();
        ob_end_clean();

		$this->assertNotEmpty( $styles );

		$frm_settings = FrmAppHelper::get_settings();
		$stylesheet_urls = $this->get_custom_stylesheet();
		$style_included = strpos( $styles, $stylesheet_urls['formidable'] );
		if ( $frm_settings->load_style == 'all' ) {
			$this->assertTrue( $style_included !== false, 'The formidablepro stylesheet is missing' );
		} else {
			$this->assertFalse( $style_included, 'The formidablepro stylesheet is included when it should not be' );
		}
	}

	/**
	 * @covers FrmStylesController::custom_stylesheet
	 */
	private function get_custom_stylesheet() {
		global $frm_vars;
		$frm_vars['css_loaded'] = false;

		$stylesheet_urls = FrmStylesController::custom_stylesheet();
		$this->assertTrue( isset( $stylesheet_urls['formidable'] ), 'The stylesheet array is empty' );
		return $stylesheet_urls;
	}
}