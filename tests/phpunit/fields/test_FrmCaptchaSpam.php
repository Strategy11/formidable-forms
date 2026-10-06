<?php

/**
 * @covers FrmFieldCaptcha
 * @covers FrmEntryValidate
 * @covers FrmSpamEntriesHelper
 * @covers FrmEntry
 */
class test_FrmCaptchaSpam extends FrmUnitTest {

	/**
	 * @var FrmSettings
	 */
	private $original_settings;

	/**
	 * @var array
	 */
	private $original_post;

	/**
	 * @var object
	 */
	private $captcha;

	/**
	 * @var array|WP_Error
	 */
	private $response;

	public function setUp(): void {
		parent::setUp();
		$this->original_settings = clone FrmAppHelper::get_settings();
		$this->original_post     = $_POST;
		$this->set_front_end();
		FrmSpamEntriesHelper::reset_flags();

		$settings                 = FrmAppHelper::get_settings();
		$settings->active_captcha = 'recaptcha';
		$settings->re_type        = 'v2';
		$settings->pubkey         = 'test-site-key';
		$settings->privkey        = 'test-secret-key';
		$settings->spam_handling  = FrmSpamEntriesHelper::get_default_handling();
		$form_id                  = $this->factory->form->create();
		$this->captcha            = $this->factory->field->create_and_get(
			array(
				'form_id' => $form_id,
				'type'    => 'captcha',
			)
		);
		$_POST                    = array( 'g-recaptcha-response' => 'test-token' );
		$this->response           = array(
			'body' => wp_json_encode(
				array(
					'success'     => false,
					'error-codes' => array( 'timeout-or-duplicate' ),
				)
			),
		);
		add_filter( 'pre_http_request', array( $this, 'mock_verification' ) );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_verification' ) );
		$GLOBALS['frm_settings'] = $this->original_settings;
		$_POST                   = $this->original_post;
		FrmSpamEntriesHelper::reset_flags();
		parent::tearDown();
	}

	/**
	 * @param mixed $preempt The HTTP response before verification.
	 *
	 * @return array|WP_Error
	 */
	public function mock_verification( $preempt ) {
		return $this->response;
	}

	/**
	 * @param array $extra Additional submission values.
	 *
	 * @return array
	 */
	private function validate_captcha( $extra = array() ) {
		$errors = array();
		$values = array_merge(
			array(
				'form_id'   => $this->captcha->form_id,
				'item_meta' => array(),
			),
			$extra
		);
		FrmEntryValidate::validate_field( $this->captcha, $errors, $values );
		return $errors;
	}

	public function test_captcha_errors_block_by_default() {
		$this->assertArrayHasKey( 'field' . $this->captcha->id, $this->validate_captcha(), 'CAPTCHA failures should block by default.' );
		$this->assertSame( '', FrmSpamEntriesHelper::get_flagged_source( array( 'form_id' => $this->captcha->form_id ) ), 'Blocked submissions should not be saved.' );
	}

	public function test_saved_failure_preserves_service_error_in_entry_and_sidebar() {
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$this->assertSame( array(), $this->validate_captcha(), 'Saved CAPTCHA failures should allow submission.' );
		$entry_id = FrmEntry::create(
			array(
				'form_id'     => $this->captcha->form_id,
				'item_meta'   => array(),
				'description' => array( 'custom' => 'preserved' ),
			)
		);
		$entry    = FrmEntry::getOne( $entry_id );
		$this->assertSame( FrmSpamEntriesHelper::SPAM_ENTRY_STATUS, (int) $entry->is_draft, 'The entry should have spam status.' );
		$description = $entry->description;
		FrmAppHelper::unserialize_or_decode( $description );
		$this->assertSame( 'captcha', $description['spam_source'], 'The CAPTCHA source should be stored.' );
		$this->assertSame( 'preserved', $description['custom'], 'Custom description values should be preserved.' );
		$this->assertStringContainsString( 'timeout-or-duplicate', $description['spam_reason'], 'The raw service error should be stored.' );
		$this->assertStringContainsString( 'expired or was already used', FrmSpamEntriesHelper::get_source_label( $entry ), 'The reason should include the readable explanation.' );
		ob_start();

		try {
			FrmEntriesController::entry_sidebar( $entry );
			$html = ob_get_contents();
		} finally {
			ob_end_clean();
		}
		$this->assertStringContainsString( 'timeout-or-duplicate', $html, 'The sidebar should display the service error.' );
		$this->assertSame( 1, substr_count( $html, 'timeout-or-duplicate' ), 'The detailed reason should appear once.' );
	}

