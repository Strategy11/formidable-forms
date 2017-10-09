<?php

/**
 * @since 3.0
 */
class FrmFieldCaptcha extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'captcha';

	/**
	 * @return string
	 */
	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-captcha.php';
	}

	/**
	 * @return array
	 */
	protected function field_settings_for_type() {
		return array(
			'required'      => false,
			'invalid'       => true,
			'default_blank' => false,
			'captcha_size'  => true,
		);
	}

	/**
	 * @return array
	 */
	protected function new_field_settings() {
		$frm_settings = FrmAppHelper::get_settings();
		return array(
			'invalid' => $frm_settings->re_msg,
		);
	}

	/**
	 * @return array
	 */
	protected function extra_field_opts() {
		return array(
			'label'         => 'none',
			'captcha_size'  => 'normal',
			'captcha_theme' => 'light',
		);
	}

	/**
	 * Remove the for attribute for captcha
	 */
	protected function before_replace_html_shortcodes( $args, $html ) {
		return str_replace( ' for="field_[key]"', '', $html );
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$frm_settings = FrmAppHelper::get_settings();
		if ( empty( $frm_settings->pubkey ) ) {
			return '';
		}

		$class_prefix = $this->class_prefix();
		$captcha_size = $this->captcha_size();
		$allow_mutiple = $frm_settings->re_multi;

		$html = '<div id="' . esc_attr( $args['html_id'] ) . '" class="' . esc_attr( $class_prefix ) . 'g-recaptcha" data-sitekey="' . esc_attr( $frm_settings->pubkey ) . '" data-size="' . esc_attr( $captcha_size ) . '" data-theme="' . esc_attr( $this->field['captcha_theme'] ) . '"';
		if ( $captcha_size == 'invisible' && ! $allow_mutiple ) {
			$html .= ' data-callback="frmAfterRecaptcha"';
		}
		$html .= '></div>';

		return $html;
	}

	protected function load_field_scripts( $args ) {
		$api_js_url = $this->api_url();

		wp_register_script( 'recaptcha-api', $api_js_url, array( 'formidable' ), true );
		wp_enqueue_script( 'recaptcha-api' );
	}

	protected function api_url() {
		$api_js_url = 'https://www.google.com/recaptcha/api.js?';

		$frm_settings = FrmAppHelper::get_settings();
		$allow_mutiple = $frm_settings->re_multi;
		if ( $allow_mutiple ) {
			$api_js_url .= '&onload=frmRecaptcha&render=explicit';
		}

		$lang = apply_filters( 'frm_recaptcha_lang', $frm_settings->re_lang, $this->field );
		if ( ! empty( $lang ) ) {
			$api_js_url .= '&hl=' . $lang;
		}

		return apply_filters( 'frm_recaptcha_js_url', $api_js_url );
	}

	protected function class_prefix() {
		if ( $this->allow_multiple() ) {
			$class_prefix = 'frm-';
		} else {
			$class_prefix = '';
		}
		return $class_prefix;
	}

	protected function allow_multiple() {
		$frm_settings = FrmAppHelper::get_settings();
		return $frm_settings->re_multi;
	}

	protected function captcha_size() {
		// for reverse compatibility
		$frm_settings = FrmAppHelper::get_settings();
		$captcha_size = ( $this->field['captcha_size'] == 'default' ) ? 'normal' : $this->field['captcha_size'];
		return ( $frm_settings->re_type == 'invisible' ) ? 'invisible' : $captcha_size;
	}
}
