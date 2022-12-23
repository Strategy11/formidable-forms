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

		if ( is_callable( 'FrmProStylesController::get_notes_for_styler_preview' ) ) {
			$notes = FrmProStylesController::get_notes_for_styler_preview();
		}

		if ( $this->form_includes_captcha ) {
			$notes[] = __( 'CAPTCHA fields are hidden.', 'formidable' );
		}

		if ( ! $notes ) {
			return array();
		}

		array_unshift( $notes, __( 'Not all JavaScript is loaded in this preview.', 'formidable' ) );

		// Implode all notes as a single note so they're all wrapped in the same element rather than individual notes.
		return array(
			implode( ' ', $notes ),
		);
	}

	/**
	 * Get all warnings to display above the visual styler preview.
	 *
	 * @since x.x
	 *
	 * @param WP_Post $style
	 * @param WP_Post $default_style
	 * @param string  $view Either 'list' or 'edit'.
	 * @return array<string>
	 */
	public function get_warnings_for_styler_preview( $style, $default_style, $view ) {
		$warnings = array();

		if ( 'edit' === $view ) {
			$is_default_style = $style->ID === $default_style->ID;
			$form_count       = FrmStylesHelper::get_form_count_for_style( $style->ID, $is_default_style );

			if ( $form_count > 1 ) {
				$warnings[] = __( 'Changes that you will make to this style will apply to every form using this style.', 'formidable' );
			}
		}

		return $warnings;
	}
}
