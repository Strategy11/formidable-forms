<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmTurnstileSettings extends FrmFieldCaptchaSettings {

	public function __construct( $frm_settings ) {
		$this->secret      = $frm_settings->turnstile_privkey;
		$this->token_field = 'frm-turnstile-response';
		$this->endpoint    = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
	}
}
