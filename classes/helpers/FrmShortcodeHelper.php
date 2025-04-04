<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 2.02.12
 */
class FrmShortcodeHelper {

	/**
	 * Get the shortcode attributes in key/value pairs from a string
	 *
	 * @since 2.02.12
	 *
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

	/**
	 * Returns shortcodes that are shown/hidden based on the context.
	 *
	 * @since 6.16.3
	 * @return array
	 */
	public static function get_contextual_shortcodes() {
		return array(
			'address' => array(
				'admin_email'        => __( 'Admin email', 'formidable' ),
				'default-from-email' => __( 'Default from email', 'formidable' ),
				'default-email'      => __( 'Default email', 'formidable' ),
			),
			'body'    => array(
				'default-message' => __( 'Default Msg', 'formidable' ),
				'default-html'    => __( 'Default HTML', 'formidable' ),
				'default-plain'   => __( 'Default Plain', 'formidable' ),
			),
		);
	}

	/**
	 * Get contextual shortcodes.
	 *
	 * @since 6.16.3
	 *
	 * @return array
	 */
	public static function get_contextual_shortcode_values() {
		$contextual_shortcodes = self::get_contextual_shortcodes();
		return array_merge( $contextual_shortcodes['address'], $contextual_shortcodes['body'] );
	}

	/**
	 * Get flattened format of contextual shortcodes.
	 *
	 * @since 6.16.3
	 *
	 * @return array
	 */
	public static function get_contextual_codes() {
		$contextual_shortcodes = self::get_contextual_shortcodes();
		$result                = array();
		foreach ( $contextual_shortcodes as $type => $shortcodes ) {
			$result[ $type ] = array_keys( $shortcodes );
		}
		return $result;
	}

	/**
	 * Get the name of the shortcode from the regEx
	 *
	 * @since 3.0
	 *
	 * @param array $shortcodes
	 * @param int   $short_key  The position in the shortcodes array.
	 * @param array $args
	 *
	 * @return string
	 */
	public static function get_shortcode_tag( $shortcodes, $short_key, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'conditional'       => false,
				'conditional_check' => false,
				'foreach'           => false,
			)
		);
		if ( ( $args['conditional'] || $args['foreach'] ) && ! $args['conditional_check'] ) {
			$args['conditional_check'] = true;
		}

		$prefix = '';
		if ( $args['conditional_check'] ) {
			if ( $args['conditional'] ) {
				$prefix = 'if ';
			} elseif ( $args['foreach'] ) {
				$prefix = 'foreach ';
			}
		}

		$with_tags = $args['conditional_check'] ? 3 : 2;
		if ( ! empty( $shortcodes[ $with_tags ][ $short_key ] ) ) {
			$tag  = str_replace( '[' . $prefix, '', $shortcodes[0][ $short_key ] );
			$tag  = str_replace( ']', '', $tag );
			$tag  = str_replace( chr( 194 ) . chr( 160 ), ' ', $tag );
			$tags = preg_split( '/\s+/', $tag, 2 );
			if ( is_array( $tags ) ) {
				$tag = $tags[0];
			}
		} else {
			$tag = $shortcodes[ $with_tags - 1 ][ $short_key ];
		}

		return $tag;
	}

	public static function remove_inline_conditions( $no_vars, $code, $replace_with, &$html ) {
		if ( $no_vars ) {
			$html = str_replace( '[if ' . $code . ']', '', $html );
			$html = str_replace( '[/if ' . $code . ']', '', $html );
		} else {
			$html = preg_replace( '/(\[if\s+' . $code . '\])(.*?)(\[\/if\s+' . $code . '\])/mis', '', $html );
		}

		$html = str_replace( '[' . $code . ']', $replace_with, $html );
	}
}
