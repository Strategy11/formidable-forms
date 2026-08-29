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
	 *
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

		return $active_captcha === 'recaptcha' && $frm_settings->re_type === 'v3' ? 'recaptcha_v3' : $active_captcha;
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
	 * Modify the captcha field label so it is not orphaned.
	 *
	 * The label is omitted when the label position is set to "none".
	 * When the label is visible it is rendered as a <span> instead of a <label>,
	 * since the captcha response input is inside an iframe and cannot be referenced.
	 *
	 * @param array  $args
	 * @param string $html
	 *
	 * @return string
	 */
	protected function before_replace_html_shortcodes( $args, $html ) {
		if ( 'none' === FrmField::get_option( $this->field, 'label' ) ) {
			// Fully strip the label for a CAPTCHA if it is set to hidden.
			return preg_replace( '~\s*<label\b[^>]*for="field_\[key\]"[^>]*>.*?</label>\s*~s', '', $html );
		}

		// Convert a CAPTCHA label to a span to prevent an orphaned label issue in WAVE.
		return preg_replace(
			'~<label\b([^>]*?)\s*for="field_\[key\]"([^>]*?)>(.*?)</label>~s',
			'<span$1$2>$3</span>',
			$html
		);
	}

	/**
	 * @param array $args
	 * @param array $shortcode_atts
	 *
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		if ( ! self::should_show_captcha() ) {
			return '';
		}

		$frm_settings   = FrmAppHelper::get_settings();
		$settings       = FrmCaptchaFactory::get_settings_object();
		$div_attributes = array(
			'id'           => $args['html_id'],
			'class'        => $this->class_prefix( $frm_settings ) . $this->captcha_class( $frm_settings ),
			'data-sitekey' => $settings->get_pubkey(),
		);

		if ( 'turnstile' === $frm_settings->active_captcha ) {
			$captcha_language = $this->get_captcha_language();

			if ( $captcha_language ) {
				$div_attributes['data-language'] = $captcha_language;
			}
		}

		$div_attributes = $settings->add_front_end_element_attributes( $div_attributes, $this->field );

		return '<div ' . FrmAppHelper::array_to_html_params( $div_attributes ) . '></div>';
	}

	/**
	 * @since 6.25
	 *
	 * @return string
	 */
	private function get_captcha_language() {
		/**
		 * Allows updating the captcha language.
		 *
		 * @since 6.25
		 *
		 * @param string $lang
		 * @param array $field
		 */
		return apply_filters( 'frm_captcha_lang', get_bloginfo( 'language' ), $this->field );
	}

	/**
	 * Load the captcha script.
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	protected function load_field_scripts( $args ) {
		wp_register_script( 'captcha-api', $this->api_url(), array( 'formidable' ), '3', true );
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
	 *
	 * @return string
	 */
	protected function recaptcha_api_url( $frm_settings ) {
		$api_js_url = 'https://www.google.com/recaptcha/api.js?';

		if ( $this->allow_multiple( $frm_settings ) ) {
			$api_js_url .= '&onload=frmRecaptcha&render=explicit';
		}

		$lang = apply_filters( 'frm_recaptcha_lang', $frm_settings->re_lang, $this->field );

		if ( $lang ) {
			$api_js_url .= '&hl=' . $lang;
		}

		// Since this URL initially ends with ? and we never use add_query_arg, remove the extra
		// & that appears immediately after the ?
		$api_js_url = str_replace( '?&', '?', $api_js_url );

		/**
		 * @param string $api_js_url
		 */
		return apply_filters( 'frm_recaptcha_js_url', $api_js_url );
	}

	/**
	 * @since 6.0
	 *
	 * @return string
	 */
	protected function hcaptcha_api_url() {
		$api_js_url = 'https://js.hcaptcha.com/1/api.js';
		$lang       = $this->get_captcha_language();

		if ( $lang ) {
			// Language might be in the format of en-US, fr-FR, etc. In that case, we need to extract the first part to comply with the hcaptcha api request format.
			$lang_parts  = explode( '-', $lang );
			$api_js_url .= '?hl=' . $lang_parts[0];
		}

		$api_js_url = add_query_arg( 'onload', 'frmHcaptcha', $api_js_url );

		/**
		 * Allows updating hcaptcha js api url.
		 *
		 * @since 6.0
		 *
		 * @param string $api_js_url
		 */
		return apply_filters( 'frm_hcaptcha_js_url', $api_js_url );
	}

	/**
	 * @since 6.8.4
	 *
	 * @return string
	 */
	protected function turnstile_api_url() {
		$api_js_url = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=frmTurnstile&render=explicit';

		/**
		 * Allows updating hcaptcha js api url.
		 *
		 * @since 6.8.4
		 *
		 * @param string $api_js_url
		 */
		$api_js_url = apply_filters( 'frm_turnstile_js_url', $api_js_url );

		// Prevent render=explicit from happening twice in case someone patched
		// The double rendering issue using the frm_turnstile_js_url hook.
		return str_replace(
			'&render=explicit&render=explicit',
			'&render=explicit',
			$api_js_url
		);
	}

	/**
	 * @param FrmSettings $frm_settings
	 *
	 * @return string
	 *
	 * @psalm-return ''|'frm-'
	 */
	protected function class_prefix( $frm_settings ) {
		return FrmCaptchaFactory::get_settings_object()->get_class_prefix( $this->allow_multiple( $frm_settings ) );
	}

	/**
	 * @param FrmSettings $frm_settings This isn't used anymore. It's only there for backwards compatibility.
	 *
	 * @return string
	 *
	 * @psalm-return 'g-recaptcha'|'h-captcha'
	 */
	protected function captcha_class( $frm_settings ) {
		$settings = FrmCaptchaFactory::get_settings_object();
		return $settings->get_element_class_name();
	}

	/**
	 * @param FrmSettings $frm_settings
	 *
	 * @return bool
	 */
	protected function allow_multiple( $frm_settings ) {
		return $frm_settings->re_multi;
	}

	/**
	 * @since 4.07
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function validate_against_api( $args ) {
		$errors       = array();
		$frm_settings = FrmAppHelper::get_settings();
		$resp         = $this->send_api_check();
		$response     = json_decode( wp_remote_retrieve_body( $resp ), true );

		if ( is_wp_error( $resp ) ) {
			$error_string                     = $resp->get_error_message();
			$errors[ 'field' . $args['id'] ]  = __( 'There was a problem verifying your captcha', 'formidable' );
			$errors[ 'field' . $args['id'] ] .= ' ' . $error_string;

			$this->log_failure( $error_string, array() );

			return $errors;
		}

		if ( ! is_array( $response ) ) {
			return $errors;
		}

		$reason = '';

		if ( $frm_settings->active_captcha === 'recaptcha' && 'v3' === $frm_settings->re_type && array_key_exists( 'score', $response ) ) {
			$threshold = floatval( $frm_settings->re_threshold );
			$score     = floatval( $response['score'] );

			$this->set_score( $score );

			if ( $score < $threshold ) {
				$response['success'] = false;
				$reason              = sprintf(
					/* translators: %1$s: the score returned by reCAPTCHA, %2$s: the score threshold from global settings */
					__( 'The reCAPTCHA v3 score of %1$s is below the threshold of %2$s.', 'formidable' ),
					$score,
					$threshold
				);
			}
		}

		if ( ! isset( $response['success'] ) || $response['success'] ) {
			return $errors;
		}

		$error_codes = $this->get_error_codes( $response );

		if ( '' === $reason ) {
			$reason = $this->get_failure_reason( $error_codes );
		}

		$this->log_failure( $reason, $error_codes );

		$errors[ 'field' . $args['id'] ] = $this->get_invalid_message( $error_codes, $reason );

		return $errors;
	}

	/**
	 * Pull the "error-codes" array out of a siteverify response.
	 * Every service we support uses this key, but it is left out when the service does not
	 * explain the failure, so an empty array is a normal result.
	 *
	 * @since x.x
	 *
	 * @param array $response The decoded siteverify response.
	 *
	 * @return array<int,string>
	 */
	private function get_error_codes( $response ) {
		if ( empty( $response['error-codes'] ) || ! is_array( $response['error-codes'] ) ) {
			return array();
		}

		$error_codes = array();

		foreach ( $response['error-codes'] as $error_code ) {
			$error_codes[] = sanitize_text_field( (string) $error_code );
		}

		return $error_codes;
	}

	/**
	 * Turn the error codes from the CAPTCHA service into a readable explanation.
	 * Codes we do not have a translation for are passed through as is so the raw code
	 * still reaches the log instead of being dropped.
	 *
	 * @since x.x
	 *
	 * @param array $error_codes The error codes returned by the CAPTCHA service.
	 *
	 * @return string
	 */
	private function get_failure_reason( $error_codes ) {
		if ( ! $error_codes ) {
			return __( 'The CAPTCHA service rejected the response without giving a reason.', 'formidable' );
		}

		$messages     = FrmCaptchaFactory::get_settings_object()->get_error_code_messages();
		$explanations = array();

		foreach ( $error_codes as $error_code ) {
			$explanations[] = isset( $messages[ $error_code ] ) ? $messages[ $error_code ] : $error_code;
		}

		return implode( ' ', $explanations );
	}

	/**
	 * Check if the failure was caused by the site's own CAPTCHA settings rather than by
	 * the person filling out the form.
	 *
	 * @since x.x
	 *
	 * @param array $error_codes The error codes returned by the CAPTCHA service.
	 *
	 * @return bool
	 */
	private function is_configuration_error( $error_codes ) {
		$configuration_codes = FrmCaptchaFactory::get_settings_object()->get_configuration_error_codes();
		return array() !== array_intersect( $error_codes, $configuration_codes );
	}

	/**
	 * Get the message to show on the front end when a CAPTCHA fails.
	 *
	 * The reason is deliberately left out of the default message. Telling a bot exactly why
	 * it was rejected helps it get past the next attempt, so the detail goes to the log and
	 * to the frm_captcha_error_message filter instead. The one exception is a site
	 * misconfiguration, where retrying cannot help and a real visitor needs to know that.
	 *
	 * @since x.x
	 *
	 * @param array  $error_codes The error codes returned by the CAPTCHA service.
	 * @param string $reason      The readable explanation for the failure.
	 *
	 * @return string
	 */
	private function get_invalid_message( $error_codes, $reason ) {
		if ( $this->is_configuration_error( $error_codes ) ) {
			$message = __( 'The CAPTCHA on this form is not set up correctly. Please contact the site administrator.', 'formidable' );
		} else {
			// What happens when the CAPTCHA was entered incorrectly
			$frm_settings    = FrmAppHelper::get_settings();
			$invalid_message = FrmField::get_option( $this->field, 'invalid' );

			if ( __( 'The reCAPTCHA was not entered correctly', 'formidable' ) === $invalid_message ) {
				$invalid_message = '';
			}

			$message = '' === $invalid_message ? $frm_settings->re_msg : $invalid_message;
		}

		/**
		 * Filter the message shown when a CAPTCHA fails validation.
		 * Use this to show the reason on the front end, keeping in mind that the reason is
		 * also useful to whatever is trying to get past the CAPTCHA.
		 *
		 * @since x.x
		 *
		 * @param string $message     The message shown to the person submitting the form.
		 * @param string $reason      The readable explanation for the failure.
		 * @param array  $error_codes The error codes returned by the CAPTCHA service.
		 * @param array  $field       The CAPTCHA field.
		 */
		return apply_filters( 'frm_captcha_error_message', $message, $reason, $error_codes, $this->field );
	}

	/**
	 * Report why a CAPTCHA failed so it does not disappear silently.
	 * The reason never reaches the front end by default, so this action and the log are how
	 * a site owner finds out that, for example, their secret key is wrong.
	 * Logging requires the logging add-on. Without it there is nowhere to write to, so the
	 * action is still fired and nothing else happens.
	 *
	 * @since x.x
	 *
	 * @param string $reason      The readable explanation for the failure.
	 * @param array  $error_codes The error codes returned by the CAPTCHA service.
	 *
	 * @return void
	 */
	private function log_failure( $reason, $error_codes ) {
		$form_id = $this->get_form_id();

		/**
		 * Fires when a CAPTCHA fails validation, with the reason the service gave.
		 *
		 * @since x.x
		 *
		 * @param string $reason      The readable explanation for the failure.
		 * @param array  $error_codes The error codes returned by the CAPTCHA service.
		 * @param int    $form_id     The ID of the form being submitted.
		 * @param array  $field       The CAPTCHA field.
		 */
		do_action( 'frm_captcha_validation_failed', $reason, $error_codes, $form_id, $this->field );

		if ( ! class_exists( 'FrmLog' ) ) {
			return;
		}

		// The form id is an int so the log list's "filter by form" meta query matches it.
		$fields = array( 'form' => $form_id );

		if ( $error_codes ) {
			$fields['code'] = implode( ', ', $error_codes );
		}

		$log = new FrmLog();
		$log->add(
			array(
				'title'   => FrmCaptchaFactory::get_settings_object()->get_name() . ' validation failed',
				'content' => $reason,
				'fields'  => $fields,
			)
		);
	}

	/**
	 * Get the ID of the form the CAPTCHA field belongs to.
	 *
	 * @since x.x
	 *
	 * @return int
	 */
	private function get_form_id() {
		return (int) ( is_object( $this->field ) ? $this->field->form_id : $this->field['form_id'] );
	}

	/**
	 * @param float $score
	 *
	 * @return void
	 */
	private function set_score( $score ) {
		global $frm_vars;

		if ( ! isset( $frm_vars['captcha_scores'] ) ) {
			$frm_vars['captcha_scores'] = array();
		}

		$form_id = $this->get_form_id();

		if ( ! isset( $frm_vars['captcha_scores'][ $form_id ] ) ) {
			$frm_vars['captcha_scores'][ $form_id ] = $score;
		}
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function validate( $args ) {
		if ( ! $this->should_validate() ) {
			return array();
		}

		$missing_token = ! self::post_data_includes_token();

		if ( $missing_token ) {
			$this->report_missing_token();
			return array( 'field' . $args['id'] => __( 'The captcha is missing from this form', 'formidable' ) );
		}

		return $this->validate_against_api( $args );
	}

	/**
	 * Report a submission that arrived without a CAPTCHA token.
	 *
	 * Nothing was sent to the service, so there is no response to explain this one. The
	 * services use missing-input-response for the same situation, so that code is reused.
	 *
	 * Pro overrides validate() and does not call the version above, so it calls this
	 * directly. Keeping it here keeps the reason string in one place and one text domain.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	protected function report_missing_token() {
		$this->log_failure(
			__( 'No CAPTCHA response was submitted. The form may have been submitted before the CAPTCHA loaded, or it failed to load.', 'formidable' ),
			array( 'missing-input-response' )
		);
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

		// Don't require the captcha if it shouldn't be shown
		return self::should_show_captcha();
	}

	/**
	 * @return array|WP_Error
	 */
	protected function send_api_check() {
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
	 * @return array Values.
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
