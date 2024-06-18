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
 * @since 6.9
 */
class FrmOnboardingWizardController {

	/**
	 * The slug of the Onboarding Wizard page.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'formidable-onboarding-wizard';

	/**
	 * The script handle.
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'frm-onboarding-wizard';

	/**
	 * The required user capability to view the Onboarding Wizard page.
	 *
	 * @var string
	 */
	const REQUIRED_CAPABILITY = 'frm_view_forms';

	/**
	 * Transient name used for managing redirection to the Onboarding Wizard page.
	 *
	 * @var string
	 */
	const TRANSIENT_NAME = 'frm_activation_redirect';

	/**
	 * Transient value associated with the redirection to the Onboarding Wizard page.
	 *
	 * @var string
	 */
	const TRANSIENT_VALUE = 'formidable-welcome';

	/**
	 * Option name for storing the redirect status for the Onboarding Wizard page.
	 *
	 * @var string
	 */
	const REDIRECT_STATUS_OPTION = 'frm_welcome_redirect';

	/**
	 * Option name for tracking if the onboarding wizard was skipped.
	 *
	 * @var string
	 */
	const ONBOARDING_SKIPPED_OPTION = 'frm_onboarding_skipped';

	/**
	 * Defines the initial step for redirection within the application flow.
	 *
	 * @var string
	 */
	const INITIAL_STEP = 'welcome';

	/**
	 * Option name to store usage data.
	 *
	 * @var string
	 */
	const USAGE_DATA_OPTION = 'frm_onboarding_usage_data';

	/**
	 * Holds the URL to access the Onboarding Wizard's page.
	 *
	 * @var string
	 */
	private static $page_url = '';

	/**
	 * Holds a list of add-ons available for installation.
	 *
	 * @var array
	 */
	private static $available_addons = array();

	/**
	 * Path to views.
	 *
	 * @var string
	 */
	private static $view_path = '';

	/**
	 * Upgrade URL.
	 *
	 * @var string
	 */
	private static $upgrade_link = '';

	/**
	 * Initialize hooks for template page only.
	 *
	 * @since 6.9
	 */
	public static function load_admin_hooks() {
		self::set_page_url();
		add_action( 'admin_init', __CLASS__ . '::do_admin_redirects' );

		if ( self::has_onboarding_been_skipped() ) {
			add_filter( 'option_frm_inbox', __CLASS__ . '::add_wizard_to_floating_links' );
		}

		// Load page if admin page is Onboarding Wizard.
		self::maybe_load_page();
	}

