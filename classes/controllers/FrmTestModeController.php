<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmTestModeController {

	/**
	 * Maybe add the test mode container.
	 *
	 * @since x.x
	 *
	 * @param string $html
	 * @return string
	 */
	public static function maybe_add_test_mode_container( $html ) {
		if ( '' === $html || ! self::should_add_test_mode_container() ) {
			return $html;
		}

		/**
		 * @since x.x
		 */
		do_action( 'frm_test_mode_container' );

		wp_enqueue_style( 'frm_testing_mode', FrmAppHelper::plugin_url() . '/css/frm_testing_mode.css', array(), FrmAppHelper::plugin_version() );

		if ( false !== strpos( $html, '<div class="frm_form_fields' ) ) {
			$html = preg_replace(
				'/<div class="frm_form_fields/',
				self::get_testing_mode_container() . '<div class="frm_form_fields',
				$html,
				1
			);
		} else {
			// If there no form, add before an error message.
			$html = '<div class="with_frm_style">' . self::get_testing_mode_container() . '</div>' . $html;
		}

		return $html;
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function should_add_test_mode_container() {
		if ( ! current_user_can( 'frm_edit_forms' ) ) {
			return false;
		}

		return (bool) apply_filters( 'frm_test_mode', (bool) FrmAppHelper::simple_get( 'testmode' ) );
	}

	/**
	 * Get the testing mode container.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_testing_mode_container() {
		return FrmAppHelper::clip(
			function () {
				$enabled                              = function_exists( 'load_formidable_test_mode' );
				$ai_enabled                           = class_exists( 'FrmAIAppHelper' );
				$roles                                = self::get_roles();
				$pagination                           = apply_filters( 'frm_test_mode_pagination_buttons', false );
				$disabled_required_fields_toggle_args = self::get_disabled_required_fields_toggle_args();
				$show_all_hidden_fields_toggle_args   = self::get_show_all_hidden_fields_toggle_args();
				$form_key                             = FrmAppHelper::simple_get( 'form' );
				$form_id                              = is_numeric( $form_key ) ? $form_key : FrmForm::get_id_by_key( $form_key );

				include FrmAppHelper::plugin_path() . '/classes/views/test-mode/container.php';
			}
		);
	}

	/**
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_roles() {
		$roles              = get_editable_roles();
		$roles['loggedout'] = array(
			'name' => __( 'Logged Out', 'formidable' ),
		);
		return $roles;
	}

	/**
	 * @return array
	 */
	private static function get_disabled_required_fields_toggle_args() {
		/**
		 * @since x.x
		 *
		 * @param array $args
		 */
		return apply_filters(
			'frm_test_mode_disable_required_fields_toggle_args',
			array(
				'echo'        => true,
				'off_label'   => __( 'Disable Required Fields', 'formidable' ),
				'show_labels' => true,
				'disabled'    => true,
			)
		);
	}

	/**
	 * @return array
	 */
	private static function get_show_all_hidden_fields_toggle_args() {
		/**
		 * @since x.x
		 *
		 * @param array $args
		 */
		return apply_filters(
			'frm_test_mode_show_all_hidden_fields_toggle_args',
			array(
				'echo'        => true,
				'off_label'   => __( 'Show All Hidden Fields', 'formidable' ),
				'show_labels' => true,
				'disabled'    => true,
			)
		);
	}
}