	public function test_missing_token_can_be_saved_as_spam() {
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$_POST = array();
		$this->assertSame( array(), $this->validate_captcha(), 'Missing CAPTCHA tokens should follow the save setting.' );
		$this->assertStringContainsString(
			'missing-input-response',
			FrmSpamEntriesHelper::get_flagged_reason( array( 'form_id' => $this->captcha->form_id ) ),
			'The missing token reason should be recorded.'
		);
	}

	public function test_transport_error_can_be_saved_as_spam() {
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$this->response                                        = new WP_Error( 'http_request_failed', 'Verification service unavailable.' );
		$this->assertSame( array(), $this->validate_captcha(), 'Transport failures should follow the save setting.' );
		$this->assertSame(
			'Verification service unavailable.',
			FrmSpamEntriesHelper::get_flagged_reason( array( 'form_id' => $this->captcha->form_id ) ),
			'The transport error should be recorded.'
		);
	}

	public function test_low_score_is_saved_with_threshold_details() {
		$settings                           = FrmAppHelper::get_settings();
		$settings->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$settings->re_type                  = 'v3';
		$settings->re_threshold             = '0.5';
		$this->response                     = array(
			'body' => wp_json_encode(
				array(
					'success' => true,
					'score'   => 0.1,
				)
			),
		);
		$this->assertSame( array(), $this->validate_captcha(), 'Low scores should follow the save setting.' );
		$this->assertStringContainsString(
			'0.1 is below the threshold of 0.5',
			FrmSpamEntriesHelper::get_flagged_reason( array( 'form_id' => $this->captcha->form_id ) ),
			'The score and threshold should be recorded.'
		);
	}

	public function test_entry_updates_keep_captcha_errors() {
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$this->assertNotEmpty( $this->validate_captcha( array( 'id' => 123 ) ), 'Updates with an ID should remain blocked.' );
		$this->assertNotEmpty( $this->validate_captcha( array( 'frm_action' => 'update' ) ), 'Update actions should remain blocked.' );
	}

	public function test_successful_captcha_is_not_flagged() {
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$this->response                                        = array( 'body' => wp_json_encode( array( 'success' => true ) ) );
		$this->assertSame( array(), $this->validate_captcha(), 'Successful CAPTCHA validation should pass.' );
		$this->assertSame( '', FrmSpamEntriesHelper::get_flagged_source( array( 'form_id' => $this->captcha->form_id ) ), 'Successful submissions should not be spam.' );
	}

	public function test_later_spam_check_preserves_captcha_reason_and_parent_inheritance() {
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$this->validate_captcha();
		FrmSpamEntriesHelper::maybe_flag_submission( $this->captcha->form_id, 'denylist' );
		$child_values = array(
			'form_id'        => 0,
			'parent_form_id' => $this->captcha->form_id,
		);
		$this->assertSame( 'captcha', FrmSpamEntriesHelper::get_flagged_source( $child_values ), 'Later checks should preserve the first source.' );
		$this->assertStringContainsString( 'timeout-or-duplicate', FrmSpamEntriesHelper::get_flagged_reason( $child_values ), 'Child entries should inherit the detailed reason.' );
		FrmSpamEntriesHelper::reset_flags();
		$this->assertSame( '', FrmSpamEntriesHelper::get_flagged_reason( $child_values ), 'Resetting flags should clear detailed reasons.' );
	}

