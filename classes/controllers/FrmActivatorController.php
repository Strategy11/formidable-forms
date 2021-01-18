<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmActivatorController {
	/**
	 * Called on plugin activation.
	 */
	public static function activate() {
		self::frm_welcome_screen_activate();
	}

	/**
	 * Register a "custom" endpoint to be applied to root-level.
	 *
	 * @return void
	 */
	private static function frm_welcome_screen_activate() {
		add_option( 'frm_welcome_screen_activation_redirect', 'yes' );
	}
}
