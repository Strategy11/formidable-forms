<?php

/**
 * SMTP Sub-page.
 *
 * @since 4.04.04
 */
class FrmSMTPController {

	/**
	 * Admin menu page slug.
	 *
	 * @since 4.04.04
	 *
	 * @var string
	 */
	public $slug = 'formidable-smtp';

	/**
	 * @since 4.04.04
	 *
	 * @var array
	 */
	private $config = array(
		'lite_plugin'       => 'wp-mail-smtp/wp_mail_smtp.php',
		'lite_download_url' => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
		'pro_plugin'        => 'wp-mail-smtp-pro/wp_mail_smtp.php',
		'smtp_settings'     => 'admin.php?page=wp-mail-smtp',
	);

	/**
	 * Runtime data used for generating page HTML.
	 *
	 * @since 4.04.04
	 *
	 * @var array
	 */
	private $output_data = array();

	/**
	 * Hooks.
	 *
	 * @since 4.04.04
	 */
	public static function load_hooks() {

		add_filter( 'wp_mail_smtp_is_white_labeled', '__return_true' );

		$self = new self();
		if ( wp_doing_ajax() ) {
			add_action( 'wp_ajax_frm_smtp_page_check_plugin_status', array( $self, 'ajax_check_plugin_status' ) );
		}

		add_action( 'admin_menu', array( $self, 'menu' ), 999 );

		// Only load if we are actually on the SMTP page.
		if ( ! FrmAppHelper::is_admin_page( $self->slug ) ) {
			return;
		}

		add_action( 'admin_init', array( $self, 'redirect_to_smtp_settings' ) );
		//add_action( 'admin_enqueue_scripts', array( $self, 'enqueue_assets' ) );

		// Hook for addons.
		do_action( 'frm_admin_pages_smtp_hooks' );
	}

	/**
	 * SMTP submenu page.
	 */
	public function menu() {		
		add_submenu_page( 'formidable', __( 'SMTP', 'formidable' ) . ' | Formidable', __( 'SMTP', 'formidable' ), 'activate_plugins', 'formidable-smtp', array( $this, 'output' ) );
	}

	/**
	 * Enqueue JS and CSS files.
	 *
	 * @since 4.04.04
	 */
	public function enqueue_assets() {

		// Lity.
		wp_enqueue_style(
			'wpforms-lity',
			FrmAppHelper::plugin_url() . 'assets/css/lity.min.css',
			null,
			'3.0.0'
		);

		wp_enqueue_script(
			'wpforms-lity',
			FrmAppHelper::plugin_url() . 'assets/js/lity.min.js',
			array( 'jquery' ),
			'3.0.0',
			true
		);

		wp_enqueue_script(
			'frm-admin-page-smtp',
			FrmAppHelper::plugin_url() . "assets/js/components/admin/pages/smtp.js",
			array( 'jquery' ),
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'frm-admin-page-smtp',
			'frm_pluginlanding',
			$this->get_js_strings()
		);
	}

	/**
	 * JS Strings.
	 *
	 * @since 4.04.04
	 *
	 * @return array Array of strings.
	 */
	protected function get_js_strings() {

		$error_could_not_install = sprintf(
			wp_kses( /* translators: %s - Lite plugin download URL. */
				__( 'Could not install plugin. Please <a href="%s">download</a> and install manually.', 'formidable' ),
				array(
					'a' => array(
						'href' => true,
					),
				)
			),
			esc_url( $this->config['lite_download_url'] )
		);

		$error_could_not_activate = sprintf(
			wp_kses( /* translators: %s - Lite plugin download URL. */
				__( 'Could not activate plugin. Please activate from the <a href="%s">Plugins page</a>.', 'formidable' ),
				array(
					'a' => array(
						'href' => true,
					),
				)
			),
			esc_url( admin_url( 'plugins.php' ) )
		);

		return array(
			'installing'               => esc_html__( 'Installing...', 'formidable' ),
			'activating'               => esc_html__( 'Activating...', 'formidable' ),
			'activated'                => esc_html__( 'WP Mail SMTP Installed & Activated', 'formidable' ),
			'install_now'              => esc_html__( 'Install Now', 'formidable' ),
			'activate_now'             => esc_html__( 'Activate Now', 'formidable' ),
			'download_now'             => esc_html__( 'Download Now', 'formidable' ),
			'plugins_page'             => esc_html__( 'Go to Plugins page', 'formidable' ),
			'error_could_not_install'  => $error_could_not_install,
			'error_could_not_activate' => $error_could_not_activate,
			'manual_install_url'       => $this->config['lite_download_url'],
			'manual_activate_url'      => admin_url( 'plugins.php' ),
			'smtp_settings_button'     => esc_html__( 'Go to SMTP Settings', 'formidable' ),
		);
	}

