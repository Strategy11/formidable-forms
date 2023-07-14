<?php

/**
 * @group stripe
 */
class test_FrmStrpLiteActionsController extends FrmUnitTest {

	/**
	 * @covers FrmStrpLiteActionsController::replace_email_shortcode
	 */
	public function test_replace_email_shortcode() {
		$this->set_current_user_to_1();
		$email_string = '[email]';
		$this->assertEquals( 'admin@example.org', $this->replace_email_shortcode( $email_string ) );
	}

	/**
	 * @param string $email
	 * @return string
	 */
	private function replace_email_shortcode( $email ) {
		return $this->run_private_method( array( 'FrmStrpLiteActionsController', 'replace_email_shortcode' ), array( $email ) );
	}
}
