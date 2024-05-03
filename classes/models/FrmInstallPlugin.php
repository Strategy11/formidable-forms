<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 4.04.04
 */
class FrmInstallPlugin {

	/**
	 * Format: folder/filename.php.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * @var string
	 */
	protected $plugin_slug;

	public function __construct( $atts ) {
		$this->plugin_file   = $atts['plugin_file'];
		list( $slug, $file ) = explode( '/', $this->plugin_file );
		$this->plugin_slug   = $slug;
	}

	public function get_activate_link() {
		if ( $this->is_installed() && $this->is_active() ) {
			return '';
		}

		if ( $this->is_installed() ) {
			$url = $this->activate_url();
		} else {
			$url = $this->install_url();
		}
		return $url;
	}

	/**
	 * @return bool
	 */
	public function is_installed() {
		return is_dir( WP_PLUGIN_DIR . '/' . $this->plugin_slug );
	}

	/**
	 * @return bool
	 */
	public function is_active() {
		return is_plugin_active( $this->plugin_file );
	}

	/**
	 * @return string
	 */
	protected function install_url() {
		return wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $this->plugin_slug ), 'install-plugin_' . $this->plugin_slug );
	}

	/**
	 * @return string
	 */
	protected function activate_url() {
		return wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $this->plugin_file ), 'activate-plugin_' . $this->plugin_file );
	}

	/**
	 * Handles the AJAX request to install a plugin.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public static function ajax_install_plugin() {
		// Check permission and nonce.
		FrmAppHelper::permission_check( 'install_plugins' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		// Get posted data.
		$plugin_slug = FrmAppHelper::get_post_param( 'plugin', '', 'sanitize_text_field' );

		// Include necessary files for plugin installation.
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		// Get the plugin information.
		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin_slug,
				'fields' => array(
					'sections' => false,
				),
			)
		);
		if ( is_wp_error( $api ) ) {
			wp_send_json_error( $api->get_error_message() );
		}
		if ( ! FrmAddonsController::url_is_allowed( $api->versions['trunk'] ) ) {
			wp_send_json_error( 'This download is not allowed' );
		}

		// Set up the Plugin Upgrader.
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );

		// Install the plugin.
		$result = $upgrader->install( $api->versions['trunk'] );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Activate the plugin.
		$activate = activate_plugin( $upgrader->plugin_info() );
		if ( is_wp_error( $activate ) ) {
			wp_send_json_error( $activate->get_error_message() );
		}

		if ( 'wp-mail-smtp' === $plugin_slug ) {
			update_option( 'wp_mail_smtp_activation_prevent_redirect', true );
		}

		// Send a success response.
		wp_send_json_success( __( 'Plugin installed and activated successfully.', 'formidable' ) );
	}

	/**
	 * Checks plugin activation status via AJAX.
	 *
	 * @since 6.9
	 *
	 * @return void
	 */
	public static function ajax_check_plugin_activation() {
		// Check permission and nonce.
		FrmAppHelper::permission_check( 'install_plugins' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		// Retrieve plugin identifier.
		$plugin_path = FrmAppHelper::get_post_param( 'plugin_path', '', 'sanitize_text_field' );

		// Respond based on plugin status.
		if ( is_plugin_active( $plugin_path ) ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}
}
