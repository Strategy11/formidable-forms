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
		// ENT_HTML5 is required so named entities like &colon; and &Tab; are decoded too. Without it a
		// payload like javascript&colon;alert(1) never reveals its scheme to the checks below.
		$url = trim( html_entity_decode( $matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );

		if ( self::has_markup_characters( $url ) ) {
			return $matches[1] . '=""';
		}

		// A drawn signature arrives as a PNG data URI. esc_url() drops it, because data is not one of
		// the protocols WordPress allows, so the value is matched against a strict base64 pattern first.
		if ( 'src' === $matches[1] && self::is_png_data_uri( $url ) ) {
			return $matches[1] . '="' . esc_attr( $url ) . '"';
		}

		// esc_url() returns anything starting with a slash unchanged, without consulting the allowed
		// protocols, so a protocol relative value pointing at another host has to be rejected here.
		if ( preg_match( '#^[/\\\\]{2}#', $url ) ) {
			return $matches[1] . '=""';
		}

		$safe = self::is_relative_url( $url ) ? self::esc_relative_url( $url ) : esc_url( $url, array( 'http', 'https', 'mailto', 'tel' ) );

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
	 * Check the decoded value for characters that belong to markup rather than to a URL.
	 *
	 * The value is decoded before the checks below run, so an entity encoded payload like
	 * &lt;img src=x onerror=alert(1)&gt; arrives here as real markup. Escaping alone would hand that
	 * payload straight back in its encoded form, and anything that decodes the attribute a second time
	 * would then get a live tag. RFC 3986 requires these characters to be percent encoded in a URL, so
	 * a value carrying them raw is not a URL and is dropped instead of escaped.
	 *
	 * @since x.x
	 *
	 * @param string $url Decoded URL value to check.
	 *
	 * @return bool
	 */
	private static function has_markup_characters( $url ) {
		return 1 === preg_match( '#[<>"\'`\x00-\x1F\x7F]#', $url );
	}

	/**
	 * Check for a relative URL, like a site path or a file name beside the current page.
	 *
	 * A relative URL carries no scheme, so it cannot hold a javascript: or data: payload. A scheme ends
	 * at the first colon, and that colon has to come before the first slash, question mark, or hash, so
	 * anything with a colon in that leading segment is treated as absolute and handed to esc_url()
	 * instead. Protocol relative values are rejected by the caller before this runs.
	 *
	 * @since x.x
	 *
	 * @param string $url Decoded URL value to check.
	 *
	 * @return bool
	 */
	private static function is_relative_url( $url ) {
		if ( '' === $url ) {
			return false;
		}

		return ! str_contains( substr( $url, 0, strcspn( $url, '/?#' ) ), ':' );
	}

	/**
	 * Run a relative URL through esc_url() without letting it become an absolute one.
	 *
	 * Core prepends http:// to a value that has no scheme, which would turn a path like hello.png
	 * into a link to another host. That step is skipped when the value starts with a slash, so one is
	 * added for the call and removed from the result. The reason to route through esc_url() at all is
	 * its character filter, which strips the markup and control characters that have no place in a URL.
	 *
	 * @since x.x
	 *
	 * @param string $url Decoded relative URL.
	 *
	 * @return string
	 */
	private static function esc_relative_url( $url ) {
		$safe = esc_url( '/' . $url );
		return '' === $safe ? '' : substr( $safe, 1 );
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
