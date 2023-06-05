<?php

#[\AllowDynamicProperties]
class FrmStrpLiteSettings {

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
		return 'strp';
	}

	/**
	 * @return array
	 */
	public function default_options() {
		return array(
			'test_mode'    => 1,
			'process'      => 'after',
			'live_secret'  => '',
			'live_publish' => '',
			'test_secret'  => '',
			'test_publish' => '',
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

		if ( ! is_object( $settings ) ) {
			if ( $settings ) { // workaround for W3 total cache conflict
				$settings = unserialize( serialize( $settings ) );
			} else {
				// If unserializing didn't work
				if ( ! is_object( $settings ) ) {
					if ( $settings ) {
						// workaround for W3 total cache conflict
						$settings = unserialize( serialize( $settings ) );
					} else {
						// TODO This line is wrong. set_default_options returns void so we shouldn't be setting $settings.
						$settings = $this->set_default_options( true );
					}
					$this->store();
				}
			}
		} else {
			$this->set_default_options( $settings );
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

		$this->settings->test_mode = isset( $params['frm_strp_test_mode'] ) ? absint( $params['frm_strp_test_mode'] ) : 0;
	}

	/**
	 * @return void
	 */
	public function store() {
		// Save the posted value in the database
		update_option( 'frm_' . $this->param() . '_options', $this->settings );
	}

	/**
	 * @return string
	 */
	public function get_active_publishable_key() {
		return $this->settings->test_mode ? $this->get_frm_publishable_test_key() : $this->get_frm_publishable_live_key();
	}

	/**
	 * @return string
	 */
	private function get_frm_publishable_test_key() {
		return 'pk_test_51I8ZwGAifsV2HHSa8NKsdEEVTa8o69bZykSf1zM3OsonXFblw7mEuNkyYUjLgGwYrgF95CmpkJSJtaWyrQNKaMMJ00y2Q7Y0jz';
	}

	/**
	 * @return string
	 */
	private function get_frm_publishable_live_key() {
		return 'pk_live_51I8ZwGAifsV2HHSa0jgLD6S16izScuihE2WtExBWzbyBsawOazS9cjt1aFyBsdSuK9nYwDD7Vh7LUOoa0Evb7Evb00yVEpTIJL';
	}
}
