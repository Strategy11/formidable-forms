<?php
/**
 * @since 2.02.12
 */

class FrmShortcodeHelper {

	/**
	 * Get the shortcode attributes in key/value pairs from a string
	 *
	 * @since 2.02.12
	 * @param string $text
	 *
	 * @return array
	 */
	public static function get_shortcode_attribute_array( $text ) {
		$atts = array();
		if ( $text !== '' ) {
			$atts = shortcode_parse_atts( $text );
		}

		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		return $atts;
	}
}
