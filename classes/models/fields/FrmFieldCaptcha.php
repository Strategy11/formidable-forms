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
		$settings = FrmCaptchaFactory::get_settings_object();
		return array(
			'required'                  => false,
			'invalid'                   => true,
			'captcha_size'              => $settings->should_show_captcha_size(),
			'captcha_theme'             => $settings->should_show_captcha_theme(),
			'captcha_theme_auto_option' => $settings->should_show_captcha_theme_auto_option(),
			'default'                   => false,
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
	 * @param array  $args
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
	 * @param array $args
	 * @param array $shortcode_atts
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$frm_settings = FrmAppHelper::get_settings();
		if ( ! self::should_show_captcha() ) {
			return '';
		}

		$settings       = FrmCaptchaFactory::get_settings_object();
		$div_attributes = array(
			'id'           => $args['html_id'],
			'class'        => $this->class_prefix( $frm_settings ) . $this->captcha_class( $frm_settings ),
			'data-sitekey' => $settings->get_pubkey(),
		);
		$div_attributes = $settings->add_front_end_element_attributes( $div_attributes, $this->field );
		$html           = '<div ' . FrmAppHelper::array_to_html_params( $div_attributes ) . '></div>';

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

	/**
	 * Get the URL for the script JS that is loaded on the front end.
	 *
	 * @return string
	 */
	protected function api_url() {
		$frm_settings = FrmAppHelper::get_settings();
		$active_mode  = $frm_settings->active_captcha;

		if ( 'recaptcha' === $active_mode ) {
			return $this->recaptcha_api_url( $frm_settings );
		}

		if ( 'hcaptcha' === $active_mode ) {
			return $this->hcaptcha_api_url();
		}

		return $this->turnstile_api_url();
	}

	/**
	 * @param FrmSettings $frm_settings
	 * @return string
	 */
	protected function recaptcha_api_url( $frm_settings ) {
		$api_js_url = 'https://www.google.com/recaptcha/api.js?';

		$allow_mutiple = $frm_settings->re_multi;
		if ( $allow_mutiple ) {
			$api_js_url .= '&onload=frmRecaptcha&render=explicit';
		}

		$lang = apply_filters( 'frm_recaptcha_lang', $frm_settings->re_lang, $this->field );
		if ( $lang ) {
			$api_js_url .= '&hl=' . $lang;
		}

		/**
		 * @param string $api_js_url
		 */
		$api_js_url = apply_filters( 'frm_recaptcha_js_url', $api_js_url );

		return $api_js_url;
	}

	/**
	 * @since 6.0
	 *
	 * @return string
	 */
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
	 * @since 6.8.4
	 *
	 * @return string
	 */
	protected function turnstile_api_url() {
		$api_js_url = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=frmTurnstile';

		/**
		 * Allows updating hcaptcha js api url.
		 *
		 * @since 6.8.4
		 *
		 * @param string $api_js_url
		 */
		$api_js_url = apply_filters( 'frm_turnstile_js_url', $api_js_url );

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
		$settings = FrmCaptchaFactory::get_settings_object();
		return $settings->get_element_class_name();
	}

	protected function allow_multiple( $frm_settings ) {
		return $frm_settings->re_multi;
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

		$missing_token = ! self::post_data_includes_token();
		if ( $missing_token ) {
			return array( 'field' . $args['id'] => __( 'The captcha is missing from this form', 'formidable' ) );
		}

		return $this->validate_against_api( $args );
	}

	/**
	 * @since 6.8.4
	 *
	 * @return bool
	 */
	protected static function post_data_includes_token() {
		$settings = FrmCaptchaFactory::get_settings_object();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		return ! empty( $_POST[ $settings->token_field ] );
	}

	/**
	 * Check if the active captcha type's public key is set.
	 *
	 * @since 4.07
	 *
	 * @return bool
	 */
	public static function should_show_captcha() {
		$settings = FrmCaptchaFactory::get_settings_object();
		return $settings->has_pubkey();
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
		$captcha_settings = FrmCaptchaFactory::get_settings_object();
		$arg_array        = array(
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

	/**
	 * @param FrmSettings $frm_settings
	 * @return string
	 */
	protected function captcha_size( $frm_settings ) {
		_deprecated_function( __METHOD__, '6.8.4' );
		return 'normal';
	}
}
