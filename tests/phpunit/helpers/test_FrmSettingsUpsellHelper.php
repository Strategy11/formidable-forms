<?php

/**
 * @group settings
 */
class test_FrmSettingsUpsellHelper extends FrmUnitTest {

	/**
	 * @covers FrmSettingsUpsellHelper::add_upgrade_modal_atts
	 */
	public function test_add_upgrade_modal_atts_tags_the_learn_more_link() {
		$atts = FrmSettingsUpsellHelper::add_upgrade_modal_atts(
			array(),
			'field_visibility',
			'Visibility options',
			'/field-options/#kb-visibility'
		);

		$this->assertStringContainsString( 'formidableforms.com/knowledgebase/field-options/', $atts['data-learn-more'] );
		$this->assertStringEndsWith( '#kb-visibility', $atts['data-learn-more'] );
		$this->assertStringContainsString( 'utm_source=', $atts['data-learn-more'] );
		$this->assertStringContainsString( 'utm_campaign=field_visibility', $atts['data-learn-more'] );
	}
}
