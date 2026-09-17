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
	 * The Import/Export page renders two <form> elements (Import, Export). Both
	 * need distinct accessible names or they violate the aria_landmark_name_unique
	 * a11y rule.
	 *
	 * @covers FrmXMLController::form
	 */
	public function test_form_has_unique_landmark_names_for_import_and_export_forms() {
		ob_start();
		FrmXMLController::form();
		$html = ob_get_clean();

		preg_match_all( '/<form\b[^>]*>/', $html, $matches );
		$this->assertCount( 2, $matches[0], 'Expected exactly two <form> elements on the Import/Export page' );

		$labels = array();
		foreach ( $matches[0] as $form_tag ) {
			preg_match( '/aria-label="([^"]*)"/', $form_tag, $label_match );
			$labels[] = $label_match[1] ?? '';
		}

		$this->assertNotContains( '', $labels, 'Every form landmark needs a non-empty accessible name' );
		$this->assertSame( array_unique( $labels ), $labels, 'Form landmarks must have distinct accessible names' );
	}
}
