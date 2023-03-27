<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 4.04.04
 */
class FrmInstallPlugin {

	protected $plugin_file; // format: folder/filename.php
	protected $plugin_slug;

	public function __construct( $atts ) {
		$this->plugin_file = $atts['plugin_file'];
		list( $slug, $file ) = explode( '/', $this->plugin_file );
		$this->plugin_slug = $slug;
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
}
