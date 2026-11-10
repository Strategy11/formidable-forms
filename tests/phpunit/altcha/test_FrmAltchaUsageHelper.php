<?php
/**
 * Usage snapshot settings.
 *
 * @package Formidable
 */

class test_FrmAltchaUsageHelper extends FrmUnitTest {

	public function tearDown(): void {
		delete_option( FrmAltchaHelper::SETTINGS_OPTION );
		parent::tearDown();
	}

	/**
	 * @covers FrmAltchaUsageHelper::add_snapshot_data
	 */
	public function test_snapshot_reports_settings_only() {
		update_option(
			FrmAltchaHelper::SETTINGS_OPTION,
			array(
				'difficulty'  => 'high',
				'expires'     => 30,
				'auto'        => 'onload',
				'hide_footer' => 1,
			)
		);

		$snapshot = FrmAltchaUsageHelper::add_snapshot_data( array( 'other' => 1 ) );
		$usage    = $snapshot['altcha'];

		$this->assertSame( 1, $snapshot['other'] );
		$this->assertSame( FrmAppHelper::$plug_version, $usage['version'] );
		$this->assertSame( 'high', $usage['difficulty'] );
		$this->assertSame( 30, $usage['expires'] );
		$this->assertSame( 'onload', $usage['auto'] );
		$this->assertSame( 1, $usage['hide_footer'] );
		$this->assertContains( $usage['active'], array( 0, 1 ) );
		$this->assertIsInt( $usage['captcha_fields'] );
		$this->assertArrayNotHasKey( 'secret', $usage );
	}
}
