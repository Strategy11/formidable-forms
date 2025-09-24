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
	 * @var array
	 */
	private static $checklist = array();

	/**
	 * Checklist data to pass to the view.
	 *
	 * @var string|array
	 */
	private static $completed_steps = array();

	/**
	 * Initialize hooks for Dashboard page only.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_admin_hooks() {
		self::$checklist = get_option( self::CHECKLIST_OPTION, array() );
		if ( ! empty( self::$checklist['done'] ) ) {
			return;
		}

		self::$completed_steps = array_flip( self::$checklist['completed_steps'] );

		self::maybe_mark_welcome_tour_as_seen();

		add_action( 'admin_init', __CLASS__ . '::setup_checklist_progress' );
		add_action( 'admin_footer', __CLASS__ . '::render' );
		add_filter( 'admin_body_class', __CLASS__ . '::add_admin_body_classes', 999 );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_assets', 15 );

		add_action( 'frm_after_changed_form_style', __CLASS__ . '::mark_styler_step_as_completed' );
		add_action( 'frm_after_saved_style', __CLASS__ . '::mark_styler_step_as_completed' );

		add_filter( 'frm_should_show_floating_links', '__return_false' );
	}

	/**
	 * Marks the welcome tour as seen if it hasn't been seen yet.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function maybe_mark_welcome_tour_as_seen() {
		if ( self::is_welcome_tour_seen() ) {
			return;
		}

		self::$checklist['seen'] = true;
		self::save_checklist();

		// TODO: remove this after development, for now we always show the checklist
		self::$checklist['seen'] = false;
		self::save_checklist();
	}

	/**
	 * Sets up the checklist progress.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function setup_checklist_progress() {
		$current_step = 0;

		foreach ( self::get_steps()['keys'] as $index => $step_key ) {
			$completed = isset( self::$completed_steps[ $step_key ] );

			if ( false === $completed ) {
				switch ( $step_key ) {
					case 'create-form':
						$completed = self::more_than_the_default_form_exists();
						break;
					case 'embed-form':
						$completed = self::check_for_form_embeds();
						break;
				}

				if ( $completed ) {
					self::$checklist['completed_steps'][] = $step_key;
					self::$completed_steps[ $step_key ]   = true;
				}
			}

			// Count completed steps from start until gap found.
			if ( $completed && $index === $current_step ) {
				$current_step++;
			}
		}//end foreach

		self::$checklist['current_step'] = $current_step;
		self::save_checklist();
	}

	/**
	 * Render the welcome tour elements.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function render() {
		$view_path       = FrmAppHelper::plugin_path() . '/classes/views/welcome-tour/';
		$current_form_id = FrmAppHelper::simple_get( 'id', 'absint', 0 );

		$active_step = self::get_active_step();
		$completed   = 'completed' === $active_step;

		$urls = array(
			'docs'                      => self::make_tracked_url( 'https://formidableforms.com/knowledgebase/' ),
			// Setup email notifications would go to the actions & notifications area
			'setup_email_notifications' => admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $current_form_id . '&t=email_settings' ),
			// Customize success message would do the same, ideally scrolling down to the message
			'customize_success_message' => admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $current_form_id . '&t=email_settings' ),
			// Manage form entries would go to the "entries" area for all forms.
			'manage_form_entries'       => admin_url( 'admin.php?page=formidable-entries' ),
			// Explore Integrations would take them to the add-ons tab
			'explore_integrations'      => admin_url( 'admin.php?page=formidable-addons' ),
		);

		include $view_path . 'index.php';
	}

	/**
	 * Checks if the welcome tour has been seen.
	 *
	 * @since x.x
	 *
	 * @return bool True if the welcome tour has been seen, false otherwise.
	 */
	private static function is_welcome_tour_seen() {
		return FrmDashboardController::is_dashboard_page() && ! empty( self::$checklist['seen'] );
	}

	/**
	 * Gets the checklist steps.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_steps() {
		$steps = array(
			'create-form' => array(
				'title'       => __( 'Create your first form', 'formidable' ),
				'description' => __( 'Start from scratch or jump in with one of our ready-to-use templates.', 'formidable' ),
			),
			'add-fields'  => array(
				'title'       => __( 'Add fields to your form', 'formidable' ),
				'description' => __( 'Click or drag fields from the left to add them to your form. Edit and/or delete them as needed.', 'formidable' ),
			),
			'style-form'  => array(
				'title'       => __( 'Style your form', 'formidable' ),
				'description' => __( 'Our default style looks great, but feel free to modify it! Change the color, font size, spacing, or whatever else you\'d like.', 'formidable' ),
			),
			'embed-form'  => array(
				'title'       => __( 'Embed in a page', 'formidable' ),
				'description' => __( 'Time to get some responses! Add your brand new form to a current page, or embed it on a new one.', 'formidable' ),
			),
		);

		$steps_keys = array_keys( $steps );

		return array(
			'keys'  => $steps_keys,
			'steps' => self::fill_step_completed_data( $steps, $steps_keys ),
		);
	}

	/**
	 * Fills the steps with the completed data.
	 *
	 * @since x.x
	 *
	 * @param array $steps The steps to fill.
	 * @param array $steps_keys The steps keys.
	 * @return array The steps with the completed data.
	 */
	private static function fill_step_completed_data( $steps, $steps_keys ) {
		return array_map(
			function ( $step, $step_key ) {
				$step['complete'] = isset( self::$completed_steps[ $step_key ] );
				return $step;
			},
			$steps,
			$steps_keys
		);
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
	 * Checks if there are form embeds.
	 *
	 * @since x.x
	 *
	 * @return bool True if there are form embeds, false otherwise.
	 */
	private static function check_for_form_embeds() {
		global $wpdb;
		$result = $wpdb->get_var( "SELECT 1 FROM {$wpdb->posts} WHERE post_content LIKE '%[formidable %' LIMIT 1" );
		return '1' === $result;
	}

	/**
	 * Get the Welcome Tour JS variables as an array.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_js_variables() {
		$current_form_id = FrmAppHelper::simple_get( 'id', 'absint', 0 );

		return array(
			'IS_WELCOME_TOUR_SEEN'          => self::is_welcome_tour_seen(),
			'i18n'                          => array(
				'CHECKLIST_HEADER_TITLE'                => __( 'Formidable Checklist', 'formidable' ),
				'CONGRATULATIONS_TEXT'                  => __( 'Congratulations! 🎉', 'formidable' ),
				'COMPLETED_MAIN_MESSAGE'                => __( 'Setup is complete and your form is ready to use. Thank you for building with Formidable Forms!', 'formidable' ),
				'WHATS_NEXT_TEXT'                       => __( 'What\'s next for you?', 'formidable' ),
				// translators: %s is the link to the documentation.
				'DOCS_MESSAGE'                          => __( 'Check %s to learn more.', 'formidable' ),
				'DOCS_LINK_TEXT'                        => __( 'Docs & Support', 'formidable' ),
				'SETUP_EMAIL_NOTIFICATIONS_BUTTON_TEXT' => __( 'Setup email notifications', 'formidable' ),
				'CUSTOMIZE_SUCCESS_MESSAGE_BUTTON_TEXT' => __( 'Customize success message', 'formidable' ),
				'MANAGE_FORM_ENTRIES_BUTTON_TEXT'       => __( 'Manage form entries', 'formidable' ),
				'EXPLORE_INTEGRATIONS_BUTTON_TEXT'      => __( 'Explore integrations', 'formidable' ),
			),
			'PROGRESS_BAR_PERCENT'          => self::get_welcome_tour_progress_bar_percent(),
			'CHECKLIST_STEPS'               => self::get_steps()['steps'],
			'TOUR_URL'                      => admin_url( 'admin.php?page=formidable-form-templates' ),
			'DOCS_URL'                      => 'https://formidableforms.com/knowledgebase/',
			'CHECKLIST_ACTIVE_STEP'         => self::get_active_step(),
			// Setup email notifications would go to the actions & notifications area
			'SETUP_EMAIL_NOTIFICATIONS_URL' => admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $current_form_id . '&t=email_settings' ),
			// Customize success message would do the same, ideally scrolling down to the message
			'CUSTOMIZE_SUCCESS_MESSAGE_URL' => admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $current_form_id . '&t=email_settings' ),
			// Manage form entries would go to the "entries" area for all forms.
			'MANAGE_FORM_ENTRIES_URL'       => admin_url( 'admin.php?page=formidable-entries' ),
			// Explore Integrations would take them to the add-ons tab
			'EXPLORE_INTEGRATIONS_URL'      => admin_url( 'admin.php?page=formidable-addons' ),
		);
	}

	/**
	 * Get the active step.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_active_step() {
		return empty( self::$checklist['current_step'] ) ? 'create-form' : self::$checklist['current_step'];
	}

	/**
	 * Get the Welcome Tour progress bar percentage.
	 *
	 * @since x.x
	 *
	 * @return int
	 */
	private static function get_welcome_tour_progress_bar_percent() {
		if ( empty( self::$checklist['current_step'] ) ) {
			return 0;
		}

		$percent = self::$checklist['current_step'] / count( self::get_steps()['keys'] ) * 100;

		return (int) $percent;
	}

	/**
	 * Mark the styler step as completed.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function mark_styler_step_as_completed() {
		if ( isset( self::$completed_steps['style-form'] ) ) {
			return;
		}

		self::$checklist['completed_steps'][] = 'style-form';
		self::$completed_steps['style-form']  = true;

		self::save_checklist();
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
	 * Saves the checklist data.
	 *
	 * @since x.x
	 *
	 * @param array|null $checklist The checklist data to set.
	 */
	public static function save_checklist( $checklist = null ) {
		update_option( self::CHECKLIST_OPTION, $checklist ?? self::$checklist, 'no' );
	}

	/**
	 * Build a tracked URL with UTM parameters and affiliate tracking.
	 *
	 * @since x.x
	 *
	 * @param string $url The base URL to process.
	 * @return string The processed URL with UTM parameters and affiliate tracking.
	 */
	private static function make_tracked_url( $url ) {
		$utm_params = array(
			'utm_source'   => 'WordPress',
			'utm_medium'   => 'welcome-tour',
			'utm_campaign' => 'liteplugin',
		);

		return FrmAppHelper::make_affiliate_url( add_query_arg( $utm_params, $url ) );
	}
}
