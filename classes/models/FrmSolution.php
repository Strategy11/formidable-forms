<?php
/**
 * Handles the installation of a solution and any dependencies.
 * This page is shown when a Formidable plugin is activated.
 *
 * @since 4.04.02
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSolution {

	protected $plugin_slug = 'formidable';

	/**
	 * Hidden welcome page slug.
	 *
	 * @since 4.04.02
	 */
	protected $page = 'formidable-getting-started';

	public function __construct( $atts = array() ) {
		if ( isset( $atts['plugin_slug'] ) ) {
			$this->plugin_slug = $atts['plugin_slug'];
		}
		add_action( 'plugins_loaded', array( $this, 'load_hooks' ) );
	}

	/**
	 * Register all WP hooks.
	 *
	 * @since 4.04.02
	 */
	public function load_hooks() {

		// If user is in admin ajax or doing cron, return.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// If user cannot manage_options, return.
		if ( ! current_user_can( 'frm_change_settings' ) || ! FrmAppHelper::is_formidable_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'register' ) );
		add_action( 'admin_head', array( $this, 'hide_menu' ) );
		add_action( 'admin_init', array( $this, 'redirect' ), 9999 );
	}

	protected function required_plugins() {
		return array();
	}

	/**
	 * Get the download URLs in order to install.
	 */
	protected function install_required_plugins() {
		$plugins = $this->required_plugins();
		if ( empty( $plugins ) ) {
			return;
		}

		foreach ( $plugins as $plugin ) {
			$install = FrmAddonsController::install_link( $plugin );

			if ( ! isset( $install['url'] ) ) {
				// TODO: User doesn't have permission to install, so show an error.
				return;
			}
			
			// See FrmAddonsController::ajax_activate_addon() and FrmAddonsController::ajax_install_addon()
		}
	}

	/**
	 * Register the pages to be used for the Welcome screen (and tabs).
	 *
	 * These pages will be removed from the Dashboard menu, so they will
	 * not actually show. Sneaky, sneaky.
	 *
	 * @since 4.04.02
	 */
	public function register() {

		// Getting started - shows after installation.
		add_dashboard_page(
			esc_html__( 'Welcome to Formidable Forms', 'formidable' ),
			esc_html__( 'Welcome to Formidable Forms', 'formidable' ),
			'frm_change_settings',
			$this->page,
			array( $this, 'output' )
		);
	}

	/**
	 * Removed the dashboard pages from the admin menu.
	 *
	 * This means the pages are still available to us, but hidden.
	 *
	 * @since 4.04.02
	 */
	public function hide_menu() {

		remove_submenu_page( 'index.php', $this->page );
	}

	/**
	 * Welcome screen redirect.
	 *
	 * This function checks if a new install or update has just occurred. If so,
	 * then we redirect the user to the appropriate page.
	 *
	 * @since 4.04.02
	 */
	public function redirect() {

		// Check if we should consider redirection.
		$to_redirect = get_transient( 'frm_activation_redirect' );
		if ( ! $to_redirect || $to_redirect !== $this->plugin_slug ) {
			return;
		}

		// If we are redirecting, clear the transient so it only happens once.
		delete_transient( 'frm_activation_redirect' );

		// Only do this for single site installs.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // WPCS: CSRF ok.
			return;
		}

		// Initial install.
		wp_safe_redirect( admin_url( 'index.php?page=' . $this->page ) );
		exit;
	}

	/**
	 * Getting Started screen. Shows after first install.
	 *
	 * @since 4.04.02
	 */
	public function output() {
		$this->header();
		$this->main_content();
		$this->footer();
	}

	protected function header() {
		$class = FrmAppHelper::pro_is_installed() ? 'pro' : 'lite';
		?>
		<div id="frm-welcome" class="upgrade_to_pro <?php echo sanitize_html_class( $class ); ?>">
			<div class="container">
				<div class="frm-logo">
					<?php
					FrmAppHelper::show_logo(
						array(
							'width'  => '125',
							'height' => '125',
						)
					);
					?>
				</div>
		<?php
	}

	/**
	 * Override me to insert different content.
	 */
	protected function main_content() {
		include( FrmAppHelper::plugin_path() . '/classes/views/shared/welcome.php' );
	}

	protected function footer() {
		?>
			</div><!-- /.container -->
		</div>
		<?php
	}
}
