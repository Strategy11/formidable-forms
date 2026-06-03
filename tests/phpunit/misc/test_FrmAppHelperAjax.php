<?php

/**
 * @group ajax
 */
class test_FrmAppHelperAjax extends FrmAjaxUnitTest {

	public function setUp(): void {
		parent::setUp();

		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->user_id );
	}

	/**
	 * @covers FrmAppHelper::dismiss_warning_message
	 */
	public function test_dismiss_warning_message() {
		$option = 'test_option';
		$action = 'frm_' . $option;
		$_POST  = array(
			'action' => $action,
			'nonce'  => wp_create_nonce( 'frm_ajax' ),
		);

		$this->_handleAjax( $action );

		// Check if the warning message is not dismissed
		$this->assertFalse( get_option( $option, false ) );

		try {
			// Call dismiss_warning_message method
			FrmAppHelper::dismiss_warning_message( $option );
		} catch ( WPAjaxDieContinueException $e ) {
			// Ignore the die() statement in wp_send_json_success()
			unset( $e );
		} finally {
			// Check if the warning message is dismissed
			$this->assertTrue( get_option( $option ) );

			// Clean up
			delete_option( $option );
			wp_set_current_user( 0 );
		}
	}
}
