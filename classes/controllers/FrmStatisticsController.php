<?php

class FrmStatisticsController {

	public static function menu() {
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Views', 'formidable' ), '<span class="frm_inactive_menu">' . __( 'Views', 'formidable' ) . '</span>', 'administrator', 'formidable-entry-templates', 'FrmStatisticsController::list_displays' );
	}

	public static function list_reports() {
		add_filter( 'frm_form_stop_action_reports', '__return_true' );
		$form = FrmAppHelper::get_param( 'form', false, 'get', 'absint' );
		require( FrmAppHelper::plugin_path() . '/classes/views/frm-statistics/list.php' );
	}

	public static function list_displays() {
		$form = FrmAppHelper::get_param( 'form', false, 'get', 'sanitize_title' );
		require( FrmAppHelper::plugin_path() . '/classes/views/frm-statistics/list_displays.php' );
	}

}
