<?php
/**
 * The ALTCHA captcha type, the challenge endpoint, and assets.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * ALTCHA integration with Lite's Captcha field.
 *
 * @since x.x
 */
class FrmAltchaController {

	/**
	 * Verified solutions waiting to be spent, keyed by challenge ID.
	 *
	 * A solution is only spent once the whole entry passes validation, see claim_solution().
	 * That way a visitor who fixes another field and submits again keeps their solution.
	 *
	 * This is not keyed by form. A captcha in an embedded form belongs to the embedded form,
	 * but it is validated with the parent form's entry.
	 *
	 * @var array<string, array{id: string, expires: int, field_id: int, message: string}>
	 */
	private static $pending = array();

	/**
	 * Whether ALTCHA is the active captcha type.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_active() {
		return FrmCaptchaFactory::get_settings_object() instanceof FrmAltchaSettings;
	}

	/**
	 * Load the widget instead of a remote captcha script when ALTCHA is active.
	 *
	 * Lite registers the URL under its captcha-api handle, so the widget loads once however
	 * many captcha fields the page has.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function api_url() {
		self::styles();
		return self::script_url();
	}

	/**
	 * Verify the posted solution locally instead of calling a remote service.
	 *
	 * Lite reads the result like a siteverify response. Failures keep Lite's behavior, so
	 * the field's error message and the CAPTCHA spam handling setting apply as usual.
	 *
	 * @since x.x
	 *
	 * @param FrmAltchaSettings $captcha_settings The active captcha type's settings.
	 * @param array|object      $field            The captcha field.
	 *
	 * @return array
	 */
	public static function verify_response( $captcha_settings, $field ) {
		$payload = FrmAppHelper::get_post_param( $captcha_settings->token_field, '', 'sanitize_text_field' );
		$result  = FrmAltchaChallengeHelper::verify( $payload );

		if ( $result['verified'] && FrmAltchaReplayHelper::is_used( $result['id'] ) ) {
			$result['verified'] = false;
			$result['reason']   = 'replayed';
		}

		if ( ! $result['verified'] ) {
			$code = 'missing' === $result['reason'] ? 'missing-input-response' : 'altcha-' . $result['reason'];

			return array(
				'success'     => false,
				'error-codes' => array( $code ),
			);
		}

		self::$pending[ $result['id'] ] = array(
			'id'       => $result['id'],
			'expires'  => $result['expires'],
			'field_id' => (int) ( is_object( $field ) ? $field->id : $field['id'] ),
			'message'  => self::invalid_message( $field ),
		);

		return array( 'success' => true );
	}

	/**
	 * Spend the verified solution once the entry has no other errors.
	 *
	 * If a parallel request with the same payload spent it first, the entry is rejected.
	 *
	 * @since x.x
	 *
	 * @param array $errors Validation errors so far.
	 *
	 * @return array
	 */
	public static function claim_solution( $errors ) {
		$solutions     = self::$pending;
		self::$pending = array();

		if ( ! is_array( $errors ) || array() !== $errors ) {
			return $errors;
		}

		foreach ( $solutions as $solution ) {
			$errors = self::claim_pending_solution( $errors, $solution );
		}

		return $errors;
	}

	/**
	 * Spend one verified solution, and add the captcha field's error when it cannot be spent.
	 *
	 * @since x.x
	 *
	 * @param array                                                           $errors  Validation errors so far.
	 * @param array{id: string, expires: int, field_id: int, message: string} $pending The verified solution.
	 *
	 * @return array
	 */
	private static function claim_pending_solution( $errors, $pending ) {
		if ( FrmAltchaReplayHelper::claim( $pending['id'], $pending['expires'] ) ) {
			return $errors;
		}

		$reason = FrmAltchaReplayHelper::storage_failed() ? 'storage_failed' : 'replayed';

		/**
		 * Fires when an ALTCHA solution is rejected after the rest of the entry was valid.
		 *
		 * Other rejections go through Lite's frm_captcha_validation_failed action.
		 *
		 * @since x.x
		 *
		 * @param string      $reason replayed when the solution was already spent, or storage_failed
		 *                            when the replay table could not be created or written.
		 * @param object|null $field  The captcha field.
		 */
		do_action( 'frm_altcha_validation_failed', $reason, FrmField::getOne( $pending['field_id'] ) );

		if ( 'storage_failed' === $reason ) {
			// Trying again cannot help when the replay table cannot be written, so point the visitor to the site owner.
			$pending['message'] = __( 'The CAPTCHA on this form is not set up correctly. Please contact the site administrator.', 'formidable' );
		}

		$errors[ 'field' . $pending['field_id'] ] = $pending['message'];

		return $errors;
	}

