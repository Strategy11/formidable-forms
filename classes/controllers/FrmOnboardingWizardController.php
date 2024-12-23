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
	 * Used when activating a single plugin.
	 *
	 * @var string
	 */
	const TRANSIENT_VALUE = 'formidable-welcome';

	/**
	 * Transient value associated with the redirection to the Onboarding Wizard page.
	 * Used when activating multiple plugins at once.
	 *
	 * @var string
	 */
	const TRANSIENT_MULTI_VALUE = 'formidable-welcome-multi';

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
	const INITIAL_STEP = 'consent-tracking';

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
	 * On single activation, we will redirect immediately.
	 * When activating multiple plugins, the redirect is delayed until a Formidable page is loaded.
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

		if ( self::has_onboarding_been_skipped() || FrmAppHelper::pro_is_connected() ) {
			return;
		}

		$transient_value = get_transient( self::TRANSIENT_NAME );
		if ( ! in_array( $transient_value, array( self::TRANSIENT_VALUE, self::TRANSIENT_MULTI_VALUE ), true ) ) {
			return;
		}

		if ( isset( $_GET['activate-multi'] ) ) {
			/**
			 * $_GET['activate-multi'] is set after activating multiple plugins.
			 * In this case, change the transient value so we know for future checks.
			 */
			set_transient( self::TRANSIENT_NAME, self::TRANSIENT_MULTI_VALUE, 60 );
			return;
		}

		if ( self::TRANSIENT_MULTI_VALUE === $transient_value && ! FrmAppHelper::is_formidable_admin() ) {
			// For multi-activations we want to only redirect when a user loads a Formidable page.
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
			// Dismiss the onboarding wizard message so it stops appearing after it is clicked.
			$message = new FrmInbox();
			$message->dismiss( 'onboarding_wizard' );

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
			'consent-tracking' => 'steps/consent-tracking-step.php',
			'install-addons'   => 'steps/install-addons-step.php',
			'success'          => 'steps/success-step.php',
			'unsuccessful'     => 'steps/unsuccessful-step.php',
		);

		include $view_path . 'index.php';
	}

	/**
	 * Handle AJAX request to setup the "Never miss an important update" step.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public static function ajax_consent_tracking() {
		// Check permission and nonce.
		FrmAppHelper::permission_check( self::REQUIRED_CAPABILITY );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		// Update Settings.
		$frm_settings = FrmAppHelper::get_settings();
		$frm_settings->update_setting( 'tracking', true, 'rest_sanitize_boolean' );

		// Remove the 'FrmProSettingsController::store' action to avoid PHP errors during AJAX call.
		remove_action( 'frm_store_settings', 'FrmProSettingsController::store' );
		$frm_settings->store();

		self::subscribe_to_active_campaign();

		// Send response.
		wp_send_json_success();
	}

	/**
	 * When the user consents to receiving news of updates, subscribe their email to ActiveCampaign.
	 *
	 * @since 6.16
	 *
	 * @return void
	 */
	private static function subscribe_to_active_campaign() {
		$user = wp_get_current_user();
		if ( empty( $user->user_email ) ) {
			return;
		}

		if ( ! self::should_send_email_to_active_campaign( $user->user_email ) ) {
			return;
		}

		$user_id    = $user->ID;
		$first_name = get_user_meta( $user_id, 'first_name', true );
		$last_name  = get_user_meta( $user_id, 'last_name', true );

		wp_remote_post(
			'https://sandbox.formidableforms.com/api/wp-admin/admin-ajax.php?action=frm_forms_preview&form=subscribe-onboarding',
			array(
				'body' => http_build_query(
					array(
						'form_key'      => 'subscribe-onboarding',
						'frm_action'    => 'create',
						'form_id'       => 5,
						'item_key'      => '',
						'item_meta[0]'  => '',
						'item_meta[15]' => $user->user_email,
						'item_meta[17]' => 'Source - FF Lite Plugin Onboarding',
						'item_meta[18]' => is_string( $first_name ) ? $first_name : '',
						'item_meta[19]' => is_string( $last_name ) ? $last_name : '',
					)
				),
			)
		);
	}

	/**
	 * Try to skip any fake emails.
	 *
	 * @since 6.16
	 *
	 * @param string $email
	 * @return bool
	 */
	private static function should_send_email_to_active_campaign( $email ) {
		$substrings = array(
			'@wpengine.local',
			'@example.com',
			'@localhost',
			'@local.dev',
			'@local.test',
			'test@gmail.com',
			'admin@gmail.com',
			
		);
		foreach ( $substrings as $substring ) {
			if ( false !== strpos( $email, $substring ) ) {
				return false;
			}
		}
		return true;
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
			'INITIAL_STEP' => self::INITIAL_STEP,
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
		$plugins          = get_plugins();

		// Base add-ons always included.
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

		// Add-ons included when Pro is not installed.
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

		// SMTP add-on if wp_mail_smtp is not installed.
		if ( ! function_exists( 'wp_mail_smtp' ) ) {
			$wp_mail_smtp_plugin       = 'wp-mail-smtp/wp_mail_smtp.php';
			$is_installed_wp_mail_smtp = array_key_exists( $wp_mail_smtp_plugin, $plugins );

			self::$available_addons['wp-mail-smtp'] = array(
				'title'        => esc_html__( 'SMTP', 'formidable' ),
				'rel'          => $is_installed_wp_mail_smtp ? $wp_mail_smtp_plugin : 'wp-mail-smtp',
				'is-checked'   => false,
				'is-vendor'    => true,
				'is-installed' => $is_installed_wp_mail_smtp,
				'help-text'    => esc_html__( 'Improve email deliverability by routing WordPress emails through SMTP.', 'formidable' ),
			);
		}

		// Add-ons available when Pro is installed.
		if ( $pro_is_installed ) {
			$available_pro_addons = array(
				'formidable-views'        => array(
					'addon_key'   => 'views',
					'title'       => __( 'Views', 'formidable' ),
					'plugin_file' => 'formidable-views/formidable-views.php',
				),
				'formidable-mailchimp'    => array(
					'addon_key'   => 'mailchimp',
					'title'       => __( 'Mailchimp', 'formidable' ),
					'plugin_file' => 'formidable-mailchimp/formidable-mailchimp.php',
				),
				'formidable-registration' => array(
					'addon_key'   => 'registration',
					'title'       => __( 'User Registration', 'formidable' ),
					'plugin_file' => 'formidable-registration/formidable-registration.php',
				),
				'formidable-api'          => array(
					'addon_key'   => 'api',
					'title'       => __( 'Form Rest API', 'formidable' ),
					'plugin_file' => 'formidable-api/formidable-api.php',
				),
				'formidable-signature'    => array(
					'addon_key'   => 'signature',
					'title'       => __( 'Signature Forms', 'formidable' ),
					'plugin_file' => 'formidable-signature/signature.php',
				),
			);

			// Include ACF Forms add-on if ACF is installed.
			if ( class_exists( 'ACF' ) ) {
				$available_pro_addons['formidable-acf'] = array(
					'addon_key'   => 'acf',
					'title'       => __( 'ACF Forms', 'formidable' ),
					'plugin_file' => 'formidable-acf/formidable-acf.php',
				);
			}

			foreach ( $available_pro_addons as $key => $data ) {
				$addon       = FrmAddonsController::get_addon( $data['addon_key'] );
				$plugin_file = $data['plugin_file'];

				if ( ! is_plugin_active( $plugin_file ) && isset( $addon['url'] ) ) {
					$is_installed = array_key_exists( $plugin_file, $plugins );

					self::$available_addons[ $key ] = array(
						'title'        => $data['title'],
						'rel'          => $is_installed ? $plugin_file : $addon['url'],
						'is-checked'   => false,
						'is-installed' => $is_installed,
						'help-text'    => $addon['excerpt'],
					);
				}
			}
		}//end if

		// Gravity Forms Migrator add-on.
		$gravity_forms_plugin = 'formidable-gravity-forms-importer/formidable-gravity-forms-importer.php';
		if ( class_exists( 'GFForms' ) && ! is_plugin_active( $gravity_forms_plugin ) ) {
			$is_installed_gravity_forms = array_key_exists( $gravity_forms_plugin, $plugins );

			self::$available_addons['formidable-gravity-forms-importer'] = array(
				'title'        => esc_html__( 'Gravity Forms Migrator', 'formidable' ),
				'rel'          => $is_installed_gravity_forms ? $gravity_forms_plugin : 'formidable-gravity-forms-importer',
				'is-checked'   => false,
				'is-vendor'    => true,
				'is-installed' => $is_installed_gravity_forms,
				'help-text'    => esc_html__( 'Easily migrate your forms from Gravity Forms to Formidable.', 'formidable' ),
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

	/**
	 * Validates if the Onboarding Wizard page is being displayed.
	 *
	 * @since 6.9
	 * @deprecated 6.16
	 *
	 * @return bool True if the Onboarding Wizard page is displayed, false otherwise.
	 */
	public static function is_onboarding_wizard_displayed() {
		_deprecated_function( __METHOD__, '6.16' );
		return get_transient( self::TRANSIENT_NAME ) === self::TRANSIENT_VALUE;
	}
}
