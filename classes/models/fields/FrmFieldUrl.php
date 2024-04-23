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

	public function validate( $args ) {
		$value = $args['value'];
		if ( trim( $value ) === 'http://' || empty( $value ) ) {
			$value = '';
		} else {
			$value = esc_url_raw( $value );
			$value = preg_match( '/^(https?|ftps?|mailto|news|feed|telnet):/is', $value ) ? $value : 'http://' . $value;
		}

		FrmEntriesHelper::set_posted_value( $this->field, $value, $args );

		$errors = array();

		// validate the url format
		if ( ! empty( $value ) && ! preg_match( '/^http(s)?:\/\/(?:localhost|(?:[\da-z\.-]+\.[\da-z\.-]+))/i', $value ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		} elseif ( $this->field->required == '1' && empty( $value ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'blank' );
		}

		return $errors;
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( $atts['html'] ) {
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
			$value = $images;
		}

		return $value;
	}

	/**
	 * @since 4.0.04
	 *
	 * @return void
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'esc_url_raw', $value );
	}
}
