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
	 *
	 * @return void
	 */
	public static function load_admin_hooks() {
		self::$checklist = get_option( self::CHECKLIST_OPTION, array() );

		// self::$checklist['completed_steps'] = array();

		if ( 'done' === self::$checklist ) {
			return;
		}

		if ( self::is_welcome_tour_not_seen() ) {
			self::mark_welcome_tour_as_seen();
		}

		self::maybe_check_for_completed_steps();

		// TODO: remove this after development
		self::$checklist['seen'] = false;
		self::set_checklist();

		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_assets', 15 );
		add_filter( 'admin_body_class', __CLASS__ . '::add_admin_body_classes', 999 );

		add_action( 'frm_after_changed_form_style', __CLASS__ . '::maybe_mark_styler_step_as_completed' );
		add_action( 'frm_after_saved_style', __CLASS__ . '::maybe_mark_styler_step_as_completed' );
	}

	/**
	 * @return void
	 */
	private static function maybe_check_for_completed_steps() {
		if ( ! isset( self::$checklist['completed_steps'] ) ) {
			self::$checklist['completed_steps'] = array();
		}

		$steps     = self::get_steps();
		$step_keys = array_keys( $steps );
		foreach ( $step_keys as $step_key ) {
			$completed = in_array( $step_key, self::$checklist['completed_steps'], true );
			if ( $completed ) {
				continue;
			}

			switch ( $step_key ) {
				case 'create-first-form':
					$completed = self::more_than_the_default_form_exists();
					break;
			}

			if ( $completed ) {
				self::$checklist['completed_steps'][] = $step_key;
			}
		}

		$current_step = 0;
		foreach ( $step_keys as $step_key ) {
			if ( in_array( $step_key, self::$checklist['completed_steps'], true ) ) {
				$current_step++;
			} else {
				break;
			}
		}

		self::$checklist['step'] = $current_step;
		self::set_checklist();
	}

	/**
	 * Checks if more than the default form exists.
	 *
	 * @since x.x
	 *
	 * @return bool True if more than the default form exists, false otherwise.
	 */
	private static function more_than_the_default_form_exists() {
		$form_keys = FrmDb::get_col( 'frm_forms', array(), 'form_key' );
		if ( count( $form_keys ) > 1 ) {
			return true;
		}

		return $form_keys && ! in_array( 'contact-form', $form_keys, true );
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
		self::set_checklist();
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
			'IS_WELCOME_TOUR_SEEN' => ! self::is_welcome_tour_not_seen(),
			'i18n'                 => array(
				'CHECKLIST_HEADER_TITLE' => __( 'Formidable Checklist', 'formidable' ),
			),
			'PROGRESS_BAR_PERCENT'   => self::get_welcome_tour_progress_bar_percent(),
			'CHECKLIST_STEPS'        => self::get_steps(),
			'TOUR_URL'               => admin_url( 'admin.php?page=formidable-form-templates' ),
			'CHECKLIST_ACTIVE_STEP'  => self::get_active_step(),
		);
	}

	private static function get_active_step() {
		// TODO
		return 'embed-form';
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
		$steps = array(
			'create-first-form' => array(
				'title'       => __( 'Create your first form', 'formidable' ),
				'description' => __( 'Start from scratch or jump in with one of our ready-to-use templates.', 'formidable' ),
			),
			'add-fields' 	=> array(
				'title'       => __( 'Add fields to your form', 'formidable' ),
				'description' => __( 'Click or drag fields from the left to add them to your form. Edit and/or delete them as needed.', 'formidable' ),
			),
			'style-form' => array(
				'title'       => __( 'Style your form', 'formidable' ),
				'description' => __( 'Our default style looks great, but feel free to modify it! Change the color, font size, spacing, or whatever else you\'d like.', 'formidable' ),
			),
			'embed-form' => array(
				'title'       => __( 'Embed in a page', 'formidable' ),
				'description' => __( 'Time to get some responses! Add your brand new form to a current page, or embed it on a new one.', 'formidable' ),
			),
		);
		$steps = self::fill_step_completed_data( $steps );
		return $steps;
	}

	/**
	 * @param array $steps
	 * @return array
	 */
	private static function fill_step_completed_data( $steps ) {
		foreach ( $steps as $step_key => $step ) {
			$steps[ $step_key ]['complete'] = in_array( $step_key, self::$checklist['completed_steps'], true );
		}
		$steps['add-fields']['complete'] = true; // Remove this.
		return $steps;
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
	public static function set_checklist() {
		update_option( self::CHECKLIST_OPTION, self::$checklist, 'no' );
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

	public static function maybe_mark_styler_step_as_completed() {
		if ( in_array( 'style-form', self::$checklist['completed_steps'], true ) ) {
			return;
		}

		self::$checklist['completed_steps'][] = 'style-form';
		self::set_checklist();
	}
}
