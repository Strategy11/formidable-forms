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

		// Lite uses its own message when no response is posted at all, as for the other captcha types.
		$this->assertSame( 'The captcha is missing from this form', $this->submit( null )[ 'field' . $this->altcha_id ] );
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
				'difficulty'  => 'medium',
				'expires'     => 1440,
				'auto'        => 'onfocus',
				'hide_footer' => 0,
				'display'     => 'standard',
				'widget_type' => 'checkbox',
				'hide_logo'   => 0,
			),
			$settings
		);
	}
}
