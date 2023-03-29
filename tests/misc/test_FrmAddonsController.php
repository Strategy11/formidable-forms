<?php

/**
 * @group addons
 */
class test_FrmAddonsController extends FrmUnitTest {
	private $addon;

	public function setUp(): void {
		parent::setUp();

		$this->check_php_version( '5.4' );
	}

	/**
	 * @covers FrmAddonsController::url_is_allowed
	 */
	public function test_url_is_allowed() {
		$allowed_download_urls = array(
			'https://downloads.wordpress.org/plugin/formidable-gravity-forms-importer.zip',
			'https://downloads.wordpress.org/plugin/formidable-import-pirate-forms.zip',
			'https://s3.amazonaws.com/fp.strategy11.com/releases/acf/formidable-acf-1.0.zip',
		);

		$disallowed_download_urls = array(
			'https://unsafeurl.com?empty_my_whole_wallet&something_to_bypass_this_check=https://downloads.wordpress.org/plugin',
			'https://downloads.wordpress.org/plugin/dangerous_script.py',
		);

		foreach ( $allowed_download_urls as $download_url ) {
			$allowed = FrmAddonsController::url_is_allowed( $download_url );
			$this->assertTrue( $allowed );
		}

		foreach ( $disallowed_download_urls as $download_url ) {
			$allowed = FrmAddonsController::url_is_allowed( $download_url );
			$this->assertFalse( $allowed );
		}
	}
}
