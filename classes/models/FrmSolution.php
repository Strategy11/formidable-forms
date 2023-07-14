<?php
/**
 * Handles the installation of a solution and any dependencies.
 * This page is shown when a Formidable plugin is activated.
 *
 * @since 4.06.02
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSolution {

	protected $plugin_slug = '';

	protected $plugin_file = '';

	/**
	 * Hidden welcome page slug.
	 *
	 * @since 4.06.02
	 */
	protected $page = '';

	protected $icon = 'frm_icon_font frm_settings_icon';

	public function __construct( $atts = array() ) {
		if ( empty( $this->plugin_slug ) ) {
			return;
		}

		add_action( 'plugins_loaded', array( $this, 'load_hooks' ), 50 );
		add_action( 'admin_init', array( $this, 'redirect' ), 9999 );

		if ( empty( $this->plugin_file ) ) {
			$this->plugin_file = $this->plugin_slug . '.php';
		}
	}

	/**
	 * Register all WP hooks.
	 *
	 * @since 4.06.02
	 *
	 * @return void
	 */
	public function load_hooks() {
		// If user is in admin ajax or doing cron, return.
		if ( wp_doing_cron() ) {
			return;
		}

		add_filter( 'frm_add_settings_section', array( $this, 'add_settings' ) );

		if ( wp_doing_ajax() ) {
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
	 * @since 4.06.02
	 *
	 * @return void
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
	 * @since 4.06.02
	 *
	 * @return void
	 */
	public function hide_menu() {
		remove_submenu_page( 'index.php', $this->page );
	}

	/**
	 * @return string
	 *
	 * @psalm-return ''
	 */
	protected function plugin_name() {
		return '';
	}

	/**
	 * @return string
	 */
	protected function page_title() {
		return __( 'Welcome to Formidable Forms', 'formidable' );
	}

	/**
	 * @return string
	 */
	protected function page_description() {
		return __( 'Follow the steps below to get started.', 'formidable' );
	}

	/**
	 * Welcome screen redirect.
	 *
	 * This function checks if a new install or update has just occurred. If so,
	 * then we redirect the user to the appropriate page.
	 *
	 * @since 4.06.02
	 *
	 * @return void
	 */
	public function redirect() {

		$current_page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		if ( $current_page === $this->page ) {
			// Prevent endless loop.
			return;
		}

		// Only do this for single site installs.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
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

	/**
	 * @return string
	 */
	protected function settings_link() {
		return admin_url( 'index.php?page=' . $this->page );
	}

	/*
	 * Add page to global settings.
	 */
	public function add_settings( $sections ) {
		wp_enqueue_style( 'formidable-pro-fields' );
		$sections[ $this->plugin_slug ] = array(
			'class'    => $this,
			'function' => 'settings_page',
			'name'     => $this->plugin_name(),
			'icon'     => $this->icon,
			'ajax'     => true,
		);
		return $sections;
	}

	/*
	 * Output for global settings.
	 */
	/**
	 * @return void
	 */
	public function settings_page() {
		$steps = $this->get_steps_data();
		if ( ! $steps['license']['complete'] || ( isset( $steps['plugin'] ) && ! $steps['plugin']['complete'] ) ) {
			// Redirect to the welcome page if install hasn't been done.
			$url = $this->settings_link();
			echo '<script>window.location.replace("' . esc_url_raw( $url ) . '");</script>';
			return;
		}

		$all_imported = $this->is_complete( 'all' );

		$step           = $steps['import'];
		$step['label']  = '';
		$step['nested'] = true;

		if ( $steps['complete']['current'] ) {
			// Always show this step in settings.
			$step['current'] = true;

			$new_class = $all_imported ? ' button frm_hidden' : '';
			$step['button_class'] = str_replace( 'frm_grey disabled', $new_class, $step['button_class'] );
		}
		if ( $all_imported ) {
			$step['description'] = __( 'The following form(s) have been created.', 'formidable' );
		}
		$this->show_app_install( $step );

		if ( ! $all_imported ) {
			$step            = $steps['complete'];
			$step['current'] = false;
			$step['button_class'] .= ' frm_grey disabled';
			$this->show_page_links( $step );
		}
	}

	/**
	 * Getting Started screen. Shows after first install.
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	protected function main_content() {
		$steps = $this->get_steps_data();
		$this->license_box( $steps['license'] );
		if ( isset( $steps['plugin'] ) ) {
			$this->show_plugin_install( $steps['plugin'] );
		}
		$this->show_app_install( $steps['import'] );
		$this->show_page_links( $steps['complete'] );
	}

	protected function get_steps_data() {
		$pro_installed = FrmAppHelper::pro_is_connected();

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
				'label'         => __( 'Setup Forms, Views, and Pages', 'formidable' ),
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
				$class .= ' frm_grey disabled';
			}
			$steps[ $k ]['button_class'] = $class;
		}

		return $steps;
	}

	/**
	 * @param array $steps
	 *
	 * @return void
	 */
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

	/**
	 * @return void
	 */
	protected function step_top( $step ) {
		$section_class = ( ! isset( $step['current'] ) || ! $step['current'] ) ? 'frm_grey' : '';

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
				<?php if ( $step['label'] ) { ?>
				<h3 class="frm-step-heading"><?php echo esc_html( $step['label'] ); ?></h3>
				<?php } ?>
				<p><?php echo esc_html( $step['description'] ); ?></p>
				<?php if ( isset( $step['error'] ) ) { ?>
					<p class="frm_error"><?php echo esc_html( $step['error'] ); ?></p>
				<?php } ?>
		<?php
	}

	/**
	 * @return void
	 */
	protected function step_bottom( $step ) {
		?>
			</div>
		</section>
		<?php
	}

	/**
	 * Generate and output Connect step section HTML.
	 *
	 * @return void
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

	/**
	 * @return void
	 */
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

	/**
	 * @return void
	 */
	protected function show_app_install( $step ) {
		$is_complete = $step['complete'];
		if ( ! empty( $this->form_options() ) && ! $is_complete ) {
			$step['description'] = __( 'Select the form or view you would like to create.', 'formidable' );
		}

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
			echo '<p class="frm_error_style">' . esc_html__( 'We didn\'t find anything to import. Please contact our team.', 'formidable' ) . '</p>';
		} elseif ( ! isset( $addons[ $id ]['beta']['package'] ) ) {
			echo '<p class="frm_error_style">' . esc_html__( 'Looks like you may not have a current subscription for this solution. Please check your account.', 'formidable' ) . '</p>';
		} else {
			$xml = $addons[ $id ]['beta']['package'];
			if ( is_array( $xml ) ) {
				$xml = reset( $xml );
			}

			if ( isset( $step['nested'] ) ) {
				echo '<fieldset id="frm-new-template" class="field-group">';
			} else {
				echo '<form name="frm-new-template" id="frm-new-template" method="post" class="field-group">';
			}

			?>
				<input type="hidden" name="link" id="frm_link" value="<?php echo esc_attr( $xml ); ?>" />
				<input type="hidden" name="type" id="frm_action_type" value="frm_install_template" />
				<input type="hidden" name="template_name" id="frm_template_name" value="" />
				<input type="hidden" name="template_desc" id="frm_template_desc" value="" />
				<input type="hidden" name="redirect" value="0" />
				<input type="hidden" name="show_response" value="frm_install_error" />
				<?php
				$this->show_form_options( $xml );
				$this->show_view_options();

				if ( ! $this->is_complete( 'all' ) ) {
					// Don't show on the settings page when complete.
					$this->show_page_options();
				}
				?>
				<p>
					<button <?php echo esc_html( isset( $step['nested'] ) ? '' : 'type="submit" ' ); ?>class="<?php echo esc_attr( $step['button_class'] ); ?>">
						<?php echo esc_html( $step['button_label'] ); ?>
					</button>
				</p>
				<p id="frm_install_error" class="frm_error_style frm_hidden"></p>
			<?php
			if ( isset( $step['nested'] ) ) {
				echo '</fieldset>';
			} else {
				echo '</form>';
			}
		}

		$this->step_bottom( $step );
	}

	/**
	 * @return void
	 */
	protected function show_form_options( $xml ) {
		$this->show_import_options( $this->form_options(), 'form', $xml );
	}

	/**
	 * @return void
	 */
	protected function show_view_options() {
		$this->show_import_options( $this->view_options(), 'view' );
	}

	/**
	 * @param string $importing
	 *
	 * @psalm-param 'form'|'view' $importing
	 *
	 * @return void
	 */
	protected function show_import_options( $options, $importing, $xml = '' ) {
		if ( empty( $options ) ) {
			return;
		}

		$imported = $this->previously_imported_forms();
		$count    = count( $options );
		foreach ( $options as $info ) {
			// Count the number of options displayed for css.
			if ( $count > 1 && ! isset( $info['img'] ) ) {
				$count --;
			}
		}
		$width = floor( ( 533 - ( ( $count - 1 ) * 20 ) ) / $count );
		unset( $count );

		$selected = false;

		include( FrmAppHelper::plugin_path() . '/classes/views/solutions/_import.php' );
	}

	/**
	 * @return void
	 */
	protected function show_page_options() {
		$pages = $this->needed_pages();
		if ( empty( $pages ) ) {
			return;
		}

		echo '<h3>Choose New Page Title</h3>';
		foreach ( $pages as $page ) {
			?>
			<p>
				<label for="pages_<?php echo esc_html( $page['type'] ); ?>">
					<?php echo esc_html( $page['label'] ); ?>
				</label>
				<input type="text" name="pages[<?php echo esc_html( $page['type'] ); ?>]" value="<?php echo esc_attr( $page['name'] ); ?>" id="pages_<?php echo esc_html( $page['type'] ); ?>" required />
			</p>
			<?php
		}
	}

	/**
	 * @return void
	 */
	protected function show_page_links( $step ) {
		if ( $step['current'] ) {
			return;
		}

		$this->step_top( $step );

		?>
		<a href="#" target="_blank" rel="noopener" id="frm-redirect-link" class="<?php echo esc_attr( $step['button_class'] ); ?>">
			<?php echo esc_html( $step['button_label'] ); ?>
		</a>
		<?php

		$this->step_bottom( $step );
	}

	/**
	 * Only show the content for the correct plugin.
	 *
	 * @return bool
	 */
	protected function is_current_plugin() {
		$to_redirect = get_transient( 'frm_activation_redirect' );
		return $to_redirect === $this->plugin_slug && empty( $this->is_complete() );
	}

	/**
	 * Override this function to indicate when install is complete.
	 *
	 * @param int|string $count
	 *
	 * @psalm-param 'all'|1 $count
	 *
	 * @return bool
	 */
	protected function is_complete( $count = 1 ) {
		$imported = $this->previously_imported_forms();
		if ( $count === 'all' ) {
			return count( $imported ) >= count( $this->form_options() );
		}
		return ! empty( $imported );
	}

	/**
	 * Get an array of all of the forms that have been imported.
	 *
	 * @return array
	 */
	protected function previously_imported_forms() {
		$imported = array();
		$forms    = $this->form_options();
		foreach ( $forms as $form ) {
			$was_imported = isset( $form['form'] ) ? FrmForm::get_id_by_key( $form['form'] ) : false;
			if ( $was_imported ) {
				$imported[ $form['form'] ] = $was_imported;
			}
		}

		return $imported;
	}

	/**
	 * In the new plugin has any dependencies, include them here.
	 *
	 * @return array
	 */
	protected function required_plugins() {
		return array();
	}

	/**
	 * This needs to be overridden.
	 *
	 * @return int
	 */
	protected function download_id() {
		return 0;
	}

	/**
	 * Give options for which forms to import.
	 *
	 * @return array
	 */
	protected function form_options() {
		/**
		 * Example:
		 * array(
		 *  'unique-key' => array(
		 *    'keys' => 'forms keys here',
		 *    'name' => 'displayed label here',
		 *    'img'  => 'svg code',
		 *  ),
		 * )
		 */
		return array();
	}

	/**
	 * Give options for which view to use.
	 *
	 * @return array
	 */
	protected function view_options() {
		return array();
	}

	/**
	 * If the pages aren't imported automatically, set the page names.
	 *
	 * @return array
	 */
	protected function needed_pages() {
		/**
		 * Example:
		 * array(
		 *   array(
		 *     'label' => 'Page Name',
		 *     'name'  => 'Default name',
		 *     'type'  => 'form' or 'view',
		 *   ),
		 * )
		 */

		return array();
	}

	/**
	 * @return void
	 */
	private function css() {
		wp_enqueue_style( 'formidable-pro-fields' );
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
#frm-welcome .step h3.frm-step-heading {
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
