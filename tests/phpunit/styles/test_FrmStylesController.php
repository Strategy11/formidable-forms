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
	 * The styler edit page's "Quick Settings" panel and its "Advanced Settings"
	 * accordion sections both render into the DOM unconditionally (only one is
	 * shown at a time via CSS), so an id reused between a quick-settings control
	 * and its advanced-settings equivalent collides and breaks any ARIA property
	 * that references it (aria_id_unique).
	 *
	 * @covers FrmStylesController::render_style_page
	 */
	public function test_render_style_page_has_no_duplicate_ids() {
		$this->set_current_user_to_1();

		// render_style_page() reads $_GET to decide the view ('edit' vs 'list'); a leftover
		// 'form'/'style_id' from another test would silently switch this to the list view.
		$_GET = array();

		$form_id      = $this->factory->form->create();
		$form         = FrmForm::getOne( $form_id );
		$frm_style    = new FrmStyle( 'default' );
		$active_style = $frm_style->get_one();

		ob_start();
		$this->run_private_method(
			array( 'FrmStylesController', 'render_style_page' ),
			array( $active_style, $form, $active_style )
		);
		$html = ob_get_clean();

		$this->assert_no_duplicate_element_ids(
			$html,
			array(
				'frm_field_pad',
				'frm_field_margin',
				'frm_border_radius',
				'frm_fieldset_color',
				// The renamed quick-settings/form-title ids themselves, so a future edit that
				// deletes one of these elements (instead of just re-duplicating its id) still
				// fails loudly here.
				'frm_style_qsettings_field_pad',
				'frm_style_qsettings_field_margin',
				'frm_style_qsettings_border_radius',
				'frm_title_color',
			)
		);
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
}
