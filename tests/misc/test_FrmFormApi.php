<?php

class test_FrmFormApi extends FrmUnitTest {

	/**
	 * @covers FrmFormApi::get_error_from_response
	 */
	public function test_get_error_from_response() {
		$api = new FrmFormApi();

		// Test an array error.
		$message = 'Your license has expired.';
		$addons  = array(
			'error' => array(
				'license' => 'license_123',
				'code'    => 'expired',
				'message' => $message,
			),
		);
		$error   = $api->get_error_from_response( $addons );
		$this->assertEquals( array( $message ), $error );

		// Test a string error.
		$message = 'Your site has been blocked!';
		$addons  = array(
			'error' => $message,
		);
		$error   = $api->get_error_from_response( $addons );

		$this->assertEquals( array( $message ), $error );
	}
}
