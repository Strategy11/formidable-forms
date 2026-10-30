<?php

/**
 * @covers FrmEntryValidate
 * @covers FrmSpamEntriesHelper
 */
class test_FrmAkismetSpam extends FrmUnitTest {

	/**
	 * Akismet headers only distinguish blatant spam when the body flags spam.
	 */
	public function test_response_sources() {
		$cases = array(
			array( array( array(), 'true' ), 'akismet' ),
			array( array( array( 'x-akismet-pro-tip' => 'discard' ), 'true' ), 'akismet_discard' ),
			array( array( array( 'X-Akismet-Pro-Tip' => 'discard' ), 'true' ), 'akismet_discard' ),
			array( array( new ArrayObject( array( 'x-akismet-pro-tip' => 'discard' ) ), 'true' ), 'akismet_discard' ),
			array( array( array( 'x-akismet-pro-tip' => 'discard' ), 'false' ), '' ),
			array( array( array( 'x-akismet-pro-tip' => 'other' ), 'true' ), 'akismet' ),
			array( array( '', 'true' ), 'akismet' ),
			array( array( array(), 'false' ), '' ),
			array( array(), '' ),
			array( false, '' ),
		);

		foreach ( $cases as $case ) {
			$source = $this->run_private_method( array( 'FrmEntryValidate', 'get_akismet_response_source' ), array( $case[0] ) );
			$this->assertSame( $case[1], $source );
		}
	}

	/**
	 * Existing Akismet settings are kept when the new blatant-spam setting is added.
	 */
	public function test_handling_defaults_and_saved_choices() {
		$handling = FrmSpamEntriesHelper::sanitize_handling( array( 'akismet' => FrmSpamEntriesHelper::BLOCK ) );
		$this->assertSame( FrmSpamEntriesHelper::BLOCK, $handling['akismet'] );
		$this->assertSame( FrmSpamEntriesHelper::BLOCK, $handling['akismet_discard'] );

		$handling = FrmSpamEntriesHelper::sanitize_handling(
			array(
				'akismet'         => FrmSpamEntriesHelper::SAVE,
				'akismet_discard' => FrmSpamEntriesHelper::SAVE,
			)
		);
		$this->assertSame( FrmSpamEntriesHelper::SAVE, $handling['akismet_discard'] );
	}

	/**
	 * Each Akismet source follows its own setting when flagging a new submission.
	 */
	public function test_separate_submission_handling() {
		$settings                = FrmAppHelper::get_settings();
		$original                = $settings->spam_handling;
		$settings->spam_handling = FrmSpamEntriesHelper::get_default_handling();

		try {
			$this->assertTrue( FrmSpamEntriesHelper::maybe_flag_submission( 123, 'akismet' ) );
			$this->assertSame( 'akismet', FrmSpamEntriesHelper::get_flagged_source( array( 'form_id' => 123 ) ) );
			FrmSpamEntriesHelper::reset_flags();

			$this->assertFalse( FrmSpamEntriesHelper::maybe_flag_submission( 123, 'akismet_discard' ) );
			$this->assertSame( '', FrmSpamEntriesHelper::get_flagged_source( array( 'form_id' => 123 ) ) );

			$settings->spam_handling['akismet_discard'] = FrmSpamEntriesHelper::SAVE;
			$this->assertTrue( FrmSpamEntriesHelper::maybe_flag_submission( 123, 'akismet_discard' ) );
			$this->assertSame( 'akismet_discard', FrmSpamEntriesHelper::get_flagged_source( array( 'form_id' => 123 ) ) );
		} finally {
			$settings->spam_handling = $original;
			FrmSpamEntriesHelper::reset_flags();
		}
	}
	/**
	 * Inactive Akismet settings stay hidden while their saved choices remain in the form.
	 */
	public function test_inactive_akismet_settings_are_preserved() {
		if ( function_exists( 'akismet_http_post' ) ) {
			$this->markTestSkipped( 'This test requires Akismet to be inactive.' );
		}

		$settings                = FrmAppHelper::get_settings();
		$original                = $settings->spam_handling;
		$settings->spam_handling = array(
			'akismet'         => FrmSpamEntriesHelper::BLOCK,
			'akismet_discard' => FrmSpamEntriesHelper::SAVE,
		);

		ob_start();

		try {
			FrmSettingsController::captcha_settings();
			$html = ob_get_contents();
		} finally {
			ob_end_clean();
			$settings->spam_handling = $original;
		}

		$this->assertStringNotContainsString( 'id="frm_spam_handling_akismet"', $html );
		$this->assertStringNotContainsString( 'id="frm_spam_handling_akismet_discard"', $html );
		$this->assertStringContainsString( 'type="hidden" name="frm_spam_handling[akismet]" value="block"', $html );
		$this->assertStringContainsString( 'type="hidden" name="frm_spam_handling[akismet_discard]" value="save"', $html );
	}

	/**
	 * A check that is turned off submits no handling, so its saved choice is kept.
	 *
	 * @return void
	 */
	public function test_disabled_check_keeps_saved_handling() {
		$settings                            = new FrmSettings();
		$settings->spam_handling             = FrmSpamEntriesHelper::get_default_handling();
		$settings->spam_handling['honeypot'] = FrmSpamEntriesHelper::SAVE;
		$posted                              = array(
			'frm_spam_handling' => array( 'denylist' => FrmSpamEntriesHelper::BLOCK ),
		);

		$params = $this->run_private_method( array( $settings, 'keep_unposted_spam_handling' ), array( $posted ) );

		// FrmSettings::update() then saves the sanitized handling.
		$handling = FrmSpamEntriesHelper::sanitize_handling( $params['frm_spam_handling'] );
		$this->assertSame( FrmSpamEntriesHelper::SAVE, $handling['honeypot'], 'The disabled check should keep its saved handling.' );
		$this->assertSame( FrmSpamEntriesHelper::BLOCK, $handling['denylist'], 'The posted handling should be saved.' );
	}

	/**
	 * Optional checks render a single handling control, so the settings form has no duplicate names.
	 *
	 * @return void
	 */
	public function test_optional_checks_render_one_handling_control() {
		ob_start();

		try {
			FrmSettingsController::captcha_settings();
			$html = ob_get_contents();
		} finally {
			ob_end_clean();
		}

		foreach ( array_keys( FrmSpamEntriesHelper::get_optional_sources() ) as $source ) {
			$this->assertSame( 1, substr_count( $html, 'name="frm_spam_handling[' . $source . ']"' ), 'Each check should submit its handling once.' );
		}
	}

	/**
	 * A saved API key does not enable checks when the Akismet plugin is inactive.
	 */
	public function test_inactive_akismet_does_not_make_a_request() {
		if ( is_callable( 'Akismet::http_post' ) ) {
			$this->markTestSkipped( 'This test requires Akismet to be inactive.' );
		}

		update_option( 'wordpress_api_key', 'test-key' );
		$is_spam = $this->run_private_method(
			array( 'FrmEntryValidate', 'is_akismet_spam' ),
			array(
				array(
					'form_id'   => 1,
					'item_meta' => array( 'spam' ),
				),
			)
		);
		$this->assertFalse( $is_spam );
	}
}
