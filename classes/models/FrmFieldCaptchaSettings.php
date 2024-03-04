<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.0
 */
class FrmFieldCaptchaSettings {

	/**
	 * @since x.x
	 *
	 * @var string
	 */
	public $name;

	/**
	 * @since 6.0
	 *
	 * @var string
	 */
	public $secret;

	/**
	 * @since 6.0
	 *
	 * @var string
	 */
	public $token_field;

	/**
	 * @since 6.0
	 *
	 * @var string
	 */
	public $endpoint;

	public function __construct( $frm_settings ) {
		$this->name        = '';
		$this->secret      = '';
		$this->token_field = '';
		$this->endpoint    = '';
	}
}
