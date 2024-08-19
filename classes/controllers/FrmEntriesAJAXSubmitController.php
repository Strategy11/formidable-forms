<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.2
 */
class FrmEntriesAJAXSubmitController {

	/**
	 * Create an entry when the ajax_submit option is on.
	 * If Pro is active, FrmProEntriesController::ajax_create will be used instead.
	 *
	 * @return void
	 */
	public static function ajax_create() {
		// This is called before we exit early to cover the conflict in Pro as well.
		self::fix_woocommerce_conflict();

		if ( is_callable( 'FrmProEntriesController::ajax_create' ) ) {
			// Let Pro handle AJAX Submit if it's available.
			// This is because Pro requires additional code to support other Pro features.
			return;
		}

		if ( ! FrmAppHelper::doing_ajax() ) {
			// Normally, this function would be triggered with the wp_ajax hook, but we need it fired sooner.
			return;
		}

		if ( 'frm_entries_create' !== FrmAppHelper::get_post_param( 'action', '', 'sanitize_title' ) ) {
			// Not a Formidable AJAX create request so exit early.
			return;
		}

		$response = array(
			'errors'  => array(),
			'content' => '',
			'pass'    => false,
		);

		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );
		if ( ! $form_id ) {
			echo json_encode( $response );
			wp_die();
		}

		$form = FrmForm::getOne( $form_id );
		if ( ! $form ) {
			echo json_encode( $response );
			wp_die();
		}

		$is_ajax_on = FrmForm::is_ajax_on( $form );
		if ( ! $is_ajax_on ) {
			// This continues in the Pro version as it is required for other features including in-place edit.
			// In Lite, if AJAX submit is not on, just exit early as this function is getting called incorrectly.
			echo json_encode( $response );
			wp_die();
		}

		$errors = FrmEntryValidate::validate( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $errors ) {
			global $frm_vars;
			$frm_vars['ajax']       = true;
			$frm_vars['css_loaded'] = true;

			FrmEntriesController::process_entry( '', true );

			$title                = FrmFormState::get_from_request( 'title', 'auto' );
			$description          = FrmFormState::get_from_request( 'description', 'auto' );
			$response['content'] .= FrmFormsController::show_form( $form->id, '', $title, $description );

			// Trigger the footer scripts if there is a form to show.
			if ( ! empty( $frm_vars['forms_loaded'] ) ) {
				ob_start();
				self::print_ajax_scripts();
				$response['content'] .= ob_get_contents();
				ob_end_clean();

				// Mark the end of added footer content.
				$response['content'] .= '<span class="frm_end_ajax_' . $form->id . '"></span>';
			}
		} else {
			$obj = array();
			foreach ( $errors as $field => $error ) {
				$field_id         = str_replace( 'field', '', $field );
				$error            = self::maybe_modify_ajax_error( $error, $field_id, $form, $errors );
				$obj[ $field_id ] = $error;
			}

			$response['errors']        = $obj;
			$invalid_msg               = FrmFormsHelper::get_invalid_error_message( array( 'form' => $form ) );
			$response['error_message'] = FrmFormsHelper::get_success_message(
				array(
					'message'  => $invalid_msg,
					'form'     => $form,
					'entry_id' => 0,
					'class'    => FrmFormsHelper::form_error_class(),
				)
			);
		}//end if

		$response = self::check_for_failed_form_submission( $response, $form->id );

		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Prevent WooCommerce 7.6.0 from triggering a fatal error when wp_print_footer_scripts is called.
	 *
	 * @since 6.2.3
	 *
	 * @return void
	 */
	private static function fix_woocommerce_conflict() {
		add_action(
			'wp_print_footer_scripts',
			function () {
				if ( ! function_exists( 'get_current_screen' ) ) {
					require_once ABSPATH . 'wp-admin/includes/screen.php';
				}

				if ( ! class_exists( 'WP_Screen', false ) ) {
					require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
				}

				FrmAppHelper::set_current_screen_and_hook_suffix();
			},
			1
		);
	}

	/**
	 * Load CAPTCHA script after AJAX submit so subsequent form CAPTCHAs don't break.
	 *
	 * @since 6.2
	 *
	 * @return void
	 */
	private static function print_ajax_scripts() {
		global $wp_scripts;
		$keep_scripts       = array( 'captcha-api' );
		$keep_scripts       = apply_filters( 'frm_ajax_load_scripts', $keep_scripts );
		$registered_scripts = (array) $wp_scripts->registered;
		$registered_scripts = array_diff( array_keys( $registered_scripts ), $keep_scripts );
		$wp_scripts->done   = array_merge( $wp_scripts->done, $registered_scripts );
		wp_print_footer_scripts();
	}

	/**
	 * If a field has custom HTML for errors, apply it around the message.
	 *
	 * @since 6.2
	 *
	 * @param string   $error
	 * @param string   $field_id
	 * @param stdClass $form the form being submitted (not necessarily the field's form when embedded/repeated).
	 * @param array    $errors all errors that were caught in this form submission, passed into the frm_before_replace_shortcodes filter for reference.
	 * @return string
	 */
	private static function maybe_modify_ajax_error( $error, $field_id, $form, $errors ) {
		if ( ! is_numeric( $field_id ) ) {
			return $error;
		}

		$use_field = FrmField::getOne( $field_id );

		if ( ! $use_field ) {
			return $error;
		}

		$use_field  = FrmFieldsHelper::setup_edit_vars( $use_field );
		$error_body = FrmFieldsController::pull_custom_error_body_from_custom_html( $form, $use_field, $errors );

		if ( false !== $error_body ) {
			$error = str_replace( '[error]', $error, $error_body );
			$error = str_replace( '[key]', $use_field['field_key'], $error );
		}

		return $error;
	}

	/**
	 * Confirm that the result of calling FrmFormsController::show_form added the failed message for a duplicate entry to the HTML.
	 * If it did, move the message to the errors key instead of returning the content.
	 *
	 * @since 6.2
	 *
	 * @param array      $response
	 * @param int|string $form_id
	 * @return array
	 */
	private static function check_for_failed_form_submission( $response, $form_id ) {
		$frm_settings = FrmAppHelper::get_settings( array( 'current_form' => $form_id ) );

		if ( false !== strpos( $response['content'], $frm_settings->failed_msg ) ) {
			$response['errors']['failed'] = $frm_settings->failed_msg;
			$response['content']          = '';
		}

		return $response;
	}
}