	/**
	 * Generate and output page HTML.
	 *
	 * @since 4.04.04
	 */
	public function output() {

		echo '<div id="frm-admin-smtp" class="wrap frm-wrap frm-admin-plugin-landing">';

		$this->output_section_heading();
		$this->output_section_screenshot();
		$this->output_section_step_install();
		$this->output_section_step_setup();

		echo '</div>';
	}

	/**
	 * Generate and output heading section HTML.
	 *
	 * @since 4.04.04
	 */
	protected function output_section_heading() {
		$size = array(
			'height' => 90,
			'width'  => 90,
		);

		// Heading section.
		?>
		<section class="top">
			<?php FrmAppHelper::show_logo( $size ); ?>
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_heart_solid_icon', array( 'aria-label' => 'Loves' ) ); ?>
			<h1><?php esc_html_e( 'Making Email Deliverability Easy for WordPress', 'formidable' ); ?></h1>
			<p><?php esc_html_e( 'WP Mail SMTP allows you to easily set up WordPress to use a trusted provider to reliably send emails, including form notifications.', 'formidable' )?></p>
		</section>
		<?php
	}

	/**
	 * Generate and output screenshot section HTML.
	 *
	 * @since 4.04.04
	 */
	protected function output_section_screenshot() {

		// Screenshot section.
		printf(
			'<section class="screenshot">
				<div class="cont">
					<img src="%1$s" alt="%2$s"/>
					<a href="%3$s" class="hover" data-lity></a>
				</div>
				<ul>
					<li>%4$s</li>
					<li>%5$s</li>
					<li>%6$s</li>
					<li>%7$s</li>
				</ul>			
			</section>',
			esc_url( FrmAppHelper::plugin_url() . '/images/smtp-screenshot-tnail.png' ),
			esc_attr__( 'WP Mail SMTP screenshot', 'formidable' ),
			esc_url( FrmAppHelper::plugin_url() . 'assets/images/smtp/screenshot-full.png' ),
			esc_html__( 'Over 1,000,000 websites use WP Mail SMTP.', 'formidable' ),
			esc_html__( 'Send emails authenticated via trusted parties.', 'formidable' ),
			esc_html__( 'Transactional Mailers: Pepipost, SendinBlue, Mailgun, SendGrid, Amazon SES.', 'formidable' ),
			esc_html__( 'Web Mailers: Gmail, G Suite, Office 365, Outlook.com.', 'formidable' )
		);
	}

	/**
	 * Generate and output step 'Install' section HTML.
	 *
	 * @since 4.04.04
	 */
	protected function output_section_step_install() {

		$step = $this->get_data_step_install();

		if ( empty( $step ) ) {
			return;
		}

		printf(
			'<section class="step step-install">
				<aside class="num">
					%1$s
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%2$s</h2>
					<p>%3$s</p>
					<a rel="%4$s" class="button button-primary frm-button-primary %5$s" aria-label="%6$s">%7$s</a>
				</div>		
			</section>',
			FrmAppHelper::icon_by_class( 'frmfont ' . $step['icon'], array( 'aria-label' => __( 'Step 1', 'formidable' ) ) ),
			esc_html__( 'Install and Activate WP Mail SMTP', 'formidable' ),
			esc_html__( 'Install WP Mail SMTP from the WordPress.org plugin repository.', 'formidable' ),
			esc_attr( $step['plugin'] ),
			esc_attr( $step['button_class'] ),
			esc_attr( $step['button_action'] ),
			esc_html( $step['button_text'] )
		);
		

	}

	/**
	 * Generate and output step 'Setup' section HTML.
	 *
	 * @since 4.04.04
	 */
	protected function output_section_step_setup() {

		$step = $this->get_data_step_setup();

		if ( empty( $step ) ) {
			return;
		}

		printf(
			'<section class="step step-setup %1$s">
				<aside class="num">
					%2$s
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%3$s</h2>
					<p>%4$s</p>
					<button class="button %5$s" data-url="%6$s">%7$s</button>
				</div>		
			</section>',
			esc_attr( $step['section_class'] ),
			FrmAppHelper::icon_by_class( 'frmfont ' . $step['icon'], array( 'aria-label' => __( 'Step 2', 'formidable' ) ) ),
			esc_html__( 'Set Up WP Mail SMTP', 'formidable' ),
			esc_html__( 'Select and configure your mailer.', 'formidable' ),
			esc_attr( $step['button_class'] ),
			esc_url( admin_url( $this->config['smtp_settings'] ) ),
			esc_html( $step['button_text'] )
		);
	}

