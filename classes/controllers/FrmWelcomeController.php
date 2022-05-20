<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmWelcomeController {

	public static $menu_slug   = 'formidable-welcome';

	public static $option_name = 'frm_activation_redirect';

	private static $last_redirect = 'frm_welcome_redirect';

	/**
	 * Register all of the hooks related to the welcome screen functionality
	 *
	 * @access   public
	 */
	public static function load_hooks() {
		add_action( 'admin_init', __CLASS__ . '::redirect' );

		if ( ! FrmAppHelper::is_admin_page( self::$menu_slug ) ) {
			return;
		}

		add_action( 'admin_menu', __CLASS__ . '::screen_page' );
		add_action( 'admin_head', __CLASS__ . '::remove_menu' );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_styles' );
	}

	/**
	 * Performs a safe (local) redirect to the welcome screen
	 * when the plugin is activated
	 *
	 * @return void
	 */
	public static function redirect() {
		$current_page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		if ( $current_page === self::$menu_slug ) {
			// Prevent endless loop.
			return;
		}

		// Only do this for single site installs.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		// Check if we should consider redirection.
		if ( ! self::is_welcome_screen() ) {
			return;
		}

		set_transient( self::$option_name, 'no', 60 );

		// Prevent redirect with every activation.
		if ( self::already_redirected() ) {
			return;
		}

		// Initial install.
		wp_safe_redirect( esc_url( self::settings_link() ) );
		exit;
	}

	/**
	 * Don't redirect every time the plugin is activated.
	 */
	private static function already_redirected() {
		$last_redirect = get_option( self::$last_redirect );
		if ( $last_redirect ) {
			return true;
		}

		update_option( self::$last_redirect, FrmAppHelper::plugin_version(), 'no' );
		return false;
	}

	/**
	 * Add a submenu welcome screen for the formidable parent menu
	 *
	 * @return void
	 */
	public static function screen_page() {
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Welcome Screen', 'formidable' ), __( 'Welcome Screen', 'formidable' ), 'read', self::$menu_slug, __CLASS__ . '::screen_content' );
	}

	/**
	 * Include html content for the welcome screem
	 *
	 * @return void
	 */
	public static function screen_content() {
		FrmAppHelper::include_svg();
		include FrmAppHelper::plugin_path() . '/classes/views/welcome/show.php';
	}

	/**
	 * Remove the welcome screen submenu page from the formidable parent menu
	 * since it is not necessary to show that link there
	 *
	 * @return void
	 */
	public static function remove_menu() {
		remove_submenu_page( 'formidable', self::$menu_slug );
	}

	/**
	 * Register the stylesheets for the welcome screen.
	 *
	 * @return void
	 */
	public static function enqueue_styles() {
		$version = FrmAppHelper::plugin_version();
		wp_enqueue_style( 'frm-welcome-screen', FrmAppHelper::plugin_url() . '/css/welcome_screen.css', array( 'formidable-admin' ), $version );
	}

	/**
	 * Helps to confirm if the user is currently on the welcome screen
	 *
	 * @return bool
	 */
	public static function is_welcome_screen() {
		$to_redirect = get_transient( self::$option_name );
		return $to_redirect === self::$menu_slug;
	}

	/**
	 * Build the admin URL link for the welcome screen
	 *
	 * @return string
	 */
	public static function settings_link() {
		return admin_url( 'admin.php?page=' . self::$menu_slug );
	}

	public static function upgrade_to_pro_button() {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			?>
				<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'settings-license' ) ); ?>" class="button-secondary frm-button-secondary" target="_blank" rel="nofollow noopener">
					<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
				</a>
			<?php
		}
	}

	public static function maybe_show_license_box() {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			FrmSettingsController::license_box();
		}
	}

	public static function maybe_show_conditional_action_button( $plugin, $upgrade_link_args ) {
		$is_installed = is_callable( 'FrmProAppHelper::views_is_installed' ) && FrmProAppHelper::views_is_installed();
		if ( ! $is_installed ) {
			FrmAddonsController::conditional_action_button( $plugin, $upgrade_link_args );
		}
	}
}