	/**
	 * Performs a safe redirect to the welcome screen when the plugin is activated.
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
		if ( is_network_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			self::mark_onboarding_as_skipped();
			return;
		}

		// Check if we should consider redirection.
		if ( ! FrmAppHelper::is_formidable_admin() || ! self::is_onboarding_wizard_displayed() || self::has_onboarding_been_skipped() || FrmAppHelper::pro_is_connected() ) {
			return;
		}

		set_transient( self::TRANSIENT_NAME, 'no', 60 );

		// Prevent redirect with every activation.
		if ( self::has_already_redirected() ) {
			return;
		}

		// Redirect to the onboarding wizard's initial step.
		$page_url = add_query_arg( 'step', self::INITIAL_STEP, self::$page_url );
		if ( wp_safe_redirect( esc_url_raw( $page_url ) ) ) {
			exit;
		}
	}

	/**
	 * Initializes the Onboarding Wizard setup if on its designated admin page.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public static function maybe_load_page() {
		if ( self::is_onboarding_wizard_page() ) {
			add_action( 'admin_menu', __CLASS__ . '::menu', 99 );
			add_action( 'admin_init', __CLASS__ . '::assign_properties' );
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_assets', 15 );
			add_action( 'admin_head', __CLASS__ . '::remove_menu' );

			add_filter( 'admin_body_class', __CLASS__ . '::add_admin_body_classes', 999 );
			add_filter( 'frm_show_footer_links', '__return_false' );
		}
	}

	/**
	 * Initializes class properties with essential values for operation.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public static function assign_properties() {
		self::$view_path = FrmAppHelper::plugin_path() . '/classes/views/onboarding-wizard/';

		self::$upgrade_link = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'onboarding-wizard',
				'content' => 'upgrade',
			)
		);

		self::set_available_addons();
	}

	/**
	 * Add Onboarding Wizard menu item to sidebar and define index page.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public static function menu() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$label = __( 'Onboarding Wizard', 'formidable' );

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
	 * Renders the Onboarding Wizard page in the WordPress admin area.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public static function render() {
		if ( self::has_onboarding_been_skipped() ) {
			delete_option( self::ONBOARDING_SKIPPED_OPTION );
			self::has_already_redirected();
		}

		// Include SVG images for icons.
		FrmAppHelper::include_svg();

		$view_path        = self::get_view_path();
		$available_addons = self::get_available_addons();
		$upgrade_link     = self::get_upgrade_link();
		$addons_count     = FrmAddonsController::get_addons_count();
		$license_key      = base64_decode( rawurldecode( FrmAppHelper::get_param( 'key', '', 'request', 'sanitize_text_field' ) ) );
		$pro_is_installed = FrmAppHelper::pro_is_installed();

		// Note: Add step parts in order.
		$step_parts = array(
			'welcome'                => 'steps/welcome-step.php',
			'install-formidable-pro' => 'steps/install-formidable-pro-step.php',
			'license-management'     => 'steps/license-management-step.php',
			'default-email-address'  => 'steps/default-email-address-step.php',
			'install-addons'         => 'steps/install-addons-step.php',
			'success'                => 'steps/success-step.php',
		);

		include $view_path . 'index.php';
	}

	/**
	 * Handle AJAX request to setup the "Default Email Address" step.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public static function ajax_setup_email_step() {
		// Check permission and nonce.
		FrmAppHelper::permission_check( self::REQUIRED_CAPABILITY );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		// Get posted data.
		$default_email   = FrmAppHelper::get_post_param( 'default_email', '', 'sanitize_text_field' );
		$allows_tracking = FrmAppHelper::get_post_param( 'allows_tracking', '', 'rest_sanitize_boolean' );
		$summary_emails  = FrmAppHelper::get_post_param( 'summary_emails', '', 'rest_sanitize_boolean' );

		// Update Settings.
		$frm_settings = FrmAppHelper::get_settings();
		$frm_settings->update_setting( 'default_email', $default_email, 'sanitize_text_field' );
		$frm_settings->update_setting( 'tracking', $allows_tracking, 'rest_sanitize_boolean' );
		$frm_settings->update_setting( 'summary_emails', $summary_emails, 'rest_sanitize_boolean' );
		// Remove the 'FrmProSettingsController::store' action to avoid PHP errors during AJAX call.
		remove_action( 'frm_store_settings', 'FrmProSettingsController::store' );
		$frm_settings->store();

		// Send response.
		wp_send_json_success();
	}

	/**
	 * Handle AJAX request to set up usage data for the Onboarding Wizard.
	 *
	 * @since 6.9
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
			'default_email'    => 'sanitize_email',
			'is_subscribed'    => 'rest_sanitize_boolean',
			'allows_tracking'  => 'rest_sanitize_boolean',
			'summary_emails'   => 'rest_sanitize_boolean',
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
	 * Enqueues the Onboarding Wizard page scripts and styles.
	 *
	 * @since 6.9
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
		 * @since 6.9
		 */
		do_action( 'frm_onboarding_wizard_enqueue_assets' );

