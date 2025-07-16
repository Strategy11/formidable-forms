<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTestModeController {


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

	private static function get_testing_mode_container() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/test-mode/container.php';
		return ob_get_clean();
	}
}
