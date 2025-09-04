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
	 * Initialize hooks for Dashboard page only.
	 *
	 * @since x.x
	 */
	public static function load_admin_hooks() {
		self::$checklist = get_option( self::CHECKLIST_OPTION, array() );

		if ( 'done' === self::$checklist ) {
			return;
		}

		if ( self::is_welcome_tour_not_seen() ) {
			self::mark_welcome_tour_as_seen();
		}

		// TODO: remove this after development
		self::$checklist['seen'] = false;
		self::set_checklist( self::$checklist );

		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_assets', 15 );
		add_filter( 'admin_body_class', __CLASS__ . '::add_admin_body_classes', 999 );
		// add_filter( 'frm_show_footer_links', '__return_false' );
	}

	/**
	 * Checks if the welcome tour has been seen.
	 *
	 * @since x.x
	 *
	 * @return bool True if the welcome tour has been seen, false otherwise.
	 */
	private static function is_welcome_tour_not_seen() {
		return FrmDashboardController::is_dashboard_page() && empty( self::$checklist['seen'] );
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
			'INITIAL_STEP'         => self::$checklist['step'] ?? self::INITIAL_STEP,
			'IS_WELCOME_TOUR_SEEN' => ! self::is_welcome_tour_not_seen(),
			'i18n'                 => array(
				'CHECKLIST_TEXT' => __( 'Checklist', 'formidable' ),
			),
			'PROGRESS_BAR_PERCENT'   => self::get_welcome_tour_progress_bar_percent(),
		);
	}

	/**
	 * Get the Welcome Tour progress bar percentage.
	 *
	 * @since x.x
	 *
	 * @return int
	 */
	private static function get_welcome_tour_progress_bar_percent() {
		if ( empty( self::$checklist['step'] ) ) {
			return 0;
		}

		$percent = ( self::$checklist['step'] / count( self::get_steps() ) ) * 100;

		return (int) $percent;
	}

	/**
	 * @return array
	 */
	private static function get_steps() {
		return array(
			'create-a-form',
			'update-form',
			'first-entry',
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
	//	update_option( self::CHECKLIST_OPTION, self::$checklist, 'no' );
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
