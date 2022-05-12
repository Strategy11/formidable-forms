<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 5.3
 */
class FrmApplicationsController {

	/**
	 * Add Applications menu item to sidebar and define Applications index page.
	 *
	 * @return void
	 */
	public static function menu() {
		$label    = __( 'Applications', 'formidable' );
		$new_pill = '<span class="frm-new-pill">NEW</span>';
		add_submenu_page( 'formidable', 'Formidable | ' . $label, $label . $new_pill, 'frm_edit_forms', 'formidable-applications', array( __CLASS__, 'landing_page' ) );
	}

	/**
	 * Render Applications index page.
	 *
	 * @return void
	 */
	public static function landing_page() {
		require self::get_view_path() . 'index.php';
	}

	/**
	 * Get path to application views.
	 *
	 * @return string
	 */
	private static function get_view_path() {
		return FrmAppHelper::plugin_path() . '/classes/views/applications/';
	}

	/**
	 * Get information about applications via AJAX action.
	 *
	 * @return void
	 */
	public static function get_applications_data() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$view = FrmAppHelper::get_param( 'view', '', 'get', 'sanitize_text_field' );
		$data = array();
		if ( 'applications' !== $view ) {
			// view may be 'applications', 'templates', or empty.
			$data['templates']  = self::get_prepared_template_data();
			$data['categories'] = FrmApplicationTemplate::get_categories();
		}

		/**
		 * @param array $data
		 */
		$data = apply_filters( 'frm_applications_data', $data );

		wp_send_json_success( $data );
	}

	/**
	 * Retrieve API data and return a reduced set back with some restructuring applied to make it easier to read.
	 *
	 * @return array<array>
	 */
	private static function get_prepared_template_data() {
		$api          = new FrmApplicationApi();
		$applications = $api->get_api_info();
		$applications = array_filter( $applications, 'is_array' );
		$applications = self::sort_templates( $applications );

		FrmApplicationTemplate::init();

		return array_reduce( $applications, array( __CLASS__, 'reduce_template' ), array() );
	}

	/**
	 * @param array $total the accumulated array of reduced application data.
	 * @param array $current data for the current template from the API.
	 * @return array<array>
	 */
	private static function reduce_template( $total, $current ) {
		$template = new FrmApplicationTemplate( $current );
		$total[]  = $template->as_js_object();
		return $total;
	}

	/**
	 * Sort applications alphabetically.
	 *
	 * @param array<array> $applications
	 * @return array<array>
	 */
	private static function sort_templates( $applications ) {
		usort(
			$applications,
			function( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			}
		);
		return $applications;
	}

	/**
	 * @return void
	 */
	public static function load_assets() {
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		wp_enqueue_style( 'formidable-admin' );
		wp_enqueue_style( 'formidable-grids' );

		$js_dependencies = array(
			'wp-i18n',
			'formidable_dom',
		);
		wp_register_script( 'formidable_applications', $plugin_url . '/js/admin/applications.js', $js_dependencies, $version, true );
		wp_register_style( 'formidable_applications', $plugin_url . '/css/admin/applications.css', array(), $version );

		$js_vars = array(
			'proUpgradeUrl' => FrmAppHelper::admin_upgrade_link( 'applications' ),
		);
		wp_localize_script( 'formidable_applications', 'frmApplicationsVars', $js_vars );

		wp_enqueue_script( 'formidable_applications' );
		wp_enqueue_style( 'formidable_applications' );

		do_action( 'frm_applications_assets' );
	}

	/**
	 * @return void
	 */
	public static function dequeue_scripts() {
		if ( 'formidable-applications' === FrmAppHelper::simple_get( 'page', 'sanitize_title' ) ) {
			// Avoid extra scripts loading on applications index that aren't needed.
			wp_dequeue_script( 'frm-surveys-admin' );
			wp_dequeue_script( 'frm-quizzes-form-action' );
		}
	}

	/**
	 * @param string $title
	 * @param string $context values include 'index', 'list', and 'edit'.
	 * @return void
	 */
	public static function render_applications_header( $title, $context ) {
		require self::get_view_path() . 'header.php';
	}
}
