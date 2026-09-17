<?php

class test_FrmXMLController extends FrmUnitTest {

	/**
	 * @covers FrmXMLController::validate_xml_url
	 */
	public function test_validate_xml_url() {
		$example_access_key_id = 'ABC123';
		$expires               = time();
		$signature             = 'DEF456';
		$example_url           = "https://s3.amazonaws.com/fp.strategy11.com/form-templates/contact-us-form.xml?AWSAccessKeyId={$example_access_key_id}&Expires={$expires}&Signature={$signature}"; // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong

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

	/**
	 * @covers FrmXMLController::form
	 */
	public function test_export_table_headers_are_th() {
		$this->set_user_by_role( 'administrator' );

		ob_start();
		FrmXMLController::form();
		$html = ob_get_clean();

		$thead = substr( $html, strpos( $html, '<thead>' ), strpos( $html, '</thead>' ) - strpos( $html, '<thead>' ) );

		$this->assertStringContainsString(
			'<th scope="col" class="column-cb check-column">',
			$thead,
			'The Export table\'s cb column header cell must be a real <th scope="col">, not a <td>, for IBM table_headers_exists.'
		);
		$this->assertStringNotContainsString( '<td', $thead );
	}
}
