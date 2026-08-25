<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldUrl extends FrmFieldType {

	/**
	 * @var string
	 *
	 * @since 3.0
	 */
	protected $type = 'url';

	/**
	 * @var bool
	 */
	protected $array_allowed = false;

	/**
	 * @return bool[]
	 */
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

	/**
	 * @return string
	 */
	protected function get_field_name() {
		return __( 'Website', 'formidable' );
	}

	/**
	 * @param array $atts
	 *
	 * @return void
	 */
	protected function fill_default_atts( &$atts ) {
		$defaults = array(
			'sep'  => ', ',
			'html' => false,
		);
		$atts     = wp_parse_args( $atts, $defaults );

		if ( $atts['html'] ) {
			$atts['sep'] = ' ';
		}
	}

	/**
	 * @param array $args
	 */
	public function validate( $args ) {
		$value = $args['value'];

		if ( trim( $value ) === 'http://' || ! $value ) {
			$value = '';
		} else {
			$value = esc_url_raw( $value );
			$value = preg_match( '/^(https?|ftps?|mailto|news|feed|telnet):/is', $value ) ? $value : 'https://' . $value;
		}

		FrmEntriesHelper::set_posted_value( $this->field, $value, $args );

		$errors = array();

		// Validate the url format. The host class allows \x80-\xff so internationalized domain names pass.
		// Byte range by design, and no /u modifier: with /u, preg_match() returns false on invalid UTF-8.
		if ( $value && ! preg_match( '/^http(s)?:\/\/(?:localhost|(?:[\da-z\x80-\xff\.-]+\.[\da-z\x80-\xff\.-]+))/i', $value ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		// skipcq: PHP-W1067 -- $this->field is always a real object here; array|int|object only matters during lazy construction elsewhere.
		} elseif ( $this->field->required == '1' && ! $value ) { // phpcs:ignore Universal.Operators.StrictComparisons
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'blank' );
		}

		return $errors;
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( ! $atts['html'] ) {
			return $value;
		}

		$images = '';

		foreach ( (array) $value as $url ) {
			$image_regex = '/(\.(?i)(jpg|jpeg|png|gif))$/';
			$is_image    = preg_match( $image_regex, $url );

			if ( $is_image ) {
				$images .= '<img src="' . esc_url( $url ) . '" class="frm_image_from_url" alt="" /> ';
			} else {
				$images .= strip_tags( $url );
			}
		}

		return $images;
	}

	/**
	 * @since 4.0.04
	 *
	 * @param array|string $value
	 *
	 * @return void
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'esc_url_raw', $value );
	}
}
