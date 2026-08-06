<?php

/**
 * @group welcome-tour
 */
class test_FrmWelcomeTourController extends FrmUnitTest {

	/**
	 * @covers FrmWelcomeTourController::check_for_form_embeds
	 */
	public function test_check_for_form_embeds() {
		$this->assertFalse( $this->check_for_form_embeds() );

		$this->factory->post->create(
			array(
				'post_content' => 'Before [formidable id=5] after',
			)
		);

		$this->assertTrue( $this->check_for_form_embeds() );
	}

	/**
	 * Calls the private FrmWelcomeTourController::check_for_form_embeds method.
	 *
	 * @return bool
	 */
	private function check_for_form_embeds() {
		return $this->run_private_method( array( 'FrmWelcomeTourController', 'check_for_form_embeds' ), array() );
	}
}
