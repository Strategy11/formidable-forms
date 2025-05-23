<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSquareLiteSettings {

	/**
	 * @var stdClass|null
	 */
	public $settings;

	public function __construct() {
		$this->set_default_options();
	}

	/**
	 * @return string
	 */
	public function param() {
		return 'square';
	}

	/**
	 * @return array
	 */
	public function default_options() {
		return array(
			'test_mode' => 1,
		);
	}

	/**
	 * @param mixed $settings
	 * @return void
	 */
	public function set_default_options( $settings = false ) {
		$default_settings = $this->default_options();

		if ( ! $settings ) {
			$settings = $this->get_options();
		} elseif ( $settings === true ) {
			$settings = new stdClass();
		}

		if ( ! isset( $this->settings ) ) {
			$this->settings = new stdClass();
		}

		foreach ( $default_settings as $setting => $default ) {
			if ( is_object( $settings ) && isset( $settings->{$setting} ) ) {
				$this->settings->{$setting} = $settings->{$setting};
			}

			if ( ! isset( $this->settings->{$setting} ) ) {
				$this->settings->{$setting} = $default;
			}
		}
	}

	public function get_options() {
		$settings = get_option( 'frm_' . $this->param() . '_options' );

		if ( is_object( $settings ) ) {
			$this->set_default_options( $settings );
		} elseif ( $settings ) {
			// Workaround for W3 total cache conflict.
			$this->settings = unserialize( serialize( $settings ) );
		} else {
			$this->set_default_options( true );
			$this->store();
		}

		return $this->settings;
	}

	/**
	 * @param array $params
	 * @return void
	 */
	public function update( $params ) {
		$settings = $this->default_options();

		foreach ( $settings as $setting => $default ) {
			if ( isset( $params[ 'frm_' . $this->param() . '_' . $setting ] ) ) {
				$this->settings->{$setting} = trim( sanitize_text_field( $params[ 'frm_' . $this->param() . '_' . $setting ] ) );
			}
		}

		$this->settings->test_mode = isset( $params[ 'frm_' . $this->param() . '_test_mode' ] ) ? absint( $params[ 'frm_' . $this->param() . '_test_mode' ] ) : 0;
	}

	/**
	 * @return void
	 */
	public function store() {
		// Save the posted value in the database.
		update_option( 'frm_' . $this->param() . '_options', $this->settings );
	}
}
