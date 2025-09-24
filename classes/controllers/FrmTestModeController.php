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
				self::render_testing_most_container();
			}
		);
	}

	/**
	 * Render the testing mode container.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function render_testing_most_container() {
		$form_key = self::get_form_key_from_request();

		if ( ! $form_key ) {
			return;
		}

		$form = FrmForm::getOne( $form_key );
		if ( ! $form ) {
			return;
		}

		if ( ! empty( $form->options['chat'] ) ) {
			echo '<div class="frm_note_style">' . esc_html__( 'Test Mode is currently not supported for conversational forms.', 'formidable' ) . '</div>';
			return;
		}

		$enabled                              = self::test_mode_addon_exists();
		$ai_enabled                           = class_exists( 'FrmAIAppHelper' );
		$roles                                = self::get_roles();
		$pagination                           = apply_filters( 'frm_test_mode_pagination_buttons', false );
		$disabled_required_fields_toggle_args = self::get_disabled_required_fields_toggle_args();
		$show_all_hidden_fields_toggle_args   = self::get_show_all_hidden_fields_toggle_args();
		$form_id                              = is_numeric( $form_key ) ? $form_key : FrmForm::get_id_by_key( $form_key );
		$should_show_upsell                   = self::should_show_upsell();
		$should_suggest_test_mode_install     = ! $enabled && ! $should_show_upsell;
		$should_suggest_ai_install            = $enabled && ! $ai_enabled;
		$should_show_warning                  = $should_suggest_test_mode_install || $should_suggest_ai_install;
		$form_actions                         = FrmFormAction::get_action_for_form( $form_id );
		$enabled_form_actions                 = self::get_enabled_form_action_ids( $form_actions, $enabled );
		$test_mode_install_span_attrs         = array(
			'data-upgrade'  => __( 'Test Mode Controls', 'formidable' ),
			'data-content'  => 'test-mode',
			'data-medium'   => 'test-mode',
			'data-requires' => 'Business',
			'style'         => 'margin-left: auto;',
		);
		$ai_install_span_attrs                = array(
			'data-upgrade'  => __( 'Autofilled forms with AI', 'formidable' ),
			'data-content'  => 'ai-autofill',
			'data-medium'   => 'test-mode',
			'data-requires' => 'Business',
			'style'         => 'margin-left: auto;',
		);

		$oneclick_data = FrmAddonsController::install_link( 'ai' );
		if ( isset( $oneclick_data['url'] ) ) {
			$ai_install_span_attrs['data-oneclick'] = json_encode( $oneclick_data );
		}

		$oneclick_data = FrmAddonsController::install_link( 'test-mode' );
		if ( isset( $oneclick_data['url'] ) ) {
			$test_mode_install_span_attrs['data-oneclick'] = json_encode( $oneclick_data );
		}

		if ( $should_show_upsell || $should_suggest_test_mode_install || $should_suggest_ai_install ) {
			// This is required for the speaker icon in the upsell to appear,
			// and for the lock icon in the upgrade modals.
			FrmAppHelper::include_svg();
		}

		include FrmAppHelper::plugin_path() . '/classes/views/test-mode/container.php';
	}

	/**
	 * @since x.x
	 *
	 * @return false|string
	 */
	private static function get_form_key_from_request() {
		$form_key = FrmAppHelper::simple_get( 'form' );
		if ( $form_key ) {
			return $form_key;
		}

		$form_key = FrmAppHelper::get_post_param( 'form', '', 'sanitize_text_field' );
		if ( $form_key ) {
			return $form_key;
		}

		$form_key = FrmAppHelper::get_post_param( 'form_key', '', 'sanitize_text_field' );
		if ( $form_key ) {
			return $form_key;
		}

		return false;
	}

	/**
	 * @since x.x
	 *
	 * @param array $form_actions
	 * @param bool  $enabled
	 * @return array
	 */
	private static function get_enabled_form_action_ids( $form_actions, $enabled ) {
		if ( ! $enabled || empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Default to having all actions selected.
			return wp_list_pluck( $form_actions, 'ID' );
		}

		if ( 'frm_load_form' === FrmAppHelper::get_post_param( 'action', '', 'sanitize_text_field' ) ) {
			// If we are starting over, select every form action again.
			return wp_list_pluck( $form_actions, 'ID' );
		}

		if ( empty( $_POST['frm_testmode']['enabled_form_actions'] ) || ! is_array( $_POST['frm_testmode']['enabled_form_actions'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, SlevomatCodingStandard.Files.LineLength.LineTooLong
			return array();
		}

		return array_map( 'absint', $_POST['frm_testmode']['enabled_form_actions'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function should_show_upsell() {
		if ( self::test_mode_addon_exists() ) {
			return false;
		}

		return ! in_array( FrmAddonsController::license_type(), array( 'plus', 'business', 'elite' ) );
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function test_mode_addon_exists() {
		if ( ! function_exists( 'load_formidable_test_mode' ) ) {
			return false;
		}

		return FrmAppHelper::pro_is_installed();
	}

	/**
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_roles() {
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

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

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_and_enqueue_required_scripts() {
		// These are used for the upgrade pop-up.
		FrmAppController::enqueue_dialog_assets();
		FrmAppController::upgrade_overlay_html();

		$version = FrmAppHelper::plugin_version();

		wp_enqueue_style( 'frm_testing_mode', FrmAppHelper::plugin_url() . '/css/frm_testing_mode.css', array(), $version );
		wp_enqueue_script( 'frm_testing_mode', FrmAppHelper::plugin_url() . '/js/frm_testing_mode.js', array( 'jquery', 'formidable_dom' ), $version, true );

		// These are used in addon-state.js.
		$admin_script_strings = array(
			'active'        => __( 'Active', 'formidable' ),
			'installed'     => __( 'Installed', 'formidable' ),
			'not_installed' => __( 'Not Installed', 'formidable' ),
		);
		wp_localize_script( 'frm_testing_mode', 'frm_admin_js', $admin_script_strings );

		self::register_and_enqueue_multiselect_dropdown_requirements();
	}

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	private static function register_and_enqueue_multiselect_dropdown_requirements() {
		// Enqueue multiselect dropdown requirements.
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		wp_register_script( 'popper', FrmAppHelper::plugin_url() . '/js/popper.min.js', array( 'jquery' ), '1.16.0', true );
		wp_register_script( 'bootstrap_tooltip', $plugin_url . '/js/bootstrap.min.js', array( 'jquery', 'popper' ), '4.6.1', true );
		wp_register_script( 'bootstrap-multiselect', $plugin_url . '/js/bootstrap-multiselect.js', array( 'jquery', 'bootstrap_tooltip', 'popper' ), '1.1.1', true );
		wp_register_script( 'formidable_dom', $plugin_url . '/js/admin/dom.js', array( 'jquery', 'jquery-ui-dialog', 'wp-i18n' ), $version, true );

		wp_enqueue_script( 'bootstrap-multiselect' );
		wp_enqueue_script( 'formidable_dom' );
	}
}
