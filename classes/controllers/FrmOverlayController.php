<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmOverlayController {

	/**
	 * Handle name used for registering controller scripts and style.
	 *
	 * @var string Handle name used for wp_register_script|wp_register_style
	 */
	public static $assets_handle_name = 'formidable-overlay';

	/**
	 * Check if the overlay will run again periodically.
	 *
	 * @var bool If the condition is true, the overlay will execute only following a specified time interval.
	 */
	private $recurring_execution = false;

	/**
	 * The controller configs passed through construct. If empty the overlay will run normaly, it will open every time when open_overlay is called.
	 *
	 * @var array Config {
	 *
	 *     @type string $execution-frequency Example values: '1 day', '10 days', '1 week', etc.
	 *     @type string $config-option-name A handle name that will be used to store the controller options_data.
	 *
	 * }
	 */
	private $config = array();

	/**
	 * The WordPress option meta name that will store the overlay options.
	 *
	 * @var string
	 */
	private $option_meta_name = 'frm-overlay-options';

	/**
	 * The controller data. It will handle the options from multiple instances of the controller
	 *
	 * @var array Option data {
	 *     @type array $options_data[ '[instance1-option_name_provided_via-->config-option-name]' ] {
	 *       @type string $execution-frequency Values example: '1 day' | '10 days' | '1 week' | '10 weeks' | '1 month' | '10 months' | '1 year' | '10 years'
	 *     }
	 *
	 *     @type array $options_data[ '[instance2-option_name_provided_via-->config-option-name]' ] {
	 *       @type string $execution-frequency Values example: '1 day' | '10 days' | '1 week' | '10 weeks' | '1 month' | '10 months' | '1 year' | '10 years'
	 *     }
	 * }
	 */
	private $options_data = array();

	public function __construct( $config = array() ) {
		if ( ! isset( $config['config-option-name'] ) || ! isset( $config['execution-frequency'] ) ) {
			return;
		}

		$this->recurring_execution = true;
		$this->config              = $config;

		$this->get_options_data();
	}

	/**
	 * Get next execution time. Return the next execution timestamp and date.
	 *
	 * @return array
	 */
	private function get_next_execution() {
		$next_timestamp = strtotime( '+' . $this->config['execution-frequency'], $this->get_time() );
		return array(
			'timestamp' => $next_timestamp,
			'date'      => gmdate( 'Y-m-d H:i:s', $next_timestamp ),
		);
	}

	/**
	 * Revise the $options_data by incorporating information about the upcoming execution time, then proceed to store it in the database.
	 *
	 * @return void
	 */
	private function update_next_execution_time() {
		$config_option_name = $this->config['config-option-name'];

		$this->options_data[ $config_option_name ] = $this->get_next_execution();
		$this->update_options_data();
	}

	/**
	 * Get the time.
	 *
	 * @return int
	 */
	protected function get_time() {
		return time();
	}

	/**
	 * Ascertain whether the overlay needs to be executed or not.
	 *
	 * @return bool
	 */
	private function is_time_to_execute() {
		if ( ! isset( $this->options_data[ $this->config['config-option-name'] ] ) ) {
			return true;
		}

		$options = $this->options_data[ $this->config['config-option-name'] ];

		return ! ( isset( $options['timestamp'] ) && (int) $options['timestamp'] > $this->get_time() );
	}

	/**
	 * Get the $options_data.
	 *
	 * @return void
	 */
	private function get_options_data() {
		$this->options_data = get_option( $this->option_meta_name, array() );
	}

	/**
	 * Save the $options_data to db.
	 *
	 * @return void
	 */
	private function update_options_data() {
		update_option( $this->option_meta_name, $this->options_data, 'no' );
	}

	/**
	 * Open overlay.
	 *
	 * @param array $data {
	 *    An array containing data for the overlay.
	 *
	 *    @type string $hero_image URL of the hero image.
	 *    @type string $heading Heading of the overlay.
	 *    @type string $copy Copy/content of the overlay.
	 *    @type array  $buttons Array of button arrays.
	 *       @type string $buttons[]['url'] URL for the button.
	 *       @type string $buttons[]['target'] Target attribute for the button link.
	 *       @type string $buttons[]['label'] Label/text of the button.
	 * }
	 * @return bool
	 */
	public function open_overlay( $data = array() ) {
		if ( true === $this->recurring_execution ) {
			if ( false === $this->is_time_to_execute() ) {
				return false;
			}
			$this->update_next_execution_time();
		}

		$this->enqueue_assets();
		$inline_script = 'frmOverlay.open(' . wp_json_encode( $data ) . ')';
		wp_add_inline_script( self::$assets_handle_name, $inline_script, 'after' );

		return true;
	}

	/**
	 * Register controller assets.
	 *
	 * @return void
	 */
	public static function register_assets() {
		wp_register_script( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/js/formidable_overlay.js', array(), FrmAppHelper::plugin_version(), true );
		wp_register_style( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/css/frm_overlay.css', array(), FrmAppHelper::plugin_version() );
	}

	/**
	 * Enqueue controller assets.
	 *
	 * @return void
	 */
	private function enqueue_assets() {
		wp_enqueue_style( 'formidable-animations' );
		wp_enqueue_style( self::$assets_handle_name );
		wp_enqueue_script( self::$assets_handle_name );
	}
}
