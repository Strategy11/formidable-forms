<?php

/**
 * @group settings
 */
#[\PHPUnit\Framework\Attributes\Group( 'settings' )]
class test_FrmSettingsController extends FrmUnitTest {

	/**
	 * Add-ons read `action` (not `frm_action`) in their Global Settings routes.
	 * Without it, they re-render the form instead of saving.
	 *
	 * @covers FrmSettingsController::display_form
	 */
	public function test_display_form_includes_action_field_for_add_ons() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		ob_start();
		FrmSettingsController::display_form();
		$html = ob_get_clean();

		$this->assertStringContainsString( '<input type="hidden" name="frm_action" value="process-form"/>', $html );
		$this->assertStringContainsString( '<input type="hidden" name="action" value="process-form"/>', $html );
	}
}
