<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmStyleComponent {

	const ASSETS_SLUG = 'formidable-style-components';

	private   $view_folder;
	protected $view_name;
	protected $data;
	protected $field_name;
	protected $field_value;

	private static $instance = null;

	public function __construct( $field_name, $data ) {
		$this->load_css();
		$this->load_js();
	}

	public static function register_assets() {
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		wp_register_style( self::ASSETS_SLUG, $plugin_url . '/css/admin/style-components.css', array(), $version );
		wp_register_script( self::ASSETS_SLUG, $plugin_url . '/js/formidable_styles.js', array( 'formidable_admin' ), $version, true );
	}

	protected static function get_instance( $field_name, $field_value, $data ) {
		if ( self::$instance ) {
			return;
		}

		self::$instance = new FrmStyleComponent( $field_name, $data );
		return self::$instance;
	}

	private function init_view_path() {
		$this->view_folder = FrmAppHelper::plugin_path() . '/classes/views/styles/components/views/';
	}

	protected function load_view() {
		if ( empty( $this->view_name ) ) {
			return;
		}

		$this->init_view_path();
		$component   = $this->data;
		$field_name  = $this->field_name;
		$field_value = $this->field_value;

		include $this->view_folder . $this->view_name . '.php';
	}

	private function load_css() {
		wp_enqueue_style( self::ASSETS_SLUG );
	}

	private function load_js() {
		wp_enqueue_script( self::ASSETS_SLUG );
	}
}
