<?php

class FrmHooksController {

	/**
	 * Trigger plugin-wide hook loading
	 */
	public static function trigger_load_hook( $hooks = 'load_hooks' ) {
		$controllers = apply_filters( 'frm_load_controllers', array( 'FrmHooksController' ) );

		$trigger_hooks = $hooks;
		$hooks         = (array) $hooks;

		if ( 'load_hooks' == $trigger_hooks ) {
			if ( is_admin() ) {
				$hooks[] = 'load_admin_hooks';
				if ( defined( 'DOING_AJAX' ) ) {
					$hooks[] = 'load_ajax_hooks';
					$hooks[] = 'load_form_hooks';
				}
			}

			if ( is_multisite() ) {
				$hooks[] = 'load_multisite_hooks';
			}
		} else {
			// Make sure the hooks are only triggered once.
			add_filter( 'frm' . str_replace( 'load', '', $trigger_hooks ) . '_loaded', '__return_true' );
		}
		unset( $trigger_hooks );

		// Instansiate Controllers.
		foreach ( $controllers as $c ) {
			foreach ( $hooks as $hook ) {
				call_user_func( array( $c, $hook ) );
				unset( $hook );
			}
			unset( $c );
		}

	}

	public static function trigger_load_form_hooks() {
		self::trigger_load_hook( 'load_form_hooks' );
	}

	public static function load_hooks() {
		add_action( 'rest_api_init', 'FrmAppController::create_rest_routes', 0 );
		add_action( 'plugins_loaded', 'FrmAppController::load_lang' );
		add_filter( 'widget_text', 'do_shortcode' );

		// Entries controller.
		add_action( 'wp_loaded', 'FrmEntriesController::process_entry', 10, 0 );
		add_action( 'frm_after_entry_processed', 'FrmEntriesController::delete_entry_after_save', 100 );

		// Form Actions Controller.
		add_action( 'init', 'FrmFormActionsController::register_post_types', 1 );
		add_action( 'frm_after_create_entry', 'FrmFormActionsController::trigger_create_actions', 20, 3 );

		// Forms Controller.
		add_action( 'widgets_init', 'FrmFormsController::register_widgets' );
		add_action( 'init', 'FrmFormsController::front_head' );
		add_filter( 'frm_content', 'FrmFormsController::filter_content', 10, 3 );
		add_filter( 'frm_replace_content_shortcodes', 'FrmFormsController::replace_content_shortcodes', 20, 3 );
		add_action( 'admin_bar_init', 'FrmFormsController::admin_bar_css' );
		add_action( 'wp_footer', 'FrmFormsController::footer_js', 1, 0 );

		add_action( 'wp_scheduled_delete', 'FrmForm::scheduled_delete' );

		// Form Shortcodes.
		add_shortcode( 'formidable', 'FrmFormsController::get_form_shortcode' );

		// Styles Controller.
		add_action( 'init', 'FrmStylesController::register_post_types', 0 );
		add_filter( 'frm_get_style_opts', 'FrmStylesController::get_style_opts' );
		add_filter( 'frm_add_form_style_class', 'FrmStylesController::get_form_style_class', 10, 2 );
		add_filter( 'frm_show_entry_styles', 'FrmStylesController::show_entry_styles' );

		// Simple Blocks Controller.
		add_action( 'init', 'FrmSimpleBlocksController::register_simple_form_block' );

		add_filter( 'cron_schedules', 'FrmUsageController::add_schedules' );
		add_action( 'formidable_send_usage', 'FrmUsageController::send_snapshot' );
	}

