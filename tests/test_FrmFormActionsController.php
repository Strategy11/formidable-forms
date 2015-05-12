<?php

class WP_Test_FrmFormActionsController extends FrmUnitTest {

	/**
	 * Make sure the form action post type exists
	 * @todo check for taxonomies and other settings
	 * @todo create an action and get_post and check for expected values $this->factory->post->create
	 */
	function test_register_post_types() {
		$post_types = get_post_types();
		$this->assertTrue( in_array( FrmFormActionsController::$action_post_type, $post_types ) );
	}

}