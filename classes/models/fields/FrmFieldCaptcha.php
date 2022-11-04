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
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->active_captcha === 'recaptcha' ) {
			$replaced_for = str_replace( ' for="field_[key]"', ' for="g-recaptcha-response"', $html );
		} else {
			$replaced_for = str_replace( ' for="field_[key]"', ' for="h-captcha-response"', $html );
		}
		return $replaced_for;
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$frm_settings = FrmAppHelper::get_settings();
		if ( ( $frm_settings->active_captcha === 'recaptcha' && empty( $frm_settings->pubkey ) ) || ( $frm_settings->active_captcha === 'hcaptcha' && empty( $frm_settings->hcaptcha_pubkey ) ) ) {
			return '';
		}

		$class_prefix  = $this->class_prefix( $frm_settings );
		$captcha_class = $this->captcha_class( $frm_settings );
		$captcha_size  = $this->captcha_size( $frm_settings );
		$allow_mutiple = $frm_settings->re_multi;

		if ( $frm_settings->active_captcha === 'recaptcha' ) {
			$site_key          = $frm_settings->pubkey;
			$recaptcha_options = '" data-size="' . esc_attr( $captcha_size ) . '" data-theme="' . esc_attr( $this->field['captcha_theme'] ) . '"';
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

	protected function load_field_scripts( $args ) {
		$api_js_url = $this->api_url();

		wp_register_script( 'captcha-api', $api_js_url, array( 'formidable' ), '3', true );
		wp_enqueue_script( 'captcha-api' );
	}

	protected function api_url() {
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->active_captcha === 'recaptcha' ) {
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
		} elseif ( $frm_settings->active_captcha === 'hcaptcha' ) {
			$api_js_url = 'https://js.hcaptcha.com/1/api.js';

			/**
			 * Allows updating hcaptcha js api url.
			 *
			 * @since x.x
			 *
			 * @param string $api_js_url
			 */
			$api_js_url = apply_filters( 'frm_hcaptcha_js_url', $api_js_url );
		}

		return $api_js_url;
	}

	protected function class_prefix( $frm_settings ) {
		if ( $this->allow_multiple( $frm_settings ) && $frm_settings->active_captcha === 'recaptcha' ) {
			$class_prefix = 'frm-';
		} else {
			$class_prefix = '';
		}

		return $class_prefix;
	}

	protected function captcha_class( $frm_settings ) {
		return $frm_settings->active_captcha === 'recaptcha' ? 'g-recaptcha' : 'h-captcha';
	}

	protected function allow_multiple( $frm_settings ) {
		return $frm_settings->re_multi;
	}

	/**
	 * @return string
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
			$invalid_message                 = FrmField::get_option( $this->field, 'invalid' );
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
		if ( ! isset( $_POST['g-recaptcha-response'] ) || ! isset( $_POST['h-captcha-response'] ) ) {
			// There was no captcha submitted.
			return array( 'field' . $args['id'] => __( 'The captcha is missing from this form', 'formidable' ) );
		}

		return $this->validate_against_api( $args );
	}

	/**
	 * @since 4.07
	 * @return bool
	 */
	private function should_show_captcha() {
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->active_captcha === 'recaptcha' ) {
			return ! empty( $frm_settings->pubkey );
		} else {
			return ! empty( $frm_settings->hcaptcha_pubkey );
		}
	}

	protected function should_validate() {
		$is_hidden_field = apply_filters( 'frm_is_field_hidden', false, $this->field, wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( FrmAppHelper::is_admin() || $is_hidden_field ) {
			return false;
		}

		// don't require the captcha if it shouldn't be shown
		return $this->should_show_captcha();
	}

	protected function send_api_check( $frm_settings ) {
		if ( $frm_settings->active_captcha === 'recaptcha' ) {
			$secret      = $frm_settings->privkey;
			$token_field = 'g-recaptcha-response';
			$endpoint    = 'https://www.google.com/recaptcha/api/siteverify';
		} else {
			$secret      = $frm_settings->hcaptcha_privkey;
			$token_field = 'h-captcha-response';
			$endpoint    = 'https://hcaptcha.com/siteverify';
		}

		$arg_array = array(
			'body' => array(
				'secret'   => $secret,
				'response' => FrmAppHelper::get_param( $token_field, '', 'post', 'sanitize_text_field' ),
				'remoteip' => FrmAppHelper::get_ip_address(),
			),
		);

		return wp_remote_post( $endpoint, $arg_array );
	}

	/**
	 * Shows warning in the captcha field settings area if it is not setup.
	 *
	 * @since x.x
	 *
	 * @param array $field
	 */
	public static function before_field_settings( $field ) {
		if ( $field['type'] !== 'captcha' ) {
			return;
		}
		$frm_settings      = FrmAppHelper::get_settings();
		$active_captcha    = $frm_settings->active_captcha;
		$captcha_not_setup = $active_captcha === 'recaptcha' && empty( $frm_settings->pubkey ) || $active_captcha === 'hcaptcha' && empty( $frm_settings->hcaptcha_pubkey );
		if ( $captcha_not_setup ) {
			echo '<div class="frm_builder_captcha frm_warning_style">';
			FrmAppHelper::icon_by_class( 'frm_icon_font frm_alert_icon' );
			echo '<div><b>' . esc_html__( 'Setup a captcha', 'formidable' ) . '</b>';
			echo '<p>';
			/* translators: %1$s: Link HTML, %2$s: End link */
			printf( esc_html__( 'Your captcha will not appear on your form until you %1$sset up%2$s the Site and Secret Keys', 'formidable' ), '<a href="?page=formidable-settings" target="_blank">', '</a>' );
			echo '</p></div></div>';
		}
	}

	/**
	 * Updates error message displayed based on the captcha activated.
	 *
	 * @since x.x
	 *
	 * @param array $errors
	 * @param array $params
	 *
	 * @return array
	 */
	public static function update_captcha_field_error_message( $errors, $params ) {
		$active_captcha = FrmAppHelper::get_settings()->active_captcha;
		$field_name     = $active_captcha === 'recaptcha' ? 'reCAPTCHA' : 'hCAPTCHA';
		$fields         = FrmFieldsHelper::get_form_fields( $params['form_id'] );
		foreach ( $fields as $field ) {
			$field_id = 'field' . $field->id;
			if ( ! isset( $field->field_options['original_type'] ) ) {
				continue;
			}
			if ( $field->field_options['original_type'] === 'captcha' && isset( $errors[ $field_id ] ) ) {
				if ( strpos( $errors[ $field_id ], 'was not entered correctly' ) ) {
					$errors[ $field_id ] = sprintf( 'The %s was not entered correctly', $field_name );
				}
			}
		}

		return $errors;
	}

	/**
	 * Updates field name in page builder to the currently activated captcha if it is set to the default.
	 *
	 * @since x.x
	 *
	 * @param array $values
	 */
	public static function update_field_name( $values ) {
		if ( $values['type'] === 'captcha' ) {
			$name = $values['name'];
			if ( in_array( $name, array( __( 'reCAPTCHA', 'formidable' ), __( 'hCAPTCHA', 'formidable' ) ), true ) ) {
				$values['name'] = __( 'CAPTCHA', 'formidable' );
			}
		}

		return $values;
	}

}