		FrmAppHelper::dequeue_extra_global_scripts();
	}

	/**
	 * Get the Onboarding Wizard JS variables as an array.
	 *
	 * @since 6.9
	 *
	 * @return array
	 */
	private static function get_js_variables() {
		return array(
			'INITIAL_STEP'  => self::INITIAL_STEP,
			'proIsIncluded' => FrmAppHelper::pro_is_included(),
		);
	}

	/**
	 * Remove the Onboarding Wizard submenu page from the formidable parent menu
	 * since it is not necessary to show that link there.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public static function remove_menu() {
		remove_submenu_page( 'formidable', self::PAGE_SLUG );
	}

	/**
	 * Adds custom classes to the existing string of admin body classes.
	 *
	 * The function appends a custom class to the existing admin body classes, enabling full-screen mode for the admin interface.
	 *
	 * @since 6.9
	 *
	 * @param string $classes Existing body classes.
	 * @return string Updated list of body classes, including the newly added classes.
	 */
	public static function add_admin_body_classes( $classes ) {
		return $classes . ' frm-admin-full-screen';
	}

	/**
	 * Checks if the Onboarding Wizard was skipped during the plugin's installation.
	 *
	 * @since 6.9
	 * @return bool True if the Onboarding Wizard was skipped, false otherwise.
	 */
	public static function has_onboarding_been_skipped() {
		return get_option( self::ONBOARDING_SKIPPED_OPTION, false );
	}

	/**
	 * Marks the Onboarding Wizard as skipped to prevent automatic redirects to the wizard.
	 *
	 * @since 6.9
	 * @return void
	 */
	public static function mark_onboarding_as_skipped() {
		update_option( self::ONBOARDING_SKIPPED_OPTION, true, 'no' );
	}

	/**
	 * Adds an Onboarding Wizard welcome message to the floating notifications.
	 *
	 * @since 6.9
	 *
	 * @param array $inbox_messages The array of existing inbox messages.
	 * @return array Configuration for the onboarding wizard slide-in notification.
	 */
	public static function add_wizard_to_floating_links( $inbox_messages ) {
		$message = __( 'Welcome to Formidable Forms! Click here to run the Onboarding Wizard and it will guide you through the basic settings and get you started in 2 minutes.', 'formidable' );

		return array(
			'onboarding_wizard' => array(
				'subject' => esc_html__( 'Begin With Ease!', 'formidable' ),
				'message' => esc_html( $message ),
				'slidein' => esc_html( $message ),
				'cta'     => '<a href="' . esc_url( self::$page_url ) . '" class="button-primary frm-button-primary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Begin Setup', 'formidable' ) . '</a>',
				'created' => time(),
				'key'     => 'onboarding_wizard',
			),
		);
	}

	/**
	 * Check if the current page is the Onboarding Wizard page.
	 *
	 * @since 6.9
	 *
	 * @return bool True if the current page is the Onboarding Wizard page, false otherwise.
	 */
	public static function is_onboarding_wizard_page() {
		return FrmAppHelper::is_admin_page( self::PAGE_SLUG );
	}

	/**
	 * Validates if the Onboarding Wizard page is being displayed.
	 *
	 * @since 6.9
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
	 * Get the path to the Onboarding Wizard views.
	 *
	 * @since 6.9
	 *
	 * @return string Path to views.
	 */
	public static function get_page_url() {
		return self::$page_url;
	}

	/**
	 * Set the URL to access the Onboarding Wizard's page.
	 *
	 * @return void
	 */
	private static function set_page_url() {
		self::$page_url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
	}

	/**
	 * Get the list of add-ons available for installation.
	 *
	 * @since 6.9
	 *
	 * @return array A list of add-ons.
	 */
	public static function get_available_addons() {
		return self::$available_addons;
	}

	/**
	 * Set the list of add-ons available for installation.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	private static function set_available_addons() {
		$pro_is_installed = FrmAppHelper::pro_is_installed();

		self::$available_addons['spam-protection'] = array(
			'title'       => esc_html__( 'Spam Protection', 'formidable' ),
			'is-checked'  => true,
			'is-disabled' => true,
			'help-text'   => esc_html__( 'Get anti-spam options like reCAPTCHA, hCaptcha, Akismet, Turnstile and the blocklist.', 'formidable' ),
		);
		self::$available_addons['stripe-payments'] = array(
			'title'       => esc_html__( 'Stripe Payments', 'formidable' ),
			'is-checked'  => true,
			'is-disabled' => true,
			'help-text'   => esc_html__( 'Collect donations and payments with your forms. Offer physical products, digital goods, services, and more.', 'formidable' ),
		);
		if ( ! $pro_is_installed ) {
			self::$available_addons['visual-styler'] = array(
				'title'       => esc_html__( 'Visual Styler', 'formidable' ),
				'is-checked'  => true,
				'is-disabled' => true,
				'help-text'   => esc_html__( 'Customize form appearance with an intuitive styling interface.', 'formidable' ),
			);
			self::$available_addons['save-entries']  = array(
				'title'       => esc_html__( 'Save Entries', 'formidable' ),
				'is-checked'  => true,
				'is-disabled' => true,
				'help-text'   => esc_html__( 'Save form submissions to your database for future reference and analysis.', 'formidable' ),
			);
		}
		if ( ! function_exists( 'wp_mail_smtp' ) ) {
			self::$available_addons['wp-mail-smtp'] = array(
				'title'      => esc_html__( 'SMTP', 'formidable' ),
				'rel'        => 'wp-mail-smtp',
				'is-checked' => false,
				'is-vendor'  => true,
				'help-text'  => esc_html__( 'Improve email deliverability by routing WordPress emails through SMTP.', 'formidable' ),
			);
		}
		if ( $pro_is_installed ) {
			$views_addon        = FrmAddonsController::get_addon( 'views' );
			$mailchimp_addon    = FrmAddonsController::get_addon( 'mailchimp' );
			$registration_addon = FrmAddonsController::get_addon( 'registration' );
			$api_addon          = FrmAddonsController::get_addon( 'api' );
			$acf_addon          = FrmAddonsController::get_addon( 'acf' );
			$signature_addon    = FrmAddonsController::get_addon( 'signature' );

			if ( ! is_plugin_active( 'formidable-views/formidable-views.php' ) ) {
				self::$available_addons['formidable-views'] = array(
					'title'      => esc_html__( 'Views', 'formidable' ),
					'rel'        => $views_addon['url'],
					'is-checked' => false,
					'help-text'  => $views_addon['excerpt'],
				);
			}
			if ( ! is_plugin_active( 'formidable-mailchimp/formidable-mailchimp.php' ) ) {
				self::$available_addons['formidable-mailchimp'] = array(
					'title'      => esc_html__( 'Mailchimp', 'formidable' ),
					'rel'        => $mailchimp_addon['url'],
					'is-checked' => false,
					'help-text'  => $mailchimp_addon['excerpt'],
				);
			}
			if ( ! is_plugin_active( 'formidable-registration/formidable-registration.php' ) ) {
				self::$available_addons['formidable-registration'] = array(
					'title'      => esc_html__( 'User Registration', 'formidable' ),
					'rel'        => $registration_addon['url'],
					'is-checked' => false,
					'help-text'  => $registration_addon['excerpt'],
				);
			}
			if ( ! is_plugin_active( 'formidable-api/formidable-api.php' ) ) {
				self::$available_addons['formidable-api'] = array(
					'title'      => esc_html__( 'Form Rest API', 'formidable' ),
					'rel'        => $api_addon['url'],
					'is-checked' => false,
					'help-text'  => $api_addon['excerpt'],
				);
			}
			if ( class_exists( 'ACF' ) && ! is_plugin_active( 'formidable-acf/formidable-acf.php' ) ) {
				self::$available_addons['formidable-acf'] = array(
					'title'      => esc_html__( 'ACF Forms', 'formidable' ),
					'rel'        => $acf_addon['url'],
					'is-checked' => false,
					'help-text'  => $acf_addon['excerpt'],
				);
			}
			if ( ! is_plugin_active( 'formidable-signature/signature.php' ) ) {
				self::$available_addons['formidable-signature'] = array(
					'title'      => esc_html__( 'Signature Forms', 'formidable' ),
					'rel'        => $signature_addon['url'],
					'is-checked' => false,
					'help-text'  => $signature_addon['excerpt'],
				);
			}
		}//end if
		if ( class_exists( 'GFForms' ) && ! is_plugin_active( 'formidable-gravity-forms-importer/formidable-gravity-forms-importer.php' ) ) {
			self::$available_addons['formidable-gravity-forms-importer'] = array(
				'title'      => esc_html__( 'Gravity Forms Migrator', 'formidable' ),
				'rel'        => 'formidable-gravity-forms-importer',
				'is-checked' => false,
				'is-vendor'  => true,
				'help-text'  => esc_html__( 'Easily migrate your forms from Gravity Forms to Formidable.', 'formidable' ),
			);
		}
	}

	/**
	 * Get the path to the Onboarding Wizard views.
	 *
	 * @since 6.9
	 *
	 * @return string Path to views.
	 */
	public static function get_view_path() {
		return self::$view_path;
	}

	/**
	 * Get the upgrade link.
	 *
	 * @since 6.9
	 *
	 * @return string URL for upgrading accounts.
	 */
	public static function get_upgrade_link() {
		return self::$upgrade_link;
	}

	/**
	 * Retrieves the current Onboarding Wizard usage data, returning an empty array if none exists.
	 *
	 * @since 6.9
	 *
	 * @return array Current usage data.
	 */
	public static function get_usage_data() {
		return get_option( self::USAGE_DATA_OPTION, array() );
	}
}
