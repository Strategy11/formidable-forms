<?php

/**
 * @group applications
 */
class test_FrmApplicationsController extends FrmUnitTest {

	/**
	 * @covers FrmApplicationsController::landing_page
	 */
	public function test_landing_page() {
		ob_start();
		FrmApplicationsController::landing_page();
		$html = ob_get_clean();
		$this->assertStringContainsString( 'id="frm_applications_container"', $html );
	}

	/**
	 * @covers FrmApplicationsController::get_prepared_template_data
	 */
	public function test_get_prepared_template_data() {
		$this->set_current_user_to_1(); // Set to admin so that locked templates get included in template data.

		$data = $this->get_prepared_template_data();
		$this->assertIsArray( $data );

		if ( ! $data ) {
			$this->markTestSkipped( 'We cannot currently reach the API, so skip the test.' );
		}

		$this->assertNotEmpty( $data );

		$template = reset( $data );
		$this->assertIsArray( $template );
		$this->assertTrue( array_key_exists( 'key', $template ) );
		$this->assertNotEmpty( $template['key'] );
	}

	private function get_prepared_template_data() {
		return $this->run_private_method( array( 'FrmApplicationsController', 'get_prepared_template_data' ) );
	}

	/**
	 * @covers FrmApplicationsController::render_applications_header
	 */
	public function test_render_applications_header() {
		ob_start();
		$title = 'Applications';
		FrmApplicationsController::render_applications_header( $title, 'index' );
		$html = ob_get_clean();
		$this->assertStringContainsString( $title, $html );
	}
}
