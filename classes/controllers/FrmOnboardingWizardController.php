<?php
/**
 * Onboarding Wizard Controller class.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Handles the Onboarding Wizard page in the admin area.
 *
 * @since x.x
 */
class FrmOnboardingWizardController {

	/**
	 * The slug of the Onboarding Wizard page.
	 *
	 * @var string PAGE_SLUG Unique identifier for the "Onboarding Wizard" page.
	 */
	const PAGE_SLUG = 'formidable-onboarding-wizard';

	/**
	 * The script handle.
	 *
	 * @var string SCRIPT_HANDLE Unique handle for the admin script.
	 */
	const SCRIPT_HANDLE = 'frm-onboarding-wizard';

	/**
	 * The required user capability to view the Onboarding Wizard page.
	 *
	 * @var string REQUIRED_CAPABILITY The capability required to access the Onboarding Wizard.
	 */
	const REQUIRED_CAPABILITY = 'frm_view_forms';

	/**
	 * Transient name used for managing redirection to the Onboarding Wizard page.
	 *
	 * @var string TRANSIENT_NAME Transient name for redirection management.
	 */
	const TRANSIENT_NAME = 'frm_activation_redirect';

	/**
	 * Transient value associated with the redirection to the Onboarding Wizard page.
	 *
	 * @var string TRANSIENT_VALUE Transient value for redirection management.
	 */
	const TRANSIENT_VALUE = 'formidable-welcome';

	/**
	 * Option name for storing the redirect status for the Onboarding Wizard page.
	 *
	 * @var string REDIRECT_STATUS_OPTION Option name for redirect status.
	 */
	const REDIRECT_STATUS_OPTION = 'frm_welcome_redirect';

	/**
	 * The type of license received from the API.
	 *
	 * @var string $license_type License type received from the API.
	 */
	private static $license_type = '';

	/**
	 * Path to views.
	 *
	 * @var string $view_path Path to the Onboarding Wizard views.
	 */
	private static $view_path = '';

	/**
	 * Path to views.
	 *
	 * @var string $view_path Path to the Onboarding Wizard views.
	 */
	private static $page_url = '';

	/**
	 * Upgrade URL.
	 *
	 * @var string $upgrade_link URL for upgrading accounts.
	 */
	private static $upgrade_link = '';

	/**
	 * Renew URL.
	 *
	 * @var string $renew_link URL for renewing accounts.
	 */
	private static $renew_link = '';

	/**
	 * Initialize hooks for template page only.
	 *
	 * @since x.x
	 */
	public static function load_admin_hooks() {
		self::initialize_properties();

		add_action( 'admin_init', __CLASS__ . '::do_admin_redirects' );

		if ( ! self::is_onboarding_wizard_page() ) {
			return;
		}

		add_action( 'admin_menu', __CLASS__ . '::menu' );
		add_action( 'admin_head', __CLASS__ . '::remove_menu' );
		add_filter( 'admin_body_class', __CLASS__ . '::add_admin_body_classes', 999 );
		add_filter( 'frm_show_footer_links', '__return_false' );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_assets' );
	}

	/**
	 * Initializes class properties with essential values for operation.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function initialize_properties() {
		self::$view_path    = FrmAppHelper::plugin_path() . '/classes/views/onboarding-wizard/';
		self::$page_url     = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		self::$license_type = FrmAddonsController::license_type();

		self::$upgrade_link = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'onboarding-wizard',
				'content' => 'upgrade',
			)
		);

		self::$renew_link = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'onboarding-wizard',
				'content' => 'renew',
			)
		);
	}

	/**
	 * Performs a safe (local) redirect to the welcome screen
	 * when the plugin is activated
	 *
	 * @return void
	 */
	public static function do_admin_redirects() {
		$current_page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );

		// Prevent endless loop.
		if ( $current_page === self::PAGE_SLUG ) {
			return;
		}

		// Only do this for single site installs.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		// Check if we should consider redirection.
		if ( ! self::is_onboarding_wizard_displayed() ) {
			return;
		}

		set_transient( self::TRANSIENT_NAME, 'no', 60 );

		// Prevent redirect with every activation.
		if ( self::has_already_redirected() ) {
			return;
		}

