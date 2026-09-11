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

	/**
	 * Reproduces the real input shape: wp-mail-smtp-pro's own Core::get_upgrade_link() already
	 * tags the link before this filter runs, so a naive fill-the-gaps re-tag would be a no-op.
	 *
	 * @covers FrmSMTPController::link
	 */
	public function test_link_overrides_preexisting_utm_params_from_wp_mail_smtp() {
		$controller       = new FrmSMTPController();
		$preexisting_link = 'https://wpmailsmtp.com/lite-upgrade/?utm_source=WordPress&utm_medium=plugin-settings&utm_campaign=liteplugin&utm_locale=en_US&utm_content=general';
		$link             = $controller->link( $preexisting_link );

		$this->assertStringContainsString( 'formidableforms.com/go-wp-mail-smtp/', $link );
		$this->assertStringContainsString( 'utm_campaign=wp-mail-smtp-upsell', $link );
		$this->assertStringNotContainsString( 'utm_campaign=liteplugin', $link, 'Our own campaign should override the pre-existing one' );
		$this->assertStringContainsString( 'urllink=wpmailsmtp%2Ecom%2Flite%2Dupgrade', $link, 'The hand-obfuscated redirect target must survive the utm re-tagging untouched' );
	}
}
