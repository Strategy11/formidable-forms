<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Track form state in an encrypted form field.
 * The state just holds some basic info, like if a [formidable] shortcode loaded
 * with a title=1 or description=1 option.
 *
 * @since 6.2
 */
class FrmFormState {

	/**
	 * @var FrmFormState
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private $state;

	private function __construct() {
		$this->state = array();
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return void
	 */
	public static function set_initial_value( $key, $value ) {
		if ( is_callable( 'FrmProFormState::set_initial_value' ) ) {
			// Let Pro handle state.
			return;
		}

		self::maybe_initialize();
		self::$instance->set( $key, $value );
	}

	/**
	 * @return bool true if just initialized.
	 */
	private static function maybe_initialize() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
			return true;
		}
		return false;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return void
	 */
	public function set( $key, $value ) {
		$this->state[ $key ] = $value;
	}

	/**
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public static function get_from_request( $key, $default ) {
		if ( self::maybe_initialize() ) {
			self::get_state_from_request();
		}
		return self::$instance->get( $key, $default );
	}

	public function get( $key, $default ) {
		if ( isset( $this->state[ $key ] ) ) {
			return $this->state[ $key ];
		}
		return $default;
	}

	/**
	 * Render a basic version of the state field from Pro.
	 * This is required only when submitting with AJAX.
	 * It is used to track the value of a title=1|0 or description=1|0 option in a [formidable] shortcode.
	 *
	 * @param stdClass $form
	 * @return void
	 */
	public static function maybe_render_state_field( $form ) {
		if ( is_callable( 'FrmProFormState::maybe_render_state_field' ) ) {
			// Let Pro handle state when Pro is available.
			// This way we can also avoid duplicate state fields if Pro isn't up to date.
			return;
		}

		if ( empty( $form->options['ajax_submit'] ) ) {
			// This is only required for AJAX submit.
			return;
		}

		if ( empty( self::$instance ) && ! self::get_state_from_request() ) {
			return;
		}

		$state_title          = ! empty( self::$instance->state['title'] ) ? 1 : 0;
		$state_description    = ! empty( self::$instance->state['description'] ) ? 1 : 0;
		$settings_title       = ! empty( $form->options['show_title'] ) ? 1 : 0;
		$settings_description = ! empty( $form->options['show_description'] ) ? 1 : 0;

		if ( $state_title === $settings_title && $state_description === $settings_description ) {
			// Avoid state field if it matches form settings.
			return;
		}

		self::$instance->render_state_field();
	}

	/**
	 * @return bool true if there is valid state data in the request.
	 */
	private static function get_state_from_request() {
		$encrypted_state = FrmAppHelper::get_post_param( 'frm_state', '', 'sanitize_text_field' );
		if ( ! $encrypted_state ) {
			return false;
		}
		$secret          = self::get_encryption_secret();
		$decrypted_state = openssl_decrypt( $encrypted_state, 'AES-128-ECB', $secret );
		if ( false === $decrypted_state ) {
			return false;
		}
		$decoded_state = json_decode( $decrypted_state, true );
		if ( ! is_array( $decoded_state ) ) {
			return false;
		}
		foreach ( $decoded_state as $key => $value ) {
			self::set_initial_value( self::decompressed_key( $key ), $value );
		}
		return true;
	}

	/**
	 * @return void
	 */
	public function render_state_field() {
		if ( ! $this->state && ! self::get_state_from_request() ) {
			return;
		}
		$state_string = $this->get_state_string();
		echo '<input name="frm_state" type="hidden" value="' . esc_attr( $state_string ) . '" />';
	}

	/**
	 * @return string
	 */
	private function get_state_string() {
		$secret           = self::get_encryption_secret();
		$compressed_state = $this->compressed_state();
		$json_encoded     = json_encode( $compressed_state );
		$encrypted        = openssl_encrypt( $json_encoded, 'AES-128-ECB', $secret );
		return $encrypted;
	}

	/**
	 * Return state but with shorter keys to use for the state string.
	 *
	 * @return array
	 */
	private function compressed_state() {
		$compressed = array();
		foreach ( $this->state as $key => $value ) {
			$compressed[ self::compressed_key( $key ) ] = $value;
		}
		return $compressed;
	}

	/**
	 * Get the first character of a key to make the option take less space.
	 * "title" => "t".
	 * "description" => "d".
	 *
	 * @param string $key
	 * @return string
	 */
	private static function compressed_key( $key ) {
		return $key[0];
	}

	/**
	 * Keys are truncated to a single character to make the state string smaller.
	 * Pro supports additional keys include "i" for include_fields and "g" for get params.
	 * To avoid conflicts, we should not add "i" or "g" in Lite for another state property.
	 *
	 * @param string $key
	 * @return string The full key name if one is found. If nothing is found, the $key param is passed back.
	 */
	private static function decompressed_key( $key ) {
		switch ( $key ) {
			case 'd':
				return 'description';
			case 't':
				return 'title';
		}
		return $key;
	}

	/**
	 * @return string
	 */
	private static function get_encryption_secret() {
		$secret_key = get_option( 'frm_form_state_key' );

		// If we already have the secret, send it back.
		if ( false !== $secret_key ) {
			return base64_decode( $secret_key ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		}

		// We don't have a secret, so let's generate one.
		$secret_key = is_callable( 'sodium_crypto_secretbox_keygen' ) ? sodium_crypto_secretbox_keygen() : wp_generate_password( 32, true, true );
		update_option( 'frm_form_state_key', base64_encode( $secret_key ), 'no' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		return $secret_key;
	}
}
