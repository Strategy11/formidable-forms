<?php
/**
 * Form validation with ALTCHA as the active captcha type, including replay protection.
 *
 * @package Formidable
 */

require_once __DIR__ . '/FrmAltchaTestHelper.php';

/**
 * @group altcha
 */
class test_FrmAltchaSubmit extends FrmUnitTest {

	/**
	 * @var int
	 */
	private $form_id;

	/**
	 * @var int
	 */
	private $text_id;

	/**
	 * @var int
	 */
	private $altcha_id;

	/**
	 * The captcha type before the test.
	 *
	 * @var string
	 */
	private $previous_captcha;

	public function setUp(): void {
		parent::setUp();

		$frm_settings                 = FrmAppHelper::get_settings();
		$this->previous_captcha       = $frm_settings->active_captcha;
		$frm_settings->active_captcha = FrmAltchaSettings::TYPE;

		$this->form_id   = (int) $this->factory->form->create();
		$this->text_id   = (int) $this->factory->field->create(
			array(
				'type'     => 'text',
				'form_id'  => $this->form_id,
				'required' => '1',
			)
		);
		$this->altcha_id = (int) $this->factory->field->create(
			array(
				'type'    => 'captcha',
				'form_id' => $this->form_id,
			)
		);
	}

	public function tearDown(): void {
		$_POST                                       = array();
		FrmAppHelper::get_settings()->active_captcha = $this->previous_captcha;
		parent::tearDown();
	}

	/**
	 * Validate a submission the way Formidable does on submit.
	 *
	 * @param mixed  $payload Value of the widget's hidden input, or null to leave it out.
	 * @param string $text    Value of the required text field.
	 *
	 * @return array Validation errors.
	 */
	private function submit( $payload, $text = 'Hello' ) {
		$_POST = array(
			'form_id'    => $this->form_id,
			'frm_action' => 'create',
			'item_meta'  => array(
				$this->text_id => $text,
			),
		);

		if ( null !== $payload ) {
			$_POST['frm-altcha-response'] = $payload;
		}

		self::reset_pro_captcha_state();

		return FrmEntryValidate::validate( wp_unslash( $_POST ) );
	}

	/**
	 * Pro remembers a passed captcha for the rest of the request, so later captcha fields on a
	 * multi-page form are skipped. Each submit in these tests stands for a separate request.
	 *
	 * @return void
	 */
	private static function reset_pro_captcha_state() {
		if ( ! class_exists( 'FrmProFieldCaptcha' ) ) {
			return;
		}

		$initial = array(
			'is_valid' => false,
			'checked'  => null,
		);

		foreach ( $initial as $name => $value ) {
			$property = new ReflectionProperty( 'FrmProFieldCaptcha', $name );
			$property->setAccessible( true );
			$property->setValue( null, $value );
		}
	}

	/**
	 * ALTCHA is a registered captcha type and Lite's Captcha field uses it.
	 *
	 * @covers FrmCaptchaFactory::get_settings_object
	 * @covers FrmAltchaSettings::add_front_end_element_attributes
	 */
	public function test_captcha_field_renders_altcha() {
		$settings = FrmCaptchaFactory::get_settings_object();

		$this->assertInstanceOf( 'FrmAltchaSettings', $settings );
		$this->assertSame( 'frm-altcha-response', $settings->token_field );
		$this->assertTrue( FrmFieldCaptcha::should_show_captcha() );

		$field = FrmFieldFactory::get_field_object( FrmField::getOne( $this->altcha_id ) );
		$html  = $field->front_field_input(
			array(
				'html_id'    => 'field_altcha_test',
				'field_name' => 'item_meta[' . $this->altcha_id . ']',
			),
			array()
		);

		$this->assertStringContainsString( 'class="frm-altcha"', $html );
		$this->assertStringContainsString( 'data-name="frm-altcha-response"', $html );
		$this->assertStringContainsString( 'action=frm_altcha_challenge', $html );
		$this->assertStringNotContainsString( 'data-sitekey', $html );
	}

	/**
	 * The builder preview shows the inline checkbox only for the inline layout.
	 *
	 * @covers FrmAltchaController::builder_image_url
	 */
	public function test_builder_preview_follows_display_mode() {
		update_option( FrmAltchaHelper::SETTINGS_OPTION, array( 'display' => 'standard' ) );
		$standard = $this->render_builder_preview();

		$this->assertStringContainsString( 'images/altcha.svg', $standard, 'The inline layout should preview the checkbox.' );
		$this->assertStringNotContainsString( 'frm-altcha-floating-preview', $standard );

		update_option( FrmAltchaHelper::SETTINGS_OPTION, array( 'display' => 'floating' ) );
		$floating = $this->render_builder_preview();

		$this->assertStringNotContainsString( 'images/altcha.svg', $floating, 'The floating layout is not shown in the field, so the inline checkbox should not be previewed.' );
		$this->assertStringContainsString( 'frm-altcha-floating-preview', $floating );
		$this->assertStringContainsString( 'name="field_options[test]"', $floating, 'The hidden builder input should still be printed.' );
	}

