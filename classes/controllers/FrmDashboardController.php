<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmDashboardController {

	/**
	 * Handle name used for registering controller scripts and style.
	 *
	 * @var string Handle name used for wp_register_script|wp_register_style
	 */
	public static $assets_handle_name = 'formidable-dashboard';

	public static function menu() {
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Dashboard', 'formidable' ), __( 'Dashboard', 'formidable' ), 'frm_view_forms', 'formidable-dashboard', 'FrmDashboardController::route' );
	}

	public static function route() {
		$dashboard_view = new FrmDashboardView(
			array(
				'counters' => array(
					'template-type' => '',
					'counters'      => array(
						array(
							'heading' => 'Total Forms',
							'type'    => 'default',
							'counter' => 120,
						),
						array(
							'heading' => 'Total Entries',
							'type'    => 'default',
							'counter' => 300,
						),
						array(
							'heading' => 'All Views',
							'counter' => 65,
							'type'    => 'default',
						),
						array(
							'heading' => 'Installed Apps',
							'counter' => 75,
							'type'    => 'default',
						),
					),
				),
			),
		);
		require FrmAppHelper::plugin_path() . '/classes/views/dashboard/dashboard.php';
	}

	/**
	 * Register controller assets.
	 *
	 * @return void
	 */
	public static function register_assets() {
		wp_register_script( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/js/formidable_dashboard.js', array(), FrmAppHelper::plugin_version(), true );
		wp_register_style( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/css/admin/dashboard.css', array(), FrmAppHelper::plugin_version() );
	}

	/**
	 * Enqueue controller assets.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {

		if ( 'formidable-dashboard' !== FrmAppHelper::simple_get( 'page', 'sanitize_title' ) ) {
			return;
		}

		wp_enqueue_style( self::$assets_handle_name );
		wp_enqueue_script( self::$assets_handle_name );
	}

}
