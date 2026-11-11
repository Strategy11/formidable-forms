<?php

/**
 * @group altcha
 */
class test_FrmAltchaDefaults extends FrmUnitTest {

	/**
	 * The built-in ALTCHA tab comes before the three existing providers.
	 */
	public function test_settings_show_altcha_first() {
		ob_start();
		FrmSettingsController::captcha_settings();
		$html = ob_get_clean();
		preg_match_all( '/name="frm_active_captcha"[^>]*value="([a-z]+)"/', $html, $matches );

		$this->assertSame( array( 'altcha', 'recaptcha', 'hcaptcha', 'turnstile' ), $matches[1], 'ALTCHA should be the first of four CAPTCHA options.' );
		$this->assertStringContainsString( 'id="altcha_settings"', $html, 'The ALTCHA settings panel should be included.' );
	}

	/**
	 * Defaults apply only to legacy providers with no keys, before a type is saved.
	 */
	public function test_legacy_provider_without_keys_defaults_to_altcha() {
		foreach ( array( 'recaptcha', 'hcaptcha', 'turnstile' ) as $provider ) {
			update_option(
				'frm_options',
				(object) array(
					'active_captcha' => $provider,
					'pubkey'         => '',
					'privkey'        => '',
				)
			);
			$settings = new FrmSettings();
			$this->assertSame( 'altcha', $settings->active_captcha, 'Legacy providers without keys should default to ALTCHA.' );
		}
	}

	/**
	 * Existing keys, including partially configured providers, preserve the selection.
	 */
	public function test_existing_provider_keys_preserve_selection() {
		foreach ( array(
			'recaptcha' => '',
			'hcaptcha'  => 'hcaptcha_',
			'turnstile' => 'turnstile_',
		) as $provider => $prefix ) {
			foreach ( array( 'pubkey', 'privkey' ) as $key ) {
				update_option(
					'frm_options',
					(object) array(
						'active_captcha' => $provider,
						'pubkey'         => '',
						'privkey'        => '',
						$prefix . $key   => 'test-key',
					)
				);
				$settings = new FrmSettings();
				$this->assertSame( $provider, $settings->active_captcha, 'Existing keys should preserve the selected provider.' );
			}
		}
	}

	/**
	 * Saving a selection opts out of the compatibility fallback on later requests.
	 */
	public function test_saved_selection_without_keys_does_not_fallback() {
		foreach ( array( 'recaptcha', 'hcaptcha', 'turnstile', 'altcha' ) as $provider ) {
			$settings = new FrmSettings();
			$params   = array();

			foreach ( get_object_vars( $settings ) as $key => $value ) {
				$params[ 'frm_' . $key ] = $value;
			}

			$params['frm_active_captcha'] = $provider;

			foreach ( array( '', 'hcaptcha_', 'turnstile_' ) as $prefix ) {
				$params[ 'frm_' . $prefix . 'pubkey' ]  = '';
				$params[ 'frm_' . $prefix . 'privkey' ] = '';
			}

			$this->run_private_method( array( $settings, 'update_settings' ), array( $params ) );
			$settings->store();
			$reloaded = new FrmSettings();

			$this->assertTrue( $reloaded->captcha_type_selected, 'Saving a provider should record an explicit selection.' );
			$this->assertSame( $provider, $reloaded->active_captcha, 'Explicit selections should survive reloads without keys.' );
		}
	}

	/**
	 * A new installation needs no API keys to use the CAPTCHA field.
	 */
	public function test_new_settings_default_to_altcha() {
		delete_option( 'frm_options' );
		delete_option( 'recaptcha' );
		$settings = new FrmSettings();
		$this->assertSame( 'altcha', $settings->active_captcha, 'New installations should default to ALTCHA.' );
	}
}
