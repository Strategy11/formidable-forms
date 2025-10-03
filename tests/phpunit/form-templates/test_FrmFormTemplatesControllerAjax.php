<?php

/**
 * @group form-templates
 */
class test_FrmFormTemplatesControllerAjax extends FrmAjaxUnitTest {

	private $controller;

	public function setUp(): void {
		parent::setUp();

		// Create admin user.
		$this->set_as_user_role( 'administrator' );

		// Assign the FrmFormTemplatesController class to a property for easier reference in tests.
		$this->controller = 'FrmFormTemplatesController';
	}

	/**
	 * @covers FrmFormTemplatesController::ajax_add_or_remove_favorite
	 */
	public function test_ajax_add_or_remove_favorite() {
		$_POST    = array(
			'action'             => 'frm_add_or_remove_favorite_template',
			'nonce'              => wp_create_nonce( 'frm_ajax' ),
			'template_id'        => array_rand( $this->controller::FEATURED_TEMPLATES_IDS ),
			'operation'          => 'add',
			'is_custom_template' => 'false',
		);
		$response = $this->trigger_action( $_POST['action'] );

		// Decode the response and get the favorite templates.
		$response_favorites = json_decode( $response, true )['data'];

		// Get the current state of favorite templates after the AJAX action.
		$current_favorites = $this->controller::get_favorite_templates();

		// Assert that the arrays are equal.
		$this->assertEquals( $current_favorites, $response_favorites, 'The favorite templates from AJAX response should match the current state.' );
	}

	/**
	 * @covers FrmFormTemplatesController::ajax_create_template
	 */
	public function test_ajax_create_template() {
		$_POST    = array(
			'action' => 'frm_create_template',
			'nonce'  => wp_create_nonce( 'frm_ajax' ),
			'xml'    => '1',
			'name'   => 'Contact Us Template',
			'desc'   => 'Lorem ipsum dolor sit amet consectetur.',
		);
		$response = $this->trigger_action( $_POST['action'] );

		// Decode the response to an array.
		$response_array = json_decode( $response, true );

		// Assert that the 'redirect' key exists in the response.
		$this->assertArrayHasKey( 'redirect', $response_array, 'The response should have a redirect key.' );
	}
}
