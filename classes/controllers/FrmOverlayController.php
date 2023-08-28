<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmOverlayController {

	/**
	 * The singleton object.
	 *
	 * @var object It will store the instance object
	 */
	private static $instance;

	private static $assets_handle_name = 'formidable-overlay';

	/**
	 * Create singleton object.
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function open_overlay( $data = array() ) {

		$data = array(
			'heroImage' => FrmAppHelper::plugin_url() . '/images/overlay/lock.svg',
			'heading'   => 'Heads up! Your license has expired',
			'copy'      => 'An active license is needed to access new features & addons, plugin updates, and our world class support!',
			'buttons'   => array(
				array(
					'url'   => 'https://localhost',
					'label' => 'Learn More',
				),
				array(
					'url'   => 'https://localhost',
					'label' => 'Renew license now',
				),
			),
		);

		$inline_script = 'frmOverlay.open(' . wp_json_encode( $data ) . ')';
		wp_add_inline_script( self::$assets_handle_name, $inline_script, 'after' );
	}

	public static function register_assets() {
		wp_register_script( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/js/formidable_overlay.js', array(), FrmAppHelper::plugin_version(), true );
		wp_register_style( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/css/frm_overlay.css', array(), FrmAppHelper::plugin_version() );
		self::enqueue_assets();
	}

	public static function enqueue_assets() {
		wp_enqueue_style( self::$assets_handle_name );
		wp_enqueue_script( self::$assets_handle_name );
	}

}
