<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 5.0.06
 */
class FrmElementorController {

	/**
	 * @return void
	 */
	public static function register_elementor_hooks() {
		require_once FrmAppHelper::plugin_path() . '/classes/widgets/FrmElementorWidget.php';
		\Elementor\Plugin::instance()->widgets_manager->register( new \FrmElementorWidget() );

		if ( is_admin() ) {
			add_action(
				'elementor/editor/after_enqueue_styles',
				function() {
					wp_enqueue_style( 'font_icons', FrmAppHelper::plugin_url() . '/css/font_icons.css', array(), FrmAppHelper::plugin_version() );
				}
			);
		}
	}

	/**
	 * @return void
	 */
	public static function admin_init() {
		FrmAppController::load_wp_admin_style();
		FrmFormsController::insert_form_popup();
	}
}