	/**
	 * Render the Captcha field's form builder view.
	 *
	 * @return string
	 */
	private function render_builder_preview() {
		$field_name = 'field_options[test]';
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-captcha.php';
		return (string) ob_get_clean();
	}

	/**
	 * Other captcha types are unchanged, and an unknown type still falls back to reCAPTCHA.
	 *
	 * @covers FrmCaptchaFactory::get_settings_object
	 */
	public function test_other_captcha_types_are_unchanged() {
		$this->assertInstanceOf( 'FrmTurnstileSettings', FrmCaptchaFactory::get_settings_object( 'turnstile' ) );
		$this->assertInstanceOf( 'FrmRecaptchaSettings', FrmCaptchaFactory::get_settings_object( 'not-registered' ) );
	}

	/**
	 * @covers FrmAltchaController::verify_response
	 * @covers FrmAltchaController::claim_solution
	 */
	public function test_solved_payload_passes_once() {
		$payload = FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() );

		$this->assertSame( array(), $this->submit( $payload ) );

		$replayed = $this->submit( $payload );
		$this->assertArrayHasKey( 'field' . $this->altcha_id, $replayed );
		$this->assertSame( FrmAppHelper::get_settings()->re_msg, $replayed[ 'field' . $this->altcha_id ] );
	}

	/**
	 * A captcha in an embedded form belongs to another form than the entry being validated.
	 * Its solution must still be spent when the entry passes.
	 *
	 * @covers FrmAltchaController::verify_response
	 * @covers FrmAltchaController::claim_solution
	 */
	public function test_captcha_from_another_form_is_spent() {
		$embedded_form_id             = (int) $this->factory->form->create();
		$embedded_captcha             = FrmField::getOne(
			$this->factory->field->create(
				array(
					'type'    => 'captcha',
					'form_id' => $embedded_form_id,
				)
			)
		);
		$payload                      = FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() );
		$challenge_id                 = FrmAltchaChallengeHelper::verify( $payload )['id'];
		$_POST['frm-altcha-response'] = $payload;
		$response                     = FrmAltchaController::verify_response( FrmCaptchaFactory::get_settings_object(), $embedded_captcha );

		$this->assertTrue( $response['success'] );
		$this->assertFalse( FrmAltchaReplayHelper::is_used( $challenge_id ) );

		// Runs on frm_validate_entry, once the parent form's entry has no errors.
		$errors = FrmAltchaController::claim_solution( array() );

		$this->assertSame( array(), $errors );
		$this->assertTrue( FrmAltchaReplayHelper::is_used( $challenge_id ) );
	}

	/**
	 * The widget only loads for forms that have a captcha field.
	 *
	 * @covers FrmAltchaController::maybe_enqueue_for_form
	 */
	public function test_widget_is_only_enqueued_for_forms_with_a_captcha() {
		$plain_form_id = (int) $this->factory->form->create();
		$this->factory->field->create(
			array(
				'type'    => 'text',
				'form_id' => $plain_form_id,
			)
		);
		wp_dequeue_script( 'captcha-api' );

		FrmAltchaController::maybe_enqueue_for_form( array( 'form_id' => $plain_form_id ) );
		$this->assertFalse( wp_script_is( 'captcha-api', 'enqueued' ) );

		FrmAltchaController::maybe_enqueue_for_form( array( 'form_id' => $this->form_id ) );
		$this->assertTrue( wp_script_is( 'captcha-api', 'enqueued' ) );

		wp_dequeue_script( 'captcha-api' );
		wp_dequeue_style( 'frm-altcha' );
	}

	/**
	 * Simulate a database user that cannot write to the replay table.
	 *
	 * @param string $query The query about to execute.
	 *
	 * @return string
	 */
	public function reject_replay_insert( $query ) {
		if ( str_starts_with( $query, 'INSERT IGNORE' ) && str_contains( $query, FrmAltchaReplayHelper::table_name() ) ) {
			return 'SELECT 1 FROM frm_missing_replay_table';
		}

		return $query;
	}

	/**
	 * A replay table that cannot be written rejects the entry with the setup message, not the retry message.
	 *
	 * @covers FrmAltchaController::claim_solution
	 */
	public function test_storage_failure_shows_setup_message() {
		global $wpdb;
		$payload  = FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() );
		$reasons  = array();
		$listener = function ( $reason ) use ( &$reasons ) {
			$reasons[] = $reason;
		};
		$previous = $wpdb->suppress_errors();
		add_filter( 'query', array( $this, 'reject_replay_insert' ) );
		add_action( 'frm_altcha_validation_failed', $listener );

		try {
			$errors = $this->submit( $payload );
		} finally {
			remove_filter( 'query', array( $this, 'reject_replay_insert' ) );
			remove_action( 'frm_altcha_validation_failed', $listener );
			$wpdb->suppress_errors( $previous );
		}

		$this->assertSame( array( 'storage_failed' ), $reasons );
		$this->assertSame(
			__( 'The CAPTCHA on this form is not set up correctly. Please contact the site administrator.', 'formidable' ),
			$errors[ 'field' . $this->altcha_id ] ?? ''
		);
	}

	/**
	 * A visitor who fixes another field and submits again keeps their solution.
	 *
	 * @covers FrmAltchaController::claim_solution
	 */
	public function test_other_errors_do_not_spend_the_solution() {
		$payload = FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() );
		$errors  = $this->submit( $payload, '' );
		$this->assertArrayHasKey( 'field' . $this->text_id, $errors );
		$this->assertArrayNotHasKey( 'field' . $this->altcha_id, $errors );

		$this->assertSame( array(), $this->submit( $payload ) );
	}

	/**
	 * @covers FrmAltchaController::verify_response
	 */
	public function test_missing_and_invalid_payloads_fail() {
		$data                           = FrmAltchaTestHelper::decode( FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() ) );
		$data['solution']['derivedKey'] = str_repeat( 'c', 64 );

		$this->assertArrayHasKey( 'field' . $this->altcha_id, $this->submit( null ) );
		$this->assertArrayHasKey( 'field' . $this->altcha_id, $this->submit( FrmAltchaTestHelper::encode( $data ) ) );
	}

	/**
	 * The field's own invalid message is used when the check fails.
	 *
	 * @covers FrmAltchaController::verify_response
	 */
	public function test_custom_invalid_message() {
		FrmField::update( $this->altcha_id, array( 'field_options' => array( 'invalid' => 'Bots go home' ) ) );

		$data                           = FrmAltchaTestHelper::decode( FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() ) );
		$data['solution']['derivedKey'] = str_repeat( 'd', 64 );
		$errors                         = $this->submit( FrmAltchaTestHelper::encode( $data ) );

		$this->assertSame( 'Bots go home', $errors[ 'field' . $this->altcha_id ] );

		// A submit without a solution asks the visitor to verify with the same message.
		$this->assertSame( 'Bots go home', $this->submit( null )[ 'field' . $this->altcha_id ] );
	}

	/**
	 * A submit without a solution, for example before the visitor clicked the checkbox in the
	 * "Only when the visitor clicks the checkbox" mode, fails with the message the widget shows.
	 *
	 * @covers FrmAltchaController::missing_solution_message
	 */
	public function test_missing_solution_asks_the_visitor_to_verify() {
		update_option( FrmAltchaHelper::SETTINGS_OPTION, array( 'auto' => 'off' ) );

		$errors = $this->submit( null );

		$this->assertSame( array( 'field' . $this->altcha_id => 'Please verify that you are not a robot.' ), $errors );

		$attributes    = FrmCaptchaFactory::get_settings_object( 'altcha' )->add_front_end_element_attributes( array(), array() );
		$configuration = json_decode( $attributes['data-configuration'], true );

		$this->assertSame( $errors[ 'field' . $this->altcha_id ], $configuration['validationMessage'], 'The server and the widget should show the same message.' );
	}

	/**
	 * In the "Only when the visitor clicks the checkbox" mode, refusing an unverified submit uses the
	 * Captcha field's own invalid message when it has one, on the server and in the widget. The global
	 * CAPTCHA message, which new fields copy, is about a failed check, so it is not used.
	 *
	 * @covers FrmAltchaController::missing_solution_message
	 * @covers FrmAltchaHelper::verification_required_message
	 * @covers FrmAltchaSettings::add_front_end_element_attributes
	 *
	 * @dataProvider field_invalid_messages
	 *
	 * @param string $invalid  The Captcha field's invalid message.
	 * @param string $expected The message the visitor should see.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'field_invalid_messages' )]
	public function test_missing_solution_uses_the_field_invalid_message( $invalid, $expected ) {
		update_option( FrmAltchaHelper::SETTINGS_OPTION, array( 'auto' => 'off' ) );
		FrmField::update( $this->altcha_id, array( 'field_options' => array( 'invalid' => $invalid ) ) );

		$this->assertSame( array( 'field' . $this->altcha_id => $expected ), $this->submit( null ) );

		$field         = FrmFieldsHelper::setup_edit_vars( FrmField::getOne( $this->altcha_id ) );
		$attributes    = FrmCaptchaFactory::get_settings_object( 'altcha' )->add_front_end_element_attributes( array(), $field );
		$configuration = json_decode( $attributes['data-configuration'], true );

		$this->assertSame( $expected, $configuration['validationMessage'], 'The widget should refuse the submit with the same message.' );
	}

	/**
	 * @return array
	 */
	public static function field_invalid_messages() {
		return array(
			'custom message'        => array( 'Click the box first', 'Click the box first' ),
			'no message'            => array( '', 'Please verify that you are not a robot.' ),
			'global message'        => array( 'The CAPTCHA was not entered correctly', 'Please verify that you are not a robot.' ),
			'old reCAPTCHA default' => array( 'The reCAPTCHA was not entered correctly', 'Please verify that you are not a robot.' ),
		);
	}

	/**
	 * An upload field's invalid message is about the file, so the upload CAPTCHA keeps the default message.
	 *
	 * @covers FrmAltchaSettings::add_front_end_element_attributes
	 */
	public function test_other_field_types_keep_the_default_message() {
		$attributes    = FrmCaptchaFactory::get_settings_object( 'altcha' )->add_front_end_element_attributes(
			array(),
			array(
				'type'    => 'file',
				'invalid' => 'This file type is not allowed',
			)
		);
		$configuration = json_decode( $attributes['data-configuration'], true );

		$this->assertSame( 'Please verify that you are not a robot.', $configuration['validationMessage'] );
	}

	/**
	 * With CAPTCHA spam handling set to save, a failed ALTCHA check saves a spam entry like the other captcha types.
	 *
	 * @covers FrmAltchaController::verify_response
	 */
	public function test_failed_check_follows_spam_handling() {
		if ( ! FrmSpamEntriesHelper::can_store_spam() ) {
			$this->markTestSkipped( 'Spam entries cannot be stored on this install.' );
		}

		add_filter( 'frm_save_spam_entry', '__return_true' );

		$data                           = FrmAltchaTestHelper::decode( FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() ) );
		$data['solution']['derivedKey'] = str_repeat( 'e', 64 );
		$errors                         = $this->submit( FrmAltchaTestHelper::encode( $data ) );

		remove_filter( 'frm_save_spam_entry', '__return_true' );

		$this->assertArrayNotHasKey( 'field' . $this->altcha_id, $errors );
		$this->assertSame( 'captcha', FrmSpamEntriesHelper::get_flagged_source( array( 'form_id' => $this->form_id ) ) );
	}

	/**
	 * @covers FrmAltchaReplayHelper::claim
	 * @covers FrmAltchaReplayHelper::is_used
	 * @covers FrmAltchaReplayHelper::purge
	 */
	public function test_replay_guard_claims_once_and_purges_expired() {
		$live    = hash( 'sha256', 'live' );
		$expired = hash( 'sha256', 'expired' );

		$this->assertFalse( FrmAltchaReplayHelper::is_used( $live ) );
		$this->assertTrue( FrmAltchaReplayHelper::claim( $live, time() + HOUR_IN_SECONDS ) );
		$this->assertFalse( FrmAltchaReplayHelper::claim( $live, time() + HOUR_IN_SECONDS ) );
		$this->assertTrue( FrmAltchaReplayHelper::is_used( $live ) );

		$this->assertTrue( FrmAltchaReplayHelper::claim( $expired, time() - 10 ) );
		FrmAltchaReplayHelper::purge();

		$this->assertTrue( FrmAltchaReplayHelper::is_used( $live ) );
		$this->assertFalse( FrmAltchaReplayHelper::is_used( $expired ) );
	}

	/**
	 * @covers FrmAltchaHelper::sanitize_settings
	 */
	public function test_settings_are_clamped() {
		$settings = FrmAltchaHelper::sanitize_settings(
			array(
				'difficulty'  => 'extreme',
				'expires'     => '99999',
				'auto'        => '<script>',
				'hide_footer' => '1',
			)
		);

		$this->assertSame(
			array(
				'difficulty' => 'medium',
				'expires'    => 1440,
				'auto'       => 'onfocus',
				'display'    => 'standard',
			),
			$settings
		);
	}
}
