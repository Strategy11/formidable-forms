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

		// Reset if the style was loaded in another test
		global $frm_vars, $wp_styles;
		$frm_vars['css_loaded'] = false;

		if ( in_array( 'formidable', $wp_styles->done, true ) ) {
			$k = array_search( 'formidable', $wp_styles->done, true );
			unset( $wp_styles->done[ $k ] );
		}

		ob_start();
		wp_head();
		$styles = ob_get_clean();
		$this->assertNotEmpty( $styles );

		$frm_settings    = FrmAppHelper::get_settings();
		$stylesheet_urls = $this->get_custom_stylesheet();
		$css_html        = "<link rel='stylesheet' id='formidable-css'";

		if ( $frm_settings->load_style === 'all' ) {
			$this->assertStringContainsString( $css_html, $styles, 'The formidablepro stylesheet is missing' );
			// $this->assertContains( $stylesheet_urls['formidable'], $styles, 'The formidablepro stylesheet is missing' );
		} else {
			$this->assertStringNotContainsString( $css_html, $styles, 'The formidablepro stylesheet is missing' );
			$this->assertStringNotContainsString( $stylesheet_urls['formidable'], $styles, 'The formidablepro stylesheet is included when it should not be' );
		}
	}

	/**
	 * @covers FrmStylesController::custom_stylesheet
	 */
	private function get_custom_stylesheet() {
		global $frm_vars;
		$frm_vars['css_loaded'] = false;
		$stylesheet_urls        = FrmStylesController::custom_stylesheet();
		$this->assertArrayHasKey( 'formidable', $stylesheet_urls, 'The stylesheet array is empty' );
		return $stylesheet_urls;
	}

	/**
	 * @covers FrmStylesController::save_style
	 * @covers FrmStyle::update
	 */
	public function test_save() {
		$this->set_current_user_to_1();

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
		$returned = ob_get_clean();

		$this->assertStringContainsString( 'Your styling settings have been saved.', $returned );
		$frm_style     = new FrmStyle( $style->ID );
		$updated_style = $frm_style->get_one();
		$this->assertSame( $style->post_title . ' Updated', $updated_style->post_title );
	}

	/**
	 * Integration test: saving a style, reading the enqueued `formidable` handle's version,
	 * changing a colour and saving again must change the enqueued version. This is what actually
	 * busts a third-party cache keyed on the enqueued stylesheet URL.
	 *
	 * @covers FrmStylesController::get_css_version
	 * @covers FrmStylesController::enqueue_css
	 * @covers FrmStyle::save_settings
	 */
	public function test_css_version_changes_when_style_content_changes_after_save() {
		$this->set_current_user_to_1();
		$this->set_front_end();

		$frm_style = new FrmStyle( 'default' );
		$style     = $frm_style->get_one();

		$_POST = array(
			'ID'                => $style->ID,
			'style_name'        => $style->post_name,
			'frm_style'         => wp_create_nonce( 'frm_style_nonce' ),
			'frm_action'        => 'save',
			'frm_style_setting' => array(
				'post_title'   => $style->post_title,
				'post_content' => array_merge( $style->post_content, array( 'submit_bg_color' => '112233' ) ),
			),
		);

		FrmStylesController::save_style();
		$version_1 = $this->get_registered_formidable_css_version();
		$this->assertNotEmpty( $version_1 );

		// Change a colour and save again.
		$_POST['frm_style_setting']['post_content'] = array_merge( $style->post_content, array( 'submit_bg_color' => '445566' ) );
		FrmStylesController::save_style();
		$version_2 = $this->get_registered_formidable_css_version();

		$this->assertNotEmpty( $version_2 );
		$this->assertNotSame( $version_1, $version_2, 'The enqueued stylesheet version should change when the generated CSS content changes.' );
	}

	/**
	 * Force the `formidable` style handle to be re-registered so its version reflects the
	 * current `frm_last_style_update` option, then return that version.
	 *
	 * @return string
	 */
	private function get_registered_formidable_css_version() {
		global $wp_styles, $frm_vars;

		$frm_vars['css_loaded'] = false;

		if ( isset( $wp_styles->registered['formidable'] ) ) {
			wp_deregister_style( 'formidable' );
		}

		FrmStylesController::enqueue_css( 'register', true );

		$this->assertArrayHasKey( 'formidable', $wp_styles->registered, 'The formidable stylesheet was not registered' );

		return $wp_styles->registered['formidable']->ver;
	}
}
