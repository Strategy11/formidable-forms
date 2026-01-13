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

		add_action( 'elementor/editor/after_enqueue_styles', array( self::class, 'enqueue_editor_styles' ) );
	}

	/**
	 * Enqueue styles for the Elementor editor to display the Formidable widget icon.
	 *
	 * @since 6.27
	 *
	 * @return void
	 */
	public static function enqueue_editor_styles() {
		$icon = rawurlencode( FrmAppHelper::svg_logo() );
		$css  = '
		.elementor-element .icon .frm_logo_icon {
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.elementor-element .icon .frm_logo_icon::before {
			content: "";
			display: block;
			width: 28px;
			height: 28px;
			background: url("data:image/svg+xml,' . $icon . '") center / contain no-repeat;
		}';

		wp_add_inline_style( 'elementor-editor', $css );
	}

	/**
	 * @return void
	 */
	public static function admin_init() {
		FrmAppController::load_wp_admin_style();
		FrmFormsController::insert_form_popup();
	}
}