	/**
	 * Step 'Install' data.
	 *
	 * @since 4.04.04
	 *
	 * @return array Step data.
	 */
	protected function get_data_step_install() {

		$lite_plugin = new FrmInstallPlugin( array( 'plugin_file' => $this->config['lite_plugin'] ) );
		$pro_plugin  = new FrmInstallPlugin( array( 'plugin_file' => $this->config['pro_plugin'] ) );

		$this->output_data['plugin_installed']     = $lite_plugin->is_installed();
		$this->output_data['pro_plugin_installed'] = $pro_plugin->is_installed();
		$this->output_data['plugin_activated']     = false;
		$this->output_data['plugin_setup']         = false;

		$step = array(
			'icon'          => 'frm_step1_icon',
			'button_action' => '',
		);

		if ( ! $this->output_data['plugin_installed'] && ! $this->output_data['pro_plugin_installed'] ) {
			$step['button_text']   = __( 'Install WP Mail SMTP', 'formidable' );
			$step['button_class']  = 'frm-install-addon';
			$step['button_action'] = __( 'Install', 'formidable' );
			$step['plugin']        = $lite_plugin->get_install_link();
		} else {
			$this->output_data['plugin_activated'] = $this->is_smtp_activated();
			$this->output_data['plugin_setup']     = $this->is_smtp_configured();

			$step['plugin'] = $this->output_data['pro_plugin_installed'] ? $this->config['pro_plugin'] : $this->config['lite_plugin'];

			if ( $this->output_data['plugin_activated'] ) {
				$step['icon']          = 'frm_step_complete_icon';
				$step['button_text']   = __( 'WP Mail SMTP Installed & Activated', 'formidable' );
				$step['button_class']  = 'grey disabled';
			} else {
				$step['button_text']   = __( 'Activate WP Mail SMTP', 'formidable' );
				$step['button_class']  = 'frm-activate-addon';
				$step['button_action'] = __( 'Activate', 'formidable' );
			}
		}

		return $step;
	}

	/**
	 * Step 'Setup' data.
	 *
	 * @since 4.04.04
	 *
	 * @return array Step data.
	 */
	protected function get_data_step_setup() {

		$step = array();

		$step['icon']          = 'frm_step2_icon';
		$step['section_class'] = $this->output_data['plugin_activated'] ? '' : 'grey';
		$step['button_text']   = esc_html__( 'Start Setup', 'formidable' );
		$step['button_class']  = 'grey disabled';

		if ( $this->output_data['plugin_setup'] ) {
			$step['icon']          = 'frm_step_complete_icon';
			$step['section_class'] = '';
			$step['button_text']   = esc_html__( 'Go to SMTP settings', 'formidable' );
		} elseif ( $this->output_data['plugin_activated'] ) {
			$step['button_class'] = '';
		}

		return $step;
	}

	/**
	 * Ajax endpoint. Check plugin setup status.
	 * Used to properly init step 'Setup' section after completing step 'Install'.
	 *
	 * @since 4.04.04
	 */
	public function ajax_check_plugin_status() {

		// Security checks.
		if (
			! check_ajax_referer( 'wpforms-admin', 'nonce', false ) ||
			! wpforms_current_user_can()
		) {
			wp_send_json_error(
				array(
					'error' => esc_html__( 'You do not have permission.', 'formidable' ),
				)
			);
		}

		$result = array();

		if ( ! $this->is_smtp_activated() ) {
			wp_send_json_error(
				array(
					'error' => esc_html__( 'Plugin unavailable.', 'formidable' ),
				)
			);
		}

		$result['setup_status']  = (int) $this->is_smtp_configured();
		$result['license_level'] = wp_mail_smtp()->get_license_type();

		wp_send_json_success( $result );
	}

	/**
	 * Get $phpmailer instance.
	 *
	 * @since 4.04.04
	 *
	 * @return PHPMailer Instance of PHPMailer.
	 */
	protected function get_phpmailer() {

		global $phpmailer;

		if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			$phpmailer = new PHPMailer( true ); // phpcs:ignore
		}

		return $phpmailer;
	}

	/**
	 * Whether WP Mail SMTP plugin configured or not.
	 *
	 * @since 4.04.04
	 *
	 * @return bool True if some mailer is selected and configured properly.
	 */
	protected function is_smtp_configured() {

		if ( ! $this->is_smtp_activated() ) {
			return false;
		}

		$phpmailer = $this->get_phpmailer();

		$mailer             = \WPMailSMTP\Options::init()->get( 'mail', 'mailer' );
		$is_mailer_complete = wp_mail_smtp()->get_providers()->get_mailer( $mailer, $phpmailer )->is_mailer_complete();

		return 'mail' !== $mailer && $is_mailer_complete;
	}

	/**
	 * Whether WP Mail SMTP plugin active or not.
	 *
	 * @since 4.04.04
	 *
	 * @return bool True if SMTP plugin is active.
	 */
	protected function is_smtp_activated() {

		return function_exists( 'wp_mail_smtp' ) && ( is_plugin_active( $this->config['lite_plugin'] ) || is_plugin_active( $this->config['pro_plugin'] ) );
	}

	/**
	 * Redirect to SMTP settings page.
	 *
	 * @since 4.04.04
	 */
	public function redirect_to_smtp_settings() {

		// Redirect to SMTP plugin if it is activated.
		if ( $this->is_smtp_configured() ) {
			wp_safe_redirect( admin_url( $this->config['smtp_settings'] ) );
			exit;
		}
	}
}
