<?php
/**
 * ALTCHA as a Formidable captcha type.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Describes ALTCHA to Lite's Captcha field and Global Settings.
 *
 * ALTCHA uses a site-generated signing key, so no service credentials are required. Lite's captcha field still renders the
 * element, checks the response is posted, and handles failures and spam entries the same
 * way it does for the other captcha types.
 *
 * @since x.x
 */
class FrmAltchaSettings extends FrmFieldCaptchaSettings {

	/**
	 * The captcha type key saved in the active_captcha global setting.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const TYPE = 'altcha';

	/**
	 * There is no secret. Challenges are signed with a key generated for this site.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	protected function set_secret() {
		$this->secret = '';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ALTCHA';
	}

	/**
	 * The element class, which also names the posted response: frm-altcha-response.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_element_class_name() {
		return 'frm-altcha';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://altcha.org/';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_settings_prefix() {
		return 'altcha_';
	}

	/**
	 * ALTCHA needs no site key, so it is always ready. Lite only shows a captcha with a key.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_pubkey() {
		return self::TYPE;
	}

	/**
	 * The widget has a single size.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public function should_show_captcha_size() {
		return false;
	}

	/**
	 * The widget follows the form style instead of a light or dark theme.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public function should_show_captcha_theme() {
		return false;
	}

	/**
	 * Replace the site key attributes with what js/src/altcha-field.js needs to build the widget.
	 *
	 * @since x.x
	 *
	 * @param array $attributes Element attributes.
	 * @param array $field      The captcha field.
	 *
	 * @return array
	 */
	public function add_front_end_element_attributes( $attributes, $field ) {
		unset( $attributes['data-sitekey'] );

		$settings      = FrmAltchaHelper::get_settings();
		$configuration = array(
			'display'           => $settings['display'],
			'type'              => $settings['widget_type'],
			'hideLogo'          => 1 === $settings['hide_logo'],
			'hideFooter'        => 1 === $settings['hide_footer'],
			'validationMessage' => __( 'Please verify that you are not a robot.', 'formidable' ),
		);

		$attributes['data-name']          = $this->token_field;
		$attributes['data-challenge']     = FrmAltchaHelper::challenge_url();
		$attributes['data-auto']          = $settings['auto'];
		$attributes['data-language']      = FrmAltchaController::language();
		$attributes['data-configuration'] = (string) wp_json_encode( $configuration );

		return $attributes;
	}

	/**
	 * Explain ALTCHA's own failure codes in stored spam entries.
	 *
	 * @since x.x
	 *
	 * @return array<string,string>
	 */
	public function get_error_code_messages() {
		return array_merge(
			parent::get_error_code_messages(),
			array(
				'altcha-malformed'         => __( 'The ALTCHA response was not in the expected format.', 'formidable' ),
				'altcha-invalid-signature' => __( 'The ALTCHA challenge was not issued by this site or was changed.', 'formidable' ),
				'altcha-expired'           => __( 'The ALTCHA challenge expired.', 'formidable' ),
				'altcha-invalid-solution'  => __( 'The ALTCHA proof of work was wrong.', 'formidable' ),
				'altcha-replayed'          => __( 'The ALTCHA solution was already used.', 'formidable' ),
			)
		);
	}
}
