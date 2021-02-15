<?php

class test_FrmInbox extends FrmUnitTest {

	/**
	 * @covers FrmInbox::validate_message_data
	 */
	public function test_validate_message_data() {
		$valid_message = array(
			'message' => 'This is a test message',
			'subject' => 'Test message subject',
			'icon'    => 'frm_report_problem_icon',
			'cta'     => '',
			'created' => time(),
			'key'     => 'test_message',
		);
		$valid         = $this->validate_message_data( $valid_message );
		$this->assertTrue( $valid );

		$invalid_message = array();
		$valid           = $this->validate_message_data( $invalid_message );
		$this->assertFalse( $valid );
	}

	private function validate_message_data( $message ) {
		$inbox = new FrmInbox();
		return $this->run_private_method( array( $inbox, 'validate_message_data' ), array( $message ) );
	}
}
