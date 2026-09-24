<?php
/**
 * @group fields
 */
class test_FrmFieldGdpr extends FrmUnitTest {

	/**
	 * @var int
	 */
	private $original_enable_gdpr;

	public function setUp(): void {
		parent::setUp();
		// $frm_settings is a process-wide global, not reset between tests by the DB rollback.
		$this->original_enable_gdpr = FrmAppHelper::get_settings()->enable_gdpr;
	}

	public function tearDown(): void {
		FrmAppHelper::get_settings()->enable_gdpr = $this->original_enable_gdpr;
		parent::tearDown();
	}

	/**
	 * @covers FrmFieldType::include_front_field_input
	 */
	public function test_disabled_notice_has_no_dangling_aria_labelledby() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		// FrmAppHelper::maybe_add_permissions() grants this via a separate WP_User
		// instance, which doesn't reach the current_user_can() cache for this request.
		wp_get_current_user()->add_cap( 'frm_edit_forms' );
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
	 * @covers FrmFieldType::include_front_field_input
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
				'type'                => 'gdpr',
				'gdpr_agreement_text' => 'I agree',
				'value'               => '',
				'default_value'       => '',
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
