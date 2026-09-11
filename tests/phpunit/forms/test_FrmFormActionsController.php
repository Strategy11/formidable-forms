<?php

/**
 * @group forms
 */
class test_FrmFormActionsController extends FrmUnitTest {

	/**
	 * Make sure the form action post type exists
	 *
	 * @todo check for taxonomies and other settings
	 * @todo create an action and get_post and check for expected values $this->factory->post->create
	 */
	public function test_register_post_types() {
		$action_post_type = FrmFormActionsController::$action_post_type;
		$this->assertContains( $action_post_type, get_post_types(), 'The ' . $action_post_type . ' is missing' );
	}

	/**
	 * @covers FrmFormActionsController::maybe_show_limit_warning
	 */
	public function test_limit_warning_tags_the_doc_link_and_drops_the_raw_url_as_anchor_text() {
		$form_id      = $this->factory->form->create();
		$form_actions = array_fill( 0, 99, null );

		ob_start();
		$this->run_private_method( array( 'FrmFormActionsController', 'maybe_show_limit_warning' ), array( $form_id, $form_actions ) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'utm_source=', $output );
		$this->assertStringContainsString( 'utm_campaign=form-action-limit', $output );
		$this->assertStringContainsString(
			'>' . esc_html__( 'Increase Limit of Form Actions', 'formidable' ) . '<',
			$output,
			'The visible anchor text should be the translated title, not the raw URL'
		);
	}
}
