<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmTestModeController {

	/**
	 * Maybe add the test mode container.
	 *
	 * @since x.x
	 *
	 * @param string $html
	 * @return string
	 */
	public static function maybe_add_test_mode_container( $html ) {
		if ( '' === $html || ! FrmAppHelper::simple_get( 'testmode' ) || ! current_user_can( 'frm_view_forms' ) ) {
			return $html;
		}

		$html = str_replace(
			'<div class="frm_form_fields',
			self::get_testing_mode_container() . '<div class="frm_form_fields',
			$html
		);

		return $html;
	}

	/**
	 * Get the testing mode container.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_testing_mode_container() {
		return FrmAppHelper::clip(
			function () {
				include FrmAppHelper::plugin_path() . '/classes/views/test-mode/container.php';
			}
		);
	}
}
