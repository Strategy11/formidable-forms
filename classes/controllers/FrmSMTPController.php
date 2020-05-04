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

		wp_enqueue_script(
			'frm-admin-page-smtp',
			FrmAppHelper::plugin_url() . 'assets/js/components/admin/pages/smtp.js',
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
		FrmAppHelper::include_svg();
		$this->css();

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
			<div class="frm-smtp-logos">
			<?php
			FrmAppHelper::show_logo( $size );
			FrmAppHelper::icon_by_class(
				'frmfont frm_heart_solid_icon',
				array(
					'aria-label' => 'Loves',
					'style'      => 'width:30px;height:30px;margin:0 35px;',
					'color'      => '#d11c25',
				)
			);
			$this->stmp_logo();
			?>
			</div>
			<h1><?php esc_html_e( 'Making Email Deliverability Easy for WordPress', 'formidable' ); ?></h1>
			<p><?php esc_html_e( 'WP Mail SMTP allows you to easily set up WordPress to use a trusted provider to reliably send emails, including form notifications.', 'formidable' ); ?></p>
		</section>
		<?php
	}

	/**
	 * Generate and output screenshot section HTML.
	 *
	 * @since 4.04.04
	 */
	protected function output_section_screenshot() {

		printf(
			'<section class="screenshot">
				<div class="cont">
					<img src="%1$s" alt="%2$s"/>
				</div>
				<ul>
					<li>%3$s %4$s</li>
					<li>%3$s %5$s</li>
					<li>%3$s %6$s</li>
					<li>%3$s %7$s</li>
				</ul>			
			</section>',
			esc_url( FrmAppHelper::plugin_url() . '/images/smtp-screenshot-tnail.png' ),
			esc_attr__( 'WP Mail SMTP screenshot', 'formidable' ),
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 492 492" width="14" height="14"><path d="M484 227L306 49a27 27 0 00-38 0l-16 16a27 27 0 000 38l104 105H27c-15 0-27 11-27 26v23c0 15 12 27 27 27h330L252 389a27 27 0 000 38l16 16a27 27 0 0038 0l178-178a27 27 0 000-38z" fill="#5bbfa5"/></svg> &nbsp;',
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

		$icon = FrmAppHelper::icon_by_class(
			'frmfont ' . $step['icon'],
			array(
				'aria-label' => __( 'Step 1', 'formidable' ),
				'echo'       => false,
				'style'      => 'width:50px;height:50px;',
			)
		);

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
			// WPCS: XSS ok.
			FrmAppHelper::kses( $icon, array( 'a', 'i', 'span', 'use', 'svg' ) ),
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

		$icon = FrmAppHelper::icon_by_class(
			'frmfont ' . $step['icon'],
			array(
				'aria-label' => __( 'Step 2', 'formidable' ),
				'echo'       => false,
				'style'      => 'width:50px;height:50px;',
			)
		);

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
			// WPCS: XSS ok.
			FrmAppHelper::kses( $icon, array( 'a', 'i', 'span', 'use', 'svg' ) ),
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

	private function stmp_logo() {
		?>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" height="90" width="90"><defs><style>.cls-11,.cls-12{fill-rule:evenodd}.cls-4{fill:none}.cls-11{fill:#86a196}.cls-12{fill:#fff}</style></defs><path class="cls-4" d="M-6.3 0h60v60h-60z"/><path d="M16.7 8.1a15.4 15.4 0 00-8 10.2 23.5 23.5 0 1030 0 15.4 15.4 0 00-9.3-10.8 3.4 3.4 0 00-2.1-2.7A4.6 4.6 0 0018.4 3a24.4 24.4 0 00-1.7 5z" fill="#395360" fill-rule="evenodd"/><path fill="#fbaa6f" d="M18 26h12v14H18z"/><path d="M25.9 33.2l-.1-.1a1.4 1.4 0 111.6-2.3 1.9 1.9 0 00-1.2.8 1.9 1.9 0 00-.3 1.6zm-4.5 0a1.8 1.8 0 00-.4-1.6 2 2 0 00-1.2-.8 1.4 1.4 0 011.6 2.3zm7.2-3.2h.5l-1 4.8-2.2 6.5h-4.3l-3.2-5.4 1.1-3.2 2.1 2.7c.6.5 2.7.5 3.8-.6a26.2 26.2 0 003.2-4.8z" fill="#dc7f3c" fill-rule="evenodd"/><path d="M9.7 29H15v-9h-4a13 13 0 017.4-10q1.2-5 2.8-6.8l.1-.1.1-.1a2.3 2.3 0 011.1-.5 2.3 2.3 0 012.2 3.8 1.6 1.6 0 01-.4.3A15 15 0 0023 8a5 5 0 013-1.5 1.4 1.4 0 01.7.2 1.3 1.3 0 01.5 1.8 1.3 1.3 0 01-.6.6 13 13 0 0110.1 11l.1.8H33v8h4.8l1.8 13.4q-6.3 4-15.8 4T8 42.4zM25 38.4q3.8-6.4 3.8-7.6c0-2.2-3.2-4-4.8-4s-4.9 1.7-4.9 4q0 1.2 3.8 7.6a1.2 1.2 0 001 .6 1 1 0 001-.6z" fill="#bdcfc8" fill-rule="evenodd"/><path class="cls-4" d="M19 31h9.6L27 47.2h-6.4l-1.6-16z"/><path d="M39.8 48.8a20 20 0 01-32 0l.8-6a2.7 2.7 0 001 .1 2.8 2.8 0 002.8-2.4v1.2a2.8 2.8 0 005.6 0v1.6a2.9 2.9 0 005.7 0 2.8 2.8 0 005.7 0v-1.6a2.8 2.8 0 105.7 0v-1.2A2.8 2.8 0 0038 43a2.9 2.9 0 001-.2l.8 6z" fill="#809eb0" fill-rule="evenodd"/><path d="M8.3 44.6l.3-1.8a2.7 2.7 0 001 .2 2.8 2.8 0 002.8-2.5v1.2a2.8 2.8 0 005.7 0v1.7a2.9 2.9 0 005.6 0 2.8 2.8 0 005.7 0v-1.7a2.8 2.8 0 105.7 0v-1.2A2.8 2.8 0 0038 43a2.9 2.9 0 001-.2l.3 2a2.9 2.9 0 01-4.1-2.2v1.2a2.8 2.8 0 11-5.7 0v1.6a2.8 2.8 0 01-5.7 0 2.9 2.9 0 01-5.7 0v-1.7a2.8 2.8 0 01-5.7 0v-1.2A2.8 2.8 0 019.6 45a2.9 2.9 0 01-1.3-.3z" fill="#738e9e" fill-rule="evenodd"/><path class="cls-11" d="M37.8 22.4c-1-2.9-3-4.7-4.7-4.5-2.2.2-2.8 3.7-2.3 8s1.7 7.5 3.9 7.3 4-3.9 3.6-8c0 1.2-.5 2.3-1.3 2.4-1.2 0-1.5-1.2-1.6-2.9s-.2-3 1-3a1.5 1.5 0 011.4.7z"/><path class="cls-12" d="M37 21.8c-.6-1.3-1.5-2-2.4-1.9-1.5.1-1.9 2.6-1.6 5.5s1.2 5.1 2.7 5c1.1-.2 2-1.5 2.2-3.4a1.2 1.2 0 01-1 .6c-1 0-1.4-1.2-1.5-2.9s-.1-3 1-3a1.6 1.6 0 01.6 0z"/><path class="cls-11" d="M9.6 22.4c1-2.9 3-4.7 4.7-4.5 2.2.2 2.8 3.7 2.3 8s-1.7 7.5-3.9 7.3-4-3.9-3.7-8c.1 1.2.5 2.3 1.4 2.4 1.1 0 1.5-1.2 1.6-2.9s.1-3-1-3a1.5 1.5 0 00-1.4.7z"/><path class="cls-12" d="M10.4 21.8c.6-1.3 1.5-2 2.4-1.9 1.5.1 1.8 2.6 1.5 5.5s-1.1 5.1-2.6 5c-1.1-.2-2-1.5-2.2-3.4a1.2 1.2 0 00.9.6c1.1 0 1.4-1.2 1.6-2.9s.1-3-1-3a1.7 1.7 0 00-.7 0z"/><path d="M19 28.6a5.3 5.3 0 010-.7c0-2.4 1.2-5.2 4.9-5.2s4.8 2.8 4.8 5.2a4.4 4.4 0 010 1c-.9-1.3-2.4-2.1-4.9-2.1-2.4 0-3.9.7-4.8 1.8z" fill="#f4f8ff" fill-rule="evenodd"/><path class="cls-11" d="M26.5 9.2L23.3 9l4-1.2a1.4 1.4 0 01-.8 1.4zm-3.5-1l-1.3 1a16.8 16.8 0 002-3.8 6.6 6.6 0 00.3-2.7A2.4 2.4 0 0125.2 5a2.4 2.4 0 01-.7 1.5A15 15 0 0023 8.1z"/></svg>
		<?php
	}

	private function css() {
		?>
<style>
#frm-admin-smtp *, #frm-admin-smtp *::before, #frm-admin-smtpp *::after {
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}
#frm-admin-smtp{
	width: 700px;
	margin: 0 auto;
}
#frm-admin-smtp p {
	font-size: 15px;
}
#frm-admin-smtp section{
	margin: 50px 0;
	text-align: left;
	clear: both;
}
#frm-admin-smtp .top{
	text-align: center;
}
.frm-smtp-logos {
	margin-bottom: 38px;
}
.frm-smtp-logos svg {
	vertical-align: middle;
}
#frm-admin-smtp .top h1 {
	font-size: 26px;
	font-weight: 600;
	margin-bottom: 0;
	padding: 0;
}
#frm-admin-smtp .top p {
	font-size: 17px;
	color: #777;
	margin-top: .5em;
}
#frm-admin-smtp .screenshot ul {
	display: inline-block;
	margin: 0 0 0 30px;
	list-style-type: none;
	max-width: calc(100% - 350px);
}
#frm-admin-smtp .screenshot li {
	margin: 16px 0;
	padding: 0;
	font-size: 15px;
	color: #777;
}
#frm-admin-smtp .screenshot .cont img {
	max-width: 100%;
	display: block;
}
#frm-admin-smtp .screenshot .cont {
	display: inline-block;
	position: relative;
	width: 315px;
	padding: 5px;
	background-color: #fff;
	border-radius: 3px;
}
#frm-admin-smtp .step,
#frm-admin-smtp .screenshot .cont {
	-webkit-box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
	-moz-box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
	box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
}
#frm-admin-smtp .step {
	background-color: #F9F9F9;
	border: 1px solid #E5E5E5;
	margin: 0 0 25px;
}
#frm-admin-smtp .screenshot > *,
#frm-admin-smtp .step > * {
	vertical-align: middle;
}
#frm-admin-smtp .step p {
	font-size: 16px;
	color: #777777;
}
#frm-admin-smtp .step .num {
	display: inline-block;
	position: relative;
	width: 100px;
	height: 50px;
	text-align: center;
}
#frm-admin-smtp .step div {
	display: inline-block;
	width: calc(100% - 104px);
	background-color: #fff;
	padding: 30px;
	border-left: 1px solid #eee;
}
#frm-admin-smtp .grey {
	opacity: 0.5;
}
#frm-admin-smtp .step h2 {
	font-size: 24px;
	line-height: 22px;
	margin-top: 0;
	margin-bottom: 15px;
}
</style>
		<?php
	}
}
