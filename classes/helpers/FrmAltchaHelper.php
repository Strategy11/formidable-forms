<?php
/**
 * Shared ALTCHA settings and paths.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * ALTCHA helpers.
 *
 * @since x.x
 */
class FrmAltchaHelper {

	/**
	 * Option holding the global ALTCHA settings.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SETTINGS_OPTION = 'frm_altcha_settings';

	/**
	 * Proof-of-work difficulty presets. Each value is the largest counter a challenge may hide.
	 *
	 * @since x.x
	 *
	 * @return array<string, int>
	 */
	public static function difficulty_levels() {
		return array(
			'low'    => 8000,
			'medium' => 20000,
			'high'   => 60000,
		);
	}

	/**
	 * Labels for the difficulty presets.
	 *
	 * @since x.x
	 *
	 * @return array<string, string>
	 */
	public static function difficulty_labels() {
		return array(
			'low'    => __( 'Low (fastest for visitors)', 'formidable' ),
			'medium' => __( 'Medium (recommended)', 'formidable' ),
			'high'   => __( 'High (strongest, slower on old phones)', 'formidable' ),
		);
	}

	/**
	 * Labels for the widget's automatic verification modes.
	 *
	 * @since x.x
	 *
	 * @return array<string, string>
	 */
	public static function auto_modes() {
		return array(
			'onfocus'  => __( 'When the visitor starts filling out the form (recommended)', 'formidable' ),
			'onload'   => __( 'As soon as the form loads', 'formidable' ),
			'onsubmit' => __( 'When the form is submitted', 'formidable' ),
			'off'      => __( 'Only when the visitor clicks the checkbox', 'formidable' ),
		);
	}

	/**
	 * Visitor-facing widget layouts supported by the bundled library.
	 *
	 * @since x.x
	 *
	 * @return array<string, string>
	 */
	public static function display_modes() {
		return array(
			'standard' => __( 'Inline (default)', 'formidable' ),
			'floating' => __( 'Floating near the Submit button', 'formidable' ),
			'overlay'  => __( 'Overlay', 'formidable' ),
			'bar'      => __( 'Bar at the bottom of the screen', 'formidable' ),
		);
	}

	/**
	 * Interaction styles supported by the bundled widget.
	 *
	 * @since x.x
	 *
	 * @return array<string, string>
	 */
	public static function widget_types() {
		return array(
			'checkbox' => __( 'Checkbox (default)', 'formidable' ),
			'switch'   => __( 'Toggle switch', 'formidable' ),
			'native'   => __( 'Browser checkbox', 'formidable' ),
		);
	}

	/**
	 * Default settings.
	 *
	 * @since x.x
	 *
	 * @return array{difficulty: string, expires: int, auto: string, hide_footer: int, display: string, widget_type: string, hide_logo: int}
	 */
	public static function default_settings() {
		return array(
			'difficulty'  => 'medium',
			'expires'     => 20,
			'auto'        => 'onfocus',
			'hide_footer' => 0,
			'display'     => 'standard',
			'widget_type' => 'checkbox',
			'hide_logo'   => 0,
		);
	}

	/**
	 * Saved settings, validated and merged with the defaults.
	 *
	 * @since x.x
	 *
	 * @return array{difficulty: string, expires: int, auto: string, hide_footer: int, display: string, widget_type: string, hide_logo: int}
	 */
	public static function get_settings() {
		$saved = get_option( self::SETTINGS_OPTION, array() );
		return self::sanitize_settings( is_array( $saved ) ? $saved : array() );
	}

	/**
	 * Clamp raw settings to allowed values, falling back to the defaults.
	 *
	 * @since x.x
	 *
	 * @param array $raw Unsanitized settings.
	 *
	 * @return array{difficulty: string, expires: int, auto: string, hide_footer: int, display: string, widget_type: string, hide_logo: int}
	 */
	public static function sanitize_settings( $raw ) {
		$defaults = self::default_settings();
		$expires  = isset( $raw['expires'] ) && is_numeric( $raw['expires'] ) ? (int) $raw['expires'] : $defaults['expires'];

		return array(
			'difficulty'  => self::sanitize_choice( $raw['difficulty'] ?? '', self::difficulty_levels(), $defaults['difficulty'] ),
			'expires'     => max( 1, min( 1440, $expires ) ),
			'auto'        => self::sanitize_choice( $raw['auto'] ?? '', self::auto_modes(), $defaults['auto'] ),
			'hide_footer' => 1 === (int) ( $raw['hide_footer'] ?? 0 ) ? 1 : 0,
			'display'     => self::sanitize_choice( $raw['display'] ?? '', self::display_modes(), $defaults['display'] ),
			'widget_type' => self::sanitize_choice( $raw['widget_type'] ?? '', self::widget_types(), $defaults['widget_type'] ),
			'hide_logo'   => in_array( $raw['hide_logo'] ?? 0, array( 1, '1', true ), true ) ? 1 : 0,
		);
	}

	/**
	 * Validate a setting against its allowed choices.
	 *
	 * @since x.x
	 *
	 * @param mixed                     $value   Unsanitized setting value.
	 * @param array<string, int|string> $choices Allowed setting choices.
	 * @param string                    $default Default setting value.
	 *
	 * @return string
	 */
	private static function sanitize_choice( $value, $choices, $default ) {
		return is_string( $value ) && array_key_exists( $value, $choices ) ? $value : $default;
	}

	/**
	 * URL the widget fetches a fresh challenge from.
	 *
	 * An admin-ajax endpoint is used rather than a REST route, because security plugins
	 * often block the REST API for visitors who are not logged in.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function challenge_url() {
		return add_query_arg( 'action', 'frm_altcha_challenge', admin_url( 'admin-ajax.php' ) );
	}
}
