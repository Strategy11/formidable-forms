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

/**
TODO:
- Directory: edit in place and go to page 4 -> ugly error
- Directory with 3 grid
- Hide install formidable button while installing it
- Add connect box on add-omns page. Don't show "Missing add-ons" message if not connected.
- Add url box to install lite in docs
- After install, show message to pick a view and publish
- Show a list of all forms, pages, and views created
- Create a page with the form for new listings
*/

class FrmSolution {

	protected $plugin_slug = 'formidable';

	protected $plugin_file = '';

	/**
	 * Hidden welcome page slug.
	 *
	 * @since 4.04.02
	 */
	protected $page = 'formidable-getting-started';

	public function __construct( $atts = array() ) {
		if ( $this->plugin_slug !== 'formidable' ) {
			add_action( 'plugins_loaded', array( $this, 'load_hooks' ), 50 );
			add_action( 'admin_init', array( $this, 'redirect' ), 9999 );
		}

		if ( empty( $this->plugin_file ) ) {
			$this->plugin_file = $this->plugin_slug . '.php';
		}
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

		add_filter( 'plugin_action_links_' . $this->plugin_slug . '/' . $this->plugin_file, array( $this, 'plugin_links' ) );
		add_action( 'admin_menu', array( $this, 'register' ) );
		add_action( 'admin_head', array( $this, 'hide_menu' ) );
	}

	public function plugin_links( $links ) {
		if ( ! $this->is_complete() ) {
			$settings = '<a href="' . esc_url( $this->settings_link() ) . '">' . __( 'Setup', 'formidable' ) . '</a>';
			array_unshift( $links, $settings );
		}

		return $links;
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

	protected function page_title() {
		return __( 'Welcome to Formidable Forms', 'formidable' );
	}

	protected function page_description() {
		return __( 'Follow the steps below to get started.', 'formidable' );
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

		$current_page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		if ( $current_page === $this->page ) {
			// Prevent endless loop.
			return;
		}

		// Only do this for single site installs.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // WPCS: CSRF ok.
			return;
		}

		// Check if we should consider redirection.
		if ( ! $this->is_current_plugin() ) {
			return;
		}

		delete_transient( 'frm_activation_redirect' );

		// Initial install.
		wp_safe_redirect( $this->settings_link() );
		exit;
	}

