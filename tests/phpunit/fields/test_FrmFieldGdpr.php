<?php
/**
 * @group fields
 */
class test_FrmFieldGdpr extends FrmUnitTest {

	/**
	 * @covers FrmFieldGdpr::include_front_form_file
	 */
	public function test_disabled_notice_has_no_dangling_aria_labelledby() {
		$this->set_current_user_to_1();
		FrmAppHelper::get_settings()->enable_gdpr = false;

		$html = $this->render_gdpr_field( 543 );

		$this->assertStringNotContainsString(
			'aria-labelledby',
			$html,
			'The disabled-notice branch has no element carrying the referenced id, so aria-labelledby should not be printed.'
		);
		$this->assertStringContainsString( 'GDPR field is disabled', $html );
	}

	/**
	 * @covers FrmFieldGdpr::include_front_form_file
	 */
	public function test_enabled_field_still_has_aria_labelledby() {
		FrmAppHelper::get_settings()->enable_gdpr = true;

		$html = $this->render_gdpr_field( 544 );

		$this->assertStringContainsString( 'aria-labelledby="frm-gdpr-accept-544"', $html );
		$this->assertStringContainsString( 'id="frm-gdpr-accept-544"', $html );
	}

	/**
	 * @param int $field_id
	 *
	 * @return string
	 */
	private function render_gdpr_field( $field_id ) {
		$field_type = new FrmFieldGdpr(
			array(
				'id'                  => $field_id,
				'gdpr_agreement_text' => 'I agree',
				'value'               => '',
			)
		);

		return $field_type->include_front_field_input(
			array(
				'html_id'    => 'field_gdpr_' . $field_id,
				'field_name' => 'item_meta[' . $field_id . ']',
			),
			array()
		);
	}
}