	public function test_configuration_error_keeps_specific_message_when_blocked() {
		$this->response = array(
			'body' => wp_json_encode(
				array(
					'success'     => false,
					'error-codes' => array( 'invalid-input-secret' ),
				)
			),
		);
		$errors         = $this->validate_captcha();
		$this->assertStringContainsString( 'not set up correctly', $errors[ 'field' . $this->captcha->id ], 'Configuration errors should explain that retrying will not help.' );
	}

	public function test_configuration_error_can_be_saved_with_actual_reason() {
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$this->response                                        = array(
			'body' => wp_json_encode(
				array(
					'success'     => false,
					'error-codes' => array( 'invalid-input-secret' ),
				)
			),
		);
		$this->assertSame( array(), $this->validate_captcha(), 'Configuration errors should follow the save setting.' );
		$reason = FrmSpamEntriesHelper::get_flagged_reason( array( 'form_id' => $this->captcha->form_id ) );
		$this->assertStringContainsString( 'secret key', $reason, 'The configuration failure explanation should be stored.' );
		$this->assertStringContainsString( 'invalid-input-secret', $reason, 'The configuration error code should be stored.' );
	}

	public function test_unknown_service_error_code_is_preserved() {
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$this->response                                        = array(
			'body' => wp_json_encode(
				array(
					'success'     => false,
					'error-codes' => array( 'future-service-error' ),
				)
			),
		);
		$this->assertSame( array(), $this->validate_captcha(), 'Unknown service errors should follow the save setting.' );
		$reason = FrmSpamEntriesHelper::get_flagged_reason( array( 'form_id' => $this->captcha->form_id ) );
		$this->assertStringContainsString( 'future-service-error', $reason, 'Unknown error codes should remain available for review.' );
	}

	public function test_other_field_errors_are_preserved() {
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$errors = array( 'field999' => 'A required field is missing.' );
		FrmEntryValidate::validate_field(
			$this->captcha,
			$errors,
			array(
				'form_id'   => $this->captcha->form_id,
				'item_meta' => array(),
			)
		);
		$this->assertSame( array( 'field999' => 'A required field is missing.' ), $errors, 'Saving CAPTCHA failures should preserve other validation errors.' );
	}

	public function test_incompatible_addon_keeps_captcha_errors() {
		if ( FrmSpamEntriesHelper::can_store_spam() ) {
			$this->markTestSkipped( 'This test requires an active add-on without spam entry support.' );
		}

		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$this->assertNotEmpty( $this->validate_captcha(), 'Incompatible add-ons should keep CAPTCHA failures blocking.' );
		$this->assertSame( '', FrmSpamEntriesHelper::get_flagged_source( array( 'form_id' => $this->captcha->form_id ) ), 'Incompatible add-ons should prevent spam storage.' );
	}

	public function test_front_end_only_bypasses_captcha_when_saving_spam() {
		$field = new FrmFieldCaptcha( array_merge( (array) $this->captcha, $this->captcha->field_options ) );
		$args  = array( 'html_id' => 'test-captcha' );
		$this->assertStringNotContainsString( 'data-save-as-spam', $field->front_field_input( $args, array() ), 'Blocking should keep client CAPTCHA validation enabled.' );
		FrmAppHelper::get_settings()->spam_handling['captcha'] = FrmSpamEntriesHelper::SAVE;
		$html = $field->front_field_input( $args, array() );

		if ( ! FrmSpamEntriesHelper::can_store_spam() ) {
			$this->assertStringNotContainsString( 'data-save-as-spam', $html, 'Incompatible add-ons should keep client CAPTCHA validation enabled.' );
			return;
		}

		$this->assertStringContainsString( 'data-save-as-spam="1"', $html, 'Saving spam should allow CAPTCHA errors to reach server validation.' );
	}
}
