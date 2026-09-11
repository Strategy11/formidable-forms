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
	 * wp-mail-smtp-pro's own Core::get_upgrade_link() already tags the link with its own
	 * utm_* params before this filter ever runs (verified against the vendored
	 * wp-mail-smtp-pro\Core::get_utm_url()) — this reproduces that real input shape to make
	 * sure our own campaign attribution actually overrides it instead of being skipped.
	 *
	 * @covers FrmSMTPController::link
	 */
	public function test_link_overrides_preexisting_utm_params_from_wp_mail_smtp() {
		$controller       = new FrmSMTPController();
		$preexisting_link = 'https://wpmailsmtp.com/lite-upgrade/?utm_source=WordPress&utm_medium=plugin-settings&utm_campaign=liteplugin&utm_locale=en_US&utm_content=general';
		$link             = $controller->link( $preexisting_link );

		$this->assertStringContainsString( 'formidableforms.com/go-wp-mail-smtp/', $link );
		$this->assertStringContainsString( 'utm_campaign=wp-mail-smtp-upsell', $link );
		$this->assertStringNotContainsString( 'utm_campaign=liteplugin', $link, 'Our own campaign should override wp-mail-smtp\'s pre-existing one' );
	}
}
