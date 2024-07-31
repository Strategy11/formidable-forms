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
		$post_types       = get_post_types();
		$action_post_type = FrmFormActionsController::$action_post_type;
		$this->assertTrue( in_array( $action_post_type, $post_types, true ), 'The ' . $action_post_type . ' is missing' );
	}
}
