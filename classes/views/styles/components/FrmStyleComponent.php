<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmStyleComponent {

	/**
	 * The CSS and JS scripts slug.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const ASSETS_SLUG = 'formidable-style-components';

	/**
	 * The folder name where views files are located.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	private $view_folder;

	/**
	 * The view file name.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	protected $view_name;

	/**
	 * The FrmStyleComponent data.
	 *
	 * @since x.x
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The name of the input field that will handle the form value
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	protected $field_name;

	/**
	 * The field value
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	protected $field_value;

	/**
	 * The FrmStyleComponent instance.
	 *
	 * @since x.x
	 *
	 * @var stdClass
	 */
	private static $instance = null;

	public function __construct() {
		$this->load_css();
		$this->load_js();
	}

	/**
	 * Register CSS & JS style components assets.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_assets() {
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		wp_register_style( self::ASSETS_SLUG, $plugin_url . '/css/admin/style-components.css', array(), $version );
		wp_register_script( self::ASSETS_SLUG, $plugin_url . '/js/formidable_styles.js', array( 'formidable_admin' ), $version, true );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since x.x
	 *
	 * @return void|stdClass
	 */
	protected static function get_instance() {
		if ( self::$instance ) {
			return;
		}

		self::$instance = new FrmStyleComponent();
		return self::$instance;
	}

	/**
	 * Get the default wrapper classnames.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	protected function get_default_wrapper_class_names() {
		$class = 'frm-style-component';
		if ( ! empty( $this->data['will_change'] ) ) {
			return $class . ' frm-style-dependent-updater-component';
		}
		return $class;
	}

	/**
	 * Get the wrapper classnames.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	protected function get_wrapper_class_name() {
		return $this->get_default_wrapper_class_names();
	}

	/**
	 * Get the wrapper's attributes.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private function get_component_attributes() {
		$attributes = '';
		if ( ! empty( $this->data['will_change'] ) ) {
			$attributes .= 'data-will-change=' . wp_json_encode( $this->data['will_change'] );
		}
		return $attributes;
	}

	/**
	 * Get the input's name that will handle the form value.
	 * It can have no name attribute for cases when the component is a dependency updater. It will update multiple fields from the same form. It will require "will-change" attribute to be set
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private function get_field_name() {
		return ! empty( $this->field_name ) ? 'name=' . $this->field_name : '';
	}

	/**
	 * Init the full view path.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function set_view_path() {
		$this->view_folder = FrmAppHelper::plugin_path() . '/classes/views/styles/components/views/';
	}

	/**
	 * Load the view file.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	protected function load_view( $data ) {
		if ( empty( $this->view_name ) ) {
			return;
		}

		$this->set_view_path();
		$component_attr  = $this->get_component_attributes();
		$component_class = $this->get_wrapper_class_name();
		$component       = $data;
		$field_name      = $this->get_field_name();
		$field_value     = $this->field_value;

		include $this->view_folder . $this->view_name . '.php';
	}

	/**
	 * Load component CSS file.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function load_css() {
		wp_enqueue_style( self::ASSETS_SLUG );
	}

	/**
	 * Load component JS file.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function load_js() {
		wp_enqueue_script( self::ASSETS_SLUG );
	}
}
