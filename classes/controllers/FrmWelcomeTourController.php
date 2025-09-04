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
	 */
	public static function load_admin_hooks() {
		self::$checklist = get_option( self::CHECKLIST_OPTION, array() );

		if ( 'done' === self::$checklist ) {
			return;
		}

		if ( self::is_welcome_tour_not_seen() ) {
			self::mark_welcome_tour_as_seen();
		}

		self::maybe_check_for_completed_steps();

		// TODO: remove this after development
		self::$checklist['seen'] = false;
		self::set_checklist( self::$checklist );

		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_assets', 15 );
		add_filter( 'admin_body_class', __CLASS__ . '::add_admin_body_classes', 999 );
	}

	/**
	 * @return void
	 */
	private static function maybe_check_for_completed_steps() {
		if ( ! isset( self::$checklist['completed_steps'] ) ) {
			return;
		}

		self::$checklist['completed_steps'] = array();

		$steps     = self::get_steps();
		$step_keys = array_keys( $steps );
		foreach ( $step_keys as $step_key ) {
			$completed = false;

			switch ( $step_key ) {
				case 'create-a-form':
					$form_keys = FrmDb::get_col( 'frm_forms', array(), 'form_key' );
					if ( count( $form_keys ) > 1 ) {
						$completed = true;
					} else {
						$completed = $form_keys && ! in_array( 'contact-form', $form_keys, true );
					}
					break;

				case 'update-form':
					
					break;

				case 'first-entry':
					$entry_id = FrmDb::get_var( 'frm_items', array(), 'id' );
					if ( $entry_id ) {
						$completed = true;
					}
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
		self::set_checklist( self::$checklist );
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
				'CHECKLIST_TEXT' => __( 'Checklist', 'formidable' ),
			),
			'PROGRESS_BAR_PERCENT'   => self::get_welcome_tour_progress_bar_percent(),
			'CHECKLIST_STEPS'        => self::get_steps(),
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
			'create-a-form' => array(
				'title' => __( 'Create a Form', 'formidable' ),
			),
			'update-form' 	=> array(
				'title' => __( 'Update a Form', 'formidable' ),
			),
			'first-entry' => array(
				'title' => __( 'Create First Entry', 'formidable' ),
			),
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
}
