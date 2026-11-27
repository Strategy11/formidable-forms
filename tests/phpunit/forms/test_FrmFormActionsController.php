<?php

/**
 * @group forms
 *
 * @covers FrmFormActionsController
 */
#[\PHPUnit\Framework\Attributes\Group( 'forms' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFormActionsController::class )]
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

	/**
	 * Installed actions show as their own link, never also as a locked upgrade card,
	 * in both the sectioned My Actions group and a regular group.
	 *
	 * @return void
	 */
	public function test_email_settings_skips_upgrade_cards_for_installed_actions() {
		$this->set_current_user_to_1();
		$form_id = $this->factory->form->create();

		$set_groups = static function () {
			return array(
				'my_actions' => array(
					'name'     => 'My Actions',
					'sections' => array(
						'active' => array(
							'name'    => '',
							'actions' => array( 'email', 'frm_test_locked_in_section' ),
						),
					),
				),
				'misc'       => array(
					'name'    => 'Misc',
					'actions' => array( 'email', 'frm_test_locked_in_group' ),
				),
			);
		};
		add_filter( 'frm_action_groups', $set_groups );

		ob_start();
		FrmFormActionsController::email_settings( array( 'id' => $form_id ) );
		$output = ob_get_clean();

		remove_filter( 'frm_action_groups', $set_groups );

		$this->assertStringContainsString( 'data-actiontype="frm_test_locked_in_section"', $output );
		$this->assertStringContainsString( 'data-actiontype="frm_test_locked_in_group"', $output );
		$this->assertSame( 2, substr_count( $output, 'data-actiontype="email"' ), 'The installed email action should render once per group, as its own link only.' );
		$this->assertStringNotContainsString( 'data-medium="settings-email"', $output, 'An installed action should not also get a locked upgrade card.' );
	}
}
