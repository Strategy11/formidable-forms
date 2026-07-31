<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Sanitize HTML attribute values to prevent stored XSS.
 *
 * @since x.x
 */
class FrmHtmlSanitizer {

	/**
	 * Sanitize href and src attribute values to valid URLs only.
	 *
	 * Decodes HTML entities in the attribute value before validating,
	 * so entity-encoded payloads are rejected.
	 *
	 * @since x.x
	 *
	 * @param string $value HTML string to process.
	 *
	 * @return string
	 */
	public static function sanitize_url_attributes( $value ) {
		if ( '' === $value || ( ! str_contains( $value, 'href' ) && ! str_contains( $value, 'src' ) ) ) {
			return $value;
		}

		return preg_replace_callback(
			'/\b(href|src)\s*=\s*"([^"]*)"/',
			array( self::class, 'sanitize_url_attribute_value' ),
			$value
		);
	}

	/**
	 * Callback to sanitize a single URL attribute match.
	 *
	 * @since x.x
	 *
	 * @param array $matches Regex matches with attribute name and value.
	 *
	 * @return string Rebuilt attribute with a safe URL value.
	 */
	private static function sanitize_url_attribute_value( $matches ) {
		$url = trim( html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' ) );

		if ( '#' === substr( $url, 0, 1 ) ) {
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
}
