<?php
/**
 * Layout overrides must survive unrelated Styler preview refreshes.
 *
 * @group styles
 */
class test_FrmStylesPreviewAlignment extends FrmUnitTest {
	public function test_preview_alignment_only_targets_inherited_fields() {
		ob_start();
		FrmStylesPreviewHelper::get_additional_preview_style(
			array(
				'radio_align' => 'block',
				'check_align' => 'inline',
			),
			true
		);
		$css = ob_get_clean();
		$this->assertStringNotContainsString( '#frm_style_preview .with_frm_style .frm_radio {', $css, 'An unrelated setting must not override a radio layout exception.' );
		$this->assertStringNotContainsString( '#frm_style_preview .with_frm_style .frm_checkbox {', $css, 'An unrelated setting must not override a checkbox layout exception.' );
		$this->assertStringContainsString( '.frm-default-option-align .frm_radio', $css, 'Inherited radio layouts should still follow the style.' );
		$this->assertStringContainsString( '.frm-default-option-align .frm_checkbox', $css, 'Inherited checkbox layouts should still follow the style.' );
		$this->assertStringContainsString( '#frm_sample_form .with_frm_style .frm_radio', $css, 'The sample form should still preview layout changes.' );
	}
}
