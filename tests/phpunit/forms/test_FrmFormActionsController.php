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

	public function tearDown(): void {
		delete_option( 'frm_options' );
		delete_user_meta( get_current_user_id(), 'frm_dismiss_default_email_message' );
		parent::tearDown();
	}

	/**
	 * On a fresh site, from_email is unset, so the effective From address
	 * falls back to admin_email, same as default_email. The notice should
	 * show because To and From really are the same address.
	 */
	public function test_should_show_notice_when_from_email_is_unset() {
		delete_option( 'frm_options' );

		$form_action               = new stdClass();
		$form_action->post_excerpt = 'email';

		$this->assertTrue( FrmFormActionsController::should_show_notice_about_using_the_same_to_from_email( $form_action ) );
	}

	/**
	 * When from_email is explicitly set to a different address than
	 * default_email, the notice should not show.
	 */
	public function test_should_not_show_notice_when_from_email_differs() {
		$settings = FrmAppHelper::get_settings();
		$settings->update_setting( 'default_email', 'to@example.com', 'sanitize_email' );
		$settings->update_setting( 'from_email', 'from@example.com', 'sanitize_email' );
		$settings->store();

		$form_action               = new stdClass();
		$form_action->post_excerpt = 'email';

		$this->assertFalse( FrmFormActionsController::should_show_notice_about_using_the_same_to_from_email( $form_action ) );
	}

	/**
	 * Dismissing the notice persists via user meta.
	 */
	public function test_should_not_show_notice_when_dismissed() {
		delete_option( 'frm_options' );
		$admin_id = $this->set_user_by_role( 'administrator' );
		update_user_meta( $admin_id, 'frm_dismiss_default_email_message', 1 );

		$form_action               = new stdClass();
		$form_action->post_excerpt = 'email';

		$this->assertFalse( FrmFormActionsController::should_show_notice_about_using_the_same_to_from_email( $form_action ) );
	}
}
