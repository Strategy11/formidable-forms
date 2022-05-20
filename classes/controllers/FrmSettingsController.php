<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSettingsController {

	public static function menu() {
		// Make sure admins can see the menu items
		FrmAppHelper::force_capability( 'frm_change_settings' );

		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Global Settings', 'formidable' ), __( 'Global Settings', 'formidable' ), 'frm_change_settings', 'formidable-settings', 'FrmSettingsController::route' );
	}

	public static function license_box() {
		$a = FrmAppHelper::simple_get( 't', 'sanitize_title', 'general_settings' );
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/license_box.php' );
	}

	public static function display_form( $errors = array(), $message = '' ) {
		global $frm_vars;

		$frm_settings = FrmAppHelper::get_settings();

		$uploads     = wp_upload_dir();
		$target_path = $uploads['basedir'] . '/formidable/css';

		$sections = self::get_settings_tabs();
		$current  = FrmAppHelper::simple_get( 't', 'sanitize_title', 'general_settings' );

		require( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/form.php' );
	}

	private static function get_settings_tabs() {
		$sections = array(
			'general' => array(
				'class'    => __CLASS__,
				'function' => 'general_settings',
				'name'     => __( 'General Settings', 'formidable' ),
				'icon'     => 'frm_icon_font frm_settings_icon',
			),
			'messages' => array(
				'class'    => __CLASS__,
				'function' => 'message_settings',
				'name'     => __( 'Message Defaults', 'formidable' ),
				'icon'     => 'frm_icon_font frm_stamp_icon',
			),
			'permissions' => array(
				'class'    => __CLASS__,
				'function' => 'permission_settings',
				'name'     => __( 'Permissions', 'formidable' ),
				'icon'     => 'frm_icon_font frm_lock_icon',
			),
			'recaptcha' => array(
				'class'    => __CLASS__,
				'function' => 'recaptcha_settings',
				'name'     => __( 'reCAPTCHA', 'formidable' ),
				'icon'     => 'frm_icon_font frm_shield_check_icon',
			),
			'white_label' => array(
				'name'       => __( 'White Labeling', 'formidable' ),
				'icon'       => 'frm_icon_font frm_ghost_icon',
				'html_class' => 'frm_show_upgrade frm_noallow',
				'data'       => array(
					'medium'  => 'white-label',
					'upgrade' => __( 'White labeling options', 'formidable' ),
				),
			),
			'inbox' => array(
				'name'       => __( 'Inbox', 'formidable' ),
				'icon'       => 'frm_icon_font frm_email_icon',
				'html_class' => 'frm_show_upgrade frm_noallow',
				'data'       => array(
					'medium'  => 'inbox-settings',
					'upgrade' => __( 'Inbox settings', 'formidable' ),
				),
			),
		);

		if ( apply_filters( 'frm_include_addon_page', false ) ) {
			// if no addons need a license, skip this page
			$show_licenses    = false;
			$installed_addons = apply_filters( 'frm_installed_addons', array() );
			foreach ( $installed_addons as $installed_addon ) {
				if ( ! $installed_addon->is_parent_licence && $installed_addon->plugin_name != 'Formidable Pro' && $installed_addon->needs_license ) {
					$show_licenses = true;
					break;
				}
			}

			if ( $show_licenses ) {
				$sections['licenses'] = array(
					'class'    => 'FrmAddonsController',
					'function' => 'license_settings',
					'name'     => __( 'Plugin Licenses', 'formidable' ),
					'icon'     => 'frm_icon_font frm_keyalt_icon',
					'ajax'     => true,
				);
			}
		}
		$sections = apply_filters( 'frm_add_settings_section', $sections );

		$sections['misc'] = array(
			'name'     => __( 'Miscellaneous', 'formidable' ),
			'icon'     => 'frm_icon_font frm_shuffle_icon',
			'class'    => __CLASS__,
			'function' => 'misc_settings',
		);

		foreach ( $sections as $key => $section ) {
			$original = $section;
			$defaults = array(
				'html_class' => '',
				'name'       => ucfirst( $key ),
				'icon'       => 'frm_icon_font frm_settings_icon',
				'anchor'     => $key . '_settings',
				'data'       => array(),
			);

			$section = array_merge( $defaults, $section );

			if ( isset( $section['ajax'] ) && ! isset( $section['data']['frmajax'] ) ) {
				$section['data']['frmajax'] = $section['ajax'];
			}

			// For reverse compatibility.
			if ( ! isset( $section['function'] ) && ( ! is_array( $original ) || ! isset( $original['name'] ) ) ) {
				$section['function'] = $original;
			}

			$sections[ $key ] = $section;
		}

		return $sections;
	}

	public static function load_settings_tab() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$section  = FrmAppHelper::get_post_param( 'tab', '', 'sanitize_text_field' );
		$sections = self::get_settings_tabs();
		if ( ! isset( $sections[ $section ] ) ) {
			wp_die();
		}

		$section = $sections[ $section ];

		if ( isset( $section['class'] ) ) {
			call_user_func( array( $section['class'], $section['function'] ) );
		} else {
			call_user_func( ( isset( $section['function'] ) ? $section['function'] : $section ) );
		}
		wp_die();
	}

	/**
	 * @since 4.0
	 */
	public static function general_settings() {
		$frm_settings = FrmAppHelper::get_settings();

		$uploads     = wp_upload_dir();
		$target_path = $uploads['basedir'] . '/formidable/css';

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/general.php' );
	}

	/**
	 * @since 4.0
	 */
	public static function message_settings() {
		$frm_settings = FrmAppHelper::get_settings();

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/messages.php' );
	}

	/**
	 * @since 4.0
	 */
	public static function recaptcha_settings() {
		$frm_settings = FrmAppHelper::get_settings();
		$captcha_lang = FrmAppHelper::locales( 'captcha' );

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/recaptcha.php' );
	}

	/**
	 * @since 4.0
	 */
	public static function permission_settings() {
		$frm_settings = FrmAppHelper::get_settings();
		$frm_roles    = FrmAppHelper::frm_capabilities();

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/permissions.php' );
	}

	/**
	 * @since 4.0
	 */
	public static function misc_settings() {
		$frm_settings = FrmAppHelper::get_settings();

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/misc.php' );
	}

	public static function process_form( $stop_load = false ) {
		global $frm_vars;

		$frm_settings = FrmAppHelper::get_settings();

		$process_form = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );
		if ( ! wp_verify_nonce( $process_form, 'process_form_nonce' ) ) {
			wp_die( esc_html( $frm_settings->admin_permission ) );
		}

		$errors  = array();
		$message = '';

		if ( ! isset( $frm_vars['settings_routed'] ) || ! $frm_vars['settings_routed'] ) {
			$errors = $frm_settings->validate( $_POST, array() );

			$frm_settings->update( wp_unslash( $_POST ) );

			if ( empty( $errors ) ) {
				$frm_settings->store();
				$message = __( 'Settings Saved', 'formidable' );
			}
		} else {
			$message = __( 'Settings Saved', 'formidable' );
		}

		if ( $stop_load == 'stop_load' ) {
			$frm_vars['settings_routed'] = true;

			return;
		}

		self::display_form( $errors, $message );
	}

	/**
	 * Include the Update button on the global settings page.
	 *
	 * @since 4.0.02
	 */
	public static function save_button() {
		echo '<input class="button-primary frm-button-primary" type="submit"
			value="' . esc_attr__( 'Update', 'formidable' ) . '"/>';
	}

	public static function route( $stop_load = false ) {
		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
		FrmAppHelper::include_svg();

		if ( $action == 'process-form' ) {
			self::process_form( $stop_load );
		} elseif ( $stop_load != 'stop_load' ) {
			self::display_form();
		}
	}

	/**
	 * Add CTA to the bottom on the plugin settings pages.
	 *
	 * @since 3.04.02
	 */
	public static function settings_cta( $view ) {

		if ( get_option( 'frm_lite_settings_upgrade', false ) ) {
			return;
		}

		$features = array(
			__( 'Extra form features like file uploads, pagination, etc', 'formidable' ),
			__( 'Repeaters & cascading fields for advanced forms', 'formidable' ),
			__( 'Flexibly view, search, edit, and delete entries anywhere', 'formidable' ),
			__( 'Display entries with virtually limitless Formidable views', 'formidable' ),
			__( 'Create surveys & polls', 'formidable' ),
			__( 'WordPress user registration and login forms', 'formidable' ),
			__( 'Create Stripe, PayPal or Authorize.net payment forms', 'formidable' ),
			__( 'Powerful conditional logic for smart forms', 'formidable' ),
			__( 'Integrations with 1000+ marketing & payment services', 'formidable' ),
			__( 'Collect digital signatures', 'formidable' ),
			__( 'Accept user-submitted content with Post submissions', 'formidable' ),
			__( 'Email routing', 'formidable' ),
			__( 'Create calculator forms', 'formidable' ),
			__( 'Save draft entries and return later', 'formidable' ),
			__( 'Analyze form data with graphs & stats', 'formidable' ),
		);

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/settings_cta.php' );
	}

	/**
	 * Dismiss upgrade notice at the bottom on the plugin settings pages.
	 *
	 * @since 3.04.02
	 */
	public static function settings_cta_dismiss() {
		FrmAppHelper::permission_check( 'frm_change_settings' );

		update_option( 'frm_lite_settings_upgrade', time(), 'no' );

		wp_send_json_success();
	}

	/**
	 * Autocomplete page admin ajax endpoint
	 *
	 * @since 4.03.06
	 */
	public static function page_search() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		global $wpdb;

		$term = FrmAppHelper::get_param( 'term', '', 'get', 'sanitize_text_field' );
		$post_type = FrmAppHelper::get_param( 'post_type', 'page', 'get', 'sanitize_text_field' );

		$where = array(
			'post_status'     => 'publish',
			'post_type'       => $post_type,
			'post_title LIKE' => $term,
		);

		$atts = array(
			'limit'    => 25,
			'order_by' => 'post_title',
		);

		$pages = FrmDb::get_results( $wpdb->posts, $where, 'ID, post_title', $atts );

		$results = array();
		foreach ( $pages as $page ) {
			$results[] = array(
				'value' => $page->ID,
				'label' => $page->post_title,
			);
		}

		wp_send_json( $results );
	}
}