	/**
	 * Send a fresh challenge to the widget.
	 *
	 * Challenges are random and single use, so the response must never be cached.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function send_challenge() {
		nocache_headers();
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true );
		wp_send_json( FrmAltchaChallengeHelper::create() );
	}

	/**
	 * Load the widget for a form with a captcha field anywhere, so it is ready when a later page loads over AJAX.
	 *
	 * @since x.x
	 *
	 * @param array $params Form parameters, including form_id.
	 *
	 * @return void
	 */
	public static function maybe_enqueue_for_form( $params ) {
		$form_id = is_array( $params ) && isset( $params['form_id'] ) ? (int) $params['form_id'] : 0;

		if ( 0 === $form_id || ! self::is_active() ) {
			return;
		}

		// No limit here. With a limit of one, a form without a captcha field can return null instead of an empty array.
		if ( ! FrmField::get_all_types_in_form( $form_id, 'captcha', '', 'include' ) ) {
			return;
		}

		self::styles();
		// The same handle and dependency Lite uses, so the script is never loaded twice.
		wp_enqueue_script( 'captcha-api', self::script_url(), array( 'formidable' ), FrmAppHelper::plugin_version(), true );
	}

	/**
	 * The widget script URL. WordPress adds the plugin version when it prints the tag, so a new
	 * build is never served from cache.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function script_url() {
		$url          = FrmAppHelper::plugin_url() . '/js/altcha.js';
		$dependencies = array( 'formidable' );
		$language     = self::translation_language();

		if ( '' !== $language ) {
			$handle = 'frm-altcha-language-' . $language;
			wp_register_script( $handle, FrmAppHelper::plugin_url() . '/js/altcha/i18n/' . $language . '.js', array(), FrmAppHelper::$plug_version, true );
			$dependencies[] = $handle;
		}

		// Register before Lite does, so both loading paths retain the translation dependency.
		wp_register_script( 'captcha-api', $url, $dependencies, FrmAppHelper::plugin_version(), true );

		return $url;
	}

	/**
	 * Find the selected translation, falling back to the base language and then English.
	 *
	 * @since x.x
	 *
	 * @return string Translation filename without its extension, or empty for built-in English.
	 */
	private static function translation_language() {
		$language = strtolower( str_replace( '_', '-', self::language() ) );

		if ( ! preg_match( '/^[a-z]{2}(?:-[a-z0-9]+)*$/D', $language ) ) {
			return '';
		}

		$base = explode( '-', $language )[0];
		$path = FrmAppHelper::plugin_path() . '/js/altcha/i18n/';
		// Regional locales without their own file, such as fr-be or es-mx, use the closest shipped variant.
		// Spanish outside Spain uses the Latin American translation. Norwegian Nynorsk, and Norwegian
		// without a written standard, use the Bokmal translation.
		$variants  = array(
			'es' => 'es' === $language ? 'es-es' : 'es-419',
			'fr' => 'fr-fr',
			'nn' => 'nb',
			'no' => 'nb',
			'pt' => 'pt-pt',
			'zh' => 'zh-cn',
		);
		$fallbacks = array( $language, $base, $variants[ $base ] ?? $base );

		foreach ( $fallbacks as $candidate ) {
			if ( 'en' !== $base && is_file( $path . $candidate . '.js' ) ) {
				return $candidate;
			}
		}

		return '';
	}

	/**
	 * The language to tell the widget: the translation that is loaded, or the site language for English.
	 *
	 * The widget only matches a translation with the same base language, so a locale such as nn-no
	 * would show English next to the loaded nb translation.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function widget_language() {
		$translation = self::translation_language();
		return '' === $translation ? self::language() : $translation;
	}

	/**
	 * The stylesheet that maps the active form style onto the widget.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function styles() {
		$file = 'altcha.css';
		wp_enqueue_style( 'frm-altcha', FrmAppHelper::plugin_url() . '/css/' . $file, array(), FrmAppHelper::$plug_version );
	}

	/**
	 * Show an ALTCHA preview in the form builder instead of a missing image.
	 *
	 * @since x.x
	 *
	 * @param string $url        Preview image URL.
	 * @param string $image_name Active captcha type.
	 *
	 * @return string
	 */
	public static function builder_image_url( $url, $image_name ) {
		if ( FrmAltchaSettings::TYPE !== $image_name ) {
			return $url;
		}

		return FrmAppHelper::plugin_url() . '/images/altcha.svg';
	}

	/**
	 * The widget language, from the site locale. The widget falls back to the base language, then English.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function language() {
		$language = strtolower( str_replace( '_', '-', determine_locale() ) );

		/**
		 * Filters the ALTCHA widget language, such as de or pt-br.
		 *
		 * @since x.x
		 *
		 * @param string $language Language code.
		 */
		$filtered = apply_filters( 'frm_altcha_language', $language );

		return is_string( $filtered ) && '' !== $filtered ? $filtered : $language;
	}

	/**
	 * The captcha field's own error message, or the global one, like Lite.
	 *
	 * @since x.x
	 *
	 * @param array|object $field The captcha field.
	 *
	 * @return string
	 */
	private static function invalid_message( $field ) {
		$message = FrmField::get_option( $field, 'invalid' );

		if ( is_string( $message ) && '' !== $message ) {
			return $message;
		}

		$frm_settings = FrmAppHelper::get_settings();

		return is_string( $frm_settings->re_msg ) ? $frm_settings->re_msg : '';
	}
}
