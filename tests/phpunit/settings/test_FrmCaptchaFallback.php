<?php

/**
 * @covers FrmSettings
 * @covers FrmFieldCaptcha
 */
class test_FrmCaptchaFallback extends FrmUnitTest {

	/**
	 * @var FrmSettings
	 */
	private $original_settings;

	public function setUp(): void {
		parent::setUp();
		$this->original_settings = clone FrmAppHelper::get_settings();
		$this->set_front_end();
	}

	public function tearDown(): void {
		$GLOBALS['frm_settings'] = $this->original_settings;
		parent::tearDown();
	}

	/**
	 * Load saved settings with test keys for the specified providers.
	 *
	 * @param string   $active     The saved provider selection.
	 * @param string[] $configured The providers with both keys configured.
	 *
	 * @return FrmSettings
	 */
	private function load_settings( $active, $configured ) {
		$settings                 = clone $this->original_settings;
		$settings->active_captcha = $active;
		$settings->re_type        = 'v2';
		$prefixes                 = array(
			'recaptcha' => '',
			'hcaptcha'  => 'hcaptcha_',
			'turnstile' => 'turnstile_',
		);

		foreach ( $prefixes as $type => $prefix ) {
			$has_keys                          = in_array( $type, $configured, true );
			$settings->{ $prefix . 'pubkey' }  = $has_keys ? 'test-site-key' : '';
			$settings->{ $prefix . 'privkey' } = $has_keys ? 'test-secret-key' : '';
		}

		update_option( $settings->option_name, $settings );
		$GLOBALS['frm_settings'] = new FrmSettings();
		return FrmAppHelper::get_settings();
	}

	public function test_unsupported_selection_uses_first_configured_provider() {
		$providers = array( 'recaptcha', 'hcaptcha', 'turnstile' );

		foreach ( $providers as $expected ) {
			$settings = $this->load_settings( 'altcha', $providers );
			$this->assertSame( $expected, $settings->active_captcha );
			$this->assertSame( $expected, FrmFieldCaptcha::get_captcha_image_name() );
			$this->assertSame( 'altcha', get_option( $settings->option_name )->active_captcha, 'Loading settings must preserve the saved selection.' );
			array_shift( $providers );
		}
	}

	public function test_supported_selection_is_preserved() {
		foreach ( array( 'recaptcha', 'hcaptcha', 'turnstile' ) as $type ) {
			$settings = $this->load_settings( $type, array( 'recaptcha', 'hcaptcha', 'turnstile' ) );
			$this->assertSame( $type, $settings->active_captcha );
		}
	}

	public function test_fallback_requires_both_keys() {
		$settings                 = $this->load_settings( 'altcha', array( 'turnstile' ) );
		$settings->pubkey         = 'test-site-key';
		$settings->active_captcha = 'altcha';
		update_option( $settings->option_name, $settings );
		$this->assertSame( 'turnstile', ( new FrmSettings() )->active_captcha );

		$settings = $this->load_settings( 'altcha', array() );
		$this->assertSame( 'recaptcha', $settings->active_captcha );
		$this->assertFalse( FrmFieldCaptcha::should_show_captcha() );
	}

	public function test_fallback_renders_matching_captcha_and_loads_matching_script() {
		$form_id = $this->factory->form->create();
		$this->factory->field->create(
			array(
				'form_id' => $form_id,
				'type'    => 'captcha',
			)
		);
		$providers = array(
			'recaptcha' => array( 'g-recaptcha', 'https://www.google.com/recaptcha/api.js' ),
			'hcaptcha'  => array( 'h-captcha', 'https://js.hcaptcha.com/1/api.js' ),
			'turnstile' => array( 'cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js' ),
		);

		foreach ( $providers as $type => $expected ) {
			$this->load_settings( 'altcha', array( $type ) );
			wp_deregister_script( 'captcha-api' );
			$html = do_shortcode( '[formidable id="' . $form_id . '"]' );
			$this->assertStringContainsString( $expected[0], $html );
			$this->assertStringContainsString( $expected[1], wp_scripts()->registered['captcha-api']->src );

			ob_start();
			FrmSettingsController::captcha_settings();
			$settings_html = ob_get_clean();
			$this->assertMatchesRegularExpression( '/id="frm-' . $type . '"[^>]+checked=/', $settings_html );
		}
	}
}
