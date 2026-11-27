<?php

/** The shared error modal must render without waiting for external dialog scripts. */
class test_FrmErrorModal extends FrmUnitTest {

	/** @return void */
	public function test_error_modal_includes_its_icons_and_opens_without_jquery_ui() {
		$this->set_private_property( 'FrmAppHelper', 'included_svg', false );
		ob_start();
		FrmAppController::show_error_modal(
			array(
				'title'         => 'Restore this item',
				'body'          => 'This item is in Trash.',
				'cancel_url'    => admin_url(),
				'continue_url'  => admin_url( 'edit.php?post_status=trash' ),
				'continue_text' => 'View Trash',
			)
		);
		$html = ob_get_clean();

		$this->assertStringContainsString( '<dialog id="frm_error_modal"', $html, 'The error must use a native dialog.' );
		$this->assertStringContainsString( 'aria-labelledby="frm_error_modal_title"', $html, 'The dialog must have an accessible title.' );
		$this->assertStringContainsString( 'id="frm_lock_simple"', $html, 'The icon sprite must be included on error pages.' );
		$this->assertStringContainsString( 'modalElement.showModal()', $html, 'The dialog must open immediately.' );
		$this->assertStringNotContainsString( 'jquery/ui/', $html, 'Rendering the error must not require jQuery UI downloads.' );
		$this->assertStringContainsString( 'View Trash', $html, 'The configured recovery link must remain available.' );
	}
}
