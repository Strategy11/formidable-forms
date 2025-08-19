<?php
/**
 * Welcome Tour Controller class.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Handles the Welcome Tour page in the admin area.
 *
 * @since x.x
 */
class FrmWelcomeTourController {

	/**
	 * Defines the initial step for redirection within the application flow.
	 *
	 * @var string
	 */
	const INITIAL_STEP = 'pick-a-template';

	/**
	 * The required user capability to view the Welcome Tour page.
	 *
	 * @var string
	 */
	const REQUIRED_CAPABILITY = 'frm_view_forms';

	/**
	 * Option name to store Welcome Tour data.
	 *
	 * @var string
	 */
	const CHECKLIST_OPTION = 'frm_welcome_tour_checklist';

	/**
	 * Option name to store usage data.
	 *
	 * @var string
	 */
	const USAGE_DATA_OPTION = 'frm_welcome_tour_usage_data';

	/**
	 * The script handle.
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'frm-welcome-tour';

	/**
	 * The slug of the Welcome Tour page.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'formidable-welcome-tour';

	/**
	 * Checklist data to pass to the view.
	 *
	 * @var string|array
	 */
	private static $checklist = array();

	/**
	 * Path to views.
	 *
	 * @var string
	 */
	private static $view_path = '';

	/**
	 * Initialize hooks for Dashboard page only.
	 *
	 * @since x.x
	 */
	public static function load_admin_hooks() {
		self::$checklist = get_option( self::CHECKLIST_OPTION, array() );

		if ( 'done' === self::$checklist ) {
			return;
		}

		self::$view_path = FrmAppHelper::plugin_path() . '/classes/views/welcome-tour/';

		if ( FrmDashboardController::is_dashboard_page() && empty( self::$checklist['seen'] ) ) {
			add_action( 'admin_footer', __CLASS__ . '::render_modal' );
			return;
		}

		add_action( 'admin_menu', __CLASS__ . '::menu', 1 );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_assets', 15 );
		add_filter( 'admin_body_class', __CLASS__ . '::add_admin_body_classes', 999 );
		// add_filter( 'frm_show_footer_links', '__return_false' );
	}

	/**
	 * Renders a modal component in the WordPress admin area.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function render_modal() {
		self::mark_welcome_tour_as_seen();
		include self::$view_path . 'get-started-modal.php';
	}

	/**
	 * Marks the welcome tour as seen.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function mark_welcome_tour_as_seen() {
		self::$checklist['seen'] = true;
		self::set_checklist( self::$checklist );
	}


	/**
	 * Add Welcome Tour menu item to sidebar.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function menu() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$label = __( 'Checklist', 'formidable' );

		add_submenu_page(
			'formidable',
			'Formidable | ' . $label,
			$label,
			self::REQUIRED_CAPABILITY,
			self::PAGE_SLUG,
			null
		);
	}

	/**
	 * Renders the Welcome Tour checklist in the WordPress admin area.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function render() {
		$step_parts = array();

		foreach ( $step_parts as $step_part ) {
			include self::$view_path . $step_part;
		}
	}

	/**
	 * Handle AJAX request to set up usage data for the Welcome Tour.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function setup_usage_data() {
		// Check permission and nonce.
		FrmAppHelper::permission_check( self::REQUIRED_CAPABILITY );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		// Retrieve the current usage data.
		$usage_data = self::get_usage_data();

		$fields_to_update = array(
			'allows_tracking'  => 'rest_sanitize_boolean',
			'installed_addons' => 'sanitize_text_field',
			'processed_steps'  => 'sanitize_text_field',
			'completed_steps'  => 'rest_sanitize_boolean',
		);

		foreach ( $fields_to_update as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$usage_data[ $field ] = FrmAppHelper::get_post_param( $field, '', $sanitize_callback );
			}
		}

		update_option( self::USAGE_DATA_OPTION, $usage_data );
		wp_send_json_success();
	}

	/**
	 * Enqueues the Welcome Tour page scripts and styles.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		wp_enqueue_style( self::SCRIPT_HANDLE, $plugin_url . '/css/admin/welcome-tour.css', array(), $version );
		wp_enqueue_style( self::SCRIPT_HANDLE );

		wp_register_script( self::SCRIPT_HANDLE, $plugin_url . '/js/welcome-tour.js', array( 'wp-i18n' ), $version, true );
		wp_localize_script( self::SCRIPT_HANDLE, 'frmWelcomeTourVars', self::get_js_variables() );
		wp_enqueue_script( self::SCRIPT_HANDLE );

		FrmAppHelper::dequeue_extra_global_scripts();
	}

	/**
	 * Get the Welcome Tour JS variables as an array.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_js_variables() {
		return array(
			'INITIAL_STEP' => self::$checklist['step'] ?? self::INITIAL_STEP,
		);
	}

	/**
	 * Adds custom classes to the existing string of admin body classes.
	 *
	 * @since x.x
	 *
	 * @param string $classes Existing body classes.
	 * @return string Updated list of body classes, including the newly added classes.
	 */
	public static function add_admin_body_classes( $classes ) {
		return $classes . ' frm-admin-welcome-tour';
	}


	/**
	 * Sets the checklist data.
	 *
	 * @since x.x
	 *
	 * @param array $checklist The checklist data to set.
	 */
	public static function set_checklist( $checklist ) {
		self::$checklist = $checklist;
		update_option( self::CHECKLIST_OPTION, self::$checklist );
	}

	/**
	 * Retrieves the current checklist data, returning an empty array if none exists.
	 *
	 * @since x.x
	 *
	 * @return array Current checklist data.
	 */
	public static function get_checklist() {
		return self::$checklist;
	}

	/**
	 * Get the path to the Onboarding Wizard views.
	 *
	 * @since x.x
	 *
	 * @return string Path to views.
	 */
	public static function get_view_path() {
		return self::$view_path;
	}

	/**
	 * Retrieves the current Onboarding Wizard usage data, returning an empty array if none exists.
	 *
	 * @since x.x
	 *
	 * @return array Current usage data.
	 */
	public static function get_usage_data() {
		return get_option( self::USAGE_DATA_OPTION, array() );
	}
}
