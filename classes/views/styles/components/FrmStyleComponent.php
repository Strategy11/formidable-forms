<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmStyleComponent {

	/**
	 * The CSS and JS scripts slug.
	 *
	 * @since 6.14
	 *
	 * @var string
	 */
	const ASSETS_SLUG = 'formidable-style-components';

	/**
	 * The folder name where views files are located.
	 *
	 * @since 6.14
	 *
	 * @var string
	 */
	private $view_folder;

	/**
	 * The view file name.
	 *
	 * @since 6.14
	 *
	 * @var string
	 */
	protected $view_name;

	/**
	 * The FrmStyleComponent data.
	 *
	 * @since 6.14
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The name of the input field that will handle the form value
	 *
	 * @since 6.14
	 *
	 * @var string
	 */
	protected $field_name;

	/**
	 * The field value
	 *
	 * @since 6.14
	 *
	 * @var string
	 */
	protected $field_value;

	/**
	 * The FrmStyleComponent instance.
	 *
	 * @since 6.14
	 *
	 * @var stdClass|null
	 */
	private static $instance;

	private function __construct() {
		$this->load_css();
		$this->load_js();
	}

	/**
	 * Register CSS & JS style components assets.
	 *
	 * @since 6.14
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
	 * @since 6.14
	 *
	 * @return stdClass|void
	 */
	protected static function get_instance() {
		if ( self::$instance ) {
			return;
		}

		self::$instance = new FrmStyleComponent();
		return self::$instance;
	}

	protected function init( $data, $field_name, $field_value ) {
		$this->init_field_data( $data, $field_name, $field_value );
		self::get_instance();
		$this->load_view();
	}

	/**
	 * Init the field component data, field name and field value.
	 *
	 * @since 6.14
	 * @param array  $data The field extra options.
	 * @param string $field_name The field input's name.
	 * @param mixed  $field_value The field value.
	 * @return void
	 */
	protected function init_field_data( $data, $field_name, $field_value ) {
		$this->data        = $data;
		$this->field_name  = $field_name;
		$this->field_value = $field_value;
	}

	/**
	 * Get the default wrapper classnames.
	 *
	 * @since 6.14
	 *
	 * @return string
	 */
	protected function get_default_wrapper_class_names() {
		$class = 'frm-style-component';
		if ( ! empty( $this->data['classname'] ) ) {
			$class .= ' ' . $this->data['classname'];
		}
		if ( ! empty( $this->data['will_change'] ) ) {
			return $class . ' frm-style-dependent-updater-component';
		}
		return $class;
	}

	/**
	 * Get the wrapper classnames.
	 *
	 * @since 6.14
	 *
	 * @return string
	 */
	protected function get_wrapper_class_name() {
		return $this->get_default_wrapper_class_names();
	}

	/**
	 * Get the wrapper's attributes.
	 *
	 * @since 6.14
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
	 * @since 6.14
	 *
	 * @return string
	 */
	private function get_field_name() {
		return ! empty( $this->field_name ) ? 'name=' . $this->field_name : '';
	}

	/**
	 * Init the full view path.
	 *
	 * @since 6.14
	 *
	 * @return void
	 */
	private function set_view_path() {
		$this->view_folder = FrmAppHelper::plugin_path() . '/classes/views/styles/components/templates/';
	}

	/**
	 * Load the view file.
	 *
	 * @since 6.14
	 *
	 * @return void
	 */
	protected function load_view() {
		if ( empty( $this->view_name ) ) {
			return;
		}

		$this->set_view_path();
		$component_attr  = $this->get_component_attributes();
		$component_class = $this->get_wrapper_class_name();
		$component       = $this->data;
		$field_name      = $this->get_field_name();
		$field_value     = $this->field_value;

		include $this->view_folder . $this->view_name . '.php';
	}

	protected function hide_component() {
		if ( empty( $this->data['not_show_in'] ) ) {
			return false;
		}

		if ( FrmAppHelper::get_param( 'section', '', 'get', 'sanitize_text_field' ) === $this->data['not_show_in'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Load component CSS file.
	 *
	 * @since 6.14
	 *
	 * @return void
	 */
	private function load_css() {
		wp_enqueue_style( self::ASSETS_SLUG );
	}

	/**
	 * Load component JS file.
	 *
	 * @since 6.14
	 *
	 * @return void
	 */
	private function load_js() {
		wp_enqueue_script( self::ASSETS_SLUG );
	}
}
