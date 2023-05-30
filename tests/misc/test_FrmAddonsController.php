<?php

/**
 * @group addons
 */
class test_FrmAddonsController extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * @covers FrmAddonsController::url_is_allowed
	 * @covers FrmAddonsController::allowed_external_urls
	 */
	public function test_url_is_allowed() {
		add_filter( 'frm_allowed_external_urls', '__return_empty_array' );
		$allowed_download_urls = $this->run_private_method( array( 'FrmAddonsController', 'allowed_external_urls' ) );
		$this->assertEmpty( $allowed_download_urls );

		remove_all_actions( 'frm_allowed_external_urls' );
		$allowed_download_urls = $this->run_private_method( array( 'FrmAddonsController', 'allowed_external_urls' ) );
		array_push( $allowed_download_urls, 'https://s3.amazonaws.com/fp.strategy11.com/releases/acf/formidable-acf-1.0.zip' );

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