		// Initial install.
		wp_safe_redirect( esc_url( self::$page_url ) );
		exit;
	}

	/**
	 * Add Onboarding Wizard menu item to sidebar and define index page.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function menu() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$label = __( 'Onboardin Wizard', 'formidable' );

		add_submenu_page(
			'formidable',
			'Formidable | ' . $label,
			$label,
			self::REQUIRED_CAPABILITY,
			self::PAGE_SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Remove the Onboarding Wizard submenu page from the formidable parent menu
	 * since it is not necessary to show that link there.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function remove_menu() {
		remove_submenu_page( 'formidable', self::PAGE_SLUG );
	}

	/**
	 * Adds custom classes to the existing string of admin body classes.
	 *
	 * This function appends a custom class to the existing admin body classes, enabling full-screen mode for the admin interface.
	 *
	 * @since x.x
	 *
	 * @param string $classes Existing body classes.
	 * @return string Updated list of body classes, including the newly added classes.
	 */
	public static function add_admin_body_classes( $classes ) {
		return $classes . ' frm-admin-full-screen';
	}

	/**
	 * Renders the Onboarding Wizard page in the WordPress admin area.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function render() {
		// Include SVG images for icons.
		FrmAppHelper::include_svg();

		$view_path    = self::get_view_path();
		$upgrade_link = self::get_upgrade_link();
		$renew_link   = self::get_renew_link();
		$license_type = self::get_license_type();
		$user         = wp_get_current_user();

		// Note: Add step parts in onrder.
		$step_parts = array(
			'welcome'        => 'steps/welcome-step.php',
			'email'          => 'steps/email-step.php',
			'install-addons' => 'steps/install-addons-step.php',
			'success'        => 'steps/success-step.php',
		);

		// Render the view.
		include $view_path . 'index.php';
	}

	/**
	 * Enqueues the Onboarding Wizard page scripts and styles.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		$plugin_url      = FrmAppHelper::plugin_url();
		$version         = FrmAppHelper::plugin_version();
		$js_dependencies = array(
			'wp-i18n',
			// This prevents a console error "wp.hooks is undefined" in WP versions older than 5.7.
			'wp-hooks',
			'formidable_dom',
		);

		// Enqueue styles that needed.
		wp_enqueue_style( 'formidable-admin' );
		wp_enqueue_style( 'formidable-grids' );

		// Register and enqueue Onboarding Wizard style.
		wp_register_style( self::SCRIPT_HANDLE, $plugin_url . '/css/admin/onboarding-wizard.css', array(), $version );
		wp_enqueue_style( self::SCRIPT_HANDLE );

		// Register and enqueue Onboarding Wizard script.
		wp_register_script( self::SCRIPT_HANDLE, $plugin_url . '/js/onboarding-wizard.js', $js_dependencies, $version, true );
		wp_localize_script( self::SCRIPT_HANDLE, 'frmOnboardingWizardVars', self::get_js_variables() );
		wp_enqueue_script( self::SCRIPT_HANDLE );

		/**
		 * Fires after the Onboarding Wizard enqueue assets.
		 *
		 * @since x.x
		 */
		do_action( 'frm_onboarding_wizard_enqueue_assets' );

		self::dequeue_scripts();
	}

	/**
	 * Get the Onboarding Wizard JS variables as an array.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_js_variables() {
		$js_variables = array(
			'upgradeLink' => self::$upgrade_link,
		);

		/**
		 * Filters `js_variables` passed to the Onboarding Wizard.
		 *
		 * @since x.x
		 *
		 * @param array $js_variables Array of js_variables passed to the Onboarding Wizard.
		 */
		return apply_filters( 'frm_onboarding_wizard_js_variables', $js_variables );
	}

	/**
	 * Dequeue scripts and styles on the Onboarding Wizard.
	 *
	 * Avoid extra scripts loading on the Onboarding Wizard page that aren't needed.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function dequeue_scripts() {
		wp_dequeue_script( 'frm-surveys-admin' );
		wp_dequeue_script( 'frm-quizzes-form-action' );
	}

	/**
	 * Check if the current page is the Onboarding Wizard page.
	 *
	 * @since x.x
	 *
	 * @return bool True if the current page is the Onboarding Wizard page, false otherwise.
	 */
	public static function is_onboarding_wizard_page() {
		return FrmAppHelper::is_admin_page( self::PAGE_SLUG );
	}

	/**
	 * Validates if the Onboarding Wizard page is being displayed.
	 *
	 * @since x.x
	 *
	 * @return bool True if the Onboarding Wizard page is displayed, false otherwise.
	 */
	public static function is_onboarding_wizard_displayed() {
		return get_transient( self::TRANSIENT_NAME ) === self::TRANSIENT_VALUE;
	}

	/**
	 * Checks if the plugin has already performed a redirect to avoid repeated redirections.
	 *
	 * @return bool Returns true if already redirected, otherwise false.
	 */
	private static function has_already_redirected() {
		if ( get_option( self::REDIRECT_STATUS_OPTION ) ) {
			return true;
		}

		update_option( self::REDIRECT_STATUS_OPTION, FrmAppHelper::plugin_version(), 'no' );
		return false;
	}

	/**
	 * Get the license type.
	 *
	 * @since x.x
	 *
	 * @return string The license type.
	 */
	public static function get_license_type() {
		return self::$license_type;
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
	 * Get the path to the Onboarding Wizard views.
	 *
	 * @since x.x
	 *
	 * @return string Path to views.
	 */
	public static function get_page_url() {
		return self::$page_url;
	}

	/**
	 * Get the upgrade link.
	 *
	 * @since x.x
	 *
	 * @return string URL for upgrading accounts.
	 */
	public static function get_upgrade_link() {
		return self::$upgrade_link;
	}

	/**
	 * Get the renewal link.
	 *
	 * @since x.x
	 *
	 * @return string URL for renewing accounts.
	 */
	public static function get_renew_link() {
		return self::$renew_link;
	}
}
