<?php

class test_FrmXMLController extends FrmUnitTest {

	/**
	 * @covers FrmXMLController::validate_xml_url
	 */
	public function test_validate_xml_url() {
		$example_access_key_id = 'ABC123';
		$expires               = time();
		$signature             = 'DEF456';
		$example_url           = "https://s3.amazonaws.com/fp.strategy11.com/form-templates/contact-us-form.xml?AWSAccessKeyId={$example_access_key_id}&Expires={$expires}&Signature={$signature}";

		$this->assertTrue( $this->validate_xml_url( $example_url ) );
		$this->assertFalse( $this->validate_xml_url( 'https://example.com' ), 'We want to block any requests that are not from our S3 Bucket' );
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 */
	private function validate_xml_url( $url ) {
		return $this->run_private_method( array( 'FrmXMLController', 'validate_xml_url' ), array( $url ) );
	}
}
