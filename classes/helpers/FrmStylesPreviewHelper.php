<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmStylesPreviewHelper {

	/**
	 * @param int $form_id
	 */
	private $form_id;

	/**
	 * This is tracked so we can show a note that the CAPTCHA was turned off.
	 *
	 * @var bool
	 */
	private $form_includes_captcha = false;

	/**
	 * @param string|int $form_id
	 * @return void
	 */
	public function __construct( $form_id ) {
		$this->form_id = (int) $form_id;
	}

	/**
	 * Modify form behaviours for the styler preview.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public function adjust_form_for_preview() {
		add_filter( 'frm_run_antispam', '__return_false', 99 ); // Don't bother including the antispam token in the preview as the form isn't submitted.
		add_filter( 'frm_run_honeypot', '__return_false' ); // We don't need the honeypot in the preview so leave it out.
		$this->hide_captcha_fields();
		$this->disable_javascript_validation();
	}

	/**
	 * Captcha does not initialize in the preview. Hide it so we don't see the label either.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function hide_captcha_fields() {
		add_filter(
			'frm_show_normal_field_type',
			/**
			 * @param bool   $show
			 * @param string $field_type
			 * @param string $target_field_type
			 * @return bool
			 */
			function( $show, $field_type ) {
				if ( 'captcha' === $field_type ) {
					$show = false;
				}
				return $show;
			},
			10,
			2
		);
	}

	/**
	 * Turn off JavaScript validation for the preview.
	 * Without this we hit a "PHP Fatal error:  Uncaught Error: Maximum function nesting level of '256' reached" error.
	 * This only happened with specific Look up fields when FrmProLookupFieldsController::get_independent_lookup_field_options is called in Pro.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private function disable_javascript_validation() {
		add_filter(
			'frm_form_object',
			function( $form ) {
				$form->options['js_validate'] = false;
				return $form;
			}
		);
	}

	/**
	 * @since x.x
	 *
	 * @param string|int $form_id
	 * @return string
	 */
	public function get_html_for_form_preview() {
		// Force is_admin to false so the "Entry Key" field doesn't render in the preview.
		add_filter( 'frm_is_admin', '__return_false' );

		$target_form_preview_html = FrmFormsController::show_form( $this->form_id, '', 'auto', 'auto' );

		$this->form_includes_captcha = wp_script_is( 'captcha-api', 'enqueued' );
		if ( $this->form_includes_captcha ) {
			// If a form includes a CAPTCHA field, don't try to load the CAPTCHA scripts for the visual styler preview.
			wp_dequeue_script( 'captcha-api' );
		}

		// Return the is_admin status.
		// Otherwise success messages won't use the proper mark up and will appear without the green background and padding.
		remove_filter( 'frm_is_admin', '__return_false' );

		return $target_form_preview_html;
	}

	/**
	 * @since x.x
	 *
	 * @todo Only show the note once for a form per user per month or something.
	 *
	 * @return array<string>
	 */
	public function get_notes_for_styler_preview() {
		$notes = array();

		if ( class_exists( 'FrmProStylesController' ) && ! class_exists( 'FrmProStylesPreviewHelper' ) ) {
			$notes[] = __( 'You are using an outdated version of Formidable Pro. Please update to version 6.0 to get access to all styler features.', 'formidable' );
		}

		if ( is_callable( 'FrmProStylesController::get_notes_for_styler_preview' ) ) {
			$notes = FrmProStylesController::get_notes_for_styler_preview();
		}

		$disabled_features_note = $this->get_disabled_features_note();
		if ( is_string( $disabled_features_note ) ) {
			$notes[] = $disabled_features_note;
		}

		$notes = $this->maybe_add_fallback_form_note( $notes );

		return $notes;
	}

	/**
	 * @since x.x
	 *
	 * @return string|false
	 */
	private function get_disabled_features_note() {
		$disabled_features = array();

		if ( $this->form_includes_captcha ) {
			$disabled_features[] = __( 'CAPTCHA fields are hidden.', 'formidable' );
		}

		if ( is_callable( 'FrmProStylesController::get_disabled_javascript_features' ) ) {
			$disabled_pro_features = FrmProStylesController::get_disabled_javascript_features();
			if ( is_string( $disabled_pro_features ) ) {
				$disabled_features[] = $disabled_pro_features;
			}
		}

		if ( $disabled_features ) {
			return __( 'Not all JavaScript is loaded in this preview.', 'formidable' ) . ' ' . implode( ' ', $disabled_features );
		}

		return false;
	}

	/**
	 * @param array<string> $notes
	 * @return array<string>
	 */
	private function maybe_add_fallback_form_note( $notes ) {
		if ( ! FrmAppHelper::simple_get( 'form', 'absint' ) ) {
			array_unshift( $notes, __( 'This form is being previewed because no form was selected. Use the form dropdown to select a new preview target.', 'formidable' ) );
		}
		return $notes;
	}

	/**
	 * Get all warnings to display above the visual styler preview.
	 *
	 * @since x.x
	 *
	 * @param WP_Post|stdClass $style A new style is not a WP_Post object.
	 * @param WP_Post          $default_style
	 * @param string           $view Either 'list' or 'edit'.
	 * @return array<string>
	 */
	public function get_warnings_for_styler_preview( $style, $default_style, $view ) {
		$warnings = array();

		if ( 'edit' === $view && $this->should_show_multiple_forms_warning( $style->ID, $default_style->ID ) ) {
			$warnings[] = __( 'Changes that you will make to this style will apply to every form using this style.', 'formidable' );
		}

		return $warnings;
	}

	/**
	 * @since x.x
	 *
	 * @param int $style_id
	 * @param int $default_style_id
	 * @return bool
	 */
	private function should_show_multiple_forms_warning( $style_id, $default_style_id ) {
		$is_default_style = $style_id === $default_style_id;

		if ( ! $is_default_style ) {
			// Also check for the conversational default.
			$conversational_style_id = FrmDb::get_var( 'posts', array( 'post_name' => 'lines-no-boxes' ), 'ID' );
			if ( $conversational_style_id && (int) $conversational_style_id === $style_id ) {
				$is_default_style = true;
			}
		}

		$form_count = FrmStylesHelper::get_form_count_for_style( $style_id, $is_default_style );

		if ( $form_count <= 1 ) {
			return false;
		}

		// Only show the warning once per user per style.
		$user_id  = get_current_user_id();
		$meta_key = 'frm_dismiss_multiple_forms_warning_' . $style_id;
		$meta     = get_user_meta( $user_id, $meta_key, true );

		if ( $meta ) {
			return false;
		}

		add_user_meta( $user_id, $meta_key, 1 );
		return true;
	}

	/**
	 * @since x.x
	 *
	 * @param WP_Styles $styles
	 * @return void
	 */
	public static function disable_conflicting_wp_admin_css( $styles ) {
		if ( ! is_callable( array( $styles, 'remove' ) ) || ! array_key_exists( 'wp-admin', $styles->registered ) ) {
			return;
		}

		$styles->remove( 'edit' );

		$wp_admin_dependencies = $styles->registered['wp-admin']->deps;
		$edit_key              = array_search( 'edit', $wp_admin_dependencies );
		if ( false === $edit_key ) {
			return;
		}

		// Remove the edit dependency from wp-admin so it still loads, just without edit.css.
		self::remove_wp_admin_dependency( $styles, 'edit' );
	}

	/**
	 * @since x.x
	 *
	 * @param WP_Styles $styles
	 * @param string    $key
	 * @return void
	 */
	private static function remove_wp_admin_dependency( $styles, $key ) {
		$dependencies = $styles->registered['wp-admin']->deps;
		$index        = array_search( $key, $dependencies );
		if ( false === $index ) {
			return;
		}

		unset( $dependencies[ $index ] );
		$dependencies = array_values( $dependencies );

		$styles->registered['wp-admin']->deps = $dependencies;
	}
}