	public static function load_admin_hooks() {
		add_action( 'admin_menu', 'FrmAppController::menu', 1 );
		add_filter( 'admin_body_class', 'FrmAppController::add_admin_class', 999 );
		add_action( 'admin_enqueue_scripts', 'FrmAppController::load_wp_admin_style' );
		add_action( 'admin_notices', 'FrmAppController::pro_get_started_headline' );
		add_action( 'admin_init', 'FrmAppController::admin_init', 11 );
		add_filter( 'plugin_action_links_' . FrmAppHelper::plugin_folder() . '/formidable.php', 'FrmAppController::settings_link' );
		add_filter( 'admin_footer_text', 'FrmAppController::set_footer_text' );
		add_action( 'wp_ajax_frm_dismiss_review', 'FrmAppController::dismiss_review' );
		add_action( 'wp_mail_smtp_core_recommendations_plugins', 'FrmAppController::remove_wpforms_nag' );

		// Addons Controller.
		add_action( 'admin_menu', 'FrmAddonsController::menu', 100 );
		add_filter( 'pre_set_site_transient_update_plugins', 'FrmAddonsController::check_update' );

		// Entries Controller.
		add_action( 'admin_menu', 'FrmEntriesController::menu', 12 );
		add_filter( 'set-screen-option', 'FrmEntriesController::save_per_page', 10, 3 );
		add_filter( 'update_user_metadata', 'FrmEntriesController::check_hidden_cols', 10, 5 );
		add_action( 'updated_user_meta', 'FrmEntriesController::update_hidden_cols', 10, 4 );

		// Form Actions Controller.
		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
			add_action( 'frm_before_update_form_settings', 'FrmFormActionsController::update_settings' );
		}
		add_action( 'frm_after_duplicate_form', 'FrmFormActionsController::duplicate_form_actions', 20, 3 );

		// Forms Controller.
		add_action( 'admin_menu', 'FrmFormsController::menu', 10 );
		add_action( 'admin_head-toplevel_page_formidable', 'FrmFormsController::head' );

		add_filter( 'set-screen-option', 'FrmFormsController::save_per_page', 10, 3 );
		add_action( 'admin_footer', 'FrmFormsController::insert_form_popup' );
		add_action( 'media_buttons', 'FrmFormsController::insert_form_button' );
		add_action( 'et_pb_admin_excluded_shortcodes', 'FrmFormsController::prevent_divi_conflict' );

		// Forms Model.
		add_action( 'frm_after_duplicate_form', 'FrmForm::after_duplicate', 10, 2 );

		// Settings Controller.
		add_action( 'admin_menu', 'FrmSettingsController::menu', 45 );
		add_action( 'frm_before_settings', 'FrmSettingsController::license_box' );
		add_action( 'frm_after_settings', 'FrmSettingsController::settings_cta' );
		add_action( 'wp_ajax_frm_settings_tab', 'FrmSettingsController::load_settings_tab' );

		// Styles Controller.
		add_action( 'admin_menu', 'FrmStylesController::menu', 14 );
		add_action( 'admin_init', 'FrmStylesController::admin_init' );

		// XML Controller.
		add_action( 'admin_menu', 'FrmXMLController::menu', 41 );

		// Simple Blocks Controller.
		add_action( 'enqueue_block_editor_assets', 'FrmSimpleBlocksController::block_editor_assets' );

