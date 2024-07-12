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

	public function __construct() {
		$this->load_css();
		$this->load_js();
	}

	public static function register_assets() {
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		wp_register_style( self::ASSETS_SLUG, $plugin_url . '/css/admin/style-components.css', array(), $version );
		wp_register_script( self::ASSETS_SLUG, $plugin_url . '/js/formidable_styles.js', array( 'formidable_admin' ), $version, true );
	}

	protected static function get_instance() {
		if ( self::$instance ) {
			return;
		}

		self::$instance = new FrmStyleComponent();
		return self::$instance;
	}

	private function get_wrapper_class_name() {
		$class = 'frm-style-component';
		if ( ! empty( $this->data['will_change'] ) ) {
			return $class . ' frm-style-dependend-updater-component';
		}
		return $class;
	}

	private function get_component_attributes() {
		$attributes = '';
		if ( isset( $this->data['will_change'] ) ) {
			$attributes .= 'data-will-change=' . wp_json_encode( $this->data['will_change'] );
		}
		return $attributes;
	}

	private function get_field_name() {
		if ( empty( $this->field_name ) ) {
			return '';
		}
		return 'name=' . $this->field_name;
	}

	private function init_view_path() {
		$this->view_folder = FrmAppHelper::plugin_path() . '/classes/views/styles/components/views/';
	}

	protected function load_view( $data ) {
		if ( empty( $this->view_name ) ) {
			return;
		}

		$this->init_view_path();
		$component_attr  = $this->get_component_attributes();
		$component_class = $this->get_wrapper_class_name();
		$component       = $data;
		$field_name      = $this->get_field_name();
		$field_value     = $this->field_value;

		include $this->view_folder . $this->view_name . '.php';
	}

	private function load_css() {
		wp_enqueue_style( self::ASSETS_SLUG );
	}

	private function load_js() {
		wp_enqueue_script( self::ASSETS_SLUG );
	}
}
