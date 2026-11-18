<?php

/**
 * @group altcha
 *
 * @covers FrmAltchaHelper
 * @covers FrmAltchaSettings
 */
class test_FrmAltchaVisitorSettings extends FrmUnitTest {

	/**
	 * The saved layout reaches the widget's configuration without changing verification.
	 */
	public function test_display_choices_reach_the_widget() {
		$provider = FrmCaptchaFactory::get_settings_object( 'altcha' );

		foreach ( array_keys( FrmAltchaHelper::display_modes() ) as $display ) {
			update_option(
				FrmAltchaHelper::SETTINGS_OPTION,
				array(
					'display'     => $display,
					'widget_type' => 'switch',
					'hide_logo'   => 1,
					'hide_footer' => 1,
				)
			);
			$attrs  = $provider->add_front_end_element_attributes( array(), array() );
			$config = json_decode( $attrs['data-configuration'], true );

			$this->assertSame( $display, $config['display'], 'The selected layout should reach the widget.' );
			$this->assertArrayNotHasKey( 'type', $config, 'The widget should use its default checkbox control.' );
			$this->assertArrayNotHasKey( 'hideLogo', $config, 'The widget should keep the ALTCHA logo.' );
			$this->assertArrayNotHasKey( 'hideFooter', $config, 'The widget should keep the ALTCHA footer.' );
			$this->assertSame( 'frm-altcha-response', $attrs['data-name'], 'The layout should not change the validation token name.' );
		}
	}

	/**
	 * Unsupported or malformed options fall back to the existing visible checkbox.
	 */
	public function test_invalid_visitor_options_use_safe_defaults() {
		foreach ( array( 'overlay', 'bar', 'switch', 'native', 'unsupported', array( 'floating' ) ) as $invalid ) {
			$settings = FrmAltchaHelper::sanitize_settings(
				array(
					'display'     => $invalid,
					'widget_type' => $invalid,
					'hide_logo'   => $invalid,
					'hide_footer' => $invalid,
					'test'        => true,
				)
			);
			$this->assertSame( 'standard', $settings['display'], 'Invalid layouts should fall back to inline display.' );
			$this->assertArrayNotHasKey( 'widget_type', $settings, 'Removed control settings should not be saved.' );
			$this->assertArrayNotHasKey( 'hide_logo', $settings, 'Removed branding settings should not be saved.' );
			$this->assertArrayNotHasKey( 'hide_footer', $settings, 'Removed branding settings should not be saved.' );
			$this->assertArrayNotHasKey( 'test', $settings, 'Posted settings must not enable mock verification.' );
		}
	}
}
