<?php

/**
 * @since 3.0
 */
class FrmFieldUrl extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'url';
	protected $display_type = 'text';

	protected function field_settings_for_type() {
		return array(
			'size'           => true,
			'clear_on_focus' => true,
			'invalid'        => true,
			'show_image'     => true,
		);
	}

	/**
	 * @return array
	 */
	protected function extra_field_opts() {
		return array(
			'show_image' => 0,
		);
	}

	protected function get_field_name() {
		return __( 'Website', 'formidable' );
	}

	protected function fill_default_atts( &$atts ) {
		$defaults = array(
			'sep' => ', ',
			'html' => false,
		);
		$atts = wp_parse_args( $atts, $defaults );

		if ( $atts['html'] ) {
			$atts['sep'] = ' ';
		}
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( $atts['html'] ) {
			$images = '';
			foreach ( (array) $value as $url ) {
				$image_regex = '/(\.(?i)(jpg|jpeg|png|gif))$/';
				$is_image = preg_match( $image_regex, $url );
				if ( $is_image ) {
					$images .= '<img src="' . esc_attr( $url ) . '" class="frm_image_from_url" alt="" /> ';
				} else {
					$images .= strip_tags( $url );
				}
			}
			$value = $images;
		}

		return $value;
	}
}
