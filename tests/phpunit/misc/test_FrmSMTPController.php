<?php

/**
 * @group misc
 */
class test_FrmSMTPController extends FrmUnitTest {

	/**
	 * @covers FrmSMTPController::link
	 */
	public function test_link_tags_the_redirect_url() {
		$controller = new FrmSMTPController();
		$link       = $controller->link( 'https://wpmailsmtp.com/lite-upgrade/?foo=bar' );

		$this->assertStringContainsString( 'formidableforms.com/go-wp-mail-smtp/', $link );
		$this->assertStringContainsString( 'utm_source=', $link );
		$this->assertStringContainsString( 'utm_campaign=wp-mail-smtp-upsell', $link );
	}
}
