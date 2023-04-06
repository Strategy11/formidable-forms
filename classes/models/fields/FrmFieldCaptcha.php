<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

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
	 * Returns the image name for a captcha.
	 *
	 * @return string
	 */
	public static function get_captcha_image_name() {
		$frm_settings   = FrmAppHelper::get_settings();
		$active_captcha = $frm_settings->active_captcha;
		if ( $active_captcha === 'recaptcha' && $frm_settings->re_type === 'v3' ) {
			$image_name = 'recaptcha_v3';
		} else {
			$image_name = $active_captcha;
		}

		return $image_name;
	}

	/**
	 * @return array
	 */
	protected function field_settings_for_type() {
		return array(
			'required'     => false,
			'invalid'      => true,
			'captcha_size' => true,
			'default'      => false,
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
	 * Remove the "for" attribute for captcha
	 *
	 * @param array $args
	 * @param string $html
	 *
	 * @return string
	 */
	protected function before_replace_html_shortcodes( $args, $html ) {
		$frm_settings     = FrmAppHelper::get_settings();
		$replace_response = $frm_settings->active_captcha === 'recaptcha' ? 'g-recaptcha-response' : 'h-captcha-response';
		$replaced_for     = str_replace( ' for="field_[key]"', ' for="' . $replace_response . '"', $html );

		return $replaced_for;
	}

	/**
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$frm_settings = FrmAppHelper::get_settings();
		if ( ! self::should_show_captcha() ) {
			return '';
		}

		$class_prefix  = $this->class_prefix( $frm_settings );
		$captcha_class = $this->captcha_class( $frm_settings );
		$captcha_size  = $this->captcha_size( $frm_settings );
		$allow_mutiple = $frm_settings->re_multi;

		if ( $frm_settings->active_captcha === 'recaptcha' ) {
			$site_key          = $frm_settings->pubkey;
			$recaptcha_options = ' data-size="' . esc_attr( $captcha_size ) . '" data-theme="' . esc_attr( $this->field['captcha_theme'] ) . '"';
		} else {
			$site_key = $frm_settings->hcaptcha_pubkey;
		}

		$html = '<div id="' . esc_attr( $args['html_id'] ) . '" class="' . esc_attr( $class_prefix ) . $captcha_class . '" data-sitekey="' . esc_attr( $site_key ) . '"';
		$html .= ! empty( $recaptcha_options ) ? $recaptcha_options : '';
		if ( $captcha_size === 'invisible' && ! $allow_mutiple ) {
			$html .= ' data-callback="frmAfterRecaptcha"';
		}
		$html .= '></div>';

		return $html;
	}

	/**
	 * @return void
	 */
	protected function load_field_scripts( $args ) {
		$api_js_url = $this->api_url();

		wp_register_script( 'captcha-api', $api_js_url, array( 'formidable' ), '3', true );
		wp_enqueue_script( 'captcha-api' );
	}

	protected function api_url() {
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->active_captcha === 'recaptcha' ) {
			return $this->recaptcha_api_url( $frm_settings );
		}

		return $this->hcaptcha_api_url();
	}

	/**
	 * @param FrmSettings $frm_settings
	 */
	protected function recaptcha_api_url( $frm_settings ) {
		$api_js_url = 'https://www.google.com/recaptcha/api.js?';

		$allow_mutiple = $frm_settings->re_multi;
		if ( $allow_mutiple ) {
			$api_js_url .= '&onload=frmRecaptcha&render=explicit';
		}

		$lang = apply_filters( 'frm_recaptcha_lang', $frm_settings->re_lang, $this->field );
		if ( ! empty( $lang ) ) {
			$api_js_url .= '&hl=' . $lang;
		}

		$api_js_url = apply_filters( 'frm_recaptcha_js_url', $api_js_url );

		return $api_js_url;
	}

	protected function hcaptcha_api_url() {
		$api_js_url = 'https://js.hcaptcha.com/1/api.js';

		/**
		 * Allows updating hcaptcha js api url.
		 *
		 * @since 6.0
		 *
		 * @param string $api_js_url
		 */
		$api_js_url = apply_filters( 'frm_hcaptcha_js_url', $api_js_url );

		return $api_js_url;
	}

	/**
	 * @param FrmSettings $frm_settings
	 *
	 * @return string
	 *
	 * @psalm-return ''|'frm-'
	 */
	protected function class_prefix( $frm_settings ) {
		if ( $this->allow_multiple( $frm_settings ) && $frm_settings->active_captcha === 'recaptcha' ) {
			$class_prefix = 'frm-';
		} else {
			$class_prefix = '';
		}

		return $class_prefix;
	}

	/**
	 * @param FrmSettings $frm_settings
	 *
	 * @return string
	 *
	 * @psalm-return 'g-recaptcha'|'h-captcha'
	 */
	protected function captcha_class( $frm_settings ) {
		return $frm_settings->active_captcha === 'recaptcha' ? 'g-recaptcha' : 'h-captcha';
	}

	protected function allow_multiple( $frm_settings ) {
		return $frm_settings->re_multi;
	}

	/**
	 * @return string
	 *
	 * @param FrmSettings $frm_settings
	 */
	protected function captcha_size( $frm_settings ) {
		if ( in_array( $frm_settings->re_type, array( 'invisible', 'v3' ), true ) ) {
			return 'invisible';
		}
		// for reverse compatibility
		return $this->field['captcha_size'] === 'default' ? 'normal' : $this->field['captcha_size'];
	}

	/**
	 * @since 4.07
	 * @param array $args
	 * @return array
	 */
	protected function validate_against_api( $args ) {
		$errors       = array();
		$frm_settings = FrmAppHelper::get_settings();
		$resp         = $this->send_api_check( $frm_settings );
		$response     = json_decode( wp_remote_retrieve_body( $resp ), true );

		if ( is_wp_error( $resp ) ) {
			$error_string                     = $resp->get_error_message();
			$errors[ 'field' . $args['id'] ]  = __( 'There was a problem verifying your captcha', 'formidable' );
			$errors[ 'field' . $args['id'] ] .= ' ' . $error_string;
			return $errors;
		}

		if ( ! is_array( $response ) ) {
			return $errors;
		}

		if ( $frm_settings->active_captcha === 'recaptcha' ) {
			if ( 'v3' === $frm_settings->re_type && array_key_exists( 'score', $response ) ) {
				$threshold = floatval( $frm_settings->re_threshold );
				$score     = floatval( $response['score'] );

				$this->set_score( $score );

				if ( $score < $threshold ) {
					$response['success'] = false;
				}
			}
		}

		if ( isset( $response['success'] ) && ! $response['success'] ) {
			// What happens when the CAPTCHA was entered incorrectly
			$invalid_message = FrmField::get_option( $this->field, 'invalid' );
			if ( $invalid_message === __( 'The reCAPTCHA was not entered correctly', 'formidable' ) ) {
				$invalid_message = '';
			}
			$errors[ 'field' . $args['id'] ] = ( $invalid_message === '' ? $frm_settings->re_msg : $invalid_message );
		}

		return $errors;
	}

	/**
	 * @param float $score
	 * @return void
	 */
	private function set_score( $score ) {
		global $frm_vars;
		if ( ! isset( $frm_vars['captcha_scores'] ) ) {
			$frm_vars['captcha_scores'] = array();
		}
		$form_id = is_object( $this->field ) ? $this->field->form_id : $this->field['form_id'];
		if ( ! isset( $frm_vars['captcha_scores'][ $form_id ] ) ) {
			$frm_vars['captcha_scores'][ $form_id ] = $score;
		}
	}

	/**
	 * @param array $args
	 * @return array
	 */
	public function validate( $args ) {
		if ( ! $this->should_validate() ) {
			return array();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['g-recaptcha-response'] ) && ! isset( $_POST['h-captcha-response'] ) ) {
			// There was no captcha submitted.
			return array( 'field' . $args['id'] => __( 'The captcha is missing from this form', 'formidable' ) );
		}

		return $this->validate_against_api( $args );
	}

	/**
	 * @since 4.07
	 * @return bool
	 */
	public static function should_show_captcha() {
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->active_captcha === 'recaptcha' ) {
			return ! empty( $frm_settings->pubkey );
		}

		return ! empty( $frm_settings->hcaptcha_pubkey );
	}

	/**
	 * @return bool
	 */
	protected function should_validate() {
		$is_hidden_field = apply_filters( 'frm_is_field_hidden', false, $this->field, wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( FrmAppHelper::is_admin() || $is_hidden_field ) {
			return false;
		}

		// don't require the captcha if it shouldn't be shown
		return self::should_show_captcha();
	}

	/**
	 * @param FrmSettings $frm_settings
	 */
	protected function send_api_check( $frm_settings ) {
		$captcha_settings = new FrmFieldCaptchaSettings( $frm_settings );

		$arg_array = array(
			'body' => array(
				'secret'   => $captcha_settings->secret,
				'response' => FrmAppHelper::get_param( $captcha_settings->token_field, '', 'post', 'sanitize_text_field' ),
				'remoteip' => FrmAppHelper::get_ip_address(),
			),
		);

		return wp_remote_post( $captcha_settings->endpoint, $arg_array );
	}

	/**
	 * Updates field name in page builder to the currently activated captcha if it is set to the default.
	 *
	 * @since 6.0
	 *
	 * @param array $values
	 *
	 * @return array $values
	 */
	public static function update_field_name( $values ) {
		if ( $values['type'] === 'captcha' ) {
			$name = $values['name'];
			if ( in_array( $name, array( __( 'reCAPTCHA', 'formidable' ), __( 'hCaptcha', 'formidable' ) ), true ) ) {
				$values['name'] = __( 'Captcha', 'formidable' );
			}
		}

		return $values;
	}
}
