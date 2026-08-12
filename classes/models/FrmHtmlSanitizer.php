<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Sanitize HTML attribute values to prevent stored XSS.
 *
 * @since 6.34
 */
class FrmHtmlSanitizer {

	/**
	 * Sanitize href and src attribute values to valid URLs only.
	 *
	 * Decodes HTML entities in the attribute value before validating,
	 * so entity-encoded payloads are rejected.
	 *
	 * @since 6.34
	 *
	 * @param string $value HTML string to process.
	 *
	 * @return string
	 */
	public static function sanitize_url_attributes( $value ) {
		if ( '' === $value || ( ! str_contains( $value, 'href' ) && ! str_contains( $value, 'src' ) ) ) {
			return $value;
		}

		$sanitized = preg_replace_callback(
			'/\b(href|src)\s*=\s*"([^"]*)"/',
			array( self::class, 'sanitize_url_attribute_value' ),
			$value
		);

		return $sanitized ?? '';
	}

	/**
	 * Callback to sanitize a single URL attribute match.
	 *
	 * @since 6.34
	 *
	 * @param array $matches Regex matches with attribute name and value.
	 *
	 * @return string Rebuilt attribute with a safe URL value.
	 */
	private static function sanitize_url_attribute_value( $matches ) {
		$url = trim( html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' ) );

		if ( str_starts_with( $url, '#' ) ) {
			return $matches[1] . '="' . esc_attr( $url ) . '"';
		}

		if ( 'src' === $matches[1] && self::is_png_data_uri( $url ) ) {
			return $matches[1] . '="' . esc_attr( $url ) . '"';
		}

		if ( ! preg_match( '/^(https?:\/\/|mailto:|tel:)/i', $url ) ) {
			return $matches[1] . '=""';
		}

		$safe = esc_url( $url, array( 'http', 'https', 'mailto', 'tel' ) );

		if ( '' === $safe ) {
			return $matches[1] . '=""';
		}

		$host = wp_parse_url( $safe, PHP_URL_HOST );

		if ( $host && preg_match( '/%[0-9a-f]{2}/i', $host ) ) {
			return $matches[1] . '=""';
		}

		return $matches[1] . '="' . esc_attr( $safe ) . '"';
	}

	/**
	 * Check for a PNG data URI that contains only base64 characters, like a drawn signature image src.
	 *
	 * The pattern allows only base64 characters after the prefix, so the value cannot carry a media
	 * type of its own or any markup into the attribute.
	 *
	 * @since 6.34
	 *
	 * @param string $url Decoded URL value to check.
	 *
	 * @return bool
	 */
	private static function is_png_data_uri( $url ) {
		return 1 === preg_match( '#^data:image/png;base64,[A-Za-z0-9+/]+={0,2}$#D', $url );
	}
}
