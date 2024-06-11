<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmHooksController {

	/**
	 * Trigger plugin-wide hook loading
	 *
	 * @param string $hooks
	 *
	 * @return void
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
				if ( is_callable( array( $c, $hook ) ) ) {
					call_user_func( array( $c, $hook ) );
				}
				unset( $hook );
			}
			unset( $c );
		}
	}

	/**
	 * @return void
	 */
	public static function trigger_load_form_hooks() {
		self::trigger_load_hook( 'load_form_hooks' );
	}

	/**
	 * @return void
	 */
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

		/**
		 * Make name field work with View.
		 * FrmProContent::replace_single_shortcode() applies this filter like 'frm_keep_' . $field->type . '_value_array'
		 */
		add_filter( 'frm_keep_name_value_array', '__return_true' );

		// Elementor.
		add_action( 'elementor/widgets/register', 'FrmElementorController::register_elementor_hooks' );
		add_filter( 'frm_fields_in_form_builder', 'FrmFormsController::update_form_builder_fields', 10, 2 );

		// Summary emails.
		add_action( 'frm_daily_event', 'FrmEmailSummaryController::maybe_send_emails' );

		FrmTransLiteHooksController::load_hooks();
		FrmStrpLiteHooksController::load_hooks();
	}

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_action( 'admin_menu', 'FrmAppController::menu', 1 );
		add_filter( 'admin_body_class', 'FrmAppController::add_admin_class', 999 );
		add_action( 'admin_notices', 'FrmAppController::pro_get_started_headline' );
		add_action( 'admin_init', 'FrmAppController::admin_init', 11 );
		add_action( 'admin_enqueue_scripts', 'FrmAppController::admin_enqueue_scripts' );
		add_filter( 'plugin_action_links_' . FrmAppHelper::plugin_folder() . '/formidable.php', 'FrmAppController::settings_link' );
		add_filter( 'admin_footer_text', 'FrmAppController::set_footer_text' );
		add_action( 'admin_footer', 'FrmAppController::add_admin_footer_links' );
		add_action( 'wp_ajax_frm_dismiss_review', 'FrmAppController::dismiss_review' );

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
			add_action( 'frm_add_form_style_tab_options', 'FrmFormsController::add_form_style_tab_options' );
		}
		add_action( 'frm_after_duplicate_form', 'FrmFormActionsController::duplicate_form_actions', 20, 3 );

		// Forms Controller.
		add_action( 'admin_menu', 'FrmFormsController::menu', 10 );
		add_action( 'admin_head-toplevel_page_formidable', 'FrmFormsController::head' );
		add_action( 'frm_after_field_options', 'FrmFormsController::logic_tip' );

		add_filter( 'set-screen-option', 'FrmFormsController::save_per_page', 10, 3 );
		add_action( 'admin_footer', 'FrmFormsController::insert_form_popup' );

		// Elementor.
		add_action( 'elementor/editor/footer', 'FrmElementorController::admin_init' );

		add_action( 'media_buttons', 'FrmFormsController::insert_form_button' );
		add_action( 'et_pb_admin_excluded_shortcodes', 'FrmFormsController::prevent_divi_conflict' );

		// Forms Model.
		add_action( 'frm_after_duplicate_form', 'FrmForm::after_duplicate', 10, 2 );

		// Settings Controller.
		add_action( 'admin_menu', 'FrmSettingsController::menu', 45 );
		add_action( 'frm_before_settings', 'FrmSettingsController::license_box' );
		add_action( 'frm_after_settings', 'FrmSettingsController::settings_cta' );
		add_action( 'wp_ajax_frm_settings_tab', 'FrmSettingsController::load_settings_tab' );
		add_action( 'wp_ajax_frm_page_search', 'FrmSettingsController::page_search' );

		// Styles Controller.
		add_action( 'admin_menu', 'FrmStylesController::menu', 14 );
		add_action( 'plugins_loaded', 'FrmStylesController::plugins_loaded' );
		add_action( 'admin_init', 'FrmStylesController::admin_init' );
		// Use 11 so it happens after add_action( 'wp_default_styles', 'wp_default_styles' ); where edit.css is added.
		add_action( 'wp_default_styles', 'FrmStylesController::disable_conflicting_wp_admin_css', 11 );

		// XML Controller.
		add_action( 'admin_menu', 'FrmXMLController::menu', 41 );

		// Simple Blocks Controller.
		add_action( 'enqueue_block_editor_assets', 'FrmSimpleBlocksController::block_editor_assets' );

		add_action( 'admin_init', 'FrmUsageController::schedule_send' );

		// Applications Controller.
		// Use the same priority as styles so Applications appear directly under Styles.
		add_action( 'admin_menu', 'FrmApplicationsController::menu', 14 );
		add_action( 'admin_enqueue_scripts', 'FrmApplicationsController::dequeue_scripts', 15 );
		add_action( 'wp_ajax_frm_get_applications_data', 'FrmApplicationsController::get_applications_data' );

		// CAPTCHA
		add_filter( 'frm_setup_edit_field_vars', 'FrmFieldCaptcha::update_field_name' );

		// From Templates.
		FrmFormTemplatesController::load_admin_hooks();

		// Cronjob.
		add_action( 'admin_init', 'FrmCronController::schedule_events' );

		FrmDashboardController::load_admin_hooks();
		FrmTransLiteHooksController::load_admin_hooks();
		FrmStrpLiteHooksController::load_admin_hooks();
		FrmSMTPController::load_hooks();
		FrmOnboardingWizardController::load_admin_hooks();
		new FrmPluginSearch();
	}

	/**
	 * @return void
	 */
	public static function load_ajax_hooks() {
		add_action( 'wp_ajax_frm_install', 'FrmAppController::ajax_install' );
		add_action( 'wp_ajax_frm_uninstall', 'FrmAppController::uninstall' );
		add_action( 'wp_ajax_frm_deauthorize', 'FrmAppController::deauthorize' );

		// Onboarding Wizard Controller.
		add_action( 'wp_ajax_frm_onboarding_setup_email_step', 'FrmOnboardingWizardController::ajax_setup_email_step' );
		add_action( 'wp_ajax_frm_onboarding_setup_usage_data', 'FrmOnboardingWizardController::setup_usage_data' );

		// Addons.
		add_action( 'wp_ajax_frm_addon_activate', 'FrmAddon::activate' );
		add_action( 'wp_ajax_frm_addon_deactivate', 'FrmAddon::deactivate' );
		add_action( 'wp_ajax_frm_activate_addon', 'FrmAddonsController::ajax_activate_addon' );
		add_action( 'wp_ajax_frm_deactivate_addon', 'FrmAddonsController::ajax_deactivate_addon' );
		add_action( 'wp_ajax_frm_install_addon', 'FrmAddonsController::ajax_install_addon' );
		add_action( 'wp_ajax_frm_uninstall_addon', 'FrmAddonsController::ajax_uninstall_addon' );
		// Plugin.
		add_action( 'wp_ajax_frm_install_plugin', 'FrmInstallPlugin::ajax_install_plugin' );
		add_action( 'wp_ajax_frm_check_plugin_activation', 'FrmInstallPlugin::ajax_check_plugin_activation' );

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
		add_action( 'wp_ajax_frm_rename_form', 'FrmFormsController::rename_form' );
		add_action( 'wp_ajax_frm_get_default_html', 'FrmFormsController::get_email_html' );
		add_action( 'wp_ajax_frm_get_shortcode_opts', 'FrmFormsController::get_shortcode_opts' );
		add_action( 'wp_ajax_frm_forms_preview', 'FrmFormsController::preview' );
		add_action( 'wp_ajax_nopriv_frm_forms_preview', 'FrmFormsController::preview' );
		add_action( 'wp_ajax_frm_forms_trash', 'FrmFormsController::ajax_trash' );
		add_action( 'wp_ajax_frm_install_form', 'FrmFormsController::build_new_form' );
		add_action( 'wp_ajax_frm_create_page_with_shortcode', 'FrmFormsController::create_page_with_shortcode' );
		add_action( 'wp_ajax_get_page_dropdown', 'FrmFormsController::get_page_dropdown' );

		add_action( 'wp_ajax_frm_dismiss_migrator', 'FrmFormMigratorsHelper::dismiss_migrator' );

		// Form Templates Controller.
		add_action( 'wp_ajax_frm_add_or_remove_favorite_template', 'FrmFormTemplatesController::ajax_add_or_remove_favorite' );
		add_action( 'wp_ajax_frm_create_template', 'FrmFormTemplatesController::ajax_create_template' );

		// Inbox.
		add_action( 'wp_ajax_frm_inbox_dismiss', 'FrmInboxController::dismiss_message' );

		// Settings.
		add_action( 'wp_ajax_frm_lite_settings_upgrade', 'FrmSettingsController::settings_cta_dismiss' );

		// Styles Controller.
		add_action( 'wp_ajax_frm_settings_reset', 'FrmStylesController::reset_styling' );
		add_action( 'wp_ajax_frm_change_styling', 'FrmStylesController::change_styling' );
		add_action( 'wp_ajax_frmpro_load_css', 'FrmStylesController::load_css' );
		add_action( 'wp_ajax_nopriv_frmpro_load_css', 'FrmStylesController::load_css' );
		add_action( 'wp_ajax_frmpro_css', 'FrmStylesController::load_saved_css' );
		add_action( 'wp_ajax_nopriv_frmpro_css', 'FrmStylesController::load_saved_css' );
		add_action( 'wp_ajax_frm_rename_style', 'FrmStylesController::rename_style' );

		// XML Controller.
		add_action( 'wp_ajax_frm_install_template', 'FrmXMLController::install_template' );
		add_action( 'wp_ajax_frm_entries_csv', 'FrmXMLController::csv' );
		add_action( 'wp_ajax_nopriv_frm_entries_csv', 'FrmXMLController::csv' );
		add_action( 'wp_ajax_frm_export_xml', 'FrmXMLController::export_xml' );

		// Templates API.
		add_action( 'wp_ajax_template_api_signup', 'FrmFormTemplateApi::signup' );

		// Dashboard Controller.
		add_action( 'wp_ajax_dashboard_ajax_action', 'FrmDashboardController::ajax_requests' );

		// Submit with AJAX.
		// Trigger before process_entry.
		add_action( 'wp_loaded', 'FrmEntriesAJAXSubmitController::ajax_create', 5 );
	}

	/**
	 * @return void
	 */
	public static function load_form_hooks() {
		// Fields Controller.
		add_filter( 'frm_field_type', 'FrmFieldsController::change_type' );
		add_action( 'frm_field_input_html', 'FrmFieldsController::input_html' );
		add_filter( 'frm_field_value_saved', 'FrmFieldsController::check_value', 50, 3 );
		add_filter( 'frm_field_label_seen', 'FrmFieldsController::check_label' );

		// Forms Controller.
		add_filter( 'frm_form_classes', 'FrmFormsController::form_classes' );
		add_filter( 'frm_submit_button_class', 'FrmFormsController::update_button_classes' );
		add_filter( 'frm_back_button_class', 'FrmFormsController::update_button_classes' );

		add_filter( 'frm_pre_display_form', 'FrmSubmitHelper::copy_submit_field_settings_to_form' );

		// Styles Controller.
		add_filter( 'frm_use_important_width', 'FrmStylesController::important_style', 10, 2 );
	}

	/**
	 * @return void
	 */
	public static function load_view_hooks() {
		// Hooks go here when a view is loaded.
	}

	/**
	 * @return void
	 */
	public static function load_multisite_hooks() {
		add_action( 'wpmu_upgrade_site', 'FrmAppController::network_upgrade_site' );

		// Drop tables when mu site is deleted.
		add_filter( 'wpmu_drop_tables', 'FrmAppController::drop_tables' );
	}
}
