<?php

/**
 * @group altcha
 *
 * @covers FrmAltchaHelper
 * @covers FrmAltchaSettings
 */
class test_FrmAltchaVisitorSettings extends FrmUnitTest {

	/**
	 * Saved visitor controls reach the widget's configuration without changing verification.
	 */
	public function test_display_and_control_choices_reach_the_widget() {
		$provider = FrmCaptchaFactory::get_settings_object( 'altcha' );

		foreach ( array_keys( FrmAltchaHelper::display_modes() ) as $display ) {
			foreach ( array_keys( FrmAltchaHelper::widget_types() ) as $type ) {
				update_option(
					FrmAltchaHelper::SETTINGS_OPTION,
					array(
						'display'     => $display,
						'widget_type' => $type,
						'hide_logo'   => 1,
					)
				);
				$attrs  = $provider->add_front_end_element_attributes( array(), array() );
				$config = json_decode( $attrs['data-configuration'], true );

				$this->assertSame( $display, $config['display'], 'The selected layout should reach the widget.' );
				$this->assertSame( $type, $config['type'], 'The selected interaction style should reach the widget.' );
				$this->assertTrue( $config['hideLogo'], 'The logo should follow its own visibility setting.' );
				$this->assertFalse( $config['hideFooter'], 'Hiding the logo should preserve the footer unless separately selected.' );
				$this->assertSame( 'frm-altcha-response', $attrs['data-name'], 'Visitor controls should preserve the validation token name.' );
			}
		}
	}

	/**
	 * Unsupported or malformed options fall back to the existing visible checkbox.
	 */
	public function test_invalid_visitor_options_use_safe_defaults() {
		foreach ( array( 'unsupported', array( 'floating' ) ) as $invalid ) {
			$settings = FrmAltchaHelper::sanitize_settings(
				array(
					'display'     => $invalid,
					'widget_type' => $invalid,
					'hide_logo'   => $invalid,
					'test'        => true,
				)
			);
			$this->assertSame( 'standard', $settings['display'], 'Invalid layouts should fall back to inline display.' );
			$this->assertSame( 'checkbox', $settings['widget_type'], 'Invalid controls should fall back to the checkbox.' );
			$this->assertSame( 0, $settings['hide_logo'], 'Invalid branding values should preserve the logo.' );
			$this->assertArrayNotHasKey( 'test', $settings, 'Posted settings must not enable mock verification.' );
		}
	}
}
