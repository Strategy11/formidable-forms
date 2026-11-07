<?php

/**
 * The hooks that let an add-on register its own captcha type.
 *
 * @covers FrmCaptchaFactory
 * @covers FrmFieldCaptcha
 */
class test_FrmCaptchaProviderHooks extends FrmUnitTest {

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
	 * Captcha types an add-on registers through frm_captcha_settings_classes.
	 *
	 * @var array<string, string>
	 */
	private $registered = array();

	/**
	 * What frm_captcha_pre_api_check returns.
	 *
	 * @var array|null
	 */
	private $pre_check;

	/**
	 * How many remote verification requests were sent.
	 *
	 * @var int
	 */
	private $remote_requests = 0;

	public function setUp(): void {
		parent::setUp();
		$this->original_settings = clone FrmAppHelper::get_settings();
		$this->original_post     = $_POST;
		$this->set_front_end();
		FrmSpamEntriesHelper::reset_flags();

		$settings                 = FrmAppHelper::get_settings();
		$settings->active_captcha = 'recaptcha';
		$settings->spam_handling  = FrmSpamEntriesHelper::get_default_handling();
		// The demo type reuses the Turnstile settings class, so it reads the Turnstile keys.
		$settings->turnstile_pubkey  = 'test-site-key';
		$settings->turnstile_privkey = 'test-secret-key';
		$this->captcha               = $this->factory->field->create_and_get(
			array(
				'form_id' => $this->factory->form->create(),
				'type'    => 'captcha',
			)
		);

		add_filter( 'frm_captcha_settings_classes', array( $this, 'register_types' ) );
		add_filter( 'frm_captcha_pre_api_check', array( $this, 'pre_check' ) );
		add_filter( 'pre_http_request', array( $this, 'count_remote_request' ) );
	}

	public function tearDown(): void {
		remove_filter( 'frm_captcha_settings_classes', array( $this, 'register_types' ) );
		remove_filter( 'frm_captcha_pre_api_check', array( $this, 'pre_check' ) );
		remove_filter( 'pre_http_request', array( $this, 'count_remote_request' ) );
		$GLOBALS['frm_settings'] = $this->original_settings;
		$_POST                   = $this->original_post;
		FrmSpamEntriesHelper::reset_flags();
		parent::tearDown();
	}

	/**
	 * @param array $classes Captcha type keyed to its settings class.
	 *
	 * @return array
	 */
	public function register_types( $classes ) {
		return array_merge( $classes, $this->registered );
	}

	/**
	 * @param array|null $response Null to send the remote request.
	 *
	 * @return array|null
	 */
	public function pre_check( $response ) {
		return $this->pre_check ?? $response;
	}

	/**
	 * @param mixed $preempt The HTTP response before the request is sent.
	 *
	 * @return array
	 */
	public function count_remote_request( $preempt ) {
		++$this->remote_requests;
		return array( 'body' => wp_json_encode( array( 'success' => false ) ) );
	}

	/**
	 * @return array
	 */
	private function validate_captcha() {
		$errors = array();
		FrmEntryValidate::validate_field(
			$this->captcha,
			$errors,
			array(
				'form_id'   => $this->captcha->form_id,
				'item_meta' => array(),
			)
		);
		return $errors;
	}

	public function test_registered_type_is_used_when_active() {
		$this->registered                            = array( 'demo' => 'FrmHcaptchaSettings' );
		FrmAppHelper::get_settings()->active_captcha = 'demo';

		$this->assertInstanceOf( 'FrmHcaptchaSettings', FrmCaptchaFactory::get_settings_object() );
	}

	public function test_built_in_types_cannot_be_replaced() {
		$this->registered = array( 'recaptcha' => 'FrmHcaptchaSettings' );
		$this->assertInstanceOf( 'FrmRecaptchaSettings', FrmCaptchaFactory::get_settings_object() );
	}

	public function test_invalid_registrations_fall_back_to_recaptcha() {
		$this->registered                            = array( 'demo' => 'stdClass' );
		FrmAppHelper::get_settings()->active_captcha = 'demo';
		$this->assertInstanceOf( 'FrmRecaptchaSettings', FrmCaptchaFactory::get_settings_object(), 'A class that is not a captcha settings class is ignored.' );

		add_filter( 'frm_captcha_settings_classes', '__return_false', 20 );
		$this->assertInstanceOf( 'FrmRecaptchaSettings', FrmCaptchaFactory::get_settings_object(), 'A filter that returns something other than an array is ignored.' );
		remove_filter( 'frm_captcha_settings_classes', '__return_false', 20 );
	}

	public function test_api_url_filter_only_applies_to_registered_types() {
		$filter = function () {
			return 'https://example.com/demo-captcha.js';
		};
		add_filter( 'frm_captcha_api_url', $filter );
		$field = FrmFieldFactory::get_field_object( $this->captcha );

		$this->registered                            = array( 'demo' => 'FrmTurnstileSettings' );
		FrmAppHelper::get_settings()->active_captcha = 'demo';
		$this->assertSame( 'https://example.com/demo-captcha.js', $this->run_private_method( array( $field, 'api_url' ) ) );

		FrmAppHelper::get_settings()->active_captcha = 'turnstile';
		$this->assertStringContainsString( 'challenges.cloudflare.com', $this->run_private_method( array( $field, 'api_url' ) ), 'Built-in types keep their own script.' );

		remove_filter( 'frm_captcha_api_url', $filter );
	}

	public function test_pre_api_check_replaces_the_remote_request() {
		$this->registered                            = array( 'demo' => 'FrmTurnstileSettings' );
		FrmAppHelper::get_settings()->active_captcha = 'demo';
		$_POST['cf-turnstile-response']              = 'test-token';

		$this->pre_check = array( 'success' => true );
		$this->assertSame( array(), $this->validate_captcha(), 'A successful local check passes validation.' );

		$this->pre_check = array(
			'success'     => false,
			'error-codes' => array( 'invalid-input-response' ),
		);
		$this->assertArrayHasKey( 'field' . $this->captcha->id, $this->validate_captcha(), 'A failed local check blocks the entry.' );
		$this->assertSame( 0, $this->remote_requests, 'No remote request is sent while the filter answers.' );
	}

	public function test_remote_request_is_sent_without_a_pre_check() {
		FrmAppHelper::get_settings()->active_captcha = 'turnstile';
		$_POST['cf-turnstile-response']              = 'test-token';

		$this->validate_captcha();
		$this->assertSame( 1, $this->remote_requests );
	}
}
