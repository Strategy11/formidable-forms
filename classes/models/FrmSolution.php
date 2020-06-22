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

	protected $plugin_db_version = 1;

	/**
	 * Hidden welcome page slug.
	 *
	 * @since 4.04.02
	 */
	protected $page = 'formidable-getting-started';

	public function __construct( $atts = array() ) {
		add_action( 'plugins_loaded', array( $this, 'load_hooks' ), 50 );

		// Uncomment this line for testing:
		// set_transient( 'frm_activation_redirect', $this->plugin_slug, 30 );
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
		if ( ! current_user_can( 'frm_change_settings' ) && ! FrmAppHelper::is_formidable_admin() ) {
			return;
		}

		if ( $this->plugin_slug !== 'formidable' ) {
			$this->maybe_install();
		}

		add_action( 'admin_menu', array( $this, 'register' ) );
		add_action( 'admin_head', array( $this, 'hide_menu' ) );
		//add_action( 'admin_init', array( $this, 'redirect' ), 9999 );
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
			esc_html( $this->page_title() ),
			esc_html( $this->page_title() ),
			'frm_change_settings',
			$this->page,
			array( $this, 'output' )
		);
	}

	protected function page_title() {
		return __( 'Welcome to Formidable Forms', 'formidable' );
	}

	protected function page_description() {
		return __( 'Follow the steps below to get started.', 'formidable' );
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
		if ( ! $this->is_current_plugin() ) {
			return;
		}

		$current_page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		if ( $current_page === $this->page ) {
			// Prevent endless loop.
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
	 */
	public function output() {
		FrmAppHelper::include_svg();
		$this->css();
		$class = FrmAppHelper::pro_is_installed() ? 'pro' : 'lite';

		echo '<div id="frm-welcome" class="wrap frm-wrap frm-admin-plugin-landing upgrade_to_pro ' . sanitize_html_class( $class ) . '">';

		$this->header();
		$this->main_content();
		$this->footer();

		echo '</div>';
	}

	/**
	 * Heading section.
	 */
	protected function header() {
		$size = array(
			'height' => 90,
			'width'  => 90,
		);

		?>
		<section class="top">
			<div class="frm-smtp-logos">
				<?php FrmAppHelper::show_logo( $size ); ?>
			</div>
			<h1><?php echo esc_html( $this->page_title() ); ?></h1>
			<p><?php echo esc_html( $this->page_description() ); ?></p>
		</section>
		<?php
	}

	/**
	 * This is the welcome page content.
	 * Override me to insert different content.
	 */
	protected function main_content() {
		if ( $this->plugin_slug === 'formidable' ) {
			include( FrmAppHelper::plugin_path() . '/classes/views/shared/welcome.php' );
		} else {
			$this->license_box();
			$shown = $this->show_plugin_install();
			$step  = $shown ? 3 : 2;
			$this->show_app_install( $step );
			//$this->show_page_links();
		}
	}

	protected function footer() {
	}

	protected function get_step_data( $num ) {
		$step = array(
			'num'           => $num,
			'icon'          => 'frm_step' . $num . '_icon',
			'section_class' => '',
		);

		return $step;
	}

	/**
	 * Generate and output Connect step section HTML.
	 */
	protected function license_box() {
		global $frm_vars;

		$step = $this->get_step_data( 1 );
		$step['section_class'] = FrmAppHelper::pro_is_installed() && $frm_vars['pro_is_authorized'] ? 'grey' : '';
		$step['label'] = __( 'Connect to FormidableForms.com', 'formidable' );
		$this->step_top( $step );

		if ( $step['section_class'] === 'grey' ) { ?>
			<a href="#" class="button-primary frm-button-primary grey disabled">
				<?php esc_html_e( 'Connect an Account', 'formidable' ); ?>
			</a>
			<?php
		} else {
			FrmSettingsController::license_box();
		}

		$this->step_bottom( $step );
	}

	protected function step_top( $step ) {
		if ( strpos( $step['section_class'], 'grey' ) !== false ) {
			$step['icon'] = 'frm_step_complete_icon';
		}
		?>
		<section class="step step-install <?php echo esc_attr( $step['section_class'] ); ?>">
			<aside class="num">
				<?php
				FrmAppHelper::icon_by_class(
					'frmfont ' . $step['icon'],
					array(
						'aria-label' => sprintf( __( 'Step %1$d', 'formidable' ), $step['num'] ),
						'style'      => 'width:50px;height:50px;',
					)
				);
				?>
				<i class="loader hidden"></i>
			</aside>
			<div>
				<h2><?php echo esc_html( $step['label'] ); ?></h2>
		<?php
	}

	protected function step_bottom( $step ) {
		?>
			</div>
		</section>
		<?php
	}

	protected function show_plugin_install() {
		$step          = $this->get_step_data( 2 );
		$step['label'] = __( 'Install Required Plugins', 'formidable' );

		$plugins = $this->required_plugins();
		if ( empty( $plugins ) ) {
			return false;
		}

		$links = array();
		$rel   = array();
		foreach ( $plugins as $plugin_key ) {
			$plugin = FrmAddonsController::install_link( $plugin_key );
			if ( $plugin['status'] === 'active' ) {
				continue;
			}
			$links[ $plugin_key ] = $plugin;
			if ( isset( $plugin['url'] ) ) {
				$rel[] = $plugin['url'];
			}
		}

		$step['section_class'] = empty( $links ) ? 'grey' : '';
		$this->step_top( $step );

		if ( empty( $links ) ) {
			?>
			<a rel="" class="button button-primary frm-button-primary disabled">
				<?php esc_html_e( 'Install & Activate', 'formidable' ); ?>
			</a>
			<?php
		} elseif ( count( $links ) === 1 ) {
			$addon = reset( $links );
			if ( isset( $addon['url'] ) ) {
				?>
				<a rel="<?php echo esc_attr( $addon['url'] ); ?>" class="button button-primary frm-button-primary <?php echo esc_attr( $addon['class'] ); ?>">
					<?php esc_html_e( 'Install & Activate', 'formidable' ); ?>
				</a>
				<?php
			} else {
				// Add add-on is required but not allowed.
				?>
				<span class="frm_error">
					<?php
					printf(
						esc_html__( 'You need permission to download the Formidable %1%s plugin', 'formidable' ),
						array_key_first( $links )
					);
					?>
				</span>
				<?php
			}
		} else {
			?>
			<a rel="<?php echo esc_attr( implode( ',', $rel ) ); ?>" class="button button-primary frm-button-primary frm-solution-multiple">
				<?php esc_html_e( 'Install & Activate', 'formidable' ); ?>
			</a>
			<?php
		}

		$this->step_bottom( $step );
		return true;
	}

	protected function show_app_install( $num ) {
		$step          = $this->get_step_data( $num );
		$step['label'] = __( 'Create Forms and Views', 'formidable' );

		$this->step_top( $step );

		echo '<br/>Include option to choose or create pages, and include or exclude sample data.<br/>';

		$this->step_bottom( $step );
	}

	protected function show_page_links() {
		$step = $this->get_step_data( 4 );
		$this->step_top( $step );

		echo '<br/>Links to new pages and/or forms<br/>';

		$this->step_bottom( $step );
	}

	/**
	 * Only show the content for the correct plugin.
	 */
	protected function is_current_plugin() {
		$to_redirect = get_transient( 'frm_activation_redirect' );
		return $to_redirect === $this->plugin_slug;
	}

	/**
	 * If the add-on process hasn't been triggered, do it now.
	 */
	protected function maybe_install() {
		if ( empty( $this->is_installed() ) && ! get_transient( 'frm_activation_redirect' ) ) {
			set_transient( 'frm_activation_redirect', $this->plugin_slug, 30 );
		}
	}

	protected function is_installed() {
		return get_option( 'frm_installed_' . $this->plugin_slug );
	}

	/**
	 * In the new plugin has any dependencies, include them here.
	 */
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
	 * Set a flag so the install won't be triggered again.
	 */
	protected function install_complete() {
		update_option( 'frm_installed_' . $this->plugin_slug, $this->plugin_version );
	}

	private function css() {
		?>
<style>
#frm-welcome *, #frm-welcome *::before, #frm-welcome  *::after {
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}
#frm-welcome{
	width: 700px;
	margin: 0 auto;
}
#frm-welcome p {
	font-size: 15px;
}
#frm-welcome section{
	margin: 50px 0;
	text-align: left;
	clear: both;
}
#frm-welcome .top{
	text-align: center;
}
.frm-smtp-logos {
	margin-bottom: 38px;
}
.frm-smtp-logos svg {
	vertical-align: middle;
}
#frm-welcome .top h1 {
	font-size: 26px;
	font-weight: 600;
	margin-bottom: 0;
	padding: 0;
}
#frm-welcome .top p {
	font-size: 17px;
	color: #777;
	margin-top: .5em;
}
#frm-welcome .screenshot ul {
	display: inline-block;
	margin: 0 0 0 30px;
	list-style-type: none;
	max-width: calc(100% - 350px);
}
#frm-welcome .screenshot li {
	margin: 16px 0;
	padding: 0;
	font-size: 15px;
	color: #777;
}
#frm-welcome .screenshot .cont img {
	max-width: 100%;
	display: block;
}
#frm-welcome .screenshot .cont {
	display: inline-block;
	position: relative;
	width: 315px;
	padding: 5px;
	background-color: #fff;
	border-radius: 3px;
}
#frm-welcome .step,
#frm-welcome .screenshot .cont {
	-webkit-box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
	-moz-box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
	box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
}
#frm-welcome .step {
	background-color: #F9F9F9;
	border: 1px solid #E5E5E5;
	margin: 0 0 25px;
}
#frm-welcome .screenshot > *,
#frm-welcome .step > * {
	vertical-align: middle;
}
#frm-welcome .step p {
	font-size: 16px;
	color: #777777;
}
#frm-welcome .step .num {
	display: inline-block;
	position: relative;
	width: 100px;
	height: 50px;
	text-align: center;
}
#frm-welcome .step > div {
	display: inline-block;
	width: calc(100% - 104px);
	background-color: #fff;
	padding: 30px;
	border-left: 1px solid #eee;
}
#frm-welcome .grey {
	opacity: 0.5;
	background: #F6F6F6 !important;
	border-color: #ddd !important;
	color: #9FA5AA !important;
}
#frm-welcome .step h2 {
	font-size: 24px;
	line-height: 22px;
	margin-top: 0;
	margin-bottom: 15px;
}
#frm-welcome .button.disabled {
	cursor: default;
}
#frm-welcome #frm-using-lite {
	display: none;
}
</style>
		<?php
	}

}