		add_action( 'admin_init', 'FrmUsageController::schedule_send' );
	}

	public static function load_ajax_hooks() {
		add_action( 'wp_ajax_frm_install', 'FrmAppController::ajax_install' );
		add_action( 'wp_ajax_frm_uninstall', 'FrmAppController::uninstall' );
		add_action( 'wp_ajax_frm_deauthorize', 'FrmAppController::deauthorize' );

		// Addons.
		add_action( 'wp_ajax_frm_addon_activate', 'FrmAddon::activate' );
		add_action( 'wp_ajax_frm_addon_deactivate', 'FrmAddon::deactivate' );
		add_action( 'wp_ajax_frm_install_addon', 'FrmAddonsController::ajax_install_addon' );
		add_action( 'wp_ajax_frm_activate_addon', 'FrmAddonsController::ajax_activate_addon' );
		add_action( 'wp_ajax_frm_connect', 'FrmAddonsController::connect_pro' );

		// Fields Controller.
		add_action( 'wp_ajax_frm_load_field', 'FrmFieldsController::load_field' );
		add_action( 'wp_ajax_frm_insert_field', 'FrmFieldsController::create' );
		add_action( 'wp_ajax_frm_duplicate_field', 'FrmFieldsController::duplicate' );
		add_action( 'wp_ajax_frm_delete_field', 'FrmFieldsController::destroy' );
		add_action( 'wp_ajax_frm_import_options', 'FrmFieldsController::import_options' );

		// Form Actions Controller.
		add_action( 'wp_ajax_frm_add_form_action', 'FrmFormActionsController::add_form_action' );
		add_action( 'wp_ajax_frm_form_action_fill', 'FrmFormActionsController::fill_action' );

		// Forms Controller.
		add_action( 'wp_ajax_frm_save_form', 'FrmFormsController::route' );
		add_action( 'wp_ajax_frm_get_default_html', 'FrmFormsController::get_email_html' );
		add_action( 'wp_ajax_frm_get_shortcode_opts', 'FrmFormsController::get_shortcode_opts' );
		add_action( 'wp_ajax_frm_forms_preview', 'FrmFormsController::preview' );
		add_action( 'wp_ajax_nopriv_frm_forms_preview', 'FrmFormsController::preview' );
		add_action( 'wp_ajax_frm_forms_trash', 'FrmFormsController::ajax_trash' );
		add_action( 'wp_ajax_frm_install_form', 'FrmFormsController::build_new_form' );
		add_action( 'wp_ajax_frm_build_template', 'FrmFormsController::build_template' );

		// Settings.
		add_action( 'wp_ajax_frm_lite_settings_upgrade', 'FrmSettingsController::settings_cta_dismiss' );

		// Styles Controller.
		add_action( 'wp_ajax_frm_settings_reset', 'FrmStylesController::reset_styling' );
		add_action( 'wp_ajax_frm_change_styling', 'FrmStylesController::change_styling' );
		add_action( 'wp_ajax_frmpro_load_css', 'FrmStylesController::load_css' );
		add_action( 'wp_ajax_nopriv_frmpro_load_css', 'FrmStylesController::load_css' );
		add_action( 'wp_ajax_frmpro_css', 'FrmStylesController::load_saved_css' );
		add_action( 'wp_ajax_nopriv_frmpro_css', 'FrmStylesController::load_saved_css' );

		// XML Controller.
		add_action( 'wp_ajax_frm_install_template', 'FrmXMLController::install_template' );
		add_action( 'wp_ajax_frm_entries_csv', 'FrmXMLController::csv' );
		add_action( 'wp_ajax_nopriv_frm_entries_csv', 'FrmXMLController::csv' );
		add_action( 'wp_ajax_frm_export_xml', 'FrmXMLController::export_xml' );
	}

	public static function load_form_hooks() {
		// Fields Controller.
		add_filter( 'frm_field_type', 'FrmFieldsController::change_type' );
		add_action( 'frm_field_input_html', 'FrmFieldsController::input_html' );
		add_filter( 'frm_field_value_saved', 'FrmFieldsController::check_value', 50, 3 );
		add_filter( 'frm_field_label_seen', 'FrmFieldsController::check_label' );

		// Forms Controller.
		add_filter( 'frm_form_classes', 'FrmFormsController::form_classes' );

		// Styles Controller.
		add_filter( 'frm_use_important_width', 'FrmStylesController::important_style', 10, 2 );
	}

	public static function load_view_hooks() {
		// Hooks go here when a view is loaded.
	}

	public static function load_multisite_hooks() {
		add_action( 'wpmu_upgrade_site', 'FrmAppController::network_upgrade_site' );

		// Drop tables when mu site is deleted.
		add_filter( 'wpmu_drop_tables', 'FrmAppController::drop_tables' );
	}
}
