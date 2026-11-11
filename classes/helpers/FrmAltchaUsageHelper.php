<?php
/**
 * ALTCHA settings for Formidable's opt-in usage snapshot.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Reports configuration only. No challenges, solutions, IP addresses or entry values are collected.
 *
 * @since x.x
 */
class FrmAltchaUsageHelper {

	/**
	 * Add the ALTCHA settings when Formidable builds a usage snapshot.
	 *
	 * @since x.x
	 *
	 * @param array $snapshot Formidable's usage snapshot.
	 *
	 * @return array
	 */
	public static function add_snapshot_data( $snapshot ) {
		$settings = FrmAltchaHelper::get_settings();

		$snapshot['altcha'] = array(
			'version'        => FrmAppHelper::$plug_version,
			'active'         => FrmAltchaController::is_active() ? 1 : 0,
			'captcha_fields' => FrmDb::get_count( 'frm_fields', array( 'type' => 'captcha' ) ),
			'difficulty'     => $settings['difficulty'],
			'expires'        => $settings['expires'],
			'auto'           => $settings['auto'],
			'hide_footer'    => $settings['hide_footer'],
		);

		return $snapshot;
	}
}
