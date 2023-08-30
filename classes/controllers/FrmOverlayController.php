<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmOverlayController {

	private static $assets_handle_name = 'formidable-overlay';
	private $recurring_execution       = false;
	private $config                    = array();
	private $option_meta_name          = 'frm-overlay-options';
	private $options_data              = array();

	public function __construct( $config = array() ) {
		if ( ! isset( $config['config-option-name'] ) || ! isset( $config['execution-frequency'] ) ) {
			return;
		}

		$this->recurring_execution = true;
		$this->config              = $config;

		$this->get_options_data();
	}

	private function get_next_execution() {
		$next_timestamp = strtotime( '+' . $this->config['execution-frequency'], time() );
		return array(
			'timestamp' => $next_timestamp,
			'date'      => gmdate( 'Y-m-d H:i:s', $next_timestamp ),
		);
	}

	private function update_next_execution_time() {
		$config_option_name = $this->config['config-option-name'];

		$this->options_data[ $config_option_name ] = $this->get_next_execution();
		$this->update_options_data();
	}

	private function is_time_to_execute() {
		if ( ! isset( $this->options_data[ $this->config['config-option-name'] ] ) ) {
			return true;
		};

		$options = $this->options_data[ $this->config['config-option-name'] ];

		return ! ( isset( $options['timestamp'] ) && (int) $options['timestamp'] >= time() );
	}

	private function get_options_data() {
		$this->options_data = get_option( $this->option_meta_name, array() );
	}

	private function update_options_data() {
		update_option( $this->option_meta_name, $this->options_data );
	}

	public function open_overlay( $data = array() ) {
		if ( true === $this->recurring_execution ) {
			if ( false === $this->is_time_to_execute() ) {
				return;
			}
			$this->update_next_execution_time();
		};

		$this->enqueue_assets();
		$inline_script = 'frmOverlay.open(' . wp_json_encode( $data ) . ')';
		wp_add_inline_script( self::$assets_handle_name, $inline_script, 'after' );
	}

	public static function register_assets() {
		wp_register_script( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/js/formidable_overlay.js', array(), FrmAppHelper::plugin_version(), true );
		wp_register_style( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/css/frm_overlay.css', array(), FrmAppHelper::plugin_version() );
	}

	private function enqueue_assets() {
		wp_enqueue_style( self::$assets_handle_name );
		wp_enqueue_script( self::$assets_handle_name );
	}

}