	protected function settings_link() {
		return admin_url( 'index.php?page=' . $this->page );
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
				<?php
				FrmAppHelper::icon_by_class(
					'frmfont frm_arrow_right_icon',
					array(
						'aria-label' => 'Install',
						'style'      => 'width:30px;height:30px;margin:0 35px;',
					)
				);
				FrmAppHelper::icon_by_class(
					'frmfont frm_wordpress_icon',
					array(
						'aria-label' => 'WordPress',
						'style'      => 'width:90px;height:90px;',
					)
				);
				?>
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
			$steps = $this->get_steps_data();
			$this->license_box( $steps['license'] );
			if ( isset( $steps['plugin'] ) ) {
				$this->show_plugin_install( $steps['plugin'] );
			}
			$this->show_app_install( $steps['import'] );
			$this->show_page_links( $steps['complete'] );
		}
	}

	protected function get_steps_data() {
		global $frm_vars;
		$pro_installed = FrmAppHelper::pro_is_installed() && $frm_vars['pro_is_authorized'];

		$steps = array(
			'license' => array(
				'label'         => __( 'Connect to FormidableForms.com', 'formidable' ),
				'description'   => __( 'Create a connection to get plugin downloads.', 'formidable' ),
				'button_label'  => __( 'Connect an Account', 'formidable' ),
				'current'       => empty( $pro_installed ),
				'complete'      => $pro_installed,
				'num'           => 1,
			),
			'plugin' => array(
				'label'         => __( 'Install and Activate Add-Ons', 'formidable' ),
				'description'   => __( 'Install any required add-ons from FormidableForms.com.', 'formidable' ),
				'button_label'  => __( 'Install & Activate', 'formidable' ),
				'current'       => false,
				'complete'      => false,
				'num'           => 2,
			),
			'import' => array(
				'label'         => __( 'Setup Forms and Views', 'formidable' ),
				'description'   => __( 'Build the forms, views, and pages automatically.', 'formidable' ),
				'button_label'  => __( 'Create Now', 'formidable' ),
				'complete'      => $this->is_complete(),
				'num'           => 3,
			),
			'complete' => array(
				'label'         => __( 'Customize Your New Pages', 'formidable' ),
				'description'   => __( 'Make any required changes and publish the page.', 'formidable' ),
				'button_label'  => __( 'View Page', 'formidable' ),
				'complete'      => false,
				'num'           => 4,
			),
		);

		$this->adjust_plugin_install_step( $steps );

		$has_current = false;
		foreach ( $steps as $k => $step ) {
			// Set the current step.
			if ( ! isset( $step['current'] ) ) {
				if ( $step['complete'] ) {
					$steps[ $k ]['current'] = false;
				} else {
					$steps[ $k ]['current'] = ! $has_current;
					$has_current = true;
				}
			} elseif ( $step['current'] ) {
				$has_current = true;
			}

			// Set disabled buttons.
			$class = isset( $step['button_class'] ) ? $step['button_class'] : '';
			$class .= ' button-primary frm-button-primary';
			if ( ! $steps[ $k ]['current'] ) {
				$class .= ' grey disabled';
			}
			$steps[ $k ]['button_class'] = $class;
		}

		return $steps;
	}

	protected function adjust_plugin_install_step( &$steps ) {
		$plugins = $this->required_plugins();
		if ( empty( $plugins ) ) {
			unset( $steps['plugin'] );
			$steps['import']['num']   = 2;
			$steps['complete']['num'] = 3;
			return;
		}

		$missing = array();
		$rel     = array();
		foreach ( $plugins as $plugin_key ) {
			$plugin = FrmAddonsController::install_link( $plugin_key );
			if ( $plugin['status'] === 'active' ) {
				continue;
			}
			$links[ $plugin_key ] = $plugin;
			if ( isset( $plugin['url'] ) ) {
				$rel[] = $plugin['url'];
			} else {
				// Add-on is required but not allowed.
				$missing[] = $plugin_key;
			}
		}
		if ( empty( $rel ) && empty( $missing ) ) {
			$steps['plugin']['complete'] = true;
		} elseif ( ! empty( $missing ) ) {
			$steps['plugin']['error'] = sprintf(
				/* translators: %1$s: Plugin name */
				esc_html__( 'You need permission to download the Formidable %1$s plugin', 'formidable' ),
				implode( ', ', $missing )
			);
		} else {
			$steps['plugin']['links'] = $rel;
			$steps['plugin']['button_class'] = 'frm-solution-multiple ';
		}

		if ( $steps['license']['complete'] && ! $steps['plugin']['complete'] ) {
			$steps['plugin']['current'] = true;
		}
	}

	protected function step_top( $step ) {
		$section_class = ( ! isset( $step['current'] ) || ! $step['current'] ) ? 'grey' : '';

		?>
		<section class="step step-install <?php echo esc_attr( $section_class ); ?>">
			<aside class="num">
			<?php
			if ( isset( $step['complete'] ) && $step['complete'] ) {
				FrmAppHelper::icon_by_class(
					'frmfont frm_step_complete_icon',
					array(
						/* translators: %1$s: Step number */
						'aria-label' => sprintf( __( 'Step %1$d', 'formidable' ), $step['num'] ),
						'style'      => 'width:50px;height:50px;',
					)
				);
			} else {
				?>
				<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="#ccc"/><text x="50%" y="50%" text-anchor="middle" fill="#fff" stroke="#fff" stroke-width="2px" dy=".3em" font-size="3.7em"><?php echo esc_html( $step['num'] ); ?></text></svg>
				<?php
			}
			?>
				<i class="loader hidden"></i>
			</aside>
			<div>
				<h2><?php echo esc_html( $step['label'] ); ?></h2>
				<p><?php echo esc_html( $step['description'] ); ?></p>
				<?php if ( isset( $step['error'] ) ) { ?>
					<p class="frm_error"><?php echo esc_html( $step['error'] ); ?></p>
				<?php } ?>
		<?php
	}

	protected function step_bottom( $step ) {
		?>
			</div>
		</section>
		<?php
	}

	/**
	 * Generate and output Connect step section HTML.
	 */
	protected function license_box( $step ) {
		$this->step_top( $step );

		if ( $step['complete'] ) {
			?>
			<a href="#" class="<?php echo esc_attr( $step['button_class'] ); ?>">
				<?php echo esc_html( $step['button_label'] ); ?>
			</a>
			<?php
		} else {
			FrmSettingsController::license_box();
		}

		$this->step_bottom( $step );
	}

	protected function show_plugin_install( $step ) {
		$this->step_top( $step );

		if ( ! isset( $step['error'] ) ) {
			$rel = isset( $step['links'] ) ? $step['links'] : array();

			?>
			<a rel="<?php echo esc_attr( implode( ',', $rel ) ); ?>" class="<?php echo esc_attr( $step['button_class'] ); ?>">
				<?php echo esc_html( $step['button_label'] ); ?>
			</a>
			<?php
		}

		$this->step_bottom( $step );
	}

	protected function show_app_install( $step ) {
		$is_complete = $step['complete'];

		$this->step_top( $step );

		$api    = new FrmFormApi();
		$addons = $api->get_api_info();

		$id = $this->download_id();
		$has_file = isset( $addons[ $id ] ) && isset( $addons[ $id ]['beta'] );

		if ( ! $step['current'] ) {
			?>
			<a href="#" class="<?php echo esc_attr( $step['button_class'] ); ?>">
				<?php echo esc_html( $step['button_label'] ); ?>
			</a>
			<?php

			$this->step_bottom( $step );
			return;
		}

		if ( ! $has_file ) {
			echo '<p class="frm_error_style">' . esc_html__( 'Files not found.', 'formidable' ) . '</p>';
		} elseif ( ! isset( $addons[ $id ]['beta']['package'] ) ) {
			echo '<p class="frm_error_style">' . esc_html__( 'Looks like you may not have a current subscription for this solution. Please check your account.', 'formidable' ) . '</p>';
		} else {
			$xml = $addons[ $id ]['beta']['package'];
			?>
			<form name="frm-new-template" id="frm-new-template" method="post" class="field-group">
				<input type="hidden" name="link" id="frm_link" value="<?php echo esc_attr( $xml ); ?>" />
				<input type="hidden" name="type" id="frm_action_type" value="frm_install_template" />
				<input type="hidden" name="template_name" id="frm_template_name" value="" />
				<input type="hidden" name="template_desc" id="frm_template_desc" value="" />
				<input type="hidden" name="redirect" value="0" />
				<button type="submit" class="<?php echo esc_attr( $step['button_class'] ); ?>">
					<?php echo esc_html( $step['button_label'] ); ?>
				</button>
			</form>
			<?php
		}

		$this->step_bottom( $step );
	}

	protected function show_page_links( $step ) {
		$this->step_top( $step );

		$page_link = $step['current'] ? $this->get_page_link() : '#';
		if ( ! empty( $page_link ) ) {
			?>
			<a href="<?php echo esc_url( $page_link ); ?>" target="_blank" rel="noopener" id="frm-redirect-link" class="<?php echo esc_attr( $step['button_class'] ); ?>">
				<?php echo esc_html( $step['button_label'] ); ?>
			</a>
			<?php

			if ( $step['current'] ) {
				$this->remove_from_inbox();
			}
		}

		$this->step_bottom( $step );
	}

	protected function get_page_link() {
		$page_slug = $this->new_page_slug();
		return get_permalink( get_page_by_path( $page_slug ) );
	}

	/**
	 * This function needs an override.
	 */
	protected function new_page_slug() {
		return '';
	}

	/**
	 * Only show the content for the correct plugin.
	 */
	protected function is_current_plugin() {
		$to_redirect = get_transient( 'frm_activation_redirect' );
		if ( empty( $to_redirect ) && FrmAppHelper::is_admin_page( 'formidable-settings' ) && ! $this->is_complete() ) {
			// The page won't be redirected but isn't complete.
			$this->add_to_inbox();
		}

		return $to_redirect === $this->plugin_slug && empty( $this->is_complete() );
	}

	/**
	 * Override this function to indicate when install is complete.
	 */
	protected function is_complete() {
		return false;
	}

	/**
	 * In the new plugin has any dependencies, include them here.
	 */
	protected function required_plugins() {
		return array();
	}

	/**
	 * This needs to be overridden.
	 */
	protected function download_id() {
		return 0;
	}

	/**
	 * If the install wasn't completed, add a message.
	 */
	protected function add_to_inbox() {
		$message = new FrmInbox();
		$message->add_message(
			array(
				'key'     => $this->plugin_slug . '-solution',
				'message' => __( 'Your plugin setup isn\'t quite complete.', 'formidable' ),
				'subject' => $this->page_title(),
				'cta'     => '<a href="' . esc_url( $this->settings_link() ) . '" class="button-primary frm-button-primary">' .
					esc_html__( 'Continue Install', 'formidable' ) . '</a>',
			)
		);
	}

	protected function remove_from_inbox() {
		$message = new FrmInbox();
		$message->remove( $this->plugin_slug . '-solution' );
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
